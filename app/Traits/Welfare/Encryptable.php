<?php

namespace App\Traits\Welfare;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

/**
 * Provides automatic encryption/decryption for sensitive model attributes.
 *
 * Use this trait for Level 4 (Highly Confidential) fields like
 * counseling notes and safeguarding details.
 *
 * Define $encryptable array in your model to specify which fields to encrypt.
 */
trait Encryptable
{
    /**
     * Get an attribute from the model, decrypting if necessary.
     *
     * @param string $key
     * @return mixed
     */
    public function getAttribute($key)
    {
        $value = parent::getAttribute($key);

        if ($this->isEncryptableAttribute($key) && !is_null($value) && !empty($value)) {
            return $this->decryptValue($value, $key);
        }

        return $value;
    }

    /**
     * Set an attribute on the model, encrypting if necessary.
     *
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    public function setAttribute($key, $value)
    {
        if ($this->isEncryptableAttribute($key) && !is_null($value) && !empty($value)) {
            $value = $this->encryptValue($value, $key);
        }

        return parent::setAttribute($key, $value);
    }

    /**
     * Check if an attribute should be encrypted.
     *
     * @param string $key
     * @return bool
     */
    protected function isEncryptableAttribute(string $key): bool
    {
        return in_array($key, $this->getEncryptableAttributes(), true);
    }

    /**
     * Get the list of encryptable attributes.
     * Override in model to customize.
     *
     * @return array
     */
    protected function getEncryptableAttributes(): array
    {
        return $this->encryptable ?? [];
    }

    /**
     * Encrypt a value with error handling.
     *
     * @param mixed $value
     * @param string $key Attribute name for logging
     * @return string
     */
    protected function encryptValue($value, string $key): string
    {
        try {
            // Only encrypt strings
            if (!is_string($value)) {
                $value = json_encode($value);
            }

            return Crypt::encryptString($value);
        } catch (\Exception $e) {
            Log::error('Welfare encryption failed', [
                'model' => static::class,
                'attribute' => $key,
                'error' => $e->getMessage(),
            ]);

            // Return original value if encryption fails
            // This ensures data isn't lost, but logs the security concern
            return $value;
        }
    }

    /**
     * Decrypt a value with error handling.
     *
     * @param mixed $value
     * @param string $key Attribute name for logging
     * @return mixed
     */
    protected function decryptValue($value, string $key)
    {
        // Skip if value doesn't look encrypted (for backwards compatibility)
        if (!$this->looksEncrypted($value)) {
            return $value;
        }

        try {
            $decrypted = Crypt::decryptString($value);

            // Try to decode JSON if it was encoded
            $decoded = json_decode($decrypted, true);

            return (json_last_error() === JSON_ERROR_NONE) ? $decoded : $decrypted;
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            // Value might not be encrypted (legacy data)
            Log::warning('Welfare decryption failed - possibly unencrypted legacy data', [
                'model' => static::class,
                'attribute' => $key,
            ]);

            return $value;
        } catch (\Exception $e) {
            Log::error('Welfare decryption error', [
                'model' => static::class,
                'attribute' => $key,
                'error' => $e->getMessage(),
            ]);

            return $value;
        }
    }

    /**
     * Check if a value looks like it's encrypted.
     * Laravel encrypted strings have a specific format.
     *
     * @param mixed $value
     * @return bool
     */
    protected function looksEncrypted($value): bool
    {
        if (!is_string($value)) {
            return false;
        }

        // Laravel encrypted strings are base64 encoded JSON
        // They typically start with 'eyJ' (base64 of '{"')
        if (strlen($value) < 50) {
            return false;
        }

        // Try to detect Laravel's encryption format
        $decoded = base64_decode($value, true);

        if ($decoded === false) {
            return false;
        }

        $json = json_decode($decoded, true);

        return is_array($json) && isset($json['iv']) && isset($json['value']);
    }

    /**
     * Get the raw encrypted value of an attribute.
     * Useful for debugging or direct database operations.
     *
     * @param string $key
     * @return mixed
     */
    public function getRawEncrypted(string $key)
    {
        return parent::getAttribute($key);
    }

    /**
     * Convert the model to an array, with decrypted values.
     * Encrypted values are decrypted for display.
     *
     * @return array
     */
    public function attributesToArray(): array
    {
        $attributes = parent::attributesToArray();

        foreach ($this->getEncryptableAttributes() as $key) {
            if (isset($attributes[$key])) {
                $attributes[$key] = $this->getAttribute($key);
            }
        }

        return $attributes;
    }

    /**
     * Get attributes for safe display (masks encrypted fields).
     * Use this when showing data to users without full access.
     *
     * @return array
     */
    public function getAttributesForLimitedAccess(): array
    {
        $attributes = parent::attributesToArray();

        foreach ($this->getEncryptableAttributes() as $key) {
            if (isset($attributes[$key])) {
                $attributes[$key] = '[CONFIDENTIAL]';
            }
        }

        return $attributes;
    }

    /**
     * Check if the model has any encrypted data.
     *
     * @return bool
     */
    public function hasEncryptedData(): bool
    {
        foreach ($this->getEncryptableAttributes() as $key) {
            $value = parent::getAttribute($key);
            if (!is_null($value) && !empty($value)) {
                return true;
            }
        }

        return false;
    }
}
