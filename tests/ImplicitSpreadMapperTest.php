<?php

namespace Apantle\HashMapper\Test;

use PHPUnit\Framework\TestCase;
use Apantle\HashMapper\HashmapMapper as HM;
use function Apantle\HashMapper\compose;

class ImplicitSpreadMapperTest extends TestCase
{
    public function testSimpleImplicitSpread()
    {
        $termData = [
          'id' => 31925,
          'link' => 'http://example.com/category/test-term/',
          'name' => 'Test term',
          'slug' => 'test-term',
          'taxonomy' => 'category',
        ];
        $source = [
          'wp:term' => [$termData],
        ];
        $expectedTarget = $termData;

        $hm = new HM([
          'wp:term' => compose('Apantle\HashMapper\head', 'Apantle\HashMapper\identity'),
        ], ['implicitSpread' => true]);

        $target = $hm->apply($source);

        $this->assertEquals($expectedTarget, $target);
    }

    public function testSpreadCallableMapping()
    {
        $mockAux = $this->getMockBuilder(stdClass::class)
            ->setMethods(['mapperCallable'])
            ->getMock();

        $termData = [
            'id' => 31925,
            'link' => 'http://example.com/category/test-term/',
            'name' => 'Test term',
            'slug' => 'test-term',
            'taxonomy' => 'category',
        ];
        $source = [
            'wp:term' => [$termData],
        ];
        $expectedTarget = $termData;

        $hm = new HM([
            'wp:term' => [$mockAux, 'mapperCallable'],
        ], ['implicitSpread' => true]);

        $mockAux->expects($this->once())
            ->method('mapperCallable')
            ->with(
                $this->equalTo([0 => $termData]),
                $this->equalTo($source)
            )->willReturn($termData);

        $target = $hm->apply($source);

        $this->assertEquals($expectedTarget, $target);
    }

    /**
     * @dataProvider Apantle\HashMapper\Test\SpreadMappingTest::spreadMappingDataProvider
     */
    public function testHashMapperReusedReturnsOk($source, $expected)
    {
        $options = ['implicitSpread' => true];

        $mediaMapper = new HM([
            'wp:featuredmedia' => function ($featuredMedia) {
                $media = $featuredMedia[0];
                foreach ($media['media_details']['sizes'] as $key => $size) {
                    if ($key === 'thumbnail') {
                        $pictureUrl = $size['source_url'];
                    }
                }
                return [
                    'img' => $pictureUrl,
                    'alttext' => $media['alt_text'],
                ];
            },
        ], $options);
        $postMapper = new HM([
            'title' => 'title',
            'link' => 'permalink',
            '_embedded' => $mediaMapper,
        ], $options);

        $this->assertEquals($expected, $postMapper->apply($source));
    }
}
