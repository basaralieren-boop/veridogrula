<?php

namespace VeriDogrula;

use PDO;
use VeriDogrula\Exceptions\ValidationException;
use VeriDogrula\Exceptions\RuleNotFoundException;
use VeriDogrula\Exceptions\DatabaseException;
use VeriDogrula\Exceptions\InvalidLanguageException;

class Validator
{
    private array $errors = [];
    private array $validated_data = [];
    private array $rules = [];
    private array $custom_rules = [];
    private string $language = 'tr';
    private array $messages = [];
    private ?PDO $pdo = null;
    private array $data = [];

    public function __construct(array $custom_messages = [])
    {
        $this->loadLanguage($this->language);
        $this->messages = array_merge($this->messages, $custom_messages);
    }

    /**
     * Ana doğrulama metodu
     */
    public function validate(array $data, array $rules): bool
    {
        $this->data = $data;
        $this->rules = $rules;
        $this->errors = [];
        $this->validated_data = [];

        foreach ($rules as $field => $rule_string) {
            $rules_list = $this->parseRules($rule_string);
            $this->validateField($field, $rules_list);
        }

        return empty($this->errors);
    }

    /**
     * Kural stringini ayırır
     */
    private function parseRules(string $rule_string): array
    {
        $rules = [];
        $parts = explode('|', $rule_string);

        foreach ($parts as $part) {
            $part = trim($part);
            if (strpos($part, ':') !== false) {
                [$rule_name, $rule_params] = explode(':', $part, 2);
                $rules[$rule_name] = explode(',', $rule_params);
            } else {
                $rules[$part] = [];
            }
        }

        return $rules;
    }

    /**
     * Alanı doğrula
     */
    private function validateField(string $field, array $rules): void
    {
        $value = $this->data[$field] ?? null;

        foreach ($rules as $rule_name => $params) {
            if ($this->shouldSkipValidation($rule_name, $value)) {
                continue;
            }

            if (!$this->applyRule($field, $value, $rule_name, $params)) {
                $this->addError($field, $rule_name, $params);
                break;
            }
        }

        if (!isset($this->errors[$field])) {
            $this->validated_data[$field] = $value;
        }
    }

