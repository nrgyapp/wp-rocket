<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Media\AboveTheFold\AJAX;

use WP_Rocket\Engine\Media\AboveTheFold\Database\Queries\AboveTheFold as ATFQuery;
use WP_Rocket\Engine\Common\Context\ContextInterface;

class Controller {
	/**
	 * ATFQuery instance
	 *
	 * @var ATFQuery
	 */
	private $query;

	/**
	 * LCP Context.
	 *
	 * @var ContextInterface
	 */
	protected $context;

	/**
	 * Constructor
	 *
	 * @param ATFQuery         $query ATFQuery instance.
	 * @param ContextInterface $context Context interface.
	 */
	public function __construct( ATFQuery $query, ContextInterface $context ) {
		$this->query   = $query;
		$this->context = $context;
	}

	/**
	 * Add LCP data to the database
	 *
	 * @return bool
	 */
	public function add_lcp_data() {
		check_ajax_referer( 'rocket_lcp', 'rocket_lcp_nonce' );

		if ( ! $this->context->is_allowed() ) {
			wp_send_json_error( 'not allowed' );
			return;
		}

		$url       = isset( $_POST['url'] ) ? untrailingslashit( esc_url_raw( wp_unslash( $_POST['url'] ) ) ) : '';
		$is_mobile = isset( $_POST['is_mobile'] ) ? filter_var( wp_unslash( $_POST['is_mobile'] ), FILTER_VALIDATE_BOOLEAN ) : false;
		$images    = isset( $_POST['images'] ) ? json_decode( sanitize_text_field( wp_unslash( $_POST['images'] ) ) ) : '';
		$lcp       = 'not found';
		$viewport  = [];

		$keys = ['bg_set', 'src']; // Add more keys here in the order of their priority

		foreach ($images as $image) {
			if ('lcp' === $image->label && 'not found' === $lcp) {
				$lcp = $this->createObject($image, $keys);
			} elseif ('above-the-fold' === $image->label) {
				$viewportImage = $this->createObject($image, $keys);
				if ($viewportImage !== null) {
					$viewport[] = $viewportImage;
				}
			}
		}

		$row = $this->query->get_row( $url, $is_mobile );

		if ( ! empty( $row ) ) {
			wp_send_json_error( 'item already in the database' );
			return;
		}

		$item = [
			'url'           => $url,
			'is_mobile'     => $is_mobile,
			'status'        => 'completed',
			'lcp'           => wp_json_encode( $lcp ),
			'viewport'      => wp_json_encode( $viewport ),
			'last_accessed' => current_time( 'mysql', true ),
		];

		$result = $this->query->add_item( $item );

		if ( ! $result ) {
			wp_send_json_error( 'error when adding the entry to the database' );
			return;
		}

		wp_send_json_success( $item );
	}

	/**
	 * Creates an object with the 'type' property and the first key that exists in the image object.
	 *
	 * @param object $image The image object.
	 * @param array  $keys  An array of keys in the order of their priority.
	 *
	 * @return object|null Returns an object with the 'type' property and the first key that exists in the image object. If none of the keys exist in the image object, it returns null.
	 */
	private function createObject($image, $keys) {
		foreach ($keys as $key) {
			if (isset($image->$key)) {
				return (object) [
					'type' => $image->type,
					$key => $image->$key,
				];
			}
		}
		return null;
	}
}
