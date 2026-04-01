<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lectura Innov – Éditer un Livre</title>

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
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-950 text-gray-800 dark:text-gray-100 font-sans antialiased min-h-screen">

<nav class="sticky top-0 z-50 backdrop-blur-md bg-white/70 dark:bg-gray-950/70 border-b border-gray-200 dark:border-gray-800">
    <div class="max-w-4xl mx-auto px-4 h-16 flex items-center justify-between">
        <a href="{{ route('admin.livres.index') }}" class="flex items-center gap-2 group">
            <span class="w-8 h-8 rounded-lg bg-gray-200 dark:bg-gray-800 flex items-center justify-center text-gray-500 group-hover:bg-indigo-500 group-hover:text-white transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </span>
            <span class="font-bold text-gray-700 dark:text-gray-200 group-hover:text-indigo-500 transition-colors">Retour au Catalogue</span>
        </a>
    </div>
</nav>

<main class="max-w-4xl mx-auto px-4 py-12">
    <header class="mb-10">
        <h1 class="font-playfair text-3xl font-bold tracking-tight mb-2 text-transparent bg-clip-text bg-gradient-to-r from-blue-500 to-indigo-500">
            Modifier l'œuvre : {{ $livre->titre }}
        </h1>
        <p class="text-gray-500 dark:text-gray-400">Ajustez les paramètres et les tags de recommandation de ce livre.</p>
    </header>

    <div class="glass-panel border border-white/20 dark:border-gray-800/80 rounded-3xl p-8 shadow-2xl dark:shadow-black/50">
        <form method="POST" action="{{ route('admin.livres.update', $livre) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Titre --}}
                <div class="col-span-1 md:col-span-2">
                    <label for="titre" class="block text-sm font-semibold mb-2">Titre du livre</label>
                    <input type="text" name="titre" id="titre" value="{{ old('titre', $livre->titre) }}" required
                           class="w-full h-12 px-4 bg-white/50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-800 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none transition-all dark:text-white"
                           placeholder="Le Seigneur des Anneaux">
                    @error('titre') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Auteur --}}
                <div>
                    <label for="auteur_id" class="block text-sm font-semibold mb-2">Auteur</label>
                    <select name="auteur_id" id="auteur_id" required
                            class="w-full h-12 px-4 bg-white/50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-800 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none transition-all dark:text-white">
                        <option value="">Sélectionnez un auteur</option>
                        @foreach($auteurs as $auteur)
                            <option value="{{ $auteur->id }}" {{ old('auteur_id', $livre->auteur_id) == $auteur->id ? 'selected' : '' }}>{{ $auteur->nom }}</option>
                        @endforeach
                    </select>
                    @error('auteur_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Catégorie --}}
                <div>
                    <label for="categorie_id" class="block text-sm font-semibold mb-2">Catégorie</label>
                    <select name="categorie_id" id="categorie_id" required
                            class="w-full h-12 px-4 bg-white/50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-800 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none transition-all dark:text-white">
                        <option value="">Sélectionnez une catégorie</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('categorie_id', $livre->categorie_id) == $cat->id ? 'selected' : '' }}>{{ $cat->nom }}</option>
                        @endforeach
                    </select>
                    @error('categorie_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Tags d'ambiance --}}
                <div class="col-span-1 md:col-span-2">
                    <label for="tags_ambiance" class="block text-sm font-semibold mb-2">
                        Tags d'ambiance <span class="text-indigo-500 font-normal ml-2">(Essentiel pour le moteur d'affinité)</span>
                    </label>
                    <div class="flex items-start gap-4">
                        @php
                            $tagsStr = is_array($livre->tags_ambiance) ? implode(', ', $livre->tags_ambiance) : '';
                        @endphp
                        <input type="text" name="tags_ambiance" id="tags_ambiance" value="{{ old('tags_ambiance', $tagsStr) }}" required
                               class="flex-1 h-12 px-4 bg-white/50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-800 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none transition-all dark:text-white font-mono text-sm"
                               placeholder="sombre, épique, philosophique">
                    </div>
                    <p class="text-xs text-gray-500 mt-2">Séparez les tags par des virgules (,). Ex: <code class="bg-gray-100 dark:bg-gray-800 px-1 rounded">joyeux, romantique, contemplatif</code>.</p>
                    @error('tags_ambiance') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Description --}}
                <div class="col-span-1 md:col-span-2">
                    <label for="description" class="block text-sm font-semibold mb-2">Description / Sinopsis</label>
                    <textarea name="description" id="description" rows="4" required
                              class="w-full p-4 bg-white/50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-800 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none transition-all dark:text-white resize-none"
                              placeholder="Résumé du livre...">{{ old('description', $livre->description) }}</textarea>
                    @error('description') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="flex justify-end pt-4 border-t border-gray-200 dark:border-gray-800">
                <button type="submit" class="px-8 py-3 bg-gradient-to-r from-blue-500 to-indigo-600 text-white font-bold rounded-xl shadow-lg shadow-indigo-500/30 hover:-translate-y-0.5 hover:shadow-xl transition-all active:scale-95 text-sm">
                    Mettre à jour le livre
                </button>
            </div>
        </form>
    </div>
</main>

</body>
</html>
