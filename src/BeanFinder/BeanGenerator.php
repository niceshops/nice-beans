<?php


namespace NiceshopsDev\Bean\BeanFinder;


use NiceshopsDev\Bean\BeanInterface;
use NiceshopsDev\Bean\BeanList\BeanListAwareInterface;
use NiceshopsDev\Bean\BeanList\BeanListAwareTrait;
use NiceshopsDev\Bean\BeanList\BeanListInterface;

class BeanGenerator implements \Iterator, BeanListAwareInterface
{
    use BeanListAwareTrait;

    /**
     * @var \Generator
     */
    private $generator;

    /**
     * BeanGenerator constructor.
     * @param callable $generateFunction
     * @param BeanListInterface $emptyBeanList
     */
    public function __construct(callable $generateFunction, BeanListInterface $emptyBeanList)
    {
        $this->beanList = $emptyBeanList;
        $this->generator = $generateFunction();
        $this->generator->rewind();
    }

    /**
     * @return \Generator
     */
    protected function getGenerator(): \Generator
    {
        return $this->generator;
    }

    /**
     * @return BeanInterface
     */
    public function current()
    {
        return $this->getGenerator()->current();
    }

    public function next()
    {
        $this->getGenerator()->next();
    }

    /**
     * @return bool|float|int|string|null
     */
    public function key()
    {
        return $this->getGenerator()->key();
    }

    /**
     * @return bool
     */
    public function valid()
    {
        return $this->getGenerator()->valid();
    }

    public function rewind()
    {
        $this->getGenerator()->rewind();
    }

    /**
     * Convert the BeanGenerator into a BeanList
     *
     * @return BeanListInterface
     */
    public function toBeanList(): BeanListInterface
    {
        if ($this->getBeanList()->count() == 0) {
            foreach ($this->getGenerator() as $bean) {
                foreach ($bean as $key => $item) {
                    if ($item instanceof BeanGenerator) {
                        $bean->setData($key, $item->toBeanList());
                    }
                }
                $this->getBeanList()->push($bean);
            }
        }
        return $this->getBeanList();
    }

}
