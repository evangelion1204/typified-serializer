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

class TypifiedNormalizer extends AbstractNormalizer
{
	const META_CLASS = '__class__';

	public function __construct(ClassMetadataFactoryInterface $classMetadataFactory = null, NameConverterInterface $nameConverter = null)
	{
		parent::__construct($classMetadataFactory, $nameConverter);

		$this->propertyNormalizer = new PropertyNormalizer($classMetadataFactory, $nameConverter);
	}

	/**
	 * {@inheritdoc}
	 * @throws CircularReferenceException
	 */
	public function normalize($object, $format = null, array $context = array())
	{
		$class = get_class($object);

		$normalized = $this->propertyNormalizer->normalize($object, $format, $context);

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
		unset($data[self::META_CLASS]);

		if ($class === 'stdClass') {
			return $this->denormalizeStdClass($data, $format, $context);
		}
		else {
			return $this->propertyNormalizer->denormalize($data, $class, $format, $context);
		}
	}

	protected function denormalizeStdClass($data, $format = null, array $context = array(), $class = 'stdClass')
	{
		$allowedAttributes = $this->getAllowedAttributes($class, $context, true);
		$normalizedData = $this->prepareForDenormalization($data);

		$reflectionClass = new \ReflectionClass($class);
		$object = $this->instantiateObject($normalizedData, $class, $context, $reflectionClass, $allowedAttributes);

		foreach ($normalizedData as $attribute => $value) {
			if ($this->nameConverter) {
				$attribute = $this->nameConverter->denormalize($attribute);
			}

			$allowed = $allowedAttributes === false || in_array($attribute, $allowedAttributes);
			$ignored = in_array($attribute, $this->ignoredAttributes);

			if ($allowed && !$ignored) {
				if (!is_scalar($value)) {
					$value = $this->serializer->denormalize($value, $class, $format, $context);
				}
				$object->$attribute = $value;
			}
		}

		return $object;
	}

	/**
	 * {@inheritdoc}
	 */
	public function supportsNormalization($data, $format = null)
	{
		return
			(is_object($data) && get_class($data) == 'stdClass') ||
			!$data instanceof \Traversable &&
			!is_array($data) &&
			!is_scalar($data);
	}

	/**
	 * {@inheritdoc}
	 */
	public function supportsDenormalization($data, $type, $format = null)
	{
		return is_array($data) && isset($data[self::META_CLASS]);
	}

	/**
	 * {@inheritdoc}
	 */
	public function setSerializer(SerializerInterface $serializer)
	{
		parent::setSerializer($serializer);

		$this->propertyNormalizer->setSerializer($serializer);
	}

}