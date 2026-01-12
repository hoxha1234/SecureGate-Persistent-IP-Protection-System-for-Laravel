<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function showLoginForm() {
        return view('auth.login');
    }
    
    public function showRegisterForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        // 1. Valida i dati inviati dal modulo
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        // 2. Crea l'utente nel database (non approvato di default)
        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'is_approved' => false, 
            'role' => 'operatore',
        ]);

        // 3. Torna al login con un messaggio
        return redirect()->route('login')->with('success', 'Registrazione completata! Attendi l\'approvazione.');
    }

    public function login(Request $request) {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user) {
            // 1. Controllo Approvazione
            if (!$user->is_approved) {
                return back()->withErrors(['email' => 'Il tuo account è in attesa di approvazione.']);
            }

            // 2. GESTIONE AUTOMATICA (Verifica manuale per evitare il crash Bcrypt)
            $passwordCorretta = false;

            if (str_starts_with($user->password, '$2y$') || str_starts_with($user->password, '$2a$')) {
                // È un hash valido, usiamo il metodo standard di Laravel
                $passwordCorretta = Hash::check($request->password, $user->password);
            } else {
                // NON è un hash (è testo semplice). Confronto diretto.
                if ($request->password === $user->password) {
                    // La password è corretta. La convertiamo subito in Bcrypt per il futuro.
                    $user->password = Hash::make($request->password);
                    $user->save();
                    $passwordCorretta = true;
                }
            }

            // 3. Esegui il Login se la password è stata validata sopra
            if ($passwordCorretta) {
                // RESET TENTATIVI: Il login è riuscito, puliamo i log per questo IP
                DB::table('security_blacklist')->where('ip_address', $request->ip())->update(['failed_attempts' => 0]);

                Auth::login($user);
                $request->session()->regenerate();
                return redirect()->intended('/');
            }
        }

        // --- GESTIONE SICUREZZA: LOGIN FALLITO ---
        $ip = $request->ip();
        
        // 1. Incrementa il contatore nel database
        DB::table('security_blacklist')->updateOrInsert(
            ['ip_address' => $ip],
            [
                'failed_attempts' => DB::raw('IFNULL(failed_attempts, 0) + 1'),
                'last_attack_detected' => now()
            ]
        );

        // 2. CONTROLLO IMMEDIATO: Se arriviamo a 5 o più, attiviamo il blocco
        $tentativi = DB::table('security_blacklist')->where('ip_address', $ip)->value('failed_attempts');
        
        if ($tentativi >= 5) {
            DB::table('security_blacklist')->where('ip_address', $ip)->update([
                'is_blocked' => 1,
                'blocked_until' => now()->addHour() // Blocca per 1 ora
            ]);
        }

        return back()->withErrors(['email' => 'Credenziali non valide.']);
    } // Chiusura del metodo login

    public function logout(Request $request) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
} // Chiusura della classe