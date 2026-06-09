<?php

declare(strict_types=1);

namespace App\Core;

class Validator
{
    private array $data;
    private array $rules;
    private array $errors = [];
    private array $messages = [];

    public function __construct(array $data, array $rules, array $messages = [])
    {
        $this->data = $data;
        $this->rules = $rules;
        $this->messages = $messages;
    }

    public function validate(): bool
    {
        foreach ($this->rules as $field => $ruleString) {
            $rules = explode('|', $ruleString);
            $value = $this->data[$field] ?? null;

            foreach ($rules as $rule) {
                $params = [];
                if (str_contains($rule, ':')) {
                    [$rule, $paramStr] = explode(':', $rule, 2);
                    $params = explode(',', $paramStr);
                }

                $method = 'validate' . ucfirst($rule);
                if (method_exists($this, $method)) {
                    if (!$this->$method($field, $value, $params)) {
                        break;
                    }
                }
            }
        }

        return empty($this->errors);
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function firstError(): ?string
    {
        return !empty($this->errors) ? reset($this->errors) : null;
    }

    private function addError(string $field, string $message): void
    {
        $this->errors[$field] = $this->messages[$field] ?? $message;
    }

    private function validateRequired(string $field, mixed $value): bool
    {
        if ($value === null || $value === '' || (is_array($value) && empty($value))) {
            $this->addError($field, "حقل {$field} مطلوب");
            return false;
        }
        return true;
    }

    private function validateEmail(string $field, mixed $value): bool
    {
        if ($value && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->addError($field, 'البريد الإلكتروني غير صالح');
            return false;
        }
        return true;
    }

    private function validateMin(string $field, mixed $value, array $params): bool
    {
        $min = (int) ($params[0] ?? 0);
        if ($value !== null && mb_strlen((string) $value) < $min) {
            $this->addError($field, "يجب أن يكون {$field} {$min} أحرف على الأقل");
            return false;
        }
        return true;
    }

    private function validateMax(string $field, mixed $value, array $params): bool
    {
        $max = (int) ($params[0] ?? 0);
        if ($value !== null && mb_strlen((string) $value) > $max) {
            $this->addError($field, "يجب ألا يتجاوز {$field} {$max} حرف");
            return false;
        }
        return true;
    }

    private function validateNumeric(string $field, mixed $value): bool
    {
        if ($value !== null && $value !== '' && !is_numeric($value)) {
            $this->addError($field, 'يجب أن يكون رقماً');
            return false;
        }
        return true;
    }

    private function validateDate(string $field, mixed $value): bool
    {
        if ($value && !preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $value)) {
            $this->addError($field, 'تاريخ غير صالح');
            return false;
        }
        return true;
    }

    private function validateIn(string $field, mixed $value, array $params): bool
    {
        if ($value !== null && $value !== '' && !in_array($value, $params, true)) {
            $this->addError($field, 'قيمة غير صالحة');
            return false;
        }
        return true;
    }

    private function validateNullable(string $field, mixed $value): bool
    {
        return true;
    }

    public static function sanitize(string $value): string
    {
        return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
    }

    public static function sanitizeArray(array $data): array
    {
        $sanitized = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $sanitized[$key] = self::sanitizeArray($value);
            } elseif (is_string($value)) {
                $sanitized[$key] = trim($value);
            } else {
                $sanitized[$key] = $value;
            }
        }
        return $sanitized;
    }
}
