<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Lectura Innov – Mon profil lecteur</title>
    <meta name="description" content="Renseignez vos goûts d'ambiance pour personnaliser vos recommandations.">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700|playfair-display:700" rel="stylesheet" />

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
    @endif

    <style>
        .font-playfair { font-family: 'Playfair Display', serif; }

        /* Tag toggle */
        .tag-btn { transition: all .2s cubic-bezier(.22,1,.36,1); }
        .tag-btn.actif {
            background: linear-gradient(135deg, #ec4899, #f97316);
            border-color: transparent;
            color: #fff;
            box-shadow: 0 4px 14px -4px rgba(236,72,153,.5);
            transform: scale(1.05);
        }
        input[type="radio"]:checked + label { /* niveau complexité */
            background: linear-gradient(135deg, #8b5cf6, #6366f1);
            color: #fff;
            border-color: transparent;
            box-shadow: 0 4px 14px -4px rgba(139,92,246,.5);
        }
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-950 text-gray-800 dark:text-gray-100 font-sans antialiased min-h-screen">

{{-- Blobs --}}
<div aria-hidden="true" class="fixed inset-0 -z-10 overflow-hidden pointer-events-none">
    <div class="absolute -top-40 -left-20 w-[60%] h-[60%] rounded-full bg-gradient-to-br from-purple-500/20 to-pink-500/20 blur-[120px]"></div>
    <div class="absolute bottom-0 right-0 w-[50%] h-[50%] rounded-full bg-gradient-to-tl from-orange-400/20 to-pink-500/20 blur-[100px]"></div>
</div>

{{-- Navbar --}}
<nav class="sticky top-0 z-50 backdrop-blur-md bg-white/70 dark:bg-gray-950/70 border-b border-gray-200 dark:border-gray-800">
    <div class="max-w-3xl mx-auto px-4 h-16 flex items-center justify-between">
        <a href="{{ route('tableau-de-bord') }}" class="flex items-center gap-2 group">
            <span class="w-8 h-8 rounded-lg bg-gradient-to-br from-pink-500 to-orange-400 flex items-center justify-center text-white font-bold text-xl shadow-lg shadow-pink-500/30">L</span>
            <span class="font-bold text-lg tracking-tight bg-clip-text text-transparent bg-gradient-to-r from-pink-500 to-orange-400">Lectura Innov</span>
        </a>
        <a href="{{ route('tableau-de-bord') }}"
           class="text-sm text-gray-500 dark:text-gray-400 hover:text-pink-500 transition-colors flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Retour aux recommandations
        </a>
    </div>
</nav>

<main class="max-w-3xl mx-auto px-4 py-12">

    {{-- En-tête --}}
    <header class="mb-10">
        <p class="text-xs font-semibold text-pink-500 uppercase tracking-widest mb-2">Configuration personnelle</p>
        <h1 class="font-playfair text-4xl font-bold mb-3">Mon Profil Lecteur</h1>
        <p class="text-gray-500 dark:text-gray-400">
            Ces préférences alimentent notre algorithme hybride. Plus elles sont précises, plus vos recommandations seront pertinentes.
        </p>
    </header>

    {{-- Message de succès --}}
    @if(session('succes'))
    <div role="alert"
         class="mb-8 flex items-center gap-3 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-2xl px-5 py-4 text-emerald-700 dark:text-emerald-300 text-sm font-medium">
        <span class="text-xl">🎉</span>
        {{ session('succes') }}
    </div>
    @endif

    {{-- Formulaire --}}
    <form id="form-profil"
          method="POST"
          action="{{ route('profil.update') }}"
          class="space-y-8">
        @csrf
        @method('PUT')

        {{-- ── Section 1 : Tags d'ambiance ── --}}
        <section class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-3xl p-6 shadow-xl dark:shadow-black/50">

            <div class="flex items-center gap-3 mb-6">
                <span class="w-10 h-10 rounded-xl bg-gradient-to-br from-pink-500 to-rose-400 flex items-center justify-center text-xl shadow-lg shadow-pink-500/30">🎨</span>
                <div>
                    <h2 class="font-bold text-gray-900 dark:text-white">Tags d'Ambiance</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Choisissez jusqu'à <strong>5 ambiances</strong> qui vous correspondent.</p>
                </div>
            </div>

            @error('preferences_ambiance')
            <p class="mb-4 text-sm text-red-500 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl px-4 py-2">
                {{ $message }}
            </p>
            @enderror

            <div id="tags-container" class="flex flex-wrap gap-3">
                @foreach($tagsDisponibles as $tag)
                @php
                    $actif = in_array($tag, old('preferences_ambiance', $profil?->preferences_ambiance ?? []));
                @endphp
                <button type="button"
                        data-tag="{{ $tag }}"
                        class="tag-btn px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 text-sm font-semibold text-gray-600 dark:text-gray-300 hover:border-pink-400 hover:text-pink-500 capitalize {{ $actif ? 'actif' : '' }}"
                        aria-pressed="{{ $actif ? 'true' : 'false' }}">
                    {{ $tag }}
                </button>
                @endforeach
            </div>

            {{-- Inputs cachés (remplis par JS) --}}
            <div id="tags-inputs" class="hidden">
                @foreach(old('preferences_ambiance', $profil?->preferences_ambiance ?? []) as $t)
                <input type="checkbox" name="preferences_ambiance[]" value="{{ $t }}" checked>
                @endforeach
            </div>

            {{-- Compteur --}}
            <p class="mt-4 text-xs text-gray-400 dark:text-gray-500">
                <span id="compteur-tags">{{ count(old('preferences_ambiance', $profil?->preferences_ambiance ?? [])) }}</span>/5 tags sélectionnés
            </p>
        </section>

        {{-- ── Section 2 : Niveau de complexité ── --}}
        <section class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-3xl p-6 shadow-xl dark:shadow-black/50">

            <div class="flex items-center gap-3 mb-6">
                <span class="w-10 h-10 rounded-xl bg-gradient-to-br from-purple-500 to-violet-400 flex items-center justify-center text-xl shadow-lg shadow-purple-500/30">🧩</span>
                <div>
                    <h2 class="font-bold text-gray-900 dark:text-white">Niveau de Complexité</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Le style de lecture qui vous convient le mieux.</p>
                </div>
            </div>

            @error('niveau_complexite')
            <p class="mb-4 text-sm text-red-500 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl px-4 py-2">{{ $message }}</p>
            @enderror

            <div class="flex flex-wrap gap-3">
                @foreach($niveaux as $niveau)
                @php
                    $niveauIcons = ['facile' => '🌱', 'moyen' => '⚡', 'difficile' => '🔥'];
                    $checked = old('niveau_complexite', $profil?->niveau_complexite) === $niveau;
                @endphp
                <div>
                    <input type="radio" id="niveau-{{ $niveau }}" name="niveau_complexite"
                           value="{{ $niveau }}" {{ $checked ? 'checked' : '' }}
                           class="sr-only">
                    <label for="niveau-{{ $niveau }}"
                           class="cursor-pointer flex items-center gap-2 px-5 py-3 rounded-xl border border-gray-200 dark:border-gray-700 text-sm font-semibold text-gray-600 dark:text-gray-300 hover:border-purple-400 hover:text-purple-500 transition-all duration-200 select-none capitalize">
                        <span>{{ $niveauIcons[$niveau] }}</span>
                        <span>{{ ucfirst($niveau) }}</span>
                    </label>
                </div>
                @endforeach
            </div>
        </section>

        {{-- ── Bouton de sauvegarde ── --}}
        <div class="flex items-center justify-between">
            <a href="{{ route('tableau-de-bord') }}"
               class="text-sm text-gray-500 dark:text-gray-400 hover:text-pink-500 transition-colors">
                Annuler
            </a>
            <button type="submit"
                    id="btn-sauvegarder"
                    class="inline-flex items-center gap-2 px-8 py-3 rounded-xl bg-gradient-to-r from-pink-500 to-orange-400 text-white font-bold text-sm shadow-lg shadow-pink-500/30 hover:shadow-xl hover:scale-105 active:scale-95 transition-all duration-300 focus:outline-none focus:ring-4 focus:ring-pink-500/30">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Sauvegarder mon profil
            </button>
        </div>
    </form>

    {{-- ── Aperçu du profil actuel ── --}}
    @if($profil && !empty($profil->preferences_ambiance))
    <aside class="mt-10 bg-gradient-to-br from-white to-gray-50 dark:from-gray-900 dark:to-gray-950 border border-gray-100 dark:border-gray-800 rounded-3xl p-6 shadow-lg">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-4">📊 Votre profil actuel</p>
        <div class="grid grid-cols-2 gap-4 text-sm">
            <div>
                <p class="text-gray-500 dark:text-gray-400 mb-2">Tags d'ambiance</p>
                <div class="flex flex-wrap gap-1.5">
                    @foreach($profil->preferences_ambiance as $t)
                    <span class="px-2.5 py-0.5 rounded-full bg-pink-50 dark:bg-pink-500/10 text-pink-600 dark:text-pink-400 border border-pink-200 dark:border-pink-500/30 text-xs font-semibold capitalize">
                        {{ $t }}
                    </span>
                    @endforeach
                </div>
            </div>
            <div>
                <p class="text-gray-500 dark:text-gray-400 mb-2">Niveau de complexité</p>
                <span class="font-semibold text-gray-800 dark:text-gray-200 capitalize">
                    {{ $profil->niveau_complexite ?? '—' }}
                </span>
            </div>
        </div>
    </aside>
    @endif

</main>

{{-- ── Script gestion des tags ── --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
    const MAX_TAGS = 5;
    const container = document.getElementById('tags-container');
    const inputsDiv = document.getElementById('tags-inputs');
    const compteur  = document.getElementById('compteur-tags');

    function getSelectionnes() {
        return [...container.querySelectorAll('.tag-btn.actif')].map(b => b.dataset.tag);
    }

    function mettreAJourInputs() {
        inputsDiv.innerHTML = '';
        getSelectionnes().forEach(tag => {
            const inp = document.createElement('input');
            inp.type    = 'checkbox';
            inp.name    = 'preferences_ambiance[]';
            inp.value   = tag;
            inp.checked = true;
            inputsDiv.appendChild(inp);
        });
        compteur.textContent = getSelectionnes().length;
    }

    container.addEventListener('click', (e) => {
        const btn = e.target.closest('.tag-btn');
        if (!btn) return;

        const estActif = btn.classList.contains('actif');

        if (!estActif && getSelectionnes().length >= MAX_TAGS) {
            btn.animate([
                { transform: 'translateX(-4px)' },
                { transform: 'translateX(4px)' },
                { transform: 'translateX(-4px)' },
                { transform: 'translateX(0)' },
            ], { duration: 300 });
            return; // max atteint
        }

        btn.classList.toggle('actif');
        btn.setAttribute('aria-pressed', btn.classList.contains('actif') ? 'true' : 'false');
        mettreAJourInputs();
    });

    // Initialisation (état depuis le Blade)
    mettreAJourInputs();
});
</script>

</body>
</html>
