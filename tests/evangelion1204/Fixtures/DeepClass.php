<?php
/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @copyright Michael Iwersen <mi.iwersen@gmail.com>
 * @link      https://github.com/evangelion1204/typified-serializer
 */

namespace tests\evangelion1204\Fixtures;

class DeepClass
{
	protected $protectedValue;

	protected $parent;

	public function setParent($parent)
	{
		$this->parent = $parent;

		return $this;
	}

	public function getParent()
	{
		return $this->parent;
	}

	public function setProtectedValue($value)
	{
		$this->protectedValue = $value;

		return $this;
	}

	public function getProtectedValue()
	{
		return $this->protectedValue;
	}
}