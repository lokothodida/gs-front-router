<?php

// Functions
namespace FrontRouterPlugin;

// Alias for defining a constant within this namespace
function define($name, $value) {
  return \define(__NAMESPACE__ . '\\' . $name, $value);
}

// Internationalization function aliases
function i18n($hash, $replacements = array()) {
  echo i18n_r($hash, $replacements);
}

function i18n_r($hash, $replacements = array()) {
  $string = \i18n_r(ID . '/' . $hash);

  foreach ($replacements as $key => $value) {
    $string = str_replace($key, $value, $string);
  }

  return $string;
}

// Administration panel
function admin() {
  // Initialize
  $succ = init();

  // If no failures have been detected, show the admin panel
  if (!count($succ)) {
    // Save the routes if the form has been sent
    if (isset($_POST['save'])) {
      $routes = validatePostData($_POST);
      $succ   = saveRoutes($routes);

      $status = $succ ? 'updated' : 'error';
      $msg    = i18n_r('UPDATE_ROUTES_' . ($succ ? 'SUCCESS' : 'ERROR'));

      // Display success/error status
      include(PHPPATH . 'statusmessage.php');
    }

    // Get the routes
    $routes = getSavedRoutes();

    // Set up some URLs that will be helpful for the form
    $css = array(
      'codemirror' => 'template/js/codemirror/lib/codemirror.css?v=screen',
    );

    $js = array(
      'codemirror' => 'template/js/codemirror/lib/codemirror-compressed.js?v=0.2.0'
    );

    // Show the form
    include(PHPPATH . 'viewroutes.php');
  } else {
    // Display the errors
    include(PHPPATH . 'initerror.php');
  }
}

// Save the routes
function saveRoutes($routes) {
  $content = json_encode($routes);
  return @file_put_contents(DATAROUTESFILE, $content);
}

// Get the routes
function getSavedRoutes() {
  if (file_exists(DATAROUTESFILE)) {
    $content = @file_get_contents(DATAROUTESFILE);
    return (array) json_decode($content);
  } else {
    return array();
  }
}

// Format $_POST array for saving
function validatePostData($data) {
  $routes = array();

  if (isset($data['route'])) {
    foreach ($data['route'] as $i => $route) {
      $routes[$route] = $data['callback'][$i];
    }
  }

  return $routes;
}

// Initialization
function init() {
  $succ = array();

  // Ensure the folder is created
  if (!file_exists(DATAPATH)) {
    if (!mkdir(DATAPATH, 0755)) {
      $succ['folder'] = 'MKDIR_ERROR';
    }
  }

  // Ensure the htaccess file is created
  if (!file_exists(DATAHTACCESSFILE)) {
    if(!file_put_contents(DATAHTACCESSFILE, 'Deny from all')) {
      $succ['htaccess'] = 'HTACCESS_ERROR';
    }
  }

  // Ensure that the core file is created
  if (!file_exists(DATAROUTESFILE) && !saveRoutes(array())) {
    $succ['file'] = 'INIT_ROUTE_ERROR';
  }

  return $succ;
}

// Execute the routes
function executeRoutes() {
  global $data_index;

  $routes = array();
  $data = false;
  $matched = false;
  $url = getRelativeURL();

  // Check saved routes
  $saved = getSavedRoutes();
  $routes = array_merge($routes, $saved);

  // Register routes from other plugins
  exec_action('front-route');

  $registered = Router::getRegisteredRoutes();
  $routes = array_merge($routes, $registered);

  ob_start();
  // Select a matching route
  foreach ($routes as $route => $callback) {
    // http://upshots.org/php/php-seriously-simple-router
    // Turn the route string into a valid regex
    $pattern = '/^' . str_replace('/', '\/', $route) . '$/';

    // If the pattern matches, run the callback and pass in the parameters
    $match = @preg_match($pattern, $url, $params);

    if ($match) {
      array_shift($params);

      // Ensure we have a valid callback
      if (is_callable($callback)) {
        $cb = $callback;
      } else {
        $cb = function() use ($callback) {
          $args = func_get_args();
          return eval('?>' . $callback);
        };
      }

      $data = call_user_func_array($cb, $params);
      $matched = true;
      break;
    }
  }

  // Finally set the page contents
  if ($matched) {
    // title
    if (isset($data['title'])) {
      $data_index->title = $data['title'];
    }

    // content
    if (isset($data['content'])) {
      call_user_func_array($data['content'], $params);
    }

    $data_index->content = ob_get_contents();
  }

  ob_end_clean();
  //return $data_index;
}

// Get URL relative to the domain
function getRelativeURL() {
  return getURL((string) $GLOBALS['SITEURL']);
}

// Get the full URL
function getURL($root = false) {
  // https://css-tricks.com/snippets/php/get-current-page-url/
  $url  = @( $_SERVER["HTTPS"] != 'on' ) ? 'http://'.$_SERVER["SERVER_NAME"] :  'https://'.$_SERVER["SERVER_NAME"];
  $url .= $_SERVER["REQUEST_URI"];

  // Remove http/https from the current url and root
  $url = strstr($url, '//');
  $root = $root ? strstr($root, '//') : '';

  return str_replace($root, '', $url);
}