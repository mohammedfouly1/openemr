<?php

/** @package OpenEMR */

declare(strict_types=1);

namespace OpenEMR\Common\Translation;

final readonly class TranslationCatalogueMigrationResult
{
    public function __construct(
        public string $action,
        public int $definitionsChanged = 0,
        public ?int $targetId = null,
    ) {
    }

    public function changed(): bool
    {
        return $this->action === 'applied' || $this->action === 'rolled_back';
    }
}
