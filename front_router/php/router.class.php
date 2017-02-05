<?php

/**
 * @package FrontRouter
 * @class Router
 */
class FrontRouterRouter {
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
   * Registers multiple routes
   *
   * @param array $routes Routes
   */
  public static function addRoutes($routes) {
    foreach ($routes as $route => $callback) {
      self::addRoute($route, $callback);
    }
  }

  /**
   * Returns the routes that have been registered
   *
   * @return array The routes
   */
  public static function getRegisteredRoutes() {
    return self::$routes;
  }

  /**
   * Converts a route to a correct regular expression (fixing slashes)
   * @param string $route
   * @return string
   */
  public static function routeToRegex($route) {
    return '/^' . str_replace('/', '\/', $route) . '$/';
  }

  /**
   * Execute the routes
   *
   * @param string $url The url to match against
   * @return object The successful route
   */
  public static function executeFront($url) {
    $route = self::findMatchedRoute(self::$routes, $url);

    if ($route) {
      return self::parseDataFromRoute($route);
    } else {
      return false;
    }
  }

  /**
   * Finds the matched route
   *
   * @param array $routes routes to check against
   * @param string $url URL to match
   * @return array|bool Route data (or false if no match)
   */
  public static function findMatchedRoute($routes, $url) {
    foreach ($routes as $route => $callback) {
      // http://upshots.org/php/php-seriously-simple-router
      // Turn the route string into a valid regex
      $pattern = self::routeToRegex($route);

      // If the pattern matches, run the callback and pass in the parameters
      $match = @preg_match($pattern, $url, $params);

      if ($match) {
        array_shift($params);

        return array(
          'route'    => $route,
          'params'   => $params,
          'callback' => $callback,
        );
      }
    }

    // No match
    return false;
  }

  /**
   * Creates a valid function from a callback
   *
   * @param string|function Callback
   * @return function
   */
  public static function callbackStringToFunction($callback) {
    if (is_callable($callback)) {
      $cb = $callback;
    } elseif (is_string($callback)) {
      // Create a callable
      $cb = create_function('', '$args = func_get_args(); ?>' . $callback);
    }

    return $cb;
  }

  /**
   * Parses route and constructs data object
   *
   * @param array $route Route data
   * @return object Result data
   */
  public static function parseDataFromRoute($route) {
    $params   = $route['params'];
    $callback = $route['callback'];

    ob_start();

    // Ensure we have a valid callback
    $cb = self::callbackStringToFunction($callback);

    $data = (object) call_user_func_array($cb, $params);

    if (is_callable($data->content) || is_null($data->content)) {
      if (is_callable($data->content)) {
        call_user_func_array($data->content,$params);
      }

      $buffer  = ob_get_contents();

      if ($buffer) {
        $data->content = $buffer;
      }
    }

    ob_end_clean();

    return $data;
  }
}