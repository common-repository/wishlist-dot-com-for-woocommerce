<?php if ( ! defined( 'ABSPATH' ) ) {
	die;
} // Cannot access pages directly.
/**
 * Standard args for all field:
 * - type
 * - id
 * - title
 *   - description
 * - class
 * - attributes
 * - before
 * - after
 */
if ( ! class_exists( 'WLDCFWC_Exopite_Simple_Options_Framework' ) ) :

	class WLDCFWC_Exopite_Simple_Options_Framework {


		use Wldcfwc_trait;

		/**
		 * Directory name
		 *
		 * @var    string
		 */
		public $dirname = '';

		/**
		 * Unique key
		 *
		 * @var    string
		 */
		public $unique = '';

		/**
		 * Notice
		 *
		 * @var    boolean
		 */
		public $notice = false;

		/**
		 * Settings
		 *
		 * @var    array
		 */
		public $config = array();

		/**
		 * Options
		 *
		 * @var    array
		 */
		public $fields   = array();
		public $elements = array();
		public $options_all = array();

		public $is_menu;

		public $lang_default;
		public $lang_current;

		public $languages = array();

		public $version;

		public $allowed_html_post;

		/**
		 * Options store
		 *
		 * @var    array
		 */
		public $db_options = array();

		/**
		 * Sets the type to  metabox|menu
		 *
		 * @var string
		 */
		private $type;

		/**
		 * Errors
		 *
		 * @var object WP_Error
		 */
		protected $errors;

		public function __construct( $config, $elements, $options_all ) {

			// If we are not in admin area exit.
			if ( ! is_admin() ) {
				return;
			}

			$this->version = '20191203';

			$this->unique = $config['id'];

			$this->config   = $config;
			$this->elements = $elements;
			$this->options_all = $options_all;
			$this->is_menu  = true;

			$this->allowed_html_post = wp_kses_allowed_html( 'post' );
			// Remove '<textarea>' tag
			unset( $this->allowed_html_post['textarea'] );

			$this->wldcfwc_get_fields();

			$this->wldcfwc_load_classes();

			$this->wldcfwc_set_properties();

			$this->wldcfwc_include_field_classes();

			$this->wldcfwc_define_shared_hooks();

			$this->wldcfwc_define_hooks();
		}

		/**
		 * Set Properties of the class
		 */
		protected function wldcfwc_set_properties() {

			if ( isset( $this->config['type'] ) ) {
				$this->wldcfwc_set_type( $this->config['type'] );
			}

			// Parse the configuration against default values for Menu
			if ( $this->is_menu ) {
				$default_menu_config = $this->wldcfwc_get_config_default_menu();
				$this->config        = wp_parse_args( $this->config, $default_menu_config );

				// override option type to nullify 'simple' even if added
				$this->config['options'] = ''; // so, even if options is 'simple', we make it non-simple

			}

			$this->config['is_options_simple'] = false;

			$this->dirname = wp_normalize_path( __DIR__ );
		}

		/**
		 * Register all of the hooks shared by all $type
		 */
		protected function wldcfwc_define_shared_hooks() {

			// scripts and styles
			add_action( 'admin_enqueue_scripts', array( $this, 'wldcfwc_load_scripts_styles' ) );
		}//end wldcfwc_define_shared_hooks()

		protected function wldcfwc_define_hooks() {

			$this->wldcfwc_define_menu_hooks();
		}

		/**
		 * Register all of the hooks related to 'menu' functionality
		 *
		 */
		protected function wldcfwc_define_menu_hooks() {

			/**
			 * Load options only if menu
			 */
			// WLDC note the filter being applied which adjust feilds at the time they're pulled from the db
			$this->db_options = apply_filters( 'exopite_sof_menu_get_options', get_option( $this->unique ), $this->unique );

			add_action( 'admin_init', array( $this, 'wldcfwc_register_admin_settings' ) );
			add_action( 'admin_menu', array( $this, 'wldcfwc_add_admin_menu' ) );

			// see plugin_action_links() below
			if ( isset( $this->config['plugin_basename'] ) && ! empty( $this->config['plugin_basename'] ) ) {
				add_filter(
					'plugin_action_links_' . $this->config['plugin_basename'],
					array(
						$this,
						'wldcfwc_plugin_action_links',
					)
				);
			}
		}

		/**
		 * Sets the $type property
		 *
		 * @param string $config_type
		 */
		protected function wldcfwc_set_type( $config_type ) {

			$config_type = sanitize_key( $config_type );

			switch ( $config_type ) {
				case ( 'menu' ):
					$this->type = 'menu';
					break;

				default:
					$this->type = '';
			}
		}

		/**
		 * Confirm if the menu page is currently load
		 *
		 * @return bool true if its menu options
		 */
		protected function wldcfwc_is_menu_page_loaded() {

			$current_screen = get_current_screen();

			return substr( $current_screen->id, - strlen( $this->unique ) ) === $this->unique;
		}

		/**
		 * Load classes
		 */
		public function wldcfwc_load_classes() {

			include_once 'fields-class.php';
		}

		/**
		 * Get url from path
		 * works only for local urls
		 *
		 * @param string $path the path
		 *
		 * @return string   the generated url
		 */
		public function wldcfwc_get_url( $path = '' ) {

			$url = str_replace(
				wp_normalize_path( untrailingslashit( ABSPATH ) ),
				site_url(),
				$path
			);

			return $url;
		}

		public function wldcfwc_locate_template( $type ) {

			$fields_dir_name   = 'fields';
			$template = join( DIRECTORY_SEPARATOR, array( $this->dirname, $fields_dir_name, $type . '.php' ) );

			return $template;
		}

		/**
		 * Register "settings" for plugin option page in plugins list
		 *
		 * @param array $links plugin links
		 *
		 * @return array possibly modified $links
		 */
		public function wldcfwc_plugin_action_links( $links ) {
			/**
			 *  Documentation : https://codex.wordpress.org/Plugin_API/Filter_Reference/plugin_action_links_(plugin_file_name)
			 */

			// BOOL of settings is given true | false
			if ( is_bool( $this->config['settings_link'] ) ) {

				// FALSE: If it is false, no need to go further
				if ( ! $this->config['settings_link'] ) {
					return $links;
				}

				// TRUE: if Settings link is not defined, lets create one
				if ( $this->config['settings_link'] ) {

					$settings_link = sanitize_file_name( $this->config['settings_link'] );

					$settings_link_array = array(
						'<a href="' . admin_url( $settings_link ) . '">' . esc_html__( 'Settings', 'wishlist-dot-com-for-woocommerce' ) . '</a>',
					);
					return array_merge( $settings_link_array, $links );
				}
			} // if ( is_bool( $this->config['settings_link'] ) )

			// URL of settings is given
			if ( ! is_bool( $this->config['settings_link'] ) && ! is_array( $this->config['settings_link'] ) ) {

				// WLDC
				$settings_link = esc_url( $this->config['settings_link'] );

				// WLDC adding link html like above
				$settings_link_html = '<a href="' . $settings_link . '" id="settings-wishlist-com-for-woocommerce" aria-label="View WishList for WooCommerce Settings">Settings</a>';

				// WLDC this needs to be an array to be merged in return
				$settings_link = array( $settings_link_html );

				return array_merge( $settings_link, $links );
			}

			// Array of settings_link is given
			if ( is_array( $this->config['settings_link'] ) ) {

				$settings_links_config_array = $this->config['settings_link'];
				$settings_link_array         = array();

				foreach ( $settings_links_config_array as $link ) {

					$link_text         = isset( $link['text'] ) ? sanitize_text_field( $link['text'] ) : esc_html__( 'Settings', 'wishlist-dot-com-for-woocommerce' );
					$link_url_un_clean = isset( $link['url'] ) ? $link['url'] : '#';

					$link_type = isset( $link['type'] ) ? sanitize_key( $link['type'] ) : 'default';

					switch ( $link_type ) {
						case ( 'external' ):
							$link_url = esc_url_raw( $link_url_un_clean );
							break;

						case ( 'file' ):
							$link_url = admin_url( sanitize_file_name( $link_url_un_clean ) );
							break;

						default:
							if ( $this->config['submenu'] ) {

								$options_base_file_name = sanitize_file_name( $this->config['parent'] );

								$options_base_file_name_extension = pathinfo( wp_parse_url( $options_base_file_name )['path'], PATHINFO_EXTENSION );

								if ( 'php' === $options_base_file_name_extension ) {
									$options_base = $options_base_file_name;
								} else {
										$options_base = 'admin.php';
								}
								$options_page_id = $this->unique;

								$settings_link = "{$options_base}?page={$options_page_id}";

								$link_url = admin_url( $settings_link );

							} else {
									$settings_link = "?page={$this->unique}";
									$link_url      = admin_url( $settings_link );
							}
					}

					$settings_link_array[] = '<a href="' . $link_url . '">' . $link_text . '</a>';

				}

				return array_merge( $settings_link_array, $links );

			} // if ( is_array( $this->config['settings_link'] ) )

			// if nothing is returned so far, return original $links
			return $links;
		}

		/**
		 * Get default config for menu
		 *
		 * @return array $default
		 */
		public function wldcfwc_get_config_default_menu() {

			$default = array(
				'parent'        => 'options-general.php',
				'menu'          => 'plugins.php', // For backward compatibility
				'menu_title'    => __( 'Plugin Options', 'exopite-options-framework' ),
				// Required for submenu
				'submenu'       => false,
				// The name of this page
				'title'         => __( 'Plugin Options', 'wishlist-dot-com-for-woocommerce' ),
				'option_title'  => '',
				// The capability needed to view the page
				'capability'    => 'manage_options',
				'settings_link' => true,
				'tabbed'        => true,
				'position'      => 100,
				'icon'          => '',
				'search_box'    => true,
				// 'multilang'     => true,
				'options'       => false,
			);

			return apply_filters( 'exopite_sof_filter_config_default_menu_array', $default );
		}

		/**
		 * Register settings for plugin option page with a callback to save
		 */
		public function wldcfwc_register_admin_settings() {

			register_setting( $this->unique, $this->unique, array( $this, 'wldcfwc_save' ) );
		}

		/**
		 * Register plugin option page
		 */
		public function wldcfwc_add_admin_menu() {
			$menu = add_menu_page(
				$this->config['title'],
				$this->config['menu_title'],
				$this->config['capability'],
				$this->unique, // slug
				array( $this, 'wldcfwc_display_page' ),
				$this->config['icon'],
				$this->config['position']
			);
		}

		/**
		 * Load scripts and styles
		 *
		 * @hooked admin_enqueue_scripts
		 *
		 * @param string hook name
		 */
		public function wldcfwc_load_scripts_styles( $hook ) {

			// return if not admin
			if ( ! is_admin() ) {
				return;
			}

			/**
			 * Load Scripts shared by all $type
			 */
			if ( $this->wldcfwc_is_menu_page_loaded() ) :

				$url  = $this->wldcfwc_get_url( $this->dirname );
				$base = trailingslashit( join( '/', array( $url, 'assets' ) ) );

				if ( WLDCFWC_USE_MIN_JS ) {
					$js_ext = '.min.js';
				} else {
					$js_ext = '.js';
				}
				if ( WLDCFWC_USE_MIN_CSS ) {
					$css_ext = '.min.css';
				} else {
					$css_ext = '.css';
				}

				if ( ! wp_style_is( 'font-awesome' ) || ! wp_style_is( 'font-awesome-470' ) || ! wp_style_is( 'FontAwesome' ) ) {

					/* Get font awsome */
					wp_enqueue_style( 'font-awesome-470', $base . 'font-awesome-4.7.0/font-awesome.min.css', array(), $this->version, 'all' );

				}

				/**
				 * Jquery-ui-core is built into WordPress
				 *
				 * @link https://developer.wordpress.org/reference/functions/wp_enqueue_script/
				 */
				wp_enqueue_style( 'jquery-ui-core' );

				wp_enqueue_style( 'exopite-simple-options-framework', $base . 'styles' . $css_ext, array(), $this->version, 'all' );

				// Add jQuery form scripts for menu options AJAX save
				wp_enqueue_script( 'jquery-form' );

				$scripts_styles = array(
					array(
						'name' => 'exopite-simple-options-framework-js',
						'fn'   => 'scripts' . $js_ext,
						'dep'  => array(
							'jquery',
							'wp-color-picker',
						),
					),
				);

				foreach ( $scripts_styles as $item ) {
					wp_enqueue_script( $item['name'], $base . $item['fn'], $item['dep'], $this->version, true );
				}

				/**
				 * Enqueue class scripts
				 * with this, only enqueue scripts if class/field is used
				 */
				$this->wldcfwc_enqueue_field_classes();

			endif; // $this->wldcfwc_is_menu_page_loaded()
		}

		/**
		 * Save options to meta
		 *
		 * @return mixed
		 *
		 * @hooked register_admin_settings() for menu
		 */
		public function wldcfwc_save( $posted_data ) {

			// Does the user have ability to save?
			if ( ! current_user_can( $this->config['capability'] ) ) {
				return null;
			}

			$valid   = array();
			$post_id = null;

			$section_fields_with_values = array();

			$section_fields_with_values = $this->wldcfwc_sanitize_posted_data( $posted_data );

			// see adjust_options_prior_to_saving
			$valid = apply_filters( 'exopite_sof_save_menu_options', $section_fields_with_values, $this->unique );

			// note: this hook happens after the filter above, even if it's above the filter call above
			do_action( 'exopite_sof_do_save_menu_options', $section_fields_with_values, $this->unique );

			return $valid;
		}

		/**
		 * Sanatize submitted data
		 *
		 * @param $posted_data
		 */
		public function wldcfwc_sanitize_posted_data( $posted_data ) {
			$arr = array();

			foreach ( $posted_data as $key => $value ) {
				if ( ! is_array( $value ) ) {
					$arr[ $key ] = $this->wldcfwc_sanitize_allowed_html( $value, $this->allowed_html_post );
				} else {
					$value_tmp = array();
					foreach ( $value as $val ) {
						$value_tmp[] = $this->wldcfwc_sanitize_allowed_html( $val, $this->allowed_html_post );
					}
					$arr[ $key ] = $value_tmp;
				}
			}
			return $arr;
		}

		/**
		 * Sanatize option value
		 *
		 * @param $value
		 */
		public function wldcfwc_sanitize_option_value( $value ) {

			if ( ! isset( $value )) {
				$value_sanitized = null;
			} elseif ( ! is_array( $value ) ) {
				$value_sanitized = $this->wldcfwc_sanitize_allowed_html( $value, $this->allowed_html_post );
			} else {
				$value_sanitized = array();
				foreach ( $value as $val ) {
					$value_sanitized[] = $this->wldcfwc_sanitize_allowed_html( $val, $this->allowed_html_post );
				}
			}

			return $value_sanitized;
		}

		/**
		 * Sanatize by field type
		 *
		 * @param $field_type, $value
		 */
		public function wldcfwc_sanitize_allowed_html( $value, $allowed_html ) {
			$value = wp_kses( $value, $allowed_html );
			return $value;
		}

		/**
		 * Loop fields and runs callback which will display the field
		 *
		 * @param $callbacks
		 */
		public function wldcfwc_loop_fields( $callbacks ) {

			if ( ! is_array( $this->fields ) ) {
				return;
			}

			foreach ( $this->fields as $section ) {

				// before
				if ( $callbacks['before'] ) {
					call_user_func( array( $this, $callbacks['before'] ), $section );
				}

				if ( ! isset( $section['fields'] ) || ! is_array( $section['fields'] ) ) {
					continue;
				}

				foreach ( $section['fields'] as $field ) {

					// If has subfields
					if ( ( 'include_field_class' == $callbacks['main'] || 'enqueue_field_class' == $callbacks['main'] ) && isset( $field['fields'] ) ) {

						foreach ( $field['fields'] as $subfield ) {

							if ( $callbacks['main'] ) {
								call_user_func( array( $this, $callbacks['main'] ), $subfield );
							}
						}
					}

					if ( $callbacks['main'] ) {
						call_user_func( array( $this, $callbacks['main'] ), $field );
					}

					// main

				}

				// after
				if ( $callbacks['after'] ) {
					call_user_func( array( $this, $callbacks['after'] ) );
				}
			}
		}

		/**
		 * Traverse the nested fields
		 *
		 * @param $array, $fields
		 */
		public function wldcfwc_recursive_walk( $array, &$fields ) {

			foreach ( $array as $key => $value ) {

				if ( is_array( $value ) ) {

					if ( isset( $value['type'] ) ) {

						if ( ! in_array( $value['type'], $fields ) && ! empty( $value['type'] ) ) {

								$temp_array = array(
									'type' => $value['type'],
								);

								if ( isset( $value['id'] ) ) {
									$temp_array['id'] = $value['id'];
								}

								$fields[ $value['type'] ] = $temp_array;

						}

						if ( 'editor' == $value['type'] && isset( $value['editor'] ) ) {

							if ( ! isset( $fields[ $value['type'] ]['editor'] ) || ! is_array( $fields[ $value['type'] ]['editor'] ) ) {
								$fields[ $value['type'] ]['editor'] = array();
							}

							if ( ! in_array( $value['editor'], $fields[ $value['type'] ]['editor'] ) ) {
								$fields[ $value['type'] ]['editor'][] = $value['editor'];
							}
						}
					}

					$this->wldcfwc_recursive_walk( $value, $fields );

				}
			}

			return $fields;
		}

		/**
		 * Loop and add callback to include and enqueue
		 */
		public function wldcfwc_include_field_classes() {

			if ( empty( $this->fields ) ) {
				return;
			}

			$fields = array();
			$fields = $this->wldcfwc_recursive_walk( $this->fields, $fields );

			foreach ( $fields as $field => $args ) {

				$this->wldcfwc_include_field_class( $field );

			}
		}

		/**
		 * Loop and add callback to include and enqueue
		 */
		public function wldcfwc_enqueue_field_classes() {

			if ( empty( $this->fields ) ) {
				return;
			}

			$fields = array();
			$fields = $this->wldcfwc_recursive_walk( $this->fields, $fields );

			foreach ( $fields as $field => $args ) {

				$this->wldcfwc_enqueue_field_class( $args );

			}
		}

		/**
		 * Include field classes
		 * and enqueue they scripts
		 */
		public function wldcfwc_include_field_class( $field ) {

			if ( is_array( $field ) && isset( $field['type'] ) ) {
				$field = $field['type'];
			}

			$class = 'WLDCFWC_Exopite_Simple_Options_Framework_Field_' . ucfirst( $field );

			if ( ! class_exists( $class ) ) {

				//only allow files that are
				$field_filename = $this->wldcfwc_locate_template( $field );

				$allow_fields = [
					'color'=>'color.php',
					'content'=>'content.php',
					'headermenu'=>'headermenu.php',
					'hidden'=>'hidden.php',
					'html'=>'html.php',
					'image'=>'image.php',
					'radio'=>'radio.php',
					'range'=>'range.php',
					'script'=>'script.php',
					'select'=>'select.php',
					'text'=>'text.php',
					'textarea'=>'textarea.php',
				];

				if ( isset( $allow_fields[$field] ) ) {
					$field_class_path = join( DIRECTORY_SEPARATOR, array( $this->dirname, 'fields', $allow_fields[$field] ) );
				}

				if ( isset( $field_class_path ) && file_exists( $field_class_path ) ) {

					include_once join(
						DIRECTORY_SEPARATOR,
						array(
							$this->dirname,
							'fields',
							$allow_fields[$field],
						)
					);

				}
			}
		}

		/**
		 * Include field classes
		 * and enqueue they scripts
		 */
		public function wldcfwc_enqueue_field_class( $field ) {

			$class = 'WLDCFWC_Exopite_Simple_Options_Framework_Field_' . $field['type'];

			if ( class_exists( $class ) && method_exists( $class, 'wldcfwc_enqueue' ) ) {

				$args = array(
					'plugin_sof_url'  => plugin_dir_url( __FILE__ ),
					'plugin_sof_path' => plugin_dir_path( __FILE__ ),
					'field'           => $field,
				);

				$class::wldcfwc_enqueue( $args );

			}
		}

		/**
		 * Generate files
		 *
		 * @param array $field field args
		 *
		 * @echo string   generated HTML for the field
		 */
		public function wldcfwc_add_field( $field, $value = null ) {

			do_action( 'exopite_sof_before_generate_field', $field, $this->config );
			do_action( 'exopite_sof_before_add_field', $field, $this->config );

			$output_header        = '';
			$output_footer        = '';
			$output_field_escaped = '';
			// field's class
			$class             = 'WLDCFWC_Exopite_Simple_Options_Framework_Field_' . $field['type'];
			$depend            = '';
			$wrap_class        = ( ! empty( $field['wrap_class'] ) ) ? ' ' . $field['wrap_class'] : '';
			$hidden            = ( 'hidden' == $field['type'] ) ? ' hidden' : '';
			$sub               = ( ! empty( $field['sub'] ) ) ? 'sub-' : '';
			$allowed_html_tags = wldcfwc_set_allowed_tags( 'post,svg,script,admin_panel' );

			/**
			 * Add editor name to classes for styling purposes.
			 */
			if ( 'editor' == $field['type'] ) {

				if ( ! isset( $field['editor'] ) || 'tinymce' == $field['editor'] ) {
					$wrap_class .= ' exopite-sof-tinymce-editor';
				} elseif ( isset( $field['editor'] ) && 'trumbowyg' == $field['editor'] ) {
					$wrap_class .= ' exopite-sof-trumbowyg-editor';
				}
			}

			if ( ! empty( $field['dependency'] ) ) {
				$hidden  = ' hidden';
				$depend .= ' data-' . $sub . 'controller="' . $field['dependency'][0] . '"';
				$depend .= ' data-' . $sub . 'condition="' . $field['dependency'][1] . '"';
				$depend .= ' data-' . $sub . 'value="' . $field['dependency'][2] . '"';
			}

			// WLDC adding $field['type'] != 'html' && so we don't close html
			if ( 'html' != $field['type'] && ( ! isset( $field['pseudo'] ) || ! $field['pseudo'] ) ) {
				$output_header .= '<div class="exopite-sof-field exopite-sof-field-' . $field['type'] . $wrap_class . $hidden . '"' . $depend . '>';
			}

			if ( isset( $field['title'] ) ) {

				$output_header .= '<h4 class="exopite-sof-title">';

				$output_header .= $field['title'];

				if ( ! empty( $field['description'] ) ) {
					$output_header .= '<p class="exopite-sof-description">' . $field['description'] . '</p>';
				}

				$output_header .= '</h4>'; // exopite-sof-title
				$output_header .= '<div class="exopite-sof-fieldset">';
			}

			if ( class_exists( $class ) ) {

				if ( empty( $value ) && 0 !== $value && '0' !== $value ) {

					// NEW

					if ( ( isset( $field['sub'] ) && ! empty( $field['sub'] ) ) || $this->is_menu ) {
						$value = $this->wldcfwc_get_value( $this->db_options, $field );
					}
				}

				// clear output buffer for field html
				ob_start();
				// field's class
				$element = new $class( $field, $value, $this->unique, $this->config, false );
				// $output_field is already escaped by its respective class
				$element->wldcfwc_output();
				// capture output buffer which is now the field's escaped html
				// see https://developer.wordpress.org/apis/security/escaping/#toc_4
				$output_field_escaped .= ob_get_clean();

			} else {

				$output_header        .= '<div class="danger unknown">';
				$output_field_escaped .= esc_attr__( 'ERROR:', 'wishlist-dot-com-for-woocommerce' ) . ' ';
				$output_field_escaped .= esc_attr__( 'This field class is not available!', 'wishlist-dot-com-for-woocommerce' );
				$output_field_escaped .= ' <i>(' . $field['type'] . ')</i>';
				$output_footer        .= '</div>';

			}

			if ( isset( $field['title'] ) ) {
				$output_footer .= '</div>';
			} // exopite-sof-fieldset

			// WLDC adding case $field['type'] != 'html' so we don't close html
			if ( 'html' != $field['type'] && ( ! isset( $field['pseudo'] ) || ! $field['pseudo'] ) ) {
				$output_footer .= '<div class="clearfix"></div>';
				$output_footer .= '</div>'; // exopite-sof-field
			}

			do_action( 'exopite_sof_after_generate_field', $field, $this->config );

			// escape header
			$output_header_escaped = wp_kses( $output_header, $allowed_html_tags );
			// escape footer
			$output_footer_escaped = wp_kses( $output_footer, $allowed_html_tags );

			// $output_field_escaped is already escaped by its respective class
			// see https://developer.wordpress.org/apis/security/escaping/#toc_4
			$output_escaped = $output_header_escaped . $output_field_escaped . $output_footer_escaped;

			//even though $output_escaped is already escaped, we're using wp_kses() again to pass PHPCS sniffer ruleset for WordPress and WooCommerce
			echo wp_kses( $output_escaped, $allowed_html_tags );

			do_action( 'exopite_sof_after_add_field', $field, $this->config );
		}

		public function wldcfwc_get_value( $options, $field ) {

			$value = null;

			if ( ! isset( $field['id'] ) ) {
				return $value;
			}

			if ( isset( $options[ $this->lang_current ][ $field['id'] ] ) ) {

				$value = $options[ $this->lang_current ][ $field['id'] ];

			} elseif ( null === $value && isset( $options[ $field['id'] ] ) ) {

				$value = $options[ $field['id'] ];

			}

			$value_sanitized = $this->wldcfwc_sanitize_option_value( $value );
			$value_sanitized = $value;

			/**
			 * Use this filter, like: add_filter( 'exopite_sof_field_value', 'prefix_exopite_sof_field_value', 10, 5 );
			 * public function prefix_exopite_sof_field_value( $value, $unique, $options, $field ) {
			 *
			 *       If ( $unique == $this->plugin_name && $field['id'] == 'your-field-id' ) {
			 *           // do the magic ;)
			 *       }
			 *
			 *       return $value;
			 * }
			 */
			return apply_filters( 'exopite_sof_field_value', $value_sanitized, $this->unique, $options, $field );
		}

		/**
		 * Display form and header for options page
		 */
		public function wldcfwc_display_options_page_header() {

			// get current saved options
			$options_saved = get_option( WLDCFWC_SLUG );

			//begin_leave_review
			$review_url = 'https://wordpress.org/support/plugin/' . WLDCFWC_SLUG . '/reviews/#new-post';
			echo '<div class="wldcfwc-header-review-request">';
			printf(
				'%s <a class="wldcfwc-link-no-decoration" href="%s" target="_blank"><u>%s</u>&nbsp;<span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-star-filled"></span></a>  %s',
				esc_html__( 'Please help us keep this plugin free.', 'wishlist-dot-com-for-woocommerce' ),
				esc_url( $review_url ),
				esc_html__( 'Leave a good review', 'wishlist-dot-com-for-woocommerce' ),
				esc_html__( 'Thank you! :)', 'wishlist-dot-com-for-woocommerce' )
			);
			echo '</div>';
			//end_leave_review

			echo '<form method="post" action="options.php" enctype="multipart/form-data" name="' . esc_attr( $this->unique ) . '" class="exopite-sof-form-js ' . esc_attr( $this->unique ) . '-form" data-save="' . esc_html__( 'Saving...', 'wishlist-dot-com-for-woocommerce' ) . '" data-saved="' . esc_attr__( 'Saved Successfully.', 'wishlist-dot-com-for-woocommerce' ) . '">';

			settings_fields( $this->unique );
			do_settings_sections( $this->unique );

			$option_title = ( ! empty( $this->config['option_title'] ) ) ? $this->config['option_title'] : $this->config['title'];

			echo '<header class="exopite-sof-header exopite-sof-header-js">';

			echo '<img class="" src="' . esc_attr( plugin_dir_url( __FILE__ ) ) . '../images/WishListIcon-48.png">';

			echo '<h1>' . esc_attr( $option_title ) . '</h1>';

			echo '</header>';


			$live_status_code = $this->options_all['wlcom_plgn_plugin_display_status'];
			if ( 'status_live' == $live_status_code ) {
				$live_status_css = 'wldcfwc-hide';
			} else {
				$live_status_css = '';
			}
			$valid_api_key = $this->options_all['valid_api_key'];
			if ( 'yes_str' == $valid_api_key ) {
				$connect_status_css = 'wldcfwc-hide';
			} else {
				$connect_status_css = '';
			}
			echo '<div class="exopite-sof-section-header-message-container"><div id="display_status_prompt" class="exopite-sof-section-header-message exopite-sof-section-header-message-warning exopite-sof-inline-block ' . esc_attr($live_status_css) . '">' . esc_html__( 'Your WishList is not "Live".', 'wishlist-dot-com-for-woocommerce' ) . ' <a class="exopite-sof-warning-font-color change_display_status_link" href="#">(change)</a></div><div id="connected_status_prompt" class="exopite-sof-section-header-message exopite-sof-section-header-message-warning exopite-sof-inline-block ' . esc_attr($connect_status_css) . '">' . esc_html__( 'Your store is not connected to WishList.com.', 'wishlist-dot-com-for-woocommerce' ) . ' <a class="exopite-sof-warning-font-color change_display_status_link" href="#">(change)</a></div></div>';

		}

		/**
		 * Display form and footer for options page
		 */
		public function wldcfwc_display_options_page_footer() {

			// WLDC moved to here from header
			echo '<div class="exopite-sof-submit-button-div">';
			submit_button(
				esc_attr__( 'Save Settings', 'wishlist-dot-com-for-woocommerce' ), 'primary wldcfwc-hide exopite-sof-submit-button-js exopite-sof-submit-button', 'wishlist-dot-com-for-woocommerce-save-id', false, array() );
			echo '<span class="exopite-sof-ajax-message"></span></div>';

			// fixed button
			echo '<div id="wldcfwc-button-div-fixed-id" class="exopite-sof-submit-button-div-fixed wldcfwc-hide">';
			submit_button( esc_attr__( 'Save Settings', 'wishlist-dot-com-for-woocommerce' ), 'primary exopite-sof-submit-button-js exopite-sof-submit-button', 'wishlist-dot-com-for-woocommerce-fixed-save', false, array() );
			echo '<span class="exopite-sof-ajax-message"></span></div>';

			echo '<footer class="exopite-sof-footer-js exopite-sof-footer">';

			echo '</footer>';

			echo '</form>';
		}

		/**
		 * Display section headers, only first is visible on start
		 */
		public function wldcfwc_display_options_section_header( $section ) {

			// called by wldcfwc_loop_fields(). all tabs have a header section that's hidden if not active

			$section_name = ( isset( $section['name'] ) ) ? $section['name'] : '';
			$section_icon = ( isset( $section['icon'] ) ) ? $section['icon'] : '';

			// nonce not required since we're not processing the $_GET variable, and there's no security implications
			if ( ! isset( $_GET['section'] ) && reset( $this->fields ) === $section ) {
				// first header is visible, even when $_GET['section'] isn't set
				$visibility = '';
			} elseif ( isset( $_GET['section'] ) && $_GET['section'] == $section_name ) {
				// selected section is visible
				$visibility = '';
			} else {
				$visibility = ' hide';
			}

			echo '<div class="exopite-sof-section exopite-sof-section-' . esc_attr( $section_name ) . esc_attr( $visibility ) . '">';

			// WLDC
			$section_title = '';
			if ( isset( $section['section_title'] ) && ! empty( $section['section_title'] ) ) {
				$section_title = $section['section_title'];
			} elseif ( isset( $section['title'] ) && ! empty( $section['title'] ) ) {
				$section_title = $section['title'];
			}
			if ( ! empty( $section_title ) ) {
				$icon_before = '';
				if ( strpos( $section_icon, 'dashicon' ) !== false ) {
					$icon_before = 'dashicons-before ';
				} elseif ( strpos( $section_icon, 'fa' ) !== false ) {
					$icon_before = 'fa-before ';
				}

				echo '<h2 class="exopite-sof-section-header" data-section="' . esc_attr( $section_name ) . '"><span class="' . esc_attr( $icon_before ) . esc_attr( $section_icon ) . '"></span>' . esc_attr( $section_title ) . '</h2>';
			}
		}

		/**
		 * Display section footer
		 */
		public function wldcfwc_display_options_section_footer() {

			echo '</div>'; // exopite-sof-section
		}

		public function wldcfwc_get_fields() {

			if ( ! $this->elements ) {
				return;
			}

			$fields = array();

			foreach ( $this->elements as $key => $value ) {

				if ( isset( $value['sections'] ) ) {

					foreach ( $value['sections'] as $section ) {

						if ( isset( $section['fields'] ) ) {
							$fields[] = $section;
						}
					}
				} elseif ( isset( $value['fields'] ) ) {

						$fields[] = $value;
				}
			}

			$this->fields = $fields;
		}

		public function wldcfwc_get_menu_item_icons( $section ) {

			if ( isset( $section['icon'] ) && strpos( $section['icon'], 'dashicon' ) !== false ) {
				echo '<span class="exopite-sof-nav-icon dashicons-before ' . esc_attr( $section['icon'] ) . '"></span>';
			} elseif ( isset( $section['icon'] ) && strpos( $section['icon'], 'fa' ) !== false ) {
				echo '<span class="exopite-sof-nav-icon fa-before ' . esc_attr( $section['icon'] ) . '" aria-hidden="true"></span>';
			}
		}

		// WLDC setting default for $force_hidden = null
		public function wldcfwc_get_menu_item( $section, $active = '', $force_hidden = null ) {

			$depend = '';
			$hidden = ( $force_hidden ) ? ' hidden' : '';

			// Dependency for tabs too
			if ( ! empty( $section['dependency'] ) ) {
				$hidden  = ' hidden';
				$depend  = ' data-controller="' . $section['dependency'][0] . '"';
				$depend .= ' data-condition="' . $section['dependency'][1] . '"';
				$depend .= ' data-value="' . $section['dependency'][2] . '"';
			}

			// WLDC adding tab css
			if ( ! empty( $section['section_tab_css'] ) ) {
				$section_tab_css = ' ' . $section['section_tab_css'];
			} else {
				$section_tab_css = ' ';
			}

			$section_name = ( isset( $section['name'] ) ) ? $section['name'] : '';

			echo '<li  class="exopite-sof-nav-list-item nav-tab' . esc_attr( $section_tab_css ) . esc_attr( $active ) . esc_attr( $hidden ) . '"' . esc_attr( $depend ) . ' data-section="' . esc_attr( $section_name ) . '">';
			echo '<span class="exopite-sof-nav-list-item-title">';
			$this->wldcfwc_get_menu_item_icons( $section );
			echo esc_attr( $section['title'] );
			echo '</span>';
			echo '</li>';
		}

		public function wldcfwc_get_menu() {

			echo '<div class="exopite-sof-nav"><ul class="exopite-sof-nav-list nav-tab-wrapper">';

			foreach ( $this->elements as $key => $value ) {

				$active = '';
				reset( $this->elements );
				// WLDC initial active set by section param and by js. scripts.js, onload:
				// nonce not required since we're not processing the $_GET variable, and there's no security implications
				if ( ( isset( $_GET['section'] ) && $_GET['section'] == $value['name'] ) || ( ! isset( $_GET['section'] ) && 0 == $key ) ) {
					$active = ' active';
				}

				if ( isset( $value['sections'] ) ) {

					echo '<li  class="exopite-sof-nav-list-parent-item' . esc_attr( $active ) . '">';
					echo '<span class="exopite-sof-nav-list-item-title">';
					$this->wldcfwc_get_menu_item_icons( $value );
					echo esc_attr( $value['title'] );
					echo '</span>';
					echo '<ul style="display:none;">';

					foreach ( $value['sections'] as $section ) {

						if ( isset( $section['fields'] ) ) {

								$this->wldcfwc_get_menu_item( $section, $active, false );

						}
					}

					echo '</ul>';
					echo '</li>';

				} elseif ( isset( $value['fields'] ) ) {

						$this->wldcfwc_get_menu_item( $value, $active, false );
				}
			}

			echo '</ul></div>';
		}
		/**
		 * Display form for either options page
		 */
		public function wldcfwc_display_page() {

			do_action( 'exopite_simple_options_framework_form_' . $this->config['type'] . '_before' );

			settings_errors();

			$review_url = 'https://wordpress.org/support/plugin/' . WLDCFWC_SLUG . '/reviews/#new-post';

			echo '<div class="exopite-sof-wrapper exopite-sof-wrapper-' . esc_attr( $this->config['type'] ) . ' ' . esc_attr( $this->unique ) . '-options">';

			add_action(
				'exopite_sof_display_page_header',
				array(
					$this,
					'wldcfwc_display_options_page_header',
				),
				10,
				1
			);
			do_action( 'exopite_sof_display_page_header', $this->config );

			$sections = count( $this->fields );

			$tabbed = ( $sections > 1 && $this->config['tabbed'] ) ? ' exopite-sof-content-nav exopite-sof-content-js' : '';

			/**
			 * Generate fields
			 */
			// Generate tab navigation
			echo '<div class="exopite-sof-content' . esc_attr( $tabbed ) . '">';

			if ( ! empty( $tabbed ) ) {

				$this->wldcfwc_get_menu();

			}

			echo '<div class="exopite-sof-sections">';

			// Generate fields
			$callbacks = array(
				'before' => 'wldcfwc_display_options_section_header',
				'main'   => 'wldcfwc_add_field',
				'after'  => 'wldcfwc_display_options_section_footer',
			);

			$this->wldcfwc_loop_fields( $callbacks );

			echo '</div>'; // sections
			echo '</div>'; // content
			if ( 'menu' == $this->config['type'] ) {

				add_action(
					'exopite_sof_display_page_footer',
					array(
						$this,
						'wldcfwc_display_options_page_footer',
					),
					10,
					1
				);
				do_action( 'exopite_sof_display_page_footer', $this->config );

			}

			echo '</div>';

			do_action( 'exopite_sof_form_' . $this->config['type'] . '_after' );
		} // wldcfwc_display_page()
	} //class

endif;
