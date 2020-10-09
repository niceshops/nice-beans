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
    private $beanFinderLink_List = [];

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
     * @return bool
     */
    public function hasBeanFinderLinkList(): bool
    {
        return is_array($this->beanFinderLink_List) && count($this->beanFinderLink_List) > 0;
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
     * @throws BeanException
     */
    public function find(): int
    {
        $this->checkExecutionAllowed();
        if ($this->hasLimit() && $this->hasOffset()) {
            $this->getLoader()->limit($this->getLimit(), $this->getOffset());
        }
        return $this->getLoader()->find();
    }

    private $beanBuffer = [];

    /**
     * @param string|null $filterField
     * @param array|null $filterValueList
     * @return BeanGenerator
     */
    public function getBeanGenerator(string $filterField = null, array $filterValueList = null): BeanGenerator
    {

        return new BeanGenerator(function () use ($filterField, $filterValueList) {
            if ($this->hasBeanFinderLinkList()) {
                foreach ($this->getBeanFinderLinkList() as $link) {
                    $link->getBeanFinder()->initByValueList($link->getLinkFieldRemote(), $this->getLoader()->preloadValueList($link->getLinkFieldSelf()));
                    if (!$link->getBeanFinder()->hasOption(self::OPTION_EXECUTED)) {
                        $link->getBeanFinder()->find();
                    }
                }
            }

            while ($this->getLoader()->fetch()) {
                $bean = $this->initializeBeanWithAdditionlData($this->getLoader()->initializeBeanWithData($this->getFactory()->createBean()));
                if ($this->hasBeanFinderLinkList()) {
                    foreach ($this->getBeanFinderLinkList() as $link) {
                        $bean->setData($link->getField(), $link->getBeanFinder()->getBeanGenerator($link->getLinkFieldRemote(), [$bean->getData($link->getLinkFieldSelf())]));
                    }
                }
                if (null !== $filterField && null !== $filterValueList) {
                    $this->beanBuffer[] = $bean;
                    if ($bean->hasData($filterField) && in_array($bean->getData($filterField), $filterValueList)) {
                        yield $bean;
                    }
                } else {
                    yield $bean;
                }
            }

            foreach ($this->beanBuffer as $bean) {
                if (null !== $filterField && null !== $filterValueList) {
                    if ($bean->hasData($filterField) && in_array($bean->getData($filterField), $filterValueList)) {
                        yield $bean;
                    }
                }
            }

        }, $this->getFactory()->createBeanList());
    }


    /**
     * @param bool $fetchAllData
     * @return BeanListInterface
     */
    public function getBeanList(bool $fetchAllData = false): BeanListInterface
    {
        return $this->getBeanGenerator()->toBeanList($fetchAllData);
    }

    /**
     * @param bool $fetchAllData
     * @return BeanInterface
     * @throws BeanException
     */
    public function getBean(bool $fetchAllData = false): BeanInterface
    {
        $beanList = $this->getBeanList($fetchAllData);
        $count = $beanList->count();
        if ($count !== 1) {
            throw new BeanException('Could not get single bean, bean list contains ' . $count . ' beans.');
        }
        return $beanList->offsetGet(0);
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
