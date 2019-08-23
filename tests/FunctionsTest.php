<?php

namespace Apantle\HashMapper\Test;

use \PHPUnit\Framework\TestCase;
use function Apantle\HashMapper\hashMapper;
use function Apantle\HashMapper\collection;

class FunctionsTest extends TestCase
{
    public function testHashMapperFunctor()
    {
        $list = [
            [
                'head' => 1,
                'tail' => 2,
            ],
            [
                'head' => 3,
                'tail' => 4,
            ],
        ];
        $mapper = hashMapper(['head' => 'fst', 'tail' => 'snd']);

        $expectedFirstItemMapped = ['fst' => 1, 'snd' => 2];

        $this->assertEquals($expectedFirstItemMapped, $mapper($list[0]));

        $expectedTransformedList = [['fst' => 1, 'snd' => 2], ['fst' => 3, 'snd' => 4]];

        $listMapper = collection($mapper);

        $this->assertEquals($expectedTransformedList, $listMapper($list));
    }
}
