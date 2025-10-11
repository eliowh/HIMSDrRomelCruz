<?php

return [
    'api_key' => env('STREAM_API_KEY', ''),
    'api_secret' => env('STREAM_API_SECRET', ''),
    'app_id' => env('STREAM_APP_ID', ''),
    'region' => env('STREAM_REGION', 'us-east-1'),
    'timeout' => 6.0,
];