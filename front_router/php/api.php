<?php

// Global functions (public API methods)
/**
 * Add a front-end route (registered by other plugins)
 *
 * @param string $route Route (regex)
 * @param function|string $callback Action
 */
function addFrontRoute($route, $callback) {
  Router::addRoute($route, $callback);
}