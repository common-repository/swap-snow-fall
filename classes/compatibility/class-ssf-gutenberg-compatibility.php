<?php
/**
 * SSF Gutenberg Compatibility class
 *
 * @package SSF
 * @since 2.0.0
 */

/**
 * SSF Gutenberg Builder Compatibility class
 *
 * @since 2.0.0
 */
class SSF_Gutenberg_Compatibility extends SSF_Page_Builder_Compatibility {

	/**
	 * Render Blocks content for post.
	 *
	 * @param int $post_id Post id.
	 *
	 * @since 2.0.0
	 */
	public function render_content( $post_id ) {

		$output       = '';
		$current_post = get_post( $post_id, OBJECT );

		if ( has_blocks( $current_post ) ) {
			$blocks = parse_blocks( $current_post->post_content );
			foreach ( $blocks as $block ) {
				$output .= render_block( $block );
			}
		} else {
			$output = $current_post->post_content;
		}

		ob_start();
		echo do_shortcode( $output );
		echo ob_get_clean(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Load Gutenberg Blocks styles & scripts.
	 *
	 * @param int $post_id Post id.
	 *
	 * @since 2.0.0
	 */
	public function enqueue_blocks_assets( $post_id ) {
		wp_enqueue_style( 'wp-block-library' );
	}
}
