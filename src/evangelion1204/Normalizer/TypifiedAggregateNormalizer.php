<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @copyright Michael Iwersen <mi.iwersen@gmail.com>
 * @link      https://github.com/evangelion1204/typified-serializer
 */

namespace evangelion1204\Normalizer;


use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;

class TypifiedNormalizer implements NormalizerInterface, DenormalizerInterface, SerializerAwareInterface
{
	const META_CLASS = '__class__';

	protected $normalizers = array();

	public function __construct()
	{
		$this->normalizers[] = new PropertyNormalizer();
		$this->normalizers[] = new ObjectNormalizer();
	}

	/**
	 * {@inheritdoc}
	 * @throws CircularReferenceException
	 */
	public function normalize($object, $format = null, array $context = array())
	{
		$normalizer = $this->getMatchingNormalizer($object);
		$normalized = $normalizer->normalize($object, $format, $context);

		echo get_class($normalizer);

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

		$class = $data[self::META_CLASS];

		$denormalizer = $this->getMatchingDenormalizer($data, $class);

		return $denormalizer->denormalize($data, $class, $format, $context);
	}

	public function getMatchingNormalizer($data)
	{
		foreach ($this->normalizers as $normalizer)
		{
			if ($normalizer->supportsNormalization($data))
			{
				return $normalizer;
			}
		}

		return null;
	}

	public function getMatchingDenormalizer($data, $class)
	{
		foreach ($this->normalizers as $normalizer)
		{
			if ($normalizer->supportsDenormalization($data, $class))
			{
				return $normalizer;
			}
		}

		return null;
	}

	/**
	 * {@inheritdoc}
	 */
	public function supportsNormalization($data, $format = null)
	{
		foreach ($this->normalizers as $normalizer)
		{
			if ($normalizer->supportsDenormalization($data, $format))
			{
				return true;
			}
		}

		return false;

//		return (is_object($data) && get_class($data) == 'stdClass') || $this->normalizers->supportsNormalization($data, $format);
	}

	/**
	 * {@inheritdoc}
	 */
	public function supportsDenormalization($data, $type, $format = null)
	{
		foreach ($this->normalizers as $normalizer)
		{
			if ($normalizer->supportsDenormalization($data, $type, $format))
			{
				return true;
			}
		}

		return false;

//		return (is_object($data) && get_class($data) == 'stdClass') || $this->normalizers->supportsDenormalization($data, $type, $format);
	}

	/**
	 * {@inheritdoc}
	 */
	public function setSerializer(SerializerInterface $serializer)
	{
		foreach ($this->normalizers as $normalizer) {
			$normalizer->setSerializer($serializer);
		}

	}

}