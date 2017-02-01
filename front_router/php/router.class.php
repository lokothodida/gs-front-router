<?php

/**
 * @package FrontRouter
 * @class Router
 */
class Router {
  /**
   * @param array $routes Routes
   */
  protected static $routes = array();

  /**
   * Registers a route
   *
   * @param string $route Route (regex)
   * @param function|string $callback Action
   */
  public static function addRoute($route, $callback) {
    self::$routes[$route] = $callback;
  }

  /**
   * Returns the routes that have been registered
   *
   * @return array The routes
   */
  public static function getRegisteredRoutes() {
    return self::$routes;
  }
}