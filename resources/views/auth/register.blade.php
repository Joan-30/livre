<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lectura Innov – Inscription</title>

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
        .dark .glass-panel { background: rgba(17, 24, 39, 0.8); border-color: rgba(31, 41, 55, 0.8); }
        @keyframes float {
            0%, 100% { transform: translateY(0) scale(1); }
            50% { transform: translateY(-20px) scale(1.05); }
        }
        .animate-float { animation: float 6s ease-in-out infinite; }
        .animate-float-delayed { animation: float 8s ease-in-out infinite 2s; }
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-950 font-sans antialiased min-h-screen flex items-center justify-center relative overflow-hidden text-gray-800 dark:text-gray-100">

{{-- Background effects --}}
<div class="absolute inset-0 z-0 overflow-hidden pointer-events-none">
    <div class="absolute top-10 left-[10%] w-72 h-72 bg-gradient-to-br from-pink-500/20 to-rose-400/20 rounded-full blur-[80px] animate-float"></div>
    <div class="absolute bottom-10 right-[10%] w-96 h-96 bg-gradient-to-tl from-indigo-500/20 to-purple-500/20 rounded-full blur-[100px] animate-float-delayed"></div>
</div>

<main class="relative z-10 w-full max-w-lg px-4 py-8 sm:px-0">
    
    {{-- Logo / En-tête --}}
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-br from-pink-500 to-orange-400 text-white font-bold text-2xl shadow-xl shadow-pink-500/40 mb-4 hover:-translate-y-1 transition-transform cursor-default">
            L
        </div>
        <h1 class="font-playfair text-3xl font-bold tracking-tight mb-2">Rejoignez-nous</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400">
            Créez votre profil pour découvrir des livres qui vous correspondent.
        </p>
    </div>

    {{-- Formulaire --}}
    <div class="glass-panel border border-white/20 dark:border-gray-800/80 rounded-3xl p-8 shadow-2xl dark:shadow-black/50 transform transition-all duration-500 translate-y-4 opacity-0 animate-[fade-in-up_0.5s_ease-out_forwards]" style="animation: fade-in-up 0.5s ease-out forwards;">
        <form method="POST" action="{{ route('register') }}" class="space-y-5">
            @csrf

            {{-- Nom complet --}}
            <div>
                <label for="name" class="block text-sm font-semibold mb-1">Nom complet</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required autofocus
                       class="w-full h-11 px-4 bg-white/50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-800 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all dark:text-white @error('name') border-red-500 @enderror"
                       placeholder="nom">
                @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Email --}}
            <div>
                <label for="email" class="block text-sm font-semibold mb-1">Adresse Email</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required
                       class="w-full h-11 px-4 bg-white/50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-800 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all dark:text-white @error('email') border-red-500 @enderror"
                       placeholder="email">
                @error('email') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                {{-- Mot de passe --}}
                <div>
                    <label for="password" class="block text-sm font-semibold mb-1">Mot de passe</label>
                    <input type="password" name="password" id="password" required
                           class="w-full h-11 px-4 bg-white/50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-800 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all dark:text-white @error('password') border-red-500 @enderror"
                           placeholder="Mot de passe">
                </div>

                {{-- Confirmation --}}
                <div>
                    <label for="password_confirmation" class="block text-sm font-semibold mb-1">Confirmation</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" required
                           class="w-full h-11 px-4 bg-white/50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-800 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all dark:text-white"
                           placeholder="Mot de passe">
                </div>
            </div>
            @error('password') <p class="text-xs text-red-500 -mt-2">{{ $message }}</p> @enderror

            {{-- Info privilège --}}
            <div class="flex items-start gap-2 p-3 bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-100 dark:border-indigo-800/30 rounded-xl text-xs text-indigo-700 dark:text-indigo-300">
                <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p>En vous inscrivant, vous obtiendrez un accès <strong class="font-bold">Lecteur</strong> par défaut, donnant accès au Tableau de bord personnel.</p>
            </div>

            {{-- Bouton Submit --}}
            <button type="submit"
                    class="w-full h-12 mt-2 flex items-center justify-center bg-gradient-to-r from-indigo-500 to-purple-500 text-white font-bold rounded-xl shadow-lg shadow-indigo-500/30 hover:-translate-y-0.5 hover:shadow-xl transition-all active:scale-95 focus:outline-none focus:ring-4 focus:ring-indigo-500/30">
                Créer mon compte
            </button>
        </form>

        <div class="mt-6 text-center text-sm">
            <p class="text-gray-500 dark:text-gray-400">
                Vous avez déjà un compte ? 
                <a href="{{ route('login') }}" class="font-semibold text-indigo-500 hover:text-indigo-400 hover:underline transition-colors">
                    Connectez-vous
                </a>
            </p>
        </div>
    </div>
</main>

<style>
@keyframes fade-in-up {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>

</body>
</html>
