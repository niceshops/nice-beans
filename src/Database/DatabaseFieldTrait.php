<?php
namespace Niceshops\Library\Core\Bean\DatabaseBean;

/**
 * Trait DatabaseFieldTrait
 * @package Niceshops\Library\Core
 */
trait DatabaseFieldTrait
{
    
    /**
     * @var array
     */
    private $arrDatabaseFields = [];
    
    /**
     * @return array
     */
    protected function getDatabaseFields(): array
    {
        return $this->arrDatabaseFields;
    }
    
    /**
     * @param string $key
     * @param string $dbColumnName
     */
    protected function addDatabaseField(string $key, string $dbColumnName)
    {
        $this->arrDatabaseFields[$key] = $dbColumnName;
    }
    
    /**
     * @param string $key
     *
     * @return mixed
     */
    protected function getDatabaseField(string $key)
    {
        return $this->arrDatabaseFields[$key];
    }
    
    /**
     * @param string $key
     *
     * @return bool
     */
    protected function hasDatabaseField(string $key)
    {
        return isset($this->arrDatabaseFields[$key]);
    }
    
}