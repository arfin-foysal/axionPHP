<?php

namespace Core;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use Exception;

class JwtHelper
{
    private static string $secret;
    private static string $algorithm;
    private static int $expiration;

    public static function init(): void
    {
        self::$secret = $_ENV['JWT_SECRET'] ?? 'default-secret-key';
        self::$algorithm = $_ENV['JWT_ALGORITHM'] ?? 'HS256';
        self::$expiration = (int)($_ENV['JWT_EXPIRATION'] ?? 3600);
    }

    public static function generateToken(array $payload): string
    {
        self::init();
        
        $issuedAt = time();
        $expirationTime = $issuedAt + self::$expiration;
        
        $token = [
            'iat' => $issuedAt,
            'exp' => $expirationTime,
            'data' => $payload
        ];

        return JWT::encode($token, self::$secret, self::$algorithm);
    }

    public static function validateToken(string $token): ?array
    {
        self::init();
        
        try {
            $decoded = JWT::decode($token, new Key(self::$secret, self::$algorithm));
            return (array) $decoded->data;
        } catch (ExpiredException $e) {
            throw new Exception('Token has expired', 401);
        } catch (SignatureInvalidException $e) {
            throw new Exception('Invalid token signature', 401);
        } catch (Exception $e) {
            throw new Exception('Invalid token', 401);
        }
    }

    public static function getTokenFromRequest(): ?string
    {
        $headers = getallheaders();
        
        // Check Authorization header
        if (isset($headers['Authorization'])) {
            $authHeader = $headers['Authorization'];
            if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
                return $matches[1];
            }
        }
        
        // Check authorization header (lowercase)
        if (isset($headers['authorization'])) {
            $authHeader = $headers['authorization'];
            if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    public static function refreshToken(string $token): string
    {
        $payload = self::validateToken($token);
        return self::generateToken($payload);
    }

    public static function revokeToken(string $token): bool
    {
        // In a real application, you would store revoked tokens in a database
        // For this example, we'll just return true
        return true;
    }

    public static function getTokenExpiration(): int
    {
        return self::$expiration;
    }
}
