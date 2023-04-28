<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Test\Translation;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Translation\IdentityTranslator;
use Symfony\Component\Translation\Translator as SymfonyTranslator;
use ZxcvbnPhp\Config;
use ZxcvbnPhp\Configuration\Configurator;
use ZxcvbnPhp\Translation\Translator;

#[CoversClass(Translator::class)]
final class TranslatorTest extends TestCase
{
    #[RunInSeparateProcess]
    public function testGetTranslatorTranslationDisabled(): void
    {
        self::assertInstanceOf(IdentityTranslator::class, Translator::getTranslator(Configurator::getOptions(new Config(translationEnabled: false))));
    }

    #[RunInSeparateProcess]
    public function testGetTranslator(): void
    {
        $translator = Translator::getTranslator(Configurator::getOptions(new Config()));

        self::assertInstanceOf(SymfonyTranslator::class, $translator);
        self::assertSame('en', $translator->getLocale());
    }

    public function testGetTranslatorAnotherLocale(): void
    {
        $translator = Translator::getTranslator(Configurator::getOptions(new Config(translationLocale: 'fr')));

        self::assertInstanceOf(SymfonyTranslator::class, $translator);
        self::assertSame('fr', $translator->getLocale());
    }
}
