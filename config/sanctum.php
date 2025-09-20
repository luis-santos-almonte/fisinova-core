<?php
return [
    'expiration' => null, // Tokens no expiran (o pon 525600 para 1 año)
    'token_prefix' => env('SANCTUM_TOKEN_PREFIX', ''),
];