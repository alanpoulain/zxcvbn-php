<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Tree;

use loophp\phptree\Node\NodeInterface;
use loophp\phptree\Node\ValueNode;
use loophp\phptree\Node\ValueNodeInterface;
use loophp\phptree\Traverser\TraverserInterface;
use ZxcvbnPhp\Multibyte\MultibyteString;

final class L33tTrieNode extends ValueNode
{
    /** @var string[] */
    private array $partialLetters;

    /** @var string[] */
    private array $letters;

    public function __construct(
        $value,
        array $partialLetters,
        array $letters = [],
        int $capacity = 0,
        ?TraverserInterface $traverser = null,
        ?NodeInterface $parent = null
    ) {
        parent::__construct($value, $capacity, $traverser, $parent);

        $this->partialLetters = $partialLetters;
        $this->letters = $letters;
    }

    public function add(NodeInterface ...$nodes): NodeInterface
    {
        /** @var L33tTrieNode $node */
        foreach ($nodes as $node) {
            $data = $node->getValue();

            $dataWithoutFirstLetter = mb_substr($data, 1);
            $isEnd = '' === $dataWithoutFirstLetter;

            $node = new self(mb_substr($data, 0, 1), $node->getPartialLetters(), $isEnd ? $node->getPartialLetters() : []);
            $parent = $this->append($node);

            if (!$isEnd) {
                $parent->add(new self($dataWithoutFirstLetter, $node->getPartialLetters(), []));
            }
        }

        return $this;
    }

    public function getWord(): string
    {
        $values = [$this->getValue()];

        /** @var ValueNodeInterface $ancestor */
        foreach ($this->getAncestors() as $ancestor) {
            $values[] = $ancestor->getValue();
        }
        array_pop($values);

        return MultibyteString::mbStrRev(implode('', $values));
    }

    /**
     * @return string[]
     */
    public function getPartialLetters(): array
    {
        return $this->partialLetters;
    }

    /**
     * @return string[]
     */
    public function getLetters(): array
    {
        return $this->letters;
    }

    public function appendLetters(array $letters): array
    {
        $this->letters = array_merge($this->letters, $letters);

        return $this->letters;
    }

    private function append(ValueNodeInterface $node): NodeInterface|ValueNodeInterface
    {
        /** @var L33tTrieNode $child */
        foreach ($this->children() as $child) {
            /** @var L33tTrieNode $node */
            if ($node->getValue() === $child->getValue()) {
                $child->appendLetters($node->getLetters());

                return $child;
            }
        }

        parent::add($node);

        return $node;
    }
}
