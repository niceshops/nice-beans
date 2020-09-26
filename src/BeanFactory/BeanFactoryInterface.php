<?php
declare(strict_types=1);

namespace NiceshopsDev\Bean\BeanFactory;

use NiceshopsDev\Bean\BeanInterface;
use NiceshopsDev\Bean\BeanList\BeanListInterface;
use NiceshopsDev\NiceCore\Attribute\AttributeAwareInterface;
use NiceshopsDev\NiceCore\Option\OptionAwareInterface;

/**
 * Interface BeanFactoryInterface
 * @package NiceshopsDev\Bean\BeanFactory
 */
interface BeanFactoryInterface extends OptionAwareInterface, AttributeAwareInterface
{
    /**
     * @return BeanInterface
     */
    public function createBean(): BeanInterface;

    /**
     * @return BeanListInterface
     */
    public function createBeanList(): BeanListInterface;
}
