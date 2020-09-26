<?php
declare(strict_types=1);


namespace NiceshopsDev\Bean\Serializable;

use NiceshopsDev\Bean\BeanException;

/**
 * Trait SerializableBeanTrait
 * @package NiceshopsDev\Bean\Serializable
 */
trait SerializableBeanTrait
{
    /**
     * NOTE: To implement bean specific data serialization logic use AbstractBean::getDataForSerialization
     *
     * @return array
     * @throws BeanException
     * @see AbstractBean::getDataForSerialization()
     */
    public function getSerializeData(): array
    {
        return [
            self::SERIALIZE_DATA_KEY => $this->getDataForSerialization(),
            self::SERIALIZE_DATA_TYPE_KEY => $this->getDataTypeDataForSerialization(),
        ];
    }

    /**
     * @return array
     */
    protected function getDataTypeDataForSerialization(): array
    {
        $arrDataType = $this->getDataType_List();

        foreach ($arrDataType as $key => $dataType) {
            if (is_array($dataType) && !empty($dataType[0]) && $dataType[0] === $this) {
                $arrDataType[$key][0] = self::SELF_REFERENCE_PLACEHOLDER;
            }
        }

        return $arrDataType;
    }

    /**
     * Overwrite to implement bean specific data serialization
     * @return array
     * @throws BeanException
     */
    protected function getDataForSerialization(): array
    {
        return $this->toArray();
    }

    /**
     * @return string
     * @throws BeanException
     */
    public function serialize()
    {
        return serialize($this->getSerializeData());
    }

    /**
     * @param $serialized
     * @return SerializableBeanTrait
     * @throws BeanException
     */
    public function unserialize($serialized)
    {
        return $this->setSerializeData(unserialize($serialized));
    }

    /**
     * NOTE: To implement bean specific data deserialization logic use AbstractBean::setDataFromSerialization
     *
     * @param array $data
     *
     * @return $this
     * @throws BeanException
     *
     */
    public function setSerializeData(array $data)
    {
        if (!empty($data[self::SERIALIZE_DATA_KEY])) {
            $this->setDataFromSerialization($data[self::SERIALIZE_DATA_KEY]);
        }
        if (!empty($data[self::SERIALIZE_DATA_TYPE_KEY])) {
            $this->setDataTypeDataFromSerialization($data[self::SERIALIZE_DATA_TYPE_KEY]);
        }
        return $this;
    }

    /**
     * Overwrite to implement bean specific data deserialization
     *
     * @param array $data
     *
     * @return $this
     * @throws BeanException
     */
    protected function setDataFromSerialization(array $data)
    {
        $this->setFromArray($data);
        return $this;
    }

    /**
     * @param array $arrDataType
     *
     * @return $this
     * @throws BeanException
     */
    protected function setDataTypeDataFromSerialization(array $arrDataType)
    {
        foreach ($arrDataType as $key => $dataType) {
            if (is_array($dataType) && !empty($dataType[0]) && $dataType[0] === self::SELF_REFERENCE_PLACEHOLDER) {
                $arrDataType[$key][0] = $this;
            }
        }

        foreach ($arrDataType as $key => $item) {
            $this->setDataType($key, $item['name'], $item['nullable'], $item['callback']);
        }

        return $this;
    }

    /**
     * @return array
     */
    static protected function getSerializeKey_List()
    {
        return [
            self::SERIALIZE_DATA_KEY,
            self::SERIALIZE_DATA_TYPE_KEY,
        ];
    }

    /**
     * @param array $arrData
     *
     * @return bool
     */
    static protected function isSerializedData(array $arrData)
    {
        $flag = true;

        foreach (static::getSerializeKey_List() as $key) {
            if (!array_key_exists($key, $arrData)) {
                $flag = false;
                break;
            }
        }

        return $flag;
    }

    /**create
     *
     * @param array $arrData
     *
     * @return static
     * @throws BeanException
     */
    static public function createFromArray(array $arrData)
    {
        $bean = new static();

        if (static::isSerializedData($arrData)) {
            $bean->setSerializeData($arrData);
        } else {
            $bean->setFromArray($arrData);
        }

        return $bean;
    }
}
