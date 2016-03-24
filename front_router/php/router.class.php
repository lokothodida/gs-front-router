<?php

namespace FrontRouterPlugin;

class Router {
  protected static $routes = array();


  public static function addRoute($route, $callback) {
    self::$routes[$route] = $callback;
  }

  public static function getRegisteredRoutes() {
    return self::$routes;
  }
}