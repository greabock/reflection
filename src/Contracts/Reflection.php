<?php
/**
 * This file is part of Railt package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Railt\Reflection\Contracts;

use Railt\Reflection\Contracts\Definition\TypeDefinition;
use Railt\Reflection\Exception\TypeNotFoundException;

/**
 * Interface Reflection
 */
interface Reflection
{
    /**
     * @return iterable|Document[]
     */
    public function getDocuments(): iterable;

    /**
     * @param Type|null $of
     * @return iterable|TypeDefinition[]
     */
    public function all(Type $of = null): iterable;

    /**
     * @param string $name
     * @return bool
     */
    public function has(string $name): bool;

    /**
     * @param string $name
     * @return TypeDefinition|null
     */
    public function find(string $name): ?TypeDefinition;

    /**
     * @param string $name
     * @param TypeDefinition|null $from
     * @return TypeDefinition
     * @throws TypeNotFoundException
     */
    public function get(string $name, TypeDefinition $from = null): TypeDefinition;

    /**
     * @param TypeDefinition $type
     * @return Reflection
     */
    public function add(TypeDefinition $type): Reflection;
}
