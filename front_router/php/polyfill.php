<?php
/**
 * Polyfill for functions that are not in GetSimple versions before 3.4
 */
if (!function_exists('\getPageObject')) {
  /**
   * Get an empty GS xml page object (for  GS v < 3.4)
   * @return SimpleXMLExtended Empty XML page object
   */
  function getPageObject() {
    $xml = new \SimpleXMLExtended('<?xml version="1.0" encoding="UTF-8"?><item></item>');
    $pagefields = array('title','pubDate','meta','metad','url','content','parent','template','private');

    foreach ($pagefields as $field) {
      $xml->$field = null;
    }

    return $xml;
  }
}