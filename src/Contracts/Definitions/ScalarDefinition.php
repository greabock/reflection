<?php
/**
 * This file is part of Railt package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Railt\Reflection\Contracts\Definitions;

use Railt\Reflection\Contracts\Behavior\Inputable;
use Railt\Reflection\Contracts\Invocations\Directive\HasDirectives;

/**
 * Interface ScalarDefinition
 */
interface ScalarDefinition extends TypeDefinition, HasDirectives, Inputable
{
}
