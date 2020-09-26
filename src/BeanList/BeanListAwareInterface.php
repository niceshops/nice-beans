<?php
declare(strict_types=1);

namespace NiceshopsDev\Bean\BeanList;


/**
 * Interface BeanListAwareInterface
 * @package NiceshopsDev\Bean\BeanList
 */
interface BeanListAwareInterface
{

    /**
     * @param BeanListInterface $beanList
     * @return $this
     */
    public function setBeanList(BeanListInterface $beanList);

}
