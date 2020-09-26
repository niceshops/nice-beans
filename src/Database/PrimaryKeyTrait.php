<?php
declare(strict_types=1);

namespace NiceshopsDev\Bean\Database;

/**
 * Trait PrimaryKeyTrait
 * @package Niceshops\Library\Core
 */
trait PrimaryKeyTrait
{
    /**
     * @var array
     */
    private $arrDatabasePrimaryKeys = [];

    /**
     * @return array
     */
    protected function getDatabasePrimaryKeys(): array
    {
        return $this->arrDatabasePrimaryKeys;
    }

    /**
     * @param string $key
     * @param string $dbColumnName
     */
    protected function addDatabasePrimaryKey(string $key, string $dbColumnName)
    {
        $this->arrDatabasePrimaryKeys[$key] = $dbColumnName;
    }

    /**
     * @param string $key
     *
     * @return mixed
     */
    protected function getPrimaryKeyField(string $key)
    {
        return $this->arrDatabasePrimaryKeys[$key];
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    protected function hasPrimaryKeyField(string $key)
    {
        return isset($this->arrDatabasePrimaryKeys[$key]);
    }

}