    /**
     * Kural uygulaması
     */
    private function applyRule(string $field, mixed $value, string $rule_name, array $params): bool
    {
        // Özel kurallar
        if (isset($this->custom_rules[$rule_name])) {
            return call_user_func($this->custom_rules[$rule_name], $value, $params);
        }

        return match ($rule_name) {
            'required' => !empty($value) || $value === '0',
            'email' => filter_var($value, FILTER_VALIDATE_EMAIL) !== false,
            'url' => filter_var($value, FILTER_VALIDATE_URL) !== false,
            'integer' => is_numeric($value) && intval($value) == $value,
            'numeric' => is_numeric($value),
            'min' => strlen((string)$value) >= (int)$params[0],
            'max' => strlen((string)$value) <= (int)$params[0],
            'min_value' => (float)$value >= (float)$params[0],
            'max_value' => (float)$value <= (float)$params[0],
            'phone' => $this->validatePhone($value, $params[0] ?? 'TR'),
            'password' => $this->validatePassword($value),
            'regex' => preg_match($params[0], $value) === 1,
            'matches' => $value === ($this->data[$params[0]] ?? null),
            'unique' => $this->validateUnique($params[0] ?? '', $params[1] ?? '', $value, $field),
            'date' => $this->validateDate($value, $params[0] ?? 'Y-m-d'),
            'accepted' => in_array($value, ['on', 'yes', '1', 1, true]),
            'in' => in_array($value, $params),
            'not_in' => !in_array($value, $params),
            'confirmed' => $value === ($this->data[$field . '_confirmation'] ?? null),
            'array' => is_array($value),
            'json' => $this->isValidJson($value),
            'ipv4' => filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false,
            'ipv6' => filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false,
            'slug' => preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $value) === 1,
            'alpha' => ctype_alpha($value),
            'alpha_num' => ctype_alnum($value),
            'alpha_dash' => preg_match('/^[a-zA-Z0-9_-]+$/', $value) === 1,
            'required_if' => $this->validateRequiredIf($field, $params),
            'required_unless' => $this->validateRequiredUnless($field, $params),
            'different' => $value !== ($this->data[$params[0]] ?? null),
            'same' => $value === ($this->data[$params[0]] ?? null),
            'starts_with' => str_starts_with((string)$value, $params[0]),
            'ends_with' => str_ends_with((string)$value, $params[0]),
            'contains' => str_contains((string)$value, $params[0]),
            'before' => strtotime($value) < strtotime($params[0]),
            'after' => strtotime($value) > strtotime($params[0]),
            default => throw new RuleNotFoundException("Kural '{$rule_name}' bulunamadı")
        };
    }

    /**
     * Telefon numarası doğrulaması
     */
    private function validatePhone(mixed $value, string $country = 'TR'): bool
    {
        $value = (string)$value;
        $patterns = [
            'TR' => '/^(\+90|0)?([1-9]{3})([0-9]{7})$/',
            'US' => '/^(\+1)?([0-9]{10})$/',
            'GB' => '/^(\+44|0)([0-9]{10})$/',
            'DE' => '/^(\+49|0)([0-9]{9,11})$/',
        ];

        $pattern = $patterns[$country] ?? '/^(\+[0-9]{1,3})?([0-9]{7,14})$/' ;
        return preg_match($pattern, $value) === 1;
    }

    /**
     * Şifre doğrulaması
     */
    private function validatePassword(mixed $value): bool
    {
        $value = (string)$value;
        return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $value) === 1;
    }

    /**
     * Tarih doğrulaması
     */
    private function validateDate(mixed $value, string $format): bool
    {
        $date = \DateTime::createFromFormat($format, (string)$value);
        return $date && $date->format($format) === (string)$value;
    }

    /**
     * JSON doğrulaması
     */
    private function isValidJson(mixed $value): bool
    {
        json_decode($value);
        return json_last_error() === JSON_ERROR_NONE;
    }

    /**
     * Benzersiz doğrulaması (Veritabanı)
     */
    private function validateUnique(string $table, string $column, mixed $value, string $field): bool
    {
        if ($this->pdo === null) {
            throw new DatabaseException('Veritabanı bağlantısı yapılandırılmadı');
        }

        try {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM {$table} WHERE {$column} = ?");
            $stmt->execute([$value]);
            return $stmt->fetchColumn() == 0;
        } catch (\PDOException $e) {
            throw new DatabaseException('Veritabanı hatası: ' . $e->getMessage());
        }
    }

    /**
     * Required if doğrulaması
     */
    private function validateRequiredIf(string $field, array $params): bool
    {
        $other_field = $params[0] ?? '';
        $other_value = $params[1] ?? '';

        if (($this->data[$other_field] ?? null) == $other_value) {
            return !empty($this->data[$field]);
        }

        return true;
    }

    /**
     * Required unless doğrulaması
     */
    private function validateRequiredUnless(string $field, array $params): bool
    {
        $other_field = $params[0] ?? '';
        $other_value = $params[1] ?? '';

        if (($this->data[$other_field] ?? null) != $other_value) {
            return !empty($this->data[$field]);
        }

        return true;
    }

    /**
     * Doğrulama atlanmalı mı?
     */
    private function shouldSkipValidation(string $rule_name, mixed $value): bool
    {
        return $rule_name !== 'required' && empty($value) && $value !== '0';
    }

    /**
     * Hata mesajı ekle
     */
    private function addError(string $field, string $rule, array $params): void
    {
        $key = "{$field}.{$rule}";
        $this->errors[$field] = $this->messages[$key] ?? 
            $this->getDefaultMessage($rule, $field, $params);
    }

    /**
     * Varsayılan hata mesajı
     */
    private function getDefaultMessage(string $rule, string $field, array $params): string
    {
        return match ($rule) {
            'required' => "$field zorunlu",
            'email' => "$field geçerli bir e-posta değil",
            'url' => "$field geçerli bir URL değil",
            'min' => "$field en az {$params[0]} karakter olmalı",
            'max' => "$field en fazla {$params[0]} karakter olmalı",
            'integer' => "$field bir tam sayı olmalı",
            'phone' => "$field geçerli bir telefon numarası değil",
            'password' => "$field yeterince güçlü değil",
            default => "$field doğrulamayı geçemedi"
        };
    }

    /**
     * Özel kural ekle
     */
    public function addRule(string $name, callable $callback): self
    {
        $this->custom_rules[$name] = $callback;
        return $this;
    }

    /**
     * Dil dosyasını yükle
     */
    private function loadLanguage(string $language): void
    {
        $lang_file = __DIR__ . "/Languages/{$language}.php";
        if (file_exists($lang_file)) {
            $this->messages = include $lang_file;
        }
    }

    /**
     * Dili ayarla
     */
    public function setLanguage(string $language): self
    {
        if (!file_exists(__DIR__ . "/Languages/{$language}.php")) {
            throw new InvalidLanguageException("Dil dosyası bulunamadı: {$language}");
        }
        $this->language = $language;
        $this->loadLanguage($language);
        return $this;
    }

    /**
     * Özel dil dosyası kullan
     */
    public function setLanguageFile(string $path): self
    {
        if (!file_exists($path)) {
            throw new InvalidLanguageException("Dil dosyası bulunamadı: {$path}");
        }
        $this->messages = include $path;
        return $this;
    }

    /**
     * Veritabanı bağlantısı ayarla
     */
    public function setDatabase(PDO $pdo): self
    {
        $this->pdo = $pdo;
        return $this;
    }

    /**
     * Hata mesajlarını al
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Doğrulanmış verileri al
     */
    public function getValidatedData(): array
    {
        return $this->validated_data;
    }

    /**
     * Sıfırla
     */
    public function reset(): self
    {
        $this->errors = [];
        $this->validated_data = [];
        $this->data = [];
        return $this;
    }

    /**
     * Belirli bir alanın hatasını al
     */
    public function getError(string $field): ?string
    {
        return $this->errors[$field] ?? null;
    }

    /**
     * Hata var mı?
     */
    public function hasError(string $field): bool
    {
        return isset($this->errors[$field]);
    }
}
