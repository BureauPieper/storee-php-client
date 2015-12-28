<?php
 
 /**
 * (c) Bureau Pieper <piet@bureaupieper.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bureaupieper\StoreeClient\Client;


use Bureaupieper\StoreeClient\Client\Config\Exception;
use Bureaupieper\StoreeClient\Resources\ConfigTree;
use Bureaupieper\StoreeClient\SimpleArrayAccessorTrait;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;

/**
 * Class Config
 * @package Bureaupieper\StoreeClient\Client
 *
 * @method getEndpoint
 * @method getApikey
 * @method getVersion
 * @method getPlatform
 * @method getFormat
 * @method getCacheTtr
 */
class Config extends \ArrayObject
{
    use SimpleArrayAccessorTrait;

    function __construct(array $config, TreeBuilder $tree = null) {
        $processor = new Processor();
        $tree = $tree ?: ConfigTree::get();
        $config = ['bureaupieper_storee' => $config];
        parent::__construct($processor->process($tree->buildTree(), $config));
    }

    public function offsetSet($offset, $value) {
        throw new Exception('Immutable');
    }

    public function offsetUnset($offset) {
        throw new Exception('Immutable');
    }
}