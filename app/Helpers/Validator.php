<?php

namespace App\Helpers;

class Validator
{
    private $data;
    private $rules;
    private $errors = [];

    public static function make(array $data, array $rules): self
    {
        return new self($data, $rules);
    }

    private function __construct(array $data, array $rules)
    {
        $this->data = $data;
        $this->rules = $rules;
    }

    public function validate(): bool
    {
        foreach ($this->rules as $field => $fieldRules) {
            $rules = explode('|', $fieldRules);
            $value = $this->data[$field] ?? null;

            foreach ($rules as $rule) {
                $this->checkRule($field, $value, $rule);
            }
        }

        if (!empty($this->errors)) {
            throw new ValidationException($this->errors);
        }

        return true;
    }

    private function checkRule(string $field, mixed $value, string $rule): void
    {
        if (str_contains($rule, ':')) {
            [$rule, $param] = explode(':', $rule);
        } else {
            $param = null;
        }

        match ($rule) {
            'required' => $value === null || $value === '' && $this->addError($field, "$field is required"),
            'email' => $value && !filter_var($value, FILTER_VALIDATE_EMAIL) && $this->addError($field, "$field must be a valid email"),
            'min' => $value && strlen($value) < $param && $this->addError($field, "$field must be at least $param characters"),
            'max' => $value && strlen($value) > $param && $this->addError($field, "$field must not exceed $param characters"),
            'string' => $value && !is_string($value) && $this->addError($field, "$field must be a string"),
            'confirmed' => $value !== ($this->data[$field . '_confirmation'] ?? null) && $this->addError($field, "$field must match confirmation"),
            'in' => $value && !in_array($value, explode(',', $param)) && $this->addError($field, "$field has invalid value"),
            default => null,
        };
    }

    private function addError(string $field, string $message): void
    {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = $message;
        }
    }

    public function errors(): array
    {
        return $this->errors;
    }
}

class ValidationException extends \Exception
{
    private $errors;

    public function __construct(array $errors)
    {
        $this->errors = $errors;
        parent::__construct('Validation failed');
    }

    public function errors(): array
    {
        return $this->errors;
    }
}
