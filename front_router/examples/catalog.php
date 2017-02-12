<?php
/**
 * Front Router Catalog Example
 *
 * This plugin assumes that you:
 *  - Have I18N Special Pages installed
 *  - Have I18N Search installed
 *  - Have a special page called "Catalog" created
 *  - Have the special fields "category" and "price" for the "Catalog"
 */
register_plugin(
  basename(__FILE__, '.php'),
  'Front Router Catalog Example',
  '0.1.0',
  'Lawrence Okoth-Odida',
  'https://github.com/lokothodida',
  'Front Router example plugin for a catalog using I18N Special Pages and I18N Search',
  'plugins',
  ''
);

// Register the routes
// Main page
add_action('front-route', 'addFrontRoute', array(
  'catalog',
  'FrontRouterExampleCatalog::indexRoute'
));

// Category items
add_action('front-route', 'addFrontRoute', array(
  'catalog/category/([a-z0-9]+)',
  'FrontRouterExampleCatalog::categoryRoute'
));

// Item
add_action('front-route', 'addFrontRoute', array(
  'catalog/item/([a-z0-9-]+)',
  'FrontRouterExampleCatalog::itemRoute'
));

/**
 * @package FrontRouter
 * @subpackage ExampleCatalog
 */
class FrontRouterExampleCatalog {
  /**
   * @param SimpleXMLElement|null Catalog item page data
   */
  static $item;

  /**
   * Route for main page
   */
  static function indexRoute() {
    return array(
      'title' => 'Catalog',
      'content' => array(__CLASS__, 'indexAction')
    );
  }

  /**
   * Action for main page (shows all categories and items)
   */
  static function indexAction() {
    $categories = self::getCategories();
    $items      = self::getItems();
    ?>
    <h2>Categories</h2>
    <ul><?php self::displayCategories($categories); ?></ul>

    <h2>All items</h2>
    <ol class="items"><?php self::displayItems($items); ?></ol>
    <?php
  }

  /**
   * Route for a category
   *
   * @param string $categorySlug
   */
  static function categoryRoute($categorySlug) {
    $categories = self::getCategories();

    if (isset($categories[$categorySlug])) {
      return array(
        'title' => 'Catalog items in "' . $categories[$categorySlug] . '"',
        'content' => array(__CLASS__, 'categoryAction')
      );
    } else {
      return array(
        'title' => 'Catalog category ' . $categorySlug . ' not found',
        'content' => 'Category not found!',
      );
    }
  }

  /**
   * Action for the category (displays the items in that category)
   *
   * @param string $categorySlug
   */
  static function categoryAction($categorySlug) {
    $items = self::getItems(array('category' => $categorySlug));
    ?>
    <p><a href="/catalog">Back to Catalog home</a></p>
    <ol><?php self::displayItems($items); ?></ol>
    <?php
  }

  /**
   * Route for an individual item
   *
   * @param string $itemSlug
   */
  static function itemRoute($itemSlug) {
    self::$item = self::getItem($itemSlug);

    if (self::$item) {
      return array(
        'title'   => self::$item->title,
        'content' => array(__CLASS__, 'itemAction')
      );
    } else {
      return array(
        'title' => 'Catalog item not found',
        'content' => 'Item <strong>' . $itemSlug . '</strong> is not in this catalog!',
      );
    }
  }

  /**
   * Action for an individual item (displays its contents)
   *
   * @param string $itemSlug
   */
  static function itemAction($itemSlug) {
    $item = self::$item;
    ?>
    <p><a href="/catalog">Back to Catalog home</a></p>
    <p>Category: <a href="<?php echo self::getCategoryURL(clean_url($item->category)); ?>"><?php echo $item->category; ?></a></p>
    <p>Price: <?php echo $item->price; ?></p>
    <?php echo $item->content; ?>
    <?php
  }

  /**
   * Gets items in an I18N Search query
   *
   * @param array $query Query parameters {
   *   @param string category Category slug
   * }
   */
  static function getItems($query = array()) {
    // Merge query defaults
    $query = array_merge(array(
      'category' => '',
    ), $query);

    $tags  = implode(' ', array('_special_catalog', $query['category']));
    $words = null;
    $first = 0;
    $max   = 999;
    $order = 'created';
    $lang  = null;

    return return_i18n_search_results($tags, $words, $first, $max, $order, $lang);
  }

  /**
   * Displays a list of items
   *
   * @uses clean_url
   *
   * @param array $items Items query {
   *   @param array $results Actual items
   * }
   */
  static function displayItems($items) {
    foreach ($items['results'] as $item) {
      ?>
      <li>
        <h3><a href="<?php echo self::getItemURL($item->slug); ?>"><?php echo $item->title; ?></a></h3>
        <p>Category: <a href="<?php echo self::getCategoryURL(clean_url($item->category)); ?>"><?php echo $item->category; ?></a></p>
        <p>Price: <?php echo $item->price; ?></p>
      </li>
      <?php
    }
  }

  /**
   * Displays a list of the categories (and links)
   *
   * @param array $categories List of categories, slug => name
   */
  static function displayCategories($categories) {
    foreach ($categories as $slug => $name) {
      ?>
      <li><a href="<?php echo self::getCategoryURL($slug); ?>"><?php echo $name; ?></a></li>
      <?php
    }
  }

  /**
   * Gets the categories
   *
   * @return array List of categories, slug => name
   */
  static function getCategories() {
    $categories = array();
    $file = GSDATAOTHERPATH . 'i18n_special_catalog.xml';

    if (file_exists($file)) {
      $xml = simplexml_load_file($file);

      foreach ($xml->fields->item as $field) {
        if ((string) $field->name === 'category') {
          foreach ($field->xpath('//option') as $option) {
            $category = (string) $option;
            $slug = clean_url($category);
            $categories[$slug] = $category;
          }
        }
      }
    }

    return $categories;
  }

  /**
   * Gets an item's XML data
   *
   * @return SimpleXMLElement|false XML data iff the file exists
   */
  static function getItem($slug) {
    $file = GSDATAPAGESPATH . $slug . '.xml';

    if (file_exists($file)) {
      return simplexml_load_file($file);
    } else {
      return false;
    }
  }

  /**
   * Gets the item's canonical catalog URL
   *
   * @param string $slug Item slug
   * @return string Item URL
   */
  static function getItemURL($slug) {
    return '/catalog/item/' . $slug;
  }

  /**
   * Gets the category's canonical catalog URL
   *
   * @param string $slug Category slug
   * @return string Category URL
   */
  static function getCategoryURL($slug) {
    return '/catalog/category/' . $slug;
  }
}