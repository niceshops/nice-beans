<?php


namespace NiceshopsDev\Bean\BeanFormatter;


interface BeanFormatterAwareInterface
{
    /**
     * @return BeanFormatterInterface
     */
    public function getBeanFormatter(): BeanFormatterInterface;
    /**
     * @param BeanFormatterInterface $beanFormatter
     *
     * @return $this
     */
    public function setBeanFormatter(BeanFormatterInterface $beanFormatter);

    /**
     * @return bool
     */
    public function hasBeanFormatter(): bool;
}
