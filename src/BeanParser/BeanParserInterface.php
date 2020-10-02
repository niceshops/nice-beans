<?php
namespace NiceshopsDev\Bean\BeanParser;


use NiceshopsDev\Bean\BeanInterface;

interface BeanParserInterface
{

    public function parse(array $data_Map, BeanInterface $bean): LazyBeanParserInterface;
}
