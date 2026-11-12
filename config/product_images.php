<?php

return [
    'driver' => env('PRODUCT_IMAGE_STORAGE', 'local'),

    'vercel_blob' => [
        'token' => env('BLOB_READ_WRITE_TOKEN'),
        'api_url' => env('VERCEL_BLOB_API_URL', 'https://vercel.com/api/blob'),
        'api_version' => '13',
    ],
];
