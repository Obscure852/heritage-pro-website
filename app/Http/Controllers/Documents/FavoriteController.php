<?php

namespace App\Http\Controllers\Documents;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\DocumentFavorite;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FavoriteController extends Controller {
    /**
     * Toggle favorite status for a document.
     */
    public function toggle(Document $document, Request $request): JsonResponse {
        $this->authorize('view', $document);

        $user = $request->user();
        $exists = DocumentFavorite::where('user_id', $user->id)
            ->where('document_id', $document->id)
            ->exists();

        if ($exists) {
            DocumentFavorite::where('user_id', $user->id)
                ->where('document_id', $document->id)
                ->delete();
            $isFavorited = false;
        } else {
            DocumentFavorite::create([
                'user_id' => $user->id,
                'document_id' => $document->id,
            ]);
            $isFavorited = true;
        }

        return response()->json([
            'success' => true,
            'is_favorited' => $isFavorited,
        ]);
    }
}
