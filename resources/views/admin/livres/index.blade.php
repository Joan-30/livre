<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lectura Innov – Gestion du Catalogue</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700|playfair-display:700" rel="stylesheet" />

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
    @endif

    <style>
        .font-playfair { font-family: 'Playfair Display', serif; }
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-950 text-gray-800 dark:text-gray-100 font-sans antialiased min-h-screen">

<nav class="sticky top-0 z-50 backdrop-blur-md bg-white/70 dark:bg-gray-950/70 border-b border-gray-200 dark:border-gray-800">
    <div class="max-w-7xl mx-auto px-4 h-16 flex items-center justify-between">
        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2 group">
            <span class="w-8 h-8 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold text-xl shadow-lg shadow-indigo-500/30">A</span>
            <span class="font-bold text-lg tracking-tight bg-clip-text text-transparent bg-gradient-to-r from-indigo-500 to-purple-600">Administration</span>
        </a>

        <div class="flex items-center gap-4">
            <a href="{{ route('admin.dashboard') }}" class="text-sm font-semibold text-gray-500 hover:text-indigo-500 transition-colors">Retour Dashboard</a>
            <span class="text-sm font-semibold text-gray-500 dark:text-gray-400 border border-gray-200 dark:border-gray-700 px-3 py-1 rounded-full bg-gray-50 dark:bg-gray-800">
                Mode Admin
            </span>
        </div>
    </div>
</nav>

<main class="max-w-7xl mx-auto px-4 py-12">
    <header class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div>
            <h1 class="font-playfair text-3xl font-bold tracking-tight mb-2 text-transparent bg-clip-text bg-gradient-to-r from-indigo-500 to-purple-500">
                L'Inventaire Littéraire
            </h1>
            <p class="text-gray-500 dark:text-gray-400">Gérez les {{ $livres->total() }} livres disponibles dans le moteur d'affinité.</p>
        </div>
        <a href="{{ route('admin.livres.create') }}" class="px-6 py-2.5 bg-gradient-to-r from-indigo-500 to-purple-600 text-white font-bold rounded-xl shadow-lg shadow-indigo-500/30 hover:-translate-y-0.5 transition-all text-sm flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Nouveau Livre
        </a>
    </header>

    @if(session('success'))
        <div class="mb-8 p-4 bg-green-50 dark:bg-green-900/20 border-l-4 border-green-500 rounded-r-xl text-green-700 dark:text-green-400 font-medium">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl shadow-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm border-collapse">
                <thead>
                    <tr class="bg-gray-50/50 dark:bg-gray-800/50 border-b border-gray-200 dark:border-gray-800 text-gray-500 dark:text-gray-400 font-semibold">
                        <th class="p-4 pl-6 whitespace-nowrap">Titre du livre</th>
                        <th class="p-4">Auteur</th>
                        <th class="p-4">Catégorie</th>
                        <th class="p-4">Tags (Algorithme)</th>
                        <th class="p-4 pr-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($livres as $livre)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors group">
                            <td class="p-4 pl-6 font-semibold text-gray-900 dark:text-white">
                                {{ $livre->titre }}
                            </td>
                            <td class="p-4 text-indigo-500 font-medium">{{ $livre->auteur->nom }}</td>
                            <td class="p-4 text-gray-500 dark:text-gray-400">{{ $livre->categorie->nom }}</td>
                            <td class="p-4">
                                <div class="flex flex-wrap gap-1">
                                    @foreach($livre->tags_ambiance ?? [] as $tag)
                                        <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400">
                                            {{ $tag }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="p-4 pr-6 text-right">
                                <div class="flex justify-end items-center gap-2 opacity-100 md:opacity-0 group-hover:opacity-100 transition-opacity">
                                    <a href="{{ route('admin.livres.edit', $livre) }}" class="p-1.5 text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors" title="Modifier">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                    <form action="{{ route('admin.livres.destroy', $livre) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer {{ addslashes($livre->titre) }} ?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="p-1.5 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors" title="Supprimer">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-8 text-center text-gray-500">Aucun livre dans le catalogue.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($livres->hasPages())
            <div class="p-4 border-t border-gray-100 dark:border-gray-800">
                {{ $livres->links() }}
            </div>
        @endif
    </div>
</main>

</body>
</html>
