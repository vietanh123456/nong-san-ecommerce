<?php

return [
    'public_url' => env(
        'TRACE_PUBLIC_URL',
        env('APP_URL', 'http://localhost')
    ),
];
