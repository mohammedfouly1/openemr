<?php

// Every shape the four ThiqaBranding guardrails forbid, written in an
// ordinary core namespace. OpenEMR does all of this legitimately, so all four
// rules must stay completely silent on this file.

declare(strict_types=1);

namespace OpenEMR\Services\Something;

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
