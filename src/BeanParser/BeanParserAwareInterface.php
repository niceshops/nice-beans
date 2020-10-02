<?php


namespace NiceshopsDev\Bean\BeanParser;


interface BeanParserAwareInterface
{
    /**
     * @return BeanParserInterface
     */
    public function getBeanParser(): BeanParserInterface;
    /**
     * @param BeanParserInterface $beanParser
     *
     * @return $this
     */
    public function setBeanParser(BeanParserInterface $beanParser);

    /**
     * @return bool
     */
    public function hasBeanParser(): bool;
}
