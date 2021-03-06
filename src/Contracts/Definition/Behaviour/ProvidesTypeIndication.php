<?php
/**
 * This file is part of Railt package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Railt\Reflection\Contracts\Definition\Behaviour;

use Railt\Reflection\Contracts\Definition\TypeDefinition;

/**
 * An interface that says that the parent type is the type
 * for which the explicit type is defined.
 */
interface ProvidesTypeIndication
{
    /**
     * @var int
     */
    public const IS_LIST = 0b0001;

    /**
     * @var int
     */
    public const IS_NOT_NULL = 0b0010;

    /**
     * @var int
     */
    public const IS_LIST_OF_NOT_NULL = 0b0100;

    /**
     * Reference to type definition.
     *
     * @return TypeDefinition
     */
    public function getDefinition(): TypeDefinition;

    /**
     * Returns a Boolean value that indicates that the type
     * reference is a child of the List type.
     *
     * @return bool
     */
    public function isList(): bool;

    /**
     * Returns a Boolean value that indicates that
     * the type reference is a NonNull type.
     *
     * @return bool
     */
    public function isNonNull(): bool;

    /**
     * Returns a Boolean value that indicates that
     * the type reference is a NonNull + List type.
     *
     * @return bool
     */
    public function isListOfNonNulls(): bool;
}
