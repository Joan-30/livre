<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lectura Innov – Administration</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700|playfair-display:700" rel="stylesheet" />

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
    @endif

    <style>
        .font-playfair { font-family: 'Playfair Display', serif; }
        @keyframes slideIn {
            from { transform: translateX(-10px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        .animate-slide {
            animation: slideIn 0.5s ease-out forwards;
        }
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-950 text-gray-800 dark:text-gray-100 font-sans antialiased min-h-screen">

<nav class="sticky top-0 z-50 backdrop-blur-md bg-white/70 dark:bg-gray-950/70 border-b border-gray-200 dark:border-gray-800">
    <div class="max-w-7xl mx-auto px-4 h-16 flex items-center justify-between">
        <a href="#" class="flex items-center gap-2 group">
            <span class="w-8 h-8 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold text-xl shadow-lg shadow-indigo-500/30">A</span>
            <span class="font-bold text-lg tracking-tight bg-clip-text text-transparent bg-gradient-to-r from-indigo-500 to-purple-600">Administration</span>
        </a>

        <div class="flex items-center gap-4">
            <span class="text-sm font-semibold text-gray-500 dark:text-gray-400 border border-gray-200 dark:border-gray-700 px-3 py-1 rounded-full bg-gray-50 dark:bg-gray-800">
                Mode Admin
            </span>
            <form method="POST" action="{{ route('logout') }}" class="ml-1 flex items-center">
                @csrf
                <button type="submit" title="Se déconnecter" class="p-2 rounded-full hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors text-gray-500 hover:text-red-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                </button>
            </form>
        </div>
    </div>
</nav>

<main class="max-w-7xl mx-auto px-4 py-12">
    <header class="mb-12 animate-slide">
        <h1 class="font-playfair text-4xl font-bold tracking-tight mb-3 text-transparent bg-clip-text bg-gradient-to-r from-indigo-500 to-purple-500">
            Bonjour Administrateur, {{ $utilisateur->name }}
        </h1>
        <p class="text-gray-500 dark:text-gray-400 max-w-2xl">
            Bienvenue dans votre espace sécurisé. Ici, vous pourrez gérer les livres, modérer les avis et gérer les campagnes de recommandation.
        </p>
    </header>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 animate-slide" style="animation-delay: 0.1s; opacity: 0;">
        {{-- Card 1 : Gestion Catalogue --}}
        <a href="{{ route('admin.livres.index') }}" class="block p-6 bg-white dark:bg-gray-900 border border-t-4 border-gray-100 dark:border-gray-800 border-t-indigo-500 rounded-2xl shadow-xl hover:-translate-y-1 transition-transform cursor-pointer">
            <div class="w-12 h-12 bg-indigo-50 dark:bg-indigo-900/30 rounded-xl flex items-center justify-center text-indigo-500 mb-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
            </div>
            <h3 class="font-bold text-gray-900 dark:text-white mb-2 group-hover:text-indigo-500 transition-colors">Gestion Catalogue</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">Modifier, ajouter ou supprimer les livres du moteur (Actif).</p>
        </a>

        {{-- Card 2 --}}
        <div class="p-6 bg-white dark:bg-gray-900 border border-t-4 border-gray-100 dark:border-gray-800 border-t-purple-500 rounded-2xl shadow-xl hover:-translate-y-1 transition-transform">
            <div class="w-12 h-12 bg-purple-50 dark:bg-purple-900/30 rounded-xl flex items-center justify-center text-purple-500 mb-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"/></svg>
            </div>
            <h3 class="font-bold text-gray-900 dark:text-white mb-2">Modération Avis</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">Gérer les avis signalés et les commentaires haineux (A venir).</p>
        </div>

        {{-- Card 3 --}}
        <div class="p-6 bg-white dark:bg-gray-900 border border-t-4 border-gray-100 dark:border-gray-800 border-t-teal-500 rounded-2xl shadow-xl hover:-translate-y-1 transition-transform">
            <div class="w-12 h-12 bg-teal-50 dark:bg-teal-900/30 rounded-xl flex items-center justify-center text-teal-500 mb-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/></svg>
            </div>
            <h3 class="font-bold text-gray-900 dark:text-white mb-2">Statistiques Moteur</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">Performances du réseau de neurones et d'affinité globale (A venir).</p>
        </div>
    </div>
</main>

</body>
</html>
