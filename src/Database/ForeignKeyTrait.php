<?php
declare(strict_types=1);

namespace NiceshopsDev\Bean\Database;

/**
 * Trait ForeignKeyTrait
 * @package Niceshops\Library\Core
 */
trait ForeignKeyTrait
{
    /**
     * @var array
     */
    private $arrDatabaseForeignKeys = [];

    /**
     * @return array
     */
    protected function getDatabaseForeignKeys(): array
    {
        return $this->arrDatabaseForeignKeys;
    }

    /**
     * @param string $key
     * @param string $dbColumnName
     */
    protected function addDatabaseForeignKey(string $key, string $dbColumnName)
    {
        $this->arrDatabaseForeignKeys[$key] = $dbColumnName;
    }

    /**
     * @param string $key
     *
     * @return mixed
     */
    protected function getForeignKeyField(string $key)
    {
        return $this->arrDatabaseForeignKeys[$key];
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    protected function hasForeignKeyField(string $key)
    {
        return isset($this->arrDatabaseForeignKeys[$key]);
    }

}
