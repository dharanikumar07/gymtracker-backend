<?php

namespace App\Helpers;

use App\Models\Setting;

class SettingsManager
{
    protected $type;
    protected $user_uuid;
    protected $settings;

    /**
     * SettingsManager constructor.
     * 
     * @param string $type
     * @param string $user_uuid
     */
    public function __construct(string $type, string $user_uuid)
    {
        $this->type = $type;
        $this->user_uuid = $user_uuid;
        $this->settings = [];
    }

    /**
     * Fetch settings from database and populate the property.
     * 
     * @return $this
     */
    public function get()
    {
        $setting = Setting::where('type', $this->type)
            ->where('user_uuid', $this->user_uuid)
            ->first();

        $this->settings = $setting ? ($setting->data ?? []) : [];

        return $this;
    }

    /**
     * Save settings to database.
     * 
     * @param array $settings
     * @return $this
     */
    public function save(array $settings)
    {
        $mergedSettings = $this->mergeWithDefault($settings);

        $setting = Setting::updateOrCreate(
            ['user_uuid' => $this->user_uuid, 'type' => $this->type],
            ['data' => $mergedSettings]
        );

        $this->settings = $setting->data;

        return $this;
    }

    /**
     * Merge provided settings with default values based on type.
     * 
     * @param array $settings
     * @return array
     */
    public function mergeWithDefault(array $settings)
    {
        if ($this->type === 'notification') {
            return [
                'workout' => [
                    'is_enabled' => (bool) ($settings['workout']['is_enabled'] ?? false),
                    'reminder_count' => (int) ($settings['workout']['reminder_count'] ?? 2),
                    'times' => $settings['workout']['times'] ?? ['10:00', '21:00'],
                ],
                'expense' => [
                    'is_enabled' => (bool) ($settings['expense']['is_enabled'] ?? false),
                    'reminder_count' => (int) ($settings['expense']['reminder_count'] ?? 2),
                    'times' => $settings['expense']['times'] ?? ['10:00', '21:00'],
                ],
            ];
        }

        return $settings;
    }

    /**
     * Get settings data.
     * 
     * @return array
     */
    public function getSettings()
    {
        return $this->settings;
    }
}
