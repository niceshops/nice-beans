<?php
declare(strict_types=1);

namespace Niceshops\Library\Core\Bean\DatabaseBean;

use Niceshops\Library\Core\Bean\BeanException;

/**
 * Class DefaultDatabaseBean
 * @package Niceshops\Library\Core
 */
class DefaultDatabaseBean extends AbstractDatabaseBean
{
    /**
     * @param string $name
     * @param string $dataType
     * @param array  $columnTypes
     * @param string $dbColumnName
     *
     * @return AbstractDatabaseBean|DefaultDatabaseBean
     * @throws BeanException
     */
    public function setDatabaseField(string $name, string $dataType, array $columnTypes = [], string $dbColumnName = "") : AbstractDatabaseBean
    {
       return parent::setDatabaseField($name, $dataType, $columnTypes, $dbColumnName);
    }
    
    /**
     * @param string $name
     * @param string $dataType
     * @param string $mapField
     *
     * @return AbstractDatabaseBean|DefaultDatabaseBean
     * @throws BeanException
     */
    public function setMappedField(string $name, string $dataType, string $mapField) : AbstractDatabaseBean
    {
        return parent::setMappedField($name, $dataType, $mapField);
    }
}
