<?php
 
 /**
 * (c) Bureau Pieper <piet@bureaupieper.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bureaupieper\StoreeClient\Fixtures;


use Bureaupieper\StoreeClient\SimpleArrayAccessorTrait;

class SimpleArrayObject extends \ArrayObject
{
    use SimpleArrayAccessorTrait;
}