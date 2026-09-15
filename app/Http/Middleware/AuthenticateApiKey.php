<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Hash;

use App\Models\ApiKey;

class AuthenticateApiKey
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $key = $request->header('X-API-Key');

        if (!$key) {
            return response()->json([
                'message' => 'API key is required.',
            ], 401);
        }

        if (!str_starts_with($key, 'sk_live_')) {
            return response()->json([
                'message' => 'Invalid API key.',
            ], 401);
        }

        $key = substr($key, strlen('sk_live_'));

        [$identifier, $secret] = array_pad(
            explode('_', $key, 2),
            2,
            null,
        );

        if (!$identifier || !$secret) {
            return response()->json([
                'message' => 'Invalid API key.',
            ], 401);
        }

        $apiKey = ApiKey::query()
            ->where('identifier', $identifier)
            ->where('active', true)
            ->first();

        if (!$apiKey || !Hash::check($secret, $apiKey->key_hash)) {
            return response()->json([
                'message' => 'Invalid API key.',
            ], 401);
        }

        $apiKey->update([
            'last_used_at' => now(),
        ]);

        $request->attributes->set('server', $apiKey->server);

        return $next($request);
    }
}
