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
        <a href="#" class="addroute"><?php FrontRouter::i18n('ADD_ROUTE'); ?></a>
        <?php i18n('FILTER'); ?>:
        <input type="text" class="_text ac_input filter-routes" style="width:80px" autocomplete="off">
      </p>
    </nav>

    <form method="post" class="routeform">
      <div class="routes">
        <?php foreach ($routes as $route => $callback) self::showRouteForm($route, $callback); ?>
      </div>

      <p id="submit_line">
        <input type="submit" class="submit" name="save" value="<?php i18n('BTN_SAVECHANGES'); ?>">
      </p>
    </form>

    <!--route template-->
    <?php
      $exampleAction = implode("\n", array(
        '<?php',
        '  // Callback',
        '  function your_callback() {',
        '    echo \'Your content\';',
        '  }',
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

    <!--route ui handler-->
    <script type="text/javascript">
      /* global jQuery, CodeMirror */
      jQuery(function($) {
        function createEditor(textarea) {
          return CodeMirror.fromTextArea(textarea, {
            lineNumbers: true,
          });
        }

        function getTemplate() {
          return $($('.routetemplate').html());
        }

        function addRouteCallback(evt) {
          var $template = getTemplate();
          var textarea  = $template.find('textarea')[0];
          $('.routes').append($template);

          // Enable CodeMirror on the new textarea
          createEditor(textarea);

          // Scroll the route into view
          $template[0].scrollIntoView();

          evt.preventDefault();
        }

        function deleteRouteCallback(evt) {
          var $route = $(evt.target).closest('.route');
          var route  = $route.find('input').val();
          var status = confirm(<?php echo json_encode(FrontRouter::i18n_r('DELETE_ROUTE_SURE')); ?>.replace('%route%', route));

          if (status) {
            $route.remove();
          }

          evt.preventDefault();
        }

        function collapseRouteCallback(evt) {
          var $target = $(evt.target);
          var $route  = $target.closest('.route');

          // Toggle the callback and "..." div
          $route.find('.callback').slideToggle(200);
          $route.find('.callback-hidden').toggleClass('collapsed');
          $target.toggleClass('collapsed');

          evt.preventDefault();
        }

        function filterRoutesCallback(evt) {
          var text    = evt.target.value;
          var $routes = $('.routeform .route');

          $routes.each(function(idx, route) {
            var $route = $(route);
            var name   = $route.find('.name').val();

            if (name.match(text)) {
              $route.show();
            } else {
              $route.hide();
            }
          });

          evt.preventDefault();
        }

        function init() {
          // Initialize editors
          $('.routeform .route textarea').each(function(idx, textarea) {
            createEditor(textarea);
          });

          // Make routes sortable (except for the buttons and inputs)
          $('.routeform .routes').sortable({
            cancel: 'input, textarea, .CodeMirror, .btn',
          });

          // Add route
          $('#maincontent').on('click', '.addroute', addRouteCallback);

          // Collapse route (hide the callback)
          $('.routeform').on('click', '.collapse-route', collapseRouteCallback);

          // Delete route
          $('.routeform').on('click', '.delete-route', deleteRouteCallback);

          // Filter routes
          $('#maincontent').on('keyup', '.filter-routes', filterRoutesCallback);
        }

        init();
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
    ?><script src="<?php echo $src; ?>"></script><?php
  }
}