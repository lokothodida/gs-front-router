<?php

/**
 * @package FrontRouter
 * @subpackage Data
 *
 * Handles data saved for routes
 */
class FrontRouterData {
  /**
   * Save the routes
   *
   * @param array $routes Routes
   * @return bool True iff routes saved successfully
   */
  public static function saveRoutes($routes) {
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
   * Format data passed in from a form
   *
   * @param array $data Input data
   * @return array The formatted routes
   */
  public static function validateRouteFormData($data) {
    $routes = array();

    if (isset($data['route'])) {
      foreach ($data['route'] as $i => $route) {
        $routes[$route] = $data['callback'][$i];
      }
    }

    return $routes;
  }

  /**
   * Initializes the plugin data
   *
   * @return array Array of error messages (if anything went wrong)
   */
  public static function initialize() {
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
}