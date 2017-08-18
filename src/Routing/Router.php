<?php
/**
 * This file is part of Railgun package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Railgun\Routing;

use Railgun\Support\HashMap;

/**
 * Class Router
 * @package Railgun\Routing
 */
class Router implements \IteratorAggregate
{
    private const MEMOISED_ROUTE_KEY = 0;
    private const MEMOISED_RESPONDER_KEY = 1;

    /**
     * @var HashMap|Respondent[]|HashMap<Route,Respondent>
     */
    private $routes;

    /**
     * @var array|Router[]
     */
    private $compiled = [];

    /**
     * Router constructor.
     */
    public function __construct()
    {
        $this->routes = new HashMap();
    }

    /**
     * @param $prefix
     * @param \Closure $body
     * @return Route
     * @throws \InvalidArgumentException
     */
    public function group($prefix, \Closure $body): Route
    {
        return tap($this->makeRoute($prefix), function (Route $parent) use ($body) {
            /** @var Router $router */
            $router = tap(new Router(), $body);

            /**
             * @var Router $route
             */
            foreach ($router as $route => $then) {
                $this->add($route->into($parent), $then);
            }
        });
    }

    /**
     * @param Route|string $route
     * @return Route
     */
    private function makeRoute($route): Route
    {
        return is_string($route) ? new Route($route) : $route;
    }

    /**
     * @param Route|string $route
     * @param callable|\Closure|string|Respondent $then
     * @return Route
     * @throws \InvalidArgumentException
     */
    public function add($route, $then): Route
    {
        return tap($this->makeRoute($route), function (Route $route) use ($then) {
            $this->routes[$route] = Respondent::new($then);
        });
    }

    /**
     * @return iterable
     */
    public function all(): iterable
    {
        yield from $this->getIterator();
    }

    /**
     * @return \Traversable
     */
    public function getIterator(): \Traversable
    {
        yield from $this->routes->getIterator();
    }

    /**
     * @param string $uri
     * @return bool
     * @throws \Railgun\Exceptions\IndeterminateBehaviorException
     * @throws \Railgun\Exceptions\CompilerException
     */
    public function has(string $uri): bool
    {
        return $this->memoiseRouteFor($uri);
    }

    /**
     * @param string $uri
     * @return bool
     * @throws \Railgun\Exceptions\IndeterminateBehaviorException
     * @throws \Railgun\Exceptions\CompilerException
     */
    private function memoiseRouteFor(string $uri): bool
    {
        if (!array_key_exists($uri, $this->compiled)) {
            /**
             * @var Route $route
             * @var Respondent $respondent
             */
            foreach ($this->routes as $route => $respondent) {
                if ($route->match($uri)) {
                    $this->compiled[$uri] = [
                        self::MEMOISED_ROUTE_KEY     => $route,
                        self::MEMOISED_RESPONDER_KEY => $respondent,
                    ];

                    return true;
                }
            }

            return false;
        }

        return true;
    }

    /**
     * @param string $uri
     * @return null|Respondent
     * @throws \Railgun\Exceptions\IndeterminateBehaviorException
     * @throws \Railgun\Exceptions\CompilerException
     */
    public function resolve(string $uri): ?Respondent
    {
        if ($this->memoiseRouteFor($uri)) {
            return $this->compiled[$uri][self::MEMOISED_RESPONDER_KEY];
        }

        return null;
    }

    /**
     * @param string $uri
     * @return null|Route
     * @throws \Railgun\Exceptions\IndeterminateBehaviorException
     * @throws \Railgun\Exceptions\CompilerException
     */
    public function find(string $uri): ?Route
    {
        if ($this->memoiseRouteFor($uri)) {
            return $this->compiled[$uri][self::MEMOISED_ROUTE_KEY];
        }

        return null;
    }

    /**
     * @return array
     */
    public function __debugInfo(): array
    {
        return to_array(map($this->routes, function (Respondent $value, Route $key) {
            return $key;
        }));
    }
}