<?php

declare(strict_types=1);

namespace ZxcvbnPhp\Translation;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Translation\IdentityTranslator;
use Symfony\Component\Translation\Loader\JsonFileLoader;
use Symfony\Component\Translation\Translator as SymfonyTranslator;
use Symfony\Contracts\Translation\TranslatorInterface;
use ZxcvbnPhp\Options;

final class Translator
{
    private static ?SymfonyTranslator $translator = null;

    private static ?IdentityTranslator $identityTranslator = null;

    public static function getTranslator(Options $options): TranslatorInterface
    {
        if (!$options->translationEnabled) {
            return self::getIdentityTranslator();
        }

        if (null === self::$translator) {
            self::$translator = self::initTranslator($options);
        }

        if ($options->translationLocale !== self::$translator->getLocale()) {
            self::$translator->setLocale($options->translationLocale);
        }

        return self::$translator;
    }

    private static function getIdentityTranslator(): IdentityTranslator
    {
        if (null === self::$identityTranslator) {
            self::$identityTranslator = self::initIdentityTranslator();
        }

        return self::$identityTranslator;
    }

    private static function initTranslator(Options $options): SymfonyTranslator
    {
        $translator = new SymfonyTranslator($options->translationLocale);
        $translator->addLoader('json', new JsonFileLoader());

        $finder = Finder::create()->in(Options::RESOURCES_PATH)->exclude('common')->directories();
        foreach ($finder as $languageDir) {
            $locale = $languageDir->getFilename();
            $translator->addResource('json', sprintf('%s/translations.json', $languageDir->getRealPath()), $locale, 'messages+intl-icu');
        }

        return $translator;
    }

    private static function initIdentityTranslator(): IdentityTranslator
    {
        return new IdentityTranslator();
    }
}
