<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @copyright Michael Iwersen <mi.iwersen@gmail.com>
 * @link      https://github.com/evangelion1204/typified-serializer
 */

namespace evangelion1204\Normalizer;


use Symfony\Component\Serializer\Exception\LogicException;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactoryInterface;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

class StdClassNormalizer extends AbstractNormalizer
{

	/**
	 * {@inheritdoc}
	 * @throws CircularReferenceException
	 */
	public function normalize($object, $format = null, array $context = array())
	{
		$reflectionObject = new \ReflectionObject($object);
		$attributes = array();
		$allowedAttributes = $this->getAllowedAttributes($object, $context, true);

		foreach ($object as $attribute => $attributeValue) {
			if (in_array($attribute, $this->ignoredAttributes)) {
				continue;
			}

			if (false !== $allowedAttributes && !in_array($attribute, $allowedAttributes)) {
				continue;
			}

			if (isset($this->callbacks[$attribute])) {
				$attributeValue = call_user_func($this->callbacks[$attribute], $attributeValue);
			}
			if (null !== $attributeValue && !is_scalar($attributeValue)) {
				if (!$this->serializer instanceof NormalizerInterface) {
					throw new LogicException(sprintf('Cannot normalize attribute "%s" because injected serializer is not a normalizer', $attribute));
				}

				$attributeValue = $this->serializer->normalize($attributeValue, $format, $context);
			}

			if ($this->nameConverter) {
				$attribute = $this->nameConverter->normalize($attribute);
			}

			$attributes[$attribute] = $attributeValue;
		}

		return $attributes;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @throws RuntimeException
	 */
	public function denormalize($data, $class = null, $format = null, array $context = array())
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
			!is_scalar($data) &&
			!is_null($data);
	}

	/**
	 * {@inheritdoc}
	 */
	public function supportsDenormalization($data, $type, $format = null)
	{
		return is_array($data) && $type == 'stdClass';
	}

}