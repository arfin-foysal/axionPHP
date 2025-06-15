<?php

namespace App\Controllers;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

abstract class BaseController
{
    protected Request $request;

    public function __construct()
    {
        $this->request = Request::createFromGlobals();
    }

    protected function json($data, int $status = 200, array $headers = []): JsonResponse
    {
        return new JsonResponse($data, $status, $headers);
    }

    protected function success($data = null, string $message = 'Success', int $status = 200): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return $this->json($response, $status);
    }

    protected function error(string $message = 'Error', int $status = 400, $errors = null): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return $this->json($response, $status);
    }

    protected function getJsonInput(): array
    {
        $content = $this->request->getContent();
        return json_decode($content, true) ?? [];
    }

    protected function validate(array $data, array $rules): array
    {
        $errors = [];

        foreach ($rules as $field => $rule) {
            $ruleArray = is_string($rule) ? explode('|', $rule) : $rule;
            
            foreach ($ruleArray as $singleRule) {
                if ($singleRule === 'required' && (!isset($data[$field]) || empty($data[$field]))) {
                    $errors[$field][] = "The {$field} field is required.";
                }
                
                if (str_starts_with($singleRule, 'min:')) {
                    $min = (int) substr($singleRule, 4);
                    if (isset($data[$field]) && strlen($data[$field]) < $min) {
                        $errors[$field][] = "The {$field} field must be at least {$min} characters.";
                    }
                }
                
                if (str_starts_with($singleRule, 'max:')) {
                    $max = (int) substr($singleRule, 4);
                    if (isset($data[$field]) && strlen($data[$field]) > $max) {
                        $errors[$field][] = "The {$field} field must not exceed {$max} characters.";
                    }
                }
                
                if ($singleRule === 'email' && isset($data[$field]) && !filter_var($data[$field], FILTER_VALIDATE_EMAIL)) {
                    $errors[$field][] = "The {$field} field must be a valid email address.";
                }
            }
        }

        return $errors;
    }

    protected function getUser(): ?array
    {
        return $this->request->attributes->get('user');
    }
}
