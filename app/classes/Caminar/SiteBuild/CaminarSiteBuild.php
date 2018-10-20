<?php
declare(strict_types=1);

namespace Zita\TestProject\Caminar\SiteBuild;

use Nyholm\Psr7\Response;
use Zita\SiteBuild\SiteBuild;
use Stex\CssSelector\CssSelectorConverter;

class CaminarSiteBuild extends SiteBuild
{

    const PAGE_TYPE_INDEX = 'index';

    const TEMPLATE_DOWNLOAD_URL = 'https://templated.co/caminar/download';

    private $_default_config = [
        /**
         * @var string
         */
        'header_title' => null,
        /**
         * @var string
         */
        'header_subtitle' => null,
        /**
         * datas for "section#one" DOM element
         * @var array
         */
        'section_one' => [
            /**
             * image src
             * @var string
             */
            'image' => null,
            /**
             * @var string
             */
            'header_title' => null,
            /**
             * @var string
             */
            'header_subtitle' => null,
        ],
        'section_two' => [
            /**
             * @var string
             */
            'header_title' => null,
            /**
             * @var string
             */
            'header_subtitle' => null,
            /**
             * @var string[]
             */
            'images' => null,
        ],
        'section_three' => [
            'spotlights' => [
                [
                    /**
                     * @var string
                     */
                    'title' => null,
                    /**
                     * @var string
                     */
                    'content' => null,
                    /**
                     * @var string
                     */
                    'image' => null,
                ],
                //
                // ... more spotlight item
                //
            ]
        ],
        //
        // Social hrefs
        // if one item is empty, remove the social icon from template
        //
        'social_hrefs' => [
            /**
             * @var string
             */
            'twitter' => null,
            /**
             * @var string
             */
            'facebook' => null,
            /**
             * @var string
             */
            'instagram' => null,
            /**
             * @var string
             */
            'email' => null,
        ],
        'footer_copyright_html' => '© Caminar & Zita. Not all rights reserved. Images <a href="https://unsplash.com">Unsplash</a> Design <a href="https://templated.co">TEMPLATED</a>',
    ];

    /**
     * @var \DOMDocument
     */
    private $_dom_document = null;

    /**
     * @return \DOMDocument
     */
    protected function _getDomDocument(): \DOMDocument
    {
        return $this->_dom_document;
    }

    public function getValidPageTypes(): array
    {
        return [
            static::PAGE_TYPE_INDEX
        ];
    }

    public function render(): \Psr\Http\Message\ResponseInterface
    {
        $content = $this->_getRawTemplateHtmlContent();

        // copy "assets" sitebuild folder to destination folder:
        $this->copyDirectory('assets');

        $dom_document = new \DOMDocument();
        $temp = libxml_use_internal_errors(true);
        $dom_document->loadHTML($content);
        libxml_use_internal_errors($temp);
        unset($temp);
        libxml_clear_errors();

        $this->_dom_document = $dom_document;

        $content = $this->_fixResourcesUri($content);

        $this->_operationsWithHeader();
        $this->_operationsWithSectionOne();
        $this->_operationsWithSectionTwo();
        $this->_operationsWithSectionThree();
        $this->_operationsWithFooter();

        $response = new Response();
        $response->getBody()->write($dom_document->saveHTML($dom_document));
        $response->getBody()->rewind();
        return $response;
    }

    protected function _getRawTemplateHtmlContent(): string
    {
        $page_type = $this->getPageType();
        $this->_unzipSiteBuild();
        return file_get_contents($this->getSourceDirectory() . '/'.$page_type.'.html');
    }

    protected function _unzipSiteBuild(): self
    {
        $source_dir = $this->getSourceDirectory();

        // -------------------------------------------------------
        // Automatic downloading templates for sitebuild
        //
        if (!is_dir($source_dir)) {
            mkdir($source_dir, 0777, true);
        }
        $sitebuild_zip_path = realpath($source_dir) . '/caminar_sitebuild.zip';
        if (!is_file($sitebuild_zip_path)) {
            file_put_contents($sitebuild_zip_path, fopen(static::TEMPLATE_DOWNLOAD_URL, 'r'));
        }
        //
        // unzip:
        //
        $unzip_destination = $source_dir;
        if (!is_file($unzip_destination . '/index.html')) {
            $zip = new \ZipArchive;
            if ($zip->open($sitebuild_zip_path) === TRUE) {
                $zip->extractTo($unzip_destination);
                $zip->close();
            } else {
                throw new \LogicException('sitebuild unzip is failed: ' . $zip->getStatusString());
            }
        }
        // -------------------------------------------------------
        return $this;
    }

