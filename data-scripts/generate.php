#!/usr/bin/env php
<?php

declare(strict_types=1);

namespace ZxcvbnPhpDataScripts;

require __DIR__.'/vendor/autoload.php';

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\SingleCommandApplication;
use Symfony\Component\Console\Style\SymfonyStyle;
use ZxcvbnPhpDataScripts\Generators\ApiGenerator;
use ZxcvbnPhpDataScripts\Generators\GeneratorOptions;
use ZxcvbnPhpDataScripts\Generators\InseeGenerator;
use ZxcvbnPhpDataScripts\Generators\JapaneseListGenerator;
use ZxcvbnPhpDataScripts\Generators\JapaneseTokenizer;
use ZxcvbnPhpDataScripts\Generators\KeyboardAdjacencyGraphGenerator;
use ZxcvbnPhpDataScripts\Generators\PasswordGenerator;
use ZxcvbnPhpDataScripts\Generators\RegExGenerator;
use ZxcvbnPhpDataScripts\Generators\RequestOptions;
use ZxcvbnPhpDataScripts\Generators\SimpleListGenerator;
use ZxcvbnPhpDataScripts\Generators\SpreadsheetGenerator;
use ZxcvbnPhpDataScripts\Generators\WikipediaGenerator;

$commonListHandlers = [
    new ListHandler('adjacencyGraphs', new KeyboardAdjacencyGraphGenerator(new GeneratorOptions(language: 'common'))),
    new ListHandler('passwords', new PasswordGenerator(new GeneratorOptions(language: 'common'), 'https://raw.githubusercontent.com/danielmiessler/SecLists/master/Passwords/xato-net-10-million-passwords-100000.txt')),
];

$wikipediaListHandlers = [
    new ListHandler('wikipedia', new WikipediaGenerator(new GeneratorOptions(language: 'cs'), 'https://dumps.wikimedia.org/cswiki/latest/cswiki-latest-pages-articles.xml.bz2')),
    new ListHandler('wikipedia', new WikipediaGenerator(new GeneratorOptions(language: 'de'), 'https://dumps.wikimedia.org/dewiki/latest/dewiki-latest-pages-articles.xml.bz2')),
    new ListHandler('wikipedia', new WikipediaGenerator(new GeneratorOptions(language: 'en'), 'https://dumps.wikimedia.org/enwiki/latest/enwiki-latest-pages-articles.xml.bz2')),
    new ListHandler('wikipedia', new WikipediaGenerator(new GeneratorOptions(language: 'fr'), 'https://dumps.wikimedia.org/frwiki/latest/frwiki-latest-pages-articles.xml.bz2')),
    new ListHandler('wikipedia', new WikipediaGenerator(new GeneratorOptions(language: 'es-es'), 'https://dumps.wikimedia.org/eswiki/latest/eswiki-latest-pages-articles.xml.bz2')),
    new ListHandler('wikipedia', new WikipediaGenerator(new GeneratorOptions(language: 'fi'), 'https://dumps.wikimedia.org/fiwiki/latest/fiwiki-latest-pages-articles.xml.bz2')),
    new ListHandler('wikipedia', new WikipediaGenerator(new GeneratorOptions(language: 'id'), 'https://dumps.wikimedia.org/idwiki/latest/idwiki-latest-pages-articles.xml.bz2')),
    new ListHandler('wikipedia', new WikipediaGenerator(new GeneratorOptions(language: 'it'), 'https://dumps.wikimedia.org/itwiki/latest/itwiki-latest-pages-articles.xml.bz2')),
    new ListHandler('wikipedia', new WikipediaGenerator(new GeneratorOptions(language: 'ja'), 'https://dumps.wikimedia.org/jawiki/latest/jawiki-latest-pages-articles.xml.bz2', new JapaneseTokenizer())),
    new ListHandler('wikipedia', new WikipediaGenerator(new GeneratorOptions(language: 'nl-be'), 'https://dumps.wikimedia.org/nlwiki/latest/nlwiki-latest-pages-articles.xml.bz2')),
    new ListHandler('wikipedia', new WikipediaGenerator(new GeneratorOptions(language: 'pl'), 'https://dumps.wikimedia.org/plwiki/latest/plwiki-latest-pages-articles.xml.bz2')),
    new ListHandler('wikipedia', new WikipediaGenerator(new GeneratorOptions(language: 'pt-br'), 'https://dumps.wikimedia.org/ptwiki/latest/ptwiki-latest-pages-articles.xml.bz2')),
];

