<?php

// Real hosts, real filenames and `.example.com` style strings are fine; only
// placeholder `.example` hosts and the upstream registration endpoint are not.

declare(strict_types=1);

namespace OpenEMR\Modules\ThiqaBranding\Config;

$tenantEndpoint = 'https://control-plane.thiqa.ae/api/v1/branding';
$upstreamDocs = 'https://www.open-emr.org';
$distFileName = 'branding.config.example.php';
$realisticExampleCom = 'https://tenant.example.com/logo.svg';
