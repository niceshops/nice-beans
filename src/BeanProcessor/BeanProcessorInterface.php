<?php
declare(strict_types=1);

namespace NiceshopsDev\Bean\BeanProcessor;

use NiceshopsDev\Bean\BeanList\BeanListAwareInterface;
use NiceshopsDev\NiceCore\Attribute\AttributeAwareInterface;
use NiceshopsDev\NiceCore\Option\OptionAwareInterface;

/**
 * Interface ProcessorInterface
 * @package Niceshops\Library\Core
 */
interface BeanProcessorInterface extends BeanListAwareInterface, OptionAwareInterface, AttributeAwareInterface
{

    /**
     * @return int
     */
    public function save(): int;

    public function delete(): int;


}
