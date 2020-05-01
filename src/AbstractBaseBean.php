<?php
declare(strict_types=1);
/**
 * @see       https://github.com/niceshops/nice-beans for the canonical source repository
 * @license   https://github.com/niceshops/nice-beans/blob/master/LICENSE BSD 3-Clause License
 */

namespace NiceshopsDev\Bean;


use ArrayAccess;
use ArrayObject;
use Countable;
use IteratorAggregate;
use NiceshopsDev\Bean\BeanList\BeanListInterface;
use NiceshopsDev\Bean\JsonSerializable\JsonSerializableInterface;
use NiceshopsDev\Bean\JsonSerializable\JsonSerializableTrait;
use stdClass;

/**
 * Class AbstractBaseBean
 * @package NiceshopsDev\Bean
 */
abstract class AbstractBaseBean implements BeanInterface, ArrayAccess, IteratorAggregate, Countable, JsonSerializableInterface
{
    
    use JsonSerializableTrait;
    
    
    const DATA_TYPE_CALLABLE = 'callable';
    const DATA_TYPE_STRING = 'string';
    const DATA_TYPE_ARRAY = 'array';
    const DATA_TYPE_INT = 'int';
    const DATA_TYPE_FLOAT = 'float';
    const DATA_TYPE_BOOL = 'bool';
    const DATA_TYPE_ITERABLE = 'iterable';
    const DATA_TYPE_DATE = 'date';
    const DATA_TYPE_DATETIME_PHP = 'datetime';
    const DATA_TYPE_OBJECT = 'object';
    
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
     * @todo refactore dot-notation name handling (extract method)
     * @todo UnitTests
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
        
        $value = $this->normalizeDataValue($value, $dataType);
        
        //  @todo hasDataModified check at AbstractModifiedBean     // $modified = $this->hasDataModified($name, $value);
        
        if ($arrName) {
            $arrName = array_values(array_map("trim", explode(".", $origName)));
            $context =& $this->data;
            $deep = 1;
            while ($deep < 100 && count($arrName)) {
                $contextName = array_shift($arrName);
                if ($deep == 1) {
                    $contextName = $this->normalizeDataName($contextName);
                }
                ++$deep;
                
                if ((is_array($context) || $context instanceof ArrayObject)) {
                    if (!array_key_exists($contextName, $context) && $arrName) {
                        $context[$contextName] = [];
                    }
                    
                    if (!$arrName) {
                        $context[$contextName] = $value;
                    } else {
                        $context =& $context[$contextName];
                    }
                } elseif (($context instanceof stdClass)) {
                    if (!array_key_exists($contextName, (array)$context) && $arrName) {
                        $context->{$contextName} = new  stdClass();
                    }
                    
                    if (!$arrName) {
                        $context->{$contextName} = $value;
                    } else {
                        $context =& $context->{$contextName};
                    }
                } elseif (($context instanceof BeanInterface)) {
                    if ($context instanceof BeanListInterface && (string)(int)$contextName === (string)$contextName) {
                        if ($context->offsetExists($contextName)) {
                            $context->offsetGet($contextName)->setData(implode(".", $arrName), $value);
                        }
                    } else {
                        array_unshift($arrName, $contextName);
                        $context->setData(implode(".", $arrName), $value);
                    }
                    break;
                } else {
                    throw new BeanException(sprintf("Could not set data '%s'!", $name));
                    break;
                }
            }
            
            unset($context);
        } else {
            $this->data[$name] = $value;
        }

        if ($dataType === self::DATA_TYPE_ARRAY && is_array($value)) {
            $this->normalizeDataValue_for_normalizedDataName($name);
        }
//
//        $this->touchData($name, $modified);
        
        return $this;
    }
    
    
    /**
     * @param mixed  $value
     * @param string $dataType
     *
     * @return mixed
     * @todo implement method
     */
    protected function normalizeDataValue($value, string $dataType = null)
    {
        if (is_null($value)) {
            return null;
        }
        
        if (null !== $dataType) {
            switch ($dataType) {
                default:
                    break;
            }
        }
        
        return $value;
    }
    
    
    /**
     * @param string $normalizedDataName
     *
     * @return $this
     * @throws BeanException
     */
    protected function normalizeDataValue_for_normalizedDataName(string $normalizedDataName)
    {
        $arrDataName_with_DataTypeDefinition = $this->getDataName_List_with_DataNamePrefix_and_DataTypeDefinition($normalizedDataName, true);
        if (!$arrDataName_with_DataTypeDefinition) {
            return $this;
        }
        
        sort($arrDataName_with_DataTypeDefinition);
        
        foreach ($arrDataName_with_DataTypeDefinition as $dataName) {
            $this->setData($dataName, $this->getData_with_DefaultValue($dataName));
        }
        
        return $this;
    }
    
    
    /**
     * @param string $name
     *
     * @return array|mixed|null
     * @throws BeanException
     */
    protected function getData_with_DefaultValue(string $name)
    {
        if ($this->hasData($name)) {
            $dataValue = $this->getData($name);
        } elseif (null !== ($dataType = $this->getDataType($name))) {
            $dataValue = $this->getDefaultValue_for_DataType($dataType);
        } else {
            $dataValue = null;
        }
        
        return $dataValue;
    }
    
    
    /**
     * @param string $dataType
     *
     * @return array|null
     */
    protected function getDefaultValue_for_DataType(string $dataType)
    {
        switch ($dataType) {
            case self::DATA_TYPE_ARRAY:
                $dataValue = [];
                break;
            
            default:
                $dataValue = null;
        }
        
        return $dataValue;
    }
    
    
    /**
     * @return array
     */
    protected function getDataType_List(): array
    {
        return $this->arrDataType;
    }
    
    
    /**
     * @param string $normalizedDataNamePrefix
     * @param bool   $ignoreSelf
     *
     * @return array
     * @throws BeanException
     */
    protected function getDataName_List_with_DataNamePrefix_and_DataTypeDefinition(string $normalizedDataNamePrefix, bool $ignoreSelf = true)
    {
        $arrDataTypeName = array_filter(
            array_keys($this->getDataType_List()), function ($key) use ($normalizedDataNamePrefix) {
            return strpos($key, $normalizedDataNamePrefix . ".") === 0;
        }
        );
        
        if (!$ignoreSelf && $this->getDataType($normalizedDataNamePrefix)) {
            array_unshift($arrDataTypeName, $normalizedDataNamePrefix);
        }
        
        return array_values($arrDataTypeName);
    }

    
}