<?php

// Most legacy OpenEMR code lives in the global namespace, where
// Scope::getNamespace() returns null. All four guardrails must stay silent.

declare(strict_types=1);

use GuzzleHttp\Client;
use Twig\Loader\FilesystemLoader;

$httpClient = new Client();
$handle = curl_init();
curl_close($handle);
file_get_contents('https://reg.open-emr.org/api/v1/product_registration');

$siteConfig = 'sites/default/config.php';
$globPattern = 'sites/*/config.php';

$loader = new FilesystemLoader();
$loader->prependPath(__DIR__);
$loader->addPath(__DIR__);

$placeholderHost = 'https://cp.thiqa.example/api/v1/branding';
$upstreamHost = 'reg.open-emr.org';
