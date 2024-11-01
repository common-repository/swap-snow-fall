<?php
/**
 * SSF Page Builder
 *
 * @package Swap Snow Fall
 * @since 2.0.0
 */

/**
 * SSF Page Builder Class
 *
 * @since 2.0.0
 */
class SSF_Page_Builder_Compatibility {

	/**
	 * Instance
	 *
	 * @since 2.0.0
	 *
	 * @access private
	 * @var object Class object.
	 */
	private static $instance;

	/**
	 * Initiator
	 *
	 * @since 2.0.0
	 *
	 * @return object initialized object of class.
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Returns instance for active page builder.
	 *
	 * @param int $post_id Post id.
	 *
	 * @since 2.0.0
	 */
	public function get_active_page_builder( $post_id ) {

		global $wp_post_types;
		$post      = get_post( $post_id );
		$post_type = get_post_type( $post_id );

		$has_rest_support = isset( $wp_post_types[ $post_type ]->show_in_rest ) ? $wp_post_types[ $post_type ]->show_in_rest : false;

		if ( $has_rest_support ) {
			return new SSF_Gutenberg_Compatibility();
		}

		return self::get_instance();
	}

	/**
	 * Render content for post.
	 *
	 * @param int $post_id Post id.
	 *
	 * @since 2.0.0
	 */
	public function render_content( $post_id ) {

		$current_post = get_post( $post_id, OBJECT );
		ob_start();
		echo do_shortcode( $current_post->post_content );
		echo ob_get_clean(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}

/**
 * Initialize class object with 'get_instance()' method
 */
SSF_Page_Builder_Compatibility::get_instance();
