<?php
declare(strict_types=1);

class Validation
{
    public static function required(string $value, string $field): ?string
    {
        return trim($value) === '' ? "{$field} is required." : null;
    }

    public static function email(?string $value, string $field): ?string
    {
        if ($value === null || $value === '') return null;
        return filter_var($value, FILTER_VALIDATE_EMAIL) ? null : "{$field} must be a valid email address.";
    }

    public static function phone(?string $value, string $field): ?string
    {
        if ($value === null || $value === '') return null;
        $cleaned = preg_replace('/[^0-9+\-\s\(\)]/', '', $value);
        return strlen($cleaned) >= 7 ? null : "{$field} must be a valid phone number.";
    }

    public static function maxLength(?string $value, int $max, string $field): ?string
    {
        if ($value === null || $value === '') return null;
        return mb_strlen($value) > $max ? "{$field} must not exceed {$max} characters." : null;
    }

    public static function date(?string $value, string $field): ?string
    {
        if ($value === null || $value === '') return null;
        $d = DateTime::createFromFormat('Y-m-d', $value);
        return ($d && $d->format('Y-m-d') === $value) ? null : "{$field} must be a valid date.";
    }

    public static function numeric(?string $value, string $field): ?string
    {
        if ($value === null || $value === '') return null;
        return is_numeric($value) ? null : "{$field} must be a number.";
    }

    public static function positiveInt(?string $value, string $field): ?string
    {
        if ($value === null || $value === '') return null;
        return ctype_digit((string)intval($value)) && intval($value) > 0 ? null : "{$field} must be a positive integer.";
    }
}
