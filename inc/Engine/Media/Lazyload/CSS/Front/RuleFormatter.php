<?php

namespace WP_Rocket\Engine\Media\Lazyload\CSS\Front;

class RuleFormatter {

	/**
	 * Format the CSS rule inside the CSS content.
	 *
	 * @param string $css CSS content.
	 * @param array  $data Data to format.
	 * @return string
	 */
	public function format( string $css, array $data ): string {

		if ( count( $data ) === 0 ) {
			return $css;
		}

		$block          = '';
		$replaced_block = null;

		foreach ( $data as $datum ) {
			if ( ! key_exists( 'selector', $datum ) || ! key_exists( 'original', $datum ) || ! key_exists( 'block', $datum ) || ! key_exists( 'hash', $datum ) ) {
				return $css;
			}

			$block          = $datum['block'];
			$replaced_block = $replaced_block ?: $datum['block'];
			$url            = $datum['original'];

			$hash = $datum['hash'];

			$placeholder          = "--wpr-bg-$hash";
			$variable_placeholder = "var($placeholder)";

			$replaced_block = str_replace( $url, $variable_placeholder, $replaced_block );
		}

		return str_replace( $block, $replaced_block, $css );
	}
}
