<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Validates cron requests using a timing-safe comparison of the X-Cron-Secret header.
 *
 * Security:
 *   - hash_equals() prevents timing attacks (attacker can't guess the secret byte-by-byte)
 *   - HMAC comparison so even if an attacker reads partial memory, the raw secret isn't compared directly
 *   - Rejects early if secret is not configured (fail-safe)
 */
class VerifyCronSecret
{
    public function handle(Request $request, Closure $next): Response
    {
        $configuredSecret = config('app.cron_secret');

        // Fail-safe: if no secret is configured, block all cron requests
        if (empty($configuredSecret)) {
            return response()->json(['message' => 'Cron secret not configured'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $providedSecret = $request->header('X-Cron-Secret', '');

        if (empty($providedSecret)) {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        // HMAC both values with a fixed key so we never compare raw secrets in memory.
        // hash_equals is timing-safe — prevents attackers from measuring response time
        // to guess the secret character by character.
        $expected = hash_hmac('sha256', $configuredSecret, 'cron-verify');
        $provided = hash_hmac('sha256', $providedSecret, 'cron-verify');

        if (!hash_equals($expected, $provided)) {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}
