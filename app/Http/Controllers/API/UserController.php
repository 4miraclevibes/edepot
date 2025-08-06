<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Models\User;

class UserController extends Controller
{
    public function userDetail()
    {
        try {
            $user = Auth::user();
            return response()->json([
                'code' => 200,
                'status' => 'success',
                'message' => 'User details retrieved successfully',
                'data' => $user
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'status' => 'error',
                'message' => 'An error occurred while retrieving user details',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function login(Request $request)
    {
        try {
            $credentials = $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);

            if (Auth::attempt($credentials)) {
                /** @var \App\Models\User $user */
                $user = Auth::user();
                $user->load('transactions', 'products');

                $token = $user->createToken('authToken')->plainTextToken;

                return response()->json([
                    'code' => 200,
                    'status' => 'success',
                    'message' => 'Login successful',
                    'data' => [
                        'user' => $user,
                        'token' => 'Bearer ' . $token
                    ]
                ], 200);
            }

            return response()->json([
                'code' => 401,
                'status' => 'error',
                'message' => 'Invalid credentials',
            ], 401);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'status' => 'error',
                'message' => 'An error occurred during login',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function register(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8|confirmed',
                'long' => 'required|string|max:255',
                'lat' => 'required|string|max:255',
                'role' => 'nullable|string|max:255',
                'phone' => 'required|string|max:255',
                'address' => 'required|string|max:255',
                'role' => 'required|string|in:user,admin,merchant',
                'long' => 'required|string|max:255',
                'lat' => 'required|string|max:255',
            ]);

            $user = User::create([
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'password' => Hash::make($validatedData['password']),
                'role' => $validatedData['role'] ?? 'user',
                'phone' => $validatedData['phone'],
                'address' => $validatedData['address'],
                'long' => $validatedData['long'],
                'lat' => $validatedData['lat'],
            ]);

            $token = $user->createToken('authToken')->plainTextToken;

            return response()->json([
                'code' => 201,
                'status' => 'success',
                'message' => 'User registered successfully',
                'data' => [
                    'user' => $user,
                    'token' => 'Bearer ' . $token
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 400,
                'status' => 'error',
                'message' => 'Failed to register user',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function update(Request $request)
    {
        try {
             /** @var \App\Models\User $user */
            $user = Auth::user();
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
                'phone' => 'required|string|max:255',
                'long' => 'required|string|max:255',
                'lat' => 'required|string|max:255',
                'role' => 'nullable|string|max:255',
                'address' => 'required|string|max:255',
                'long' => 'required|string|max:255',
                'lat' => 'required|string|max:255',
                'role' => 'nullable|string|max:255',
            ]);

            $user->update([
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'phone' => $validatedData['phone'],
                'address' => $validatedData['address'],
                'long' => $validatedData['long'],
                'lat' => $validatedData['lat'],
                'role' => $validatedData['role'] ?? 'user',
            ]);

            return response()->json([
                'code' => 200,
                'status' => 'success',
                'message' => 'User updated successfully',
                'data' => $user
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 400,
                'status' => 'error',
                'message' => 'Failed to update user',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function logout(Request $request)
    {
        try {
            // Revoke the token that was used to authenticate the current request...
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'code' => 200,
                'status' => 'success',
                'message' => 'Successfully logged out'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'status' => 'error',
                'message' => 'An error occurred during logout',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function toggleOpen(Request $request)
    {
        $user = $request->user();

        if ($user->role !== 'merchant') {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
            ], 403);
        }

        $user->is_open = !$user->is_open;
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Store status updated',
            'is_open' => $user->is_open
        ]);
    }
}
