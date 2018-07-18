<?php
namespace Zita;

use Psr\Http\Server\MiddlewareInterface;

/**
 * Middleware list with priority
 *
 * @author Rácz Tibor Zoltán <racztiborzoltan@gmail.com>
 *
 */
class MiddlewareList implements \Iterator, \Countable
{

    /**
     * List of prioritized middlewares
     *
     * @var \SplPriorityQueue
     */
    private $_middlewares = null;

    /**
     * Last automatic priority
     *
     * @var integer
     */
    private $_last_automatic_priority = PHP_INT_MAX;

    protected function _getMiddlewares(): \SplPriorityQueue
    {
        if (empty($this->_middlewares)) {
            $this->_middlewares = new \SplPriorityQueue();
        }
        return $this->_middlewares;
    }

    /**
     * Returns last automatic priority number
     *
     * @return int
     */
    protected function _getLastAutomaticPriority(): int
    {
        return $this->_last_automatic_priority;
    }

    /**
     * Returns next automatic priority number
     *
     * @return int
     */
    protected function _getNextAutomaticPriority(): int
    {
        return --$this->_last_automatic_priority;
    }

    /**
     * Add middleware with optional priority
     *
     * if priority is not present, generate automatic priority.
     *
     * @param MiddlewareInterface $middleware
     * @param int $priority
     * @return \Zita\MiddlewareList
     */
    public function add(MiddlewareInterface $middleware, int $priority = null)
    {
        if (is_null($priority)) {
            $priority = $this->_getNextAutomaticPriority();
        }
        $this->_getMiddlewares()->insert($middleware, $priority);
        return $this;
    }

    public function remove(MiddlewareInterface $middleware)
    {
        //
        // thanks for original source code: https://gist.github.com/denisdeejay/1ee0ce70b3afe76cf31e
        //
        /**
         * @var \SplPriorityQueue $list
         */
        $list = $this->_getMiddlewares();
        $original_extract_flag = $list->getExtractFlags();
        $this->_getMiddlewares()->setExtractFlags(\SplPriorityQueue::EXTR_BOTH);

        $new_list = [];
        foreach($list as $item){
            if($item['data'] !== $middleware){
                $new_list[] = $item;
            }
        }
        unset($item);

        $this->_getMiddlewares()->setExtractFlags($original_extract_flag);

        foreach($new_list as $item){
            $list->insert($item['data'], $item['priority']);
        }

        return $this;
    }

    public function current()
    {
        return $this->_getMiddlewares()->current();
    }

    public function next()
    {
        return $this->_getMiddlewares()->next();
    }

    public function key()
    {
        return $this->_getMiddlewares()->key();
    }

    public function valid()
    {
        return $this->_getMiddlewares()->valid();
    }

    public function rewind ()
    {
        return $this->_getMiddlewares()->rewind();
    }

    public function count ()
    {
        return $this->_getMiddlewares()->count();
    }
}