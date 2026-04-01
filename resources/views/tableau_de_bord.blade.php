<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Lectura Innov – Tableau de bord</title>
    <meta name="description" content="Vos recommandations de lecture personnalisées par notre moteur d'affinité hybride.">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700|playfair-display:700" rel="stylesheet" />

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
    @endif

    <style>
        /* ── Animations personnalisées ── */
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50%       { transform: translateY(-8px); }
        }
        @keyframes shimmer {
            0%   { background-position: -1000px 0; }
            100% { background-position:  1000px 0; }
        }
        .animate-float   { animation: float 4s ease-in-out infinite; }
        .font-playfair   { font-family: 'Playfair Display', serif; }

        /* Barre de score animée */
        .barre-score {
            transition: width 1.2s cubic-bezier(0.22, 1, 0.36, 1);
        }

        /* Tooltip personnalisé */
        .tooltip-group:hover .tooltip-content { opacity: 1; transform: translateY(0); pointer-events: auto; }
        .tooltip-content {
            opacity: 0;
            transform: translateY(4px);
            pointer-events: none;
            transition: opacity .2s ease, transform .2s ease;
        }
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-950 text-gray-800 dark:text-gray-100 font-sans antialiased min-h-screen selection:bg-pink-500 selection:text-white">

<div class="relative min-h-screen overflow-hidden">

    {{-- ── Blobs de fond (glassmorphisme) ─────────────────────────── --}}
    <div aria-hidden="true" class="fixed top-0 left-0 w-full h-full overflow-hidden -z-10 pointer-events-none">
        <div class="absolute -top-[30%] -left-[10%] w-[70%] h-[70%] rounded-full bg-gradient-to-br from-purple-500/20 to-pink-500/20 blur-[120px]"></div>
        <div class="absolute top-[20%] -right-[20%] w-[60%] h-[60%] rounded-full bg-gradient-to-bl from-orange-400/20 to-pink-600/20 blur-[120px]"></div>
        <div class="absolute bottom-0 left-1/3 w-[40%] h-[40%] rounded-full bg-gradient-to-tr from-blue-500/10 to-indigo-500/10 blur-[100px]"></div>
    </div>

    {{-- ── Navbar ───────────────────────────────────────────────────── --}}
    <nav class="sticky top-0 z-50 backdrop-blur-md bg-white/70 dark:bg-gray-950/70 border-b border-gray-200 dark:border-gray-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                {{-- Logo --}}
                <div class="flex-shrink-0 flex items-center gap-2">
                    <span class="w-8 h-8 rounded-lg bg-gradient-to-br from-pink-500 to-orange-400 flex items-center justify-center text-white font-bold text-xl shadow-lg shadow-pink-500/30">L</span>
                    <span class="font-bold text-xl tracking-tight bg-clip-text text-transparent bg-gradient-to-r from-pink-500 to-orange-400">Lectura Innov</span>
                </div>

                {{-- Actions --}}
                <div class="flex items-center gap-3">
                    
                    {{-- Favoris (Wishlist) --}}
                    <a href="{{ route('favoris.index') }}" title="Mes favoris" class="p-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors text-gray-500 dark:text-gray-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                    </a>

                    {{-- Notifications --}}
                    <div class="relative group" id="notification-container">
                        <button id="btn-notifications" class="relative p-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors text-gray-500 dark:text-gray-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                            @if(auth()->check() && auth()->user()->unreadNotifications->count() > 0)
                                <span class="badge-notif absolute top-1.5 right-1.5 w-2.5 h-2.5 bg-pink-500 rounded-full border-2 border-white dark:border-gray-950 animate-pulse"></span>
                            @endif
                        </button>
                        
                        {{-- Dropdown Notifications --}}
                        <div id="dropdown-notifications" class="absolute right-0 mt-2 w-80 bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-2xl shadow-xl shadow-gray-200/50 dark:shadow-black/50 opacity-0 invisible transform scale-95 transition-all duration-200 origin-top-right z-50">
                            <div class="p-4 border-b border-gray-100 dark:border-gray-800 flex justify-between items-center">
                                <h3 class="font-bold text-gray-900 dark:text-white">Notifications</h3>
                                <button id="btn-tout-lu" class="text-xs font-semibold text-pink-500 hover:text-pink-600 transition-colors">Tout marquer comme lu</button>
                            </div>
                            <div class="max-h-80 overflow-y-auto">
                                @forelse(auth()->check() ? auth()->user()->notifications()->take(5)->get() : [] as $notif)
                                    <div class="notif-item p-4 {{ $notif->read_at ? 'opacity-60' : 'bg-pink-50/50 dark:bg-pink-900/10' }} border-b border-gray-50 dark:border-gray-800 last:border-0 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors cursor-pointer" data-id="{{ $notif->id }}">
                                        <p class="text-xs text-gray-800 dark:text-gray-200 font-medium leading-relaxed">
                                            {!! strip_tags($notif->data['message']) !!}
                                        </p>
                                        <span class="text-[10px] text-gray-400 mt-2 block">{{ $notif->created_at->diffForHumans() }}</span>
                                    </div>
                                @empty
                                    <div class="p-6 text-center text-sm text-gray-500">Aucune notification pour le moment.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    {{-- Lien profil --}}
                    <a href="{{ route('profil.edit') }}" title="Mon profil" class="p-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors text-gray-500 dark:text-gray-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </a>
                    {{-- Avatar --}}
                    <div class="w-9 h-9 rounded-full border-2 border-pink-500 overflow-hidden shadow-lg shadow-pink-500/20 cursor-pointer hover:scale-105 transition-transform ml-2">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($utilisateur->name ?? 'U') }}&background=F472B6&color=fff"
                             alt="Profil de {{ $utilisateur->name ?? 'l\'utilisateur' }}">
                    </div>

                    {{-- Déconnexion --}}
                    <form method="POST" action="{{ route('logout') }}" class="ml-1 flex items-center">
                        @csrf
                        <button type="submit" title="Se déconnecter" class="p-2 rounded-full hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors text-gray-500 dark:text-gray-400 hover:text-red-500 dark:hover:text-red-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    {{-- ── Contenu principal ────────────────────────────────────────── --}}
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

        {{-- En-tête personnalisé --}}
        <header class="mb-12 flex flex-col md:flex-row md:items-end md:justify-between gap-6">
            <div>
                <p class="text-sm font-semibold text-pink-500 uppercase tracking-widest mb-2"></p>
                <h1 class="font-playfair text-4xl md:text-5xl font-bold tracking-tight mb-3 leading-tight">
                    Vos Recommandations<br>
                    <span class="bg-clip-text text-transparent bg-gradient-to-r from-pink-500 via-purple-500 to-orange-400">Personnalisées</span>
                </h1>
                <p class="text-base text-gray-600 dark:text-gray-400 max-w-xl">
                    Sélectionnés<strong class="text-gray-800 dark:text-gray-200">tags d'ambiance</strong> et les goûts de lecteurs aux profils similaires.
                </p>
            </div>

            {{-- Légende des scores --}}
            <div class="flex-shrink-0 bg-white/60 dark:bg-gray-900/60 backdrop-blur-sm border border-gray-200 dark:border-gray-700 rounded-2xl p-4 text-xs space-y-2 w-64">
                <p class="font-semibold text-gray-700 dark:text-gray-300 mb-3">📊 Décomposition du score</p>
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-gradient-to-r from-pink-500 to-rose-400 flex-shrink-0"></span>
                    <span class="text-gray-600 dark:text-gray-400"><strong>Contenu</strong> — similarité de tags</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-gradient-to-r from-indigo-500 to-blue-400 flex-shrink-0"></span>
                    <span class="text-gray-600 dark:text-gray-400"><strong>Collaboratif</strong> — voisins similaires</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-gradient-to-r from-purple-500 to-violet-400 flex-shrink-0"></span>
                    <span class="text-gray-600 dark:text-gray-400"><strong>Affinité finale</strong> — score combiné</span>
                </div>
            </div>
        </header>

        {{-- Alerte profil incomplet --}}
        @if(!$aProfilComplet)
        <div id="alerte-profil" role="alert"
             class="mb-8 flex items-start gap-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-2xl p-5">
            <span class="text-2xl flex-shrink-0">🎯</span>
            <div class="flex-grow">
                <p class="font-semibold text-amber-800 dark:text-amber-300">Complétez votre profil lecteur</p>
                <p class="text-sm text-amber-700 dark:text-amber-400 mt-1">
                    Renseignez vos tags d'ambiance préférés (ex : sombre, épique, romantique…) pour activer les recommandations personnalisées.
                </p>
            </div>
            <a href="#" class="flex-shrink-0 text-sm font-semibold text-amber-700 dark:text-amber-300 hover:underline whitespace-nowrap mt-0.5">
                Compléter →
            </a>
        </div>
        @endif

        {{-- ── Grille de livres ─────────────────────────────────────── --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">

            @forelse($livres as $livre)

            {{-- Carte livre --}}
            <article id="livre-{{ $livre['id'] }}"
                     class="group relative flex flex-col bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-3xl shadow-xl shadow-gray-200/50 dark:shadow-black/50 hover:shadow-2xl hover:shadow-pink-500/10 hover:-translate-y-2 transition-all duration-500 overflow-hidden">

                {{-- Badge affinité (coin haut-droit) --}}
                <div class="absolute top-3 right-3 z-20">
                    @php
                        $af = $livre['affinite'];
                        [$grad, $shadow] = match(true) {
                            $af >= 90 => ['from-green-400 to-emerald-500', 'shadow-emerald-500/40'],
                            $af >= 75 => ['from-orange-400 to-amber-500',  'shadow-orange-500/40'],
                            default   => ['from-blue-400 to-indigo-500',    'shadow-blue-500/40'],
                        };
                    @endphp
                    <div class="tooltip-group relative flex items-center">
                        <span class="absolute inset-0 rounded-full bg-gradient-to-br {{ $grad }} opacity-40 animate-ping [animation-duration:2s]"></span>
                        <span class="relative bg-gradient-to-br {{ $grad }} {{ $shadow }} shadow-md px-2.5 py-1 rounded-full text-[11px] font-bold text-white flex items-center gap-1 cursor-help">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/></svg>
                            {{ $af }}%
                        </span>
                        {{-- Tooltip détail --}}
                        <div class="tooltip-content absolute top-full right-0 mt-2 w-52 bg-gray-900 dark:bg-gray-800 border border-gray-700 rounded-xl p-3 text-xs z-50 shadow-xl">
                            <p class="font-semibold text-white mb-2">Décomposition</p>
                            <div class="space-y-1.5">
                                <div class="flex justify-between text-gray-300">
                                    <span>🎨 Contenu</span>
                                    <span class="font-mono font-bold text-pink-400">{{ $livre['score_contenu'] }}%</span>
                                </div>
                                <div class="flex justify-between text-gray-300">
                                    <span>👥 Collaboratif</span>
                                    <span class="font-mono font-bold text-indigo-400">+{{ $livre['bonus_collaboratif'] }}%</span>
                                </div>
                                @if($livre['utilisateurs_similaires'] > 0)
                                <p class="text-gray-500 text-[10px] pt-1 border-t border-gray-700">
                                    Basé sur {{ $livre['utilisateurs_similaires'] }} lecteur(s) similaire(s)
                                </p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Couverture --}}
                <div class="w-full aspect-[3/4] overflow-hidden relative bg-gray-100 dark:bg-gray-800 flex-shrink-0">
                    @if(!empty($livre['image']))
                        <img src="{{ $livre['image'] }}" alt="Couverture – {{ $livre['titre'] }}"
                             class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110 will-change-transform">
                    @else
                        {{-- Placeholder dégradé avec initiale --}}
                        @php
                            $palettes = [
                                ['from-pink-500 to-rose-600',     'bg-rose-500/20'],
                                ['from-purple-500 to-violet-600', 'bg-violet-500/20'],
                                ['from-orange-400 to-amber-600',  'bg-amber-500/20'],
                                ['from-teal-400 to-cyan-600',     'bg-cyan-500/20'],
                                ['from-indigo-400 to-blue-600',   'bg-blue-500/20'],
                            ];
                            $palette = $palettes[$livre['id'] % count($palettes)];
                        @endphp
                        <div class="w-full h-full flex items-center justify-center bg-gradient-to-br {{ $palette[0] }} transition-transform duration-700 group-hover:scale-110">
                            <span class="font-playfair text-6xl font-bold text-white/60 select-none">
                                {{ mb_strtoupper(mb_substr($livre['titre'], 0, 1)) }}
                            </span>
                        </div>
                    @endif

                    {{-- Overlay catégorie au survol --}}
                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end p-4">
                        <span class="text-white text-xs font-semibold uppercase tracking-widest backdrop-blur-sm bg-white/10 px-3 py-1 rounded-full border border-white/20">
                            {{ $livre['categorie'] }}
                        </span>
                    </div>
                </div>

                {{-- Corps de la carte --}}
                <div class="flex flex-col flex-grow p-5">

                    {{-- Tags d'ambiance --}}
                    @if(!empty($livre['tags_ambiance']))
                    <div class="flex flex-wrap gap-1.5 mb-3">
                        @foreach($livre['tags_ambiance'] as $tag)
                        <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-pink-50 dark:bg-pink-500/10 text-pink-600 dark:text-pink-400 border border-pink-200 dark:border-pink-500/30 uppercase tracking-wide">
                            {{ $tag }}
                        </span>
                        @endforeach
                    </div>
                    @endif

                    {{-- Titre + Auteur --}}
                    <h2 class="text-base font-bold mb-0.5 line-clamp-2 text-gray-900 dark:text-white group-hover:text-pink-500 transition-colors leading-snug">
                        {{ $livre['titre'] }}
                    </h2>
                    <p class="text-xs font-semibold text-purple-600 dark:text-purple-400 mb-3">
                        {{ $livre['auteur'] }}
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 line-clamp-3 flex-grow mb-4">
                        {{ $livre['description'] }}
                    </p>

                    {{-- Barre de score hybride --}}
                    <div class="mb-5 space-y-2">
                        {{-- Contenu --}}
                        <div>
                            <div class="flex justify-between text-[10px] text-gray-500 dark:text-gray-400 mb-1">
                                <span>🎨 Contenu</span>
                                <span class="font-mono font-bold text-pink-500">{{ $livre['score_contenu'] }}%</span>
                            </div>
                            <div class="h-1.5 bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden">
                                <div class="barre-score h-full rounded-full bg-gradient-to-r from-pink-500 to-rose-400"
                                     style="width: 0%"
                                     data-target="{{ $livre['score_contenu'] }}"></div>
                            </div>
                        </div>
                        {{-- Collaboratif --}}
                        <div>
                            <div class="flex justify-between text-[10px] text-gray-500 dark:text-gray-400 mb-1">
                                <span>👥 Collaboratif</span>
                                <span class="font-mono font-bold text-indigo-500">+{{ $livre['bonus_collaboratif'] }}%</span>
                            </div>
                            <div class="h-1.5 bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden">
                                <div class="barre-score h-full rounded-full bg-gradient-to-r from-indigo-500 to-blue-400"
                                     style="width: 0%"
                                     data-target="{{ $livre['bonus_collaboratif'] * 2 }}"></div>
                                {{-- ×2 car max théorique du bonus = 50% du score total → visuellement 100% --}}
                            </div>
                        </div>
                    </div>

                    {{-- Actions (Favori + Noter) --}}
                    <div class="pt-4 border-t border-gray-100 dark:border-gray-800 mt-auto flex gap-2">
                        <div class="flex-1">
                            <x-btn-favori :livre-id="$livre['id']" />
                        </div>
                        <button type="button" 
                                onclick="ouvrirModalAvis({{ $livre['id'] }}, '{{ addslashes($livre['titre']) }}')"
                                class="flex-1 inline-flex items-center justify-center p-2 rounded-xl border border-gray-200 dark:border-gray-700 font-semibold text-xs text-gray-700 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400 hover:border-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition-all">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                            Noter
                        </button>
                    </div>
                </div>
            </article>

            @empty

            {{-- État vide --}}
            <div class="col-span-full py-24 text-center flex flex-col items-center">
                <div class="w-24 h-24 bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-800 dark:to-gray-900 rounded-full flex items-center justify-center mb-6 animate-float shadow-xl">
                    <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200 mb-2">Aucun livre suggéré pour l'instant</h2>
                <p class="text-gray-500 dark:text-gray-400 max-w-md mb-6">
                    Complétez votre profil lecteur avec des tags d'ambiance (ex : sombre, épique, romantique…) pour que l'algorithme puisse vous proposer des lectures idéales.
                </p>
                <a href="#" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-gradient-to-r from-pink-500 to-orange-400 text-white font-semibold text-sm shadow-lg shadow-pink-500/30 hover:shadow-xl hover:scale-105 transition-all duration-300">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Compléter mon profil
                </a>
            </div>

            @endforelse
        </div>

    </main>

    {{-- ── Modal d'Avis ────────────────────────────────────────────────── --}}
    <div id="modal-avis" class="fixed inset-0 z-[60] flex items-center justify-center opacity-0 invisible transition-all duration-300">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" onclick="fermerModalAvis()"></div>
        
        <!-- Contenu Modal -->
        <div class="relative bg-white dark:bg-gray-900 w-full max-w-md mx-4 rounded-3xl shadow-2xl border border-gray-200 dark:border-gray-800 p-6 transform scale-95 transition-transform duration-300" id="modal-content">
            <button type="button" onclick="fermerModalAvis()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
            
            <h3 class="text-xl font-bold mb-1 dark:text-white">Évaluer ce livre</h3>
            <p id="modal-livre-titre" class="text-sm font-semibold text-indigo-500 mb-6 truncate"></p>

            <form id="form-avis" onsubmit="soumettreAvis(event)">
                <input type="hidden" id="avis-livre-id">
                
                <!-- Étoiles -->
                <div class="flex justify-center gap-2 mb-6" id="star-rating">
                    @for($i=1; $i<=5; $i++)
                    <button type="button" data-val="{{ $i }}" class="star-btn text-gray-300 dark:text-gray-700 hover:scale-110 transition-transform focus:outline-none">
                        <svg class="w-10 h-10" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    </button>
                    @endfor
                </div>
                <input type="hidden" id="avis-note" required>

                <!-- Commentaire -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Votre avis (optionnel)</label>
                    <textarea id="avis-commentaire" rows="3" class="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 transition-colors p-3 text-sm" placeholder="Qu'avez-vous pensé de l'ambiance, de l'intrigue ?"></textarea>
                </div>

                <button type="submit" id="btn-submit-avis" class="w-full py-3 px-4 bg-gradient-to-r from-indigo-500 to-purple-500 text-white font-bold rounded-xl shadow-lg hover:shadow-indigo-500/30 transition-all hover:-translate-y-0.5" disabled>
                    Enregistrer mon avis
                </button>
            </form>
        </div>
    </div>

    {{-- ── Footer ───────────────────────────────────────────────────── --}}
    <footer class="mt-24 py-8 border-t border-gray-200 dark:border-gray-800">
        <div class="max-w-7xl mx-auto px-4 text-center text-sm text-gray-500 dark:text-gray-400">
            &copy; {{ date('Y') }} Lectura Innov. Conçu avec passion.
        </div>
    </footer>

</div>

{{-- ── Toast notification ───────────────────────────────────────── --}}
<div id="toast" role="status" aria-live="polite"
     class="fixed bottom-6 right-6 z-50 flex items-center gap-3 bg-gray-900 text-white text-sm font-medium px-5 py-3 rounded-2xl shadow-2xl translate-y-16 opacity-0 transition-all duration-500 pointer-events-none max-w-xs">
    <span id="toast-icone" class="text-lg">✓</span>
    <span id="toast-message">Message</span>
</div>

{{-- ── Scripts ──────────────────────────────────────────────────── --}}
<script>
document.addEventListener('DOMContentLoaded', () => {

    // ── 1. Animation des barres de score au chargement ────────────
    const barres = document.querySelectorAll('.barre-score[data-target]');
    const animerBarres = () => {
        barres.forEach(barre => {
            const rect = barre.closest('article')?.getBoundingClientRect();
            if (!rect) return;
            if (rect.top < window.innerHeight - 50) {
                barre.style.width = Math.min(parseFloat(barre.dataset.target), 100) + '%';
                barre.removeAttribute('data-target'); // n'animer qu'une fois
            }
        });
    };
    window.addEventListener('scroll', animerBarres, { passive: true });
    setTimeout(animerBarres, 200); // déclencher au chargement initial

    // ── 2. Initialisation de l'état des boutons favoris ───────────
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    fetch('/favoris', { headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken } })
        .then(r => r.ok ? r.json() : Promise.resolve({ favoris: [] }))
        .then(({ favoris }) => {
            favoris.forEach(id => activerBouton(id, true));
        })
        .catch(() => {}); // silencieux si non connecté

    // ── 3. Toggle favori via AJAX ──────────────────────────────────
    document.addEventListener('click', async (e) => {
        const btn = e.target.closest('.btn-favori');
        if (!btn) return;

        const livreId = btn.dataset.livreId;
        if (!livreId) return;

        btn.disabled = true;
        btn.style.opacity = '0.6';

        try {
            const response = await fetch(`/favoris/${livreId}`, {
                method: 'POST',
                headers: {
                    'Accept':       'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
            });

            if (!response.ok) {
                if (response.status === 401) {
                    afficherToast('🔒 Connectez-vous pour gérer vos favoris.', false);
                    return;
                }
                throw new Error('Erreur réseau');
            }

            const data = await response.json();
            activerBouton(livreId, data.estFavori);
            afficherToast(data.estFavori ? '❤️ ' + data.message : '🤍 ' + data.message, data.estFavori);

        } catch {
            afficherToast('⚠️ Une erreur est survenue.', false);
        } finally {
            btn.disabled = false;
            btn.style.opacity = '';
        }
    });

    // ── Helpers ───────────────────────────────────────────────────

    /** Met à jour l'apparence visuelle du bouton favori */
    function activerBouton(livreId, estFavori) {
        const btn = document.querySelector(`.btn-favori[data-livre-id="${livreId}"]`);
        if (!btn) return;

        const icone = btn.querySelector('.icone-coeur');
        const label = btn.querySelector('.label-favori');

        if (estFavori) {
            btn.classList.add('est-favori');
            if (icone) icone.setAttribute('fill', 'currentColor');
            if (label) label.textContent = 'Retiré des Favoris';
        } else {
            btn.classList.remove('est-favori');
            if (icone) icone.setAttribute('fill', 'none');
            if (label) label.textContent = 'Ajouter aux Favoris';
        }
    }

    /** Affiche un toast en bas à droite de l'écran */
    function afficherToast(message, succes = true) {
        const toast   = document.getElementById('toast');
        const icone   = document.getElementById('toast-icone');
        const msgEl   = document.getElementById('toast-message');

        icone.textContent  = succes ? '✓' : '⚠';
        msgEl.textContent  = message;
        toast.className    = toast.className.replace(/translate-y-16|opacity-0/, '').trim()
            + ' translate-y-0 opacity-100';

        clearTimeout(toast._timer);
        toast._timer = setTimeout(() => {
            toast.classList.replace('translate-y-0', 'translate-y-16');
            toast.classList.replace('opacity-100', 'opacity-0');
        }, 3500);
    }
    
    // ── 4. Gestion UX du Dropdown de Notifications ────────────────
    const btnNotif = document.getElementById('btn-notifications');
    const dropdownNotif = document.getElementById('dropdown-notifications');
    
    if (btnNotif && dropdownNotif) {
        btnNotif.addEventListener('click', (e) => {
            e.stopPropagation();
            const isOpen = dropdownNotif.classList.contains('opacity-100');
            if (isOpen) {
                dropdownNotif.classList.remove('opacity-100', 'visible', 'scale-100');
                dropdownNotif.classList.add('opacity-0', 'invisible', 'scale-95');
            } else {
                dropdownNotif.classList.remove('opacity-0', 'invisible', 'scale-95');
                dropdownNotif.classList.add('opacity-100', 'visible', 'scale-100');
            }
        });
        
        document.addEventListener('click', (e) => {
            if (!dropdownNotif.contains(e.target) && !btnNotif.contains(e.target)) {
                dropdownNotif.classList.remove('opacity-100', 'visible', 'scale-100');
                dropdownNotif.classList.add('opacity-0', 'invisible', 'scale-95');
            }
        });
    }

    // ── 5. AJAX Notifications (Marquer comme lu) ──────────────────
    const btnToutLu = document.getElementById('btn-tout-lu');
    if (btnToutLu) {
        btnToutLu.addEventListener('click', async (e) => {
            e.stopPropagation();
            try {
                await fetch('/notifications/lues', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }
                });
                document.querySelectorAll('.badge-notif').forEach(b => b.remove());
                document.querySelectorAll('.notif-item').forEach(el => {
                    el.classList.add('opacity-60');
                    el.classList.remove('bg-pink-50/50', 'dark:bg-pink-900/10');
                });
            } catch (err) {}
        });
    }

    document.querySelectorAll('.notif-item').forEach(item => {
        item.addEventListener('click', async (e) => {
            if (item.classList.contains('opacity-60')) return; // Déjà lu
            const id = item.dataset.id;
            try {
                await fetch(`/notifications/${id}`, {
                    method: 'PATCH',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }
                });
                item.classList.add('opacity-60');
                item.classList.remove('bg-pink-50/50', 'dark:bg-pink-900/10');
                // Optionnel: décrémenter le badge
            } catch (err) {}
        });
    });

    // ── 6. Gestion du Modal d'Avis (Reviews) ──────────────────────────
    const modalAvis = document.getElementById('modal-avis');
    const modalContent = document.getElementById('modal-content');
    const formAvis = document.getElementById('form-avis');
    const starBtns = document.querySelectorAll('.star-btn');
    const noteInput = document.getElementById('avis-note');
    const btnSubmitAvis = document.getElementById('btn-submit-avis');

    window.ouvrirModalAvis = async (livreId, titre) => {
        document.getElementById('avis-livre-id').value = livreId;
        document.getElementById('modal-livre-titre').textContent = titre;
        
        // Reset form
        noteInput.value = '';
        document.getElementById('avis-commentaire').value = '';
        mettreAJourEtoiles(0);
        btnSubmitAvis.disabled = true;

        // Fetch existing review if any
        try {
            const res = await fetch(`/avis/${livreId}`, { headers: { 'Accept': 'application/json' }});
            if (res.ok) {
                const data = await res.json();
                if (data.avis) {
                    noteInput.value = data.avis.note;
                    document.getElementById('avis-commentaire').value = data.avis.commentaire || '';
                    mettreAJourEtoiles(data.avis.note);
                    btnSubmitAvis.disabled = false;
                    btnSubmitAvis.textContent = "Mettre à jour mon avis";
                } else {
                    btnSubmitAvis.textContent = "Enregistrer mon avis";
                }
            }
        } catch (e) {}

        modalAvis.classList.remove('invisible', 'opacity-0');
        modalContent.classList.remove('scale-95');
    };

    window.fermerModalAvis = () => {
        modalAvis.classList.add('invisible', 'opacity-0');
        modalContent.classList.add('scale-95');
    };

    // Hover et Clic sur étoiles
    starBtns.forEach(btn => {
        btn.addEventListener('mouseenter', () => {
            if (!noteInput.value) mettreAJourEtoiles(btn.dataset.val, true);
        });
        btn.addEventListener('mouseleave', () => {
            if (!noteInput.value) mettreAJourEtoiles(0);
        });
        btn.addEventListener('click', () => {
            noteInput.value = btn.dataset.val;
            mettreAJourEtoiles(noteInput.value);
            btnSubmitAvis.disabled = false;
        });
    });

    function mettreAJourEtoiles(valeur, hover = false) {
        starBtns.forEach(b => {
            const v = parseInt(b.dataset.val);
            if (v <= valeur) {
                b.classList.remove('text-gray-300', 'dark:text-gray-700');
                b.classList.add(hover ? 'text-indigo-300' : 'text-indigo-500');
            } else {
                b.classList.add('text-gray-300', 'dark:text-gray-700');
                b.classList.remove('text-indigo-300', 'text-indigo-500');
            }
        });
    }

    // Soumission du formulaire
    window.soumettreAvis = async (e) => {
        e.preventDefault();
        if (!noteInput.value) return;
        
        const livreId = document.getElementById('avis-livre-id').value;
        const note = noteInput.value;
        const commentaire = document.getElementById('avis-commentaire').value;

        btnSubmitAvis.disabled = true;
        btnSubmitAvis.innerHTML = '<span class="animate-pulse">Enregistrement...</span>';

        try {
            const response = await fetch(`/avis/${livreId}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify({ note, commentaire })
            });

            if (response.ok) {
                const data = await response.json();
                afficherToast('⭐ ' + data.message, true);
                fermerModalAvis();
            } else {
                afficherToast('⚠️ Erreur lors de l\'enregistrement', false);
                btnSubmitAvis.disabled = false;
                btnSubmitAvis.textContent = "Réessayer";
            }
        } catch (err) {
            afficherToast('⚠️ Erreur réseau', false);
            btnSubmitAvis.disabled = false;
        }
    };

});
</script>

</body>
</html>
