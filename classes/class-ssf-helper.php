<?php
/**
 * Common helper function file.
 *
 * @package SSF
 */

add_filter( 'body_class', 'ssf_frontend_body_class' );

/**
 * Function Name: SSF Front End Body Class.
 * Function Description: This function adds a new class to the body tag of website.
 *
 * @param array $classes get all body classes.
 *
 * @since 1.1.0
 * @return array $classes all classes.
 */
function ssf_frontend_body_class( $classes ) {
	$classes[] = 'ssf-active';
	return $classes;
}
