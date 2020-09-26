<?php

namespace Niceshops\Library\Core\Bean\DatabaseBean;

use Datenkraft_Exception;
use Niceshops\Library\Core\Bean\AbstractBean;
use Niceshops\Library\Core\Bean\BackendBean\BackendBeanTrait;
use Niceshops\Library\Core\Bean\BeanException;
use Niceshops\Library\Core\Bean\ViewIDAwareBeanTrait;
use Zend_Date_Exception;

/**
 * Class ParentBeanTrait
 */
trait DatabaseBeanTrait
{
    use BackendBeanTrait;
    use DatabaseFieldTrait;
    use ForeignKeyTrait;
    use PrimaryKeyTrait;
    use UniqueKeyTrait;
    use MappedFieldTrait;
    
    /**
     * @return array
     * @throws Datenkraft_Exception
     * @throws BeanException
     * @throws Zend_Date_Exception
     */
    public function toPersistence() : array
    {
        return $this->getFieldsForDatabase();
    }
    
    /**
     * @param string $viewID
     *
     * @return $this
     */
    public function setViewID(string $viewID) : self
    {
        $this->setDatabaseViewID($viewID);
        return $this;
    }
    
    /**
     * @return string
     */
    public function getViewID(): string
    {
        return $this->getDatabaseViewID();
    }
    
    /**
     * @return array
     * @throws BeanException
     * @throws Datenkraft_Exception
     * @throws Zend_Date_Exception
     */
    public function getFieldsForDatabase(): array
    {
        $arrDatabaseData = [];
        foreach ($this->getDatabaseFields() as $name => $dbColumnName) {
            if ($this->hasData($name)) {
                $arrDatabaseData[$dbColumnName] = $this->convertValueToDatabase($this->getData($name), $this->getDataType($name));
            }
        }
        return $arrDatabaseData;
    }
    
    
    /**
     * @param array $arrayData
     *
     * @throws BeanException
     */
    public function setFieldsFromDatabase(array $arrayData): void
    {
        foreach ($this->getDatabaseFields() as $name => $dbColumnName) {
            if (isset($arrayData[$dbColumnName])) {
                $this->setData($this->getOriginalDataName($name), $this->convertValueFromDatabase($arrayData[$dbColumnName], $this->getDataType($name)));
            }
        }
        $this->setData('_TranslationState', $arrayData['_TranslationState']);
    }
    
    /**
     * @param bool $includeForeignKeys
     *
     * @return string
     */
    public function getDatabaseViewID(bool $includeForeignKeys = false) : string
    {
        $keys = $this->getDatabasePrimaryKeys();
        if ($includeForeignKeys) {
            $keys = array_merge($keys, $this->getDatabaseForeignKeys());
        }
        return $this->getViewIDFromData($keys);
    }
    
    /**
     * @param string $viewID
     */
    public function setDatabaseViewID(string $viewID) : void
    {
        $arrKeys = array_merge($this->getDatabasePrimaryKeys(), $this->getDatabaseForeignKeys());
        $this->setDataFromViewID($viewID, $arrKeys);
    }
    
    /**
     * @return string|null
     */
    private function getNullValueFromDataObject()
    {
        return \DB_DataObject::_is_null(null, false) ? null : 'NULL';
    }
    
    /**
     * @param bool $includeForeignKeys
     *
     * @throws BeanException
     */
    public function removeDatabaseViewID(bool $includeForeignKeys = false) : void
    {
        $keys = $this->getDatabasePrimaryKeys();
        if ($includeForeignKeys) {
            $keys = array_merge($keys, $this->getDatabaseForeignKeys());
        }
        foreach ($keys as $key) {
            $this->setData($key, null);
        }
    }
    
    /***
     * @param        $value
     * @param string $type
     *
     * @return string
     * @throws Datenkraft_Exception
     * @throws Zend_Date_Exception
     */
    private function convertValueToDatabase($value, string $type) {
        $result = $this->getNullValueFromDataObject();
        if (is_null($value)) {
            return $result;
        }
        switch ($type) {
            case AbstractBean::DATA_TYPE_STRING:
                $result = \Datenkraft_Db_Escape::string(strval($value));
                break;
            case AbstractBean::DATA_TYPE_INT:
                $result = \Datenkraft_Db_Escape::int($value);
                break;
            case AbstractBean::DATA_TYPE_FLOAT:
                $result = \Datenkraft_Db_Escape::float($value);
                break;
            case AbstractBean::DATA_TYPE_DATE:
                $date = new \Datenkraft_Date($value);
                $result = $date->toSql(true);
                break;
            case AbstractBean::DATA_TYPE_DATETIME:
            case AbstractBean::DATA_TYPE_DATETIME_PHP:
                $date = new \Datenkraft_Date($value);
                $result = $date->toSql();
                break;
            case AbstractBean::DATA_TYPE_ARRAY:
            case AbstractBean::DATA_TYPE_OBJECT:
                $result = \Zend_Json_Encoder::encode($value);
                break;
            case AbstractBean::DATA_TYPE_BOOL:
                if ($value === true) {
                    $result = "true";
                } elseif ($value === false) {
                    $result = "false";
                }
                break;
        }
        return $result;
    }
    
