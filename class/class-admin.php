<?php
require_once('class-mgs-admin.php');

if( !class_exists('MGS_LightBox_AddOn_Admin') ){
	class MGS_LightBox_AddOn_Admin extends MGS_Admin_Class{
		public static $mce_js_url;
		public static $ACF;
		public static $lightbox_enabled;
		
		public function __construct($config){
            $this->slug = 'mgs_lightbox_addon_page';
			$this->plg_url = MGS_LIGHTBOX_ADDON_PLUGIN_DIR_URL;
			$this->plg_git = MGS_LIGHTBOX_ADDON_GIT;
			$this->plg_ver = MGS_LIGHTBOX_ADDON_VERSION;
			$this->plg_name = MGS_LIGHTBOX_ADDON_NAME;
			$this->admin_option = [
				'page_title'	=> 'MGS LightBox Addon',
				'menu_title'	=> 'MGS LightBox Addon',
				'capability'	=> 'manage_options',
				'menu_slug'		=> $this->slug,
			];
			$this->settings = $config;
			$this->load();
			
			
			self::$ACF = ( $this->get_field_value('moreinfo-enabled') ) ? true : false;
			self::$lightbox_enabled = ( $this->get_field_value('addon-enabled') ) ? true : false;
			
			self::$mce_js_url = MGS_LIGHTBOX_ADDON_PLUGIN_DIR_URL.'assets/js/mgs-tinymce.js';
			
			if( self::$lightbox_enabled ){
				add_filter('mce_external_plugins', [$this, 'mce_shortcode_button_init_mce_external_plugins']);
				add_filter('mce_buttons', [$this, 'mce_shortcode_button_init_mce_buttons']);
				add_editor_style(MGS_LIGHTBOX_ADDON_PLUGIN_DIR_URL.'assets/css/editor-style.css');
				
			}
        }
		
		public function mce_shortcode_button_init_mce_external_plugins($plugin_array){
			$screen = get_current_screen();
			if( !current_user_can('edit_posts') && !current_user_can('edit_pages') && get_user_option('rich_editing')=='true' && $screen->parent_file=='edit.php' && $screen->post_type=='post' ) return;
			$plugin_array['mgs_lightbox_mce_button'] = self::$mce_js_url;
    		return $plugin_array;
		}
		public function mce_shortcode_button_init_mce_buttons($buttons){
			$screen = get_current_screen();
			if( !current_user_can('edit_posts') && !current_user_can('edit_pages') && get_user_option('rich_editing') == 'true' && $screen->parent_file=='edit.php' && $screen->post_type=='post' ) return;
			$buttons[] = "mgs_lightbox_mce_button";
    		return $buttons;
		}
		
	}
}
