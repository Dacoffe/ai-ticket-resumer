<?php

// Todos estes providers falam o formato "OpenAI chat completions",
// por isso um único cliente HTTP serve para todos: só muda base_url, key e model.
return [
    'default' => env('LLM_PROVIDER', 'openrouter'),


     'providers' => [
        'openrouter' => [
            'base_url' => 'https://openrouter.ai/api/v1',
            'api_key'  => env('OPENROUTER_API_KEY'),
            'model'    => env('OPENROUTER_MODEL'), // ex.: um modelo com sufixo ":free"
        ],
        'gemini' => [
            'base_url' => 'https://generativelanguage.googleapis.com/v1beta/openai',
            'api_key'  => env('GEMINI_API_KEY'),
            'model'    => env('GEMINI_MODEL'),
        ],
     ],

    'embeddings' => [
        'base_url'          => 'https://generativelanguage.googleapis.com/v1beta',
        'api_key'           => env('GEMINI_API_KEY'),
        'model'             => env('EMBEDDING_MODEL', 'gemini-embedding-001'),
        'output_dimensions' => 768, //recomendado pelo Google
    ],

];
