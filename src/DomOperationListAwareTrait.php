<?php
declare(strict_types=1);

namespace Zita;

use DomOperationQueue\DomOperationQueue;

/**
 *
 * DomOperationList aware trait
 *
 * @method \Zita\TestProject\Application setApplication(\Zita\TestProject\Application $application)
 * @method \Zita\TestProject\Application getApplication()
 *
 * @author Rácz Tibor Zoltán <racztiborzoltan@gmail.com>
 *
 */
trait DomOperationListAwareTrait
{

    /**
     * @var \DomOperationQueue\DomOperationQueue
     */
    private $_dom_operation_list = null;

    public function setDomOperationList(DomOperationQueue $dom_operation_list): self
    {
        $this->_dom_operation_list = $dom_operation_list;
        return $this;
    }

    public function getDomOperationList(): DomOperationQueue
    {
        if (empty($this->_dom_operation_list)) {
            $this->setDomOperationList(new DomOperationQueue());
        }
        return $this->_dom_operation_list;
    }
}