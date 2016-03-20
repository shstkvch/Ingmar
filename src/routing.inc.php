<?php
/**
 * Main Thomas router class
 */

namespace Thomas\Routing;

class ThomasRouter {

	protected $routes = array();

	static private $instance;

	protected $controller_to_load = null;

	function __construct() {
		add_filter( 'template_include', array( $this, 'loadRoute' ) );
		$this->init( $this );
	}

	function loadRoute( $template ) {
		$template_slug = substr( basename( $template ), 0, -4 );

		if ( $this->routes[ $template_slug ] ){
			$this->controller_to_load = $this->routes[ $template_slug ];

			$template = realpath( dirname( __FILE__ ) . '/routingDummyTemplate.inc.php' );
		}

		return $template;
	}

	function init( $router ) {
		$router::get( 'index', 'IndexController' );
		$router::get( '404', '404Controller' );
	}

	function get( $route, $controller ) {
		$this->routes[$route] = $controller;
	}

	function instance() {
		if ( !self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	function loadController() {
		if ( $this->controller_to_load ) {
			if ( class_exists( $this->controller_to_load ) ) {
				$controller = new $this->controller_to_load();

				/*
				 * TODO: the ThomasViewContext will use __get to catch changes
				 * (array-like) and save them to the $controller object somehow
				 */
				$view_context = new ThomasViewContext( $controller );

				$controller->main( $view_context );
			}
		}
	}
}

ThomasRouter::instance();
// var_dump( ThomasRouter::instance() );