    /***
     * @param        $value
     * @param string $type
     *
     * @return string
     */
    private function convertValueFromDatabase($value, string $type) {
        $result = null;
        switch ($type) {
            case AbstractBean::DATA_TYPE_STRING:
                $result = strval($value);
                break;
            case AbstractBean::DATA_TYPE_INT:
                $result = intval($value);
                break;
            case AbstractBean::DATA_TYPE_FLOAT:
                $result = floatval($value);
                break;
            case AbstractBean::DATA_TYPE_DATE:
                try {
                    $result = new \Datenkraft_Date($value, \Datenkraft_Date::PART_MYSQL);
                } catch (Zend_Date_Exception $ex) {
                    $result = null;
                }
                break;
            case AbstractBean::DATA_TYPE_DATETIME:
                try {
                    $result = new \Datenkraft_Date($value, \Datenkraft_Date::PART_MYSQL_DATETIME);
                } catch (Zend_Date_Exception $ex) {
                    $result = null;
                }
                break;
            case AbstractBean::DATA_TYPE_DATETIME_PHP:
                try {
                    $datetime = new \DateTime();
                    $datetime->setTimestamp((new \Datenkraft_Date($value,\Datenkraft_Date::PART_MYSQL_DATETIME))->getTimestamp());
                    $result = $datetime;
                } catch (Zend_Date_Exception $ex) {
                    $result = null;
                }
                break;
            case AbstractBean::DATA_TYPE_ARRAY:
            case AbstractBean::DATA_TYPE_OBJECT:
                try {
                    $result = \Zend_Json_Decoder::decode($value);
                } catch (\Zend_Json_Exception $ex) {
                    $result = null;
                }
                break;
            case AbstractBean::DATA_TYPE_BOOL:
                if ($value === "true") {
                    $result = true;
                } elseif ($value === "false") {
                    $result = false;
                }
                break;
        }
        return $result;
    }
    
    /**
     * @return array
     * @throws BeanException
     * @throws Datenkraft_Exception
     * @throws Zend_Date_Exception
     */
    public function toDatabase(): array
    {
        return $this->getFieldsForDatabase();
    }
    
    
    /**
     * @param array $arrayData
     *
     * @throws BeanException
     */
    public function initByDatabase(array $arrayData) : void
    {
        $this->setFieldsFromDatabase($arrayData);
    }
    
    /**
     * @param string $name
     * @param string $dataType
     * @param array  $columnTypes
     * @param string $dbColumnName optional name of db column default is same as $name
     *
     * @return AbstractDatabaseBean
     * @throws BeanException
     */
    protected function setDatabaseField(string $name, string $dataType, array $columnTypes = [], string $dbColumnName = "") : self
    {
        if (!strlen(trim($dbColumnName))) {
            $dbColumnName = $name;
        }
        if (!in_array(self::COLUMN_TYPE_DEFAULT, $columnTypes)) {
            $columnTypes[] = self::COLUMN_TYPE_DEFAULT;
        }
        
        $this->throwErrors = true;
        if ($this->isSealed() || $this->isFrozen()) {
            $this->throwError('Can not change data types when bean is sealed or frozen!');
        }
        
        $this->setDataType($name, $dataType);
        
        $key = $this->normalizeDataName($name);
        
        $this->addDatabaseField($key, $dbColumnName);
        
        if (in_array(self::COLUMN_TYPE_PRIMARY_KEY, $columnTypes)) {
            $this->addDatabasePrimaryKey($key, $dbColumnName);
        }
        if (in_array(self::COLUMN_TYPE_FOREIGN_KEY, $columnTypes)) {
            $this->addDatabaseForeignKey($key, $dbColumnName);
        }
        if (in_array(self::COLUMN_TYPE_UNIQUE, $columnTypes)) {
            $this->addDatabaseUniqueKey($key, $dbColumnName);
        }
        return $this;
    }
    
