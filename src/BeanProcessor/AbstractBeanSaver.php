<?php
declare(strict_types=1);

namespace NiceshopsDev\Bean\BeanProcessor;

use NiceshopsDev\Bean\BeanException;
use NiceshopsDev\Bean\BeanInterface;
use NiceshopsDev\Bean\BeanList\BeanListAwareTrait;
use NiceshopsDev\Bean\BeanList\BeanListInterface;
use NiceshopsDev\NiceCore\Attribute\AttributeTrait;
use NiceshopsDev\NiceCore\Option\OptionTrait;
use Throwable;

/**
 * Class AbstractBeanSaver
 * @package Niceshops\Library\Core
 */
abstract class AbstractBeanSaver implements BeanSaverInterface
{
    use BeanListAwareTrait;
    use OptionTrait;
    use AttributeTrait;

    /**
     * @return int number of successfully saved beans
     * @throws BeanException
     */
    public function save(): int
    {
        if (!$this->hasBeanList()) {
            throw new BeanException('No bean list set in bean saver.');
        }
        $affectdRows = 0;
        try {
            $affectdRows = $this->saveBeanList($this->getBeanList());
        } catch (Throwable $error) {
            $this->onError($error);
        }
        return $affectdRows;
    }

    /**
     * @return int number of successfully saved beans
     * @throws BeanException
     */
    public function delete(): int
    {
        if (!$this->hasBeanList()) {
            throw new BeanException('No bean list set in bean saver.');
        }
        $affectdRows = 0;
        try {
            $affectdRows = $this->deleteBeanList($this->getBeanList());
        } catch (Throwable $error) {
            $this->onError($error);
        }
        return $affectdRows;
    }

    /**
     * @param BeanListInterface $beanList
     *
     * @return int number of successfully saved beans
     */
    protected function saveBeanList(BeanListInterface $beanList): int
    {
        $affectdRows = 0;
        foreach ($beanList as $bean) {
            if ($this->saveBean($bean)) {
                $affectdRows++;
            }
        }
        return $affectdRows;
    }

    /**
     * @param BeanListInterface $beanList
     *
     * @return int number of successfully saved beans
     */
    protected function deleteBeanList(BeanListInterface $beanList): int
    {
        $affectdRows = 0;
        foreach ($beanList as $bean) {
            if ($this->deleteBean($bean)) {
                $affectdRows++;
            }
        }
        return $affectdRows;
    }

    /**
     * @param BeanInterface $bean
     *
     * @return bool true on success
     */
    abstract protected function saveBean(BeanInterface $bean): bool;

    /**
     * @param BeanInterface $bean
     *
     * @return bool true on success
     */
    abstract protected function deleteBean(BeanInterface $bean): bool;

    /**
     * @param Throwable $error
     *
     * @throws BeanException
     */
    protected function onError(Throwable $error)
    {
        throw new BeanException($error->getMessage(), $error->getCode(), $error);
    }

}
