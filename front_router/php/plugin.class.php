<?php

/**
 * @package FrontRouter
 *
 * Utility methods for the plugin and hooks/filters
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
   */
  static function i18n($hash, $replacements = array()) {
    echo self::i18n_r($hash, $replacements);
  }

  /**
   * Execute the routes
   *
   * @param SimpleXMLExtended $data_index Page data
   * @return SimpleXMLExtended Filtered page data
   */
  public static function executeRoutes($data_index) {
    // Get the full URL for matching
    $url = FrontRouterURL::getRelativePageURL();

    // Register saved routes
    FrontRouterRouter::addRoutes(FrontRouterData::getSavedRoutes());

    // Register routes from other plugins
    exec_action('front-route');

    // Execute the router
    $data = FrontRouterRouter::executeFront($url);

    // Set data from the router's action
    if ($data && property_exists($data, 'type') && $data->type === 'json') {
      // RESTful JSON service
      header('Content-Type: application/json');
      exit(FrontRouterRest::arrayToJSONString($data->content));
    } elseif ($data && property_exists($data, 'type') && $data->type === 'xml') {
      // RESTful XML service
      header('Content-Type: application/xml');
      exit(FrontRouterREST::arrayToXMLString($data->content));
    } elseif ($data) {
      // Front routed page
      // Ensure $data_index has a default object
      $data_index = $data_index ? $data_index : getPageObject();

      // Merge in the data
      foreach ($data as $prop => $value) {
        $data_index->{$prop} = $value;
      }
    }

    return $data_index;
  }
}