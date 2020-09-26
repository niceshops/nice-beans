<?php
namespace Niceshops\Library\Core\Bean\DatabaseBean;

/**
 * Trait UniqueKeyTrait
 * @package Niceshops\Library\Core
 */
trait UniqueKeyTrait
{
    /**
     * @var array
     */
    private $arrDatabaseUniqueKeys = [];
    
    /**
     * @return array
     */
    protected function getDatabaseUniqueKeys(): array
    {
        return $this->arrDatabaseUniqueKeys;
    }
    
    /**
     * @param string $key
     * @param string $dbColumnName
     */
    protected function addDatabaseUniqueKey(string $key, string $dbColumnName)
    {
        $this->arrDatabaseUniqueKeys[$key] = $dbColumnName;
    }
    
    /**
     * @param string $key
     *
     * @return mixed
     */
    protected function getUniqueKeyField(string $key)
    {
        return $this->arrDatabaseUniqueKeys[$key];
    }
    
    /**
     * @param string $key
     *
     * @return bool
     */
    protected function hasUniqueKeyField(string $key)
    {
        return isset($this->arrDatabaseUniqueKeys[$key]);
    }
    
}