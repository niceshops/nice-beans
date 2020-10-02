<?php


namespace NiceshopsDev\Bean\BeanFormatter;


trait BeanFormatterAwareTrait
{
    /**
     * @var BeanFormatterInterface
     */
    private $beanFormatter;


    /**
    * @return BeanFormatterInterface
    */
    public function getBeanFormatter(): BeanFormatterInterface
    {
        return $this->beanFormatter;
    }

    /**
    * @param BeanFormatterInterface $beanFormatter
    *
    * @return $this
    */
    public function setBeanFormatter(BeanFormatterInterface $beanFormatter)
    {
        $this->beanFormatter = $beanFormatter;
        return $this;
    }

    /**
    * @return bool
    */
    public function hasBeanFormatter(): bool
    {
        return $this->beanFormatter !== null;
    }

}
