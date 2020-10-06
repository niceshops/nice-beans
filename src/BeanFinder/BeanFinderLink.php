<?php


namespace NiceshopsDev\Bean\BeanFinder;


class BeanFinderLink
{
    /**
     * @var BeanFinderInterface
     */
    private $beanFinder;

    /**
     * @var string
     */
    private $field;

    /**
     * @var string
     */
    private $linkFieldSelf;

    /**
     * @var string
     */
    private $linkFieldRemote;

    /**
     * BeanFinderLink constructor.
     * @param BeanFinderInterface $beanFinder
     * @param string $field
     * @param string $linkFieldSelf
     * @param string $linkFieldRemote
     */
    public function __construct(BeanFinderInterface $beanFinder, string $field, string $linkFieldSelf, string $linkFieldRemote)
    {
        $this->beanFinder = $beanFinder;
        $this->field = $field;
        $this->linkFieldSelf = $linkFieldSelf;
        $this->linkFieldRemote = $linkFieldRemote;
    }

    /**
     * @return BeanFinderInterface
     */
    public function getBeanFinder(): BeanFinderInterface
    {
        return $this->beanFinder;
    }

    /**
     * @return string
     */
    public function getField(): string
    {
        return $this->field;
    }

    /**
     * @return string
     */
    public function getLinkFieldSelf(): string
    {
        return $this->linkFieldSelf;
    }

    /**
     * @return string
     */
    public function getLinkFieldRemote(): string
    {
        return $this->linkFieldRemote;
    }


}
