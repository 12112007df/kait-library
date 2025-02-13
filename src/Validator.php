<?php

/**
 * Простая проверка входных данных.
 * Пример: $v = new Validator($_POST); $v->required('email')->email('email');
 */
class Validator
{
    private $data;
    private $errors = [];

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function required($field, $message = null)
    {
        if (!isset($this->data[$field]) || trim((string)$this->data[$field]) === '') {
            $this->errors[$field] = $message ?? 'Поле обязательно для заполнения';
        }
        return $this;
    }

    public function email($field)
    {
        if (!empty($this->data[$field]) && !filter_var($this->data[$field], FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = 'Некорректный email';
        }
        return $this;
    }

    public function minLength($field, $length)
    {
        if (isset($this->data[$field]) && mb_strlen($this->data[$field]) < $length) {
            $this->errors[$field] = "Минимальная длина — $length символов";
        }
        return $this;
    }

    public function maxLength($field, $length)
    {
        if (isset($this->data[$field]) && mb_strlen($this->data[$field]) > $length) {
            $this->errors[$field] = "Максимальная длина — $length символов";
        }
        return $this;
    }

    public function in($field, $values)
    {
        if (isset($this->data[$field]) && !in_array($this->data[$field], $values, true)) {
            $this->errors[$field] = 'Недопустимое значение';
        }
        return $this;
    }

    public function fails()
    {
        return !empty($this->errors);
    }

    public function errors()
    {
        return $this->errors;
    }

    public function first()
    {
        return reset($this->errors) ?: null;
    }
}
