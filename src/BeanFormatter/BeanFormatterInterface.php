<?php
namespace NiceshopsDev\Bean\BeanFormatter;




use NiceshopsDev\Bean\BeanInterface;

interface BeanFormatterInterface
{
    /**
     * @return array
     */
    public function format(BeanInterface $bean): LazyBeanFormatterInterface;

}
