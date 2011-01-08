<?php

namespace Tests\Behat\Gherkin\Loader;

use Symfony\Component\Finder\Finder,
    Symfony\Component\Translation\Translator,
    Symfony\Component\Translation\MessageSelector;

use Behat\Gherkin\Lexer,
    Behat\Gherkin\Parser,
    Behat\Gherkin\Node,
    Behat\Gherkin\Keywords\SymfonyTranslationKeywords,
    Behat\Gherkin\Loader\GherkinFileLoader;

class GherkinFileLoaderTest extends \PHPUnit_Framework_TestCase
{
    private $loader;
    private $featuresPath;

    protected function setUp()
    {
        $translator     = new Translator('en', new MessageSelector());
        $keywords       = new SymfonyTranslationKeywords($translator);
        $parser         = new Parser(new Lexer($keywords));
        $this->loader   = new GherkinFileLoader($parser);

        $keywords->setXliffTranslationsPath(__DIR__ . '/../../../../i18n');
        $this->featuresPath = realpath(__DIR__ . '/../Fixtures/features');
    }

    public function testSupports()
    {
        $this->assertFalse($this->loader->supports('non-existent path'));
        $this->assertFalse($this->loader->supports('non-existent path:2'));

        $this->assertTrue($this->loader->supports(__DIR__));
        $this->assertFalse($this->loader->supports(__DIR__ . ':d'));
        $this->assertTrue($this->loader->supports(__FILE__));
        $this->assertFalse($this->loader->supports(__FILE__ . '_'));
    }

    public function testLoad()
    {
        $features = $this->loader->load($this->featuresPath);
        $this->assertEquals(count(glob($this->featuresPath . '/*.feature')), count($features));

        $features = $this->loader->load($this->featuresPath . '/pystring.feature');
        $this->assertEquals(1, count($features));
        $this->assertEquals('A py string feature', $features[0]->getTitle());
        $this->assertEquals($this->featuresPath . '/pystring.feature', $features[0]->getFile());

        $features = $this->loader->load($this->featuresPath . '/multiline_name.feature');
        $this->assertEquals(1, count($features));
        $this->assertEquals('multiline', $features[0]->getTitle());
        $this->assertEquals($this->featuresPath . '/multiline_name.feature', $features[0]->getFile());
    }

    public function testBasePath()
    {
        $this->assertFalse($this->loader->supports('features'));
        $this->assertFalse($this->loader->supports('tables.feature'));

        $this->loader->setBasePath($this->featuresPath . '/../');
        $this->assertTrue($this->loader->supports('features'));
        $this->assertFalse($this->loader->supports('tables.feature'));
        $this->assertTrue($this->loader->supports('features/tables.feature'));

        $features = $this->loader->load('features/pystring.feature');
        $this->assertEquals(1, count($features));
        $this->assertEquals('A py string feature', $features[0]->getTitle());
        $this->assertEquals('features/pystring.feature', $features[0]->getFile());

        $this->loader->setBasePath($this->featuresPath);
        $features = $this->loader->load('multiline_name.feature');
        $this->assertEquals(1, count($features));
        $this->assertEquals('multiline', $features[0]->getTitle());
        $this->assertEquals('multiline_name.feature', $features[0]->getFile());
    }
}
