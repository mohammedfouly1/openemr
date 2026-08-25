<?php

// The wider OpenEMR codebase uses Guzzle, curl and stream wrappers
// legitimately. None of this may be flagged.

declare(strict_types=1);

namespace OpenEMR\Services\Example;

use GuzzleHttp\Client;
use Symfony\Contracts\HttpClient\HttpClientInterface;

$inject = function (HttpClientInterface $http): void {
};

$client = new Client();
$client->request('GET', 'https://example.org/anything');

$handle = curl_init();
curl_close($handle);

file_get_contents('https://example.org/anything');
fopen('http://example.org/anything', 'rb');
