<?php

// A namespace segment that merely begins with "Tests" is not test code, so the
// test exemption must not reach it. Case variation must not evade the literal
// host check either.

declare(strict_types=1);

namespace OpenEMR\Modules\ThiqaBranding\TestsHelper;

$controlPlane = 'https://cp.thiqa.example/api/v1/branding';
$mixedCaseLiteral = 'https://Thiqa.Example/api';
$mixedCaseUpstream = 'https://REG.OPEN-EMR.ORG/api';
