<?php
declare(strict_types=1);

namespace NiceshopsDev\Bean\Serializable;


use Serializable;

/**
 * Interface SerializeableBeanInterface
 * @package NiceshopsDev\Bean\Serializable
 */
interface SerializeableBeanInterface extends Serializable
{
    /**
     *
     */
    public const SERIALIZE_DATA_KEY = "data";
    /**
     *
     */
    public const SERIALIZE_DATA_TYPE_KEY = "arrDataType";
    /**
     *
     */
    public const SELF_REFERENCE_PLACEHOLDER = "__THIS__";

    /**
     * @param array $data
     * @return mixed
     */
    public function setSerializeData(array $data);

    /**
     * @return array
     */
    public function getSerializeData(): array;
}
