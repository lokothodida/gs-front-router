<?php
/**
 * Front Router File Listing Example
 */
register_plugin(
  basename(__FILE__, '.php'),
  'Front Router File Listing Example',
  '0.1.0',
  'Lawrence Okoth-Odida',
  'https://github.com/lokothodida',
  'Front Router example plugin for listing files from your /uploads/public folder',
  'plugins',
  ''
);

// Register the route
add_action('front-route', 'addFrontRoute', array(
  'files(.*)',
  'FrontRouterFileListingExample::route'
));

// Register FontAwesome for some nice list styling
register_style('fontawesome', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css', '4.7.0', 'screen');
queue_style('fontawesome', GSFRONT);

/**
 * @package FrontRouter
 * @subpackage FileListingExample
 *
 * Methods for displaying files in the uploads/public folder
 */
class FrontRouterFileListingExample {
  /**
   * Route for listing the files
   *
   * @param string $path Folder path (relative to uploads/public)
   */
  static function route($path) {
    $title = 'Viewing files';

    if (!empty($path)) {
      $title .= ' in folder "' . $path . '"';
    }

    return array(
      'title'   => $title,
      'content' => array(__CLASS__, 'action'),
    );
  }

  /**
   * Action for listing files
   *
   * @param string $path Folder path (relative to uploads/public)
   */
  static function action($path) {
    // Get folders under this path
    $folders = self::getFolders($path);

    // Get files under this path
    $files = self::getFiles($path);

    // Breadcrumb trail
    self::displayBreadcrumbs($path);

    echo '<ul class="files fa-ul">';

    // Display folders
    self::displayFolders($folders);

    // Display files
    self::displayFiles($files);

    echo '</ul>';
  }

  /**
   * Gets the absolute path of a given folder
   *
   * @param string $path Folder path (relative to uploads/public)
   * @return string Absolute path
   */
  static function getAbsolutePath($path = '') {
    return GSDATAUPLOADPATH . '/public' . $path . '/';
  }

  /**
   * Gets folders from a given relative path
   *
   * @param string $path
   * @return array Folder array, each with property 'name', 'url' and 'path'
   */
  static function getFolders($path) {
    return self::getItems($path, '*/', 'getFolderURL');
  }

  /**
   * Gets files from a given relative path
   *
   * @param string $path
   * @return array File array, each with property 'name', 'url' and 'path'
   */
  static function getFiles($path) {
    return self::getItems($path, '*.*', 'getFileURL');
  }

  /**
   * Gets items from a given relative path
   *
   * @param string $path
   * @param string $ext Item file extension
   * @param string $urlFunction Function to call to get corretc URL for item
   * @return array Items array, each with property 'name', 'url' and 'path'
   */
  static function getItems($path, $ext, $urlFunction) {
    $absolutePath = self::getAbsolutePath($path);
    $glob         = glob($absolutePath . $ext);
    $items        = array();

    foreach ($glob as $item) {
      $name = basename($item);
      $items[] = array(
        'url' => call_user_func(array(__CLASS__, $urlFunction), $name, $path),
        'path' => $item,
        'name' => $name,
      );
    }

    return $items;
  }

  /**
   * Gets a folder's URL
   *
   * @param string $name Folder name
   * @param string $path Folder path
   * @return string Folder URL
   */
  static function getFolderURL($name, $path) {
    return '/files' . $path . '/' . $name;
  }

  /**
   * Gets a file's URL
   *
   * @param string $name File name
   * @param string $path File path
   * @return string File URL
   */
  static function getFileURL($name, $path) {
    return '/data/uploads/public' . $path . '/' . $name;
  }

  /**
   * Displays breadcrumbs trail for given path
   *
   * @param string $path
   */
  static function displayBreadcrumbs($path) {
    $folders = explode('/', $path);
    $url     = '/files';

    echo '<p>';

    foreach ($folders as $folder) {
      $url .= $folder . '/';

      if (empty($folder)) $folder = 'root';

      echo '<a href="' . $url . '">' . $folder . '</a>' . ' / ';
    }

    echo '</p>';
  }

  /**
   * Displays list of folders
   *
   * @param array $folders
   */
  static function displayFolders($folders) {
    foreach ($folders as $folder) {
      echo '<li><i class="fa-li fa fa-folder"></i><a href="' . $folder['url'] . '">' . $folder['name']  . '</a></li>';
    }
  }

  /**
   * Displays list of files
   *
   * @param array $files
   */
  static function displayFiles($files) {
    foreach ($files as $file) {
      echo '<li><i class="fa-li fa fa-file-o"></i><a href="' . $file['url'] . '">' . $file['name'] . '</a></li>';
    }
  }
}