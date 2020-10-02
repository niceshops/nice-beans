<?php


namespace NiceshopsDev\Bean\BeanFactory;


use NiceshopsDev\NiceCore\Attribute\AttributeTrait;
use NiceshopsDev\NiceCore\Option\OptionTrait;

abstract class AbstractBeanFactory implements BeanFactoryInterface
{
    use OptionTrait;
    use AttributeTrait;

}
