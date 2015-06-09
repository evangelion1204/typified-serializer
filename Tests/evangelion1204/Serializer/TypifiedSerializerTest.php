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
use evangelion1204\Serializer\TypifiedSerializer;
use Symfony\Component\Serializer\Serializer;
use tests\evangelion1204\Fixtures\FlatClass;

class TypifiedSerializerTest extends \PHPUnit_Framework_TestCase
{

	/**
	 * @dataProvider defaultDataProvider
	 */
	public function testNormalize($src, $expected)
	{
		$serializer = new TypifiedSerializer(array(new TypifiedNormalizer()));

		$this->assertEquals($expected, $serializer->normalize($src));
	}

	/**
	 * @dataProvider defaultDataProvider
	 */
	public function _testDenormalize($expected, $src)
	{
		$serializer = new Serializer(array(new TypifiedNormalizer()));

		$this->assertEquals($expected, $serializer->denormalize($src, ''));
	}

	public function defaultDataProvider()
	{
		$stdClass = new \stdClass();
		$stdClass->prop1 = 1;
		$stdClass->prop2 = 'string';

		$stdClassNormalized = array('prop1' => 1, 'prop2' => 'string', TypifiedNormalizer::META_CLASS => 'stdClass');

		$flatClass = new FlatClass();
		$flatClass->setProtectedValue(1)->publicValue = 2;
		$flatClassNormalized = array(
			'protectedValue' => 1,
			'publicValue' => 2,
			TypifiedNormalizer::META_CLASS => 'tests\evangelion1204\Fixtures\FlatClass',
		);

		$parentClass = new \stdClass();
		$parentClass->prop3 = true;
		$parentClass->child = $stdClass;

		$parentClassNormalized = array(
			'prop3' => true,
			'child' => $stdClassNormalized,
			TypifiedNormalizer::META_CLASS => 'stdClass'
		);

		return array(
			array(array(), array()),
			array(array('key' => 'value'), array('key' => 'value')),
			array($stdClass, $stdClassNormalized),
			array($flatClass, $flatClassNormalized),
			array(array($stdClass), array($stdClassNormalized)),
			array($parentClass, $parentClassNormalized),
		);
	}

}