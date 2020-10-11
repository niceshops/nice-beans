<?php


namespace NiceshopsDev\Bean\BeanFinder;


abstract class AbstractBeanLoader implements BeanLoaderInterface
{
    public function current()
    {
        return $this->data();
    }

    public function next()
    {
        $this->fetch();
    }

    public function valid()
    {
        return $this->fetch();
    }


}
