<?php
/**
 * SSF Loader.
 *
 * @package SSF
 */

if ( ! class_exists( 'SSF_Loader' ) ) {
	/**
	 * Class SSF_Loader.
	 */
	final class SSF_Loader {
		/**
		 * Member Variable
		 *
		 * @var object
		 *
		 * @since 1.1.0
		 */
		private static $instance;
		/**
		 * Initiator
		 *
		 * @since 1.1.0
		 * @return object
		 */
		public static function get_instance() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new self;
			}
			return self::$instance;
		}
		/**
		 * Constructor
		 *
		 * @since 1.1.0
		 * @return void
		 */
		public function __construct() {
			$this->define_constants();
			$this->loader();
			add_action( 'plugins_loaded', array( $this, 'load_plugin' ) );
		}
		/**
		 * Loads Other files.
		 *
		 * @since 1.1.0
		 * @return void
		 */
		public function loader() {
			require_once SSF_DIR . 'classes/plugin-update/class-ssf-update.php';
			require_once SSF_DIR . 'classes/compatibility/class-ssf-page-builder-compatibility.php';
			require_once SSF_DIR . 'classes/compatibility/class-ssf-gutenberg-compatibility.php';
			require_once SSF_DIR . 'classes/class-ssf-helper.php';
			// Page Builder compatibility base class.
			require_once SSF_DIR . 'custom-hook/class-ssf-custom-hook-post-type.php';
			require_once SSF_DIR . 'classes/admin/class-ssf-menu.php';
		}
		/**
		 * Defines all constants
		 *
		 * @since 1.1.0
		 * @return void
		 */
		public function define_constants() {
			define( 'SSF_BASE', plugin_basename( SSF_FILE ) );
			define( 'SSF_DIR', plugin_dir_path( SSF_FILE ) );
			define( 'SSF_URL', plugins_url( '/', SSF_FILE ) );
			define( 'SSF_VER', '2.0.0' );
			define( 'SSF_SLUG', 'ssf' );
		}
		/**
		 * Loads plugin files.
		 *
		 * @since 1.1.0
		 * @return void
		 */
		function load_plugin() {
			$this->load_textdomain();
			require_once SSF_DIR . 'classes/class-ssf-frontend.php';
		}
		/**
		 * Load Swap Snow Fall Text Domain.
		 * This will load the translation textdomain depending on the file priorities.
		 *      1. Global Languages /wp-content/languages/swap-snow-fall/ folder
		 *      2. Local dorectory /wp-content/plugins/swap-snow-fall/languages/ folder
		 *
		 * @since  1.1.0
		 * @return void
		 */
		public function load_textdomain() {
			/**
			 * Filters the languages directory path to use for AffiliateWP.
			 *
			 * @param string $lang_dir The languages directory path.
			 */
			$lang_dir = apply_filters( 'ssf_languages_directory', SSF_ROOT . '/languages/' );
			load_plugin_textdomain( 'swap-snow-fall', false, $lang_dir );
		}
	}
	/**
	 *  Prepare if class 'SSF_Loader' exist.
	 *  Kicking this off by calling 'get_instance()' method
	 */
	SSF_Loader::get_instance();
}
