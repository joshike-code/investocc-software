<?php
require_once __DIR__ . '/../services/PermissionService.php';

class Validator
{
    public static function validate(array $data, array $rules): array
    {
        $errors = [];

        foreach ($rules as $field => $ruleSet) {
            $rulesArray = explode('|', $ruleSet);
            $value = $data[$field] ?? null;

            foreach ($rulesArray as $rule) {
                // Handle rules with parameters (e.g. min:3)
                [$ruleName, $param] = array_pad(explode(':', $rule, 2), 2, null);

                switch ($ruleName) {
                    case 'required':
                        if (is_null($value) || $value === '') {
                            $errors[$field][] = 'This field is required.';
                        }
                        break;

                    case 'integer':
                        if (!filter_var($value, FILTER_VALIDATE_INT) || $value != (int) $value) {
                            $errors[$field][] = 'Must be an integer.';
                        }
                        break;

                    case 'float':
                        if (!filter_var($value, FILTER_VALIDATE_FLOAT)) {
                            $errors[$field][] = 'Must be a float.';
                        }
                        break;

                    case 'email':
                        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                            $errors[$field][] = 'Invalid email format.';
                        }
                        break;

                    case 'url':
                        if (!filter_var($value, FILTER_VALIDATE_URL)) {
                            $errors[$field][] = 'Invalid URL.';
                        }
                        break;

                    case 'min':
                        if (strlen((string)$value) < (int)$param) {
                            $errors[$field][] = "Minimum length is $param.";
                        }
                        break;

                    case 'max':
                        if (strlen((string)$value) > (int)$param) {
                            $errors[$field][] = "Maximum length is $param.";
                        }
                        break;

                    case 'regex':
                        if (!preg_match($param, (string)$value)) {
                            $errors[$field][] = "Invalid format.";
                        }
                        break;

                        //Omo not that important jare
                    case 'string':
                        if (!is_string($value)) {
                            $errors[$field][] = 'Must be a string.';
                        }
                        break;

                    case 'password':
                        if (!preg_match('/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}$/', (string)$value)) {
                            $errors[$field][] = "Invalid password format.";
                        }
                        break;

                    case 'permission':
                        if (!is_array($value) || !PermissionService::validatePermissions($value)) {
                            $errors[$field][] = "Invalid permission format.";
                        }
                        break;
                }
            }
        }

        return $errors;
    }
}