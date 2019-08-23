<?php

namespace Apantle\HashMapper\Test;

use PHPUnit\Framework\TestCase;
use Apantle\HashMapper\HashmapMapperInterface;
use Apantle\HashMapper\HashmapMapper as HM;

class UnaryFunctionsSupportTest extends TestCase
{
    public function testShouldPassOnlyOneArgToUnary()
    {
	    $hm = new HM(['test' => ['passed', 'strval']]);

	    $source = [ 'test' => 1 ];

	    $this->assertEquals([ 'passed' => '1' ], $hm->apply($source)); 
    }
}

