<?php

namespace Athorrent\Utils;

use Athorrent\Controllers\AbstractController;

class AliasResolver {
    private $routes;

    private $controller;

    public function __construct(array $routes) {
        $this->routes = $routes;
        $this->controller = null;
        $this->controllerClass = '';
    }

    public function setController(AbstractController $controller) {
        $this->controller = $controller;
    }

    public function resolveAlias($action, &$actionPrefix = null) {
        global $app;

        if ($app['routes']->get($action)) {
            return $action;
        }

        if (!isset($this->routes[$action])) {
            return null;
        }

        if ($this->controller !== null && $actionPrefix === null) {
            $controllerClass = get_class($this->controller);
            $actionPrefix = $controllerClass::getActionPrefix();
        }

        if (isset($this->routes[$action][$actionPrefix])) {
            return $this->routes[$action][$actionPrefix]->getOption('alias');
        }

        $route = current($this->routes[$action]);
        $actionPrefix = $route->getOption('actionPrefix');

        return $route->getOption('alias');
    }

    public function generatePath($action, $parameters = array(), $actionPrefix = null) {
        global $app;

        if (is_string($parameters)) {
            $actionPrefix = $parameters;
            $parameters = array();
        }

        $alias = $this->resolveAlias($action, $actionPrefix);

        if ($this->controller !== null) {
            $controllerClass = get_class($this->controller);

            if ($controllerClass::getActionPrefix() === $actionPrefix) {
                $parameters = array_merge($this->controller->getRouteParameters($action), $parameters);
            }
        }

        return $app['url_generator']->generate($alias, $parameters);
    }

    public function generateUrl($action, $parameters = array(), $actionPrefix = null) {
        global $app;
        return $app['request']->getSchemeAndHttpHost() . $this->generatePath($action, $parameters, $actionPrefix);
    }
}

?>
