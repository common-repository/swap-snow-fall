<?php
/**
 * Create Menu & Admin Page.
 *
 * @package SSF
 */

// Prohibit direct script loading.
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );

if ( ! class_exists( 'SSF_Menu' ) ) {

	/**
	 * Class SSF_Menu.
	 */
	class SSF_Menu {

		/**
		 * Holds the values to be used in the fields callbacks
		 *
		 * @var array
		 *
		 * @since 1.1.0
		 */
		private $options;

		/**
		 * Constructor
		 *
		 * @since 1.1.0
		 * @return void
		 */
		function __construct() {
			add_action( 'admin_menu', array( $this, 'add_admin_menu' ), 99 );
			add_action( 'admin_init', array( $this, 'settings_init' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_ssf_admin_style' ) );
		}

		/**
		 * Add menu to the dasboard.
		 *
		 * @since 1.1.0
		 * @return void
		 */
		function add_admin_menu() {
			add_submenu_page(
				'edit.php?post_type=ssf_particle_hook', // $parent_slug
				'Full Page Settings', // $page_title
				'Full Page Settings',        // $menu_title,
				'manage_options',           // $capability
				'swap-snow-fall',
				array( $this, 'ssf_settings_page' )
			);
		}

		/**
		 * Enqueue style/script to admin pages.
		 *
		 * @since 1.1.0
		 * @return void
		 */
		function enqueue_ssf_admin_style() {
			wp_register_style( 'ssf-admin-style', SSF_URL . '/assets/admin/css/style.css', false, SSF_VER );
			wp_enqueue_style( 'ssf-admin-style' );
			// Add the color picker css file.
			wp_enqueue_style( 'wp-color-picker' );
			wp_register_script( 'ssf-color-alpha', SSF_URL . '/assets/admin/js/wp-color-picker-alpha.js', array( 'jquery', 'wp-color-picker' ), SSF_VER, true );
			wp_register_script( 'ssf-admin-script', SSF_URL . '/assets/admin/js/script.js', array( 'jquery', 'wp-color-picker', 'ssf-color-alpha' ), SSF_VER, true );
			wp_enqueue_script( 'ssf-color-alpha' );
			wp_enqueue_script( 'ssf-admin-script' );
			wp_localize_script(
				'wp-color-picker',
				'wpColorPickerL10n',
				array(
					'clear'            => __( 'Clear', 'swap-snow-fall' ),
					'clearAriaLabel'   => __( 'Clear color', 'swap-snow-fall' ),
					'defaultString'    => __( 'Default', 'swap-snow-fall' ),
					'defaultAriaLabel' => __( 'Select default color', 'swap-snow-fall' ),
					'pick'             => __( 'Select Color', 'swap-snow-fall' ),
					'defaultLabel'     => __( 'Color value', 'swap-snow-fall' ),
				)
			);
		}


		/**
		 * Register and Add settings.
		 *
		 * @since 1.1.0
		 * @return void
		 */
		public function settings_init() {

			$ssf_settings = get_option( 'ssf_settings' );

			register_setting(
				'ssf_settings_group', // Option group.
				'ssf_settings' // Option name.
			);

			add_settings_section(
				'ssf_settings_section', // ID.
				__( 'These settings Enable the Particle Effect on Full Page. Edit these settings to control Full Page particle effects.', 'swap-snow-fall' ), // Title.
				null, // Callback.
				'swap-snow-fall' // Page.
			);

			if ( ! isset( $ssf_settings['load_disable_checkbox'] ) ) {
				add_settings_field(
					'ssf_enable_checkbox', // ID.
					__( 'Enable Snow Effect:', 'swap-snow-fall' ), // Title.
					array( $this, 'enable_checkbox_callback' ), // Callback.
					'swap-snow-fall', // Page.
					'ssf_settings_section' // Section.
				);
			}

			add_settings_field(
				'ssf_select_position_dropdown', // ID.
				__( 'Type:', 'swap-snow-fall' ), // Title.
				array( $this, 'position_dropdown_callback' ), // Callback.
				'swap-snow-fall', // Page.
				'ssf_settings_section' // Section.
			);

			if ( isset( $ssf_settings['ssf_select_position_dropdown'] ) ) {
				if ( 'above-content' === $ssf_settings['ssf_select_position_dropdown'] ) {
					$is_hidden = '';
				} else {
					$is_hidden = ' ssf-hide';
				}
			} else {
				$is_hidden = ' ssf-hide';
			}

			add_settings_field(
				'ssf_enable_mobile', // ID.
				__( 'Enable on Mobile:', 'swap-snow-fall' ), // Title.
				array( $this, 'enable_mobile_callback' ), // Callback.
				'swap-snow-fall', // Page.
				'ssf_settings_section', // Section.
				array(
					'label_for' => 'ssf-enable-mobile',
					'class'     => 'ssf-enable-mobile-row' . $is_hidden,
				)
			);

			add_settings_field(
				'ssf_particle_color', // ID.
				__( 'Particle Color:', 'swap-snow-fall' ), // Title.
				array( $this, 'particle_color_callback' ), // Callback.
				'swap-snow-fall', // Page.
				'ssf_settings_section' // Section.
			);

			add_settings_field(
				'ssf_particle_shape', // ID.
				__( 'Particle Shape:', 'swap-snow-fall' ), // Title.
				array( $this, 'particle_shape_callback' ), // Callback.
				'swap-snow-fall', // Page.
				'ssf_settings_section' // Section.
			);

			add_settings_field(
				'ssf_particle_number', // ID.
				__( 'Number of Particles:', 'swap-snow-fall' ), // Title.
				array( $this, 'particle_number_callback' ), // Callback.
				'swap-snow-fall', // Page.
				'ssf_settings_section' // Section.
			);

			add_settings_field(
				'ssf_display_rules', // ID.
				__( 'Display On:', 'swap-snow-fall' ), // Title.
				array( $this, 'display_rules_callback' ), // Callback.
				'swap-snow-fall', // Page.
				'ssf_settings_section' // Section.
			);

			add_settings_field(
				'ssf_display_rules_ids', // ID.
				'', // Title.
				array( $this, 'ssf_display_rules_ids_callback' ), // Callback.
				'swap-snow-fall', // Page.
				'ssf_settings_section' // Section.
			);

			if ( isset( $ssf_settings['load_disable_checkbox'] ) && ( true === $ssf_settings['load_disable_checkbox'] || '1' === $ssf_settings['load_disable_checkbox'] ) ) {
				add_settings_field(
					'ssf_disable_checkbox', // ID.
					__( 'Disable Snow Effect:', 'swap-snow-fall' ), // Title.
					array( $this, 'disable_checkbox_callback' ), // Callback.
					'swap-snow-fall', // Page.
					'ssf_settings_section' // Section.
				);
				add_settings_field(
					'load_disable_checkbox', // ID.
					'', // Title.
					array( $this, 'load_disable_checkbox_callback' ), // Callback.
					'swap-snow-fall', // Page.
					'ssf_settings_section' // Section.
				);
			}
		}

		/**
		 * Get the settings option array and print one of its values.
		 *
		 * @since 1.1.0
		 * @return void
		 */
		public function disable_checkbox_callback() {
			printf(
				'<input type="checkbox" name="ssf_settings[ssf_disable_checkbox]" value="1" %s />',
				isset( $this->options['ssf_disable_checkbox'] ) ? esc_attr( 'checked' ) : ''
			);
		}

		/**
		 * Hidden input field to load load_disable_checkbox and set during save.
		 *
		 * @since 2.0.0
		 * @return void
		 */
		public function load_disable_checkbox_callback() {
			printf(
				'<input type="hidden" name="ssf_settings[load_disable_checkbox]" value="1" />'
			);
		}

		/**
		 * Enable Checkbox option for new users 2.0.0 and greater versions.
		 *
		 * @since 2.0.0
		 * @return void
		 */
		public function enable_checkbox_callback() {
			printf(
				'<input type="checkbox" name="ssf_settings[ssf_enable_checkbox]" value="1" %s />',
				isset( $this->options['ssf_enable_checkbox'] ) ? esc_attr( 'checked' ) : ''
			);
		}

		/**
		 * Get the settings Enable on Mobile option array and print one of its values.
		 *
		 * @since 1.3.2
		 * @return void
		 */
		public function enable_mobile_callback() {
			printf(
				'<input type="checkbox" name="ssf_settings[ssf_enable_mobile]" value="" %s />',
				isset( $this->options['ssf_enable_mobile'] ) ? esc_attr( 'checked' ) : ''
			);
		}

		/**
		 * Get the settings option array and print one of its values.
		 *
		 * @since 1.3.0
		 * @return void
		 */
		public function position_dropdown_callback() {
			?>
				<select name="ssf_settings[ssf_select_position_dropdown]" id="ssf-position" >
					<option value="background" <?php isset( $this->options['ssf_select_position_dropdown'] ) ? selected( $this->options['ssf_select_position_dropdown'], 'background' ) : ''; ?>><?php _e( 'Particles', 'swap-snow-fall' ); ?></option>
					<option value="above-content" <?php isset( $this->options['ssf_select_position_dropdown'] ) ? selected( $this->options['ssf_select_position_dropdown'], 'above-content' ) : ''; ?>><?php _e( 'Plain Snow', 'swap-snow-fall' ); ?></option>
				</select>
			<?php
		}

		/**
		 * Get the settings option array and print one of its values.
		 *
		 * @since 1.3.0
		 * @return void
		 */
		public function display_rules_callback() {
			?>
				<select name="ssf_settings[ssf_display_rules]" id="ssf-display-rules">
						<optgroup label="Basic">
							<option <?php isset( $this->options['ssf_display_rules'] ) ? selected( $this->options['ssf_display_rules'], 'basic-global' ) : ''; ?> value="basic-global">Entire Website</option>
							<option <?php isset( $this->options['ssf_display_rules'] ) ? selected( $this->options['ssf_display_rules'], 'basic-singulars' ) : ''; ?> value="basic-singulars">All Singulars</option>
							<option <?php isset( $this->options['ssf_display_rules'] ) ? selected( $this->options['ssf_display_rules'], 'basic-archives' ) : ''; ?> value="basic-archives">All Archives</option>
						</optgroup>
						<optgroup label="Special Pages">
							<option <?php isset( $this->options['ssf_display_rules'] ) ? selected( $this->options['ssf_display_rules'], 'special-404' ) : ''; ?> value="special-404">404 Page</option>
							<option <?php isset( $this->options['ssf_display_rules'] ) ? selected( $this->options['ssf_display_rules'], 'special-search' ) : ''; ?> value="special-search">Search Page</option>
							<option <?php isset( $this->options['ssf_display_rules'] ) ? selected( $this->options['ssf_display_rules'], 'special-front' ) : ''; ?> value="special-front">Front Page</option>
							<option <?php isset( $this->options['ssf_display_rules'] ) ? selected( $this->options['ssf_display_rules'], 'special-date' ) : ''; ?> value="special-date">Date Archive</option>
							<option <?php isset( $this->options['ssf_display_rules'] ) ? selected( $this->options['ssf_display_rules'], 'special-author' ) : ''; ?> value="special-author">Author Archive</option>
						</optgroup>
						<optgroup label="Posts">
							<option <?php isset( $this->options['ssf_display_rules'] ) ? selected( $this->options['ssf_display_rules'], 'post|all' ) : ''; ?> value="post|all">All Posts</option>
							<option <?php isset( $this->options['ssf_display_rules'] ) ? selected( $this->options['ssf_display_rules'], 'post|all|archive' ) : ''; ?> value="post|all|archive">All Posts Archive</option>
							<option <?php isset( $this->options['ssf_display_rules'] ) ? selected( $this->options['ssf_display_rules'], 'post|all|taxarchive|category' ) : ''; ?> value="post|all|taxarchive|category">All Categories Archive</option>
							<option <?php isset( $this->options['ssf_display_rules'] ) ? selected( $this->options['ssf_display_rules'], 'post|all|taxarchive|post_tag' ) : ''; ?> value="post|all|taxarchive|post_tag">All Tags Archive</option>
						</optgroup>
						<optgroup label="Pages">
							<option <?php isset( $this->options['ssf_display_rules'] ) ? selected( $this->options['ssf_display_rules'], 'page|all' ) : ''; ?> value="page|all">All Pages</option>
						</optgroup>
						<optgroup label="Specific Target">
							<option <?php isset( $this->options['ssf_display_rules'] ) ? selected( $this->options['ssf_display_rules'], 'specifics' ) : ''; ?> value="specifics">Specific Pages / Posts / Taxonomies, etc.</option>
						</optgroup>
				</select>
			<?php
		}

		/**
		 * Get the settings option array and print one of its values.
		 *
		 * @since 1.3.0
		 * @return void
		 */
		public function particle_shape_callback() {
			?>
				<select name="ssf_settings[ssf_particle_shape]">
					<option value="circle" <?php isset( $this->options['ssf_particle_shape'] ) ? selected( $this->options['ssf_particle_shape'], 'circle' ) : ''; ?>><?php _e( 'Circle', 'swap-snow-fall' ); ?></option>
					<option value="star" <?php isset( $this->options['ssf_particle_shape'] ) ? selected( $this->options['ssf_particle_shape'], 'star' ) : ''; ?>><?php _e( 'Star', 'swap-snow-fall' ); ?></option>
					<option value="triangle" <?php isset( $this->options['ssf_particle_shape'] ) ? selected( $this->options['ssf_particle_shape'], 'triangle' ) : ''; ?>><?php _e( 'Triangle', 'swap-snow-fall' ); ?></option>
					<option value="polygon" <?php isset( $this->options['ssf_particle_shape'] ) ? selected( $this->options['ssf_particle_shape'], 'polygon' ) : ''; ?>><?php _e( 'Polygon', 'swap-snow-fall' ); ?></option>
					<option value="edge" <?php isset( $this->options['ssf_particle_shape'] ) ? selected( $this->options['ssf_particle_shape'], 'edge' ) : ''; ?>><?php _e( 'Edge', 'swap-snow-fall' ); ?></option>
				</select>
			<?php
		}

		/**
		 * Get the settings option array and print one of its values.
		 *
		 * @since 1.3.0
		 * @return void
		 */
		public function particle_color_callback() {
			printf(
				'<input type="text" class="ssf-color-picker" name="ssf_settings[ssf_particle_color]" data-alpha="true" value="%s" />',
				isset( $this->options['ssf_particle_color'] ) ? esc_attr( $this->options['ssf_particle_color'] ) : '#ffffff'
			);
		}

		/**
		 * Get the settings option array and print one of its values.
		 *
		 * @since 1.3.0
		 * @return void
		 */
		public function ssf_display_rules_ids_callback() {
			printf(
				'<input type="text" class="ssf-display-rule-ids %1s" name="ssf_settings[ssf_display_rules_ids]" value="%2s" /></br><a class="ssf-display-rule-ids %3s" href="https://premanshupandey.com/wordpress-plugins/particle-wordpress-backgrounds-plugin/"><i>Check this doc for more information</i></a>',
				esc_attr( ( isset( $this->options['ssf_display_rules'] ) && 'specifics' === $this->options['ssf_display_rules'] ) ? '' : 'ssf-hide' ),
				esc_attr( isset( $this->options['ssf_display_rules_ids'] ) ? $this->options['ssf_display_rules_ids'] : '' ),
				esc_attr( ( isset( $this->options['ssf_display_rules'] ) && 'specifics' === $this->options['ssf_display_rules'] ) ? '' : 'ssf-hide' )
			);
		}

		/**
		 * Get the settings option array and print one of its values.
		 *
		 * @since 1.3.0
		 * @return void
		 */
		public function particle_number_callback() {
			printf(
				"<div class='ssf_range'><input class='ssf_range__slider' name='ssf_settings[ssf_particle_number]' id='ssf_slider' max='200' min='0' oninput='ssf_amount.value=ssf_slider.value' type='range' value='%s'><input id='ssf_amount' class='ssf_range__amount' oninput='ssf_slider.value=ssf_amount.value' type='text' value='%s'></div>",
				isset( $this->options['ssf_particle_number'] ) ? esc_attr( $this->options['ssf_particle_number'] ) : '100',
				isset( $this->options['ssf_particle_number'] ) ? esc_attr( $this->options['ssf_particle_number'] ) : '100'
			);
		}

		/**
		 * Main settings page for the plugin.
		 *
		 * @since 1.1.0
		 * @return void
		 */
		function ssf_settings_page() {
			?>
			<!-- Create a header in the default WordPress 'wrap' container -->
			<div class="wrap">

				<div id="icon-themes" class="icon32"></div>
				<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
				<?php settings_errors(); ?>

				<?php $active_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'settings'; ?>

				<h2 class="nav-tab-wrapper">
					<a href="?post_type=ssf_particle_hook&page=swap-snow-fall&tab=settings" class="nav-tab <?php echo 'settings' === $active_tab ? 'nav-tab-active' : ''; ?>"><?php _e( 'Settings', 'swap-snow-fall' ); ?></a>
					<a href="?post_type=ssf_particle_hook&page=swap-snow-fall&tab=about" class="nav-tab <?php echo 'about' === $active_tab ? 'nav-tab-active' : ''; ?>"><?php _e( 'About', 'swap-snow-fall' ); ?></a>
				</h2>

				<?php
				if ( 'settings' === $active_tab ) {
					$this->render_settings_tab();
				} else {
					$this->render_about_tab();
				}
				?>

			</div><!-- /.wrap -->
			<?php
		}

		/**
		 * Print settings tab content.
		 *
		 * @since 1.1.0
		 * @return void
		 */
		function render_settings_tab() {
			?>
			<form method="post" action="options.php">
				<?php
					$this->options = get_option( 'ssf_settings' );
					// This prints out all hidden setting fields.
					settings_fields( 'ssf_settings_group' );
					do_settings_sections( 'swap-snow-fall' );
					submit_button();
				?>
				</form>
			<?php
		}

		/**
		 * Print about tabs content.
		 *
		 * @since 1.1.0
		 * @return void
		 */
		function render_about_tab() {
			?>
			<div class="ssf-about">
				<div class="ssf-about-icon"></div>
				<div class="ssf-about-info">
					<h3><?php _e( 'What is the Particle Background Plugin?', 'swap-snow-fall' ); ?></h3>
					<p><?php _e( 'The Particle Background plugin is developed for the users to easily add Particle Background to their website and create attractive beautiful particle backgrounds.', 'swap-snow-fall' ); ?></p>
				</div>
				<div class="ssf-support-icon"></div>
				<div class="ssf-about-info">
					<h3><?php _e( 'We stand by you!', 'swap-snow-fall' ); ?></h3>
					<p><?php _e( 'We assure complete help and support whenever you need us. Go ahead and add some cool effects to your website!', 'swap-snow-fall' ); ?></p>
					<a href="https://wordpress.org/support/plugin/swap-snow-fall/#new-topic-0" target="_blank"><?php _e( 'Need help? Post your query here.', 'swap-snow-fall' ); ?></a>
				</div>
				<div class="ssf-donate-icon"></div>
				<div class="ssf-about-info">
					<h3><?php _e( 'Particle WordPress Backgrounds needs your support.', 'swap-snow-fall' ); ?></h3>
					<p><?php _e( 'It is hard to continue development and support for this plugin without contributions from users like you. If you enjoy using Particle WordPress Backgrounds and find it useful, please consider ', 'swap-snow-fall' ); ?><a href="https://www.paypal.com/paypalme/premanshupandey" target="_blank"><?php _e( 'making a donation', 'swap-snow-fall' ); ?></a></p>
				</div>
			</div>
			<?php
		}
	}

	/**
	 *  Kick off the class - SSF_Menu.
	 */
	new SSF_Menu;
}
