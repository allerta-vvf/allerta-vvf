<?php

return [
    "required" => "Il campo :attribute è richiesto.",
    "unique" => "Il campo :attribute deve essere unico.",
    "email" => "Il campo :attribute deve essere un indirizzo email valido.",
    "exists" => "Il campo :attribute non esiste.",
    'before' => 'Il campo :attribute deve essere una data precedente a :date.',
    'before_or_equal' => 'Il campo :attribute deve essere una data precedente o uguale a :date.',
    'boolean' => 'Il campo :attribute deve essere vero o falso.',
    'date' => 'Il campo :attribute deve essere una data valida.',
    'file' => 'Il campo :attribute deve essere un file.',
    'image' => 'Il campo :attribute deve essere un\'immagine.',
    'max' => [
        'numeric' => 'Il campo :attribute non può essere maggiore di :max.',
        'file' => 'Il campo :attribute non può essere maggiore di :max kilobytes.',
        'string' => 'Il campo :attribute non può essere maggiore di :max caratteri.',
        'array' => 'Il campo :attribute non può avere più di :max elementi.',
    ],
    'mimes' => 'Il campo :attribute deve essere un file di tipo: :values.',
    'mimetypes' => 'Il campo :attribute deve essere un file di tipo: :values.',
    'min' => [
        'numeric' => 'Il campo :attribute deve essere almeno :min.',
        'file' => 'Il campo :attribute deve essere almeno :min kilobytes.',
        'string' => 'Il campo :attribute deve essere almeno :min caratteri.',
        'array' => 'Il campo :attribute deve avere almeno :min elementi.',
    ],
    'missing' => 'Il campo :attribute è richiesto.',
    'present' => 'Il campo :attribute deve essere presente.',
    'size' => [
        'numeric' => 'Il campo :attribute deve essere :size.',
        'file' => 'Il campo :attribute deve essere :size kilobytes.',
        'string' => 'Il campo :attribute deve essere :size caratteri.',
        'array' => 'Il campo :attribute deve contenere :size elementi.',
    ],
    'string' => 'Il campo :attribute deve essere una stringa.',
    'unique' => 'Il campo :attribute è già stato utilizzato.',
    'uploaded' => 'Il campo :attribute non è stato caricato correttamente.',
];
