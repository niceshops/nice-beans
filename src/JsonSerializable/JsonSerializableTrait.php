<?php
declare(strict_types=1);
/**
 * @see       https://github.com/niceshops/nice-beans for the canonical source repository
 * @license   https://github.com/niceshops/nice-beans/blob/master/LICENSE BSD 3-Clause License
 */

namespace NiceshopsDev\Bean\JsonSerializable;


use NiceshopsDev\Bean\BeanException;
use NiceshopsDev\Bean\BeanInterface;

/**
 * Trait JsonSerializableTrait
 *
 * can be used for classes which should implement the @see JsonSerializableInterface
 *
 * NOTE: requires that the class which use the trait implements the @see JsonSerializableInterface
 * @package Niceshops\Library\Core
 * @todo    use https://docs.laminas.dev/laminas-hydrator to hydrate bean with data (fromJson) or extract data from bean (toJson)
 * @todo    UnitTests
 */
trait JsonSerializableTrait
{
    
    /**
     * @return array
     * @throws BeanException
     */
    public function jsonSerialize(): array
    {
        if (method_exists($this, "getSerializeData")) {
            return $this->getSerializeData();
        }
        
        throw new BeanException("Could not get data for json serialization!");
    }
    
    
    /**
     * @param bool $dataOnly
     *
     * @return string
     * @throws BeanException
     */
    public function toJson($dataOnly = false): string
    {
        if ($dataOnly) {
            if (method_exists($this, "toArrayRecursive")) {
                $arrData = $this->toArrayRecursive();
            } else {
                $arrData = $this->toArray();
            }
        } else {
            $arrData = $this->jsonSerialize();
        }
        
        return json_encode($arrData);
    }
    
    
    /**
     * @param string $json
     * @param bool   $dataOnly
     *
     * @return $this
     * @throws BeanException
     */
    public function fromJson(string $json, $dataOnly = false)
    {
        $data = $this->getDataFromJson($json);
        if ($dataOnly) {
            if ($data) {
                if (method_exists($this, "setSerializeData")) {
                    $this->setSerializeData(array(BeanInterface::SERIALIZE_DATA_KEY => $data));
                }
                
                throw new BeanException("Could not set data from json!");
            }
        } else {
            if ($data) {
                if (method_exists($this, "setSerializeData")) {
                    $this->setSerializeData($data);
                }
                
                throw new BeanException("Could not set data from json!");
            }
        }
        
        return $this;
    }
    
    
    /**
     * @param string $json
     *
     * @return mixed|null
     * @throws BeanException
     */
    protected function getDataFromJson(string $json)
    {
        try {
            $data = json_decode($json);
        } catch (\Exception $e) {
            throw new BeanException($e->getMessage(), $e->getCode(), $e->getPrevious());
        }
        
        return $data;
    }
    
}
