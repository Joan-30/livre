<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Affiche le formulaire de connexion.
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('tableau-de-bord');
        }
        
        return view('auth.login');
    }

    /**
     * Tente d'authentifier l'utilisateur et gère la redirection selon le rôle.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $throttleKey = \Illuminate\Support\Str::lower($request->input('email')) . '|' . $request->ip();

        // Si l'utilisateur a dépassé 3 tentatives
        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($throttleKey, 3)) {
            return back()->withErrors([
                'email' => 'Erreur interne du serveur : veuillez patienter 5 minutes ou créer un compte.',
            ])->onlyInput('email');
        }

        try {
            if (Auth::attempt($credentials, $request->boolean('remember'))) {
                \Illuminate\Support\Facades\RateLimiter::clear($throttleKey);
                $request->session()->regenerate();
                $user = Auth::user();

                // Redirection intelligente selon les privilèges
                if ($user->role === User::ROLE_ADMIN) {
                    return redirect()->intended(route('admin.dashboard'));
                }

                return redirect()->intended(route('tableau-de-bord'));
            }

            // Tentative échouée (mauvais mot de passe)
            \Illuminate\Support\Facades\RateLimiter::hit($throttleKey, 300); // Bloque pendant 5 minutes après 3 échecs

            if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($throttleKey, 3)) {
                return back()->withErrors([
                    'email' => 'Erreur interne du serveur : veuillez patienter 5 minutes ou créer un compte.',
                ])->onlyInput('email');
            }

            return back()->withErrors([
                'email' => 'Les identifiants fournis ne correspondent pas à nos enregistrements.',
            ])->onlyInput('email');
            
        } catch (\Exception $e) {
            // En cas d'erreur de base de données (ex: QueryException) ou autre
            \Illuminate\Support\Facades\RateLimiter::hit($throttleKey, 300);

            if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($throttleKey, 3)) {
                return back()->withErrors([
                    'email' => 'Erreur interne du serveur : veuillez patienter 5 minutes ou créer un compte.',
                ])->onlyInput('email');
            }

            return back()->withErrors([
                'email' => 'Une erreur est survenue lors de la connexion. Veuillez réessayer.',
            ])->onlyInput('email');
        }
    }

    /**
     * Affiche le formulaire d'inscription.
     */
    public function showRegisterForm()
    {
        if (Auth::check()) {
            return redirect()->route('tableau-de-bord');
        }
        
        return view('auth.register');
    }

    /**
     * Gère la création d'un nouveau compte lecteur.
     */
    public function register(Request $request)
    {
        try {
            $validated = $request->validate([
                'name'     => ['required', 'string', 'max:255'],
                'email'    => ['required', 'string', 'email', 'max:255', 'unique:utilisateurs,email'],
                'password' => ['required', 'string', 'min:8', 'confirmed'],
            ]);

            $user = User::create([
                'name'     => $validated['name'],
                'email'    => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role'     => User::ROLE_LECTEUR, // Rôle par défaut
            ]);

            Auth::login($user);

            return redirect()->route('tableau-de-bord')
                ->with('succes', 'Bienvenue sur Lectura Innov ! Complétez votre profil pour obtenir vos premières recommandations.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Laisser Laravel gérer les erreurs de validation normalement
            throw $e;
        } catch (\Exception $e) {
            // Intercepter les QueryException (base de données hors ligne) sans faire planter l'application
            return back()->withErrors([
                'email' => 'Erreur interne du serveur : impossible de traiter votre inscription pour le moment.',
            ])->withInput();
        }
    }

    /**
     * Déconnecte l'utilisateur.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
