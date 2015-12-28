<?php

/**
 * (c) Bureau Pieper <piet@bureaupieper.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bureaupieper\StoreeClient;

use Bureaupieper\StoreeClient\Fixtures\SimpleArrayObject;

class SimpleArrayAccessorTraitTest extends BaseTest
{
    private $ob;

    function setUp() {
        $this->ob = new SimpleArrayObject([
            'plain' => '1',
            'under_score' => '2',
            'a' => [
                'plain' => 'x',
                'under_score' => 'y'
            ]
        ]);
    }

    function testArrayAccessorHelper() {
        $this->assertInternalType('array', $this->ob->getA());
        $this->assertArrayHasKey('plain', $this->ob->getA());
        $this->assertEquals('x', $this->ob->getAPlain());
        $this->assertEquals('y', $this->ob->getAUnder_score());
    }
}