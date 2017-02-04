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
    if ($data) {
      // Ensure $data_index has a default object
      $data_index = $data_index ? $data_index : getPageObject();

      // Merge in the data
      $data_index = (object) array_merge((array) $data_index, (array) $data);
    }

    return $data_index;
  }
}