<?php
declare(strict_types=1);

namespace NiceshopsDev\Bean\BeanFinder;

use NiceshopsDev\Bean\BeanFactory\BeanFactoryInterface;
use NiceshopsDev\Bean\BeanInterface;
use NiceshopsDev\Bean\BeanList\BeanListInterface;
use NiceshopsDev\NiceCore\Attribute\AttributeAwareInterface;
use NiceshopsDev\NiceCore\Option\OptionAwareInterface;

/**
 * Interface BeanFinderInterface
 * @package Niceshops\Library\Core
 */
interface BeanFinderInterface extends OptionAwareInterface, AttributeAwareInterface
{
    /**
     * @return int
     */
    public function find(): int;

    /**
     * @return int
     */
    public function count(): int;

    /**
     * @param int $limit
     * @param int $offset
     * @return $this
     */
    public function limit(int $limit, int $offset);

    /**
     * @param string $field
     * @param array $list
     * @return $this
     */
    public function initByValueList(string $field, array $list);


    /**
     * @return BeanListInterface
     */
    public function getBeanList(): BeanListInterface;

    /**
     * @return BeanInterface
     */
    public function getBean(): BeanInterface;


    /**
     * @return BeanLoaderInterface
     */
    public function getLoader(): BeanLoaderInterface;

    /**
     * @return BeanFactoryInterface
     */
    public function getFactory(): BeanFactoryInterface;

    /**
     * @return int
     */
    public function getLimit(): int;

    /**
     * @return bool
     */
    public function hasLimit(): bool;

    /**
     * @return int
     */
    public function getOffset(): int;

    /**
     * @return bool
     */
    public function hasOffset(): bool;

}
