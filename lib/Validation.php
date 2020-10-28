<?php

class Validation
{
    private $validation_rules;

    public function __construct($validation_rules)
    {
        $this->validation_rules = $validation_rules;
    }

    public function validate($inputs)
    {
        $error_messages = [];
        foreach ($this->validation_rules as $input_key => $validation_rule) {
            $input = null;
            if (isset($inputs[$input_key])) {
                $input = $inputs[$input_key];
            }

            foreach ($validation_rule['rules'] as $rule_name => $rule) {
                if ($rule_name === 'required') {
                    $validate_required_error_message = $this->validateRequired($input, $validation_rule['name'], $rule);
                    if ($validate_required_error_message !== '') {
                        $error_messages[] = $validate_required_error_message;
                    }
                }

                if ($rule_name === 'length') {
                    $validate_length_error_message = $this->validateLength($input, $validation_rule['name'], $rule);
                    if ($validate_length_error_message !== '') {
                        $error_messages[] = $validate_length_error_message;
                    }
                }

                if ($rule_name === 'pattern') {
                    $validate_pattern_error_message = $this->validatePattern($input, $validation_rule['name'], $rule);
                    if ($validate_pattern_error_message !== '') {
                        $error_messages[] = $validate_pattern_error_message;
                    }
                }
            }
        }

        return $error_messages;
    }

    private function validateRequired($input, $input_name, $is_required)
    {
        if ($is_required && is_empty($input)) {
            return $input_name . 'を入力してください。';
        }

        return '';
    }

    private function validateLength($input, $input_name, $length)
    {
        if (is_empty($input)) {
            return '';
        }

        $min = null;
        $max = null;
        if (isset($length['min'])) {
            $min = $length['min'];
        }
        if (isset($length['max'])) {
            $max = $length['max'];
        }

        if ($min && $max) {
            if (mb_strlen($input) < $min || mb_strlen($input) > $max) {
                return $input_name . 'は' . $min . '文字以上' . $max . '文字以下で入力してください。';
            }
        } elseif ($min) {
            if (mb_strlen($input) < $min) {
                return $input_name . 'は' . $min . '文字以上で入力してください。';
            }
        } elseif ($max) {
            if (mb_strlen($input) > $max) {
                return $input_name . 'は' . $max . '文字以下で入力してください。';
            }
        }

        return '';
    }

    private function validatePattern($input, $input_name, $pattern)
    {
        if (is_empty($input)) {
            return '';
        }

        if (!preg_match($pattern['regex'], $input)) {
            return $input_name . 'は' . $pattern['meaning'] . 'で入力してください。';
        }

        return '';
    }
}
