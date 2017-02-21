<?php
/**
 * Front Router I18N News Example
 *
 * This plugin assumes that you:
 *  - Have I18N Special Pages installed
 *  - Have I18N Search installed
 *  - Have a special page called "News" created
 *
 * Once you have created some articles, go to:
 *   /news/ to see the main page (lists all news articles)
 *   /news/yyyy/ to see articles created in the year yyyy
 *   /news/yyyy/mm/ to see articles created in the month mm of year yyyy
 *   /news/yyyy/mm/dd/slug.html to see article with slug created yyyy/mm/dd
 *
 * If any page is suffixed with a language parameter (e.g. ?setlang=de), then
 * that page will be shown with the language option restricted (e.g. listing
 * only articles published in that language).
 */
register_plugin(
  basename(__FILE__, '.php'),
  'Front Router I18N News Example',
  '0.1.0',
  'Lawrence Okoth-Odida',
  'https://github.com/lokothodida',
  'Front Router example plugin for a news module using I18N Special Pages and Search',
  'plugins',
  ''
);

// Register the routes
// Main page
add_action('front-route', 'addFrontRoute', array(
  'news',
  'FrontRouterExampleI18NNews::indexRoute'
));

// Articles by Year
add_action('front-route', 'addFrontRoute', array(
  'news/([0-9]{4})',
  'FrontRouterExampleI18NNews::yearRoute'
));

// Articles by Month
add_action('front-route', 'addFrontRoute', array(
  'news/([0-9]{4})/([0-9]{2})',
  'FrontRouterExampleI18NNews::monthRoute'
));

// Article
add_action('front-route', 'addFrontRoute', array(
  'news/([0-9]{4})/([0-9]{2})/([0-9]{2})/([a-z0-9-]+).html',
  'FrontRouterExampleI18NNews::articleRoute'
));

// Add a sidebar link in the admin panel for quick access to the special pages
add_action('pages-sidebar', 'createSideMenu', array('i18n_specialpages&pages&special=news', 'Manage News'));

/**
 * Class for routing and displaying the news pages
 *
 * @package FrontRouter
 * @subpackage ExampleI18NNews
 */
class FrontRouterExampleI18NNews {
  /**
   * @var SimpleXMLElement $article Individual article data (for article page)
   */
  private static $article;

  /**
   * Load the index page
   */
  public static function indexRoute() {
    return array(
      'title'   => 'All News',
      'content' => array(__CLASS__, 'indexAction')
    );
  }

  /**
   * Load the Year page
   *
   * @param string $year
   */
  public static function yearRoute($year) {
    return array(
      'title'   => 'News articles published in ' . $year,
      'content' => array(__CLASS__, 'yearAction')
    );
  }

  /**
   * Load the Month page
   *
   * @param string $year
   * @param string $month
   */
  public static function monthRoute($year, $month) {
    return array(
      'title'   => 'New articles published in ' . $year . ', ' . $month,
      'content' => array(__CLASS__, 'monthAction')
    );
  }

  /**
   * Load the Article page
   *
   * @param string $year
   * @param string $month
   * @param string $day
   * @param string $slug
   */
  public static function articleRoute($year, $month, $day, $slug) {
    self::$article = self::getArticle($year, $month, $day, $slug);

    if (self::$article) {
      $title   = 'News article: ' . self::$article->title;
      $content = array(__CLASS__, 'articleAction');
    } else {
      $title   = 'Article not found';
      $content = 'Article ' . $slug . ' not found';
    }

    return array('title' => $title, 'content' => $content);
  }

  /**
   * Load articles for main page
   */
  public static function indexAction() {
    $articles = self::getArticles(array(
      'lang' => self::getCurrentLang(),
    ));

    self::displayLangOptions();
    self::displayArticles($articles);
  }

  /**
   * Load articles from a given year
   *
   * @param string $year
   */
  public static function yearAction($year) {
    $articles = self::getArticles(array(
      'year' => $year,
      'lang' => self::getCurrentLang(),
    ));

    self::displayLangOptions();
    self::displayArticles($articles);
    self::displayHomeLink();
  }

  /**
   * Load articles from a given month
   *
   * @param string $year
   * @param string $month
   */
  public static function monthAction($year, $month) {
    $articles = self::getArticles(array(
      'year'  => $year,
      'month' => $month,
      'lang'  => self::getCurrentLang(),
    ));

    self::displayLangOptions();
    self::displayArticles($articles);
    self::displayHomeLink();
  }

  /**
   * Load an individual article
   *
   * @param string $year
   * @param string $month
   * @param string $day
   * @param string $slug
   */
  public static function articleAction($year, $month, $day, $slug) {
    self::displayLangOptions($slug);
    self::displayArticle(self::$article);
    self::displayHomeLink();
  }

