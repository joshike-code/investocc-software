<?php
namespace Core;

class SanitizationService
{
    public static function sanitize(array $data): array
    {
        $sanitized = [];

        foreach ($data as $key => $value) {
            if (is_string($value)) {
                // Trim and clean strings
                $value = trim($value);
                $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
                $value = strip_tags($value);
                $sanitized[$key] = $value;

            } elseif (is_numeric($value)) {
                // Numeric values passed as-is
                $sanitized[$key] = $value;

            } elseif (is_array($value)) {
                // Recursively sanitize arrays
                $sanitized[$key] = self::sanitize($value);

            } else {
                // Fallback for bools/nulls/objects
                $sanitized[$key] = $value;
            }
        }

        return $sanitized;
    }

    public static function sanitizeParam(string $value)
    {
        // Trim and clean strings
        $value = trim($value);
        $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        $value = strip_tags($value);
        $sanitizedValue = $value;

        return $sanitizedValue;
    }

    public static function sanitizeUrl(string $value)
    {
        $url = preg_replace('/^(https?:\/\/)+/', '', $value);
        return 'https://' . $url;
    }
}