/** @var ListHandler[] $listHandlers */
$listHandlers = [
    new ListHandler('firstnames', new SimpleListGenerator(new GeneratorOptions(language: 'ar'), 'https://raw.githubusercontent.com/AKhateeb/arabic-names/master/Most-Popular-Arabic-FirstNames.txt')),
    new ListHandler('lastnames', new SimpleListGenerator(new GeneratorOptions(language: 'ar'), 'https://raw.githubusercontent.com/AKhateeb/arabic-names/master/Most-Popular-Arabic-LastNames.txt')),
    new ListHandler('commonWords', new SimpleListGenerator(new GeneratorOptions(language: 'cs', hasOccurrences: true, normalizeDiacritics: true), 'https://raw.githubusercontent.com/hermitdave/FrequencyWords/master/content/2018/cs/cs_50k.txt')),
    new ListHandler('firstnames', new RegExGenerator(new GeneratorOptions(language: 'cs', convertEncoding: 'windows-1250', normalizeDiacritics: true, pagination: 15, regEx: '/<tr class="itemjmeno"><td>.*<\/td><td><a href=\'.*\'>(.+)<\/a><\/td><td>\d*<\/td><\/tr>/'), 'https://krestnijmeno.prijmeni.cz/oblast/3000-ceska_republika/muzska_jmena&page=__PAGINATION__')),
    new ListHandler('lastnames', new RegExGenerator(new GeneratorOptions(language: 'cs', convertEncoding: 'windows-1250', normalizeDiacritics: true, pagination: 50, regEx: '/<tr class="itemprijmeni"><td>.*<\/td><td><a href=\'.*\'>(.+)<\/a><\/td><td>\d*<\/td><\/tr>/'), 'https://www.prijmeni.cz/oblast/3000-ceska_republika&page=__PAGINATION__')),
    new ListHandler('commonWords', new SimpleListGenerator(new GeneratorOptions(language: 'de', hasOccurrences: true), 'https://raw.githubusercontent.com/hermitdave/FrequencyWords/master/content/2018/de/de_50k.txt')),
    new ListHandler('firstnames', new SimpleListGenerator(new GeneratorOptions(language: 'de'), 'https://gist.githubusercontent.com/hrueger/2aa48086e9720ee9b87ec734889e1b15/raw')),
    new ListHandler('lastnames', new SimpleListGenerator(new GeneratorOptions(language: 'de'), 'https://gist.githubusercontent.com/hrueger/6599d1ac1e03b4c3dc432d722ffcefd0/raw')),
    new ListHandler('commonWords', new SimpleListGenerator(new GeneratorOptions(language: 'en', hasOccurrences: true), 'https://raw.githubusercontent.com/hermitdave/FrequencyWords/master/content/2018/en/en_50k.txt')),
    new ListHandler('firstnames', new SimpleListGenerator(new GeneratorOptions(language: 'en', splitter: "\r\n"), 'https://raw.githubusercontent.com/dominictarr/random-name/master/first-names.txt')),
    new ListHandler('lastnames', new SimpleListGenerator(new GeneratorOptions(language: 'en'), 'https://raw.githubusercontent.com/arineng/arincli/master/lib/last-names.txt')),
    new ListHandler('commonWords', new SimpleListGenerator(new GeneratorOptions(language: 'es-es', hasOccurrences: true), 'https://raw.githubusercontent.com/hermitdave/FrequencyWords/master/content/2018/es/es_50k.txt')),
    new ListHandler('maleFirstnames', new SpreadsheetGenerator(new GeneratorOptions(language: 'es-es', fromRow: 7, hasOccurrences: true, occurrenceSeparator: '|', minOccurrences: 500, occurrenceColumn: 2, valueColumn: 1, splitCompoundNames: true, sheetName: 'Hombres'), 'https://www.ine.es/daco/daco42/nombyapel/nombres_por_edad_media.xls')),
    new ListHandler('femaleFirstnames', new SpreadsheetGenerator(new GeneratorOptions(language: 'es-es', fromRow: 7, hasOccurrences: true, occurrenceSeparator: '|', minOccurrences: 500, occurrenceColumn: 2, valueColumn: 1, splitCompoundNames: true, sheetName: 'Mujeres'), 'https://www.ine.es/daco/daco42/nombyapel/nombres_por_edad_media.xls')),
    new ListHandler('lastnames', new SpreadsheetGenerator(new GeneratorOptions(language: 'es-es', fromRow: 5, hasOccurrences: true, occurrenceSeparator: '|', minOccurrences: 500, occurrenceColumn: 2, valueColumn: 1), 'https://www.ine.es/daco/daco42/nombyapel/apellidos_frecuencia.xls')),
    new ListHandler('commonWords', new SimpleListGenerator(new GeneratorOptions(language: 'fi', hasOccurrences: true), 'https://raw.githubusercontent.com/hermitdave/FrequencyWords/master/content/2018/fi/fi_50k.txt')),
    new ListHandler('maleFirstnames', new SpreadsheetGenerator(new GeneratorOptions(language: 'fi', fromRow: 1, hasOccurrences: true, occurrenceSeparator: '|', minOccurrences: 50, sheetName: 'Miehet kaikki'), 'https://www.avoindata.fi/data/dataset/57282ad6-3ab1-48fb-983a-8aba5ff8d29a/resource/08c89936-a230-42e9-a9fc-288632e234f5/download/etunimitilasto-2023-08-01-dvv.xlsx')),
    new ListHandler('femaleFirstnames', new SpreadsheetGenerator(new GeneratorOptions(language: 'fi', fromRow: 1, hasOccurrences: true, occurrenceSeparator: '|', minOccurrences: 50, sheetName: 'Naiset kaikki'), 'https://www.avoindata.fi/data/dataset/57282ad6-3ab1-48fb-983a-8aba5ff8d29a/resource/08c89936-a230-42e9-a9fc-288632e234f5/download/etunimitilasto-2023-08-01-dvv.xlsx')),
    new ListHandler('lastnames', new SpreadsheetGenerator(new GeneratorOptions(language: 'fi', fromRow: 1, hasOccurrences: true, occurrenceSeparator: '|', minOccurrences: 50, sheetName: 'Nimet'), 'https://www.avoindata.fi/data/dataset/57282ad6-3ab1-48fb-983a-8aba5ff8d29a/resource/957d19a5-b87a-4c4d-8595-49c22d9d3c58/download/sukunimitilasto-2023-08-01-dvv.xlsx')),
    new ListHandler('commonWords', new SimpleListGenerator(new GeneratorOptions(language: 'fr', hasOccurrences: true), 'https://raw.githubusercontent.com/hermitdave/FrequencyWords/master/content/2018/fr/fr_50k.txt')),
    new ListHandler('firstnames', new InseeGenerator(new GeneratorOptions(language: 'fr', splitter: "\r\n", fromRow: 123, hasOccurrences: true, occurrenceSeparator: ';', minOccurrences: 100, occurrenceColumn: 3, valueColumn: 1), 'https://www.insee.fr/fr/statistiques/fichier/2540004/nat2021_csv.zip')),
    new ListHandler('lastnames', new InseeGenerator(new GeneratorOptions(language: 'fr', splitter: "\r\n", fromRow: 1, hasOccurrences: true, occurrenceSeparator: "\t", minOccurrences: 100, occurrenceColumn: 11), 'https://www.insee.fr/fr/statistiques/fichier/3536630/noms2008nat_txt.zip')),
    new ListHandler('commonWords', new SimpleListGenerator(new GeneratorOptions(language: 'id', hasOccurrences: true), 'https://raw.githubusercontent.com/hermitdave/FrequencyWords/master/content/2018/id/id_50k.txt')),
    new ListHandler('names', new RegExGenerator(new GeneratorOptions(language: 'id', splitter: ',', splitCompoundNames: true, regEx: '/== [A-Z] ==([^=\.]+)/'), 'https://en.wiktionary.org/w/index.php?title=Appendix:Indonesian_given_names&action=edit')),
    new ListHandler('commonWords', new SimpleListGenerator(new GeneratorOptions(language: 'it', hasOccurrences: true), 'https://raw.githubusercontent.com/hermitdave/FrequencyWords/master/content/2018/it/it_50k.txt')),
    new ListHandler('firstnames', new SimpleListGenerator(new GeneratorOptions(language: 'it'), 'https://gist.githubusercontent.com/allanlewis/ddfe6e7053fd12986589c52edf6ef856/raw/bc6ca7a55527930ec5f25e448c3aa0a7deee2de6/italian-first-names.txt')),
    new ListHandler('lastnames', new RegExGenerator(new GeneratorOptions(language: 'it', regEx: '/\*\[\[([A-z]+)\]\]/'), 'https://en.wiktionary.org/w/index.php?title=Appendix:Italian_surnames&action=edit&section=3')),
    new ListHandler('commonWords', new JapaneseListGenerator(new GeneratorOptions(language: 'ja', hasOccurrences: true), 'https://raw.githubusercontent.com/hermitdave/FrequencyWords/master/content/2018/ja/ja_full.txt')),
    new ListHandler('firstnames', new SimpleListGenerator(new GeneratorOptions(language: 'ja'), 'https://raw.githubusercontent.com/tomoyukikashiro/zxcvbn-japanese-data/main/data/first-names.txt.txt')),
    new ListHandler('lastnames', new SimpleListGenerator(new GeneratorOptions(language: 'ja'), 'https://raw.githubusercontent.com/tomoyukikashiro/zxcvbn-japanese-data/main/data/last-names.txt.txt')),
    new ListHandler('commonWords', new SimpleListGenerator(new GeneratorOptions(language: 'nl-be', hasOccurrences: true), 'https://raw.githubusercontent.com/hermitdave/FrequencyWords/master/content/2018/nl/nl_50k.txt')),
    new ListHandler('boyFirstnames', new SpreadsheetGenerator(new GeneratorOptions(language: 'nl-be', fromRow: 1, hasOccurrences: true, occurrenceSeparator: '|', minOccurrences: 50, occurrenceColumn: 5, valueColumn: 4, sheetName: '1995-2022'), 'https://statbel.fgov.be/sites/default/files/files/documents/bevolking/5.10%20Namen%20en%20voornamen/5.10.%203%20Voornamen%20meisjes%20en%20jongens/Voornamen_Jongens_1995-.xlsx')),
    new ListHandler('girlFirstnames', new SpreadsheetGenerator(new GeneratorOptions(language: 'nl-be', fromRow: 1, hasOccurrences: true, occurrenceSeparator: '|', minOccurrences: 50, occurrenceColumn: 5, valueColumn: 4, sheetName: '1995-2022'), 'https://statbel.fgov.be/sites/default/files/files/documents/bevolking/5.10%20Namen%20en%20voornamen/5.10.%203%20Voornamen%20meisjes%20en%20jongens/Voornamen_meisjes_1995-.xlsx')),
    new ListHandler('lastnames', new SpreadsheetGenerator(new GeneratorOptions(language: 'nl-be', fromRow: 1, hasOccurrences: true, occurrenceSeparator: '|', minOccurrences: 50, occurrenceColumn: 5, valueColumn: 4), 'https://statbel.fgov.be/sites/default/files/files/documents/bevolking/5.10%20Namen%20en%20voornamen/5.10.1%20Familienamen/Familienamen_2022.xlsx')),
    new ListHandler('commonWords', new SimpleListGenerator(new GeneratorOptions(language: 'pl', hasOccurrences: true), 'https://raw.githubusercontent.com/hermitdave/FrequencyWords/master/content/2018/pl/pl_50k.txt')),
    new ListHandler('maleFirstnames', new SimpleListGenerator(new GeneratorOptions(language: 'pl', splitter: "\r\n", fromRow: 1, hasOccurrences: true, occurrenceSeparator: ',', minOccurrences: 200, occurrenceColumn: 2), 'https://api.dane.gov.pl/1.4/resources/44837,lista-imion-meskich-w-rejestrze-pesel-stan-na-31012023-imie-pierwsze/csv')),
    new ListHandler('femaleFirstnames', new SimpleListGenerator(new GeneratorOptions(language: 'pl', splitter: "\r\n", fromRow: 1, hasOccurrences: true, occurrenceSeparator: ',', minOccurrences: 200, occurrenceColumn: 2), 'https://api.dane.gov.pl/1.4/resources/44832,lista-imion-zenskich-w-rejestrze-pesel-stan-na-31012023-imie-pierwsze/csv')),
    new ListHandler('maleLastnames', new SimpleListGenerator(new GeneratorOptions(language: 'pl', splitter: "\r\n", fromRow: 1, hasOccurrences: true, occurrenceSeparator: ',', minOccurrences: 200), 'https://api.dane.gov.pl/1.4/resources/44647,nazwiska-meskie-stan-na-2023-01-30/csv')),
    new ListHandler('femaleLastnames', new SimpleListGenerator(new GeneratorOptions(language: 'pl', splitter: "\r\n", fromRow: 1, hasOccurrences: true, occurrenceSeparator: ',', minOccurrences: 200), 'https://api.dane.gov.pl/1.4/resources/44646,nazwiska-zenskie-stan-na-2023-01-30/csv')),
    new ListHandler('commonWords', new SimpleListGenerator(new GeneratorOptions(language: 'pt-br', hasOccurrences: true), 'https://raw.githubusercontent.com/hermitdave/FrequencyWords/master/content/2018/pt_br/pt_br_50k.txt')),
    new ListHandler('firstnames', new ApiGenerator(new GeneratorOptions(language: 'pt-br', hasOccurrences: true, occurrenceSeparator: '|', requestOptions: new RequestOptions(query: ['qtd' => 5000, 'faixa' => 2010])), 'http://servicodados.ibge.gov.br/api/v1/censos/nomes/faixa')),
    new ListHandler('lastnames', new RegExGenerator(new GeneratorOptions(language: 'pt-br', regEx: '/<li><a .*>([A-z]+)<\/a><\/li>/'), 'https://pt.wiktionary.org/wiki/Ap%C3%AAndice:Sobrenomes_em_portugu%C3%AAs')),
];

(new SingleCommandApplication())
    ->addOption('forceLanguage', null, InputOption::VALUE_OPTIONAL, 'Force a specific language')
    ->addOption('common', null, InputOption::VALUE_NONE, 'Generate common resources')
    ->addOption('wikipedia', null, InputOption::VALUE_NONE, 'Generate wikipedia resources')
    ->setCode(static function (InputInterface $input, OutputInterface $output) use ($listHandlers, $commonListHandlers, $wikipediaListHandlers) {
        $io = new SymfonyStyle($input, $output);

        $forceLanguage = $input->getOption('forceLanguage');
        $common = $input->getOption('common');
        $wikipedia = $input->getOption('wikipedia');

        if ($common) {
            $listHandlers = $commonListHandlers;
        }
        if ($wikipedia) {
            $listHandlers = $wikipediaListHandlers;
        }

        foreach ($listHandlers as $listHandler) {
            if ($forceLanguage && $listHandler->getLanguage() !== $forceLanguage) {
                continue;
            }

            $listHandler->setIo($io);
            $listHandler->run();
        }
    })
    ->run()
;
