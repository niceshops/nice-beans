<?php
declare(strict_types=1);
/**
 * @see       https://github.com/niceshops/nice-beans for the canonical source repository
 * @license   https://github.com/niceshops/nice-beans/blob/master/LICENSE BSD 3-Clause License
 */

namespace NiceshopsDev\Bean\JsonSerializable;

use JsonSerializable;

/**
 * Interface JsonSerializableInterface
 * @package Bean
 */
interface JsonSerializableBeanInterface extends JsonSerializable
{


    /**
     * @param bool $dataOnly
     *
     * @return string
     */
    public function toJson($dataOnly = false) : string;


    /**
     * @param string $json
     */
    public function fromJson(string $json);


}
