<?php

// Global functions (public API methods)
// Add a front-end route (registered by other plugins)
function addFrontRoute($route, $callback) {
  FrontRouterPlugin\Router::addRoute($route, $callback);
}