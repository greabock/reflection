<?php
/**
 * This file is part of Railt package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Railt\Reflection;

use Railt\Reflection\Common\Serializable;
use Railt\Reflection\Contracts\Definition\Behaviour\ProvidesType;
use Railt\Reflection\Contracts\Definition\TypeDefinition;
use Railt\Reflection\Contracts\Type as TypeInterface;
use Railt\Reflection\Exception\ReflectionException;
use Railt\Reflection\Exception\TypeConflictException;

/**
 * Class Type
 */
class Type implements TypeInterface
{
    use Serializable;

    /**
     * @var Type[]
     */
    private static $instances = [];

    /**
     * @var array[]|string[][]
     */
    private static $inheritance = [];

    /**
     * @var array|string[]
     */
    private $parent;

    /**
     * @var string
     */
    protected $name;

    /**
     * BaseType constructor.
     * @param string $name
     * @throws \Railt\Io\Exception\ExternalFileException
     */
    private function __construct(string $name)
    {
        $this->name = $name;
        $this->parent = $this->getInheritanceSequence($name);

        $this->verifyType($name);
    }

    /**
     * @param string $name
     * @throws \Railt\Io\Exception\ExternalFileException
     */
    private function verifyType(string $name): void
    {
        $types = \array_merge(static::DEPENDENT_TYPES, static::ROOT_TYPES);

        if (! \in_array($name, $types, true)) {
            throw new TypeConflictException(\sprintf('Invalid type name %s', $this));
        }
    }

    /**
     * @return bool
     */
    public function isInputable(): bool
    {
        return \in_array($this->name, static::ALLOWS_TO_INPUT, true);
    }

    /**
     * @return bool
     */
    public function isReturnable(): bool
    {
        return \in_array($this->name, static::ALLOWS_TO_OUTPUT, true);
    }

    /**
     * @param string $name
     * @return array
     */
    private function getInheritanceSequence(string $name): array
    {
        if (self::$inheritance === []) {
            $this->bootInheritance(new \SplStack(), static::INHERITANCE_TREE);
        }

        return self::$inheritance[$name] ?? [static::ROOT_TYPE];
    }

    /**
     * @param \SplStack $stack
     * @param array $children
     */
    private function bootInheritance(\SplStack $stack, array $children = []): void
    {
        $push = function (string $type) use ($stack): void {
            self::$inheritance[$type] = \array_values(\iterator_to_array($stack));
            self::$inheritance[$type][] = static::ROOT_TYPE;

            $stack->push($type);
        };

        foreach ($children as $type => $child) {
            switch (true) {
                case \is_string($child):
                    $push($child);
                    break;

                case \is_array($child):
                    $push($type);
                    $this->bootInheritance($stack, $child);
                    break;
            }

            $stack->pop();
        }
    }

    /**
     * @param string|ProvidesType $type
     * @return Type|\Railt\Reflection\Contracts\Type
     * @throws \Railt\Io\Exception\ExternalFileException
     */
    public static function of($type): Type
    {
        switch (true) {
            case \is_string($type):
                return self::$instances[$type] ?? (self::$instances[$type] = new static($type));

            case $type instanceof ProvidesType:
                return $type::getType();

            default:
                throw new ReflectionException('Unsupported argument type');
        }
    }

    /**
     * @return bool
     */
    public function isDependent(): bool
    {
        return \in_array($this->name, static::DEPENDENT_TYPES, true);
    }

    /**
     * @param TypeInterface $type
     * @return bool
     */
    public function instanceOf(TypeInterface $type): bool
    {
        $needle = $type->getName();

        return $this->is($needle) || \in_array($needle, $this->parent, true);
    }

    /**
     * @param string $type
     * @return bool
     */
    public function is(string $type): bool
    {
        return $this->getName() === $type;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->getName();
    }
}
