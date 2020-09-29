<?php
declare(strict_types=1);

namespace NiceshopsDev\Bean\BeanProcessor;

use NiceshopsDev\Bean\BeanList\BeanListAwareInterface;
use NiceshopsDev\NiceCore\Attribute\AttributeAwareInterface;
use NiceshopsDev\NiceCore\Option\OptionAwareInterface;


/**
 * Interface BeanSaverInterface
 * @package Niceshops\Library\Core
 */
interface BeanSaverInterface extends BeanListAwareInterface, OptionAwareInterface, AttributeAwareInterface
{
    /**
     * @return int
     */
    public function save(): int;

    /**
     * @return int
     */
    public function delete(): int;


}
