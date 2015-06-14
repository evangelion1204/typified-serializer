<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @copyright Michael Iwersen <mi.iwersen@gmail.com>
 * @link      https://github.com/evangelion1204/typified-serializer
 */

namespace tests\evangelion1204\Normalizer;


use evangelion1204\Normalizer\ArrayNormalizer;
use Prophecy\Argument;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;

class ArrayNormalizerTest extends \PHPUnit_Framework_TestCase
{

	/**
	 * @dataProvider defaultDataProvider
	 */
	public function testNormalize($src, $expected)
	{
		$mock = $this->prophesize('Symfony\Component\Serializer\NameConverter\NameConverterInterface');
		$mock->normalize(Argument::any())->will(function ($args) {
			return $args[0];
		});
		$normalizer = new ArrayNormalizer(null, null);

		$this->assertEquals($expected, $normalizer->normalize($src));
	}

	/**
	 * @dataProvider defaultDataProvider
	 */
	public function testDenormalize($expected, $src)
	{
		$mock = $this->prophesize('Symfony\Component\Serializer\NameConverter\NameConverterInterface');
		$mock->denormalize(Argument::any())->will(function ($args) {
			return $args[0];
		});

		$normalizer = new ArrayNormalizer(null, $mock->reveal());

		$this->assertEquals($expected, $normalizer->denormalize($src));
	}

	public function defaultDataProvider()
	{
		return array(
			array(array(), array()),
			array(array(1, 2), array(1, 2)),
			array(array(null), array(null)),
			array(array('key' => null), array('key' => null)),
		);
	}

	/**
	 * @dataProvider scalarDataProvider
	 */
	public function testDenormalizeScalar($value)
	{
		$normalizer = new ArrayNormalizer();

		$this->assertEquals($value, $normalizer->denormalize($value));
	}

	public function scalarDataProvider()
	{
		return array(
			array(1),
			array(true),
			array('string'),
		);
	}

}