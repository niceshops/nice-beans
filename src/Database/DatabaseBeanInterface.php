<?php
declare(strict_types=1);

namespace NiceshopsDev\Bean\Database;


/**
 * Interface DataObjectAwareBeanInterface
 * @package Bean
 */
interface DatabaseBeanInterface
{
    const COLUMN_TYPE_DEFAULT = "default";
    const COLUMN_TYPE_PRIMARY_KEY = "primary_key";
    const COLUMN_TYPE_FOREIGN_KEY = "foreign_key";
    const COLUMN_TYPE_UNIQUE = "unique";

    const SERIALIZE_DATABASE_FIELDS_KEY = "arrDatabaseFields";
    const SERIALIZE_DATABASE_PRIMARY_FIELDS_KEY = "arrDatabasePrimaryKeys";
    const SERIALIZE_DATABASE_FOREIGN_FIELDS_KEY = "arrDatabaseForeignKeys";
    const SERIALIZE_DATABASE_UNIQUE_FIELDS_KEY = "arrDatabaseUniqueKeys";
    const SERIALIZE_DATABASE_MAPPED_FIELDS_KEY = "arrDatabaseMappedFields";


    /**
     * @return array
     */
    public function getFieldsForDatabase(): array;


    /**
     * @param array $arrayData
     *
     */
    public function setFieldsFromDatabase(array $arrayData): void;

    /**
     * @return array
     */
    public function getPrimaryKeys(): array;

    /**
     * @return array
     */
    public function getUnqiqueKeys(): array;

    /**
     * @return array
     */
    public function getForeignKeys(): array;
}
