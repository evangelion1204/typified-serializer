<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @copyright Michael Iwersen <mi.iwersen@gmail.com>
 * @link      https://github.com/evangelion1204/typified-serializer
 */

namespace tests\evangelion1204\Normalizer;


use evangelion1204\Normalizer\TypifiedNormalizer;
use tests\evangelion1204\Fixtures\FlatClass;

class TypifiedNormalizerTest extends \PHPUnit_Framework_TestCase
{

	/**
	 * @dataProvider defaultDataProvider
	 */
	public function testNormalize($src, $expected)
	{
		$normalizer = new TypifiedNormalizer();

		$this->assertEquals($expected, $normalizer->normalize($src));
	}

	/**
	 * @dataProvider defaultDataProvider
	 */
	public function testDenormalize($expected, $src)
	{
		$normalizer = new TypifiedNormalizer();

		$this->assertEquals($expected, $normalizer->denormalize($src));
	}

	public function defaultDataProvider()
	{
		$stdClass = new \stdClass();
		$stdClass->prop1 = 1;
		$stdClass->prop2 = 'string';
		$stdClass->prop3 = null;

		$stdClassNormalized = array('prop1' => 1, 'prop2' => 'string', 'prop3' => null, TypifiedNormalizer::META_CLASS => 'stdClass');

		$flatClass = new FlatClass();
		$flatClass->setProtectedValue(1)->publicValue = 2;
		$flatClassNormalized = array(
			'protectedValue' => 1,
			'publicValue' => 2,
			TypifiedNormalizer::META_CLASS => 'tests\evangelion1204\Fixtures\FlatClass',
		);

		return array(
			array($stdClass, $stdClassNormalized),
			array($flatClass, $flatClassNormalized),
		);
	}

}