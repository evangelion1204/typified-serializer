<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @copyright Michael Iwersen <mi.iwersen@gmail.com>
 * @link      https://github.com/evangelion1204/typified-serializer
 */

namespace evangelion1204\Normalizer;


use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;

class TypifiedNormalizer extends PropertyNormalizer
{
	const META_CLASS = '__class__';

	/**
	 * {@inheritdoc}
	 * @throws CircularReferenceException
	 */
	public function normalize($object, $format = null, array $context = array())
	{
		$normalized = parent::normalize($object, $format, $context);

		$class = get_class($object);

		$normalized[self::META_CLASS] = $class;

		return $normalized;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @throws RuntimeException
	 */

	public function denormalize($data, $class = null, $format = null, array $context = array())
	{
		if ( !is_array($data) || !isset($data[self::META_CLASS]) ) {
			return $data;
		}

		return parent::denormalize($data, $data[self::META_CLASS], $format, $context);
	}

	protected function prepareForDenormalization($data)
	{
		$normalizedData = parent::prepareForDenormalization($data);

		foreach ( $normalizedData as &$value ) {
			$value = $this->denormalize($value);
		}

		return $normalizedData;
	}

	/**
	 * {@inheritdoc}
	 */
	public function supportsNormalization($data, $format = null)
	{
		return (is_object($data) && get_class($data) == 'stdClass') || parent::supportsNormalization($data, $format);
	}


//	protected function isCircularReference($object, &$context)
//	{
//		if (!is_object($object)) {
//			return false;
//		}
//
//		return parent::isCircularReference($object, $context);
//	}


}