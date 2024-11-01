<?php
/**
 * Class SSF_Custom_Hook_Post_Type
 *
 * @package Swap Snow Fall
 */

define( 'SSF_ADVANCED_HOOKS_DIR', SSF_DIR . 'custom-hook/includes/' );
define( 'SSF_ADVANCED_HOOKS_URL', SSF_URL . 'custom-hook/includes/' );

/**
 * Class managing the template areas post type.
 */
class SSF_Custom_Hook_Post_Type {

	const SLUG = 'ssf_particle_hook';

	/**
	 * Instance Control
	 *
	 * @var null
	 */
	private static $instance = null;

	/**
	 * Current condition
	 *
	 * @var null
	 */
	public static $current_condition = null;

	/**
	 * Holds post types.
	 *
	 * @var values of all the post types.
	 */
	protected static $post_types = null;

	/**
	 * Holds ignore post types.
	 *
	 * @var values of all the post types.
	 */
	protected static $public_ignore_post_types = null;

	/**
	 * Instance Control.
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor function.
	 */
	public function __construct() {
		add_action( 'wp', array( $this, 'load_markup' ), 1 );
		add_action( 'init', array( $this, 'register_post_type' ), 1 );
		add_action( 'init', array( $this, 'plugin_register' ), 20 );
		add_action( 'init', array( $this, 'register_meta' ), 20 );
		add_action( 'enqueue_block_editor_assets', array( $this, 'assets_enqueue' ) );
		add_action( 'admin_init', array( $this, 'add_editor_styles' ) );
	}

	/**
	 * Registers an editor stylesheet for the theme.
	 */
	public function add_editor_styles() {

		$post_type = get_post_type();
		if ( self::SLUG !== $post_type ) {
			return;
		}

	}

	/**
	 * Get an array of post meta.
	 *
	 * @param object $post the current element to check.
	 * @return array
	 */
	public function get_post_meta_array( $post ) {
		$meta = array(
			'hook'             => '',
			'specific_hook'    => '',
			'show'             => array(),
			'specific_ids'     => '',
			'bg_color'         => '',
			'color'            => '#ffffff',
			'shape'            => 'circle',
			'particle_numbers' => 100,
			'height'           => 400,
		);

		$particle_number = get_post_meta( $post->ID, '_ssf_particle_numbers', true );
		if ( ! empty( $particle_number ) ) {
			$meta['particle_numbers'] = get_post_meta( $post->ID, '_ssf_particle_numbers', true );
		}

		$height = get_post_meta( $post->ID, '_ssf_height', true );
		if ( ! empty( $height ) ) {
			$meta['height'] = get_post_meta( $post->ID, '_ssf_height', true );
		}

		if ( '' !== get_post_meta( $post->ID, '_ssf_shape', true ) ) {
			$meta['shape'] = get_post_meta( $post->ID, '_ssf_shape', true );
		}
		if ( '' !== get_post_meta( $post->ID, '_ssf_color', true ) ) {
			$meta['color'] = get_post_meta( $post->ID, '_ssf_color', true );
		}
		if ( '' !== get_post_meta( $post->ID, '_ssf_bg_color', true ) ) {
			$meta['bg_color'] = get_post_meta( $post->ID, '_ssf_bg_color', true );
		}
		if ( get_post_meta( $post->ID, '_ssf_display_rule', true ) ) {
			$meta['show'] = get_post_meta( $post->ID, '_ssf_display_rule', true );
		}
		if ( get_post_meta( $post->ID, '_ssf_specific_ids', true ) ) {
			$meta['specific_ids'] = get_post_meta( $post->ID, '_ssf_specific_ids', true );
		}
		if ( get_post_meta( $post->ID, '_ssf_hook', true ) ) {
			$meta['hook'] = get_post_meta( $post->ID, '_ssf_hook', true );
		}
		if ( get_post_meta( $post->ID, '_ssf_specific_hook', true ) ) {
			$meta['specific_hook'] = get_post_meta( $post->ID, '_ssf_specific_hook', true );
		}
		return $meta;
	}

