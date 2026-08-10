<?php

// Further runtime-plane violation shapes: an intersection parameter type that
// includes an HTTP client, URLs that only PHPStan's constant-string inference
// sees, and URLs inference cannot fold at all — where the rule falls back to
// the leading literal of a concatenation or an interpolation.

declare(strict_types=1);

namespace OpenEMR\Modules\ThiqaBranding\Fetcher;

$intersection = function (\GuzzleHttp\ClientInterface&\Countable $client): void {
};

$endpoint = 'https://control-plane.internal/tokens';

file_get_contents($endpoint);
fopen('https://control-plane' . '.internal/tokens', 'rb');

$unfoldable = function (string $tenantId): void {
    file_get_contents('https://control-plane.internal/tokens/' . $tenantId);
    file_get_contents("https://control-plane.internal/brands/$tenantId");
};
