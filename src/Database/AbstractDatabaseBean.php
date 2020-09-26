<?php
declare(strict_types=1);

namespace NiceshopsDev\Bean\Database;

use NiceshopsDev\Bean\JsonSerializable\AbstractJsonSerializableBean;


/**
 * Class AbstractDatabaseBean
 * @package Niceshops\Library\Core\Bean\DatabaseBean
 */
abstract class AbstractDatabaseBean extends AbstractJsonSerializableBean implements DatabaseBeanInterface
{
    use DatabaseBeanTrait;
}