	/**
	 * Load frontend markup.
	 *
	 * @return void
	 */
	public function load_markup() {
		if ( is_admin() || is_singular( self::SLUG ) ) {
			return;
		}
		$args = array(
			'post_type'              => self::SLUG,
			'no_found_rows'          => true,
			'update_post_term_cache' => false,
			'post_status'            => 'publish',
			'numberposts'            => 333,
			'order'                  => 'ASC',
			'orderby'                => 'menu_order',
			'suppress_filters'       => false,
		);

		$posts = get_posts( $args );

		foreach ( $posts as $post ) {
			$meta = $this->get_post_meta_array( $post );
			if ( apply_filters( 'ssf_element_display', $this->check_element_conditionals( $post, $meta ), $post, $meta ) ) {

				$post_id  = $post->ID;
				$priority = 10;

				if ( ! empty( $meta['hook'] ) ) {
					$hook_name = 'add_custom_hook' === $meta['hook'] ? isset( $meta['specific_hook'] ) ? $meta['specific_hook'] : '' : $meta['hook'];
					add_action(
						$hook_name,
						function() use ( $post_id ) {
							echo "<div id='" . esc_attr( "ssf-particle-hook-{$post_id}" ) . "'><div id='" . esc_attr( "ssf-ph-wrap-{$post_id}" ) . "'>";
								$page_builder_base_instance = SSF_Page_Builder_Compatibility::get_instance();
								$page_builder_instance      = $page_builder_base_instance->get_active_page_builder( $post_id );
								$page_builder_instance->render_content( $post_id );
							echo '</div></div>';
						},
						$priority
					);

					add_action(
						'wp_print_styles',
						function() use ( $post_id, $meta ) {
							$bg_color   = ! empty( $meta['bg_color'] ) ? $meta['bg_color'] : 'inherit';
							$get_height = ! empty( $meta['height'] ) ? $meta['height'] : 400;

							$css = "#ssf-particle-hook-{$post_id} { background: {$bg_color}; position: relative; } #ssf-ph-wrap-{$post_id} { position: absolute; top: 50px; width: 100%; padding: 2em; } #ssf-particle-hook-{$post_id} canvas { height: {$get_height}px !important }"

							?>
								<style id="ssf-hook-css-<?php echo $post_id; ?>"><?php echo $css; ?></style>
							<?php
						}
					);

					$dir_name    = ( SCRIPT_DEBUG ) ? 'unminified' : 'minified';
					$file_prefix = ( SCRIPT_DEBUG ) ? '' : '.min';

					// Enqeue Particle JS.
					wp_enqueue_script( 'ssf-frontend-particle-script', SSF_URL . 'assets/js/' . $dir_name . '/particles' . $file_prefix . '.js', array(), SSF_VER );

					$json = '{
						"particles": {
						  "number": {
							"value": "' . $meta['particle_numbers'] . '"
						  },
						  "color": {
							"value": "' . $meta['color'] . '"
						  },
						  "shape": {
							"type": "' . $meta['shape'] . '"
						  },
						  "size": {
							"value": 5,
							"random": true
						  },
						  "line_linked": {
							"enable": false
						  },
						  "move": {
							"enable": true,
							"speed": 2,
							"direction": "bottom",
							"straight": false
						  }
						},
						"interactivity": {
						  "detect_on": "canvas",
						  "events": {
							"onhover": {
							  "enable": false
							}
						  },
						  "modes": {
							"push": {
							  "particles_nb": 0
							}
						  }
						}
					  }';

					$js = 'document.addEventListener("DOMContentLoaded", function () {
						particlesJS("ssf-particle-hook-' . $post_id . '", ' . apply_filters( "ssf_particle_json_{$post_id}", $json ) . ');
					});';

					wp_add_inline_script( 'ssf-frontend-particle-script', $js );
				}
			}
		}
	}

	/**
	 * Check if element should show in current page.
	 *
	 * @param object $post the current element to check.
	 * @param array  $meta The meta of current post type.
	 * @return bool
	 */
	public function check_element_conditionals( $post, $meta ) {
		$current_condition = self::get_current_page_conditions();
		$show              = false;
		if ( ! empty( $meta['show'] ) ) {
			if ( 'specific_ids' === $meta['show'] && ! empty( $meta['specific_ids'] ) ) {
				global $post;

				if ( strpos( $meta['specific_ids'], ',' ) !== false ) {
					$ids_arr = explode( ',', $meta['specific_ids'] );
					if ( in_array( $post->ID, $ids_arr ) ) { // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
						$show = true;
					}
				} else {
					if ( (int) $meta['specific_ids'] === $post->ID ) {
						$show = true;
					}
				}
				return $show;
			}
			if ( in_array( $meta['show'], $current_condition, true ) ) {
				$show = true;
			}
		}
		return $show;
	}

	/**
	 * Gets and returns page conditions.
	 */
	public static function get_current_page_conditions() {
		if ( is_null( self::$current_condition ) ) {
			$condition = array( 'general|site' );
			if ( is_front_page() ) {
				$condition[] = 'general|front_page';
			}
			if ( is_home() ) {
				$condition[] = 'general|archive';
				$condition[] = 'post_type_archive|post';
				$condition[] = 'general|home';
			} elseif ( is_search() ) {
				$condition[] = 'general|search';
			} elseif ( is_404() ) {
				$condition[] = 'general|404';
			} elseif ( is_singular() ) {
				$condition[] = 'general|singular';
				$condition[] = 'singular|' . get_post_type();
			} elseif ( is_archive() ) {
				$queried_obj = get_queried_object();
				$condition[] = 'general|archive';
				if ( is_post_type_archive() && is_object( $queried_obj ) ) {
					$condition[] = 'post_type_archive|' . $queried_obj->name;
				} elseif ( is_tax() || is_category() || is_tag() ) {
					if ( is_object( $queried_obj ) ) {
						$condition[] = 'tax_archive|' . $queried_obj->taxonomy;
					}
				} elseif ( is_date() ) {
					$condition[] = 'general|date';
				} elseif ( is_author() ) {
					$condition[] = 'general|author';
				}
			}
			self::$current_condition = $condition;
		}
		return self::$current_condition;
	}

	/**
	 * Get all Display Options
	 */
	public function get_display_options() {
		$display_general = array(
			array(
				'label'   => esc_attr__( 'General', 'swap-snow-fall' ),
				'options' => array(
					array(
						'value' => 'general|site',
						'label' => esc_attr__( 'Entire Site', 'swap-snow-fall' ),
					),
					array(
						'value' => 'general|singular',
						'label' => esc_attr__( 'All Singular', 'swap-snow-fall' ),
					),
					array(
						'value' => 'general|archive',
						'label' => esc_attr__( 'All Archives', 'swap-snow-fall' ),
					),
				),
			),
		);

		$display_special = array(
			array(
				'label'   => esc_attr__( 'Special', 'swap-snow-fall' ),
				'options' => array(
					array(
						'value' => 'general|front_page',
						'label' => esc_attr__( 'Front Page', 'swap-snow-fall' ),
					),
					array(
						'value' => 'general|home',
						'label' => esc_attr__( 'Blog Page', 'swap-snow-fall' ),
					),
					array(
						'value' => 'general|search',
						'label' => esc_attr__( 'Search Results', 'swap-snow-fall' ),
					),
					array(
						'value' => 'general|404',
						'label' => esc_attr__( 'Not Found (404)', 'swap-snow-fall' ),
					),
					array(
						'value' => 'general|author',
						'label' => esc_attr__( 'Author Archives', 'swap-snow-fall' ),
					),
					array(
						'value' => 'general|date',
						'label' => esc_attr__( 'Date Archives', 'swap-snow-fall' ),
					),
				),
			),
		);

		$ssf_public_post_types = $this->get_post_types();
		$ignore_types          = $this->get_public_post_types_to_ignore();
		$display_singular      = array();
		foreach ( $ssf_public_post_types as $post_type ) {

			if ( 'ssf_particle_hook' === $post_type ) {
				continue;
			}

			$post_type_item         = get_post_type_object( $post_type );
			$post_type_name         = $post_type_item->name;
			$post_type_label        = $post_type_item->label;
			$post_type_label_plural = $post_type_item->labels->name;
			if ( ! in_array( $post_type_name, $ignore_types, true ) ) {
				$post_type_options     = array(
					array(
						'value' => 'singular|' . $post_type_name,
						'label' => esc_attr__( 'Single', 'swap-snow-fall' ) . ' ' . $post_type_label_plural,
					),
				);
				$post_type_tax_objects = get_object_taxonomies( $post_type, 'objects' );
				foreach ( $post_type_tax_objects as $taxonomy_slug => $taxonomy ) {
					if ( $taxonomy->public && $taxonomy->show_ui && 'post_format' !== $taxonomy_slug ) {
						$post_type_options[] = array(
							'value' => 'tax_archive|' . $taxonomy_slug,
							/* translators: %1$s: taxonomy singular label.  */
							'label' => sprintf( esc_attr__( '%1$s Archives', 'swap-snow-fall' ), $taxonomy->labels->singular_name ),
						);
					}
				}
				if ( ! empty( $post_type_item->has_archive ) ) {
					$post_type_options[] = array(
						'value' => 'post_type_archive|' . $post_type_name,
						/* translators: %1$s: post type plural label  */
						'label' => sprintf( esc_attr__( '%1$s Archive', 'swap-snow-fall' ), $post_type_label_plural ),
					);
				}
				$display_singular[] = array(
					'label'   => $post_type_label,
					'options' => $post_type_options,
				);
			}
		}

		$specific_posts = array(
			array(
				'label'   => esc_attr__( 'Specific Target', 'swap-snow-fall' ),
				'options' => array(
					array(
						'value' => 'specific_ids',
						'label' => esc_attr__( 'Specific Pages / Posts / Taxonomies, etc.', 'swap-snow-fall' ),
					),
				),
			),
		);

		$display_options = array_merge( $display_general, $display_special );
		$display_options = array_merge( $display_options, $display_singular );
		$display_options = array_merge( $display_options, $specific_posts );
		return apply_filters( 'ssf_element_display_options', $display_options );
	}

	/**
	 * Enqueue Script for Meta options
	 */
	public function assets_enqueue() {
		$post_type = get_post_type();
		if ( self::SLUG !== $post_type ) {
			return;
		}
		wp_enqueue_script( 'ssf-particle-meta' );
		wp_localize_script(
			'ssf-particle-meta',
			'ssfMetaParams',
			array(
				'hooks'   => $this->get_hook_options(),
				'display' => $this->get_display_options(),
				'shape'   => $this->get_shape_options(),
			)
		);
		$css_path = SSF_URL . 'assets/admin/css/ssf-editor-style.css';
		wp_enqueue_style( 'ssf-block-editor-styles', $css_path, false, SSF_VER, 'all' );
		$bg_color_meta = get_post_meta( get_the_ID(), '_ssf_bg_color', true ) ? get_post_meta( get_the_ID(), '_ssf_bg_color', true ) : '#ffffff';
		$custom_css    = "
                :root {
					--ssf-editor-bg-color: {$bg_color_meta};
                }";
		wp_add_inline_style( 'ssf-block-editor-styles', $custom_css );
	}

	/**
	 * Register Script for Meta options
	 */
	public function plugin_register() {
		$path = SSF_URL . 'custom-hook/react/build/';
		wp_register_script(
			'ssf-particle-meta',
			$path . 'index.js',
			array( 'wp-plugins', 'wp-edit-post', 'wp-element' ),
			SSF_VER
		);
	}
	/**
	 * Register Post Meta options
	 */
	public function register_meta() {
		register_post_meta(
			self::SLUG,
			'_ssf_display_rule',
			array(
				'show_in_rest'  => true,
				'single'        => true,
				'type'          => 'string',
				'auth_callback' => '__return_true',
			)
		);
		register_post_meta(
			self::SLUG,
			'_ssf_specific_ids',
			array(
				'show_in_rest'  => true,
				'single'        => true,
				'type'          => 'string',
				'auth_callback' => '__return_true',
			)
		);
		register_post_meta(
			self::SLUG,
			'_ssf_hook',
			array(
				'show_in_rest'  => true,
				'single'        => true,
				'type'          => 'string',
				'auth_callback' => '__return_true',
			)
		);
		register_post_meta(
			self::SLUG,
			'_ssf_specific_hook',
			array(
				'show_in_rest'  => true,
				'single'        => true,
				'type'          => 'string',
				'auth_callback' => '__return_true',
			)
		);
		register_post_meta(
			self::SLUG,
			'_ssf_color',
			array(
				'show_in_rest'  => true,
				'single'        => true,
				'type'          => 'string',
				'auth_callback' => '__return_true',
			)
		);
		register_post_meta(
			self::SLUG,
			'_ssf_bg_color',
			array(
				'show_in_rest'  => true,
				'single'        => true,
				'type'          => 'string',
				'auth_callback' => '__return_true',
			)
		);
		register_post_meta(
			self::SLUG,
			'_ssf_shape',
			array(
				'show_in_rest'  => true,
				'single'        => true,
				'type'          => 'string',
				'auth_callback' => '__return_true',
			)
		);
		register_post_meta(
			self::SLUG,
			'_ssf_particle_numbers',
			array(
				'show_in_rest'  => true,
				'single'        => true,
				'type'          => 'number',
				'auth_callback' => '__return_true',
			)
		);
		register_post_meta(
			self::SLUG,
			'_ssf_height',
			array(
				'show_in_rest'  => true,
				'single'        => true,
				'type'          => 'number',
				'auth_callback' => '__return_true',
			)
		);
	}

	/**
	 * Get all public post types.
	 *
	 * @return array of post types.
	 */
	public static function get_post_types() {
		if ( is_null( self::$post_types ) ) {
			$args             = array(
				'public'       => true,
				'show_in_rest' => true,
				'_builtin'     => false,
			);
			$builtin          = array(
				'post',
				'page',
			);
			$output           = 'names'; // names or objects, note names is the default.
			$operator         = 'and';
			$post_types       = get_post_types( $args, $output, $operator );
			self::$post_types = apply_filters( 'ssf_public_post_type_array', array_merge( $builtin, $post_types ) );
		}

		return self::$post_types;
	}

	/**
	 * Get array of post types we want to exclude from use in non public areas.
	 *
	 * @return array of post types.
	 */
	public static function get_public_post_types_to_ignore() {
		if ( is_null( self::$public_ignore_post_types ) ) {
			$public_ignore_post_types       = array(
				'elementor_library',
				'fl-theme-layout',
				'shop_order',
				'ele-product-template',
				'ele-p-arch-template',
				'ele-p-loop-template',
				'ele-check-template',
				'jet-menu',
				'jet-popup',
				'jet-smart-filters',
				'jet-theme-core',
				'jet-woo-builder',
				'jet-engine',
				'llms_certificate',
				'llms_my_certificate',
				'sfwd-certificates',
				'sfwd-transactions',
				'reply',
			);
			self::$public_ignore_post_types = apply_filters( 'ssf_public_post_type_ignore_array', $public_ignore_post_types );
		}

		return self::$public_ignore_post_types;
	}

	/**
	 * Registers the block areas post type.
	 *
	 * @since 0.1.0
	 */
	public function register_post_type() {
		$labels = array(
			'name'                  => __( 'Particle Hooks', 'swap-snow-fall' ),
			'singular_name'         => __( 'Particle Hook', 'swap-snow-fall' ),
			'menu_name'             => _x( 'Particle Hook', 'Admin Menu text', 'swap-snow-fall' ),
			'add_new'               => _x( 'Add New', 'Element', 'swap-snow-fall' ),
			'add_new_item'          => __( 'Add New Particle Hook', 'swap-snow-fall' ),
			'new_item'              => __( 'New Particle Hook', 'swap-snow-fall' ),
			'edit_item'             => __( 'Edit Particle Hook', 'swap-snow-fall' ),
			'view_item'             => __( 'View Particle Hook', 'swap-snow-fall' ),
			'all_items'             => __( 'All Particle Hooks', 'swap-snow-fall' ),
			'search_items'          => __( 'Search Particle Hooks', 'swap-snow-fall' ),
			'parent_item_colon'     => __( 'Parent Particle Hook:', 'swap-snow-fall' ),
			'not_found'             => __( 'No Particle Hooks found.', 'swap-snow-fall' ),
			'not_found_in_trash'    => __( 'No Particle Hooks found in Trash.', 'swap-snow-fall' ),
			'archives'              => __( 'Particle Hook archives', 'swap-snow-fall' ),
			'insert_into_item'      => __( 'Insert into Particle Hook', 'swap-snow-fall' ),
			'uploaded_to_this_item' => __( 'Uploaded to this Particle Hook', 'swap-snow-fall' ),
			'filter_items_list'     => __( 'Filter Particle Hooks list', 'swap-snow-fall' ),
			'items_list_navigation' => __( 'Particle Hooks list navigation', 'swap-snow-fall' ),
			'items_list'            => __( 'Particle Hooks list', 'swap-snow-fall' ),
		);

		$args = array(
			'labels'              => $labels,
			'description'         => __( 'Particle Hook areas to include in your site.', 'swap-snow-fall' ),
			'public'              => true,
			'publicly_queryable'  => true,
			'has_archive'         => false,
			'exclude_from_search' => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_nav_menus'   => false,
			'show_in_admin_bar'   => false,
			'can_export'          => true,
			'show_in_rest'        => true,
			'rewrite'             => false,
			'capability_type'     => 'post',
			'map_meta_cap'        => true,
			'menu_icon'           => 'dashicons-image-filter',
			'supports'            => apply_filters(
				'ssf_advanced_hooks_supports',
				array(
					'title',
					'editor',
					'custom-fields',
					'revisions',
					'elementor',
				)
			),
		);

		register_post_type( self::SLUG, $args );
	}

	/**
	 * Get all Shape Options
	 */
	public function get_shape_options() {
		/**
		 * Filter for the 'Hooks' in Custom Layouts selection.
		 *
		 * @since 2.0.0
		 */

		$shape_options = array(
			array(
				'label'   => esc_attr__( 'Type Of Shape', 'swap-snow-fall' ),
				'options' => array(
					array(
						'value' => 'circle',
						'label' => esc_attr__( 'Circle', 'swap-snow-fall' ),
					),
					array(
						'value' => 'star',
						'label' => esc_attr__( 'Star', 'swap-snow-fall' ),
					),
					array(
						'value' => 'triangle',
						'label' => esc_attr__( 'Triangle', 'swap-snow-fall' ),
					),
					array(
						'value' => 'polygon',
						'label' => esc_attr__( 'Polygon', 'swap-snow-fall' ),
					),
					array(
						'value' => 'edge',
						'label' => esc_attr__( 'Edge', 'swap-snow-fall' ),
					),
				),
			),
		);

		return apply_filters( 'ssf_element_shape_options', $shape_options );
	}

	/**
	 * Get all Normal Hook Options
	 */
	public function get_hook_options() {
		/**
		 * Filter for the 'Hooks' in Custom Layouts selection.
		 *
		 * @since 2.0.0
		 */

		$hooks_header = array(
			array(
				'label'   => esc_attr__( 'Header', 'swap-snow-fall' ),
				'options' => array(
					array(
						'value' => 'wp_head',
						'label' => esc_attr__( 'wp_head', 'swap-snow-fall' ),
					),
				),
			),
		);

		$hooks_footer = array(
			array(
				'label'   => esc_attr__( 'Footer', 'swap-snow-fall' ),
				'options' => array(
					array(
						'value' => 'wp_footer',
						'label' => esc_attr__( 'wp_footer', 'swap-snow-fall' ),
					),
				),
			),
		);

		$custom_hook = array(
			array(
				'label'   => esc_attr__( 'Custom Hook', 'swap-snow-fall' ),
				'options' => array(
					array(
						'value' => 'add_custom_hook',
						'label' => esc_attr__( 'Insert Specific Hook', 'swap-snow-fall' ),
					),
				),
			),
		);

		$hooks = array_merge( $hooks_header, $hooks_footer );
		$hooks = array_merge( $hooks, $custom_hook );

		return apply_filters( 'ssf_element_hooks_options', $hooks );
	}
}
SSF_Custom_Hook_Post_Type::get_instance();
