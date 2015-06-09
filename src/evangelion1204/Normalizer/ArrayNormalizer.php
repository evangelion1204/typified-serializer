<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @copyright Michael Iwersen <mi.iwersen@gmail.com>
 * @link      https://github.com/evangelion1204/typified-serializer
 */

namespace evangelion1204\Normalizer;


use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactoryInterface;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

class ArrayNormalizer extends AbstractNormalizer
{
	const META_CLASS = '__class__';

	public function __construct(ClassMetadataFactoryInterface $classMetadataFactory = null, NameConverterInterface $nameConverter = null)
	{
		parent::__construct($classMetadataFactory, $nameConverter);
	}

	/**
	 * {@inheritdoc}
	 * @throws CircularReferenceException
	 */
	public function normalize($array, $format = null, array $context = array())
	{
		$normalized = array();

		foreach ($array  as $key => $value) {
			if (in_array($key, $this->ignoredAttributes)) {
				continue;
			}

			if (isset($this->callbacks[$key])) {
				$value = call_user_func($this->callbacks[$key], $value);
			}

			if (!empty($value) && !is_scalar($value)) {
				if (!$this->serializer instanceof NormalizerInterface) {
					throw new LogicException(sprintf('Cannot normalize element "%s" because injected serializer is not a normalizer', $key));
				}

				$value = $this->serializer->normalize($value, $format, $context);
			}

			if ($this->nameConverter) {
				$key = $this->nameConverter->normalize($key);
			}

			$normalized[$key] = $value;
		}

		return $normalized;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @throws RuntimeException
	 */
	public function denormalize($data, $class = null, $format = null, array $context = array())
	{
		if ( !is_array($data) ) {
			return $data;
		}

		$normalized = array();

		foreach ($data as $key => $value) {
			if ($this->nameConverter) {
				$key = $this->nameConverter->denormalize($key);
			}

            if (in_array($key, $this->ignoredAttributes)) {
				continue;
			}

			if (!is_scalar($value)) {
				$normalized[$key] = $this->serializer->denormalize($value, $class, $format, $context);
			}
			else {
				$normalized[$key] = $value;
			}
		}

		return $normalized;
	}

	/**
	 * {@inheritdoc}
	 */
	public function supportsNormalization($data, $format = null)
	{
		return is_array($data);
	}

	/**
	 * {@inheritdoc}
	 */
	public function supportsDenormalization($data, $type, $format = null)
	{
		return is_array($data);
	}

}