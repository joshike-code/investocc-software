<?php
namespace Core;

require_once __DIR__ . '/../core/SanitizationService.php';

class InputData
{
    private $data = [];

    public function add($key, $value, $sanitizeType = 'string')
    {
        switch ($sanitizeType) {
            case 'url':
                $this->data[$key] = SanitizationService::sanitizeUrl($value ?? '');
                break;
            case 'string':
                $this->data[$key] = SanitizationService::sanitizeString($value ?? '');
                break;
            case 'int':
                $this->data[$key] = (int) $value ?? 0;
                break;
            case 'float':
                $this->data[$key] = (float) $value ?? 0;
                break;
            default:
                $this->data[$key] = $value;
                break;
        }
    }

    public function get($key)
    {
        return $this->data[$key] ?? null;
    }

    // Retrieve all sanitized data
    public function getAll()
    {
        return $this->data;
    }
}