<?php

class FrontRouterREST {
  /**
   * Convert an array data to a valid JSON string
   *
   * @param array $array
   * @return string
   */
  public static function arrayToJSONString($array = array()) {
    return json_encode($array);
  }

  /**
   * Convert an array data to a valid XML string
   *
   * @param array $array
   * @return string
   */
  public static function arrayToXMLString($array = array()) {
    $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><data></data>');

    // Convert data to XML
    self::arrayToXML($array, $xml);

    // Generate XML
    return $xml->asXML();
  }

  /**
   * Convert an array to XML
   * @link http://stackoverflow.com/a/5965940
   *
   * @param array $data
   * @param SimpleXMLElement $xml_data
   */
  private static function arrayToXML($data = array(), &$xml_data) {
    if (!is_array($data) && !is_object($data)) {
      // No more arrays/objects to iterate over - just stick the data into the node
      $xml_data[0] = "$data";
      return;
    }

    // Iterate through each data key=>value pair
    foreach ($data as $key => $value) {
      if (is_numeric($key)) {
        $key = 'item' . $key; // dealing with <0/>..<n/> issues
      }

      if ($key === '@attributes' && is_array($value)) {
        // Attributes
        foreach ($value as $attr => $val) {
          $xml_data->addAttribute("$attr", htmlspecialchars("$val"));
        }
      } elseif (is_array($value)) {
        // Multiple nodes with the same name
        foreach ($value as $k => $v) {
          $subnode = $xml_data->addChild($key);
          self::arrayToXML($v, $subnode);
        }
      } else {
        // Individual node
        $xml_data->addChild("$key", htmlspecialchars("$value"));
      }
    }
  }
}