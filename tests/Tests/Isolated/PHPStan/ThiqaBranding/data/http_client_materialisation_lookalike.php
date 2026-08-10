<?php

// `MaterialisationHelper` merely starts with the exempt namespace's text; it
// is a distinct namespace segment and therefore still the runtime plane.

declare(strict_types=1);

namespace OpenEMR\Modules\ThiqaBranding\MaterialisationHelper;

use GuzzleHttp\Client;

$client = new Client();

$handle = curl_init();
