<?php
/**
 * SSF Frontend.
 *
 * @package SSF
 */

// Prohibit direct script loading.
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );

if ( ! class_exists( 'SSF_Frontend' ) ) {

	/**
	 * Class SSF_Frontend.
	 */
	class SSF_Frontend {

		/**
		 * Holds the values to be used in the fields callbacks
		 *
		 * @var array
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
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_ssf_frontend_scripts' ) );
		}

		/**
		 * Enqueue style/script to fontend.
		 *
		 * @since 1.1.0
		 * @return void
		 */
		function enqueue_ssf_frontend_scripts() {
			$this->options = get_option( 'ssf_settings' );

			if ( isset( $this->options['load_disable_checkbox'] ) ) {
				if ( isset( $this->options['ssf_disable_checkbox'] ) && 1 === (int) $this->options['ssf_disable_checkbox'] ) {
					return;
				}
			} else {
				if ( ! ( isset( $this->options['ssf_enable_checkbox'] ) && 1 === (int) $this->options['ssf_enable_checkbox'] ) ) {
					return;
				}
			}

			if ( isset( $this->options['ssf_display_rules_ids'] ) ) {

				$display = false;

				switch ( $this->options['ssf_display_rules'] ) {
					case 'select':
						$display = true;
						break;

					case 'basic-global':
						$display = true;
						break;

					case 'basic-singulars':
						$display = is_singular() ? true : false;
						break;

					case 'basic-archives':
						$display = is_archive() ? true : false;
						break;

					case 'special-404':
						$display = is_404() ? true : false;
						break;

					case 'special-search':
						$display = is_search() ? true : false;
						break;

					case 'special-front':
						$display = is_front_page() ? true : false;
						break;

					case 'special-date':
						$display = is_date() ? true : false;

						break;

					case 'special-author':
						$display = is_author() ? true : false;
						break;

					case 'post|all':
						$display = is_single() && 'post' === get_post_type() ? true : false;
						break;

					case 'post|all|archive':
						$display = is_archive() && 'post' === get_post_type() ? true : false;
						break;

					case 'post|all|taxarchive|category':
						$display = is_category() && 'post' === get_post_type() ? true : false;
						break;

					case 'post|all|taxarchive|post_tag':
						$display = is_tag() && 'post' === get_post_type() ? true : false;
						break;

					case 'page|all':
						$display = is_page() ? true : false;
						break;

					case 'specifics':
						if ( false !== $this->options['ssf_display_rules_ids'] ) {
							global $post;

							if ( strpos( $this->options['ssf_display_rules_ids'], ',' ) !== false ) {
								$ids_arr = explode( ',', $this->options['ssf_display_rules_ids'] );
								if ( in_array( $post->ID, $ids_arr ) ) { // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
									$display = true;
								}
							} else {
								if ( (int) $this->options['ssf_display_rules_ids'] === $post->ID ) {
									$display = true;
								}
							}
						}
						break;

					default:
						break;
				}

				if ( false === $display ) {
					return;
				}
			}

			/**
			 * Compatibility of options with Plain Snow Fall JS.
			 *
			 * @since 1.3.1
			 */
			$particle_shape = isset( $this->options['ssf_particle_shape'] ) ? $this->options['ssf_particle_shape'] : 'circle';

			if ( isset( $this->options['ssf_select_position_dropdown'] ) && 'above-content' === $this->options['ssf_select_position_dropdown'] ) {
				switch ( $particle_shape ) {
					case 'circle':
						$particle_shape = '&bull;';
						break;
					case 'star':
						$particle_shape = '&sstarf;';
						break;
					case 'triangle':
						$particle_shape = '&blacktriangle;';
						break;
					case 'polygon':
						$particle_shape = '&#x2B20;';
						break;
					case 'edge':
						$particle_shape = '&#9632;';
						break;

					default:
						$particle_shape = '&bull;';
						break;
				}
			}

			$config_array = array(
				'ssf_url' => SSF_URL,
				'color'   => isset( $this->options['ssf_particle_color'] ) ? $this->options['ssf_particle_color'] : '#FFFFFF',
				'shape'   => $particle_shape,
				'number'  => isset( $this->options['ssf_particle_number'] ) ? $this->options['ssf_particle_number'] : '100',
			);

			$dir_name    = ( SCRIPT_DEBUG ) ? 'unminified' : 'minified';
			$file_prefix = ( SCRIPT_DEBUG ) ? '' : '.min';

			$file_rtl = ( is_rtl() ) ? '-rtl' : '';
			$css_uri  = SSF_URL . 'assets/css/' . $dir_name . '/';

			if ( isset( $this->options['ssf_select_position_dropdown'] ) && 'above-content' === $this->options['ssf_select_position_dropdown'] ) {
				// Enqeue SSF Frontend JS.
				if ( SCRIPT_DEBUG ) {
					wp_enqueue_script( 'ssf-frontend-script', SSF_URL . 'assets/js/' . $dir_name . '/above-text-script' . $file_prefix . '.js', array(), SSF_VER );
				} else {
					wp_enqueue_script( 'ssf-frontend-script', SSF_URL . 'assets/js/' . $dir_name . '/frontend-above-content' . $file_prefix . '.js', array(), SSF_VER );
				}

				$config_array['enable_mobile']     = isset( $this->options['ssf_enable_mobile'] ) ? false : true;
				$config_array['autoStart']         = true;          // Whether the snow should start automatically or not.
				$config_array['flakesMaxActive']   = 200;      // Limit amount of snow falling at once (less = lower CPU use).
				$config_array['animationInterval'] = 10;    // Theoretical "miliseconds per frame" measurement. 20 = fast + smooth, but high CPU use. 50 = more conservative, but slower.
				$config_array['useGPU']            = true;             // Enable transform-based hardware acceleration, reduce CPU load.
				$config_array['className']         = null;          // CSS class name for further customization on snow elements.
				$config_array['flakeBottom']       = null;        // Integer for Y axis snow limit, 0 or null for "full-screen" snow effect.
				$config_array['followMouse']       = true;        // Snow movement can respond to the user's mouse.
				$config_array['snowStick']         = true;          // Whether or not snow should "stick" at the bottom. When off, will never collect.
				$config_array['targetElement']     = null;      // element which snow will be appended to (null = document.body) - can be an element ID eg. 'myDiv', or a DOM node reference.
				$config_array['useMeltEffect']     = true;      // When recycling fallen snow (or rarely, when falling), have it "melt" and fade out if browser supports it.
				$config_array['useTwinkleEffect']  = true;  // Allow snow to randomly "flicker" in and out of view while falling.
				$config_array['usePositionFixed']  = false;  // true = snow does not shift vertically when scrolling. May increase CPU load, disabled by default - if enabled, used only where supported.
				$config_array['usePixelPosition']  = false;  // Whether to use pixel values for snow top/left vs. percentages. Auto-enabled if body is position:relative or targetElement is specified.

				// --- less-used bits ---

				$config_array['freezeOnBlur']     = true;       // Only snow when the window is in focus (foreground.) Saves CPU.
				$config_array['flakeLeftOffset']  = 0;       // Left margin/gutter space on edge of container (eg. browser window.) Bump up these values if seeing horizontal scrollbars.
				$config_array['flakeRightOffset'] = 0;      // Right margin/gutter space on edge of container.
				$config_array['vMaxX']            = 5;                 // Maximum X velocity range for snow.
				$config_array['vMaxY']            = 4;                 // Maximum Y velocity range for snow.
				$config_array['zIndex']           = 0;                // CSS stacking order applied to each snowflake.

				// Localize SSF JS.
				wp_localize_script( 'ssf-frontend-script', 'ssf_script', apply_filters( 'ssf_plain_snow_params', $config_array ) );

			} else {

				add_action(
					'wp_print_styles',
					function() {
						$css = '#ssf-particles-js{position:fixed;width:100%;height:100%;z-index:-1}';
						?>
							<style id="ssf-global-style"><?php echo $css; ?></style>
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
						"value": "' . $config_array['number'] . '"
						},
						"color": {
						"value": "' . $config_array['color'] . '"
						},
						"shape": {
						"type": "' . $config_array['shape'] . '"
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
					var body = document.getElementsByClassName("ssf-active");
					body[0].insertAdjacentHTML("afterbegin", "<div id=\'ssf-particles-js\'></div>");
					particlesJS("ssf-particles-js", ' . apply_filters( 'ssf_particle_json_full_page', $json ) . ');
				});';

				wp_add_inline_script( 'ssf-frontend-particle-script', $js );
			}
		}
	}
	/**
	 *  Kick off the class - SSF_Frontend.
	 */
	new SSF_Frontend;
}
