<?php
/**
 * Front Router External Comments Example
 */
register_plugin(
  basename(__FILE__, '.php'),
  'Front Router External Comments Example',
  '0.1.0',
  'Lawrence Okoth-Odida',
  'https://github.com/lokothodida',
  'Front Router example plugin for external comments pages',
  'plugins',
  ''
);

// Register the route
add_action('front-route', 'addFrontRoute', array(
  '([a-z0-9-]+)/comments',
  'front_router_example_comments_route'
));

/**
 * Router function for the comments
 *
 * @param string $slug The input slug
 * @return array The title and content for the page
 */
function front_router_example_comments_route($slug) {
  // Get the page's filename
  $file = GSDATAPAGESPATH . $slug . '.xml';

  if (file_exists($file)) {
    // Load the XML data
    $xml = simplexml_load_file($file);

    // Set the data
    return array(
      'title'   => 'Viewing comments for page "' . $xml->title . '"',
      'content' => 'front_router_example_comments_action',
    );
  } else {
    // Page not found
    return array(
      'title'   => 'Page "' . $slug . '" does not exist',
      'content' => 'Cannot display comments for the page "' . $slug . '"!',
    );
  }
}

/**
 * Router action for the comments (displaying the comments)
 *
 * @param string $slug The input slug
 */
function front_router_example_comments_action($slug) {
  if (function_exists('get_external_comments')) {
    get_external_comments($slug);
  } else {
    echo 'Install and enable the External Comments plugin to display comments.';
  }
}