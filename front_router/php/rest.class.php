<?php

class FrontRouterREST {
  /**
   * Convert an array data to a valid JSON string
   */
  public static function arrayToJSONString($array = array()) {
    return json_encode($array);
  }

  /**
   * Convert an array data to a valid XML string
   *
   */
  public static function arrayToXMLString($array = array()) {
    $xml = new SimpleXMLElement('<?xml version="1.0"?><data></data>');

    // function call to convert array to xml
    self::arrayToXML($array, $xml);

    //saving generated xml file;
    return $xml->asXML();
  }

  /**
   * http://stackoverflow.com/a/5965940
   */
  private static function arrayToXML($data = array(), &$xml_data) {
    foreach ($data as $key => $value) {
      if (is_numeric($key)) {
        $key = 'item'.$key; //dealing with <0/>..<n/> issues
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