<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Media\Lazyload\Content\Processor;

class Regex {
	public function add_locations_hash_to_html( $html ) {
		$result = preg_match( '/(?><body[^>]*>)(?>.*?<\/body>)/is', $html, $matches, PREG_OFFSET_CAPTURE );
		if ( ! $result ) {
			return $html;
		}
		return $this->add_hash_to_element( $html, $matches[0][0], 2, $matches[0][1] );
	}

	private function add_hash_to_element( $html, $element, $depth, $body_offset ) {
		if ( $depth < 0 ) {
			return $html;
		}

		$skip_tags = [
			'div',
			'main',
			'footer',
			'section',
			'article',
			'header',
		];

		$result = preg_match_all( '/(?><(' . implode( '|', $skip_tags ) . ')[^>]*>)/is', $element, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);

		if ( ! $result ) {
			return $html;
		}
		error_log( 'Page URL - In Progress: ' . $_SERVER['REQUEST_URI'] );
		error_log("Match found: " . print_r($matches, true));
		$count = 0;

		// Offset adjustment for cumulative replacements
		$offset_adjustment = $body_offset;

		foreach ( $matches as $child ) {
			error_log("Processing: " . print_r($child, true));
			// Get the matched tag and its offset
			$matched_tag = $child[0][0];
			$offset = $child[0][1] + $offset_adjustment;

			// Calculate the hash of the opening tag.
			$opening_tag_html = strstr( $matched_tag, '>', true ) . '>';
			$hash = md5( $opening_tag_html . $count );
			++$count;

			// Add the data-rocket-location-hash attribute.
			$replace = preg_replace( '/' . preg_quote($child[1][0], '/') . '/is', '$0 data-rocket-location-hash="' . $hash . '"', $matched_tag, 1 );
			$html = substr_replace($html, $replace, $offset, strlen($matched_tag));
			$offset_adjustment += strlen($replace) - strlen($matched_tag);
		}

		return $html;
	}
}
