<?php
declare(strict_types=1);

namespace Zita\Tests;

use PHPUnit\Framework\TestCase;
use Zita\DomOperation\LoadHtmlFileDomOperation;

final class LoadHtmlFileDomOperationTest extends TestCase
{

    private $_test_html_path = null;

    private $_test_html_text = null;

    public function setUp()
    {
        $this->_test_html_path = realpath(__DIR__ . '/test.html');
        $this->_test_html_text = file_get_contents($this->_test_html_path);
    }

    protected function _trimHtmlText(string $html_text): string
    {
        $parse = preg_split('#(\s|\r|\r\n|\t|)+$#m', $html_text);
        $parse = array_map('trim', $parse);
        $parse = array_filter($parse);
        return implode('', $parse);
    }

    public function test_1()
    {
        $dom_operation = new LoadHtmlFileDomOperation();
        $dom_operation->setHtmlFilePath($this->_test_html_path);
        $dom_operation->setLoadOptions(LIBXML_HTML_NOIMPLIED);

        $dom_document = new \DOMDocument();

        /**
         * @var \DOMDocument $dom_document
         */
        $dom_document = $dom_operation->executeDomOperation($dom_document);
        $result = $dom_document->saveHTML();

        $this->assertEquals($this->_trimHtmlText($this->_test_html_text), $this->_trimHtmlText($result));
   }
}