  /**
   * Display an individual article
   *
   * @param SimpleXMLElement $article
   */
  public static function displayArticle($article) {
    $date = self::getArticleYearMonthDay($article->creDate);
    ?>
    <p>Posted by <?php echo $article->author; ?></p>
    <p>Date:
      <a href="/news/<?php echo $date['year']; ?>/"><?php echo $date['year']; ?></a>
      <a href="/news/<?php echo $date['year']; ?>/<?php echo $date['month']; ?>/"><?php echo $date['month']; ?></a>
    </p>
    <?php echo $article->content; ?>
    <?php
  }

  /**
   * Display a list of articles
   *
   * @param array $articles
   */
  public static function displayArticles($articles) {
    ?>
    <ul>
    <?php
    foreach ($articles['results'] as $article) {
      $date  = self::getArticleYearMonthDay($article->creDate);
      $year  = $date['year'];
      $month = $date['month'];
      $day   = $date['day'];
      $slug  = strtok($article->slug, '_'); // Remove any language code from slug
      ?>
      <li>
        <h3>
          <strong>[<?php echo $year; ?>/<?php echo $month; ?>/<?php echo $day; ?>]</strong>
          <a href="/news/<?php echo $date['year']; ?>/<?php echo $date['month']; ?>/<?php echo $date['day']; ?>/<?php echo $slug; ?>.html"><?php echo $article->title; ?></a>
          </h3>
      </li>
      <?php
    }
    ?>
    </ul>
    <?php
  }

  /**
   * Search/query for articles
   *
   * @link http://mvlcek.bplaced.net/get-simple/i18nsearch for more I18N Search API details
   *
   * @param array $query {
   *   @param string $year
   *   @param string $month
   *   @param string $words
   *   @param array  $tags
   *   @param string $lang
   * }
   *
   * @return array {
   *   @var int   totalCount
   *   @var int   first
   *   @var array results
   * }
   */
  public static function getArticles($query = array()) {
    // Merge query defaults
    $query = array_merge(array(
      'words' => null,
      'year'  => '',
      'month' => '',
      'tags'  => array(),
      'lang'  => return_i18n_default_language(),
    ), $query);

    $tags  = array_merge(array('_special_news'), $query['tags']);

    if ($query['year']) {
      $tags[] = '_cre_' . $query['year'];
    }

    if ($query['month']) {
      $tags[] = '_cre_' . $query['year'] . $query['month'];
    }

    $words = $query['words'];
    $first = 0;
    $max   = 9999; // Shows all articles (no pagination)
    $order = 'created';
    $lang  = $query['lang'];

    return return_i18n_search_results($tags, $words, $first, $max, $order, $lang);
  }

  /**
   * Get an individual article
   *
   * @param string $year
   * @param string $month
   * @param string $day
   * @param string $slug
   *
   * @return SimpleXMLElement|bool Returns article if one on that day with that slug exists
   */
  public static function getArticle($year, $month, $day, $slug) {
    $xml = @return_i18n_page_data($slug);

    if ($xml) {
      // Check that the date is correct
      $credate = (string) $xml->creDate;
      $timestamp = strtotime($credate);
      $compareDate = date('Y m d', $timestamp);
      $compareTo   = $year . ' ' . $month . ' ' . $day;

      if ($compareDate === $compareTo) {
        return $xml;
      } else {
        return false;
      }
    } else {
      return false;
    }
  }

  /**
   * Get the year, month and day from a timestamp
   *
   * @param string|long $timestamp
   *
   * @return array {
   *   @var string year
   *   @var string month
   *   @var string day
   * }
   */
  public static function getArticleYearMonthDay($timestamp) {
    if (!is_long($timestamp)) {
      $timestamp = strtotime((string) $timestamp);
    }

    $string = date('Y m d', $timestamp);
    $date   = explode(' ', $string);

    return array('year' => $date[0], 'month' => $date[1], 'day' => $date[2]);
  }

  /**
   * Get the current language (either from setlang or lang URL parameter)
   *
   * @return string Langauge code, e.g. de
   */
  private static function getCurrentLang() {
    if (isset($_GET['setlang'])) {
      return $_GET['setlang'];
    } elseif (isset($_GET['lang'])) {
      return $_GET['lang'];
    } elseif (isset($_COOKIE['language'])) {
      return $_COOKIE['language'];
    } else {
      return return_i18n_default_language();
    }
  }

  /**
   * Print links to other language options for current page
   *
   * @param string $slug
   */
  private static function displayLangOptions($slug = null) {
    $languages = return_i18n_available_languages($slug);
    ?>
    <p>
      Languages:
      <?php foreach ($languages as $lang): ?>
      <span>
        <a href="?setlang=<?php echo $lang?>"><?php echo $lang; ?></a>
      </span>
      <?php endforeach; ?>
    </p>
    <?php
  }

  /**
   * Display link to news homepage
   */
  private static function displayHomeLink() {
    ?><p><a href="/news/">Back to news home</a></p><?php
  }
}