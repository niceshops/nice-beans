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
    protected function getSaver(): BeanSaverInterface
    {
        return $this->saver;
    }

    /**
     * Returns the processed bean list.
     */
    public function save(): int
    {
        $this->getSaver()->setBeanList($this->getBeanListForSave());
        return $this->getSaver()->save();
    }


    /**
     * Returns the processed bean list.
     */
    public function delete(): int
    {
        $this->getSaver()->setBeanList($this->getBeanListForDelete());
        return $this->getSaver()->delete();
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
     * @param BeanInterface $bean
     * @return bool
     */
    abstract protected function validateForSave(BeanInterface $bean): bool;


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
    abstract protected function validateForDelete(BeanInterface $bean): bool;
}
