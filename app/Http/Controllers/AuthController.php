<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Team login with username + password.
     * On success it generates a token and hands it back to the client.
     */
    public function login(Request $request)
    {
        $dados = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('username', $dados['username'])->first();

        if (! $user || ! Hash::check($dados['password'], $user->password)) {
            return response()->json(['message' => 'Usuário ou senha inválidos.'], 401);
        }

        // Generate a token and store only its hash (the raw value stays with the client).
        $token = Str::random(60);
        $user->update(['api_token' => hash('sha256', $token)]);

        return response()->json([
            'token' => $token,
            'user' => ['username' => $user->username, 'name' => $user->name],
        ]);
    }

    /**
     * Logout: invalidates the current token.
     */
    public function logout(Request $request)
    {
        $user = $request->user();
        if ($user) {
            $user->update(['api_token' => null]);
        }

        return response()->noContent();
    }
}
