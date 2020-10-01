<?php
/*
https://wordpress.stackexchange.com/questions/39142/html-tags-in-wordpress-image-caption

https://developer.wordpress.org/reference/hooks/attachment_fields_to_edit/
https://code.tutsplus.com/articles/creating-custom-fields-for-attachments-in-wordpress--net-13076
https://code.tutsplus.com/articles/how-to-add-custom-fields-to-attachments--wp-31100
https://wordpress.stackexchange.com/questions/4290/can-i-add-custom-meta-for-each-image-uploaded-via-media-upload-php
https://www.wpbeginner.com/wp-tutorials/how-to-add-additional-fields-to-the-wordpress-media-uploader/

http://fancyapps.com/fancybox/3/docs/#inline
*/



add_action('fusion_builder_before_init', 'mgs_lightbox_addon_fusion_builder_init');
add_action('elementor/widgets/widgets_registered', 'mgs_lightbox_addon_init_widgets_elementor');

if( !class_exists('MGS_LightBox_AddOn') ){
	class MGS_LightBox_AddOn{		
		public static $ACF;
		public static $lightbox_enabled;
		public static $compatibility;
		public $settings;
		public $plg_name;
		
		function __construct($config){
			$this->plg_name = MGS_LIGHTBOX_ADDON_NAME;
			$this->build_options($config);
			$this->on_load();
			
			if( self::$ACF ) $this->Enabled_ACF();
			
			add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
			add_shortcode('mgs_lightbox_addon', [$this, 'mgs_lightbox_addon_build']);
		}
		
		public function on_load(){
			//carga configuracion y opciones
			self::$ACF = ( $this->get_field_value('moreinfo-enabled') ) ? true : false;
			self::$lightbox_enabled = false;
			
			//determina compativilidad
			self::$compatibility = [
                'elementor'     => ( did_action('elementor/loaded') )   ? true : false ,
                'avada'         => ( class_exists('FusionBuilder') )    ? true : false ,
                'wpml'          => ( function_exists('icl_object_id') ) ? true : false ,
            ];
		}
		
		public function mgs_lightbox_addon_build($attr){
			$out = '';
			
			if( !isset($attr['img_id']) && isset($attr['avada_img']) ){
				$attr['img_id'] = $this->get_attachment_id($attr['avada_img']);
			}
			$attr['size'] = ( $attr['size'] ) ? $attr['size'] : 'medium';
			
			$uniqid = uniqid();
			$sc_id = 'mgs-lightbox-addon-sc-'.$uniqid;
			$out .= '<span id="'.$sc_id.'" class="mgs-lightbox-addon-sc '.$attr['class'].'">';
			//$out .= '<pre>'.print_r($attr, true).'</pre>';
			
			
			
			
			$title = get_the_title($attr['img_id']);
			$all_img_info = $this->get_attachment_info($attr['img_id']);
			$img = wp_get_attachment_image(
				$attr['img_id'], 
				$attr['size'], 
				false, 
				[
					'class'	=>'mgs-lightbox-img',
					'alt'	=> $title,
				]
			);
			
			$img_full_url = wp_get_attachment_url($attr['img_id']);
			$img_full = wp_get_attachment_image(
				$attr['img_id'], 
				'', 
				false, 
				[
					'class'	=>'mgs-lightbox-img-full ',
					'alt'	=> $title,
				]
			);
			
			
			if( $attr['layout']=='image' ){
				$out .= '
					<a data-fancybox href="'.$img_full_url.'" title="'.$title.'">'.$img.'</a>
				';
			}elseif( $attr['layout']=='image_text' ){
				$out .= '
					<a data-fancybox data-src="#mgs-lightbox-'.$uniqid.'" href="javascript:;" title="'.$title.'">'.$img.'</a>
					<div class="mgs-lightbox-warpper" id="mgs-lightbox-'.$uniqid.'" style="display: none;">
						<div class="mgs-lightbox-grid">
							<div class="mgs-lightbox-img">
								<div class="mgs-lightbox-img-warpper">'.$img_full.'</div>
							</div>
							<div class="mgs-lightbox-content">
								<div class="mgs-lightbox-content-warper">
				';
				if( $attr['title']=='true' ){
					$out .= '		<h3 class="mgs-lightbox-content-title">'.$title.'</h3>';
				}
				if( $attr['desc']=='plano' ){
					$out.= '		<span class="mgs-lightbox-content-desc plano">'.$all_img_info['desc'].'</span>';
				}if( $attr['desc']=='html' ){
					$out.= '		<span class="mgs-lightbox-content-desc html">'.$all_img_info['desc_html'].'</span>';
				}
				$out .= '
									
								</div>
							</div>
						</div>
					</div>
				';
			}elseif( $attr['layout']=='text' ){
				$out .= '
					<a data-fancybox data-src="#mgs-lightbox-'.$uniqid.'" href="javascript:;" title="'.$title.'">'.$img.'</a>
					<div class="mgs-lightbox-warpper" id="mgs-lightbox-'.$uniqid.'" style="display: none;">
						<div class="mgs-lightbox-content">
							<div class="mgs-lightbox-content-warper">
				';
				if( $attr['title']=='true' ){
					$out .= '	<h3 class="mgs-lightbox-content-title">'.$title.'</h3>';
				}
				if( $attr['desc']=='plano' ){
					$out.= '	<span class="mgs-lightbox-content-desc plano">'.$all_img_info['desc'].'</span>';
				}if( $attr['desc']=='html' ){
					$out.= '	<span class="mgs-lightbox-content-desc html">'.$all_img_info['desc_html'].'</span>';
				}
				$out .= '
							</div>
						</div>
					</div>
				';
			}
			
			
			
			$out .= '</span>';
			return $out;
		}
		
		public function enqueue_scripts(){
			wp_enqueue_script('jquery');
			wp_enqueue_script('jquery-fancybox-js', MGS_LIGHTBOX_ADDON_PLUGIN_DIR_URL.'assets/js/jquery.fancybox.min.js');
			wp_enqueue_style('jquery-fancybox-css', MGS_LIGHTBOX_ADDON_PLUGIN_DIR_URL.'assets/css/jquery.fancybox.min.css');
			wp_enqueue_style('mgs-lightbox-css', MGS_LIGHTBOX_ADDON_PLUGIN_DIR_URL.'assets/css/main.css');
			
		}
		
		private function get_attachment_info($attachment_id){
			$attachment = get_post($attachment_id);
			return [
    			'alt'			=> ( get_post_meta($attachment->ID, '_wp_attachment_image_alt', true) ) ? ( get_post_meta($attachment->ID, '_wp_attachment_image_alt', true) ) : $attachment->post_title,
				'caption'		=> $attachment->post_excerpt,
				'desc'			=> $attachment->post_content,
				'title'			=> $attachment->post_title,
				'desc_html'		=> get_field('field_5f6e180c9ffca', $attachment_id)
			];
		}
		
		private function Enabled_ACF(){
			include_once(MGS_LIGHTBOX_ADDON_ACF_PATH.'acf.php');
			add_filter('acf/settings/url', function($url){
				return MGS_LIGHTBOX_ADDON_ACF_URL;
			});
			add_filter('acf/settings/show_admin', function($show_admin){
				return false;
			});
			add_action('acf/init', function(){
				//crea campo
				acf_add_local_field_group([
					'key' => 'group_5f6e17e7e3c8d',
					'title' => 'MGS Media Options',
					'fields' => [
						[
							'key' => 'field_5f6e180c9ffca',
							'label' => 'Descripción',
							'name' => 'descripcion',
							'type' => 'wysiwyg',
							'instructions' => '',
							'required' => 0,
							'conditional_logic' => 0,
							'wrapper' => [
								'width' => '',
								'class' => '',
								'id' => '',
							],
							'default_value' => '',
							'tabs' => 'all',
							'toolbar' => 'full',
							'media_upload' => 0,
							'delay' => 0,
						],
					],
					'location' => [
						[
							[
								'param' => 'attachment',
								'operator' => '==',
								'value' => 'image',
							],
						],
					],
					'menu_order' => 0,
					'position' => 'acf_after_title',
					'style' => 'default',
					'label_placement' => 'top',
					'instruction_placement' => 'label',
					'hide_on_screen' => '',
					'active' => true,
					'description' => ''
				]);
			});
		}
				
		private function get_field_name($id){
            if( self::$compatibility['wpml'] && $this->settings[$id]['wpml'] ){
				$name = $this->plg_name . '_' . $id . '_' . ICL_LANGUAGE_CODE;
            }else{
                $name = $this->plg_name . '_' . $id;
            }
            return $name;
        }
		
		private function get_field_value($id=NULL){
			if( $id===NULL ) return false;
            $name = $this->get_field_name($id);
            $val = get_option($name);
            if( $val=='' ) $val = $this->settings[$id]['def'];
            return $val;
        }
		
		private function build_options($config){
			$this->settings = [];
			foreach( $config as $sec=>$val ){
				foreach( $val['fields'] as $field_name=>$field){
					$this->settings[$field_name] = $field;
				}
			}
			//echo '<pre>'.print_r($this->settings, true).'</pre>';
		}
		
		private function get_attachment_id($url){
			$attachment_id = 0;
			$dir = wp_upload_dir();
			if( false!==strpos($url, $dir['baseurl'].'/')){
				$file = basename($url);
				$query_args = [
					'post_type'		=> 'attachment',
					'post_status'	=> 'inherit',
					'fields'		=> 'ids',
					'meta_query'	=> [
						[
							'value'		=> $file,
							'compare'	=> 'LIKE',
							'key'		=> '_wp_attachment_metadata',
						]
					]
				];
				$query = new WP_Query($query_args);
				if( $query->have_posts() ){
					foreach( $query->posts as $post_id ){
						$meta = wp_get_attachment_metadata($post_id);
						$original_file = basename($meta['file']);
						$cropped_image_files = wp_list_pluck($meta['sizes'], 'file');
						if( $original_file===$file || in_array($file, $cropped_image_files) ){
							$attachment_id = $post_id;
							break;
						}
					}
				}
			}
			return $attachment_id;
		}
	}
}

