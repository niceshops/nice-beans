<?php
declare(strict_types=1);

namespace NiceshopsDev\Bean\Serializable;


use NiceshopsDev\Bean\AbstractBaseBean;

/**
 * Class AbstractSerializableBean
 * @package NiceshopsDev\Bean\Serializable
 */
class AbstractSerializableBean extends AbstractBaseBean implements SerializeableBeanInterface
{
    use SerializableBeanTrait;
}
