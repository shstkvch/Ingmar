<?php
/**
 * Main Phoenix Collection class
 */

class Collection {

	/**
	 * The raw post objects
	 * @var array $posts
	 */
	private $posts = array();

	/**
	 * The main collection objects
	 * @var array $objects
	 */
	private $objects = array();

	/**
	 * Constructor.
	 *
	 * @param  array of WP_Post objects
	 * @param  string the class to use for creating new objects
	 * @return ThomasCollection
	 */
	function __construct( $posts, $class ) {
		if ( !is_array( $posts ) ) {
			throw new Exception( 'Invalid $posts array for ThomasCollection.' );
		}

		$this->posts = $posts;

		// load the posts into our objects array
		$this->loadPosts( $class );

		return $this;
	}

	/**
	 * Get the first item in the collection
	 */
	public function first() {
		reset( $this->objects );
		return current( $this->objects );
	}

	/**
	 * Get the first item in the collection
	 */
	public function last() {
		end( $this->objects );
		return current( $this->objects );
	}

	/**
	 * Load the posts into our object array
	 */
	private function loadPosts( $class ) {
		foreach( $this->posts as $post ) {
			$this->objects[] = new $class( $post );
		}
	}

}
