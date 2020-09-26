<?php
declare(strict_types=1);

namespace NiceshopsDev\Bean\Database;


use NiceshopsDev\Bean\BeanException;

/**
 * Class ParentBeanTrait
 */
trait DatabaseBeanTrait
{
    use DatabaseFieldTrait;
    use ForeignKeyTrait;
    use PrimaryKeyTrait;
    use UniqueKeyTrait;
    use MappedFieldTrait;

    /**
     * @return bool
     * @throws BeanException
     */
    public function hasPrimaryKeyValue(): bool
    {
        foreach ($this->getDatabasePrimaryKeys() as $name => $databasePrimaryKey) {
            if ($this->hasData($name)) {
                return true;
            }
        }
        return false;
    }


    /**
     * @param $value
     * @throws \NiceshopsDev\Bean\BeanException
     */
    public function setPrimaryKeyValue($value): self
    {
        foreach ($this->getDatabasePrimaryKeys() as $field => $dbColumn) {
            $this->setData($field, $value);
        }
        return $this;
    }

    /**
     * @param string|null $columnType
     * @return array
     */
    public function getDatabaseFieldName_Map(?string $columnType = null): array
    {
        switch ($columnType) {
            case self::COLUMN_TYPE_PRIMARY_KEY:
                return $this->getDatabasePrimaryKeys();
            case self::COLUMN_TYPE_UNIQUE:
                return $this->getDatabaseUniqueKeys();
            case self::COLUMN_TYPE_FOREIGN_KEY:
                return $this->getDatabaseForeignKeys();
        }
        return $this->getDatabaseFields();
    }

    /**
     * @param string $name
     * @return mixed
     * @throws BeanException
     */
    public function getDatabaseColumn(string $name): string
    {
        if ($this->hasDatabaseField($name)) {
            return $this->getDatabaseField($name);
        }
        throw new BeanException("No database field for name: $name.");
    }


    /**
     * @param string $name
     * @param string $dataType
     * @param array $columnTypes
     * @param string $dbColumnName optional name of db column default is same as $name
     *
     * @return AbstractDatabaseBean
     */
    protected function setDatabaseField(string $name, string $dataType, array $columnTypes = [], string $dbColumnName = ""): self
    {
        if (!strlen(trim($dbColumnName))) {
            $dbColumnName = $name;
        }
        if (!in_array(self::COLUMN_TYPE_DEFAULT, $columnTypes)) {
            $columnTypes[] = self::COLUMN_TYPE_DEFAULT;
        }

        $this->setDataType($name, $dataType, true);

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
    protected function setMappedField(string $name, string $dataType, string $mapField): self
    {
        if (!$this->hasData($mapField)) {
            $this->setData($mapField, []);
        }
        $this->setDataType($name, $dataType, true);
        $key = $this->normalizeDataName($name);
        $mapFieldKey = $this->normalizeDataName($mapField);
        if ($this->isFieldMappedToData($mapFieldKey)) {
            throw new BeanException("Can not map field to already mapped field! Mapping: $mapFieldKey => " . $this->arrMappedFields[$mapFieldKey]);
        }
        $this->addMappedField($key, $mapFieldKey);
        return $this;
    }


    /**
     * @return array
     */
    public function getSerializeData(): array
    {
        $arrData = parent::getSerializeData();
        $arrData[self::SERIALIZE_DATABASE_FIELDS_KEY] = $this->getDatabaseFields();
        $arrData[self::SERIALIZE_DATABASE_PRIMARY_FIELDS_KEY] = $this->getDatabasePrimaryKeys();
        $arrData[self::SERIALIZE_DATABASE_FOREIGN_FIELDS_KEY] = $this->getDatabaseForeignKeys();
        $arrData[self::SERIALIZE_DATABASE_UNIQUE_FIELDS_KEY] = $this->getDatabaseUniqueKeys();
        $arrData[self::SERIALIZE_DATABASE_MAPPED_FIELDS_KEY] = $this->getMappedFields();
        return $arrData;
    }

    /**
     * @return array
     */
    static protected function getSerializeKey_List()
    {
        $arrList = parent::getSerializeKey_List();
        $arrList[] = self::SERIALIZE_DATABASE_FIELDS_KEY;
        $arrList[] = self::SERIALIZE_DATABASE_PRIMARY_FIELDS_KEY;
        $arrList[] = self::SERIALIZE_DATABASE_FOREIGN_FIELDS_KEY;
        $arrList[] = self::SERIALIZE_DATABASE_UNIQUE_FIELDS_KEY;
        $arrList[] = self::SERIALIZE_DATABASE_MAPPED_FIELDS_KEY;
        return $arrList;
    }

    /**
     * @param array $data
     *
     * @return $this
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
        if (!empty($data[self::SERIALIZE_DATABASE_MAPPED_FIELDS_KEY])) {
            $this->arrMappedFields = $data[self::SERIALIZE_DATABASE_MAPPED_FIELDS_KEY];
        }
        return $this;
    }
}
