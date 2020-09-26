<?php
namespace Niceshops\Library\Core\Bean\DatabaseBean;

use Niceshops\Library\Core\Bean\BeanException;

/**
 * Trait MappedFieldTrait
 * @package Niceshops\Library\Core
 */
trait MappedFieldTrait
{
    
    /**
     * @var array
     */
    private $arrMappedFields = [];
    
    /**
     * @return array
     */
    protected function getMappedFields(): array
    {
        return $this->arrMappedFields;
    }
    
    /**
     * @param string $key
     * @param string $mapFieldKey
     */
    protected function addMappedField(string $key, string $mapFieldKey)
    {
        $this->arrMappedFields[$key] = $mapFieldKey;
    }
    
    /**
     * @param string $key
     *
     * @return mixed
     */
    protected function getMappedField(string $key)
    {
        return $this->arrMappedFields[$key];
    }
    
    /**
     * @param string $key
     *
     * @return bool
     */
    protected function isFieldMappedToData(string $key)
    {
        return isset($this->arrMappedFields[$key]);
    }
    
    /**
     * @param string $key
     *
     * @return bool
     */
    protected function isDataMappedToField(string $key)
    {
        return in_array($key, $this->getMappedFields());
    }
    
    /**
     * @param $name
     * @param $value
     *
     * @return $this
     * @throws BeanException
     */
    public function setData($name, $value)
    {
        $key = $this->normalizeDataName($name);
        if ($this->isFieldMappedToData($key) && $this->hasData($this->getMappedField($key))) {
            return parent::setData($this->getMappedField($key) .  "." . $key, $value);
        }
        
         return parent::setData($name, $value);
    }
    
    /**
     * @param $name
     *
     * @return mixed
     * @throws \Datenkraft_Exception
     * @throws \Zend_Date_Exception
     */
    public function getData($name)
    {
        $key = $this->normalizeDataName($name);
        if ($this->isFieldMappedToData($key)) {
            return parent::getData($this->getMappedField($key) .  "." . $key);
        }
        
        return parent::getData($name);
    }
    
    
    /**
     * @param string $name
     *
     * @return bool
     * @throws BeanException
     */
    public function hasData($name)
    {
        $key = $this->normalizeDataName($name);
        if ($this->isFieldMappedToData($key)) {
            return parent::hasData($this->getMappedField($key) .  "." . $key);
        }
        
        return parent::hasData($name);
    }
    
    /**
     * @throws BeanException
     * @throws \Datenkraft_Exception
     * @throws \Zend_Date_Exception
     */
    protected function initializeDataFromMappedFields()
    {
        foreach ($this->getMappedFields() as $name => $mappedField) {
            if ($this->hasData($mappedField)) {
                $mappedData = $this->getData($mappedField);
                if (isset($mappedData[$name])) {
                    $this->setData($name, $mappedData[$name]);
                }
            }
        }
    }
    
    /**
     * @throws BeanException
     * @throws \Datenkraft_Exception
     * @throws \Zend_Date_Exception
     */
    protected function initializeMappedFieldsFromData()
    {
        foreach ($this->getMappedFields() as $name => $mappedField) {
            if ($this->hasData($name)) {
                $mappedData = [];
                if ($this->hasData($mappedField)) {
                    $mappedData = $this->getData($mappedField);
                }
                $mappedData[$name] = $this->getData($name);
                $this->setData($mappedField, $mappedData);
            }
        }
    }
  
    
}