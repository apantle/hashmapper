<?php

use Apantle\HashMapper\HashmapMapper;

class ThrowLogicExceptionIfNotCallableTest extends PHPUnit\Framework\TestCase
{

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage mapper is not a callable or instance of HashmapMapperInterface
     */
    public function testApply()
    {
        $hm = new HashmapMapper([
            'input' => ['target', 'not_a_callable']
        ]);
        $hm->apply(['input' => 'valid, but not the mapper spec']);
    }
}
