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
     * @return BeanLoaderInterface
     */
    protected function getLoader(): BeanLoaderInterface
    {
        return $this->loader;
    }

    /**
     * @return BeanFactoryInterface
     */
    protected function getFactory(): BeanFactoryInterface
    {
        return $this->factory;
    }

    /**
     * @return BeanListInterface
     * @throws BeanException
     */
    public function getBeanList(): BeanListInterface
    {
        if (null !== $this->beanList) {
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
            throw new BeanException('Could not get single bean, beanlsit containes ' . $count . ' beans.');
        }
        return $beanList->offsetGet(0);
    }

    /**
     * @param BeanInterface $bean
     * @param array $arrData
     *
     * @return BeanInterface
     */
    abstract public function initializeBeanWithData(BeanInterface $bean, array $arrData): BeanInterface;


    /**
     * @throws BeanException
     */
    public function find(): int
    {
        $this->checkExecutionAllowed();
        $foundRows = $this->getLoader()->find();
        while ($this->getLoader()->fetch()) {
            $this->getBeanList()->push($this->initializeBeanWithData($this->getFactory()->createBean(), $this->getLoader()->getRow()));
        }
        return $foundRows;
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return $this->getLoader()->count();
    }

    /**
     * @throws BeanException
     */
    private function checkExecutionAllowed()
    {
        if ($this->hasOption(self::OPTION_EXECUTED)) {
            throw new BeanException("Dataobject executed twice!");
        } else {
            $this->addOption(self::OPTION_EXECUTED);
        }
    }
}
