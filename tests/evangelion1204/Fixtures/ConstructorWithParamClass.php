<?php
/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @copyright Michael Iwersen <mi.iwersen@gmail.com>
 * @link      https://github.com/evangelion1204/typified-serializer
 */

namespace tests\evangelion1204\Fixtures;

class ConstructorWithParamClass
{
    protected $property;

    public function __construct($property)
    {
        $this->property = $property;
    }

    public function getProperty()
    {
        return $this->property;
    }

}