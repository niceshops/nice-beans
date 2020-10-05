<?php
declare(strict_types=1);

namespace NiceshopsDev\Bean\BeanFinder;

use NiceshopsDev\Bean\BeanInterface;
use NiceshopsDev\NiceCore\Attribute\AttributeAwareInterface;
use NiceshopsDev\NiceCore\Option\OptionAwareInterface;

/**
 * Interface BeanFinderLoaderInterface
 * @package Niceshops\Library\Core
 */
interface BeanLoaderInterface extends OptionAwareInterface, AttributeAwareInterface
{

    /**
     * @return int
     */
    public function count(): int;

    /**
     * @param int $limit
     * @param int $offset
     * @return mixed
     */
    public function limit(int $limit, int $offset);

    /**
     *
     * @return int
     */
    public function find(): int;

    /**
     * @return bool
     */
    public function fetch(): bool;

    /**
     * @return array
     */
    public function getRow(): array;

    /**
     * @param BeanInterface $bean
     * @return BeanInterface
     */
    public function initializeBeanWithData(BeanInterface $bean): BeanInterface;

}
