<?php

// The same violating body as all_violations_outside_module.php, byte for byte
// from line 10 down. Only the namespace on line 9 differs, and that alone must
// flip every one of the four guardrails from silent to firing.

declare(strict_types=1);

namespace OpenEMR\Modules\ThiqaBranding\Runtime;

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
