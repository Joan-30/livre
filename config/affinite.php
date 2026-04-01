<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Seuil de similarité Jaccard
    |--------------------------------------------------------------------------
    | Valeur minimale de similarité entre deux profils lecteurs pour que
    | l'utilisateur voisin soit inclus dans le filtrage collaboratif.
    | Plage : 0.0 (aucune exigence) – 1.0 (profils identiques uniquement).
    | Recommandé : 0.15 – 0.35
    */
    'seuil_similarite' => env('AFFINITE_SEUIL_SIMILARITE', 0.2),

    /*
    |--------------------------------------------------------------------------
    | Poids du bonus collaboratif
    |--------------------------------------------------------------------------
    | Multiplicateur appliqué au score collaboratif dans le score final.
    | Plus cette valeur est élevée, plus l'algorithme s'appuie sur les
    | comportements des voisins plutôt que sur les tags du livre seul.
    | Exemples :
    |   0.0 → filtrage uniquement par contenu (tags)
    |   0.5 → hybride équilibré (défaut)
    |   1.0 → filtrage fortement collaboratif
    */
    'poids_collaboratif' => env('AFFINITE_POIDS_COLLABORATIF', 0.5),

    /*
    |--------------------------------------------------------------------------
    | Nombre maximum de voisins similaires
    |--------------------------------------------------------------------------
    | Limite le nombre d'utilisateurs voisins pris en compte pour le calcul
    | collaboratif. Augmenter améliore la précision au détriment des perfs.
    */
    'max_voisins' => env('AFFINITE_MAX_VOISINS', 50),

    /*
    |--------------------------------------------------------------------------
    | Nombre de suggestions retournées
    |--------------------------------------------------------------------------
    | Nombre maximum de livres dans la liste de recommandations finale.
    */
    'nombre_suggestions' => env('AFFINITE_NOMBRE_SUGGESTIONS', 10),

];
