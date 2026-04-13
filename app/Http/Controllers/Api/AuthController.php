<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Membership;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:50|unique:users|alpha_dash',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
        ]);

        $user = User::create([
            ...$validated,
            'password' => Hash::make($validated['password']),
        ]);

        $user->assignRole('user');

        $token = $user->createToken('mobile')->plainTextToken;

        return $this->success([
            'user' => $this->formatUser($user),
            'token' => $token,
        ], 'Inscription réussie', 201);
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return $this->error('Identifiants incorrects', 401);
        }

        if ($user->status !== 'active') {
            return $this->error('Compte suspendu ou banni. Contactez le support.', 403);
        }

        $token = $user->createToken('mobile')->plainTextToken;

        return $this->success([
            'user' => $this->formatUser($user->load('membership', 'badges')),
            'token' => $token,
        ], 'Connexion réussie');
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return $this->success(null, 'Déconnexion réussie');
    }

    public function me(Request $request)
    {
        $user = $request->user()->load('membership', 'badges', 'certifications');
        return $this->success($this->formatUser($user));
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'username' => 'sometimes|string|max:50|unique:users,username,'.$user->id.'|alpha_dash',
            'bio' => 'sometimes|nullable|string|max:500',
            'location' => 'sometimes|nullable|string|max:255',
            'phone' => 'sometimes|nullable|string|max:20',
            'birth_date' => 'sometimes|nullable|date',
            'gender' => 'sometimes|nullable|in:male,female,other',
            'fcm_token' => 'sometimes|nullable|string',
        ]);

        $user->update($validated);

        return $this->success($this->formatUser($user), 'Profil mis à jour');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return $this->error('Mot de passe actuel incorrect', 422);
        }

        $user->update(['password' => Hash::make($request->password)]);

        return $this->success(null, 'Mot de passe modifié');
    }

    public function getUserProfile(string $username)
    {
        $user = User::where('username', $username)
            ->where('status', 'active')
            ->with(['badges', 'membership'])
            ->firstOrFail();

        return $this->success([
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'avatar_url' => $user->avatar_url,
            'bio' => $user->bio,
            'location' => $user->location,
            'is_verified' => $user->is_verified,
            'engagement_level' => $user->engagement_level,
            'badges' => $user->badges,
            'membership_type' => $user->membership?->type,
            'followers_count' => $user->followers()->count(),
            'following_count' => $user->following()->count(),
            'videos_count' => $user->videos()->where('status', 'published')->count(),
        ]);
    }

    private function formatUser(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'email' => $user->email,
            'phone' => $user->phone,
            'avatar_url' => $user->avatar_url,
            'bio' => $user->bio,
            'location' => $user->location,
            'birth_date' => $user->birth_date,
            'gender' => $user->gender,
            'status' => $user->status,
            'is_verified' => $user->is_verified,
            'is_admin' => $user->is_admin,
            'engagement_level' => $user->engagement_level,
            'gjka_member_id' => $user->gjka_member_id,
            'gjka_member_since' => $user->gjka_member_since,
            'membership' => $user->relationLoaded('membership') ? $user->membership : null,
            'badges' => $user->relationLoaded('badges') ? $user->badges : null,
            'total_points' => $user->total_points,
            'created_at' => $user->created_at,
        ];
    }
}
