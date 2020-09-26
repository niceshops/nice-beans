<?php
declare(strict_types=1);
/**
 * @see       https://github.com/niceshops/nice-beans for the canonical source repository
 * @license   https://github.com/niceshops/nice-beans/blob/master/LICENSE BSD 3-Clause License
 */

namespace NiceshopsDev\Bean\BeanList;

use NiceshopsDev\NiceCore\Exception;

/**
 * Class BeanException
 * @package NiceshopsDev\Bean
 */
class BeanListException extends Exception
{
    const ERROR_CODE_DATA_NOT_FOUND = 1000;
}
