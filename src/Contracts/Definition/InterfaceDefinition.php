<?php
/**
 * This file is part of Railt package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Railt\Reflection\Contracts\Definition;

use Railt\Reflection\Contracts\Definition\Behaviour\ProvidesFields;
use Railt\Reflection\Contracts\Definition\Behaviour\ProvidesInterfaces;

/**
 * Interface InterfaceDefinition
 */
interface InterfaceDefinition extends TypeDefinition, ProvidesFields, ProvidesInterfaces
{

}
