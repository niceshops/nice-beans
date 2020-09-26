<?php
declare(strict_types=1);

namespace NiceshopsDev\Bean\BeanList\Serializable;

use NiceshopsDev\Bean\AbstractBaseBean;
use NiceshopsDev\Bean\BeanInterface;
use NiceshopsDev\Bean\BeanList\BeanListException;

trait SerializableBeanListTrait
{
    /**
     * @return string
     */
    public function serialize()
    {
        return serialize($this->getSerializeData());
    }


    /**
     * @param string $serialized
     *
     * @return $this
     * @throws BeanListException
     */
    public function unserialize($serialized)
    {
        return $this->setSerializeData(unserialize($serialized));
    }


    /**
     * @return array
     */
    public function getSerializeData(): array
    {
        $arrData = array(
            self::SERIALIZE_ARR_BEAN_KEY => $this->getBeans(),
            self::SERIALIZE_ARR_BEAN_CLASS_KEY => $this->getBeanClasses(),
            self::SERIALIZE_ARR_BEAN_CLASS_MAP_KEY => [],
        );

        foreach ($arrData[self::SERIALIZE_ARR_BEAN_KEY] as $key => $bean) {
            $arrData[self::SERIALIZE_ARR_BEAN_CLASS_MAP_KEY][$key] = get_class($bean);
        }

        return $arrData;
    }


    /**
     * @param array $data
     *
     * @return $this
     * @throws BeanListException
     */
    public function setSerializeData(array $data)
    {
        if (!empty($data[self::SERIALIZE_ARR_BEAN_KEY]) && is_array($data[self::SERIALIZE_ARR_BEAN_KEY])) {
            $arrBean = [];

            $arrBeanClassMap = [];
            if (!empty($data[self::SERIALIZE_ARR_BEAN_CLASS_MAP_KEY]) && is_array($data[self::SERIALIZE_ARR_BEAN_CLASS_MAP_KEY])) {
                $arrBeanClassMap = $data[self::SERIALIZE_ARR_BEAN_CLASS_MAP_KEY];
            }

            foreach ($data[self::SERIALIZE_ARR_BEAN_KEY] as $key => $val) {
                if (is_array($val)) {
                    if (!empty($arrBeanClassMap[$key]) && class_exists($arrBeanClassMap[$key])) {
                        try {
                            /**
                             * @var AbstractBaseBean $beanClass
                             */
                            $beanClass = $arrBeanClassMap[$key];
                            $arrBean[] = $beanClass::createFromArray($val);
                        } catch (\Exception $e) {
                            throw new BeanListException($e->getMessage(), $e->getCode(), $e);
                        }
                    }
                } elseif ($val instanceof BeanInterface) {
                    $arrBean[] = $val;
                }
            }

            $this->setBeans($arrBean);
        }
        if (!empty($data[self::SERIALIZE_ARR_BEAN_CLASS_KEY]) && is_array($data[self::SERIALIZE_ARR_BEAN_CLASS_KEY])) {
            $this->setBeanClasses($data[self::SERIALIZE_ARR_BEAN_CLASS_KEY]);
        }

        return $this;
    }
}
