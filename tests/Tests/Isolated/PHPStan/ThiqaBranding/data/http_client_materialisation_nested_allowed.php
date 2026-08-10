<?php

// The exemption covers the whole Materialisation sub-tree, not just its root
// namespace, so a nested materialiser may still reach the Control Plane.

declare(strict_types=1);

namespace OpenEMR\Modules\ThiqaBranding\Materialisation\Fetcher;

use GuzzleHttp\Client;

$client = new Client();
$client->request('GET', 'https://control-plane.internal/tokens');

$handle = curl_init();
curl_close($handle);

file_get_contents('https://control-plane.internal/tokens');
