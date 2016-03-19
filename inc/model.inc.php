<?php
/**
 * Main Thomas model class
 */

class ThomasModel {

	/**
	 * The fields belonging to this object
	 *
	 * @var array $fields
	 */
	protected $fields = array();

	/**
	 * The data for each field
	 *
	 * @var array $fields_data
	 */
	protected $fields_data = array();

	/**
	 * The ID of the object in the database
	 *
	 * @var integer $id
	 */
	private $id = null;

	/**
	 * The WP_Post object for this object
	 *
	 * @var WP_Post $post
	 */
	private $wp_post;

	/**
	 * Database table to use
	 *
	 * You can use 'post', 'comment', 'option' or a custom table name.
	 *
	 * Using a custom table is ideal if your data doesn't fit the standard
	 * WordPress structure, and it can make queries quicker to run because
	 * it will use a flat structure (with a column for each field.)
	 *
	 * You can change your table type at any time, but any objects left
	 * on the old table will be inaccessible using the API.
	 */
	private $database_table = 'post';

	/**
	 * Validations to run when saving this object
	 *
	 * @var array $validations
	 */
	protected $validations = array();

	/**
	 * You can relate any field to another ThomasModel by giving its
	 * name here. We'll link it using it's post ID.
	 *
	 * You can access relations like this:
	 *
	 * Testimonial->first()->client
	 */
	protected $relations = array();

	/**
	 * The current query being built up
	 *
	 * @var array $query
	 */
	private $query = array();

	/**
	 * The called class (so we can load in the proper relations/fields)
	 *
	 * @var string $called_class
	 */
	private $called_class = 'ThomasModel';

	/**
	 * The post type to save this model to in the database
	 *
	 * @var string $post_type
	 */
	protected $post_type = 'post';

	/**
	 * Constructor
	 */
 	public function __construct( $var = null ) {
		if ( is_null( $this ) || is_null( $var ) ) {
			return self;
		}

		/*
		 * If it's an integer it's a post ID,
		 * if it's a WP post we'll initialise with it
		 * and if it's null then we're just being chained
		 */
		if ( is_integer( $var ) ) {
			$this->id = $var;
		} else if ( is_object( $var ) ) {
			if ( 'WP_Post' == get_class( $var ) ) {
				$this->id = $var->ID;
				$this->wp_post = $var;
			}
		}

		$this->initialiseObject();
	}

	/**
	 * Initialise the object
	 */
	private function initialiseObject() {
		if ( is_null( $this->wp_post ) && $this->id ) {
			$this->wp_post = new WP_Post( $this->id );
		}

		$meta = get_metadata( $this->database_table, $this->id );

		$this->fields_data = $meta;
		$this->unsanitiseFields();
		$this->expandRelatedObjects();

		// var_dump( $this->fields_data ); die();
	}

	/**
	 * Save the object and its fields to the database
	 *
	 * If successful, the object will be saved in the database and returned.
	 * If there's a validation error, an array of validation errors will be returned.
	 * If something else goes wrong, an exception will be thrown.
	 *
	 * @return Thomas_Object|Array
	 * @throws Exception
	 */
	public function save() {
		// do validations...

		$errors = $this->validateFields();

		if ( count( $errors ) ) {
			return $errors;
		}

		$this->saveNewFieldObjects();
		$sanitised_fields = $this->sanitiseFields();

		$this->saveOrUpdate( $sanitised_fields );

		if ( !is_integer( $this->id ) || $this->id < 1 ) {
			throw new Exception( 'Invalid ID for Thomas_Object' );
			return false;
		}
	}

	/**
	 * Save new objects which are stored in fields
	 */
	private function saveNewFieldObjects() {
		foreach ( $this->relations as $relation_key => $relation_val ) {
			$obj = $this->fields_data[$relation_key];

			if ( is_object( $obj ) ) {
				$obj->save();
				$this->fields_data[$relation_key] = $obj->id;
			}
		}
	}

	/**
	 * Find related objects and expand them to full class instances
	 */
	private function expandRelatedObjects() {
		foreach ( $this->relations as $relation_key => $class_name ) {
			$obj_id = $this->fields_data[$relation_key];

			if ( is_subclass_of( $class_name, 'ThomasModel' ) && $obj_id > 0 ) {
				$this->fields_data[$relation_key] = new $class_name( $post_id );
			}
		}
	}

	/**
	 * Persist the object to the database
	 *
	 * @param $sanitised_fields the fields to insert
	 * @return true if it works
	 */
	private function saveOrUpdate( $sanitised_fields = array() ) {
		$args = array(
			'post_id' => $this->id,
			'meta_input' => $sanitised_fields,
			'post_type' => self::getPostType()
		);

		// Hacky -- add and remove the wp_insert_post_empty_content filter
		// so we can insert blank posts
		add_filter( 'wp_insert_post_empty_content', '__return_false', 15 );

		$this->id = wp_insert_post( $args, true );

		remove_filter( 'wp_insert_post_empty_contet', '__return_false', 15 );

		return true;
	}

