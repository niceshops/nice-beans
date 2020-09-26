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
    public function process(): int
    {
        $this->getSaver()->setBeanList($this->getBeanListForProcess());
        return $this->getSaver()->save();
    }

    /**
     * Returns a filtered copy of the source bean list.
     * All saving operations are applied on this filtered copy.
     *
     */
    protected function getBeanListForProcess(): BeanListInterface
    {
        return (clone $this->getBeanList())->filter(function (BeanInterface $bean) {
            return $this->isBeanAllowedToProcess($bean);
        });
    }

    /**
     * @param BeanInterface $bean
     *
     * @return bool
     */
    protected function isBeanAllowedToProcess(BeanInterface $bean): bool
    {
        $isSaveAllowed = false;
        if ($this->hasOption(self::OPTION_SAVE_NON_EMPTY_ONLY) && $bean instanceof Countable) {
            $isSaveAllowed = $isSaveAllowed && $bean->count() > 0;
        }
        return $isSaveAllowed;
    }
}
