<?php


namespace NiceshopsDev\Bean\BeanFinder;


use NiceshopsDev\Bean\BeanInterface;
use NiceshopsDev\Bean\BeanList\BeanListAwareInterface;
use NiceshopsDev\Bean\BeanList\BeanListAwareTrait;
use NiceshopsDev\Bean\BeanList\BeanListException;
use NiceshopsDev\Bean\BeanList\BeanListInterface;

class BeanGenerator implements BeanListInterface, BeanListAwareInterface
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
    }

    /**
     * @return \Generator
     */
    protected function getGenerator(): \Generator
    {
        return $this->generator;
    }


    /**
     * Convert the BeanGenerator into a BeanList
     *
     * @param bool $recursive
     * @return BeanListInterface
     */
    public function toBeanList(bool $recursive = false): BeanListInterface
    {
        if ($this->getBeanList()->count() == 0 && $this->getGenerator()->valid()) {
            foreach ($this->getGenerator() as $bean) {
                if ($recursive) {
                    foreach ($bean as $key => $item) {
                        if ($item instanceof BeanGenerator) {
                            $bean->setData($key, $item->toBeanList($recursive));
                        }
                    }
                }
                $this->getBeanList()->push($bean);
            }
        }
        return $this->getBeanList();
    }

    public function setData($name, $value)
    {
        return $this->toBeanList()->setData($name, $value);
    }

    public function getData($name)
    {
        return $this->toBeanList()->getData($name);
    }

    public function hasData($name)
    {
        return $this->toBeanList()->hasData($name);
    }

    public function removeData($name)
    {
        return $this->toBeanList()->removeData($name);
    }

    public function resetData()
    {
        return $this->toBeanList()->resetData();
    }

    public function getDataType($name)
    {
        return $this->toBeanList()->getDataType($name);
    }

    public function toArray()
    {
        return $this->toBeanList()->toArray();
    }

    public function setFromArray(array $data)
    {
        return $this->toBeanList()->setFromArray($data);
    }

    public function addBean(BeanInterface $bean)
    {
        return $this->toBeanList()->addBean($bean);
    }

    public function addBeans($beans)
    {
        return $this->toBeanList()->addBeans($beans);
    }

    public function removeBean(BeanInterface $bean)
    {
        return $this->toBeanList()->removeBean($bean);
    }

    public function hasBean(BeanInterface $bean)
    {
        return $this->toBeanList()->hasBean($bean);
    }

    public function indexOfBean(BeanInterface $bean)
    {
        return $this->toBeanList()->indexOfBean($bean);
    }

    public function getBeans(): array
    {
        return $this->toBeanList()->getBeans();
    }

    public function setBeans($beans)
    {
        return $this->toBeanList()->setBeans($beans);
    }

    public function resetBeans()
    {
        return $this->toBeanList()->resetBeans();
    }

    public function slice($offset = 0, $length = null, $stepWidth = 1)
    {
        return $this->toBeanList()->slice($offset, $length, $stepWidth);
    }

    public function each(callable $callback)
    {
        return $this->toBeanList()->each($callback);
    }

    public function every(callable $callback)
    {
        return $this->toBeanList()->every($callback);
    }

    public function isEmpty(): bool
    {
        return $this->toBeanList()->isEmpty();
    }

    public function some(callable $callback, $returnBeanList = false)
    {
        return $this->toBeanList()->some($callback, $returnBeanList);
    }

    public function filter(callable $callback)
    {
        return $this->toBeanList()->filter($callback);
    }

    public function exclusive(callable $callback, $returnBean = false)
    {
        return $this->toBeanList()->exclusive($callback, $returnBean);
    }

    public function map(callable $callback)
    {
        return $this->toBeanList()->map($callback);
    }

    public function sort(callable $callback)
    {
        return $this->toBeanList()->sort($callback);
    }

    public function sortByData($key1, $order1 = SORT_ASC, $flags1 = SORT_REGULAR)
    {
        return $this->toBeanList()->sortByData($key1, $order1, $flags1);
    }

    public function sortAscendingByKey($key, $flags = SORT_REGULAR)
    {
        return $this->toBeanList()->sortAscendingByKey($key, $flags);
    }

    public function sortDescendingByKey($key, $flags = SORT_REGULAR)
    {
        return $this->toBeanList()->sortDescendingByKey($key, $flags);
    }

    public function reverse()
    {
        return $this->toBeanList()->reverse();
    }

    public function push(BeanInterface $bean)
    {
        return $this->toBeanList()->push($bean);
    }

    public function unshift(BeanInterface $bean)
    {
        return $this->toBeanList()->unshift($bean);
    }

    public function shift()
    {
        return $this->toBeanList()->shift();
    }

    public function pop()
    {
        return $this->toBeanList()->pop();
    }

    public function countValues_for_DataName(string $dataName): array
    {
        return $this->toBeanList()->pop();
    }

    static public function createFromArray(array $arrData, $beanClass = BeanInterface::class) {
      throw new BeanListException('Unsuppoerted in BeanGenerator: ' . __METHOD__);
    }

    /**
     * @return $this|\Traversable
     */
    public function getIterator()
    {
        return $this->getGenerator();
    }

    public function offsetExists($offset)
    {
        return $this->toBeanList()->offsetExists($offset);
    }

    public function offsetGet($offset)
    {
        return $this->toBeanList()->offsetGet($offset);
    }

    public function offsetSet($offset, $value)
    {
        $this->toBeanList()->offsetSet($offset, $value);
    }

    public function offsetUnset($offset)
    {
         $this->toBeanList()->offsetUnset($offset);
    }

    public function count()
    {
        return $this->toBeanList()->count();
    }


}
