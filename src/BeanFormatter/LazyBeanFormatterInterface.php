<?php


namespace NiceshopsDev\Bean\BeanFormatter;


interface LazyBeanFormatterInterface
{
    /**
     * @param string $name
     *
     * @return mixed
     */
    public function getValue(string $name);

    /**
     * @return array
     */
    public function toArray(): array;
}
