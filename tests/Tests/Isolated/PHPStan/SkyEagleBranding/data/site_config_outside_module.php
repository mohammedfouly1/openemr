<?php

// Core OpenEMR reads sites/<site>/config.php legitimately. The rule is scoped
// to the branding module namespace and must not fire here.

declare(strict_types=1);

namespace OpenEMR\Common\Example;

$siteId = 'default';

$constructed = 'sites/' . $siteId . '/config.php';
$wholeLiteral = 'sites/default/config.php';
