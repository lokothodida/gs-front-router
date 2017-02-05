<?php
/**
 * Front Router User Profile Example
 */
register_plugin(
  basename(__FILE__, '.php'),
  'Front Router User Profile Example',
  '0.1.0',
  'Lawrence Okoth-Odida',
  'https://github.com/lokothodida',
  'Front Router example plugin for showing user profiles',
  'plugins',
  ''
);

// Register the routes
// Main user listing
add_action('front-route', 'addFrontRoute', array(
  'users',
  'FrontRouterExampleUserProfile::listRoute'
));

// Individual user profile
add_action('front-route', 'addFrontRoute', array(
  'users/([a-z0-9-]+)',
  'FrontRouterExampleUserProfile::userRoute'
));

/**
 * @package FrontRouter
 * @subpackage ExampleUserProfile
 *
 * Functions for displaying the profile(s) of user(s)
 */
class FrontRouterExampleUserProfile {
  /**
   * Route for listing all users
   */
  static function listRoute() {
    return array(
      'title'   => 'View All Users',
      'content' => array(__CLASS__, 'listAction'),
    );
  }

  /**
   * Action for listing users
   */
  static function listAction() {
    // Get user files
    $users = self::getUsers();

    // Display user ids
    echo '<ul>';

    foreach ($users as  $user) {
      echo '<li><a href="/users/' . $user->USR . '">' . $user->NAME . ' (' . $user->USR . ')</a></li>';
    }

    echo '</ul>';
  }

  /**
   * Route for an individual user
   */
  static function userRoute($username) {
    return array(
      'title'   => 'User Profile: ' . $username,
      'content' => array(__CLASS__, 'userAction'),
    );
  }

  /**
   * Action for displaying an individual profile
   */
  static function userAction($username) {
    $user = self::getUser($username);

    if ($user) {
      $name  = $user->NAME;
      $email = $user->EMAIL;
      $bio   = $user->USERSBIO;

      // Display the profile
      echo '<h2>Display Name</h2><p>' . $name . '</p>';
      echo '<h2>Email</h2><p><a href="mailto:' . $email . '">' . $email . '</a></p>';
      echo '<h2>Bio</h2>' . $bio;
      echo '<p><a href="/users">Back to user list</a></p>';
    } else {
      echo 'User ' . $username . ' does not exist.';
    }
  }

  /**
   * Get array of all user profiles
   *
   * @return array User profiles
   */
  static function getUsers() {
    // Get user files
    $dir   = GSUSERSPATH . '*.xml';
    $files = glob($dir);
    $users = array();

    foreach ($files as $file) {
      $users[] = simplexml_load_file($file);
    }

    return $users;
  }

  /**
   * Get individual profile information
   *
   * @param string $username Username/slug
   * @return SimpleXMLExtended|bool Profile information if the user exists; false otherwise
   */
  static function getUser($username) {
    $file = GSUSERSPATH . $username . '.xml';

    if (file_exists($file)) {
      return simplexml_load_file($file);
    } else {
      return false;
    }
  }
}