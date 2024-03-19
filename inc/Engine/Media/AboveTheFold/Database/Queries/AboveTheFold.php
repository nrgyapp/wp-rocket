<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Media\AboveTheFold\Database\Queries;

use WP_Rocket\Engine\Common\Database\Queries\AbstractQuery;
use WP_Rocket\Engine\Media\AboveTheFold\Database\Schemas\AboveTheFold as AboveTheFoldSchema;
use WP_Rocket\Engine\Media\AboveTheFold\Database\Rows\AboveTheFold as AboveTheFoldRow;

class AboveTheFold extends AbstractQuery {

	/**
	 * Name of the database table to query.
	 *
	 * @var   string
	 */
	protected $table_name = 'wpr_above_the_fold';

	/**
	 * String used to alias the database table in MySQL statement.
	 *
	 * Keep this short, but descriptive. I.E. "tr" for term relationships.
	 *
	 * This is used to avoid collisions with JOINs.
	 *
	 * @var   string
	 */
	protected $table_alias = 'wpr_atf';

	/**
	 * Name of class used to setup the database schema.
	 *
	 * @var   string
	 */
	protected $table_schema = AboveTheFoldSchema::class;

	/** Item ******************************************************************/

	/**
	 * Name for a single item.
	 *
	 * Use underscores between words. I.E. "term_relationship"
	 *
	 * This is used to automatically generate action hooks.
	 *
	 * @var   string
	 */
	protected $item_name = 'above_the_fold';

	/**
	 * Plural version for a group of items.
	 *
	 * Use underscores between words. I.E. "term_relationships"
	 *
	 * This is used to automatically generate action hooks.
	 *
	 * @var   string
	 */
	protected $item_name_plural = 'above_the_fold';

	/**
	 * Name of class used to turn IDs into first-class objects.
	 *
	 * This is used when looping through return values to guarantee their shape.
	 *
	 * @var   mixed
	 */
	protected $item_shape = AboveTheFoldRow::class;

	/**
	 * Complete a job.
	 *
	 * @param string  $url Url from DB row.
	 * @param boolean $is_mobile Is mobile from DB row.
	 * @param array   $data LCP & Above the fold data.
	 *
	 * @return boolean|int
	 */
	public function make_job_completed( string $url, bool $is_mobile, array $data ) {
		if ( ! self::$table_exists && ! $this->table_exists() ) {
			return false;
		}

		// Get the database interface.
		$db = $this->get_db();

		// Bail if no database interface is available.
		if ( empty( $db ) ) {
			return false;
		}

		$prefixed_table_name = $db->prefix . $this->table_name;

		$data = [
			'status'   => 'completed',
			'lcp'      => $data['lcp'],
			'viewport' => $data['viewport'],
		];

		$where = [
			'url'       => untrailingslashit( $url ),
			'is_mobile' => $is_mobile,
		];

		return $db->update( $prefixed_table_name, $data, $where );
	}

	/**
	 * Deletes rows from the 'above_the_fold' table that have a 'failed' status and have not been accessed since a specified date.
	 *
	 * @return int|false The number of rows affected if the query is successful, or false on failure.
	 */
	public function delete_failed_and_not_accessed_rows() {
		// Get the database object.
		$db = $this->get_db();

		// If no database interface is available, return false.
		if ( empty( $db ) ) {
			return false;
		}

		// Prepare the SQL query. The '%s' placeholder will be replaced by the value of $date.
		// The query will delete rows from the 'above_the_fold' table where 'status' is 'failed' and 'last_accessed' is less than or equal to $date.
		$query = $db->prepare( "DELETE FROM {$this->table_name} WHERE status = 'failed' AND last_accessed <= %s", $date );

		// Execute the query and return the result. If the query is successful, the number of affected rows will be returned. If the query fails, false will be returned.
		return $db->query( $query );
	}
}
