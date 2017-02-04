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
      <a href="#" class="addroute"><?php FrontRouter::i18n('ADD_ROUTE'); ?></a>
    </nav>

    <form method="post" class="routeform">
      <div class="routes">
        <?php foreach ($routes as $route => $callback) self::showRouteForm($route, $callback); ?>
      </div>

      <p id="submit_line">
        <input type="submit" class="submit" name="save" value="<?php i18n('BTN_SAVECHANGES'); ?>">
      </p>
    </form>

    <!--javascript-->

    <!--route template-->
    <template class="routetemplate"><?php self::showRouteForm('', ''); ?></template>

    <!--codemirror-->
    <?php self::showJS('template/js/codemirror/lib/codemirror-compressed.js?v=0.2.0'); ?>

    <!--route ui handler-->
    <script type="text/javascript">
      /* global jQuery, CodeMirror */
      jQuery(function($) {
        // Make a Codemirror Instance
        var makeEditor = function(textarea) {
          var editor = CodeMirror.fromTextArea(textarea, {
            lineNumbers: true,
          });
        };

        $('.routeform .route textarea').each(function(idx, elem) {
          makeEditor(elem);
        });

        // Make routes sortable
        $('.routeform .routes').sortable({
          cancel: 'textarea, .CodeMirror, .deleteroute, input',
        });

        // Add route
        $('body').on('click', '.addroute', function(evt) {
          var $template = $($('.routetemplate').html());
          $('.routes').append($template);

          // Enable CodeMirror on the new textarea
          makeEditor($template.find('textarea')[0]);
          $template[0].scrollIntoView();

          evt.preventDefault();
        });

        // Delete route
        $('.routeform').on('click', '.deleteroute', function(evt) {
          var $route = $(evt.target).closest('.route');
          var route  = $route.find('input').val();
          var status = confirm(<?php echo json_encode(FrontRouter::i18n_r('DELETE_ROUTE_SURE')); ?>.replace('%route%', route));

          if (status) {
            $route.remove();
          }

          evt.preventDefault();
        });
      });
    </script>
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
    <div class="route">
      <a href="#" class="deleteroute">&times;</a>
      <p>
        <label for="route[]"><?php FrontRouter::i18n('ROUTE'); ?>:</label>
        <input class="text" name="route[]" value="<?php echo $route; ?>" required/>
      <p>
      <p>
        <label for="route[]"><?php FrontRouter::i18n('ACTION'); ?>:</label>
        <textarea class="text" name="callback[]" required><?php echo $callback; ?></textarea>
      </p>
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

  private static function showCSS($href) {
    ?>
    <link rel="stylesheet" href="<?php echo $href; ?>">
    <?php
  }

  private static function showJS($src) {
    ?>
    <script src="<?php echo $src; ?>"></script>
    <?php
  }
}