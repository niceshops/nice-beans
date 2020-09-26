<?php
declare(strict_types=1);

namespace NiceshopsDev\Bean\BeanFinder;

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
     * @return BeanListInterface
     */
    public function getBeanList(): BeanListInterface;

    /**
     * @return BeanInterface
     */
    public function getBean(): BeanInterface;
}
