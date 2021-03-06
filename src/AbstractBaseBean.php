<?php
declare(strict_types=1);
/**
 * @see       https://github.com/niceshops/nice-beans for the canonical source repository
 * @license   https://github.com/niceshops/nice-beans/blob/master/LICENSE BSD 3-Clause License
 */

namespace NiceshopsDev\Bean;


use ArrayObject;
use DateTime;
use DateTimeInterface;
use IteratorAggregate;
use NiceshopsDev\Bean\BeanList\BeanListInterface;
use NiceshopsDev\Bean\JsonSerializable\JsonSerializableInterface;
use NiceshopsDev\Bean\JsonSerializable\JsonSerializableTrait;
use NiceshopsDev\NiceCore\Exception;
use NiceshopsDev\NiceCore\Helper\Object\ObjectPropertyFinder;
use stdClass;

/**
 * Class AbstractBaseBean
 * @package NiceshopsDev\Bean
 */
abstract class AbstractBaseBean implements BeanInterface, IteratorAggregate, JsonSerializableInterface
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
    const DATA_TYPE_RESOURCE = 'resource';
    
    const DATA_KEY_WILDCARD = "*";
    
    /**
     * @var array
     */
    private $data = [];
    
    
    /**
     * @var array   [ "<NORMALIZED_NAME>" => "<ORIGINAL_NAME>", ... ]
     */
    private $arrOriginalDataName = [];
    
    
    /**
     * @var array   [ "<NORMALIZED_NAME>" => [ "name" => "<DATA_TYPE>", "callback" => <CALLABLE>?, "nullable" => <BOOL> ], ... ]
     */
    private $arrDataType = [];
    
    
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
        
        $value = $this->normalizeDataValue($value, $name);
        
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
    
        if (is_array($value) && $this->getDataType($name) === self::DATA_TYPE_ARRAY) {
            $this->normalizeDataValue_for_normalizedDataName($name);
        }
        
        //  @todo hasDataModified check at AbstractModifiedBean     // $this->touchData($name, $modified);
        
        return $this;
    }
    
    
    /**
     * @param string $name
     *
     * @return mixed
     * @throws BeanException if invalid name is defined or data could not found
     * @todo should it possible to return values by reference?!?
     */
    public function getData($name)
    {
        $data = null;
        
        $result = $this->findData($name);
        if (!$result["found"]) {
            throw new BeanException(sprintf("Data '%s' not found!", $name), BeanException::ERROR_CODE_DATA_NOT_FOUND);
        } else {
            if (array_key_exists("value", $result)) {
                $data = $result["value"];
            }
        }
        
        return $data;
    }
    
    
    /**
     * @param string $name
     *
     * @return mixed    the removed data
     * @throws BeanException    Data at passed name not found
     */
    public function removeData($name)
    {
        $name = $this->normalizeDataName($name);
        if (!$this->hasData($name)) {
            throw new BeanException(sprintf("Data '%s' not found!", $name), BeanException::ERROR_CODE_DATA_NOT_FOUND);
        }
        
        //  @todo isFrozen check at FreezableBeanTrait
        
        //  @todo isSealed check at SealableBeanTrait
        
        $removedData = $this->data[$name];
        unset($this->data[$name]);
        
        $this->removeDataType($name);
        
        $this->unsetOriginalDataName($name);
        
        //  @todo remove modified meta data for removed data check at AbstractModifiedBean     // unset($this->arrModified[$name]);
        
        return $removedData;
    }
    
    
    /**
     * @return $this
     */
    public function resetData()
    {
        //  @todo isFrozen check at FreezableBeanTrait
        
        //  @todo isSealed check at SealableBeanTrait
        
        $this->data = [];
        
        //  @todo reset modified meta data at AbstractModifiedBean     // $this->arrModified = [];
        
        return $this;
    }
    
    
    /**
     * @param string $name
     *
     * @return bool
     * @throws BeanException
     */
    public function hasData($name)
    {
        $result = $this->findData($name);
        
        return (bool)$result["found"];
    }
    
    
    /**
     * @param bool $useOrigDataNames
     *
     * @return array
     * @todo UnitTests
     */
    public function toArray($useOrigDataNames = true)
    {
        if (!$useOrigDataNames) {
            return $this->data;
        }
        
        $arrData = [];
        foreach ($this->data as $name => $value) {
            $arrData[$this->getOriginalDataName($name)] = $value;
        }
        
        return $arrData;
    }
    
    
    /**
     * NOTE: existing data will be overwritten (to merge data use "mergeWithData")
     * NOTE: there will be no data reset applied before setting the passed data (a data reset has to be done explicitly with "resetData")
     *
     * @param array      $arrData [ "<NAME>" => <VALUE>, ... ]
     * @param array|null $arrName [ "<NAME>", ... ]     <NAME> can also reference to nested data at the passed data with dot-notation syntax (e.g. "foo.bar.baz")
     *
     * @return $this|mixed
     * @throws BeanException
     * @see AbstractBaseBean::mergeWithData()
     * @see AbstractBaseBean::resetData()
     *
     */
    public function setFromArray(array $arrData, array $arrName = null)
    {
        $arrData = array_combine(array_map("trim", array_keys($arrData)), $arrData);
        if ($arrName) {
            $arrName = array_map("trim", $arrName);
            foreach ($arrName as $key => $name) {
                if (array_key_exists($name, $arrData)) {
                    continue;
                }
                
                if (strpos($name, ".") < 1) {
                    continue;
                }
                
                $arrNamePart = explode(".", $name);
                $context = $arrData;
                $dataFound = true;
                foreach ($arrNamePart as $namePart) {
                    try {
                        $finder = new ObjectPropertyFinder($context);
                    } catch (Exception $e) {
                        $dataFound = false;
                        break;
                    }
                    if (!$finder->hasKey($namePart)) {
                        $dataFound = false;
                        break;
                    }
                    
                    $context = $finder->getValue($namePart);
                }
                if ($dataFound) {
                    $arrData[$name] = $context;
                } else {
                    unset($arrName[$key]);
                }
            }
            $arrData = array_intersect_key($arrData, array_flip($arrName));
        }
        
        foreach ($arrData as $name => $value) {
            $this->setData($name, $value);
        }
        
        return $this;
    }
    
    
    /**
     * @param $name
     *
     * @return string
     * @throws BeanException
     */
    protected function normalizeDataName(string $name): string
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
     * @param string   $name
     * @param callable $callable
     *
     * @return $this
     * @throws BeanException
     */
    protected function setDataTypeCallable(string $name, callable $callable): self
    {
        return $this->setDataType($name, self::DATA_TYPE_CALLABLE, false, $callable);
    }
    
    
    /**
     * @param string $name
     * @param string $dataType
     *
     * @return $this
     * @throws BeanException
     */
    protected function setDataTypeNullable(string $name, string $dataType): self
    {
        return $this->setDataType($name, $dataType, true);
    }
    
    
    /**
     * @param string        $name
     * @param string        $dataType
     * @param bool          $nullable
     * @param callable|null $callable only valid and considered for self::DATA_TYPE_CALLABLE datatype
     *
     * @return $this
     * @throws BeanException
     */
    protected function setDataType(string $name, string $dataType, bool $nullable = false, callable $callable = null): self
    {
        do {
            //  @todo isFrozen check at FreezableBeanTrait
            
            //  @todo isSealed check at SealableBeanTrait
            
            $dataType = $this->normalizeDataType($dataType);
            $normalizedDataName = $this->normalizeDataName($name);
            
            if ($this->hasParentDataName($normalizedDataName)) {
                $this->setDataType_to_Parent($normalizedDataName, self::DATA_TYPE_ARRAY);
                if (self::DATA_TYPE_ARRAY !== $this->getDataType_from_Parent($normalizedDataName)) {
                    throw new BeanException(
                        sprintf(
                            "Parent of '%s' has wrong datatype '%s' ('%s' required)!", $normalizedDataName, $this->getDataType_from_Parent($normalizedDataName),
                            self::DATA_TYPE_ARRAY
                        ), BeanException::ERROR_CODE_INVALID_DATA_TYPE
                    );
                }
            }
            
            if ($dataType === self::DATA_TYPE_CALLABLE) {
                if (null === $callable) {
                    throw new BeanException(sprintf("No callable passed for '%s' datatype!", $dataType));
                }
                $this->arrDataType[$normalizedDataName] = [
                    "name" => $dataType,
                    "callback" => $callable,
                    "nullable" => $nullable,
                ];
            } elseif (in_array($dataType, $this->getValidDataType_List())) {
                $this->arrDataType[$normalizedDataName] = [
                    "name" => $dataType,
                    "callback" => null,
                    "nullable" => $nullable,
                ];
            } elseif (class_exists($dataType) || interface_exists($dataType)) {
                $this->arrDataType[$normalizedDataName] = [
                    "name" => $dataType,
                    "callback" => function ($value) use ($dataType) {
                        if (!($value instanceof $dataType)) {
                            throw new BeanException(sprintf("Value is not an instance of '%s'!", $dataType));
                        }
                        return $value;
                    },
                    "nullable" => $nullable,
                ];
            } else {
                throw new BeanException(
                    sprintf("Try to set invalid datatype '%s' for data '%s'!", $dataType, $name), BeanException::ERROR_CODE_INVALID_DATA_TYPE
                );
            }
            
            $this->setOriginalDataName($name, $normalizedDataName);
        } while (false);
        
        return $this;
    }
    
    
    /**
     * @param string $name
     *
     * @return array|null   [ "name" => "<DATA_TYPE>", "callback" => <CALLABLE>? ]
     */
    protected function getDataTypeData(string $name) : ?array
    {
        try {
            $key = $this->normalizeDataName($name);
        } catch (BeanException $e) {
            return null;
        }
        
        if (isset($this->arrDataType[$key]) && is_array($this->arrDataType[$key])) {
            return $this->arrDataType[$key];
        }
        
        return null;
    }
    
    
    /**
     * @param string $name  data name
     *
     * @return null|string
     */
    protected function getDataType(string $name): ?string
    {
        $data = $this->getDataTypeData($name);
        if (isset($data["name"]) && is_string($data["name"])) {
            return $data["name"];
        }
        
        return null;
    }
    
    
    /**
     * @param string $name
     *
     * @return bool
     */
    protected function getDataTypeNullable(string $name): bool
    {
        $data = $this->getDataTypeData($name);
        return (bool)($data["nullable"] ?? false);
    }
    
    
    /**
     * @param string $name  data name
     *
     * @return callable|null
     */
    protected function getDataTypeCallback(string $name): ?callable
    {
        $data = $this->getDataTypeData($name);
        if (isset($data["callback"]) && is_callable($data["callback"])) {
            return $data["callback"];
        }
        
        $dataType = $this->getDataType($name);
        if (null !== $dataType) {
            $methodName = "normalizeDataValue_" . $dataType;
            if (method_exists($this, $methodName)) {
                return [$this, $methodName];
            }
        }
    
        return null;
    }
    
    
    /**
     * @param string $name
     *
     * @return $this
     */
    protected function removeDataType(string $name)
    {
        if (array_key_exists($name, $this->arrDataType)) {
            unset($this->arrDataType[$name]);
        }
        
        return $this;
    }
    
    
    /**
     * @param string $dataType
     *
     * @return string
     */
    protected function normalizeDataType(string $dataType): string
    {
        $dataType = trim($dataType);
        switch (strtolower($dataType)) {
            case self::DATA_TYPE_BOOL:
            case "boolean":
                $dataType = self::DATA_TYPE_BOOL;
                break;
            
            case self::DATA_TYPE_INT:
            case "integer":
                $dataType = "int";
                break;
            
            case self::DATA_TYPE_FLOAT:
            case "double":
                $dataType = self::DATA_TYPE_FLOAT;
                break;
            
            case self::DATA_TYPE_STRING:
            case "str":
                $dataType = self::DATA_TYPE_STRING;
                break;
            
            case self::DATA_TYPE_ARRAY:
            case "arr":
                $dataType = self::DATA_TYPE_ARRAY;
                break;
            
            case self::DATA_TYPE_DATETIME_PHP:
            case self::DATA_TYPE_DATE;
                $dataType = self::DATA_TYPE_DATETIME_PHP;
                break;
            
            case self::DATA_TYPE_OBJECT:
            case "obj";
                $dataType = self::DATA_TYPE_OBJECT;
                break;
            
            case self::DATA_TYPE_RESOURCE:
            case "res";
                $dataType = self::DATA_TYPE_RESOURCE;
                break;
    
            case self::DATA_TYPE_ITERABLE:
            case "iter";
                $dataType = self::DATA_TYPE_ITERABLE;
                break;
    
            case self::DATA_TYPE_CALLABLE:
            case "callback";
                $dataType = self::DATA_TYPE_CALLABLE;
                break;
        }
        
        return $dataType;
    }
    
    
    /**
     * @param mixed  $value
     * @param string $name
     *
     * @return mixed
     * @throws BeanException
     */
    protected function normalizeDataValue($value, string $name)
    {
        $dataType = $this->getDataType($name);
    
        if (null === $dataType) {
            return $value;
        }
        
        if (null === $value) {
            $value = $this->getDefaultValue_for_DataType($dataType);
        }
        
        if (null === $value && !$this->getDataTypeNullable($name)) {
            throw new BeanException(sprintf("Data '%s' can not be NULL!", $name), BeanException::ERROR_CODE_DATA_IS_NOT_NULLABLE);
        }
        
        $callback = $this->getDataTypeCallback($name);
        if (null !== $callback) {
            return call_user_func($callback, $value, $name, $this);
        }
        
        return $value;
    }
    
    
    /**
     * @param $value
     *
     * @return bool
     * @throws BeanException
     */
    protected function normalizeDataValue_bool($value): bool
    {
        $origValue = $value;
        $value = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        if (is_null($value)) {
            throw new BeanException(sprintf("Invalid value '%s' for data type 'boolean'!", is_scalar($origValue) ? (string)$origValue : "NOT_A_SCALAR_VALUE"), BeanException::ERROR_CODE_INVALID_DATA_VALUE);
        }
        
        return $value;
    }
    
    
    /**
     * @param $value
     *
     * @return int
     * @throws BeanException
     */
    protected function normalizeDataValue_int($value): int
    {
        $origValue = $value;
        if (is_bool($value)) {
            $value = null;
        } elseif (is_numeric($value)) {
            $value = intval($value);
        }
        $value = filter_var($value, FILTER_VALIDATE_INT);
        if ($value === false) {
            throw new BeanException(
                sprintf("Invalid value '%s' for data type 'integer'!", is_scalar($origValue) ? (string)$origValue : "NOT_A_SCALAR_VALUE"),
                BeanException::ERROR_CODE_INVALID_DATA_VALUE
            );
        }
        
        return $value;
    }
    
    
    /**
     * @param $value
     *
     * @return float
     * @throws BeanException
     */
    protected function normalizeDataValue_float($value): float
    {
        $origValue = $value;
        if (is_bool($value)) {
            $value = null;
        }
        $value = filter_var($value, FILTER_VALIDATE_FLOAT, ["flags" => FILTER_FLAG_ALLOW_THOUSAND]);
        if ($value === false) {
            throw new BeanException(
                sprintf("Invalid value '%s' for data type 'float'!", is_scalar($origValue) ? (string)$origValue : "NOT_A_SCALAR_VALUE"),
                BeanException::ERROR_CODE_INVALID_DATA_VALUE
            );
        }
        
        return $value;
    }
    
    
    /**
     * @param $value
     *
     * @return string
     * @throws BeanException
     */
    protected function normalizeDataValue_string($value): string
    {
        $origValue = $value;
        try {
            if (is_object($value) && !method_exists($value, "__toString")) {
                throw new BeanException("object to string conversion not possible!");
            } elseif (is_array($value)) {
                throw new BeanException("array to string conversion not possible!");
            }
            $value = (string)$value;
        } catch (Exception $e) {
            throw new BeanException(
                sprintf("Invalid value '%s' for data type 'string' - %s!", is_scalar($origValue) ? (string)$origValue : "NOT_A_SCALAR_VALUE", $e->getMessage()),
                BeanException::ERROR_CODE_INVALID_DATA_VALUE
            );
        }
        
        return $value;
    }
    
    
    /**
     * @param $value
     *
     * @return array
     */
    protected function normalizeDataValue_array($value): array
    {
        if (is_object($value) && method_exists($value, "toArray")) {
            $value = $value->toArray();
        } elseif(is_string($value)) {
            $trimmedValue = trim($value);
            if (substr($trimmedValue, 0, 1) === "{" && substr($trimmedValue, -1) === "}") {
                $value = json_decode($trimmedValue);
            } elseif (substr($trimmedValue, 0, 1) === "[" && substr($trimmedValue, -1) === "]") {
                $value = json_decode($trimmedValue);
            }
        }
        
        $value = (array)$value;
        
        return $value;
    }
    
    
    /**
     * @param $value
     *
     * @return array
     * @throws BeanException
     */
    protected function normalizeDataValue_iterable($value): iterable
    {
        if (!is_iterable($value)) {
            if ($value instanceof stdClass) {
                $value = (array)$value;
            } elseif (is_object($value) && method_exists($value, "toArray")) {
                $value = $value->toArray();
            } else {
                throw new BeanException(
                    "Invalid value for data type 'iterable'!", BeanException::ERROR_CODE_INVALID_DATA_VALUE
                );
            }
        }
        
        return $value;
    }
    
    
    /**
     * @param $value
     *
     * @return DateTimeInterface
     * @throws BeanException
     */
    protected function normalizeDataValue_datetime($value): DateTimeInterface
    {
        if (!($value instanceof DateTimeInterface)) {
            $origValue = $value;
            if (is_numeric($value)) {
                $value = new DateTime();
                $value->setTimestamp(intval($origValue));
            } elseif (is_string($value)) {
                try {
                    $value = DateTime::createFromFormat('Y-m-d H:i:s', $value);
                    if (false === $value) {
                        throw new BeanException("Invalid time string");
                    }
                } catch (Exception $e) {
                    throw new BeanException(
                        sprintf("Invalid value '%s' for data type 'datetime' - %s!", $origValue, $e->getMessage()), BeanException::ERROR_CODE_INVALID_DATA_VALUE
                    );
                }
            } else {
                throw new BeanException(
                    sprintf("Invalid value '%s' for data type 'datetime'!", is_scalar($origValue) ? (string)$origValue : "NOT_A_SCALAR_VALUE"), BeanException::ERROR_CODE_INVALID_DATA_VALUE
                );
            }
        }
    
        return $value;
    }
    
    
    /**
     * @param $value
     *
     * @return object
     * @throws BeanException
     */
    protected function normalizeDataValue_object($value): object
    {
        if (is_scalar($value) || is_null($value) || is_resource($value)) {
            $origValue = $value;
            throw new BeanException(
                sprintf("Invalid value '%s' for data type 'object'!", is_scalar($origValue) ? (string)$origValue : "NOT_A_SCALAR_VALUE"),
                BeanException::ERROR_CODE_INVALID_DATA_VALUE
            );
        } else {
            if (!is_object($value)) {
                $value = (object)$value;
            }
        }
        return $value;
    }
    
    
    /**
     * @param $value
     *
     * @return resource
     * @throws BeanException
     */
    protected function normalizeDataValue_resource($value)
    {
        $origValue = $value;
        if (is_string($value) && is_file($value)) {
            $value = fopen($value, "r");
        }
        if (!is_resource($value)) {
            throw new BeanException(
                sprintf("Invalid value '%s' for data type 'resource'!", is_scalar($origValue) ? (string)$origValue : "NOT_A_SCALAR_VALUE"),
                BeanException::ERROR_CODE_INVALID_DATA_VALUE
            );
        }
        
        return $value;
    }
    
    
    /**
     * @param $value
     *
     * @return callable
     * @throws BeanException
     */
    protected function normalizeDataValue_callable($value): callable
    {
        if (!is_callable($value)) {
            $origValue = $value;
            throw new BeanException(
                sprintf("Invalid value '%s' for data type 'callable'!", is_scalar($origValue) ? (string)$origValue : "NOT_A_SCALAR_VALUE"),
                BeanException::ERROR_CODE_INVALID_DATA_VALUE
            );
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
     * @return string[]
     */
    protected function getValidDataType_List(): array 
    {
        return [
            self::DATA_TYPE_CALLABLE,
            self::DATA_TYPE_STRING,
            self::DATA_TYPE_ARRAY,
            self::DATA_TYPE_INT,
            self::DATA_TYPE_FLOAT,
            self::DATA_TYPE_BOOL,
            self::DATA_TYPE_ITERABLE,
            self::DATA_TYPE_DATE,
            self::DATA_TYPE_DATETIME_PHP,
            self::DATA_TYPE_OBJECT,
            self::DATA_TYPE_RESOURCE,
        ];
    }
    
    
    /**
     * @param string $normalizedDataNamePrefix
     * @param bool   $ignoreSelf
     *
     * @return array
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
    
    
    /**
     * @param string $name
     *
     * @return array    [ "<DATA_KEY>" => <VALUE>, ... ]
     * @throws BeanException
     */
    protected function resolveWildcards(string $name): array
    {
        $arrValue = [];
        $arrName = array_values(array_map("trim", explode(".", $name)));
    
        if (in_array(self::DATA_KEY_WILDCARD, $arrName)) {
            $arrFound = $this->findDataIgnoreWildcards($name);
            if ($arrFound["found"]) {
                $arrValue[$name] = $arrFound["value"] ?? null;
                return $arrValue;
            }
        }
    
        $context = $this->toArray(false);
    
        foreach ($arrName as $nameKey => $nameVal) {
            if ($nameVal === self::DATA_KEY_WILDCARD) {
                $arrContextDataKey = $this->getObjectKeys($context);
            
                foreach ($arrContextDataKey as $contextDataKey) {
                    $newSearchName = $arrName;
                    array_splice($newSearchName, $nameKey, 1, $contextDataKey);
                    $newSearchName = implode(".", $newSearchName);
                
                    if ($contextDataKey === self::DATA_KEY_WILDCARD) {
                        $arrFound = $this->findDataIgnoreWildcards($newSearchName);
                        if ($arrFound["found"]) {
                            $arrValue[$newSearchName] = $arrFound["value"] ?? null;
                        }
                        continue;
                    }
                
                    if (strpos($newSearchName, self::DATA_KEY_WILDCARD) !== false) {
                        $arrValue = array_merge($arrValue, $this->resolveWildcards($newSearchName));
                    } else {
                        $arrFound = $this->findDataIgnoreWildcards($newSearchName);
                        if ($arrFound["found"]) {
                            $arrValue[$newSearchName] = $arrFound["value"] ?? null;
                        }
                    }
                }
                break;
            }
        
            list($context, $contextFound) = $this->getValueAtObjectKey($context, $nameVal);
            if (!$contextFound) {
                break;
            }
        }
        
        return $arrValue;
    }
    
    
    /**
     * @param string $name
     *
     * @return array
     * @throws BeanException
     */
    protected function findDataIgnoreWildcards(string $name): array
    {
        return $this->findData($name, true);
    }
    
    
    /**
     * Return an array with the following properties
     * - found          TRUE or FALSE
     * - context        where data was found or NULL
     * - key            context key where data was found or NULL
     * - value          found value at context<<KEY>>
     *
     * @param      $name
     * @param bool $ignoreWildcards
     *
     * @return array
     * @throws BeanException
     * @todo Refactore handling wildcard names  (e.g.: foo.*) use method "resolveWildcards"
     * @todo Refactore handling dot-notation names
     * @todo UnitTests
     */
    protected function findData(string $name, $ignoreWildcards = false)
    {
        $normalizedName = $this->normalizeDataName($name);
        $flag = array_key_exists($normalizedName, $this->data);
        $value = $contextName = null;
        $context = null;
        
        if (!$flag) {
            if ($name === self::DATA_KEY_WILDCARD) {
                $flag = true;
                $value = $this->toArray();
            } elseif (!$ignoreWildcards && strpos($name, self::DATA_KEY_WILDCARD) !== false) {
                $arrFound = $this->findData($name, true);
                if ($arrFound["found"]) {
                    return $arrFound;
                }
                
                $arrSearchName = [$name];
                $searchNameWithWildcardsCount = 1;
                $killer = 0;
                
                
                while ($searchNameWithWildcardsCount >= 1 && $killer < 100) {
                    ++$killer;
                    
                    foreach ($arrSearchName as $searchNameKey => $searchName) {
                        if (is_array($searchName)) {
                            continue;
                        }
                        
                        $context = $this->data;
                        $arrName = array_values(array_map("trim", explode(".", $searchName)));
                        
                        if (in_array(self::DATA_KEY_WILDCARD, $arrName)) {
                            $arrFound = $this->findData($searchName, true);
                            if ($arrFound["found"]) {
                                --$searchNameWithWildcardsCount;
                                $arrSearchName[$searchNameKey] = $arrFound;
                                continue;
                            }
                        }
                        
                        foreach ($arrName as $nameKey => $nameVal) {
                            if ($nameVal === self::DATA_KEY_WILDCARD) {
                                $arrKey = $this->getObjectKeys($context);
                                --$searchNameWithWildcardsCount;
                                unset($arrSearchName[$searchNameKey]);
                                
                                
                                foreach ($arrKey as $key) {
                                    $newSearchName = $arrName;
                                    array_splice($newSearchName, $nameKey, 1, $key);
                                    $newSearchName = implode(".", $newSearchName);
                                    
                                    if ($key === self::DATA_KEY_WILDCARD) {
                                        $arrFound = $this->findData($newSearchName, true);
                                        if ($arrFound["found"]) {
                                            $arrSearchName[] = $arrFound;
                                        }
                                        continue;
                                    }
                                    
                                    if (strpos($newSearchName, self::DATA_KEY_WILDCARD) !== false) {
                                        ++$searchNameWithWildcardsCount;
                                    }
                                    $arrSearchName[] = $newSearchName;
                                }
                                break;
                            }
                            
                            list($context, $contextFound) = $this->getValueAtObjectKey($context, $nameVal);
                            if (!$contextFound) {
                                --$searchNameWithWildcardsCount;
                                unset($arrSearchName[$searchNameKey]);
                                break;
                            }
                        }
                    }
                }
                
                if (!$flag) {
                    $value = [];
                    foreach ($arrSearchName as $searchName) {
                        if (is_array($searchName)) {
                            if (array_key_exists("value", $searchName)) {
                                $value[] = $searchName["value"];
                            }
                        } else {
                            $arrFound = $this->findData($searchName, true);
                            if ($arrFound["found"]) {
                                $value[] = $arrFound["value"];
                            }
                        }
                    }
                    
                    if (count($value) > 0) {
                        $flag = true;
                    }
                }
            } elseif (strpos($name, ".") >= 1) {
                $arrName = array_values(array_map("trim", explode(".", $name)));
                
                if (!array_key_exists($this->normalizeDataName($arrName[0]), $this->data)) {
                    $dataType = $this->getDataType($arrName[0]);
                    if ($dataType) {
                        $value = $this->getDefaultValue_for_DataType($dataType) ?? $value;
                    }
                    if (is_callable($value)) {
                        $value = call_user_func($value, $arrName[0]);
                    }
                    
                    $this->setData($arrName[0], $value);
                }
                
                $context = $this->data;
                
                $deep = 1;
                while ($deep < 100 && count($arrName) && $context) {
                    $contextName = array_shift($arrName);
                    if ($deep == 1) {
                        $contextName = $this->normalizeDataName($contextName);
                    }
                    
                    $oldContext = $context;
                    list($context, $contextFound) = $this->getValueAtObjectKey($context, $contextName);
                    if ($contextFound && !$arrName) {
                        $flag = true;
                    }
                    
                    if (!$arrName) {
                        $value = $context;
                        $context = $oldContext;
                    } else {
                        if (is_scalar($context)) {
                            $context = null;
                        }
                    }
                    
                    ++$deep;
                }
            }
        } else {
            $contextName = $normalizedName;
            $context = $this->data;
            $value = $this->data[$normalizedName];
        }
        
        return [
            "found" => $flag,
            "key" => $flag ? $contextName : null,
            "value" => $flag ? $value : null,
            "context" => $flag ? $context : null,
        ];
    }
    
    
    /**
     * @param $object
     *
     * @return array
     */
    protected function getObjectKeys($object): array
    {
        $arrKey = [];
        
        try {
            if (is_array($object)) {
                $arrKey = ObjectPropertyFinder::createFromArray($object)->getKeys();
            } elseif (is_object($object)) {
                $arrKey = ObjectPropertyFinder::createFromObject($object)->getKeys();
            }
        } catch (Exception $e) {
        }
        
        return $arrKey;
    }
    
    
    /**
     * @param $object
     * @param $key
     *
     * @return array    [ <VALUE>, (bool)<KEY_FOUND> ]
     */
    protected function getValueAtObjectKey($object, $key): array
    {
        $found = false;
        
        if (($object instanceof BeanInterface)) {
            if ($object instanceof BeanListInterface && (string)(int)$key === (string)$key) {
                if ($object->offsetExists($key)) {
                    $object = $object->offsetGet($key);
                    $found = true;
                } else {
                    $object = null;
                }
            } elseif ($object->hasData($key)) {
                $object = $object->getData($key);
                $found = true;
            } else {
                $object = null;
            }
        } else {
            try {
                $finder = new ObjectPropertyFinder($object);
            } catch (Exception $e) {
                $finder = null;
            }
            
            if ($finder) {
                $found = $finder->hasKey($key);
                $object = $finder->getValue($key);
            } else {
                $object = null;
            }
        }
        
        return [$object, $found];
    }
    
    
    /**
     * Return parent name for dot notation data names
     * e.g.: the parent name for "foo.bar.baz" is "foo.bar"
     *
     * @param string $name leading and trailing dots will be trimmed
     *
     * @return string|null  NULL if there is no parent name
     */
    protected function getParentDataName(string $name): ?string
    {
        $parentName = null;
        $name = trim($name, ".");
        if (false !== ($lastDotPos = strrpos($name, "."))) {
            $parentName = substr($name, 0, $lastDotPos);
        }
        
        return $parentName;
    }
    
    
    /**
     * @param string $name
     *
     * @return bool
     */
    protected function hasParentDataName(string $name): bool
    {
        return null !== $this->getParentDataName($name);
    }
    
    
    /**
     * @param string $name
     * @param string $dataType
     * @param bool   $overwrite
     *
     * @return $this
     * @throws BeanException
     */
    protected function setDataType_to_Parent(string $name, string $dataType, bool $overwrite = false): self
    {
        if (($parentName = $this->getParentDataName($name))) {
            if ($overwrite || null === $this->getDataType($parentName)) {
                $this->setDataType($parentName, $dataType);
            }
        }
        
        return $this;
    }
    
    
    /**
     * @param string $name
     *
     * @return string|null
     */
    protected function getDataType_from_Parent(string $name): ?string
    {
        $dataType = null;
        
        if (($parentName = $this->getParentDataName($name))) {
            $dataType = $this->getDataType($parentName);
        }
        return $dataType;
    }
}