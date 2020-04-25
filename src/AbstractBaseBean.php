<?php
declare(strict_types=1);
/**
 * @see       https://github.com/niceshops/nice-beans for the canonical source repository
 * @license   https://github.com/niceshops/nice-beans/blob/master/LICENSE BSD 3-Clause License
 */

namespace NiceshopsDev\Bean;


use ArrayAccess;
use Countable;
use IteratorAggregate;
use NiceshopsDev\Bean\JsonSerializable\JsonSerializableInterface;
use NiceshopsDev\Bean\JsonSerializable\JsonSerializableTrait;

/**
 * Class AbstractBaseBean
 * @package NiceshopsDev\Bean
 */
abstract class AbstractBaseBean implements BeanInterface, ArrayAccess, IteratorAggregate, Countable, JsonSerializableInterface
{
    
    use JsonSerializableTrait;
}