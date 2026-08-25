<?php

// The same violating body again, byte for byte from line 10 down. This namespace is the
// pre-database identity layer (ADR-BRAND-005): shipped branding code that runs before the
// module can boot, and that every guardrail was blind to until S4B-10 / S4E-06.

declare(strict_types=1);

namespace OpenEMR\Common\Branding;

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
