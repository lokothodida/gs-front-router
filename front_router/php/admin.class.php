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

    <!--route ui handler-->
    <script type="text/javascript">
      /* global jQuery, CodeMirror */
      // I18N Hashes
      var i18n = {};

      i18n.DELETE_ROUTE_SURE = <?php echo json_encode(FrontRouter::i18n_r('DELETE_ROUTE_SURE')); ?>;

      jQuery(function($) {
        /**
         * Create an editor instance
         *
         * @param textarea
         * @return CodeMirror instance
         */
        function createEditor(textarea) {
          return CodeMirror.fromTextArea(textarea, {
            lineNumbers: true,
            mode: 'php',
          });
        }

        /**
         * Get a route template
         *
         * @return jQueryObject
         */
        function getRouteTemplate() {
          return $($('.routetemplate').html());
        }

        /**
         * Callback fired when clicking "Add Route" button is clicked
         *
         * @param Event evt
         */
        function addRouteCallback(evt) {
          var $template = getRouteTemplate();
          var textarea  = $template.find('textarea')[0];
          $('.routes').append($template);

          // Enable CodeMirror on the new textarea
          createEditor(textarea);

          // Scroll the route into view
          scrollTo($template);

          evt.preventDefault();
        }

        /**
         * Callback fired when "Delete Route" button is clicked
         *
         * @param Event evt
         */
        function deleteRouteCallback(evt) {
          var $route = $(evt.target).closest('.route-container');
          var route  = $route.find('input').val();
          var status = confirm(i18n.DELETE_ROUTE_SURE.replace('%route%', route));

          if (status) {
            $route.remove();
          }

          evt.preventDefault();
        }

        /**
         * Toggle a route action's visibility
         *
         * @param jQueryObject $route Route
         * @param Boolean collapse Selects whether or not to collapse/expand the route
         */
        function toggleRoute($route, collapse = true) {
          var $button = $route.find('.btn.collapse-route');
          var delay   = 200;

          if (collapse) {
            $route.find('.callback').slideUp(delay);
            $route.find('.callback-hidden').removeClass('collapsed');
            $button.addClass('collapsed');
          } else {
            $route.find('.callback').slideDown(delay);
            $route.find('.callback-hidden').addClass('collapsed');
            $button.removeClass('collapsed');
          }
        }

        /**
         * Toggle action visibility of all routes
         *
         * @param jQueryCollection $routes
         * @param Boolean collapsee Selects whether or not to collapse/expand the routes
         */
        function toggleAllRoutes($routes, collapse = true) {
          $routes.each(function(idx, route) {
            toggleRoute($(route), collapse);
          });
        }

        /**
         * Callback fired when "Collapse route" button clicked
         *
         * @param Event evt
         */
        function collapseRouteCallback(evt) {
          var $target  = $(evt.target);
          var $route   = $target.closest('.route');
          var collapse = !$target.hasClass('collapsed');

          toggleRoute($route, collapse);

          evt.preventDefault();
        }

        /**
         * Callback fired when "Collapse all routes" button clicked
         *
         * @param Event evt
         */
        function collapseAllRoutesCallback(evt) {
          toggleAllRoutes($('.route'), true);

          evt.preventDefault();
        }

        /**
         * Callback fired when "Expand all routes" button clicked
         *
         * @param Event evt
         */
        function expandAllRoutesCallback(evt) {
          toggleAllRoutes($('.route'), false);

          evt.preventDefault();
        }

        /**
         * Filter routes according to given text
         *
         * @param jQueryObject $routes
         * @param string text
         */
        function filterRoutes($routes, text) {
          $routes.each(function(idx, route) {
            var $route = $(route);
            var name   = $route.find('.name').val();

            if (name.match(text)) {
              $route.show();
            } else {
              $route.hide();
            }
          });
        }

        /**
         * Callback fired when filter field text changes
         *
         * @param Event evt
         */
        function filterRoutesCallback(evt) {
          var text    = evt.target.value;
          var $routes = $('.route-container');

          filterRoutes($routes, text);

          evt.preventDefault();
        }

        /**
         * Callback fired when "Move route up" button clicked
         *
         * @param Event evt
         */
        function moveRouteUpCallback(evt) {
          var $container = $(evt.target).closest('.route-container');
          $prev = $container.prev();

          if ($prev.length) {
            $container.remove();
            $prev.before($container);
          }

          evt.preventDefault();
        }

        /**
         * Callback fired when "Move route down" button clicked
         *
         * @param Event evt
         */
        function moveRouteDownCallback(evt) {
          var $container = $(evt.target).closest('.route-container');
          $next = $container.next();

          if ($next.length) {
            $container.remove();
            $next.after($container);
          }

          evt.preventDefault();
        }

        /**
         * Creates copy of submit button in the plugin sidebar
         */
        function createSubmitButtonForSidebar() {
          // Duplicate the save changes button and push it into the sidebar
          var $form = $('<form id="js_submit_line"></form>');
          var $saveChanges = $('.save-changes')
          var $submitButton = $saveChanges.clone();
          var $sidebar = $('#sidebar');

          $sidebar.append($form.append($submitButton));

          // When the button is clicked, submit the original form
          $form.on('submit', function(evt) {
            evt.preventDefault();
            $saveChanges.click();
          });
        }

        /**
         * Scrolls to an element
         *
         * @link https://www.abeautifulsite.net/smoothly-scroll-to-an-element-without-a-jquery-plugin-2
         *
         * @param HTMLElement elem
         */
        function scrollTo(elem) {
          $('html, body').animate({
            scrollTop: $(elem).offset().top
          }, 1000);
        }

        /**
         * Initialize the page
         */
        function init() {
          var $maincontent = $('#maincontent');

          // Initialize editors
          $maincontent.find('.routeform .route textarea').each(function(idx, textarea) {
            createEditor(textarea);
          });

          // Make routes sortable (except for the buttons and inputs)
          $maincontent.find('.routes').sortable({
            cancel: 'label, input, textarea, .CodeMirror, .btn',
          });

          // Add route
          $maincontent.on('click', '.addroute', addRouteCallback);

          // Collapse route (hide the callback)
          $maincontent.on('click', '.collapse-route', collapseRouteCallback);

          // Delete route
          $maincontent.on('click', '.delete-route', deleteRouteCallback);

          // Filter routes
          $maincontent.on('keyup', '.filter-routes', filterRoutesCallback);

          // Move route up/down
          $maincontent.on('click', '.move-up', moveRouteUpCallback);
          $maincontent.on('click', '.move-down', moveRouteDownCallback);

          // Toggle all routes
          $maincontent.on('click', '.collapse-all-routes', collapseAllRoutesCallback);
          $maincontent.on('click', '.expand-all-routes', expandAllRoutesCallback);

          // Duplicate submit button for sidebar
          createSubmitButtonForSidebar();
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
    <div class="route-container">
      <div class="move-controls">
        <a href="#" class="move-up">&#x25B2;</a>
        <a href="#" class="move-down">&#x25BC;</a>
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
    ?><script src="<?php echo $src; ?>"></script><?php
  }
}