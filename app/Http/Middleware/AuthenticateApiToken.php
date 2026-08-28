<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateApiToken
{
    /**
     * Guards the team routes: requires a valid token in the
     * Authorization: Bearer <token> header.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (! $token) {
            return response()->json(['message' => 'Não autenticado.'], 401);
        }

        $user = User::where('api_token', hash('sha256', $token))->first();

        if (! $user) {
            return response()->json(['message' => 'Token inválido ou expirado.'], 401);
        }

        // Makes the authenticated user available to the controllers.
        $request->setUserResolver(fn () => $user);

        return $next($request);
    }
}
