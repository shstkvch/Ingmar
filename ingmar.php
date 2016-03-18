<?php
/**
 * Plugin Name: Ingmar
 * Version: 0.1-alpha
 * Description: Modern development tools for WordPress
 * Author: David Hewitson
 * Author URI: http://github.com/shstkvch/
 * Plugin URI: http://github.com/shstkvch/ingmar
 * Text Domain: ingmar
 * Domain Path: /languages
 * @package Ingmar
 */

require( 'inc/object.inc.php');
require( 'inc/collection.inc.php');

class Client extends IngmarObject {

	protected $fields = array(
		'name',
		'profession',
		'location'
	);

}


class Testimonial extends IngmarObject {

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
	 * You can relate any field to another IngmarObject by giving its class
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
	// $testimonial = new Testimonial();
	// $testimonial->title = 'My fortieth testimonial';
	// $testimonial->client = new Client();
	// $testimonial->client->name = 'Daff';
	// $testimonial->save();
	//
	$first = Testimonial::limit(10)->get()->first();
	// $first->client = new Client();
	// $first->client->name = 'hhehwhehwe';
	// $first->save();

	var_dump( $first );
	die();
});
