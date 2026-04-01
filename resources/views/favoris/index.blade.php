<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Lectura Innov – Mes Favoris & Souhaits</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700|playfair-display:700" rel="stylesheet" />

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
    @endif

    <style>
        .font-playfair { font-family: 'Playfair Display', serif; }
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50%       { transform: translateY(-8px); }
        }
        .animate-float { animation: float 4s ease-in-out infinite; }
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-950 text-gray-800 dark:text-gray-100 font-sans antialiased min-h-screen">

<div class="fixed inset-0 -z-10 overflow-hidden pointer-events-none">
    <div class="absolute -top-40 -left-20 w-[60%] h-[60%] rounded-full bg-gradient-to-br from-indigo-500/10 to-pink-500/10 blur-[120px]"></div>
    <div class="absolute bottom-0 right-0 w-[50%] h-[50%] rounded-full bg-gradient-to-tl from-teal-400/10 to-blue-500/10 blur-[100px]"></div>
</div>

<nav class="sticky top-0 z-50 backdrop-blur-md bg-white/70 dark:bg-gray-950/70 border-b border-gray-200 dark:border-gray-800">
    <div class="max-w-7xl mx-auto px-4 h-16 flex items-center justify-between">
        <a href="{{ route('tableau-de-bord') }}" class="flex items-center gap-2 group">
            <span class="w-8 h-8 rounded-lg bg-gradient-to-br from-pink-500 to-orange-400 flex items-center justify-center text-white font-bold text-xl shadow-lg shadow-pink-500/30">L</span>
            <span class="font-bold text-lg tracking-tight bg-clip-text text-transparent bg-gradient-to-r from-pink-500 to-orange-400">Lectura Innov</span>
        </a>
        <a href="{{ route('tableau-de-bord') }}" class="flex items-center gap-2 text-sm font-semibold text-gray-500 dark:text-gray-400 hover:text-indigo-500 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Retour au tableau de bord
        </a>
    </div>
</nav>

<main class="max-w-7xl mx-auto px-4 py-12">
    <header class="mb-12 text-center md:text-left">
        <p class="text-sm font-semibold text-indigo-500 uppercase tracking-widest mb-2">Votre bibliothèque personnelle</p>
        <h1 class="font-playfair text-4xl md:text-5xl font-bold tracking-tight mb-4">
            Mes Favoris & <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-500 to-pink-500">Souhaits</span>
        </h1>
        <p class="text-gray-500 dark:text-gray-400 max-w-2xl">
            Retrouvez ici tous les livres que vous avez mis de côté, vos coups de cœur et vos prochaines lectures.
        </p>
    </header>

    @if($favoris->isEmpty())
        <div class="py-20 text-center flex flex-col items-center">
            <div class="w-24 h-24 bg-gray-100 dark:bg-gray-900 rounded-full flex items-center justify-center mb-6 animate-float shadow-xl border border-gray-200 dark:border-gray-800">
                <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
            </div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Aucun favori pour le moment</h2>
            <p class="text-gray-500 dark:text-gray-400 max-w-md mb-8">
                Explorez vos recommandations et cliquez sur le bouton "Ajouter aux favoris" pour construire votre liste de souhaits.
            </p>
            <a href="{{ route('tableau-de-bord') }}" class="px-6 py-3 bg-gradient-to-r from-indigo-500 to-purple-500 text-white font-bold rounded-xl shadow-lg hover:shadow-indigo-500/30 transition-transform hover:-translate-y-1">
                Découvrir des livres
            </a>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
            @foreach($favoris as $livre)
                <article class="group relative flex flex-col bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-3xl shadow-xl hover:shadow-2xl hover:-translate-y-2 transition-all duration-500 overflow-hidden p-5">
                    
                    {{-- Icône favori fixe --}}
                    <div class="absolute top-4 right-4 z-20">
                        <span class="flex items-center justify-center w-8 h-8 rounded-full bg-pink-500 text-white shadow-lg shadow-pink-500/40">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/></svg>
                        </span>
                    </div>

                    <div class="w-full aspect-[3/4] rounded-2xl overflow-hidden relative mb-4 bg-gray-100 dark:bg-gray-800">
                        @php
                            $palettes = [
                                ['from-pink-500 to-rose-600'], 
                                ['from-purple-500 to-violet-600'],
                                ['from-indigo-400 to-blue-600']
                            ];
                            $palette = $palettes[$livre->id % 3][0];
                        @endphp
                        <div class="w-full h-full flex items-center justify-center bg-gradient-to-br {{ $palette }} group-hover:scale-110 transition-transform duration-700">
                            <span class="font-playfair text-6xl font-bold text-white/60 uppercase">
                                {{ mb_substr($livre->titre, 0, 1) }}
                            </span>
                        </div>
                    </div>

                    <div class="flex flex-col flex-grow">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1">{{ $livre->categorie->nom }}</span>
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-1 line-clamp-2 leading-tight">
                            {{ $livre->titre }}
                        </h2>
                        <p class="text-sm text-indigo-500 font-semibold mb-4">{{ $livre->auteur->nom }}</p>
                        
                        <div class="mt-auto pt-4 border-t border-gray-100 dark:border-gray-800">
                            <x-btn-favori :livre-id="$livre->id" />
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    @endif
</main>

{{-- Toast pour le bouton Favori --}}
<div id="toast" role="status" aria-live="polite" class="fixed bottom-6 right-6 z-50 flex items-center gap-3 bg-gray-900 text-white text-sm font-medium px-5 py-3 rounded-2xl shadow-2xl translate-y-16 opacity-0 transition-all duration-500 pointer-events-none max-w-xs">
    <span id="toast-icone" class="text-lg">✓</span>
    <span id="toast-message">Message</span>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    // Initialisation
    fetch('/favoris', { headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken } })
        .then(r => r.ok ? r.json() : { favoris: [] })
        .then(({ favoris }) => {
            favoris.forEach(id => {
                const btn = document.querySelector(`.btn-favori[data-livre-id="${id}"]`);
                if (btn) btn.classList.add('est-favori');
            });
        });

    // Toggle
    document.addEventListener('click', async (e) => {
        const btn = e.target.closest('.btn-favori');
        if (!btn) return;
        const livreId = btn.dataset.livreId;
        if (!livreId) return;

        btn.disabled = true;
        try {
            const res = await fetch(`/favoris/${livreId}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }
            });
            const data = await res.json();
            
            // Sur la page favoris, si on retire un favori, on pourrait vouloir cacher la carte.
            // Pour l'instant on met juste à jour le bouton.
            if (data.estFavori) {
                btn.classList.add('est-favori');
                afficherToast('❤️ ' + data.message, true);
            } else {
                btn.classList.remove('est-favori');
                afficherToast('🤍 ' + data.message, true);
                // Effet visuel suppression
                btn.closest('article').style.opacity = '0.5';
            }
        } catch (e) {} finally { btn.disabled = false; }
    });

    function afficherToast(message, succes) {
        const toast = document.getElementById('toast');
        document.getElementById('toast-message').textContent = message;
        toast.classList.remove('translate-y-16', 'opacity-0');
        setTimeout(() => toast.classList.add('translate-y-16', 'opacity-0'), 3000);
    }
});
</script>

</body>
</html>
