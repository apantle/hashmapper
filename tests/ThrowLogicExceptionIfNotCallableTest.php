<?php

use Apantle\HashMapper\HashmapMapper;

class ThrowLogicExceptionIfNotCallableTest extends PHPUnit\Framework\TestCase
{
    public function testApply()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('mapper is not a callable or instance of HashmapMapperInterface');

        $hm = new HashmapMapper([
            'input' => ['target', 'not_a_callable']
        ]);
        $hm->apply(['input' => 'valid, but not the mapper spec']);
    }
}
