<?php
/**
 * Main Ingmar object class
 */

class Ingmar_Object {

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
	 * Validations to run when saving this object
	 *
	 * @var array $validations
	 */
	protected $validations = array();

	/**
	 * Constructor
	 */
 	public function __construct( $fields_data = array() ) {
		$this->fields_data = $fields_data;
	}

	/**
	 * Save the object and its fields to the database
	 *
	 * If successful, the object will be saved in the database and returned.
	 * If there's a validation error, an array of validation errors will be returned.
	 * If something else goes wrong, an exception will be thrown.
	 *
	 * @return Ingmar_Object|Array
	 * @throws Exception
	 */
	public function save() {
		// do validations...

		$errors = $this->validateFields();

		if ( count( $errors ) ) {
			return $errors;
		}

		var_dump( $this->fields_data ); die();

		$sanitised_fields = $this->sanitiseFields();

		$this->saveOrUpdate( $sanitised_fields );

		if ( !is_integer( $this->id ) || $this->id < 1 ) {
			throw new Exception( 'Invalid ID for Ingmar_Object' );
			return false;
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
		);

		// Hacky -- add and remove the wp_insert_post_empty_content filter
		// so we can insert blank posts
		add_filter( 'wp_insert_post_empty_content', '__return_false', 15 );

		$this->id = wp_insert_post( $args, true );

		remove_filter( 'wp_insert_post_empty_content', '__return_false', 15 );

		return true;
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
		 * TODO: Check if a field value is a valid Ingmar_User
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
		array_map( function( $item ) {
			var_dump( $item );
		}, $this->fields_data );
		die();
	}

	/**
	 * Public API for the object
	 */
	public function __get( $key ) {
		return $this->fields_data[$key];
	}

	/**
	 * Public API for the object
	 *
	 * @TODO validation etc
	 */
	public function __set( $key, $value ) {
		$this->fields_data[$key] = $value;
	}

}
