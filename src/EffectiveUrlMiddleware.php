<?php
 
 /**
 * (c) Bureau Pieper <piet@bureaupieper.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bureaupieper\StoreeClient;

class EffectiveUrlMiddleware
{
    /**
     * @var \Psr\Http\Message\RequestInterface
     */
    private $request;

    /**
     * @param \Psr\Http\Message\RequestInterface $request
     *
     * @return \Psr\Http\Message\RequestInterface
     */
    public function __invoke(\Psr\Http\Message\RequestInterface $request)
    {
        $this->request = $request;
        return $request;
    }

    /**
     * @return \Psr\Http\Message\RequestInterface
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return string
     */
    public function getUri()
    {
        if (!$this->request) {
            return null;
        }
        return (string)$this->request->getUri();
    }
}
