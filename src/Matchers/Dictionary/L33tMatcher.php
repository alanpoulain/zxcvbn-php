<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Matchers\Dictionary;

use ZxcvbnPhp\Matcher;
use ZxcvbnPhp\Matchers\Dictionary\Result\L33tChangeResult;
use ZxcvbnPhp\Matchers\Dictionary\Result\L33tExtraResult;
use ZxcvbnPhp\Matchers\Dictionary\Result\L33tResult;
use ZxcvbnPhp\Matchers\MatcherInterface;
use ZxcvbnPhp\Options;
use ZxcvbnPhp\Tree\L33tTrieNode;

final class L33tMatcher implements MatcherInterface
{
    /**
     * Match occurrences of l33t words in password to dictionary words.
     *
     * @return DictionaryMatch[]
     */
    public static function match(#[\SensitiveParameter] string $password, Options $options, array $userInputs = []): array
    {
        /** @var DictionaryMatch[] $l33tMatches */
        $l33tMatches = [];

        $trie = new L33tTrieNode('root', []);
        foreach ($options->l33tTable as $letter => $substitutions) {
            foreach ($substitutions as $substitution) {
                $trie->add(new L33tTrieNode($substitution, [$letter]));
            }
        }

        $passwordResults = self::substituteL33t($password, $options->l33tMaxSubstitutions, $trie);
        $hasFullMatch = false;
        foreach ($passwordResults as $passwordResult) {
            if ($hasFullMatch) {
                break;
            }
            $matches = DictionaryMatcher::match($passwordResult->password, $options, $userInputs, useLevenshtein: $passwordResult->isFullSubstitution);
            foreach ($matches as $match) {
                if (!$hasFullMatch) {
                    $hasFullMatch = 0 === $match->begin() && $match->end() === mb_strlen($password) - 1;
                }
                $extraResult = self::getExtraResult($passwordResult, $match->begin(), $match->end());
                $token = mb_substr($password, $extraResult->begin, $extraResult->end - $extraResult->begin + 1);
                $l33tMatch = new DictionaryMatch(
                    password: $password,
                    begin: $extraResult->begin,
                    end: $extraResult->end,
                    token: $token,
                    matchedWord: $match->matchedWord(),
                    rank: $match->rank(),
                    dictionaryName: $match->dictionaryName(),
                    reversed: false,
                    l33t: true,
                    levenshteinDistance: $match->levenshteinDistance(),
                    l33tExtra: new L33tExtraMatch(changes: $extraResult->changes, changesDisplay: $extraResult->changesDisplay),
                );

                $alreadyIncluded = false;
                foreach ($l33tMatches as $previousL33tMatch) {
                    if ($previousL33tMatch->begin() === $l33tMatch->begin()
                        && $previousL33tMatch->end() === $l33tMatch->end()
                        && $previousL33tMatch->matchedWord() === $l33tMatch->matchedWord()
                        && $previousL33tMatch->dictionaryName() === $l33tMatch->dictionaryName()) {
                        $alreadyIncluded = true;
                    }
                }
                if ($alreadyIncluded) {
                    continue;
                }
                // Only return the matches that contain an actual substitution.
                if (mb_strtolower($token) === $match->matchedWord()) {
                    continue;
                }
                // Filter single-character l33t matches to reduce noise.
                // Otherwise, '1' matches 'i', '4' matches 'a', both very common English words with low dictionary rank.
                if (1 === mb_strlen($token)) {
                    continue;
                }

                $l33tMatches[] = $l33tMatch;
            }
        }

        return Matcher::sortMatches($l33tMatches);
    }

    public static function getPattern(): string
    {
        return DictionaryMatch::PATTERN;
    }

    /**
     * @param L33tChangeResult[] $changes
     * @param string[]           $buffer
     * @param L33tResult[]       $results
     *
     * @return L33tResult[]
     */
    private static function substituteL33t(string $password, int $limit, L33tTrieNode $trie, bool $isFullSub = true, int $index = 0, int $subIndex = 0, array $changes = [], ?string $lastSub = null, int $consecutiveSubCount = 0, array $buffer = [], array $results = []): array
    {
        if (\count($results) >= $limit) {
            return $results;
        }

        if ($index === mb_strlen($password)) {
            $results[] = new L33tResult(password: implode('', $buffer), changes: $changes, isFullSubstitution: $isFullSub);

            return $results;
        }

        // Exhaust all possible substitutions at this index.
        $nodes = self::getSubstitutionsAtIndex($index, $password, $trie);

        $hasSubs = false;
        // Iterate backward to get wider substitutions first.
        for ($i = $index + \count($nodes) - 1; $i >= $index; --$i) {
            $cur = $nodes[$i - $index];
            $sub = $cur->getWord();
            // Skip if this would be a 4th or more consecutive substitution of the same letter.
            // This should work in all language as there shouldn't be the same letter more than four times in a row.
            // So we can ignore the rest to save calculation time.
            if ($consecutiveSubCount >= 3 && $lastSub === $sub) {
                continue;
            }
            // The letters can be empty if the substitution is partial.
            foreach ($cur->getLetters() as $letter) {
                $buffer[] = $letter;
                $subChanges = [...$changes, new L33tChangeResult(index: $subIndex, letter: $letter, substitution: $sub)];

                $results = self::substituteL33t($password, $limit, $trie, $isFullSub, index: $index + mb_strlen($sub), subIndex: $subIndex + mb_strlen($letter), changes: $subChanges, lastSub: $sub, consecutiveSubCount: $lastSub === $sub ? $consecutiveSubCount + 1 : 1, buffer: $buffer, results: $results);

                // Backtrack by ignoring the added postfix.
                array_pop($buffer);

                $hasSubs = true;
            }
        }

        // Generate all combos without doing a substitution at this index.
        $character = mb_substr($password, $index, 1);
        $buffer[] = $character;
        // Reset the last substitution if the character at this index is different from the last substitution.
        if ($character !== $lastSub) {
            $lastSub = null;
        }

        return self::substituteL33t($password, $limit, $trie, $isFullSub && !$hasSubs, index: $index + 1, subIndex: $subIndex + 1, changes: $changes, lastSub: $lastSub, consecutiveSubCount: $consecutiveSubCount, buffer: $buffer, results: $results);
    }

    /**
     * @return L33tTrieNode[]
     */
    private static function getSubstitutionsAtIndex(int $index, string $password, L33tTrieNode $trie): array
    {
        /** @var L33tTrieNode[] $nodes */
        $nodes = [];
        $cur = $trie;
        $length = mb_strlen($password);
        for ($i = $index; $i < $length; ++$i) {
            $character = mb_substr($password, $i, 1);
            $cur = self::getChildByValue($cur, $character);
            if (!$cur) {
                break;
            }
            $nodes[] = $cur;
        }

        return $nodes;
    }

    private static function getChildByValue(L33tTrieNode $node, string $value): ?L33tTrieNode
    {
        /** @var L33tTrieNode $child */
        foreach ($node->children() as $child) {
            if ($value === $child->getValue()) {
                return $child;
            }
        }

        return null;
    }

    private static function getExtraResult(L33tResult $passwordResult, int $begin, int $end): L33tExtraResult
    {
        $previousChanges = [];
        foreach ($passwordResult->changes as $changes) {
            if ($changes->index < $begin) {
                $previousChanges[] = $changes;
            }
        }

        $beginShift = 0;
        foreach ($previousChanges as $change) {
            $beginShift -= mb_strlen($change->letter);
            $beginShift += mb_strlen($change->substitution);
        }
        $beginUnsubstituted = $begin + $beginShift;

        $usedChanges = [];
        $subDisplay = [];
        foreach ($passwordResult->changes as $change) {
            if ($change->index >= $begin && $change->index <= $end) {
                $usedChangeIndex = $change->index + $beginShift;
                $usedChanges[] = new L33tChangeResult(
                    index: $usedChangeIndex,
                    letter: $change->letter,
                    substitution: $change->substitution
                );
                $subDisplay[] = "[{$usedChangeIndex}] {$change->substitution} -> {$change->letter}";
            }
        }

        $endUnsubstituted = $end - $begin + $beginUnsubstituted;
        foreach ($usedChanges as $change) {
            $endUnsubstituted -= mb_strlen($change->letter);
            $endUnsubstituted += mb_strlen($change->substitution);
        }

        return new L33tExtraResult(
            begin: $beginUnsubstituted,
            end: $endUnsubstituted,
            changes: $usedChanges,
            changesDisplay: implode(', ', $subDisplay),
        );
    }
}
