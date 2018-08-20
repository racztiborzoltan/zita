<?php
declare(strict_types=1);

namespace Zita\Tests;

use PHPUnit\Framework\TestCase;
use Zita\DomOperation\LoadHtmlTextDomOperation;

final class LoadHtmlTextDomOperationTest extends TestCase
{

    private $_test_html_text = '
<!DOCTYPE html>
<meta charset="utf-8">
<title>test title</title>
<p>Test content</p>
';

    protected function _trimHtmlText(string $html_text): string
    {
        $parse = preg_split('#(\s|\r|\r\n|\t|)+$#m', $html_text);
        $parse = array_map('trim', $parse);
        $parse = array_filter($parse);
        return implode('', $parse);
    }

    public function test_1()
    {
        $dom_operation = new LoadHtmlTextDomOperation();
        $dom_operation->setHtmlText($this->_test_html_text);
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