    /**
     * @param string $name
     * @param string $dataType
     * @param string $mapField
     *
     * @return AbstractDatabaseBean
     * @throws BeanException
     */
    protected function setMappedField(string $name, string $dataType, string $mapField) : self
    {
        $this->throwErrors = true;
        if ($this->getDataType($mapField) != self::DATA_TYPE_ARRAY) {
            $this->throwError("Mapped field is not an array!");
        }
        if ($this->isSealed() || $this->isFrozen()) {
            $this->throwError('Can not change data types when bean is sealed or frozen!');
        }
        if (!$this->hasData($mapField)) {
            $this->setData($mapField, []);
        }
        $this->setDataType($name, $dataType);
        $key = $this->normalizeDataName($name);
        $mapFieldKey = $this->normalizeDataName($mapField);
        if ($this->isFieldMappedToData($mapFieldKey)) {
            $this->throwError("Can not map field to already mapped field! Mapping: $mapFieldKey => " . $this->arrMappedFields[$mapFieldKey]);
        }
        $this->addMappedField($key, $mapFieldKey);
        return $this;
    }
    
    
 
    
    
    /**
     * @return array
     * @throws \Datenkraft_Exception
     */
    public function getSerializeData(): array
    {
        $arrData =  parent::getSerializeData();
        $arrData[self::SERIALIZE_DATABASE_FIELDS_KEY] = $this->getDatabaseFields();
        $arrData[self::SERIALIZE_DATABASE_PRIMARY_FIELDS_KEY] = $this->getDatabasePrimaryKeys();
        $arrData[self::SERIALIZE_DATABASE_FOREIGN_FIELDS_KEY] = $this->getDatabaseForeignKeys();
        $arrData[self::SERIALIZE_DATABASE_UNIQUE_FIELDS_KEY] = $this->getDatabaseUniqueKeys();
        $arrData[self::SERIALIZE_DATABASE_MAPPED_FIELDS_KEY] = $this->getMappedFields();
    
        $arrData[self::SERIALIZE_VIEW_ID] = $this->getViewID();
        return $arrData;
    }
    
    /**
     * @return array
     */
    static protected function getSerializeKey_List()
    {
        $arrList =  parent::getSerializeKey_List();
        $arrList[] = self::SERIALIZE_DATABASE_FIELDS_KEY;
        $arrList[] = self::SERIALIZE_DATABASE_PRIMARY_FIELDS_KEY;
        $arrList[] = self::SERIALIZE_DATABASE_FOREIGN_FIELDS_KEY;
        $arrList[] = self::SERIALIZE_DATABASE_UNIQUE_FIELDS_KEY;
        $arrList[] = self::SERIALIZE_VIEW_ID;
        $arrList[] = self::SERIALIZE_DATABASE_MAPPED_FIELDS_KEY;
    
        return $arrList;
    }
    
    /**
     * @param array $data
     *
     * @return $this|AbstractBean
     * @throws \Datenkraft_Exception
     * @throws \Zend_Date_Exception
     */
    public function setSerializeData(array $data)
    {
        parent::setSerializeData($data);
        if (!empty($data[self::SERIALIZE_DATABASE_FIELDS_KEY])) {
            $this->arrDatabaseFields = $data[self::SERIALIZE_DATABASE_FIELDS_KEY];
        }
        if (!empty($data[self::SERIALIZE_DATABASE_PRIMARY_FIELDS_KEY])) {
            $this->arrDatabasePrimaryKeys = $data[self::SERIALIZE_DATABASE_PRIMARY_FIELDS_KEY];
        }
        if (!empty($data[self::SERIALIZE_DATABASE_FOREIGN_FIELDS_KEY])) {
            $this->arrDatabaseForeignKeys = $data[self::SERIALIZE_DATABASE_FOREIGN_FIELDS_KEY];
        }
        if (!empty($data[self::SERIALIZE_DATABASE_UNIQUE_FIELDS_KEY])) {
            $this->arrDatabaseUniqueKeys = $data[self::SERIALIZE_DATABASE_UNIQUE_FIELDS_KEY];
        }
        if (!empty($data[self::SERIALIZE_VIEW_ID])) {
            $this->viewID = $data[self::SERIALIZE_VIEW_ID];
        }
        if (!empty($data[self::SERIALIZE_DATABASE_MAPPED_FIELDS_KEY])) {
            $this->arrMappedFields = $data[self::SERIALIZE_DATABASE_MAPPED_FIELDS_KEY];
        }
        return $this;
    }
}
