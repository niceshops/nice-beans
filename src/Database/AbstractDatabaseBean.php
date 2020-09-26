<?php
namespace Niceshops\Library\Core\Bean\DatabaseBean;

use Niceshops\Library\Core\Bean\AbstractBean;
use Niceshops\Library\Core\Bean\BackendBean\BackendBeanInterface;
use Niceshops\Library\Core\Bean\BackendBean\BackendBeanTrait;
use Niceshops\Library\Core\Bean\BeanException;
use NiceshopsDev\Bean\AbstractBaseBean;


/**
 * Class AbstractDatabaseBean
 * @package Niceshops\Library\Core\Bean\DatabaseBean
 */
abstract class AbstractDatabaseBean extends AbstractBaseBean implements DatabaseBeanInterface
{
    use DatabaseBeanTrait;

}
