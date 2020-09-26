<?php
declare(strict_types=1);


namespace NiceshopsDev\Bean\JsonSerializable;


use NiceshopsDev\Bean\Serializable\AbstractSerializableBean;

/**
 * Class AbstractJsonSerializableBean
 * @package NiceshopsDev\Bean\JsonSerializable
 */
class AbstractJsonSerializableBean extends AbstractSerializableBean implements JsonSerializableBeanInterface
{
    use JsonSerializableBeanTrait;
}
