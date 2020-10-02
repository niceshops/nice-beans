<?php


namespace NiceshopsDev\Bean\BeanParser;



use NiceshopsDev\Bean\BeanInterface;

interface LazyBeanParserInterface
{
    /**
     * @param bool $returnNew
     *
     * @return BeanInterface
     */
    public function toBean(bool $returnNew = false): BeanInterface;

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function getValue(string $name);
}
