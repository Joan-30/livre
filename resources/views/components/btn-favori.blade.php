@props(['livreId'])

{{--
    Bouton favori interactif.
    Géré par le script global dans tableau_de_bord.blade.php.
    Classes CSS attendues : .btn-favori, .icone-coeur, .label-favori
    Styles "est-favori" appliqués dynamiquement via JavaScript.
--}}

<style>
    .btn-favori {
        position: relative;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        padding: 0.5rem 1rem;
        border-radius: 0.75rem;
        font-size: 0.8125rem;
        font-weight: 600;
        cursor: pointer;
        border: 1.5px solid transparent;
        background-clip: padding-box;
        transition: all 0.25s cubic-bezier(0.22, 1, 0.36, 1);
        outline: none;
        /* Bordure dégradée */
        background-color: transparent;
        border-color: #f472b6; /* pink-400 */
        color: #f472b6;
    }
    .btn-favori:hover,
    .btn-favori.est-favori {
        background: linear-gradient(135deg, #ec4899, #f97316); /* pink-500 → orange-500 */
        border-color: transparent;
        color: #ffffff;
        box-shadow: 0 6px 20px -4px rgba(236,72,153,0.45);
        transform: translateY(-1px);
    }
    .btn-favori:active { transform: scale(0.97); }
    .btn-favori:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }
</style>

<button type="button"
        id="btn-favori-{{ $livreId }}"
        class="btn-favori"
        data-livre-id="{{ $livreId }}"
        aria-pressed="false"
        aria-label="Ajouter le livre aux favoris">

    {{-- Icône cœur --}}
    <svg class="icone-coeur w-4 h-4 mr-2 transition-all duration-300" fill="none" stroke="currentColor"
         stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
        <path stroke-linecap="round" stroke-linejoin="round"
              d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
    </svg>

    <span class="label-favori">Ajouter aux Favoris</span>
</button>
