<?php

namespace CleaniqueCoders\LaravelOrganization\Concerns;

use Illuminate\Support\Facades\Validator;

trait InteractsWithOrganizationSettings
{
    /**
     * Apply default settings from configuration.
     */
    public function applyDefaultSettings(): void
    {
        $defaultSettings = config('organization.default-settings', []);
        $currentSettings = $this->settings ?? [];

        // Merge current settings with defaults, keeping existing values
        $this->settings = array_replace_recursive($defaultSettings, $currentSettings);
    }

    /**
     * Get default settings from configuration.
     */
    public static function getDefaultSettings(): array
    {
        return config('organization.default-settings', []);
    }

    /**
     * Validate settings according to validation rules.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function validateSettings(): void
    {
        // Always get fresh config to ensure test changes are reflected
        $rules = config('organization.validation_rules', []);

        // Skip validation if no rules
        if (empty($rules)) {
            return;
        }

        // Always validate if we have rules, using empty array if settings is null
        $settingsToValidate = $this->settings ?? [];

        $validator = Validator::make($settingsToValidate, $rules);

        if ($validator->fails()) {
            throw new \Illuminate\Validation\ValidationException($validator);
        }
    }

    /**
     * Reset settings to defaults.
     */
    public function resetSettingsToDefaults(): void
    {
        $this->settings = static::getDefaultSettings();
    }

    /**
     * Merge additional settings with current settings.
     */
    public function mergeSettings(array $newSettings): void
    {
        $currentSettings = $this->settings ?? [];
        $this->settings = array_replace_recursive($currentSettings, $newSettings);
    }

    /**
     * Get a setting value.
     */
    public function getSetting(string $key, $default = null)
    {
        return data_get($this->settings, $key, $default);
    }

    /**
     * Set a setting value.
     */
    public function setSetting(string $key, $value): void
    {
        $settings = $this->settings ?? [];
        data_set($settings, $key, $value);
        $this->settings = $settings;
    }

    /**
     * Check if a setting exists.
     */
    public function hasSetting(string $key): bool
    {
        return data_get($this->settings, $key) !== null;
    }

    /**
     * Remove a setting.
     */
    public function removeSetting(string $key): void
    {
        $settings = $this->settings ?? [];
        data_forget($settings, $key);
        $this->settings = $settings;
    }
}
