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
use evangelion1204\Normalizer\StdClassNormalizer;
use evangelion1204\Serializer\TypifiedSerializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;
use tests\evangelion1204\Fixtures\ConstructorWithParamClass;
use tests\evangelion1204\Fixtures\DeepClass;
use tests\evangelion1204\Fixtures\FlatClass;

class TypifiedSerializerTest extends \PHPUnit_Framework_TestCase
{

	/**
	 * @dataProvider defaultDataProvider
	 */
	public function testNormalize($src, $expected)
	{
		$serializer = new TypifiedSerializer(array(new PropertyNormalizer(), new StdClassNormalizer(), new ArrayNormalizer()));

		$this->assertEquals($expected, $serializer->normalize($src));
	}

	/**
	 * @dataProvider defaultDataProvider
	 */
	public function testSerialize($src, $expected_array)
	{
		$serializer = new TypifiedSerializer(
			array(new PropertyNormalizer(), new StdClassNormalizer(), new ArrayNormalizer()),
			array(new JsonEncoder())
		);

		$this->assertEquals(json_encode($expected_array), $serializer->serialize($src, 'json'));
	}

	/**
	 * @dataProvider defaultDataProvider
	 */
	public function testDenormalize($expected, $src)
	{
		$serializer = new TypifiedSerializer(
			array(new ArrayNormalizer(), new StdClassNormalizer(), new PropertyNormalizer())
		);

		$this->assertEquals($expected, $serializer->denormalize($src));
	}

	/**
	 * @dataProvider defaultDataProvider
	 */
	public function testDeserialize($expected, $src_array)
	{
		$serializer = new TypifiedSerializer(
			array(new ArrayNormalizer(), new StdClassNormalizer(), new PropertyNormalizer()),
			array(new JsonEncoder())
		);

		$this->assertEquals($expected, $serializer->deserialize(json_encode($src_array), null, 'json'));
	}

	public function defaultDataProvider()
	{
		$stdClass = new \stdClass();
		$stdClass->prop1 = 1;
		$stdClass->prop2 = 'string';
		$stdClass->prop3 = null;

		$stdClassNormalized = array('prop1' => 1, 'prop2' => 'string', 'prop3' => null, TypifiedSerializer::META_CLASS => 'stdClass');

		$flatClass = new FlatClass();
		$flatClass->setProtectedValue(1)->publicValue = 2;
		$flatClassNormalized = array(
			'protectedValue' => 1,
			'publicValue' => 2,
			TypifiedSerializer::META_CLASS => 'tests\evangelion1204\Fixtures\FlatClass',
		);

		$parentClass = new \stdClass();
		$parentClass->prop3 = true;
		$parentClass->child = $stdClass;

		$parentClassNormalized = array(
			'prop3' => true,
			'child' => $stdClassNormalized,
			TypifiedSerializer::META_CLASS => 'stdClass'
		);

		$deepClass = new DeepClass();
		$deepClass->setProtectedValue(1);

		$nestedDeepClass = new DeepClass();
		$nestedDeepClass->setProtectedValue(2);
		$nestedDeepClass->setParent($deepClass);

		$deepClassNormalized = array(
			'protectedValue' => 1,
			'parent' => null,
			TypifiedSerializer::META_CLASS => 'tests\evangelion1204\Fixtures\DeepClass',
		);

		$nestedDeepClassNormalized = array(
			'protectedValue' => 2,
			'parent' => $deepClassNormalized,
			TypifiedSerializer::META_CLASS => 'tests\evangelion1204\Fixtures\DeepClass',
		);

		$paramConstructorClass = new ConstructorWithParamClass(1);
		$paramConstructorClassNormalized = array(
			'property' => 1,
			TypifiedSerializer::META_CLASS => 'tests\evangelion1204\Fixtures\ConstructorWithParamClass',
		);

		return array(
			array(array(), array()),
			array(array('key' => 'value'), array('key' => 'value')),
			array(array('key' => null), array('key' => null)),
			array(array(array('key' => null)), array(array('key' => null))),
			array(array(null), array(null)),
			array($stdClass, $stdClassNormalized),
			array($flatClass, $flatClassNormalized),
			array(array($stdClass), array($stdClassNormalized)),
			array($deepClass, $deepClassNormalized),
			array($nestedDeepClass, $nestedDeepClassNormalized),
			array($paramConstructorClass, $paramConstructorClassNormalized),
		);
	}

}