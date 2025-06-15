<?php

namespace App\Controllers;

use App\Models\User;
use Core\JwtHelper;
use Symfony\Component\HttpFoundation\JsonResponse;
use Exception;

class AuthController extends BaseController
{
    public function register(): JsonResponse
    {
        try {
            $data = $this->getJsonInput();
            
            // Validate input
            $errors = $this->validate($data, [
                'name' => 'required|min:2|max:255',
                'email' => 'required|email|max:255',
                'password' => 'required|min:6|max:255'
            ]);

            if (!empty($errors)) {
                return $this->error('Validation failed', 422, $errors);
            }

            // Check if user already exists
            if (User::findByEmail($data['email'])) {
                return $this->error('User with this email already exists', 409);
            }

            // Create user
            $user = new User();
            $user->name = $data['name'];
            $user->email = $data['email'];
            $user->password = $data['password']; // Will be hashed by the model
            $user->save();

            // Generate JWT token
            $token = JwtHelper::generateToken($user->getJwtPayload());

            return $this->success([
                'user' => $user->toArray(),
                'token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => JwtHelper::getTokenExpiration()
            ], 'User registered successfully', 201);

        } catch (Exception $e) {
            return $this->error('Registration failed: ' . $e->getMessage(), 500);
        }
    }

    public function login(): JsonResponse
    {
        try {
            $data = $this->getJsonInput();
            
            // Validate input
            $errors = $this->validate($data, [
                'email' => 'required|email',
                'password' => 'required'
            ]);

            if (!empty($errors)) {
                return $this->error('Validation failed', 422, $errors);
            }

            // Find user
            $user = User::findByEmail($data['email']);
            
            if (!$user || !$user->verifyPassword($data['password'])) {
                return $this->error('Invalid credentials', 401);
            }

            // Generate JWT token
            $token = JwtHelper::generateToken($user->getJwtPayload());

            return $this->success([
                'user' => $user->toArray(),
                'token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => JwtHelper::getTokenExpiration()
            ], 'Login successful');

        } catch (Exception $e) {
            return $this->error('Login failed: ' . $e->getMessage(), 500);
        }
    }

    public function profile(): JsonResponse
    {
        try {
            $userData = $this->getUser();
            
            if (!$userData) {
                return $this->error('User not authenticated', 401);
            }

            // Get fresh user data from database
            $user = User::find($userData['id']);
            
            if (!$user) {
                return $this->error('User not found', 404);
            }

            return $this->success($user->toArray(), 'Profile retrieved successfully');

        } catch (Exception $e) {
            return $this->error('Failed to retrieve profile: ' . $e->getMessage(), 500);
        }
    }

    public function logout(): JsonResponse
    {
        try {
            $token = JwtHelper::getTokenFromRequest();
            
            if ($token) {
                // In a real application, you would add the token to a blacklist
                JwtHelper::revokeToken($token);
            }

            return $this->success(null, 'Logout successful');

        } catch (Exception $e) {
            return $this->error('Logout failed: ' . $e->getMessage(), 500);
        }
    }

    public function refresh(): JsonResponse
    {
        try {
            $token = JwtHelper::getTokenFromRequest();
            
            if (!$token) {
                return $this->error('Token not provided', 401);
            }

            $newToken = JwtHelper::refreshToken($token);

            return $this->success([
                'token' => $newToken,
                'token_type' => 'Bearer',
                'expires_in' => JwtHelper::getTokenExpiration()
            ], 'Token refreshed successfully');

        } catch (Exception $e) {
            return $this->error('Token refresh failed: ' . $e->getMessage(), 401);
        }
    }
}
