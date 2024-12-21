<?php
return [
    'enable' => true,

    'environment' => env('APP_ENV', 'production'),

    'migration_table' => 'migrations',

    'migrations_path' => base_path('database/migrations'),

    'seeds_path' => base_path('database/seeds'),
];