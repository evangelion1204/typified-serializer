<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @copyright Michael Iwersen <mi.iwersen@gmail.com>
 * @link      https://github.com/evangelion1204/typified-serializer
 */

namespace evangelion1204\Serializer;


use evangelion1204\Normalizer\TypifiedNormalizer;
use Symfony\Component\Serializer\Serializer;

class TypifiedSerializer extends Serializer
{
	const META_CLASS = '__class__';

	/**
	 * {@inheritdoc}
	 */
	public function normalize($data, $format = null, array $context = array())
	{
		$normalized = parent::normalize($data, $format, $context);

		if (is_object($data)) {
			$normalized[self::META_CLASS] = get_class($data);
		}

		return $normalized;
	}

	/**
	 * {@inheritdoc}
	 */
	public function denormalize($data, $type = null, $format = null, array $context = array())
	{
		if (!is_array($data) && !is_object($data)) {
			return $data;
		}

		$preprocessedData = array();

		foreach ($data as $attribute => $attributeValue) {
			if ($attribute === self::META_CLASS) {
				continue;
			}

			$preprocessedData[$attribute] = $this->denormalize($attributeValue, null, $format, $context);
		}

		if (isset($data[self::META_CLASS])) {
			$type = $data[self::META_CLASS];
		}

		return parent::denormalize($preprocessedData, $type, $format, $context);
	}
}
