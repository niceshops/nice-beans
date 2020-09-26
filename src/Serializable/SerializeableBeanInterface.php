<?php

namespace NiceshopsDev\Bean\Serializable;


interface SerializeableBeanInterface extends \Serializable
{
    private const SERIALIZE_DATA_KEY = "data";
    private const SERIALIZE_DATA_TYPE_KEY = "arrDataType";
    private const SELF_REFERENCE_PLACEHOLDER = "__THIS__";

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
