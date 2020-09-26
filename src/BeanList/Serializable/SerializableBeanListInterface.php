<?php
declare(strict_types=1);

namespace NiceshopsDev\Bean\BeanList\Serializable;


use NiceshopsDev\Bean\Serializable\SerializeableBeanInterface;

interface SerializableBeanListInterface extends SerializeableBeanInterface
{
    const SERIALIZE_ARR_BEAN_KEY = "arrBean";
    const SERIALIZE_ARR_BEAN_CLASS_KEY = "arrBeanClass";
    const SERIALIZE_ARR_BEAN_CLASS_MAP_KEY = "arrBeanClassMap";

}
