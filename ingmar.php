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


class HTBD_Testimonial extends Ingmar_Object {

	protected $fields = array(
		'title',
		'author',
		'rating'
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
	$testimonial = new HTBD_Testimonial();
	
	$testimonial->title = 'My first testimonial';

	$testimonial->save();
});
