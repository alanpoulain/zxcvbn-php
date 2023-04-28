<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Matchers\Dictionary;

use ZxcvbnPhp\Matchers\Dictionary\Result\L33tChangeResult;

final readonly class L33tExtraMatch
{
    public function __construct(
        /**
         * An array of changes (substitutions) made to get from the token to the dictionary word.
         *
         * @var L33tChangeResult[]
         */
        public array $changes,
        /**
         * A user-readable string that shows which changes (substitutions) were detected.
         */
        public string $changesDisplay,
    ) {
    }
}
