<?php

namespace Core;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Core\JwtHelper;
use Exception;

class JwtMiddleware
{
    public function handle(Request $request): ?JsonResponse
    {
        try {
            $token = JwtHelper::getTokenFromRequest();
            
            if (!$token) {
                return new JsonResponse([
                    'error' => 'Token not provided',
                    'message' => 'Authorization token is required'
                ], 401);
            }

            $payload = JwtHelper::validateToken($token);
            
            // Add user data to request attributes for use in controllers
            $request->attributes->set('user', $payload);
            
            return null; // Continue to next middleware/controller
            
        } catch (Exception $e) {
            return new JsonResponse([
                'error' => 'Unauthorized',
                'message' => $e->getMessage()
            ], 401);
        }
    }

    public static function optional(Request $request): ?JsonResponse
    {
        try {
            $token = JwtHelper::getTokenFromRequest();
            
            if ($token) {
                $payload = JwtHelper::validateToken($token);
                $request->attributes->set('user', $payload);
            }
            
            return null; // Continue regardless of token presence
            
        } catch (Exception $e) {
            // For optional middleware, we don't return error responses
            // Just continue without setting user data
            return null;
        }
    }
}
