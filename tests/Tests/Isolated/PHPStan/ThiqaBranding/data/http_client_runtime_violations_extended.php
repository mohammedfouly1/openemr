<?php

// Runtime-plane violation shapes beyond the plain `use`/`new` pair: a group
// use, a static call, the OpenEMR HTTP wrapper namespace, nullable and union
// parameter types, and URLs assembled by concatenation or interpolation.

declare(strict_types=1);

namespace OpenEMR\Modules\ThiqaBranding\Client;

use GuzzleHttp\{Client, Psr7\Request};
use OpenEMR\Common\Http\oeHttp;

$path = 'tokens';

oeHttp::get('https://control-plane.internal/tokens');

$nullable = function (?\GuzzleHttp\ClientInterface $client): void {
};

$union = function (\Psr\Http\Client\ClientInterface|string $client): void {
};

file_get_contents('https://control-plane.internal/' . $path);
file_get_contents("https://control-plane.internal/$path");
