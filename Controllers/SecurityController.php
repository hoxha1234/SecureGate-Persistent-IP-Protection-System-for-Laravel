<?php

namespace App\Http\Controllers; // Deve essere esattamente così

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SecurityController extends Controller 
{
    /**
     * Recupera gli ultimi log di sicurezza per la vista Blade
     * Questo è il metodo che mancava e causava l'errore
     */
    public static function getLatestLogs()
    {
        return DB::table('security_blacklist')
            ->orderBy('last_attack_detected', 'desc')
            ->limit(5)
            ->get();
    }

    /**
     * Svuota la tabella e resetta gli IP
     */
    public function clearLogs()
    {
        DB::table('security_blacklist')->truncate();

        return back()->with('success', 'Database di sicurezza ripulito correttamente.');
    }
    public function unlockIp($ip)
    {
        \DB::table('security_blacklist')
            ->where('ip_address', $ip)
            ->update([
                'failed_attempts' => 0,
                'is_blocked' => 0,
                'blocked_until' => null,
                'updated_at' => now()
            ]);

        return back()->with('success', "L'IP $ip è stato sbloccato correttamente.");
    }
    public function blockIp($ip)
    {
        \DB::table('security_blacklist')->updateOrInsert(
            ['ip_address' => $ip],
            [
                'is_blocked' => 1,
                'failed_attempts' => 5, // Forziamo a 5 per attivare i badge rossi
                'blocked_until' => now()->addDays(365), // Lo blocchiamo per un anno (o quanto preferisci)
                'last_attack_detected' => now()
            ]
        );

        
        return back()->with('success', "L'IP $ip è stato bloccato manualmente.");
    }
}
