<?php

namespace App\Http\Controllers\Crm;

use App\Exports\Crm\ArrayRowsExport;
use App\Http\Requests\Crm\CrmImportPreviewRequest;
use App\Models\CrmImportRun;
use App\Services\Crm\Imports\CrmImportDefinitionRegistry;
use App\Services\Crm\Imports\CrmImportPreviewService;
use App\Services\Crm\Imports\CrmImportRunService;
use App\Services\Crm\Imports\CrmImportTemplateService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ImportController extends CrmController
{
    public function __construct(
        private readonly CrmImportDefinitionRegistry $definitionRegistry,
        private readonly CrmImportTemplateService $templateService,
        private readonly CrmImportPreviewService $previewService,
        private readonly CrmImportRunService $runService
    ) {
    }

    public function index(): RedirectResponse
    {
        return redirect()->route('crm.settings.imports.users');
    }

    public function users(): View
    {
        return $this->renderEntityPage('users');
    }

    public function leads(): View
    {
        return $this->renderEntityPage('leads');
    }

    public function customers(): View
    {
        return $this->renderEntityPage('customers');
    }

    public function contacts(): View
    {
        return $this->renderEntityPage('contacts');
    }

    public function preview(CrmImportPreviewRequest $request): RedirectResponse
    {
        $this->authorizeAdminSettings();

        try {
            $run = $this->previewService->createPreview(
                $request->validated('entity'),
                $request->file('file'),
                $this->crmUser()
            );
        } catch (\Throwable $exception) {
            return back()->withErrors(['file' => $exception->getMessage()]);
        }

        return redirect()
            ->route('crm.settings.imports.' . $run->entity, ['preview_run' => $run->uuid])
            ->with('crm_success', 'Import preview generated successfully.');
    }

    public function confirm(CrmImportRun $crmImportRun): RedirectResponse
    {
        $this->authorizeAdminSettings();

        try {
            ['run' => $run, 'processed' => $processed] = $this->runService->process($crmImportRun);
        } catch (\Throwable $exception) {
            return redirect()
                ->route('crm.settings.imports.' . $crmImportRun->entity, ['preview_run' => $crmImportRun->uuid])
                ->with('crm_error', $exception->getMessage());
        }

        $message = 'This import has already been processed.';

        if ($processed) {
            $message = $run->status === 'completed_with_errors'
                ? 'Import finished with some errors.'
                : 'Import completed successfully.';
        }

        return redirect()
            ->route('crm.settings.imports.runs.show', $run)
            ->with('crm_success', $message);
    }

    public function showRun(CrmImportRun $crmImportRun): View
    {
        $this->authorizeAdminSettings();

        $crmImportRun->load('initiatedBy');
        $rows = $crmImportRun->rows()->orderBy('row_number')->paginate(25);

        return view('crm.settings.import-run-show', [
            'run' => $crmImportRun,
            'rows' => $rows,
            'settingsTabsActive' => 'imports',
            'entityTabs' => $this->definitionRegistry->entities(),
            'importDefinition' => $this->definitionRegistry->entity($crmImportRun->entity),
            'importStatuses' => config('heritage_crm.import_statuses'),
        ]);
    }

    public function downloadTemplate(string $entity): BinaryFileResponse
    {
        $this->authorizeAdminSettings();

        return $this->templateService->download($entity);
    }

    public function downloadFailures(CrmImportRun $crmImportRun): BinaryFileResponse
    {
        $this->authorizeAdminSettings();

        $rows = $this->runService->failureRows($crmImportRun)
            ->map(function ($row) {
                return [
                    $row->row_number,
                    $row->normalized_key,
                    $row->action,
                    implode(' | ', $row->validation_errors ?? []),
                ];
            })
            ->values()
            ->all();

        return Excel::download(
            new ArrayRowsExport(['row_number', 'normalized_key', 'action', 'errors'], $rows),
            'crm-' . $crmImportRun->entity . '-import-failures-' . $crmImportRun->uuid . '.xlsx'
        );
    }

    public function downloadPasswords(CrmImportRun $crmImportRun): StreamedResponse|RedirectResponse
    {
        $this->authorizeAdminSettings();

        $rows = $this->runService->consumePasswords($crmImportRun);

        if ($rows->isEmpty()) {
            return redirect()
                ->route('crm.settings.imports.runs.show', $crmImportRun)
                ->with('crm_error', 'Temporary password results are not available for this run.');
        }

        $exportRows = $rows->map(fn (array $row) => [
            $row['name'] ?? '',
            $row['email'] ?? '',
            $row['role'] ?? '',
            $row['temporary_password'] ?? '',
        ])->all();

        $binary = Excel::raw(
            new ArrayRowsExport(['name', 'email', 'role', 'temporary_password'], $exportRows),
            \Maatwebsite\Excel\Excel::XLSX
        );

        return response()->streamDownload(
            function () use ($binary) {
                echo $binary;
            },
            'crm-user-import-passwords-' . $crmImportRun->uuid . '.xlsx',
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        );
    }

    private function renderEntityPage(string $entity): View
    {
        $this->authorizeAdminSettings();

        $definition = $this->definitionRegistry->entity($entity);
        $previewRunUuid = request()->query('preview_run');
        $previewRun = null;
        $previewRows = collect();

        if (is_string($previewRunUuid) && $previewRunUuid !== '') {
            $previewRun = CrmImportRun::query()
                ->where('uuid', $previewRunUuid)
                ->where('entity', $entity)
                ->first();

            if ($previewRun) {
                $previewRows = $previewRun->rows()->orderBy('row_number')->limit(25)->get();
            }
        }

        return view('crm.settings.imports', [
            'activeSection' => 'imports',
            'activeImportEntity' => $entity,
            'settingsTabsActive' => 'imports',
            'entityTabs' => $this->definitionRegistry->entities(),
            'importDefinition' => $definition,
            'previewRun' => $previewRun,
            'previewRows' => $previewRows,
            'recentRuns' => CrmImportRun::query()
                ->with('initiatedBy')
                ->where('entity', $entity)
                ->latest()
                ->limit(10)
                ->get(),
            'importStatuses' => config('heritage_crm.import_statuses'),
        ]);
    }
}
