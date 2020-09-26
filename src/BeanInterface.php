<?php
declare(strict_types=1);
/**
 * @see       https://github.com/niceshops/nice-beans for the canonical source repository
 * @license   https://github.com/niceshops/nice-beans/blob/master/LICENSE BSD 3-Clause License
 */

namespace NiceshopsDev\Bean;

use ArrayAccess;
use Countable;
use IteratorAggregate;

/**
 * Interface BeanInterface
 * @package Niceshops\Library\Core\Bean
 */
interface BeanInterface extends ArrayAccess, IteratorAggregate, Countable
{

    /**
     * @param string $name
     * @param mixed  $value
     *
     * @return BeanInterface
     */
    function setData($name, $value);


    /**
     * @param string $name
     *
     * @return mixed
     */
    function getData($name);


    /**
     * @param string $name
     *
     * @return bool
     */
    function hasData($name);


    /**
     * @param string $name
     *
     * @return mixed    the removed data or NULL if data couldn't be found
     */
    function removeData($name);


    /**
     * @return BeanInterface
     */
    function resetData();


    /**
     * @return array
     */
    function toArray();


    /**
     * @param array $data
     *
     * @return mixed
     */
    function setFromArray(array $data);

    /**
     * @param array $arrData
     * @return mixed
     */
    static public function createFromArray(array $arrData);
}
