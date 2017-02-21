<?php

register_plugin_front_router();

/**
 * Registers the plugin, actions and filters
 */
function register_plugin_front_router() {
  // Constants
  define('FRONTROUTER', basename(__FILE__, '.php'));
  define('FRONTROUTER_VERSION', '0.5.0');
  define('FRONTROUTER_PLUGINPATH', GSPLUGINPATH . FRONTROUTER . '/');
  define('FRONTROUTER_PHPPATH', FRONTROUTER_PLUGINPATH . 'php/');

  define('FRONTROUTER_DATAPATH', GSDATAOTHERPATH . FRONTROUTER . '/');
  define('FRONTROUTER_DATAHTACCESSFILE', FRONTROUTER_DATAPATH . '.htaccess');
  define('FRONTROUTER_DATAROUTESFILE', FRONTROUTER_DATAPATH . 'routes.json');

  define('FRONTROUTER_ADMINURL', 'load.php?id=' . FRONTROUTER . '&action=');
  define('FRONTROUTER_PLUGINURL', $GLOBALS['SITEURL'] . '/plugins/' . FRONTROUTER . '/');
  define('FRONTROUTER_IMGURL', FRONTROUTER_PLUGINURL . 'img/');
  define('FRONTROUTER_JSURL', FRONTROUTER_PLUGINURL . 'js/');
  define('FRONTROUTER_CSSURL', FRONTROUTER_PLUGINURL . 'css/');
  define('FRONTROUTER_PHPURL', FRONTROUTER_PLUGINURL . 'php/');

  // Language
  i18n_merge(FRONTROUTER) || i18n_merge(FRONTROUTER, 'en_US');

  // Require dependencies
  require_once(FRONTROUTER_PHPPATH . 'plugin.class.php');
  require_once(FRONTROUTER_PHPPATH . 'router.class.php');
  require_once(FRONTROUTER_PHPPATH . 'url.class.php');
  require_once(FRONTROUTER_PHPPATH . 'admin.class.php');
  require_once(FRONTROUTER_PHPPATH . 'data.class.php');
  require_once(FRONTROUTER_PHPPATH . 'rest.class.php');
  require_once(FRONTROUTER_PHPPATH . 'polyfill.php');
  require_once(FRONTROUTER_PHPPATH . 'api.php');

  // Register plugin
  call_user_func_array('register_plugin', array(
    'id'      => FRONTROUTER,
    'name'    => FrontRouter::i18n_r('PLUGIN_NAME'),
    'version' => FRONTROUTER_VERSION,
    'author'  => 'Lawrence Okoth-Odida',
    'url'     => 'https://github.com/lokothodida',
    'desc'    => FrontRouter::i18n_r('PLUGIN_DESC'),
    'tab'     => 'plugins',
    'admin'   => 'FrontRouterAdmin::main'
  ));

  // Activate actions/filters
  // Front-end
  // Route execution
  add_filter('data_index', 'FrontRouter::executeRoutes');

  // Back-end
  // Sidebar link
  add_action('plugins-sidebar', 'createSideMenu', array(FRONTROUTER, FrontRouter::i18n_r('MANAGE_ROUTES')));
}