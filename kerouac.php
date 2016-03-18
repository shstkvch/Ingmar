<?php
/**
 * Plugin Name: Thomas
 * Version: 0.1-alpha
 * Description: Improved APIs for faster WordPress development.
 * Author: David Hewitson
 * Author URI: http://github.com/shstkvch/
 * Plugin URI: http://github.com/shstkvch/thomas
 * Text Domain: thomas
 * Domain Path: /languages
 * @package Thomas
 */

require( 'inc/object.inc.php');
require( 'inc/collection.inc.php');

class Client extends ThomasObject {

	protected $fields = array(
		'name',
		'profession',
		'location'
	);

}


class Testimonial extends ThomasObject {

	/**
	 * Declare your fields here for the object.
	 */
	protected $fields = array(
		'title',
		'author',
		'rating',
		'client'
	);

	/**
	 * You can relate any field to another ThomasObject by giving its class
	 * name here. We'll link it using it's post ID.
	 *
	 * You can access relations like this:
	 *
	 * Testimonial->first()->client
	 */
	protected $relations = array(
		'client' => 'Client'
	);

	protected $validations = array(
		'title' => 'is_string',
		'author' => array(
			'is_user',
			'exists'
		),
		'rating' => array(
			'is_integer',
			'$val <= 5',
			'$val >= 0',
			'exists'
		)
	);

	private function getRatingPercentage() {
		return $this->rating;
	}

}

add_action('init', function() {
	$first = Testimonial::first();

	var_dump( $first );
	die();
});
