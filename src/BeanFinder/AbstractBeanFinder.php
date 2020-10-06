<?php
declare(strict_types=1);

namespace NiceshopsDev\Bean\BeanFinder;


use NiceshopsDev\Bean\BeanException;
use NiceshopsDev\Bean\BeanFactory\BeanFactoryInterface;
use NiceshopsDev\Bean\BeanInterface;
use NiceshopsDev\Bean\BeanList\BeanListInterface;
use NiceshopsDev\NiceCore\Attribute\AttributeTrait;
use NiceshopsDev\NiceCore\Option\OptionTrait;

/**
 * Class AbstractBeanFinderFactory
 * @package Niceshops\Library\Core
 */
abstract class AbstractBeanFinder implements BeanFinderInterface
{
    use OptionTrait;
    use AttributeTrait;

    protected const OPTION_EXECUTED = 'executed';

    /**
     * @var BeanListInterface
     */
    protected $beanList;

    /**
     * @var BeanLoaderInterface
     */
    private $loader;

    /**
     * @var BeanFactoryInterface
     */
    private $factory;

    /**
     * @var int
     */
    private $limit;

    /**
     * @var int
     */
    private $offset;

    /**
     * @var BeanFinderLink[]
     */
    private $beanFinderLink_List;

    /**
     * AbstractBeanFinderFactory constructor.
     *
     * @param BeanLoaderInterface $loader
     * @param BeanFactoryInterface $factory
     */
    public function __construct(BeanLoaderInterface $loader, BeanFactoryInterface $factory)
    {
        $this->loader = $loader;
        $this->factory = $factory;
    }

    /**
     * @param BeanFinderInterface $beanFinder
     * @param string $field
     * @param string $linkFieldSelf
     * @param string $linkFieldRemote
     * @return $this|BeanFinderInterface
     */
    public function linkBeanFinder(BeanFinderInterface $beanFinder, string $field, string $linkFieldSelf, string $linkFieldRemote): BeanFinderInterface
    {
        $this->beanFinderLink_List[] = new BeanFinderLink($beanFinder, $field, $linkFieldSelf, $linkFieldRemote);
        return $this;
    }

    /**
     * @return BeanFinderLink[]
     */
    public function getBeanFinderLinkList(): array
    {
        return $this->beanFinderLink_List;
    }

    /**
     * @return BeanLoaderInterface
     */
    public function getLoader(): BeanLoaderInterface
    {
        return $this->loader;
    }

    /**
     * @return BeanFactoryInterface
     */
    public function getFactory(): BeanFactoryInterface
    {
        return $this->factory;
    }

    /**
     * @return BeanListInterface
     * @throws BeanException
     */
    public function getBeanList(): BeanListInterface
    {

        if (null === $this->beanList) {
            throw new BeanException('BeanList not initialized, run find() first.');
        }
        return $this->beanList;
    }

    /**
     * @return BeanInterface
     * @throws BeanException
     */
    public function getBean(): BeanInterface
    {
        $beanList = $this->getBeanList();
        $count = $beanList->count();
        if ($count !== 1) {
            throw new BeanException('Could not get single bean, bean list contains ' . $count . ' beans.');
        }
        return $beanList->offsetGet(0);
    }

    /**
     * @throws BeanException
     */
    public function find(): int
    {
        $this->checkExecutionAllowed();
        if ($this->hasLimit() && $this->hasOffset()) {
            $this->getLoader()->limit($this->getLimit(), $this->getOffset());
        }
        $foundRows = $this->getLoader()->find();
        $this->beanList = $this->getFactory()->createBeanList();
        while ($this->getLoader()->fetch()) {
            $this->getBeanList()->push(
                $this->initializeBeanWithAdditionlData($this->getLoader()->initializeBeanWithData($this->getFactory()->createBean()))
            );
        }
        foreach ($this->getBeanFinderLinkList() as $link) {
            $this->handleLinkedFinder($link->getBeanFinder(), $link->getField(), $link->getLinkFieldSelf(), $link->getLinkFieldRemote());
        }
        return $foundRows;
    }

    /**
     * @param BeanFinderInterface $finder
     * @param string $field
     * @param string $linkFieldSelf
     * @param string $linkFieldRemote
     * @throws BeanException
     */
    protected function handleLinkedFinder(BeanFinderInterface $finder, string $field, string $linkFieldSelf, string $linkFieldRemote)
    {
        $idList = $this->getBeanList()->getData($linkFieldSelf);
        $finder->initByValueList($linkFieldRemote, $idList);
        $finder->find();
        foreach ($this->getBeanList() as $parentBean) {
            $parentBean->setData($field, $finder->getBeanList()->filter(
                function (BeanInterface $childBean) use ($parentBean, $linkFieldSelf, $linkFieldRemote) {
                    return $parentBean->getData($linkFieldSelf) == $childBean->getData($linkFieldRemote);
                })
            );
        }
    }

    /**
     * @param string $field
     * @param array $valueList
     * @return $this
     */
    public function initByValueList(string $field, array $valueList)
    {
        $this->getLoader()->initByValueList($field, $valueList);
        return $this;
    }

    /**
     * @param BeanInterface $bean
     * @return BeanInterface
     */
    protected function initializeBeanWithAdditionlData(BeanInterface $bean): BeanInterface
    {
        return $bean;
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return $this->getLoader()->count();
    }

    /**
     * @param int $limit
     * @param int $offset
     * @return $this
     */
    public function limit(int $limit, int $offset)
    {
        $this->limit = $limit;
        $this->offset = $offset;
        return $this;
    }

    /**
     * @return int
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * @return bool
     */
    public function hasLimit(): bool
    {
        return $this->limit !== null;
    }

    /**
     * @return int
     */
    public function getOffset(): int
    {
        return $this->offset;
    }


    /**
     * @return bool
     */
    public function hasOffset(): bool
    {
        return $this->offset !== null;
    }

    /**
     * @throws BeanException
     */
    private function checkExecutionAllowed()
    {
        if ($this->hasOption(self::OPTION_EXECUTED)) {
            throw new BeanException("Finder executed twice!");
        } else {
            $this->addOption(self::OPTION_EXECUTED);
        }
    }
}
