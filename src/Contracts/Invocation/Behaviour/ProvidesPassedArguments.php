<?php
/**
 * This file is part of Railt package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Railt\Reflection\Contracts\Invocation\Behaviour;

use Railt\Reflection\Contracts\Invocation\Dependent\ArgumentInvocation;

/**
 * Interface ProvidesPassedArguments
 */
interface ProvidesPassedArguments
{
    /**
     * @return iterable|ArgumentInvocation[]
     */
    public function getArguments(): iterable;

    /**
     * @param string $name
     * @return bool
     */
    public function hasArgument(string $name): bool;

    /**
     * @param string $name
     * @return ArgumentInvocation|null
     */
    public function getArgument(string $name): ?ArgumentInvocation;
}