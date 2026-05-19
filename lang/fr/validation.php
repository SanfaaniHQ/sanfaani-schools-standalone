<?php

return [
    'required' => 'Le champ :attribute est obligatoire.',
    'email' => 'Le champ :attribute doit être une adresse e-mail valide.',
    'string' => 'Le champ :attribute doit être une chaîne de caractères.',
    'max' => [
        'string' => 'Le champ :attribute ne doit pas dépasser :max caractères.',
        'numeric' => 'Le champ :attribute ne doit pas dépasser :max.',
    ],
    'min' => [
        'numeric' => 'Le champ :attribute doit être au moins :min.',
    ],
    'date' => 'Le champ :attribute doit être une date valide.',
    'exists' => 'La valeur sélectionnée pour :attribute est invalide.',
    'in' => 'La valeur sélectionnée pour :attribute est invalide.',
    'boolean' => 'Le champ :attribute doit être vrai ou faux.',
    'integer' => 'Le champ :attribute doit être un nombre entier.',
];
