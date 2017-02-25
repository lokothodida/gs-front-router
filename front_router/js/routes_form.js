/* global jQuery, CodeMirror, i18n */
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
    scrollTo($template, 500);

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
    var $prev = $container.prev();

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
    var $next = $container.next();

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
  function scrollTo(elem, delay) {
    $('html, body').animate({
      scrollTop: $(elem).offset().top
    }, delay);
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