    protected function _fixResourcesUri(string $html_content)
    {
        $change = false;

        $dom_document = $this->_getDomDocument();
        $xpath = new \DOMXPath($dom_document);

        $nodes = $xpath->query('//img[@src] | //script[@src] | //link[@rel="stylesheet"][@href]');
        foreach ($nodes as $node) {
            /**
             * @var \DOMElement $node
             */
            $attr_name = $node->nodeName === 'link' ? 'href' : 'src';
            $attr_value = $node->getAttribute($attr_name);
            // if resource has absolute uri:
            if (preg_match('#^https?://#i', $attr_value)) {
                continue;
            }
            $this->copyFile($node->getAttribute($attr_name));
            $node->setAttribute($attr_name, $this->getPublicUrl() . '/' . $attr_value);
            $change = true;
        }
        return $change ? $dom_document->saveHTML($dom_document) : $html_content;
    }

    protected function _operationsWithHeader()
    {
        /**
         * @var \DOMDocument $dom_document
         */
        $dom_document = $this->_getDomDocument();
        $xpath = new \DOMXPath($dom_document);

        $config = $this->getConfig();

        if (isset($config['header_title'])) {
            $nodes = $xpath->query((new CssSelectorConverter())->toXPath('header a') .'/text()');
            foreach ($nodes as $node) {
                /**
                 * @var \DOMText $node
                 */
                // $node->textContent = 'Zita Test Page';
                $node->textContent = $config['header_title'];
            }
        }
        if (isset($config['header_subtitle'])) {
            $nodes = $xpath->query((new CssSelectorConverter())->toXPath('header a span'));
            foreach ($nodes as $node) {
                /**
                 * @var \DOMText $node
                 */
                // $node->textContent = 'by Caminar template';
                $node->textContent = $config['header_subtitle'];
            }
        }
    }

    protected function _operationsWithSectionOne()
    {
        /**
         * @var \DOMDocument $dom_document
         */
        $dom_document = $this->_getDomDocument();
        $xpath = new \DOMXPath($dom_document);

        $section_one = $dom_document->getElementById('one');

        $config = $this->getConfig();
        if (!isset($config['section_one'])) {
            $this->_removeNode($section_one);
            return;
        }
        $section_config = $config['section_one'];

        $nodes = $xpath->query((new CssSelectorConverter())->toXPath('.image img'), $section_one);
        if (isset($section_config['image'])) {
            foreach ($nodes as $image_node) {
                $image_node->setAttribute('src', $section_config['image']);
            }
        } else {
            $this->_removeNode($nodes);
        }
        unset($nodes);

        $nodes = $xpath->query((new CssSelectorConverter())->toXPath('header *:first-child'), $section_one);
        if (isset($section_config['header_title'])) {
            foreach ($nodes as $section_header_title) {
                $section_header_title->textContent = $section_config['header_title'];
           }
        } else {
            $this->_removeNode($nodes);
        }
        unset($nodes);

        $nodes = $xpath->query((new CssSelectorConverter())->toXPath('header p'), $section_one);
        if (isset($section_config['header_subtitle'])) {
            foreach ($nodes as $section_header_subtitle) {
                $section_header_subtitle->textContent = $section_config['header_subtitle'];
           }
        } else {
            $this->_removeNode($nodes);
        }
        unset($nodes);

        $nodes = $xpath->query((new CssSelectorConverter())->toXPath('.content'), $section_one);
        if (isset($section_config['content'])) {
            foreach ($nodes as $section_content) {
                $this->_setInnerHtml($section_content, $section_config['content']);
           }
        } else {
            $this->_removeNode($nodes);
        }
        unset($nodes);
    }

    /**
     * Remove DOM node
     *
     * @param \DOMNode|\DOMNodeList $node node or nodelist
     */
    protected function _removeNode($node)
    {
        if ($node instanceof \DOMNodeList) {
            foreach ($node as $n) {
                $n->parentNode->removeChild($n);
           }
        }
        if ($node instanceof \DOMNode) {
            $node->parentNode->removeChild($node);
        }
    }

    /**
     * Set innerHTML of an \DOMNode object
     *
     * @param \DOMNode $element
     * @param string $innerhtml
     */
    protected function _setInnerHtml(\DOMNode $element, string $innerhtml)
    {
        // ---------------------------------------------------
        // set inner html:
        $element->textContent = '';
        $fragment = $element->ownerDocument->createDocumentFragment();
        $fragment->appendXML($innerhtml);
        $element->appendChild($fragment);
        // ---------------------------------------------------
    }

