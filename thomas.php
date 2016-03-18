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

require( 'inc/model.inc.php');
require( 'inc/collection.inc.php');

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

	$shop = new Shop();
	$shop->location = 'High Street';

	$greengrocer = new Shopkeeper();
	$greengrocer->name = 'Simon';
	$greengrocer->works_at = $grocer;
	$greengrocer->temperament = 'Rude';

	/*
	 * You only need to save the highest-level object,
	 * related objects are saved automatically:
	 */
	$greengrocer->save();

	// You can also pass arrays into the constructor:
	$greengrocer2 = new Shopkeeper( [
		'name' => 'Tim',
		'works_at' => new Shop( [
			'location' => 'South Street'
		] ),
		'temperament' => 'polite'
	] );

	$greengrocer2->save();

	/*
	 * You can now query the greengrocers model by
	 * chaining methods -- much nicer than faffing with
	 * WP_Query:
	 */
	$collection = Shopkeeper::get();

	/*
	 * ThomasCollections are very basic right now. You can
	 * get the first, last items and enumerate them --
	 */
	var_dump( $collection );
	die();
});
