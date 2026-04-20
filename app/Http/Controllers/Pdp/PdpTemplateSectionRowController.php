<?php

namespace App\Http\Controllers\Pdp;

use App\Models\Pdp\PdpTemplate;
use App\Models\Pdp\PdpTemplateSection;
use App\Models\Pdp\PdpTemplateSectionRow;
use App\Services\Pdp\PdpTemplateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use LogicException;

class PdpTemplateSectionRowController extends BasePdpController
{
    public function __construct(
        \App\Services\Pdp\PdpAccessService $accessService,
        private readonly PdpTemplateService $templateService
    ) {
        parent::__construct($accessService);
        $this->middleware('auth');
    }

    public function store(Request $request, PdpTemplate $template, PdpTemplateSection $section): RedirectResponse
    {
        $this->authorizeTemplateManage($request->user());
        abort_unless($section->pdp_template_id === $template->id, 404);
        $parentRow = null;

        if ($request->filled('parent_row_id')) {
            $parentRow = PdpTemplateSectionRow::query()->findOrFail((int) $request->integer('parent_row_id'));
        }

        try {
            $this->templateService->createSectionRow(
                $section,
                $request->input('values', []),
                $request->filled('sort_order') ? (int) $request->integer('sort_order') : null,
                $parentRow
            );
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (InvalidArgumentException|LogicException $exception) {
            return $this->redirectWithTemplateError($template, $section, $exception->getMessage());
        }

        return redirect()
            ->to(route('staff.pdp.templates.show', $template) . '#section-' . $section->key)
            ->with('message', 'Template section row added successfully.');
    }

    public function update(
        Request $request,
        PdpTemplate $template,
        PdpTemplateSection $section,
        PdpTemplateSectionRow $row
    ): RedirectResponse {
        $this->authorizeTemplateManage($request->user());
        abort_unless($section->pdp_template_id === $template->id && $row->pdp_template_section_id === $section->id, 404);

        try {
            $this->templateService->updateSectionRow(
                $section,
                $row,
                $request->input('values', []),
                $request->filled('sort_order') ? (int) $request->integer('sort_order') : null
            );
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (InvalidArgumentException|LogicException $exception) {
            return $this->redirectWithTemplateError($template, $section, $exception->getMessage());
        }

        return redirect()
            ->to(route('staff.pdp.templates.show', $template) . '#section-' . $section->key)
            ->with('message', 'Template section row updated successfully.');
    }

    public function destroy(
        Request $request,
        PdpTemplate $template,
        PdpTemplateSection $section,
        PdpTemplateSectionRow $row
    ): RedirectResponse {
        $this->authorizeTemplateManage($request->user());
        abort_unless($section->pdp_template_id === $template->id && $row->pdp_template_section_id === $section->id, 404);

        try {
            $this->templateService->deleteSectionRow($section, $row);
        } catch (InvalidArgumentException|LogicException $exception) {
            return $this->redirectWithTemplateError($template, $section, $exception->getMessage());
        }

        return redirect()
            ->to(route('staff.pdp.templates.show', $template) . '#section-' . $section->key)
            ->with('message', 'Template section row removed successfully.');
    }

    private function redirectWithTemplateError(PdpTemplate $template, PdpTemplateSection $section, string $message): RedirectResponse
    {
        return redirect()
            ->to(route('staff.pdp.templates.show', $template) . '#section-' . $section->key)
            ->withErrors(['template_rows' => $message]);
    }
}
