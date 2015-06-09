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
	/**
	 * {@inheritdoc}
	 */
	public function denormalize($data, $type = null, $format = null, array $context = array())
	{
		$type = isset($data[TypifiedNormalizer::META_CLASS]) ? $data[TypifiedNormalizer::META_CLASS] : $type;

		return parent::denormalize($data, $type, $format, $context);
	}
}
