<?php

namespace App\Services\Crm;

use App\Models\CrmUserQualification;
use App\Models\CrmUserSignature;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CrmUserMediaService
{
    public function storeAvatarFromCroppedImage(?string $croppedImage, ?string $existingPath = null): ?string
    {
        return $this->storePublicSquareImageFromCroppedImage(
            $croppedImage,
            'crm/users/avatars',
            $existingPath,
            'avatar_cropped_image'
        );
    }

    public function storeBrandingImageFromCroppedImage(
        ?string $croppedImage,
        string $directory,
        ?string $existingPath = null,
        string $errorKey = 'cropped_image'
    ): ?string {
        return $this->storePublicSquareImageFromCroppedImage(
            $croppedImage,
            'crm/settings/' . trim($directory, '/'),
            $existingPath,
            $errorKey
        );
    }

    public function deleteAvatar(?string $path): void
    {
        $this->deletePublicImage($path);
    }

    public function deletePublicImage(?string $path): void
    {
        if (is_string($path) && $path !== '') {
            Storage::disk('public')->delete($path);
        }
    }

    public function storeQualificationAttachments(CrmUserQualification $qualification, array $files, ?User $actor = null): void
    {
        foreach ($files as $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }

            $storedPath = $file->storeAs(
                'crm/users/qualifications/' . $qualification->user_id . '/' . $qualification->id,
                Str::uuid() . '.' . ($file->getClientOriginalExtension() ?: 'file'),
                'documents'
            );

            $qualification->attachments()->create([
                'uploaded_by_id' => $actor?->id,
                'disk' => 'documents',
                'path' => $storedPath,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'extension' => $file->getClientOriginalExtension(),
                'size' => $file->getSize() ?: 0,
            ]);
        }
    }

    public function storeSignature(User $user, UploadedFile $file, string $label, ?User $actor = null): CrmUserSignature
    {
        $storedPath = $file->storeAs(
            'crm/users/signatures/' . $user->id,
            Str::uuid() . '.' . ($file->getClientOriginalExtension() ?: 'file'),
            'documents'
        );

        return $user->signatures()->create([
            'uploaded_by_id' => $actor?->id,
            'label' => $label,
            'disk' => 'documents',
            'path' => $storedPath,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'extension' => $file->getClientOriginalExtension(),
            'size' => $file->getSize() ?: 0,
            'is_default' => ! $user->signatures()->exists(),
        ]);
    }

    public function deleteDocument(string $disk, string $path): void
    {
        Storage::disk($disk)->delete($path);
    }

    public function absoluteDocumentPath(string $disk, string $path): string
    {
        return Storage::disk($disk)->path($path);
    }

    private function storePublicSquareImageFromCroppedImage(
        ?string $croppedImage,
        string $directory,
        ?string $existingPath,
        string $errorKey
    ): ?string {
        if (! is_string($croppedImage) || trim($croppedImage) === '') {
            return $existingPath;
        }

        if (! preg_match('/^data:image\/[a-zA-Z0-9.+-]+;base64,/', $croppedImage)) {
            throw ValidationException::withMessages([
                $errorKey => 'The cropped image payload is invalid.',
            ]);
        }

        $binary = base64_decode(substr($croppedImage, strpos($croppedImage, ',') + 1), true);

        if ($binary === false) {
            throw ValidationException::withMessages([
                $errorKey => 'The cropped image could not be decoded.',
            ]);
        }

        $image = @imagecreatefromstring($binary);

        if ($image === false) {
            throw ValidationException::withMessages([
                $errorKey => 'The cropped image is not a valid image.',
            ]);
        }

        $sourceWidth = imagesx($image);
        $sourceHeight = imagesy($image);
        $sourceSize = min($sourceWidth, $sourceHeight);
        $sourceX = (int) floor(($sourceWidth - $sourceSize) / 2);
        $sourceY = (int) floor(($sourceHeight - $sourceSize) / 2);

        $canvas = imagecreatetruecolor(512, 512);
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        $transparent = imagecolorallocatealpha($canvas, 255, 255, 255, 127);
        imagefill($canvas, 0, 0, $transparent);

        imagecopyresampled(
            $canvas,
            $image,
            0,
            0,
            $sourceX,
            $sourceY,
            512,
            512,
            $sourceSize,
            $sourceSize
        );

        ob_start();
        imagepng($canvas, null, 9);
        $output = ob_get_clean();

        imagedestroy($image);
        imagedestroy($canvas);

        if (! is_string($output) || $output === '') {
            throw ValidationException::withMessages([
                $errorKey => 'The cropped image could not be processed.',
            ]);
        }

        $path = trim($directory, '/') . '/' . Str::uuid() . '.png';
        Storage::disk('public')->put($path, $output, ['visibility' => 'public']);

        if (is_string($existingPath) && $existingPath !== '' && $existingPath !== $path) {
            Storage::disk('public')->delete($existingPath);
        }

        return $path;
    }
}
