<?php
declare(strict_types=1);

namespace NiceshopsDev\Bean\BeanList\JsonSerializable;

use NiceshopsDev\Bean\BeanList\Serializable\AbstractSerializableBeanList;

/**
 * Class AbstractJsonSerializableBeanList
 * @package NiceshopsDev\Bean\BeanList\JsonSerializable
 */
class AbstractJsonSerializableBeanList extends AbstractSerializableBeanList implements JsonSerializableBeanListBeanInterface
{
    use JsonSerializableBeanListTrait;
}
