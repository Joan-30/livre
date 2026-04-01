<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lectura Innov – Connexion</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700|playfair-display:700" rel="stylesheet" />

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
    @endif

    <style>
        .font-playfair { font-family: 'Playfair Display', serif; }
        .glass-panel { background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(16px); }
        .dark .glass-panel { background: rgba(17, 24, 39, 0.7); border-color: rgba(31, 41, 55, 0.8); }
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-950 font-sans antialiased min-h-screen flex items-center justify-center relative overflow-hidden text-gray-800 dark:text-gray-100">

{{-- Background effects --}}
<div class="absolute inset-0 z-0 overflow-hidden pointer-events-none">
    <div class="absolute -top-60 -right-40 w-[800px] h-[800px] rounded-full bg-gradient-to-br from-indigo-500/20 to-purple-500/20 blur-[120px]"></div>
    <div class="absolute -bottom-60 -left-40 w-[600px] h-[600px] rounded-full bg-gradient-to-tr from-pink-500/20 to-orange-400/20 blur-[100px]"></div>
</div>

<main class="relative z-10 w-full max-w-md px-4 py-12 sm:px-0">
    
    {{-- Logo / En-tête --}}
    <div class="text-center mb-10">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br from-pink-500 to-orange-400 text-white font-bold text-3xl shadow-xl shadow-pink-500/40 mb-6 group-hover:scale-110 transition-transform">
            L
        </div>
        <h1 class="font-playfair text-3xl font-bold tracking-tight mb-2">Bon retour !</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400">
            Connectez-vous pour retrouver vos recommandations personnalisées.
        </p>
    </div>

    {{-- Formulaire --}}
    <div class="glass-panel border border-white/20 dark:border-gray-800/80 rounded-3xl p-8 shadow-2xl dark:shadow-black/50">
        <form method="POST" action="{{ route('login') }}" class="space-y-6">
            @csrf

            {{-- Erreurs Globales --}}
            @if($errors->any())
            <div class="p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl">
                <p class="text-sm text-red-600 dark:text-red-400 font-medium">
                    {{ $errors->first() }}
                </p>
            </div>
            @endif

            {{-- Email --}}
            <div>
                <label for="email" class="block text-sm font-semibold mb-2">Adresse Email</label>
                <input type="email" name="email" id="email" value="{{ old('email', 'alice@lectura.test') }}" required autofocus
                       class="w-full h-12 px-4 bg-white/50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-800 rounded-xl focus:ring-2 focus:ring-pink-500 focus:border-pink-500 outline-none transition-all dark:text-white"
                       placeholder="email">
            </div>

            {{-- Mot de passe --}}
            <div>
                <div class="flex justify-between items-center mb-2">
                    <label for="password" class="block text-sm font-semibold">Mot de passe</label>
                </div>
                <input type="password" name="password" id="password" required value="password"
                       class="w-full h-12 px-4 bg-white/50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-800 rounded-xl focus:ring-2 focus:ring-pink-500 focus:border-pink-500 outline-none transition-all dark:text-white"
                       placeholder="Mot de passe">
            </div>

            {{-- Se souvenir de moi --}}
            <div class="flex items-center">
                <input type="checkbox" name="remember" id="remember" class="w-4 h-4 rounded border-gray-300 text-pink-500 focus:ring-pink-500 dark:bg-gray-800 dark:border-gray-700">
                <label for="remember" class="ml-2 text-sm text-gray-500 dark:text-gray-400 select-none">Se souvenir de moi</label>
            </div>

            {{-- Bouton Submit --}}
            <button type="submit"
                    class="w-full h-12 flex items-center justify-center bg-gradient-to-r from-pink-500 to-orange-400 text-white font-bold rounded-xl shadow-lg shadow-pink-500/30 hover:scale-[1.02] hover:shadow-xl transition-all active:scale-95 focus:outline-none focus:ring-4 focus:ring-pink-500/30">
                Se connecter
            </button>
        </form>

        <div class="mt-8 text-center text-sm text-gray-500 dark:text-gray-400 border-t border-gray-200 dark:border-gray-800 pt-6 mt-6">
            <p>Pas encore de compte ? 
                <a href="{{ route('register') }}" class="font-semibold text-pink-500 hover:text-pink-400 hover:underline transition-colors">
                    S'inscrire sur Lectura Innov
                </a>
            </p>
        </div>
    </div>
</main>

</body>
</html>
