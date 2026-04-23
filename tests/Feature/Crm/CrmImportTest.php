<?php

namespace Tests\Feature\Crm;

use App\Models\Contact;
use App\Models\CrmImportRun;
use App\Models\CrmUserDepartment;
use App\Models\CrmUserFilter;
use App\Models\CrmUserPosition;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\User;
use App\Services\Crm\Imports\CrmImportRunService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class CrmImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_render_import_pages_and_download_templates(): void
    {
        $admin = $this->createUser();

        $this->actingAs($admin)
            ->get(route('crm.settings.imports'))
            ->assertRedirect(route('crm.settings.imports.users'));

        $this->actingAs($admin)
            ->get(route('crm.settings.imports.users'))
            ->assertOk()
            ->assertSee('Users import columns');

        $this->actingAs($admin)
            ->get(route('crm.settings.imports.leads'))
            ->assertOk()
            ->assertSee('Leads import columns');

        $this->actingAs($admin)
            ->get(route('crm.settings.imports.contacts'))
            ->assertOk()
            ->assertSee('Contacts import columns');

        $this->assertSame(
            config('heritage_crm.imports.entities.users.headings'),
            $this->downloadedHeadings($admin, 'users')
        );

        $this->assertSame(
            config('heritage_crm.imports.entities.leads.headings'),
            $this->downloadedHeadings($admin, 'leads')
        );

        $this->assertSame(
            config('heritage_crm.imports.entities.contacts.headings'),
            $this->downloadedHeadings($admin, 'contacts')
        );
    }

    public function test_non_admin_users_cannot_access_import_routes(): void
    {
        Storage::fake('documents');
        $rep = $this->createUser([
            'role' => 'rep',
            'email' => 'rep@example.com',
        ]);

        $this->actingAs($rep)
            ->get(route('crm.settings.imports.users'))
            ->assertForbidden();

        $this->actingAs($rep)
            ->post(route('crm.settings.imports.preview'), [
                'entity' => 'users',
                'file' => $this->makeSpreadsheetUpload(
                    'users.xlsx',
                    config('heritage_crm.imports.entities.users.headings'),
                    [['Rep User', 'rep2@example.com', 'rep', 'yes']]
                ),
            ])
            ->assertForbidden();
    }

    public function test_preview_rejects_bad_headers_and_records_invalid_rows(): void
    {
        Storage::fake('documents');
        $admin = $this->createUser();

        $this->actingAs($admin)
            ->from(route('crm.settings.imports.users'))
            ->post(route('crm.settings.imports.preview'), [
                'entity' => 'users',
                'file' => $this->makeSpreadsheetUpload(
                    'bad-users.xlsx',
                    ['full_name', 'email', 'role'],
                    [['Broken Header User', 'broken@example.com', 'rep']]
                ),
            ])
            ->assertRedirect(route('crm.settings.imports.users'))
            ->assertSessionHasErrors('file');

        $this->actingAs($admin)
            ->post(route('crm.settings.imports.preview'), [
                'entity' => 'users',
                'file' => $this->makeSpreadsheetUpload(
                    'users-invalid.xlsx',
                    config('heritage_crm.imports.entities.users.headings'),
                    [['Preview Error', 'preview-error@example.com', 'invalid-role', 'maybe']]
                ),
            ])
            ->assertRedirect();

        $run = CrmImportRun::query()->latest('id')->firstOrFail();
        $row = $run->rows()->firstOrFail();

        $this->assertSame('validated', $run->status);
        $this->assertSame(1, $run->failed_count);
        $this->assertSame('error', $row->action);
        $this->assertStringContainsString('role', strtolower(implode(' ', $row->validation_errors ?? [])));
        $this->assertStringContainsString('boolean', strtolower(implode(' ', $row->validation_errors ?? [])));
    }

    public function test_preview_ignores_trailing_blank_header_columns_from_sheet_range(): void
    {
        Storage::fake('documents');
        $admin = $this->createUser();

        $this->actingAs($admin)
            ->post(route('crm.settings.imports.preview'), [
                'entity' => 'users',
                'file' => $this->makeSpreadsheetUpload(
                    'users-trailing-blank-columns.xlsx',
                    config('heritage_crm.imports.entities.users.headings'),
                    [['Trim Header User', 'trim-header-user@example.com', 'rep', 'yes']],
                    function ($sheet) {
                        $sheet->getColumnDimension('Z')->setWidth(12);
                    }
                ),
            ])
            ->assertRedirect();

        $run = CrmImportRun::query()->latest('id')->firstOrFail();
        $row = $run->rows()->firstOrFail();

        $this->assertSame('validated', $run->status);
        $this->assertSame(0, $run->failed_count);
        $this->assertSame('create', $row->action);
    }

    public function test_preview_ignores_trailing_blank_rows_from_sheet_range(): void
    {
        Storage::fake('documents');
        $admin = $this->createUser();

        $this->actingAs($admin)
            ->post(route('crm.settings.imports.preview'), [
                'entity' => 'users',
                'file' => $this->makeSpreadsheetUpload(
                    'users-trailing-blank-rows.xlsx',
                    config('heritage_crm.imports.entities.users.headings'),
                    [['Trim Row User', 'trim-row-user@example.com', 'rep', 'yes']],
                    function ($sheet) {
                        $sheet->getRowDimension(25)->setRowHeight(18);
                    }
                ),
            ])
            ->assertRedirect();

        $run = CrmImportRun::query()->latest('id')->firstOrFail();
        $row = $run->rows()->sole();

        $this->assertSame('validated', $run->status);
        $this->assertSame(1, $run->total_count);
        $this->assertSame(0, $run->skipped_count);
        $this->assertSame(0, $run->failed_count);
        $this->assertSame('create', $row->action);
    }

    public function test_user_import_requires_dd_mm_yyyy_for_text_dates(): void
    {
        Storage::fake('documents');
        $admin = $this->createUser();

        $this->actingAs($admin)
            ->post(route('crm.settings.imports.preview'), [
                'entity' => 'users',
                'file' => $this->makeSpreadsheetUpload(
                    'users-invalid-dates.xlsx',
                    config('heritage_crm.imports.entities.users.headings'),
                    [[
                        'Date Format User',
                        'date-format-user@example.com',
                        'rep',
                        'yes',
                        '1990-02-14',
                        'female',
                        'Botswana',
                        'ID-9002',
                        '+267 7000 0000',
                        'active',
                        'Student Services',
                        'Coordinator',
                        $admin->email,
                        'PAY-9002',
                        '2024-03-01',
                        'Priority',
                    ]]
                ),
            ])
            ->assertRedirect();

        $run = CrmImportRun::query()->latest('id')->firstOrFail();
        $row = $run->rows()->firstOrFail();
        $errors = implode(' ', $row->validation_errors ?? []);

        $this->assertSame('error', $row->action);
        $this->assertStringContainsString('DD/MM/YYYY', $errors);
    }

    public function test_user_import_accepts_common_role_labels_case_insensitively(): void
    {
        Storage::fake('documents');
        $admin = $this->createUser();

        $run = $this->previewImport($admin, 'users', [
            ['Admin Label User', 'admin-label@example.com', 'Admin', 'yes'],
            ['Manager Label User', 'manager-label@example.com', 'Manager', 'yes'],
            ['Rep Label User', 'rep-label@example.com', 'Rep', 'yes'],
        ]);

        $rows = $run->rows()->orderBy('row_number')->get();

        $this->assertSame('validated', $run->status);
        $this->assertSame(0, $run->failed_count);
        $this->assertSame('admin', $rows[0]->payload['role']);
        $this->assertSame('manager', $rows[1]->payload['role']);
        $this->assertSame('rep', $rows[2]->payload['role']);
        $this->assertSame('create', $rows[0]->action);
        $this->assertSame('create', $rows[1]->action);
        $this->assertSame('create', $rows[2]->action);
    }

    public function test_confirm_processes_immediately_and_is_idempotent(): void
    {
        Storage::fake('documents');

        $admin = $this->createUser();
        $firstRun = $this->previewImport($admin, 'users', [
            ['Import Admin', 'import-admin@example.com', 'admin', 'yes'],
        ]);

        $this->actingAs($admin)
            ->post(route('crm.settings.imports.confirm', $firstRun))
            ->assertRedirect(route('crm.settings.imports.runs.show', $firstRun));

        $this->actingAs($admin)
            ->post(route('crm.settings.imports.confirm', $firstRun))
            ->assertRedirect(route('crm.settings.imports.runs.show', $firstRun));

        $this->assertSame('completed', $firstRun->fresh()->status);
        $this->assertSame(1, CrmImportRun::query()->count());

        $secondRun = $this->previewImport($admin, 'users', [
            ['Direct Import User', 'direct-import-user@example.com', 'rep', 'yes'],
        ]);

        $this->actingAs($admin)
            ->post(route('crm.settings.imports.confirm', $secondRun))
            ->assertRedirect(route('crm.settings.imports.runs.show', $secondRun));

        $this->assertSame('completed', $secondRun->fresh()->status);

        $leadRun = $this->previewImport($admin, 'leads', [
            ['LEAD-001', $admin->email, 'Immediate Lead', 'Education', '', '', '', 'Botswana', 'active', 'Imported directly'],
        ]);

        $this->actingAs($admin)
            ->post(route('crm.settings.imports.confirm', $leadRun))
            ->assertRedirect(route('crm.settings.imports.runs.show', $leadRun));

        $this->assertSame('completed', $leadRun->fresh()->status);
    }

    public function test_user_import_upserts_and_password_export_is_one_time(): void
    {
        Storage::fake('documents');
        $admin = $this->createUser();
        $existingPassword = Hash::make('legacy-password');

        $existing = User::query()->create([
            'name' => 'Legacy User',
            'email' => 'legacy-user@example.com',
            'password' => $existingPassword,
            'role' => 'rep',
            'active' => false,
        ]);

        $run = $this->previewImport($admin, 'users', [
            ['Updated Legacy User', 'legacy-user@example.com', 'manager', 'yes'],
            ['Fresh CRM User', 'fresh-user@example.com', 'rep', 'yes'],
        ]);

        $this->processRun($run);

        $existing->refresh();
        $fresh = User::query()->where('email', 'fresh-user@example.com')->firstOrFail();

        $this->assertSame('manager', $existing->role);
        $this->assertTrue($existing->active);
        $this->assertSame($existingPassword, $existing->getAuthPassword());

        $this->assertSame('rep', $fresh->role);
        $this->assertTrue($fresh->active);
        $this->assertNotSame('', $fresh->getAuthPassword());
        $this->assertFalse(Hash::check('legacy-password', $fresh->getAuthPassword()));

        $run->refresh();
        $this->assertSame('completed', $run->status);
        $this->assertSame(1, $run->created_count);
        $this->assertSame(1, $run->updated_count);

        $this->actingAs($admin)
            ->get(route('crm.settings.imports.runs.show', $run))
            ->assertOk()
            ->assertSee('fresh-user@example.com')
            ->assertSee('Completed');

        $download = $this->actingAs($admin)
            ->get(route('crm.settings.imports.runs.passwords.download', $run))
            ->assertOk();

        $passwordRows = $this->streamedSpreadsheetRows($download->streamedContent());

        $this->assertSame(['name', 'email', 'role', 'temporary_password'], $passwordRows[0]);
        $this->assertSame('fresh-user@example.com', $passwordRows[1][1]);
        $this->assertNotSame('', $passwordRows[1][3]);
        $this->assertNotNull($run->fresh()->passwords_downloaded_at);

        $this->actingAs($admin)
            ->get(route('crm.settings.imports.runs.passwords.download', $run))
            ->assertRedirect(route('crm.settings.imports.runs.show', $run))
            ->assertSessionHas('crm_error');
    }

    public function test_user_import_populates_extended_profile_fields_and_custom_filters(): void
    {
        Storage::fake('documents');
        $admin = $this->createUser([
            'email' => 'import-admin-profile@example.com',
            'role' => 'admin',
        ]);

        $run = $this->previewImport($admin, 'users', [
            [
                'Imported Profile User',
                'imported-profile@example.com',
                'rep',
                'yes',
                '14/02/1990',
                'female',
                'Botswana',
                'ID-9001',
                '+267 7111 9999',
                'active',
                'Student Services',
                'Coordinator',
                $admin->email,
                'PAY-9090',
                '01/03/2024',
                'Priority|Regional',
            ],
        ]);

        $this->processRun($run);

        $user = User::query()->where('email', 'imported-profile@example.com')->firstOrFail();
        $department = CrmUserDepartment::query()->where('name', 'Student Services')->firstOrFail();
        $position = CrmUserPosition::query()->where('name', 'Coordinator')->firstOrFail();
        $priorityFilter = CrmUserFilter::query()->where('name', 'Priority')->firstOrFail();
        $regionalFilter = CrmUserFilter::query()->where('name', 'Regional')->firstOrFail();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'gender' => 'female',
            'nationality' => 'Botswana',
            'id_number' => 'ID-9001',
            'phone' => '+267 7111 9999',
            'employment_status' => 'active',
            'department_id' => $department->id,
            'position_id' => $position->id,
            'reports_to_user_id' => $admin->id,
            'personal_payroll_number' => 'PAY-9090',
        ]);
        $this->assertSame('1990-02-14', optional($user->date_of_birth)->toDateString());
        $this->assertSame('2024-03-01', optional($user->date_of_appointment)->toDateString());
        $this->assertDatabaseHas('crm_user_filter_user', [
            'user_id' => $user->id,
            'crm_user_filter_id' => $priorityFilter->id,
        ]);
        $this->assertDatabaseHas('crm_user_filter_user', [
            'user_id' => $user->id,
            'crm_user_filter_id' => $regionalFilter->id,
        ]);
    }

    public function test_lead_import_upserts_by_reference_and_preserves_converted_state(): void
    {
        Storage::fake('documents');
        $admin = $this->createUser();

        $lead = Lead::query()->create([
            'owner_id' => $admin->id,
            'import_reference' => 'LEAD-CONVERTED',
            'company_name' => 'Converted Lead',
            'status' => 'converted',
            'converted_at' => now()->subDay(),
            'notes' => 'Existing converted lead',
        ]);

        $originalConvertedAt = $lead->converted_at;

        $run = $this->previewImport($admin, 'leads', [
            ['LEAD-CONVERTED', $admin->email, 'Converted Lead Updated', 'Education', '', 'converted@example.com', '+267 111 2222', 'Botswana', 'lost', 'Should stay converted'],
            ['LEAD-NEW', $admin->email, 'New Pipeline Lead', 'Education', '', 'new@example.com', '+267 333 4444', 'Botswana', 'qualified', 'New import'],
        ]);

        $this->processRun($run);

        $lead->refresh();
        $newLead = Lead::query()->where('import_reference', 'LEAD-NEW')->firstOrFail();

        $this->assertSame('Converted Lead Updated', $lead->company_name);
        $this->assertSame('converted', $lead->status);
        $this->assertTrue($lead->converted_at?->eq($originalConvertedAt));
        $this->assertSame('qualified', $newLead->status);
        $this->assertNull($newLead->converted_at);
    }

    public function test_contact_import_updates_by_reference_links_entities_and_keeps_one_primary_contact(): void
    {
        Storage::fake('documents');
        $admin = $this->createUser();

        $openLead = Lead::query()->create([
            'owner_id' => $admin->id,
            'import_reference' => 'LEAD-OPEN',
            'company_name' => 'Open Lead',
            'status' => 'active',
        ]);

        $convertedLead = Lead::query()->create([
            'owner_id' => $admin->id,
            'import_reference' => 'LEAD-CONVERTED',
            'company_name' => 'Converted Lead',
            'status' => 'converted',
            'converted_at' => now(),
        ]);

        $customer = Customer::query()->create([
            'owner_id' => $admin->id,
            'lead_id' => $convertedLead->id,
            'company_name' => 'Converted Customer',
            'status' => 'active',
        ]);

        $oldPrimary = Contact::query()->create([
            'owner_id' => $admin->id,
            'lead_id' => $openLead->id,
            'name' => 'Old Primary Contact',
            'is_primary' => true,
        ]);

        $updatableContact = Contact::query()->create([
            'owner_id' => $admin->id,
            'import_reference' => 'CONT-OPEN',
            'lead_id' => $openLead->id,
            'name' => 'Open Contact',
            'is_primary' => false,
        ]);

        $run = $this->previewImport($admin, 'contacts', [
            ['CONT-OPEN', 'LEAD-OPEN', 'Updated Open Contact', 'Registrar', 'open@example.com', '+267 444 5555', 'yes', '', 'Promoted to primary'],
            ['CONT-CUSTOMER', 'LEAD-CONVERTED', 'Customer Contact', 'Operations Manager', 'customer@example.com', '+267 777 8888', 'yes', '', 'Linked to converted customer'],
        ]);

        $this->processRun($run);

        $oldPrimary->refresh();
        $updatableContact->refresh();
        $customerContact = Contact::query()->where('import_reference', 'CONT-CUSTOMER')->firstOrFail();

        $this->assertFalse($oldPrimary->is_primary);
        $this->assertTrue($updatableContact->is_primary);
        $this->assertSame('Updated Open Contact', $updatableContact->name);
        $this->assertSame($openLead->id, $updatableContact->lead_id);
        $this->assertNull($updatableContact->customer_id);

        $this->assertNull($customerContact->lead_id);
        $this->assertSame($customer->id, $customerContact->customer_id);
        $this->assertTrue($customerContact->is_primary);
    }

    public function test_failure_report_can_be_downloaded_for_completed_run(): void
    {
        Storage::fake('documents');
        $admin = $this->createUser();

        $run = $this->previewImport($admin, 'users', [
            ['Valid User', 'valid-user@example.com', 'rep', 'yes'],
            ['Broken User', 'broken-user@example.com', 'bad-role', 'yes'],
        ]);

        $this->processRun($run);

        $download = $this->actingAs($admin)
            ->get(route('crm.settings.imports.runs.failures.download', $run))
            ->assertOk();

        $rows = $this->binarySpreadsheetRows($download->baseResponse->getFile()->getPathname());

        $this->assertSame(['row_number', 'normalized_key', 'action', 'errors'], $rows[0]);
        $this->assertSame('broken-user@example.com', $rows[1][1]);
        $this->assertStringContainsString('role', strtolower((string) $rows[1][3]));
        $this->assertSame('completed_with_errors', $run->fresh()->status);
    }

    private function previewImport(User $admin, string $entity, array $rows): CrmImportRun
    {
        $this->actingAs($admin)
            ->post(route('crm.settings.imports.preview'), [
                'entity' => $entity,
                'file' => $this->makeSpreadsheetUpload(
                    'crm-' . $entity . '-import.xlsx',
                    config('heritage_crm.imports.entities.' . $entity . '.headings'),
                    $rows
                ),
            ])
            ->assertRedirect();

        return CrmImportRun::query()->latest('id')->firstOrFail();
    }

    private function processRun(CrmImportRun $run): CrmImportRun
    {
        $service = app(CrmImportRunService::class);
        $service->process($run->fresh());

        return $run->fresh();
    }

    private function downloadedHeadings(User $admin, string $entity): array
    {
        $response = $this->actingAs($admin)
            ->get(route('crm.settings.imports.templates.download', $entity))
            ->assertOk();

        return $this->binarySpreadsheetRows($response->baseResponse->getFile()->getPathname())[0];
    }

    private function makeSpreadsheetUpload(string $filename, array $headings, array $rows, ?callable $mutateSheet = null): UploadedFile
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray($headings, null, 'A1');

        foreach ($rows as $index => $row) {
            $sheet->fromArray($row, null, 'A' . ($index + 2));
        }

        if ($mutateSheet) {
            $mutateSheet($sheet);
        }

        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION)) === 'xls' ? 'xls' : 'xlsx';
        $temporaryPath = tempnam(sys_get_temp_dir(), 'crm-import-');
        $finalPath = $temporaryPath . '.' . $extension;
        @rename($temporaryPath, $finalPath);

        if ($extension === 'xls') {
            (new Xls($spreadsheet))->save($finalPath);
        } else {
            (new Xlsx($spreadsheet))->save($finalPath);
        }

        return new UploadedFile(
            $finalPath,
            $filename,
            $extension === 'xls'
                ? 'application/vnd.ms-excel'
                : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );
    }

    private function binarySpreadsheetRows(string $path): array
    {
        return IOFactory::load($path)
            ->getActiveSheet()
            ->toArray('', false, false, false);
    }

    private function streamedSpreadsheetRows(string $content): array
    {
        $temporaryPath = tempnam(sys_get_temp_dir(), 'crm-export-');
        $finalPath = $temporaryPath . '.xlsx';
        @rename($temporaryPath, $finalPath);
        file_put_contents($finalPath, $content);

        return $this->binarySpreadsheetRows($finalPath);
    }

    private function createUser(array $attributes = []): User
    {
        return User::query()->create(array_merge([
            'name' => 'CRM Admin',
            'email' => 'admin-' . uniqid('', true) . '@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'active' => true,
        ], $attributes));
    }
}
