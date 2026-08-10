<?php

declare(strict_types=1);

namespace OpenEMR\Modules\ThiqaBranding\Service;

use GuzzleHttp\Client;
use Symfony\Contracts\HttpClient\HttpClientInterface;

// Injecting an HTTP client is the seam the rule exists to close.
$inject = function (HttpClientInterface $http): void {
};

$client = new Client();
$client->request('GET', 'https://control-plane.internal/tokens');

$handle = curl_init();
curl_close($handle);

file_get_contents('https://control-plane.internal/tokens');
fopen('http://control-plane.internal/tokens', 'rb');