    protected function _operationsWithSectionTwo()
    {
        /**
         * @var \DOMDocument $dom_document
         */
        $dom_document = $this->_getDomDocument();
        $xpath = new \DOMXPath($dom_document);

        $section_two = $dom_document->getElementById('two');

        $config = $this->getConfig();
        if (!isset($config['section_two'])) {
            $this->_removeNode($section_two);
            return;
        }
        $section_config = $config['section_two'];

        $nodes = $xpath->query((new CssSelectorConverter())->toXPath('header *:first-child'), $section_two);
        if (isset($section_config['header_title'])) {
            foreach ($nodes as $section_header_title) {
                $section_header_title->textContent = $section_config['header_title'];
           }
        } else {
            $this->_removeNode($nodes);
        }
        unset($nodes);

        $nodes = $xpath->query((new CssSelectorConverter())->toXPath('header p'), $section_two);
        if (isset($section_config['header_subtitle'])) {
            foreach ($nodes as $section_header_subtitle) {
                $section_header_subtitle->textContent = $section_config['header_subtitle'];
           }
        } else {
            $this->_removeNode($nodes);
        }
        unset($nodes);

        //
        // images in section two
        //
        $nodes = $xpath->query((new CssSelectorConverter())->toXPath('.gallery'), $section_two);
        if ($nodes->length > 0) {
            $gallery_node = $nodes->item(0);
            if (isset($section_config['images']) && is_array($section_config['images']) && !empty($section_config['images'])) {
                $gallery_item_template = $xpath->query((new CssSelectorConverter())->toXPath(':first-child'), $gallery_node);
                if ($gallery_item_template->length > 0) {
                    $gallery_item_template = $gallery_item_template->item(0)->cloneNode(true);
                    $gallery_node->textContent = '';
                }
                foreach ($section_config['images'] as $image_src) {
                    $gallery_item = $gallery_item_template->cloneNode(true);
                    $image_link = $xpath->query((new CssSelectorConverter())->toXPath('a'), $gallery_item);
                    if ($image_link->length > 0) {
                        $image_link = $image_link->item(0);
                        /**
                         * @var \DOMElement $image_link
                         */
                        $image_link->setAttribute('href', $image_src);

                        $image_tag = $xpath->query((new CssSelectorConverter())->toXPath('img'), $image_link);
                        if ($image_tag->length > 0) {
                            /**
                             * @var \DOMElement $image_tag
                             */
                            $image_tag = $image_tag->item(0);
                            $image_tag->setAttribute('src', $image_src);
                        }
                        $gallery_node->appendChild($gallery_item);
                    }
                }
            } else {
                $this->_removeNode($gallery_node);
            }
        }
    }

    protected function _operationsWithSectionThree()
    {
        /**
         * @var \DOMDocument $dom_document
         */
        $dom_document = $this->_getDomDocument();
        $xpath = new \DOMXPath($dom_document);

        $section_three = $dom_document->getElementById('three');

        $config = $this->getConfig();
        if (!isset($config['section_three'])) {
            $this->_removeNode($section_three);
            return;
        }
        $section_config = $config['section_three'];

        $spotlight_nodes = $xpath->query((new CssSelectorConverter())->toXPath('.spotlight'), $section_three);
        if (isset($section_config['spotlights']) && is_array($section_config['spotlights']) && !empty($section_config['spotlights'])) {
            if ($spotlight_nodes->length > 0) {
                $spotlight_template = $spotlight_nodes->item(0)->cloneNode(true);
                // remove template nodes:
                $this->_removeNode($spotlight_nodes);
                foreach ($section_config['spotlights'] as $spotlight_index => $spotlight_data) {
                    if (empty($spotlight_data)) {
                        continue;
                    }
                    /**
                     * @var \DOMElement $spotlight_node
                     */
                    $spotlight_node = $spotlight_template->cloneNode(true);

                    if ($spotlight_index % 2 == 1) {
                        $spotlight_node->setAttribute('class', $spotlight_node->getAttribute('class') . ' alt');
                    }

                    $spotlight_image_container = $xpath->query((new CssSelectorConverter())->toXPath('.image'), $spotlight_node);
                    if ($spotlight_image_container->length > 0) {
                        $spotlight_image_container = $spotlight_image_container->item(0);
                        if (isset($spotlight_data['image'])) {
                            $spotlight_image = $xpath->query((new CssSelectorConverter())->toXPath('img'), $spotlight_image_container);
                            if ($spotlight_image->length > 0) {
                                $spotlight_image = $spotlight_image->item(0);
                                /**
                                 * @var \DOMElement $spotlight_image
                                 */
                                $spotlight_image->setAttribute('src', $spotlight_data['image']);
                            }
                        } else {
                            $this->_removeNode($spotlight_image_container);
                        }
                    }

                    $spotlight_title = $xpath->query((new CssSelectorConverter())->toXPath('h3'), $spotlight_node);
                    if ($spotlight_title->length > 0) {
                        $spotlight_title = $spotlight_title->item(0);
                        if (isset($spotlight_data['title'])) {
                            $spotlight_title->textContent = $spotlight_data['title'];
                        } else {
                            $this->_removeNode($spotlight_title);
                        }
                    }

                    $spotlight_content = $xpath->query((new CssSelectorConverter())->toXPath('p'), $spotlight_node);
                    if ($spotlight_content->length > 0) {
                        $spotlight_content = $spotlight_content->item(0);
                        if (isset($spotlight_data['content'])) {

                            $fragment = $spotlight_content->ownerDocument->createDocumentFragment();
                            $fragment->appendXML('<div class="content">'.$spotlight_data['content'].'</div>');
                            $fragment = $spotlight_content->ownerDocument->importNode($fragment, true);
                            $spotlight_content->parentNode->replaceChild($fragment, $spotlight_content);
                        } else {
                            $this->_removeNode($spotlight_content);
                        }
                    }

                    $section_three->appendChild($spotlight_node);
                }
            }
        } else {
            $this->_removeNode($spotlight_nodes);
        }
    }

