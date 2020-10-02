<?php


namespace NiceshopsDev\Bean\BeanParser;


trait BeanParserAwareTrait
{
    /**
     * @var BeanParserInterface
     */
    private $beanParser;


    /**
    * @return BeanParserInterface
    */
    public function getBeanParser(): BeanParserInterface
    {
        return $this->beanParser;
    }

    /**
    * @param BeanParserInterface $beanParser
    *
    * @return $this
    */
    public function setBeanParser(BeanParserInterface $beanParser)
    {
        $this->beanParser = $beanParser;
        return $this;
    }

    /**
    * @return bool
    */
    public function hasBeanParser(): bool
    {
        return $this->beanParser !== null;
    }

}
