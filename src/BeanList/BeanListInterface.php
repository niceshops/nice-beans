<?php
declare(strict_types=1);
/**
 * @see       https://github.com/niceshops/nice-beans for the canonical source repository
 * @license   https://github.com/niceshops/nice-beans/blob/master/LICENSE BSD 3-Clause License
 */

namespace NiceshopsDev\Bean\BeanList;

use NiceshopsDev\Bean\BeanInterface;

/**
 * Interface BeanListInterface
 * @package NiceshopsDev\Bean\BeanList
 */
interface BeanListInterface extends BeanInterface
{
    /**
     * @param BeanInterface $bean
     * @return mixed
     */
    public function addBean(BeanInterface $bean);

    /**
     * @param $beans
     * @return mixed
     */
    public function addBeans($beans);

    /**
     * @param BeanInterface $bean
     * @return mixed
     */
    public function removeBean(BeanInterface $bean);

    /**
     * @param BeanInterface $bean
     * @return mixed
     */
    public function hasBean(BeanInterface $bean);

    /**
     * @param BeanInterface $bean
     * @return mixed
     */
    public function indexOfBean(BeanInterface $bean);

    /**
     * @return mixed
     */
    public function getBeans();

    /**
     * @param $beans
     * @return mixed
     */
    public function setBeans($beans);

    /**
     * @return mixed
     */
    public function resetBeans();

    /**
     * @param int $offset
     * @param null $length
     * @param int $stepWidth
     * @return mixed
     */
    public function slice($offset = 0, $length = null, $stepWidth = 1);

    /**
     * @param callable $callback
     * @return mixed
     */
    public function each(callable $callback);

    /**
     * @param callable $callback
     * @return mixed
     */
    public function every(callable $callback);

    /**
     * @return bool
     */
    public function isEmpty(): bool;

    /**
     * @param callable $callback
     * @param bool $returnBeanList
     * @return mixed
     */
    public function some(callable $callback, $returnBeanList = false);

    /**
     * @param callable $callback
     * @return mixed
     */
    public function filter(callable $callback);

    /**
     * @param callable $callback
     * @param bool $returnBean
     * @return mixed
     */
    public function exclusive(callable $callback, $returnBean = false);

    /**
     * @param callable $callback
     * @return mixed
     */
    public function map(callable $callback);

    /**
     * @param callable $callback
     * @return mixed
     */
    public function sort(callable $callback);

    /**
     * @param $key1
     * @param int $order1
     * @param int $flags1
     * @return mixed
     */
    public function sortByData($key1, $order1 = SORT_ASC, $flags1 = SORT_REGULAR);

    /**
     * @param $key
     * @param int $flags
     * @return mixed
     */
    public function sortAscendingByKey($key, $flags = SORT_REGULAR);

    /**
     * @param $key
     * @param int $flags
     * @return mixed
     */
    public function sortDescendingByKey($key, $flags = SORT_REGULAR);

    /**
     * @return mixed
     */
    public function reverse();

    /**
     * @param BeanInterface $bean
     * @return mixed
     */
    public function push(BeanInterface $bean);

    /**
     * @param BeanInterface $bean
     * @return mixed
     */
    public function unshift(BeanInterface $bean);

    /**
     * @return mixed
     */
    public function shift();

    /**
     * @return mixed
     */
    public function pop();

    /**
     * @param string $dataName
     * @return array
     */
    public function countValues_for_DataName(string $dataName): array;

    /**
     * @param array $arrData
     * @param string $beanClass
     * @return mixed
     */
    static public function createFromArray(array $arrData, $beanClass = BeanInterface::class);


}
