<?php

// Shapes the rule cannot resolve statically: a dynamic class name, a dynamic
// static-call target, a dynamic function name, a URL-aware call with no
// arguments, stream calls whose URL is an unknown parameter, built-in and
// absent parameter types, and a non-network intersection type. The rule must
// degrade to silence rather than guess.

declare(strict_types=1);

namespace OpenEMR\Modules\SkyEagleBranding\Dynamic;

$className = 'GuzzleHttp\Client';
$functionName = 'curl_init';

$client = new $className();
$className::request('GET', '/tokens');
$functionName();

file_get_contents();
fopen();

$unknownUrl = function (string $url, string $suffix): void {
    file_get_contents($url);
    file_get_contents("$url/$suffix");
    fopen($url . $suffix, 'rb');
};

$builtInTypes = function (string $path, int $flags): void {
};

$untyped = function ($anything): void {
};

$harmlessIntersection = function (\Countable&\ArrayAccess $collection): void {
};

$notUrlAware = function (string $url): void {
    is_file($url);
    realpath($url);
};
