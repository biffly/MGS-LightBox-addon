<?php
/*
Plugin Name: MGS LightBox AddOn
Plugin URI: https://github.com/biffly/MGS-LightBox-addon/
Description: Crea Lightbox con informacion de la imagen
Version: 0.1
Author: Marcelo Scenna
Author URI: http://www.marceloscenna.com.ar
Text Domain: mgs-lightbox-addon
*/

if( !defined('ABSPATH') ){ exit; }
error_reporting(E_ALL & ~E_NOTICE);

if( !defined('MGS_LIGHTBOX_ADDON_VERSION') )             	define('MGS_LIGHTBOX_ADDON_VERSION', '0.1');
if( !defined('MGS_LIGHTBOX_ADDON_BASENAME') )				define('MGS_LIGHTBOX_ADDON_BASENAME', plugin_basename(__FILE__));
if( !defined('MGS_LIGHTBOX_ADDON_PLUGIN_DIR') ) 			define('MGS_LIGHTBOX_ADDON_PLUGIN_DIR', plugin_dir_path(__FILE__));
if( !defined('MGS_LIGHTBOX_ADDON_PLUGIN_DIR_URL') )			define('MGS_LIGHTBOX_ADDON_PLUGIN_DIR_URL', plugin_dir_url(__FILE__));
if( !defined('MGS_LIGHTBOX_ADDON_GIT') )             		define('MGS_LIGHTBOX_ADDON_GIT', 'biffly/MGS-LightBox-addon');
if( !defined('MGS_LIGHTBOX_ADDON_NAME') )             		define('MGS_LIGHTBOX_ADDON_NAME', 'MGS-LightBox-addon');
//ACF Paths
define('MGS_LIGHTBOX_ADDON_ACF_PATH', MGS_LIGHTBOX_ADDON_PLUGIN_DIR.'includes/acf/');
define('MGS_LIGHTBOX_ADDON_ACF_URL', MGS_LIGHTBOX_ADDON_PLUGIN_DIR_URL.'includes/acf/');

$config = [
	'imagenes'				=> [
		'label'     => __('Imagenes', 'mgs-admin'),
		'fields'    => [
			'moreinfo-enabled'			=> [
				'wpml'              => false,
				'type'              => 'onoff',
				'label'             => __('Más info', 'mgs-admin'),
				'desc'              => __('Funcionalidad que permite agregar un campo HTML a los META de una imagen.', 'mgs-admin'),
				'def'               => '',
				'more-help'			=> __('Esta opción permite agregar una descripción en <strong>HTML</strong> a las imagenes cuando son subidas o desde el la sección de <i>Multimedia</i>.', 'mgs-admin'),
			],
			'addon-enabled'				=> [
				'wpml'              => false,
				'type'              => 'onoff',
				'label'             => __('MGS Lightbox AdddOn', 'mgs-admin'),
				'desc'              => __('Opción que permite mostrar en un lightbox la informacion de la imagen.', 'mgs-admin'),
				'def'               => '',
				'dependent'         => 'moreinfo-enabled'
			]
		]
	],
];
define('MGS_LIGHTBOX_ADDON_CONFIG', $config);


require 'plugin-update-checker/plugin-update-checker.php';
$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
	MGS_LIGHTBOX_ADDON_GIT,
	__FILE__,
	'MGS-LightBox-addon/MGS-LightBox-addon.php'
);


require_once('class/class-main.php');
new MGS_LightBox_AddOn($config);
if( is_admin() ){
    require_once('class/class-mgs-admin.php');
	require_once('class/class-admin.php');
	new MGS_LightBox_AddOn_Admin($config);
}

register_activation_hook(__FILE__, 'mgs_lightbox_addon_activation');
register_deactivation_hook(__FILE__, 'mgs_lightbox_addon_activation');

function mgs_lightbox_addon_activation(){
}