    protected function _operationsWithFooter()
    {
        /**
         * @var \DOMDocument $dom_document
         */
        $dom_document = $this->_getDomDocument();
        $xpath = new \DOMXPath($dom_document);

        $footer_node = $dom_document->getElementsByTagName('footer');
        if ($footer_node->length == 0) {
            return;
        }
        $footer_node = $footer_node->item(0);

        $config = $this->getConfig();

        $social_icons_container = $xpath->query((new CssSelectorConverter())->toXPath('.icons'), $footer_node);
        if ($social_icons_container->length > 0) {
            if (isset($config['social_hrefs']) && is_array($config['social_hrefs']) && !empty($config['social_hrefs'])) {

                $social_hrefs = $config['social_hrefs'];
                $social_icons_container = $social_icons_container->item(0);

                if (isset($social_hrefs['twitter'])) {
                    $this->_modifySocialLink($social_icons_container, '.icon.fa-twitter', $social_hrefs['twitter']);
                }
                if (isset($social_hrefs['facebook'])) {
                    $this->_modifySocialLink($social_icons_container, '.icon.fa-facebook', $social_hrefs['facebook']);
                }
                if (isset($social_hrefs['instagram'])) {
                    $this->_modifySocialLink($social_icons_container, '.icon.fa-instagram', $social_hrefs['instagram']);
                }
                if (isset($social_hrefs['email'])) {
                    $this->_modifySocialLink($social_icons_container, '.icon.fa-envelope-o', $social_hrefs['email']);
                }

            } else {
                $this->_removeNode($social_icons_container);
            }
        }


        $copyright_node = $xpath->query((new CssSelectorConverter())->toXPath('.copyright'), $footer_node);
        if ($copyright_node->length > 0) {
            if (isset($config['footer_copyright_html'])) {
                $copyright_node = $copyright_node->item(0);
                $this->_setInnerHtml($copyright_node, $config['footer_copyright_html']);
            } else {
                $this->_removeNode($copyright_node);
            }
        }
        return;

        //
        // change copyright
        //
        $dom_operation_list->add((new CallbackDomOperation())->setCallback(function(\DOMDocument $dom_document){
            $xpath = new \DOMXPath($dom_document);
            $nodes = $xpath->query((new CssSelectorConverter())->toXPath('footer .copyright'));
            foreach ($nodes as $node) {
                /**
                 * @var \DOMElement $node
                 */
                $span = $dom_document->createElement('span');
                $span->textContent = '';
                $node->appendChild($span);
            }
            return $dom_document;
        }));

    }

    protected function _modifySocialLink($social_icons_container, $css_selector, $social_href)
    {
        $dom_document = $this->_getDomDocument();
        $xpath = new \DOMXPath($dom_document);

        $twitter_link = $xpath->query((new CssSelectorConverter())->toXPath($css_selector), $social_icons_container);
        if (!is_null($social_href)) {
            $twitter_link = $twitter_link->item(0);
            /**
             * @var \DOMElement $twitter_link
             */
            $twitter_link->setAttribute('href', $social_href);
        } else {
            foreach ($twitter_link as $node) {
                $this->_removeNode($node->parentNode);
            }
        }
    }
}
