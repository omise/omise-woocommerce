<?php

$filesToConvert = [
    __DIR__ . '/omise-woocommerce.php',
    __DIR__ . '/includes/gateway/abstract-omise-payment-base-card.php',
    __DIR__ . '/includes/libraries/omise-php/lib/omise/res/OmiseApiResource.php',
];

foreach ($filesToConvert as $file) {

    $contentsToReplace = [
        'https://cdn.omise.co/omise.js' => 'https://cdn.staging-omise.co/omise.js',
        'https://api.omise.co/' => 'https://api.staging-omise.co/',
        'https://vault.omise.co/' => 'https://vault.staging-omise.co/',
    ];

    foreach ($contentsToReplace as $key => $value) {
        $content = file_get_contents($file);
        $content = str_replace($key, $value, $content);
        file_put_contents($file, $content);
    }
}
