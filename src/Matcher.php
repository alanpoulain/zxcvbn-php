<?php

declare(strict_types=1);

namespace ZxcvbnPhp;

use ZxcvbnPhp\Configuration\Configurator;
use ZxcvbnPhp\Matchers\MatcherInterface;
use ZxcvbnPhp\Matchers\MatchInterface;

final class Matcher
{
    private Options $options;

    public function __construct(?Options $options = null)
    {
        $this->options = $options ?? Configurator::getOptions(new Config());
    }

    /**
     * Get matches for a password.
     *
     * @param string   $password   password string to match
     * @param string[] $userInputs array of values related to the user (optional)
     *
     * @return MatchInterface[]
     */
    public function getMatches(#[\SensitiveParameter] string $password, array $userInputs = []): array
    {
        $matches = [];
        foreach ($this->options->matchers as $matcher) {
            if (!is_a($matcher, MatcherInterface::class, true)) {
                throw new \InvalidArgumentException(sprintf('Matcher class must implement %s', MatcherInterface::class));
            }

            $matched = $matcher::match($password, $this->options, $userInputs);
            if (\is_array($matched) && !empty($matched)) {
                $matches[] = $matched;
            }
        }

        $matches = array_merge([], ...$matches);
        uasort($matches, $this->compareMatches(...));

        return array_values($matches);
    }

    /**
     * @template M
     *
     * @param M[] $matches
     *
     * @return M[]
     */
    public static function sortMatches(array $matches): array
    {
        uasort($matches, self::compareMatches(...));

        return array_values($matches);
    }

    private static function compareMatches(MatchInterface $a, MatchInterface $b): int
    {
        $beginDiff = $a->begin() - $b->begin();
        if ($beginDiff) {
            return $beginDiff;
        }

        return $a->end() - $b->end();
    }
}
