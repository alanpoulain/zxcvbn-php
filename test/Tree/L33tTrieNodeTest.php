<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Test\Tree;

use PHPUnit\Framework\TestCase;
use ZxcvbnPhp\Tree\L33tTrieNode;

final class L33tTrieNodeTest extends TestCase
{
    public function testRetrievingWord(): void
    {
        $trie = new L33tTrieNode('root', []);

        $trie->add(new L33tTrieNode('><', ['x']), new L33tTrieNode('>', ['v']));

        /** @var L33tTrieNode $child */
        $child = iterator_to_array($trie->children())[0];
        self::assertSame('>', $child->getValue());
        self::assertSame(['v'], $child->getLetters());
        self::assertSame(['x'], $child->getPartialLetters());
        self::assertFalse($child->isLeaf());
        /** @var L33tTrieNode $child */
        $child = iterator_to_array($child->children())[0];
        self::assertSame('<', $child->getValue());

        self::assertSame('><', $child->getWord());
        self::assertSame(['x'], $child->getLetters());
        self::assertTrue($child->isLeaf());
    }
}
