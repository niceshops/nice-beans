<?php
declare(strict_types=1);
/**
 * @see       https://github.com/niceshops/nice-beans for the canonical source repository
 * @license   https://github.com/niceshops/nice-beans/blob/master/LICENSE BSD 3-Clause License
 */

namespace NiceshopsDev\Bean\BeanList;

use NiceshopsDev\Bean\BeanInterface;

interface BeanListInterface extends BeanInterface
{
    public function addBean(BeanInterface $bean);
    public function addBeans($beans);
    public function removeBean(BeanInterface $bean);
    public function hasBean(BeanInterface $bean);
    public function indexOfBean(BeanInterface $bean);
    public function getBeans();
    public function setBeans($beans);
    public function resetBeans();
    public function slice($offset = 0, $length = null, $stepWidth = 1);
    public function each(callable $callback);
    public function every(callable $callback);
    public function isEmpty(): bool;
    public function some(callable $callback, $returnBeanList = false);
    public function filter(callable $callback);
    public function exclusive(callable $callback, $returnBean = false);
    public function map(callable $callback);
    public function sort(callable $callback);
    public function sortByData($key1, $order1 = SORT_ASC, $flags1 = SORT_REGULAR);
    public function sortAscendingByKey($key, $flags = SORT_REGULAR);
    public function sortDescendingByKey($key, $flags = SORT_REGULAR);
    public function reverse();
    public function push(BeanInterface $bean);
    public function unshift(BeanInterface $bean);
    public function shift();
    public function pop();
    public function countValues_for_DataName(string $dataName): array;

    static public function createFromArray(array $arrData, $beanClass = BeanInterface::class);


}
