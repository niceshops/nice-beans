<?php
declare(strict_types=1);

namespace NiceshopsDev\Bean\BeanList\Serializable;


use NiceshopsDev\Bean\BeanList\AbstractBaseBeanList;

/**
 * Class AbstractSerializableBeanList
 * @package NiceshopsDev\Bean\BeanList\Serializable
 */
class AbstractSerializableBeanList extends AbstractBaseBeanList implements SerializableBeanListInterface
{
    use SerializableBeanListTrait;
}
