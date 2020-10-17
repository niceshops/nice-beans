<?php
declare(strict_types=1);

namespace NiceshopsDev\Bean\BeanProcessor;


use Countable;
use NiceshopsDev\Bean\BeanInterface;
use NiceshopsDev\Bean\BeanList\BeanListAwareTrait;
use NiceshopsDev\Bean\BeanList\BeanListInterface;
use NiceshopsDev\NiceCore\Attribute\AttributeTrait;
use NiceshopsDev\NiceCore\Option\OptionTrait;

/**
 * Class AbstractBeanProcessor
 * @package NiceshopsDev\Bean\BeanProcessor
 */
abstract class AbstractBeanProcessor implements BeanProcessorInterface
{
    use BeanListAwareTrait;
    use OptionTrait;
    use AttributeTrait;

    /**
     *
     */
    const OPTION_SAVE_NON_EMPTY_ONLY = "non_empty_only";
    const OPTION_IGNORE_VALIDATION = "ignore_validation";

    /**
     * @var BeanSaverInterface
     */
    private $saver;

    /**
     * AbstractBeanProcessor constructor.
     *
     * @param BeanSaverInterface $saver
     */
    public function __construct(BeanSaverInterface $saver)
    {
        $this->saver = $saver;
    }

    /**
     * @return BeanSaverInterface
     */
    public function getSaver(): BeanSaverInterface
    {
        return $this->saver;
    }

    /**
     * Returns the processed bean list.
     */
    public function save(): int
    {
        $beanList = $this->getBeanListForSave();
        foreach ($beanList as $bean) {
            $this->beforeSave($bean);
        }
        $this->getSaver()->setBeanList($beanList);
        $result = $this->getSaver()->save();
        foreach ($beanList as $bean) {
            $this->afterSave($bean);
        }
        return $result;
    }


    /**
     * Returns the processed bean list.
     */
    public function delete(): int
    {
        $this->getSaver()->setBeanList($this->getBeanListForDelete());
        return $this->getSaver()->delete();
    }

    protected function beforeSave(BeanInterface $bean)
    {

    }

    protected function afterSave(BeanInterface $bean) {

    }

    /**
     * Returns a filtered copy of the source bean list.
     * All saving operations are applied on this filtered copy.
     *
     */
    protected function getBeanListForSave(): BeanListInterface
    {
        return (clone $this->getBeanList())->filter(function (BeanInterface $bean) {
            return $this->isBeanAllowedToSave($bean);
        });
    }

    /**
     * @param BeanInterface $bean
     *
     * @return bool
     */
    protected function isBeanAllowedToSave(BeanInterface $bean): bool
    {
        if ($this->hasOption(self::OPTION_SAVE_NON_EMPTY_ONLY) && $bean instanceof Countable &&  $bean->count() == 0) {
            return false;
        }
        if ($this->hasOption(self::OPTION_IGNORE_VALIDATION)) {
            return true;
        }
        return $this->validateForSave($bean);
    }


    /**
     * Returns a filtered copy of the source bean list.
     * All saving operations are applied on this filtered copy.
     *
     */
    protected function getBeanListForDelete(): BeanListInterface
    {
        return (clone $this->getBeanList())->filter(function (BeanInterface $bean) {
            return $this->isBeanAllowedToDelete($bean);
        });
    }

    /**
     * @param BeanInterface $bean
     *
     * @return bool
     */
    protected function isBeanAllowedToDelete(BeanInterface $bean): bool
    {
        if ($this->hasOption(self::OPTION_IGNORE_VALIDATION)) {
            return true;
        }
        return $this->validateForDelete($bean);
    }

    /**
     * @param BeanInterface $bean
     * @return bool
     */
    protected function validateForSave(BeanInterface $bean): bool
    {
        return true;
    }

    /**
     * @param BeanInterface $bean
     * @return bool
     */
    protected function validateForDelete(BeanInterface $bean): bool
    {
        return true;
    }
}
