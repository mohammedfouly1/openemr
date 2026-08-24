<?php

/**
 * Brand-neutral translated labels containing one product-name placeholder.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Translation;

/**
 * Composes a product name into a translated pattern before the caller applies
 * its context-specific escaping. Only `%s`, `%1$s` and literal `%%` tokens are
 * accepted, preventing catalogue data from becoming an arbitrary format string.
 */
final class ProductContextTranslation
{
    public static function compose(string $translatedPattern, string $productName): string
    {
        $result = '';
        $placeholderCount = 0;
        $length = strlen($translatedPattern);

        for ($offset = 0; $offset < $length; $offset++) {
            $character = $translatedPattern[$offset];
            if ($character !== '%') {
                $result .= $character;
                continue;
            }

            if (substr($translatedPattern, $offset, 2) === '%%') {
                $result .= '%';
                $offset++;
                continue;
            }

            if (substr($translatedPattern, $offset, 4) === '%1$s') {
                $result .= $productName;
                $placeholderCount++;
                $offset += 3;
                continue;
            }

            if (substr($translatedPattern, $offset, 2) === '%s') {
                $result .= $productName;
                $placeholderCount++;
                $offset++;
                continue;
            }

            throw new \InvalidArgumentException('Unsupported translation placeholder near byte ' . $offset . '.');
        }

        if ($placeholderCount !== 1) {
            throw new \InvalidArgumentException(
                'A product-context translation must contain exactly one %s or %1$s placeholder.',
            );
        }

        return $result;
    }
}
