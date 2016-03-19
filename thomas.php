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

require( 'inc/model.inc.php' );
require( 'inc/collection.inc.php' );
require( 'inc/routing.inc.php' );
require( 'vendor/autoload.php' );

class Client extends ThomasModel {

	protected $fields = array(
		'name',
		'profession',
		'location'
	);

}


class Testimonial extends ThomasModel {

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
	 * You can relate any field to another ThomasModel by giving its class
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
class Shop extends ThomasModel {

	// add fields to objects (stored as post meta)
	protected $fields = [
		'location',
		'price_range'
	];

}

class Shopkeeper extends ThomasModel {

	protected $fields = [
		'name',
		'temperament',
		'works_at'
	];

	/*
	 * Create arbitrary relationships between objects,
	 * just specify the name of the class you created:
	 */
	protected $relations = [
		'works_at' => 'Shop'
	];

}


add_action('init', function() {
	// $shop = new Shop();
	// $shop->location = 'High Street';
	// $shop->save();
	//
	// var_dump( $shop );

	// var_dump( Shop::get() );
	$shops = Shop::limit(10);
	var_dump( $shops );
});
