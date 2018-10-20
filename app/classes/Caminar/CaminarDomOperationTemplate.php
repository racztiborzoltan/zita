<?php
declare(strict_types=1);

namespace Zita\TestProject\Caminar;

use Zita\DomOperationTemplate;
use Zita\TestProject\ApplicationAwareTrait;
use Zita\DomOperationListAwareTrait;
use Zita\SiteBuild\SiteBuildInterface;
use Zita\DomOperation\LoadHtmlFromResponseDomOperation;
use Zita\TestProject\Caminar\SiteBuild\CaminarSiteBuild;

class CaminarDomOperationTemplate extends DomOperationTemplate
{

    use ApplicationAwareTrait;
    use DomOperationListAwareTrait;

    /**
     * @var SiteBuildInterface
     */
    private $_sitebuild = null;

    public function setSiteBuild(SiteBuildInterface $sitebuild): self
    {
        $this->_sitebuild = $sitebuild;
        return $this;
    }

    public function getSiteBuild(): SiteBuildInterface
    {
        if (empty($this->_sitebuild)) {
            $this->_sitebuild = $this->_factorySiteBuild();
        }
        return $this->_sitebuild;
    }

    protected function _factorySiteBuild(): SiteBuildInterface
    {
        $application = $this->getApplication();

        $sitebuild = new CaminarSiteBuild();
        $sitebuild->setPageType($sitebuild::PAGE_TYPE_INDEX);

        $unzip_destination = realpath($application->getBaseDir() . '/storage/cache') . '/caminar_html_template';
        $sitebuild->setSourceDirectory($unzip_destination);
        $sitebuild->setDestinationDirectory($application->getPublicDir() . '/cache/caminar_template');
        $sitebuild->setPublicUrl($application->getBaseUri()->withPath($application->getBaseUri()->getPath() . '/cache/caminar_template'));

        return $sitebuild;
    }

    private $_initialized = false;

    protected function _init()
    {
        if ($this->_initialized) {
            return;
        }

        $dom_operation_list = $this->getDomOperationList();

        /**
         * @var SiteBuildInterface $sitebuild
         */
        $sitebuild = $this->getSiteBuild();

        $sitebuild->mergeConfig([
            'header_title' => 'Zita Test Page',
            'header_subtitle' => 'by Caminar Template',
            'section_one' => [
                'image' => 'https://loremflickr.com/1177/443/nature?random=100',
                'header_title' => 'Section One Title',
                'header_subtitle' => 'Section One SubTitle',
                'content' => '
                    <p>
                    Gingerbread jelly beans oat cake caramels chocolate donut. Topping sweet jelly beans jujubes gummi bears apple pie caramels fruitcake jelly beans. Carrot cake candy canes cookie bonbon sweet muffin. Gingerbread sweet roll gummies lollipop. Wafer cake soufflé. Cookie danish pastry bonbon ice cream icing wafer. Danish tootsie roll liquorice chupa chups tart donut cake. Sugar plum cheesecake cheesecake jelly-o sesame snaps powder bonbon icing. Lollipop gummies danish fruitcake fruitcake lollipop sugar plum liquorice. Bear claw apple pie chocolate dragée croissant sesame snaps sugar plum. Candy jelly beans lollipop sesame snaps marshmallow caramels cookie caramels sweet roll. Cookie soufflé ice cream carrot cake cheesecake gingerbread croissant jelly beans ice cream.
                    </p>
                    <p>
                    Dragée lollipop gummies cupcake gummi bears brownie chupa chups. Brownie lollipop muffin lemon drops caramels gingerbread croissant gummi bears brownie. Pudding lollipop croissant jelly beans jelly-o liquorice. Lemon drops croissant lollipop. Jelly danish donut donut danish. Pie sweet roll macaroon sweet roll halvah bear claw oat cake bear claw. Jujubes cotton candy brownie. Soufflé lollipop jelly beans pie biscuit marzipan chocolate cake. Caramels topping caramels candy pudding chocolate bar. Chupa chups biscuit pudding soufflé chocolate caramels gummies danish apple pie. Candy canes muffin chupa chups icing halvah danish muffin lemon drops. Marshmallow pudding icing icing dessert caramels biscuit. Ice cream cheesecake cheesecake cookie gummi bears lemon drops candy cheesecake. Chocolate oat cake pastry gummi bears.
                    </p>
                    <small>
                        Source: <a target="_blank" href="http://www.cupcakeipsum.com/#/paragraphs/5/length/long/with_love/false/start_with_cupcake/false/seed/6f4bc9ad28e646963ea34363bb701203f21c663d7e210305bdb306230e6a1a1f">cupcakeipsum.com</a>
                    </small>
                ',
            ],
            'section_two' => [
                'header_title' => 'Section Two Title',
                'header_subtitle' => 'Section Two SubTitle',
                'images' => [
                    'https://loremflickr.com/600/300/nature?random=' . mt_rand(),
                    'https://loremflickr.com/600/300/nature?random=' . mt_rand(),
                    'https://loremflickr.com/600/300/nature?random=' . mt_rand(),
                    'https://loremflickr.com/600/300/nature?random=' . mt_rand(),
                ],
            ],
            'section_three' => [
                'spotlights' => []
            ],
            'social_hrefs' => [
                'twitter' => 'https://twitter.com/twitter',
                'facebook' => 'https://www.facebook.com/facebook',
                'instagram' => 'https://www.instagram.com',
                'email' => 'mailto:email@domain.com',
            ],
            'footer_copyright_html' => '© Caminar &amp; Zita. Not all rights reserved. Images <a href="https://loremflickr.com">Unsplash</a> Design <a href="https://templated.co">TEMPLATED</a> - Modified by XSLT with Zita PHP Framework',
        ]);

        //
        // generate spotlights items
        //
        $spotlights = [];
        for ($i = 0; $i < 3; $i++) {
            $spotlights[] = [
                'title' => 'Spotlight Title ' . $i,
                'content' => file_get_contents('https://loripsum.net/api/1/link'),
                'image' => 'https://loremflickr.com/700/525/nature?random=' . mt_rand(),
            ];
        }
        $sitebuild->mergeConfig([
            'section_three' => [
                'spotlights' => $spotlights,
            ]
        ]);

        $load_html_dom_operation = new LoadHtmlFromResponseDomOperation();
        $load_html_dom_operation->setResponse($sitebuild->render());
        $dom_operation_list->add($load_html_dom_operation);

        $this->_initialized = true;
    }

    public function executeDomOperation(\DOMDocument $dom_document): \DOMDocument
    {
        $this->_init();
        $dom_operation_list = $this->getDomOperationList();
        return $dom_operation_list->executeDomOperation($dom_document);
    }
}