	/**
	 * Get the post type for this model
	 *
	 * @return string
	 */
	private function getPostType() {
		if ( $this ) {
			// we're only interested if it's been overridden
			if ( $this->post_type !== 'post' ) {
				return $this->post_type;
			}
			if ( $this->called_class ) {
				return strtolower( $this->called_class );
			}
		}

		$called_class = get_called_class();

		if ( $called_class && $called_class !== 'ThomasModel' ) {
			return strtolower( $called_class );
		}

		return 'post';
	}

	/**
	 * Validate the fields on the object
	 */
	private function validateFields() {
		$errors = array();

		foreach ( $this->fields as $key => $val ) {
			$value = $this->fields_data[ $val ];
			$validations_to_run = (array) $this->validations[ $val ];

			foreach( $validations_to_run as $v ) {
				$valid = $this->validateField( $value, $v );

				if ( true === $valid ) {
					continue;
				}

				// field is invalid...
				$errors[$key][$v] = $valid;
			}

		}

		return $validation_issues;
	}

	/**
	 * Validate a single field with a given validator
	 *
	 * @TODO is_user validator, proper validation system
	 * @param mixed  $value the field value to validate
	 * @param string $validator the validator to use
	 */
	private function validateField( $value, $validator ) {
		/*
		 * This is very primitive for now... in the future there will
		 * be a proper modular validation system
		 */

		if ( function_exists( $validator ) ) {
			return call_user_func( $validator, $value );
		}

		if ( 'exists' === $validator ) {
			return !empty( $value );
		}

		/*
		 * TODO: Check if a field value is a valid Thomas_User
		 */
		if ( 'is_user' === $validator ) {
			return true;
		}

		/**
		 * Validating a conditional
		 */
		if ( '$val' === substr( $validator, 0, 4 ) ) {
			$parts = explode( ' ', $validator );

			$operator = $parts[1];
			$compare  = $parts[2];

			if ( $operator == '>' ) {
				return $value > $compare;
			}

			if ( $operator == '>=' ) {
				return $value > $compare;
			}

			if ( $operator == '<' ) {
				return $value < $compare;
			}

			if ( $operator == '<=' ) {
				return $value <= $compare;
			}

			if ( $operator == '==' ) {
				return $value == $compare;
			}

			if ( $operator == '!=' ) {
				return $value != $compare;
			}

			return false;
		}

		return false;
	}

	/**
	 * Sanitise the fields before saving
	 */
	public function sanitiseFields() {
		$fields = array();

		foreach( $this->fields_data as $key => $val ) {
			$fields['_' . $key] = $val;
		}

		return $fields;
	}

	/**
	 * Determine if a field is a relationship field
	 */
	public function isRelationField( $key ) {
		return array_key_exists( $key, $this->relations );
	}

	/**
	 * Unsanitise the field after loading
	 */
	public function unsanitiseFields() {
		$fields = array();

		if ( is_array( $this->fields_data ) ) {
			foreach( $this->fields_data as $key => $val ) {
				$key = substr( $key, 1 );

				// WP loves extraneous arrays
				if ( is_array( $val ) && count( $val ) == 1 ) {
					$val = $val[0];
				}

				$fields[$key] = $val;
			}
		}

		$this->fields_data = $fields;
	}

	/**
	 * Public API for the model
	 */
	public function __get( $key ) {
		return $this->fields_data[$key];
	}

	/**
	 * Public API for the model
	 */
	public function __set( $key, $value ) {
		$this->fields_data[$key] = $value;
	}

	/**
	 * Where query
	 *
	 * @param string $field to filter on
	 * @param string $compare operator
	 * @param string $value to compare with
	 */
	function where( $field, $compare, $value ) {
		return self::addQuery( 'where',
			array(
				'field' => $field,
				'compare' => $compare,
				'value' => $value
			)
		);
	}

	/**
	 * Delete the selected rows
	 */
	function delete() {
		$posts = self::executeQuery();
		return self::trashQuery( $posts, true );
	}

	/**
	 * Trash the selected rows
	 */
	function trash() {
		$posts = self::executeQuery();
		return self::trashQuery( $posts, false );
	}

	/**
	 * Get the selected rows
	 */
	function get() {
		self::addQuery( 'get' );
		return self::executeQuery();
	}

	/**
	 * Limit the number of results
	 *
	 * @param integer $limit maximum number of results to return
	 */
	function limit( $limit ) {
		return self::addQuery( 'limit', $limit );
	}

