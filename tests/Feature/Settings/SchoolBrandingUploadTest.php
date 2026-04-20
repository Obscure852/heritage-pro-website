<?php

namespace Tests\Feature\Settings;

use App\Models\SchoolSetup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SchoolBrandingUploadTest extends TestCase
{
    use RefreshDatabase;

    private string $defaultDisk;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware();

        $this->defaultDisk = (string) config('filesystems.default', 'local');
        Storage::fake($this->defaultDisk);

        SchoolSetup::query()->forceDelete();

        SchoolSetup::query()->create([
            'school_name' => 'Merementsi Junior Secondary School',
            'type' => 'Junior',
        ]);
    }

    public function test_logo_upload_accepts_exact_500_square_image(): void
    {
        $response = $this->from(route('setup.school-setup'))->post(route('setup.upload-logo'), [
            'logo' => UploadedFile::fake()->image('logo.png', 500, 500),
        ]);

        $response->assertRedirect(route('setup.school-setup'));
        $response->assertSessionHas('message', 'Logo uploaded successfully!');

        $setup = SchoolSetup::query()->firstOrFail();

        $this->assertNotNull($setup->logo_path);
        $this->assertStringContainsString('/storage/branding/', $setup->logo_path);

        Storage::disk($this->defaultDisk)->assertExists(
            str_replace('/storage/', 'public/', $setup->logo_path)
        );
    }

    public function test_logo_upload_rejects_non_standard_dimensions(): void
    {
        $response = $this->from(route('setup.school-setup'))->post(route('setup.upload-logo'), [
            'logo' => UploadedFile::fake()->image('logo.png', 520, 500),
        ]);

        $response->assertRedirect(route('setup.school-setup'));
        $response->assertSessionHasErrors([
            'logo' => 'The logo must be exactly 500x500 pixels.',
        ]);

        $this->assertNull(SchoolSetup::query()->firstOrFail()->logo_path);
    }

    public function test_logo_upload_returns_json_success_for_ajax_fallback(): void
    {
        $response = $this->post(
            route('setup.upload-logo'),
            ['logo' => UploadedFile::fake()->image('logo.png', 500, 500)],
            [
                'Accept' => 'application/json',
                'X-Requested-With' => 'XMLHttpRequest',
            ]
        );

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Logo uploaded successfully!',
            ]);
    }

    public function test_login_image_upload_accepts_exact_1000_by_600_image(): void
    {
        $response = $this->from(route('setup.school-setup'))->post(route('setup.upload-login-image'), [
            'login_image' => UploadedFile::fake()->image('login.jpg', 1000, 600),
        ]);

        $response->assertRedirect(route('setup.school-setup'));
        $response->assertSessionHas('message', 'Login page image uploaded successfully!');

        $setup = SchoolSetup::query()->firstOrFail();

        $this->assertNotNull($setup->login_image_path);
        $this->assertTrue((bool) $setup->use_custom_login_image);
        $this->assertStringContainsString('/storage/branding/login/', $setup->login_image_path);

        Storage::disk($this->defaultDisk)->assertExists(
            str_replace('/storage/', 'public/', $setup->login_image_path)
        );
    }

    public function test_login_image_upload_rejects_non_standard_dimensions(): void
    {
        $response = $this->from(route('setup.school-setup'))->post(route('setup.upload-login-image'), [
            'login_image' => UploadedFile::fake()->image('login.jpg', 1200, 700),
        ]);

        $response->assertRedirect(route('setup.school-setup'));
        $response->assertSessionHasErrors([
            'login_image' => 'The image must be exactly 1000x600 pixels.',
        ]);

        $setup = SchoolSetup::query()->firstOrFail();

        $this->assertNull($setup->login_image_path);
        $this->assertFalse((bool) $setup->use_custom_login_image);
    }
}
