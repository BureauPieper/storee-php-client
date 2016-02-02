<?php

/**
* (c) Bureau Pieper <piet@bureaupieper.nl>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Bureaupieper\StoreeClient\Client\Request;


abstract class AbstractRequest
{
    /**
     * @var bool
     */
    protected $use_cache = true;

    /**
     * @var integer
     */
    protected $ttr;

    /**
     * @var array
     */
    protected $args;

    /**
     * @param array $args
     */
    function __construct(array $args = []) {
        $this->args = $args;
    }

    /**
     * @return mixed
     */
    abstract function getPath();

    /**
     * @return mixed
     */
    public function getArgs()
    {
        return $this->args;
    }

    /**
     * @param mixed $args
     */
    public function setArgs(array $args)
    {
        $this->args = $args;
    }

    /**
     * @return boolean
     */
    public function isUseCache()
    {
        return $this->use_cache;
    }

    /**
     * @param boolean $use_cache
     */
    public function setUseCache($use_cache)
    {
        $this->use_cache = $use_cache;
    }

    /**
     * @return mixed
     */
    public function getTtr()
    {
        return $this->ttr;
    }

    /**
     * @param mixed $ttr
     */
    public function setTtr($ttr)
    {
        $this->ttr = (int)$ttr;
    }

    /**
     * @param $response
     * @return mixed
     */
    public function handleResponse($response)
    {
        if (!$response['result']) {
            return null;
        }
        return $response['result'];
    }
}