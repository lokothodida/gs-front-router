<?php

/**
 * @package FrontRouter
 * @subpackage Admin
 */
class FrontRouterAdmin {
  /**
   * Main admin panel function
   */
  public static function main() {
    // Initialize the plugin data
    $init = FrontRouterData::initialize();

    // If there is an initialization error, show that page
    if (count($init)) {
      return self::showInitializeErrorPage($init);
    }

    // If a request was made to save the routes, save them
    if (!empty($_POST['save'])) {
      $routes = FrontRouterData::validateRouteFormData($_POST);
      $succ   = FrontRouterData::saveRoutes($routes);
      $status = $succ ? 'updated' : 'error';
      $msg    = FrontRouter::i18n_r('UPDATE_ROUTES_' . ($succ ? 'SUCCESS' : 'ERROR'));

      self::showStatusMessage($status, $msg);
    }

    // Show the routes page
    self::showRoutesPage(FrontRouterData::getSavedRoutes());
  }

  /**
   * Shows the error page for when plugin initialization fails
   */
  private static function showInitializeErrorPage($succ) {
    ?>
    <h3><?php FrontRouter::i18n('PLUGIN_NAME'); ?></h3>
    <p><?php FrontRouter::i18n('INIT_ERROR'); ?></p>
    <ul>
      <?php foreach ($succ as $err) : ?>
      <li><?php echo $err; ?></li>
      <?php endforeach; ?>
    </ul>
    <?php
  }

  /**
   * Shows currently saved routes
   *
   * @param array $routes The saved routes
   */
  private static function showRoutesPage($routes) {
    ?>
    <!--css-->

    <?php self::showCSS('template/js/codemirror/lib/codemirror.css?v=screen'); ?>
    <?php self::showCSS(FRONTROUTER_CSSURL . 'routes_form.css?v=screen'); ?>

    <!--html-->

    <h3 class="floated"><?php FrontRouter::i18n('MANAGE_ROUTES'); ?></h3>
    <nav class="edit-nav clearfix">
      <p>
        <!--Add Route-->
        <a href="#" class="addroute"><?php FrontRouter::i18n('ADD_ROUTE'); ?></a>

        <!--Documentation-->
        <a href="https://github.com/lokothodida/gs-front-router/wiki/" target="_blank"><?php i18n('SIDE_DOCUMENTATION'); ?></a>

        <!--Live filter-->
        <?php i18n('FILTER'); ?>:
        <input type="text" class="_text ac_input filter-routes" style="width:80px" autocomplete="off">
      </p>
    </nav>

    <p>
      <a href="#" class="cancel collapse-all-routes">
        <?php FrontRouter::i18n('COLLAPSE_ALL_ROUTES'); ?>
      </a>
      <a href="#" class="cancel expand-all-routes">
        <?php FrontRouter::i18n('EXPAND_ALL_ROUTES'); ?>
      </a>
    </p>

    <form method="post" class="routeform">
      <div class="routes">
        <?php foreach ($routes as $route => $callback) self::showRouteForm($route, $callback); ?>
      </div>

      <div class="submit-line">
        <input type="submit" class="submit save-changes" name="save" value="<?php i18n('BTN_SAVECHANGES'); ?>">
      </div>
    </form>

    <!--route template-->
    <?php
      $exampleAction = implode("\n", array(
        '<?php',
        '  // Callback',
        '  function your_callback() {',
        '    echo \'Your content\';',
        '  }',
        '',
        '  // Action data',
        '  return array(',
        '    \'title\'   => \'Your title\',',
        '    \'content\' => \'your_callback\',',
        '  );'
      ));
    ?>
    <template class="routetemplate"><?php self::showRouteForm('', $exampleAction); ?></template>

    <!--javascript-->

    <!--codemirror-->
    <?php self::showJS('template/js/codemirror/lib/codemirror-compressed.js?v=0.2.0'); ?>

    <!--route ui i18n-->
    <script type="text/javascript">
      // I18N Hashes
      var i18n = {};

      i18n.DELETE_ROUTE_SURE = <?php echo json_encode(FrontRouter::i18n_r('DELETE_ROUTE_SURE')); ?>;
    </script>

    <!--routes form ui -->
    <?php self::showJS(FRONTROUTER_JSURL . 'routes_form.js'); ?>
    <?php
  }

  /**
   * Shows the form for an individual route
   *
   * @param string $route
   * @param string $callback
   */
  private static function showRouteForm($route, $callback) {
    ?>
    <div class="route-container">
      <div class="move-controls">
        <a href="#" class="cancel move-up">&#x25B2;</a>
        <a href="#" class="cancel move-down">&#x25BC;</a>
      </div>
      <div class="route">
        <div class="delete">
          <a href="#" class="btn delete-route">&times;</a>
          <a href="#" class="btn collapse-route"></a>
        </div>
        <div class="field">
          <label for="route[]"><?php FrontRouter::i18n('ROUTE'); ?>:</label>
          <input class="text name" name="route[]" value="<?php echo $route; ?>" placeholder="your/route/here/" required/>
        </div>
        <div class="field">
          <label for="route[]"><?php FrontRouter::i18n('ACTION'); ?>:</label>
          <div class="callback">
            <textarea class="text" name="callback[]"><?php echo $callback; ?></textarea>
          </div>
          <div class="callback-hidden collapsed">
            <p>...</p>
          </div>
        </div>
      </div>
    </div>
    <?php
  }

  /**
   * Shows the status of a given action (at the top of the admin panel page)
   *
   * @param string $status "success" or "error"
   * @param string $message The message itself
   */
  private static function showStatusMessage($status, $message) {
    $statusEncoded  = json_encode($status);
    $messageEncoded = json_encode($message);
    ?>
    <script>
      jQuery(function($) {
        $('div.bodycontent').before('<div class="' + <?php echo $statusEncoded; ?> + '" style="display:block;">'+<?php echo $messageEncoded; ?>+'</div>');
      }); // ready
    </script>
    <?php
  }

  /**
   * Embed an external CSS file
   *
   * @param string $href Link to the file
   */
  private static function showCSS($href) {
    ?><link rel="stylesheet" href="<?php echo $href; ?>"><?php
  }

  /**
   * Embed an external JavaScript file
   *
   * @param string $src Link to the file
   */
  private static function showJS($src) {
    ?><script type="text/javascript" src="<?php echo $src; ?>"></script><?php
  }
}