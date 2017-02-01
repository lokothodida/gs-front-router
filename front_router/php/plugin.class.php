<?php

/**
 * @package FrontRouter
 * @class FrontRouter
 */
class FrontRouter {
  /**
   * Gets internationalized strings
   *
   * @param string $hash Internationalized key string
   * @param array $replacements
   * @return string Language of the corresponding string
   */
  static function i18n_r($hash, $replacements = array()) {
    $string = i18n_r(FRONTROUTER . '/' . $hash);

    foreach ($replacements as $key => $value) {
      $string = str_replace($key, $value, $string);
    }

    return $string;
  }

  /**
   * Prints internationalized strings
   *
   * @param string $hash Internationalized key string
   * @param array $replacements
   * @void
   */
  static function i18n($hash, $replacements = array()) {
    echo self::i18n_r($hash, $replacements);
  }

  /**
   * Plugin initialization - ensures that the default directories and files are created
   *
   * @return array Array of statuses (error messages for anything that went wrong)
   */
  static function init() {
    $succ = array();

    // Ensure the folder is created
    if (!file_exists(FRONTROUTER_DATAPATH)) {
      if (!mkdir(FRONTROUTER_DATAPATH, 0755)) {
        $succ['folder'] = self::i18n_r('MKDIR_ERROR', array('%folder%' => 'data/other/front_router'));
      }
    }

    // Ensure the htaccess file is created
    if (!file_exists(FRONTROUTER_DATAHTACCESSFILE)) {
      if(!file_put_contents(FRONTROUTER_DATAHTACCESSFILE, 'Deny from all')) {
        $succ['htaccess'] = self::i18n_r('HTACCESS_ERROR', array('%htaccess%' => 'data/other/front_router/.htaccess'));
      }
    }

    // Ensure that the core file is created
    if (!file_exists(FRONTROUTER_DATAROUTESFILE) && !self::saveRoutes(array())) {
      $succ['file'] = self::i18n_r('INIT_ROUTE_ERROR');
    }

    return $succ;
  }

  /**
   * Save the routes
   *
   * @param array $routes Routes
   * @return bool True iff routes saved successfully
   */
  static function saveRoutes($routes) {
    $content = json_encode($routes);
    return @file_put_contents(FRONTROUTER_DATAROUTESFILE, $content);
  }

  /**
   * Get the routes
   *
   * @return array The routes
   */
  static function getSavedRoutes() {
    if (file_exists(FRONTROUTER_DATAROUTESFILE)) {
      $content = @file_get_contents(FRONTROUTER_DATAROUTESFILE);
      return (array) json_decode($content);
    } else {
      return array();
    }
  }

  /**
   * Format $_POST array for saving
   *
   * @param array $data Input data
   * @return array The formatted routes
   */
  static function validatePostData($data) {
    $routes = array();

    if (isset($data['route'])) {
      foreach ($data['route'] as $i => $route) {
        $routes[$route] = $data['callback'][$i];
      }
    }

    return $routes;
  }

  /**
   * Displays administration Panel
   *
   * @return void
   */
  static function admin() {
    // Initialize
    $succ = self::init();

    // If no failures have been detected, show the admin panel
    if (!count($succ)) {
      // Save the routes if the form has been sent
      if (isset($_POST['save'])) {
        $routes = self::validatePostData($_POST);
        $succ   = self::saveRoutes($routes);

        $status = $succ ? 'updated' : 'error';
        $msg    = self::i18n_r('UPDATE_ROUTES_' . ($succ ? 'SUCCESS' : 'ERROR'));

        // Display success/error status
        include(FRONTROUTER_PHPPATH . 'statusmessage.php');
      }

      // Get the routes
      $routes = self::getSavedRoutes();

      // Set up some URLs that will be helpful for the form
      $css = array(
        'codemirror' => 'template/js/codemirror/lib/codemirror.css?v=screen',
      );

      $js = array(
        'codemirror' => 'template/js/codemirror/lib/codemirror-compressed.js?v=0.2.0'
      );

      // Show the form
      include(FRONTROUTER_PHPPATH . 'viewroutes.php');
    } else {
      // Display the errors
      include(FRONTROUTER_PHPPATH . 'initerror.php');
    }
  }

  /**
   * Execute the routes
   *
   * @param SimpleXMLExtended $data_index Page data
   * @return SimpleXMLExtended Filtered page data
   */
  static function executeRoutes($data_index) {
    $routes  = array();
    $data    = array();
    $matched = false;
    $url     = self::getRelativeURL();

    // Check saved routes
    $saved = self::getSavedRoutes();
    $routes = array_merge($routes, $saved);

    // Register routes from other plugins
    exec_action('front-route');

    $registered = Router::getRegisteredRoutes();
    $routes = array_merge($routes, $registered);

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

        $data = (object) call_user_func_array($cb, $params);
        $matched = true;
        break;
      }
    }

    // Start output buffering
    ob_start();

    // Finally set the page data contents from return array, and collect buffer and save to content
    if ($matched) {
      // route match

      if (!$data_index) {
        $data_index = getPageObject();
      }

      if ($data) {
        // callback has return data
        $data_index = (object) array_merge((array) $data_index, (array) $data);
      }

      // support for content callables, so user can get arguments
      // if content is callable, or content is null, save buffer to content
      if (is_callable($data->content) || is_null($data->content)) {
        if (is_callable($data->content)) {
          call_user_func_array($data->content,$params);
        }

        $buffer  = ob_get_contents();

        if ($buffer) {
          $data_index->content = $buffer;
        }
      }
    }

    ob_end_clean();
    return $data_index;
  }

  // Gets the root URL
  static function getRootURL() {
    $pretty  = (string) $GLOBALS['PRETTYURLS'];
    $root    = $GLOBALS['SITEURL'] . (empty($pretty) ? 'index.php' : null);
    return $root;
  }

  // Get URL relative to the domain
  static function getRelativeURL() {
    $pretty  = (string) $GLOBALS['PRETTYURLS'];
    $root    = self::getRootURL() . (empty($pretty) ? '?id=' : null);

    return self::getURL($root);
  }

  // Get the full URL
  static function getURL($root = false) {
    // https://css-tricks.com/snippets/php/get-current-page-url/
    $url  = @( $_SERVER["HTTPS"] != 'on' ) ? 'http://' . $_SERVER["SERVER_NAME"] :  'https://' . $_SERVER["SERVER_NAME"];
    $url .= $_SERVER["REQUEST_URI"];

    // Remove http/https from the current url and root
    $url = strstr($url, '//');
    $root = $root ? strstr($root, '//') : '';

    // Shave off the root
    $url = str_replace($root, '', $url);

    // Shave off trailing slashes and double slashes
    $url = ltrim($url, '/');
    $url = rtrim($url, '/');
    $url = preg_replace('~/+~', '/', $url); // http://stackoverflow.com/questions/2217759/regular-expression-replace-multiple-slashes-with-only-one

    return $url;
  }
}