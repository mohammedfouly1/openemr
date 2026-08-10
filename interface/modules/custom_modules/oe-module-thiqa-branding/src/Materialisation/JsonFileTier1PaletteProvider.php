<?php

/**
 * Reads the shipped brand/tokens/thiqa-tokens.json as the Tier 1 base.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\ThiqaBranding\Materialisation;

use OpenEMR\Modules\ThiqaBranding\Theme\ThemeVariant;
use OpenEMR\Modules\ThiqaBranding\Token\TokenParseException;
use OpenEMR\Modules\ThiqaBranding\Token\TokenSet;
use OpenEMR\Modules\ThiqaBranding\Token\TokenSetParser;

/**
 * Not `readonly`: the parsed document is memoised. Materialisation runs out of the
 * request path and may process both variants, and re-reading and re-parsing the same
 * immutable shipped file twice buys nothing.
 *
 * The parse is deliberately eager on first use and total afterwards — a malformed token
 * document is a build defect, not a tenant event, so it surfaces as TokenParseException
 * and the materialiser reports the run as a transient failure rather than silently
 * validating against a partial palette.
 */
final class JsonFileTier1PaletteProvider implements Tier1PaletteProviderInterface
{
    /** @var array{light: TokenSet, dark: TokenSet}|null */
    private ?array $parsed = null;

    public function __construct(
        private readonly TokenSetParser $parser,
        private readonly string $documentPath,
    ) {
    }

    /**
     * @throws TokenParseException
     */
    public function paletteFor(ThemeVariant $variant): TokenSet
    {
        $parsed = $this->parsed ??= $this->parser->parseDocument($this->read());

        return match ($variant) {
            ThemeVariant::Light => $parsed['light'],
            ThemeVariant::Dark => $parsed['dark'],
        };
    }

    /**
     * @throws TokenParseException
     */
    private function read(): string
    {
        $contents = @file_get_contents($this->documentPath);
        if ($contents === false) {
            throw TokenParseException::malformedDocument('the shipped token document could not be read');
        }

        return $contents;
    }
}
