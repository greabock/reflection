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
 * Interface ProvidesInheritance
 */
interface ProvidesInheritance
{
    /**
     * @return iterable|TypeDefinition[]
     */
    public function getParents(): iterable;

    /**
     * @param string $name
     * @return bool
     */
    public function hasParent(string $name): bool;

    /**
     * @param string $name
     * @return TypeDefinition|null
     */
    public function getParent(string $name): ?TypeDefinition;

    /**
     * @param string $name
     * @return bool
     */
    public function isExtends(string $name): bool;
}