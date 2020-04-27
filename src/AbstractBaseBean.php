<?php
declare(strict_types=1);
/**
 * @see       https://github.com/niceshops/nice-beans for the canonical source repository
 * @license   https://github.com/niceshops/nice-beans/blob/master/LICENSE BSD 3-Clause License
 */

namespace NiceshopsDev\Bean;


use ArrayAccess;
use Countable;
use IteratorAggregate;
use NiceshopsDev\Bean\JsonSerializable\JsonSerializableInterface;
use NiceshopsDev\Bean\JsonSerializable\JsonSerializableTrait;

/**
 * Class AbstractBaseBean
 * @package NiceshopsDev\Bean
 */
abstract class AbstractBaseBean implements BeanInterface, ArrayAccess, IteratorAggregate, Countable, JsonSerializableInterface
{
    
    use JsonSerializableTrait;
    
    
    const DATA_TYPE_CALLABLE = 'callable';
    
    
    /**
     * @var array
     */
    private $data = [];
    
    
    /**
     * @var array   [ "<NORMALIZED_NAME>" => "<ORIGINAL_NAME>", ... ]
     */
    private $arrOriginalDataName = [];
    
    
    /**
     * @var array   [ "<NORMALIZED_NAME>" => "<DATA_TYPE" | <CALLABLE>, ... ]
     */
    private $arrDataType = [];
    
    
    /**
     * @param $name
     *
     * @return string
     * @throws BeanException
     */
    public function normalizeDataName(string $name): string
    {
        if (is_string($name) && array_key_exists($name, $this->data)) {
            return $name;
        }
        
        $name = strtolower(trim($name));
        
        if (!strlen($name)) {
            throw new BeanException("Invalid data name defined!");
        }
        
        return $name;
    }
    
    
    /**
     * @param string $originalName
     * @param string $normalizedName
     *
     * @return $this
     */
    protected function setOriginalDataName(string $originalName, string $normalizedName)
    {
        $this->arrOriginalDataName[$normalizedName] = $originalName;
        
        return $this;
    }
    
    
    /**
     * @param string $normalizedName
     *
     * @return mixed|string
     */
    protected function getOriginalDataName(string $normalizedName)
    {
        if (array_key_exists($normalizedName, $this->arrOriginalDataName)) {
            return $this->arrOriginalDataName[$normalizedName];
        }
        
        return $normalizedName;
    }
    
    
    /**
     * @param string $normalizedName
     *
     * @return $this
     */
    protected function unsetOriginalDataName(string $normalizedName)
    {
        if (array_key_exists($normalizedName, $this->arrOriginalDataName)) {
            unset($this->arrOriginalDataName[$normalizedName]);
        }
        
        return $this;
    }
    
    
    /**
     * @param string $name
     *
     * @return null|string  NULL if no datatype is defined for passed name
     * @throws BeanException
     */
    protected function getDataType(string $name): ?string
    {
        $key = $this->normalizeDataName($name);
        
        if (isset($this->arrDataType[$key]) && is_string($this->arrDataType[$key])) {
            return $this->arrDataType[$key];
        }
        
        if (isset($this->arrDataType[$key]) && is_callable($this->arrDataType[$key])) {
            return self::DATA_TYPE_CALLABLE;
        }
        
        return null;
    }
    
    
    /**
     * @param string $name
     *
     * @return callable|null
     * @throws BeanException
     */
    protected function getDataTypeCallable(string $name): ?callable
    {
        $key = $this->normalizeDataName($name);
        if (is_callable($this->arrDataType[$key])) {
            return $this->arrDataType[$key];
        }
        
        return null;
    }
    
    
    /**
     * @param string $name
     * @param        $value
     *
     * @return $this
     * @throws BeanException     if invalid name is defined or data could not be set
     */
    public function setData($name, $value)
    {
        $origName = $name;
        $name = $this->normalizeDataName($name);
        if (is_null($name)) {
            return $this;
        }
        
        //  @todo isFrozen check at FreezableBeanTrait
        
        //  @todo isSealed check at SealableBeanTrait
        
        $error = false;
        $arrName = null;
        if (strpos($origName, ".") >= 1) {
            $arrName = array_values(array_map("trim", explode(".", $origName)));
            
            $this->setOriginalDataName($arrName[0], $this->normalizeDataName($arrName[0]));
        } else {
            $this->setOriginalDataName($origName, $name);
        }
        
        $dataType = $this->getDataType($name);
        if ($dataType === self::DATA_TYPE_CALLABLE) {
            $dataType = $this->getDataTypeCallable($name);
        }
        
        
        return $this;
    }
}