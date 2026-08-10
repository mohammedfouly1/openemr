<?php

/**
 * The fixed, ordered colour-token schema shared by every generated artefact.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    mohammedfouly1 <mselfouly2008@yahoo.com>
 * @copyright Copyright (c) 2026 mohammedfouly1
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Branding;

/**
 * Declaration order here is the emission order of every generated file, which
 * is what makes the output independent of JSON key order or hash iteration.
 *
 * The schema is also a contract in the other direction: `assertCovers()` fails
 * when `brand/tokens/thiqa-tokens.json` grows a token the generator does not
 * know how to emit, so a token can never be added and silently dropped.
 */
final readonly class ColorTokenSchema
{
    private const GROUP_BRAND = 'Brand palette';
    private const GROUP_SURFACE = 'Surfaces, borders and lines';
    private const GROUP_TEXT = 'Text';
    private const GROUP_SEMANTIC = 'Semantic status';
    private const GROUP_INTERACTIVE = 'Interactive';
    private const GROUP_LINK = 'Links';

    /**
     * @param list<ColorToken> $tokens
     */
    private function __construct(private array $tokens)
    {
    }

    public static function create(): self
    {
        return new self([
            new ColorToken(self::GROUP_BRAND, 'brand.navy'),
            new ColorToken(self::GROUP_BRAND, 'brand.coral'),
            new ColorToken(self::GROUP_BRAND, 'brand.coralDeep'),
            new ColorToken(self::GROUP_BRAND, 'brand.sage'),
            new ColorToken(self::GROUP_BRAND, 'brand.sky'),
            new ColorToken(self::GROUP_BRAND, 'brand.amber'),
            new ColorToken(self::GROUP_BRAND, 'brand.critical'),

            new ColorToken(self::GROUP_SURFACE, 'background'),
            new ColorToken(self::GROUP_SURFACE, 'surface'),
            // Light has no distinct raised surface: a raised card is the plain
            // white surface on the off-white page, so alias rather than invent.
            new ColorToken(self::GROUP_SURFACE, 'surfaceRaised', 'surface'),
            new ColorToken(self::GROUP_SURFACE, 'surfaceSunken'),
            new ColorToken(self::GROUP_SURFACE, 'surfaceInput'),
            new ColorToken(self::GROUP_SURFACE, 'surfaceInputOnRaised'),
            new ColorToken(self::GROUP_SURFACE, 'border'),
            new ColorToken(self::GROUP_SURFACE, 'borderStrong'),
            new ColorToken(self::GROUP_SURFACE, 'divider'),

            new ColorToken(self::GROUP_TEXT, 'text.primary'),
            new ColorToken(self::GROUP_TEXT, 'text.secondary'),
            new ColorToken(self::GROUP_TEXT, 'text.disabled'),
            new ColorToken(self::GROUP_TEXT, 'text.inverse'),

            new ColorToken(self::GROUP_SEMANTIC, 'semantic.success.bg'),
            new ColorToken(self::GROUP_SEMANTIC, 'semantic.success.text'),
            new ColorToken(self::GROUP_SEMANTIC, 'semantic.success.border'),
            new ColorToken(self::GROUP_SEMANTIC, 'semantic.warning.bg'),
            new ColorToken(self::GROUP_SEMANTIC, 'semantic.warning.text'),
            new ColorToken(self::GROUP_SEMANTIC, 'semantic.warning.border'),
            new ColorToken(self::GROUP_SEMANTIC, 'semantic.critical.bg'),
            new ColorToken(self::GROUP_SEMANTIC, 'semantic.critical.text'),
            new ColorToken(self::GROUP_SEMANTIC, 'semantic.critical.border'),
            new ColorToken(self::GROUP_SEMANTIC, 'semantic.info.bg'),
            new ColorToken(self::GROUP_SEMANTIC, 'semantic.info.text'),
            new ColorToken(self::GROUP_SEMANTIC, 'semantic.info.border'),

            new ColorToken(self::GROUP_INTERACTIVE, 'interactive.primary.default'),
            new ColorToken(self::GROUP_INTERACTIVE, 'interactive.primary.hover'),
            new ColorToken(self::GROUP_INTERACTIVE, 'interactive.primary.active'),
            new ColorToken(self::GROUP_INTERACTIVE, 'interactive.primary.disabled'),
            new ColorToken(self::GROUP_INTERACTIVE, 'interactive.primary.textOn'),
            new ColorToken(self::GROUP_INTERACTIVE, 'interactive.secondary.default'),
            new ColorToken(self::GROUP_INTERACTIVE, 'interactive.secondary.hover'),
            new ColorToken(self::GROUP_INTERACTIVE, 'interactive.secondary.textOn'),
            new ColorToken(self::GROUP_INTERACTIVE, 'interactive.focusRing'),

            new ColorToken(self::GROUP_LINK, 'link.default'),
            new ColorToken(self::GROUP_LINK, 'link.hover'),
        ]);
    }

    /**
     * @return list<ColorToken>
     */
    public function tokens(): array
    {
        return $this->tokens;
    }

    public function get(string $path): ColorToken
    {
        foreach ($this->tokens as $token) {
            if ($token->path === $path) {
                return $token;
            }
        }

        throw new GeneratorException(sprintf('Colour token "%s" is not declared in the generator schema.', $path));
    }

    /**
     * Rejects any leaf in the variant sub-tree that the schema does not declare.
     */
    public function assertCovers(JsonDocument $document, Variant $variant): void
    {
        $known = [];
        foreach ($this->tokens as $token) {
            $known[$token->path] = true;
        }

        $unknown = [];
        foreach ($document->leafPaths($variant->key()) as $leaf) {
            if (!isset($known[$leaf])) {
                $unknown[] = $leaf;
            }
        }

        if ($unknown !== []) {
            throw new GeneratorException(sprintf(
                'Token document %s declares %s under "%s" that the generator schema does not know: %s. '
                . 'Add them to ColorTokenSchema so every consumer stays in sync.',
                $document->origin(),
                count($unknown) === 1 ? 'a token' : 'tokens',
                $variant->key(),
                implode(', ', $unknown),
            ));
        }
    }
}
