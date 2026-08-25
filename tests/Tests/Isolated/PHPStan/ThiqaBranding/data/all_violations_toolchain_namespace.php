<?php

// The same violating body again, byte for byte from line 10 down. This namespace is the
// generator toolchain under tools/branding/src. It does not run in production, but it
// writes what production reads, so a breach here ships downstream rather than staying put.

declare(strict_types=1);

namespace OpenEMR\Branding\Generator;

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
