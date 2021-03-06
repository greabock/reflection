<?php
/**
 * This file is part of Railt package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Railt\Reflection\Contracts\Definition\Dependent;

use Railt\Reflection\Contracts\Definition\Behaviour\ProvidesDefaultValue;
use Railt\Reflection\Contracts\Definition\Behaviour\ProvidesTypeIndication;

/**
 * Interface ArgumentDefinition
 */
interface ArgumentDefinition extends ProvidesTypeIndication, ProvidesDefaultValue, DependentTypeDefinition
{
}
