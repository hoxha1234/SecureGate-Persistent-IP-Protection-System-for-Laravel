<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SecureGate
{
    public function handle(Request $request, Closure $next)
    {
        $ip = $request->ip();

        // Controlla se l'IP ha superato i 5 tentativi O se è marcato come bloccato
        $securityRecord = DB::table('security_blacklist')
            ->where('ip_address', $ip)
            ->first();

        if ($securityRecord) {
            $tooManyAttempts = $securityRecord->failed_attempts >= 5;
            $isStillTimeBlocked = $securityRecord->blocked_until && $securityRecord->blocked_until > now();

            if ($tooManyAttempts || $isStillTimeBlocked) {
                // Se è bloccato ma la colonna is_blocked è ancora 0, la aggiorniamo per coerenza
                if ($securityRecord->is_blocked == 0) {
                    DB::table('security_blacklist')->where('ip_address', $ip)->update([
                        'is_blocked' => 1,
                        'blocked_until' => now()->addHour()
                    ]);
                }

                // Al posto del JSON, restituiamo la vista grafica
                return response()->view('errors.blocked', [
                    'attempts' => $securityRecord->failed_attempts,
                    'until' => \Carbon\Carbon::parse($securityRecord->blocked_until ?? now()->addHour())->format('H:i')
                ], 403);
            }
        }

        return $next($request);
    }
}
