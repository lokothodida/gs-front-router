<?php

// Constants
namespace FrontRouterPlugin;

define('ID', $thisfile);

// Paths
define('PLUGINPATH', GSPLUGINPATH . ID . '/');
define('PHPPATH', PLUGINPATH . 'php/');
define('DATAPATH', GSDATAOTHERPATH . ID . '/');
define('DATAHTACCESSFILE', DATAPATH . '.htaccess');
define('DATAROUTESFILE', DATAPATH . '.routes.json');

// URLs
define('ADMINURL', 'load.php?id=' . ID . '&action=');
define('PLUGINURL', $GLOBALS['SITEURL'] . '/plugins/' . ID . '/');
define('IMGURL', PLUGINURL . 'img/');
define('JSURL', PLUGINURL . 'js/');
define('CSSURL', PLUGINURL . 'css/');
define('PHPURL', PLUGINURL . 'php/');