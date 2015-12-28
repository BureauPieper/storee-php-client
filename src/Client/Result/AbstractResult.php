<?php
 
 /**
 * (c) Bureau Pieper <piet@bureaupieper.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bureaupieper\StoreeClient\Client\Result;


use Bureaupieper\StoreeClient\SimpleArrayAccessorTrait;

abstract class AbstractResult extends \ArrayObject
{
    use SimpleArrayAccessorTrait;
}