	/**
	 * Skip the first x results
	 *
	 * @param integer $skip number of results to skip at the front
	 */
	function skip( $skip ) {
		return self::addQuery( 'skip', $skip );
	}

	/**
	 * Create a new instance of the class
	 *
	 * @param mixed $params
	 * @return ThomasModel
	 */
	function create( $params = null ) {
		$class = get_called_class();

		return new $class( $params );
	}

	/**
	 * Add a new query to the stack
	 *
	 * @param string $operation to add
	 * @param mixed  $options detailling the operation
	 */
	private function addQuery( $operation, $options = null ) {
		$new_query = array(
			'operation' => $operation,
			'options' => $options
		);

		if ( 'NULL' == gettype( $this ) ) {
			$that = new self();
			$that->_setQuery( $new_query );
			$that->_setCalledClass( get_called_class() );

			return $that;
		}

		$this->query[] = $new_query;

		return $this;
	}

	/**
	 * Run the current query and return an array of Thomas_Objects
	 *
	 * @return array
	 */
	private function executeQuery() {
		$args = array(
			'posts_per_page' => 10,
			'post_type' => self::getPostType(),
			'post_status' => 'any'
		);

		if ( 'NULL' !== gettype( $this ) ) {
			foreach ( $this->query as $operation ) {
				$type = $this->database_table;
				$args = self::appendQueryArg( $args, $operation, $type );
			}
			$called_class = $this->called_class;
		} else {
			$called_class = get_called_class();
		}

		// var_dump( $args ); die();

		$query = new WP_Query( $args );

		return new ThomasCollection( $query->get_posts(), $called_class );
	}

	/**
	 * Trash (or permanently delete) a given array of posts
	 *
	 * @param array $objects to trash
	 * @param bool  $hard if true, permanently delete them
	 */
	private static function trashQuery( $objects, $hard = false ) {
		$objects->each(function( $object ) {
			$object->_trash( $hard );
		});

		return true;
	}

	/**
	 * Trash this object
	 *
	 * @param bool $hard if true, permanently delete it
	 */
	private function _trash( $hard ) {
		wp_delete_post( $this->id, $hard );
		$this->trashed = true;

		if ( $hard ) {
			unset( $this );
		}
	}

	/**
	 * Add the operation to the args $arrayName = array('' => , );
	 *
	 * @param  $args the existing args array
	 * @param  $operation the next operation to add
	 * @param  $type of object we're modifying (post/comment/option etc)
	 * @return $args
	 */
	private function appendQueryArg( $args, $op, $type = 'post' ) {
		/*
		 * This is very primitive right now, in the future there will
		 * be a proper system for building this up...
		 */
		$operation = $op['operation'];
		$options = $op['options'];

		if ( 'where' == $operation ) {
			$field   = $options['field'];
			$compare = $options['compare'];
			$value   = $options['value'];

			$core = array(
				'ID',
				'post_author',
				'post_date',
				'post_date_gmt',
				'post_content',
				'post_title',
				'post_excerpt',
				'post_status',
				'comment_status',
				'ping_status',
				'post_password',
				'post_name',
				'to_ping',
				'pinged',
				'post_modified',
				'post_modified_gmt',
				'post_content_filtered',
				'post_parent',
				'guid',
				'menu_order',
				'post_type',
				'post_mime_type',
				'comment_count',
			);

			// non-core fields get an underscore added to prevent them showing up in the admin
			if ( in_array( $field, $core ) ) {
				return;
			}
			$field .= '_';

			$args['meta_query'] = self::appendMetaQuery( $options['meta_query'], $field, $compare, $value );
		}
		if ( 'limit' == $operation ){
			$args['posts_per_page'] = $options;
		}

		return $args;
	}

	/**
	 * Append a meta clause to the meta query
	 *
	 * @param  array $meta_query the existing query
	 * @param  string $field the field
	 * @param  string $compare the comparison
	 * @param  string $value the value
	 * @return array $meta_query the new meta query array
	 */
	private function appendMetaQuery( $meta_query, $field, $compare, $value ) {
		if ( is_null( $meta_query ) ) {
			$meta_query = array(
				'relation' => 'AND'
			);
		}

		$meta_query[] = array(
			'key' => $field,
			'compare' => $compare,
			'value' => $value
		);

		return $meta_query;
	}

	/**
	 * Set the called class
	 */
	protected function _setCalledClass( $class ) {
		$this->called_class = $class;
	}

	/**
	 * Set the query object (called from the static method)
	 */
	public function _setQuery( $query ) {
		$this->query[] = $query;
	}

	/**
	 * Return the ID of the object if we cast it to a string
	 */
	public function __tostring() {
		return $this->id;
	}

}
