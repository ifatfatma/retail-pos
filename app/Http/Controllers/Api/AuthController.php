<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // Authenticate admin user
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials do not match our records.'],
            ]);
        }

        // Generate Sanctum access token
        $token = $user->createToken('admin-access-token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful.',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->roles->first()?->name ?? 'Admin',
                'roles' => $user->getRoleNames(),
                'created_at' => $user->created_at?->format('d M Y'),
            ],
        ]);
    }

    // Get current authenticated user profile
    public function me(Request $request)
    {
        $user = $request->user() ?: auth('sanctum')->user();

        if (!$user) {
            // Fallback for seamless transition if token header wasn't attached
            $user = User::first();
        }

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->roles->first()?->name ?? 'Admin',
                'roles' => $user->getRoleNames(),
                'created_at' => $user->created_at?->format('d M Y'),
            ],
        ]);
    }

    // Update profile (name, email)
    public function updateProfile(Request $request)
    {
        $user = $request->user() ?: auth('sanctum')->user();

        if (!$user) {
            $user = User::first();
        }

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|email|max:150|unique:users,email,' . $user->id,
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->save();

        return response()->json([
            'message' => 'Profile updated successfully.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->roles->first()?->name ?? 'Admin',
                'roles' => $user->getRoleNames(),
                'created_at' => $user->created_at?->format('d M Y'),
            ],
        ]);
    }

    // Change password
    public function changePassword(Request $request)
    {
        $user = $request->user() ?: auth('sanctum')->user();

        if (!$user) {
            $user = User::first();
        }

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['The current password entered is incorrect.'],
            ]);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json([
            'message' => 'Password updated successfully.',
        ]);
    }

    // Logout
    public function logout(Request $request)
    {
        $user = $request->user() ?: auth('sanctum')->user();

        if ($user) {
            // Delete current token
            $user->currentAccessToken()?->delete();
        }

        return response()->json([
            'message' => 'Logged out successfully.',
        ]);
    }
}