function mgs_lightbox_addon_init_widgets_elementor(){
	$elementor_elements = [
		'MGS_Ligtbox_Elementor'   => [
			'file'      => 'elementor-lightbox.php',
			'name'      => 'Lightbox',
			'ico'       => 'fa fa-bars',
			'ver'       => '1.0.0'
		],	
	];
	require_once(MGS_LIGHTBOX_ADDON_PLUGIN_DIR.'elementor/elementor.php');
	foreach( $elementor_elements as $k=>$v ){
		if( get_option($k, 1)==0 ){
		}else{
			if( file_exists(MGS_LIGHTBOX_ADDON_PLUGIN_DIR.'elementor/'.$v['file']) ){
				update_option($k, 1, false);
				require_once(MGS_LIGHTBOX_ADDON_PLUGIN_DIR.'elementor/'.$v['file']);    
				\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new $k );
			}else{
				update_option($k, 0, false);
			}
		}
	}
}


function mgs_lightbox_addon_fusion_builder_init(){
			global $fusion_settings, $pagenow;
			$builder_status = function_exists('is_fusion_editor') && is_fusion_editor();
			
			fusion_builder_map(
				[
					'name'			=> 'MGS LightBox AddOn',
					'shortcode'		=> 'mgs_lightbox_addon',
					'icon'			=> '',
					'params'		=> [
						[
							'type'        => 'upload',
							'heading'     => 'Imagen',
							'param_name'  => 'avada_img',
							'value'       => '',
						],
						[
							'type'          => 'radio_button_set',
							'heading'       => 'Diseño',
							'description'	=> 'Seleccione que desea mostrar en el lightbox',
							'param_name'    => 'layout',
							'default'       => 'image',
							'value'         => [
								'image'			=> 'Solo la imagen',
								'image_text'	=> 'Imagen y texto',
								'text'			=> 'Solo texto',
							],
						],
						[
							'type'          => 'radio_button_set',
							'heading'       => 'Titulo',
							'param_name'    => 'title',
							'default'       => 'true',
							'value'         => [
								'true'		=> 'Mostrar',
								'false'		=> 'ocultar',
							],
							'dependency'  => [
								[
									'element'  => 'layout',
									'value'    => 'image',
									'operator' => '!=',
								],
							],
						],
						[
							'type'          => 'radio_button_set',
							'heading'       => 'Descripción',
							'param_name'    => 'desc',
							'default'       => 'html',
							'value'         => [
								'html'		=> 'Texto HTML',
								'plano'		=> 'Texto plano',
							],
							'dependency'  => [
								[
									'element'  => 'layout',
									'value'    => 'image',
									'operator' => '!=',
								],
							],
						],
						[
							'type'        => 'textfield',
							'heading'     => esc_attr__( 'CSS Class', 'fusion-builder' ),
							'description' => esc_attr__( 'Add a class to the wrapping HTML element.', 'fusion-builder' ),
							'param_name'  => 'class',
							'value'       => '',
						],
					]
				]
			);
		}