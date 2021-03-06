<?php
declare(strict_types=1);
/**
 * @see       https://github.com/niceshops/nice-beans for the canonical source repository
 * @license   https://github.com/niceshops/nice-beans/blob/master/LICENSE BSD 3-Clause License
 */

namespace NiceshopsDev\Bean;

use NiceshopsDev\NiceCore\Exception;

/**
 * Class BeanException
 * @package NiceshopsDev\Bean
 */
class BeanException extends Exception
{

    
    const ERROR_CODE_DATA_NOT_FOUND = 1000;
    const ERROR_CODE_INVALID_DATA_VALUE = 1001;
    const ERROR_CODE_INVALID_DATA_TYPE = 1002;
    const ERROR_CODE_DATA_IS_NOT_NULLABLE = 1003;
}