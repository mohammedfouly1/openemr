<?php

// The materialisation plane runs out-of-request, so a Control Plane call is
// legitimate here and the rule must stay silent.

declare(strict_types=1);

namespace OpenEMR\Modules\SkyEagleBranding\Materialisation;

use GuzzleHttp\Client;
use Symfony\Contracts\HttpClient\HttpClientInterface;

$inject = function (HttpClientInterface $http): void {
};

$client = new Client();
$client->request('GET', 'https://control-plane.internal/tokens');

$handle = curl_init();
curl_close($handle);

file_get_contents('https://control-plane.internal/tokens');
fopen('http://control-plane.internal/tokens', 'rb');
