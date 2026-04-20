<?php

namespace App\Http\Controllers\Documents;

use App\Http\Controllers\Controller;
use App\Http\Requests\Documents\StoreShareRequest;
use App\Models\Document;
use App\Models\Role;
use App\Models\DocumentShare;
use App\Models\User;
use App\Services\Documents\SharingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ShareController extends Controller {
    private SharingService $sharingService;

    public function __construct(SharingService $sharingService) {
        $this->sharingService = $sharingService;
    }

    /**
     * List all active shares for a document.
     */
    public function index(Document $document): JsonResponse {
        $this->authorize('view', $document);

        $shares = $this->sharingService->getDocumentShares($document);

        // Pre-load users and roles in bulk to avoid N+1
        $userIds = $shares->where('shareable_type', DocumentShare::TYPE_USER)->pluck('shareable_id')->map(fn ($id) => (int) $id)->unique();
        $roleIds = $shares->where('shareable_type', DocumentShare::TYPE_ROLE)->pluck('shareable_id')->map(fn ($id) => (int) $id)->unique();

        $usersMap = $userIds->isNotEmpty()
            ? User::select('id', 'firstname', 'lastname', 'email')->whereIn('id', $userIds)->get()->keyBy('id')
            : collect();
        $rolesMap = $roleIds->isNotEmpty()
            ? Role::select('id', 'name')->whereIn('id', $roleIds)->get()->keyBy('id')
            : collect();

        // Group by type and enrich
        $grouped = [
            'users' => [],
            'roles' => [],
            'departments' => [],
        ];

        foreach ($shares as $share) {
            $shareData = [
                'id' => $share->id,
                'shareable_type' => $share->shareable_type,
                'shareable_id' => $share->shareable_id,
                'permission' => $share->permission_level,
                'shared_by' => $share->sharedBy ? $share->sharedBy->full_name : null,
                'message' => $share->message,
                'created_at' => $share->created_at->toISOString(),
            ];

            switch ($share->shareable_type) {
                case DocumentShare::TYPE_USER:
                    $user = $usersMap->get((int) $share->shareable_id);
                    $shareData['user_name'] = $user ? $user->full_name : 'Unknown User';
                    $shareData['user_email'] = $user?->email;
                    $grouped['users'][] = $shareData;
                    break;
                case DocumentShare::TYPE_ROLE:
                    $role = $rolesMap->get((int) $share->shareable_id);
                    $shareData['role_name'] = $role ? $role->name : (string) $share->shareable_id;
                    $grouped['roles'][] = $shareData;
                    break;
                case DocumentShare::TYPE_DEPARTMENT:
                    $grouped['departments'][] = $shareData;
                    break;
            }
        }

        return response()->json($grouped);
    }

    /**
     * Create a new share on a document.
     */
    public function store(StoreShareRequest $request, Document $document): JsonResponse {
        $this->authorize('share', $document);

        try {
            $share = $this->sharingService->createShare(
                $document,
                auth()->user(),
                $request->validated()
            );

            return response()->json([
                'message' => 'Document shared successfully.',
                'share' => $share,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Unable to share document.',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    /**
     * Revoke (soft-delete) a share.
     */
    public function destroy(Document $document, DocumentShare $share): JsonResponse {
        $this->authorize('share', $document);

        // Validate the share belongs to this document
        if ($share->document_id !== $document->id) {
            return response()->json(['message' => 'Share does not belong to this document.'], 404);
        }

        $this->sharingService->revokeShare($share, auth()->user());

        return response()->json(['message' => 'Share revoked successfully.']);
    }

    /**
     * Search users for share autocomplete.
     */
    public function userSearch(Request $request): JsonResponse {
        $this->authorize('viewAny', Document::class);

        $request->validate([
            'q' => 'required|string|min:2',
        ]);

        $users = $this->sharingService->searchUsers($request->q);

        return response()->json($users);
    }

    /**
     * Return available roles for sharing.
     */
    public function roles(): JsonResponse {
        $this->authorize('viewAny', Document::class);

        return response()->json($this->sharingService->getAvailableRoles());
    }

    /**
     * Return available departments for sharing.
     */
    public function departments(): JsonResponse {
        $this->authorize('viewAny', Document::class);

        return response()->json($this->sharingService->getAvailableDepartments());
    }

    /**
     * Show documents shared with the current user.
     * View rendering done in Plan 03; this controller passes data.
     */
    public function sharedWithMe(Request $request) {
        $this->authorize('viewAny', Document::class);

        $user = auth()->user();
        $roleIdentifiers = array_values(array_unique(array_map('strval', array_merge(
            $user->roles()->pluck('roles.id')->toArray(),
            $user->roles()->pluck('roles.name')->toArray()
        ))));

        $shares = DocumentShare::where('is_active', true)
            ->whereNull('revoked_at')
            ->where(function ($query) use ($user, $roleIdentifiers) {
                // Shared directly with user
                $query->where(function ($q) use ($user) {
                    $q->where('shareable_type', DocumentShare::TYPE_USER)
                      ->where('shareable_id', (string) $user->id);
                });

                // Shared with user's roles
                if (!empty($roleIdentifiers)) {
                    $query->orWhere(function ($q) use ($roleIdentifiers) {
                        $q->where('shareable_type', DocumentShare::TYPE_ROLE)
                          ->whereIn('shareable_id', $roleIdentifiers);
                    });
                }

                // Shared with user's department
                if ($user->department) {
                    $query->orWhere(function ($q) use ($user) {
                        $q->where('shareable_type', DocumentShare::TYPE_DEPARTMENT)
                          ->where('shareable_id', (string) $user->department);
                    });
                }
            })
            ->with(['document', 'sharedBy'])
            ->orderByDesc('created_at')
            ->get()
            ->groupBy('shared_by_user_id');

        return view('documents.shared', ['shareGroups' => $shares]);
    }
}
