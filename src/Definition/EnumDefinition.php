<?php
/**
 * This file is part of Railt package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Railt\Reflection\Definition;

use Railt\Reflection\Contracts\Definition\EnumDefinition as EnumDefinitionInterface;
use Railt\Reflection\Contracts\TypeInterface as TypeInterface;
use Railt\Reflection\Definition\Behaviour\HasEnumValues;
use Railt\Reflection\Document;
use Railt\Reflection\Type;

/**
 * Class EnumDefinition
 */
class EnumDefinition extends ScalarDefinition implements EnumDefinitionInterface
{
    use HasEnumValues;

    /**
     * @return TypeInterface
     */
    public static function getType(): TypeInterface
    {
        return Type::of(Type::ENUM);
    }
}
