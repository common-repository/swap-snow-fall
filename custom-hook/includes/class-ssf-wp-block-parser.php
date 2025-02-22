<?php
/**
 * SSF Block parser
 *
 * @package Swap Snow Fall
 */

/**
 * SSF Block Parser
 *
 * @since 2.0.0
 */
class SSF_WP_Block_Parser extends WP_Block_Parser {


	/**
	 * Parse block document.
	 *
	 * @param string $document block document.
	 */
	public function parse( $document ) {
		$result = parent::parse( $document );

		$current_index         = 0;
		$current_heading_index = 0;

		foreach ( $result as $index => $first_level_block ) {
			$result[ $index ]['firstLevelBlock'] = true;
			$inner_html                          = trim( $first_level_block['innerHTML'] );

			if ( ! empty( $inner_html ) ) {
				$result[ $index ]['firstLevelBlockIndex'] = $current_index++;

				if (
					strpos( $first_level_block['blockName'], 'heading' ) !== false
					||
					strpos( $first_level_block['blockName'], 'headline' ) !== false
					||
					in_array( // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
						substr( $inner_html, 0, 3 ),
						array(
							'<h1',
							'<h2',
							'<h3',
							'<h4',
							'<h5',
							'<h6',
						)
					)
				) {
					$result[ $index ]['firstLevelHeadingIndex'] = $current_heading_index++;
				}
			}
		}

		return $result;
	}
}
