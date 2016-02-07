<?php
 
 /**
 * (c) Bureau Pieper <piet@bureaupieper.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bureaupieper\StoreeClient;


trait SimpleArrayAccessorTrait
{
    function __call($name, $args) {
        $name = preg_replace('/^(get|is|has)/', '', $name);
        $split = preg_split('/(?=[A-Z])/', $name, -1, PREG_SPLIT_NO_EMPTY);
        $pool = $this;
        foreach($split as $token) {
            $token = strtolower($token);
            if (!array_key_exists($token, $pool)) {
                throw new Exception(sprintf('Invalid method call %s(%s).', $name, $token));
            }
            $pool = $pool[$token];
        }
        return $pool;
    }

    /**
     * For Twig, it doesnt get to the point of calling a method if it doesnt exist
     */
    function __get($name) {
        return call_user_func([$this, $name]);
    }
}
