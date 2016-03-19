<?php
/**
 * Main Thomas router class
 */

class ThomasRouter {

	static protected $routes = array();

	static private $instance;

	static public $controller_to_load = null;

	function __construct() {
		add_filter( 'template_include', function( $route ) {
			$route_slug = substr( basename( $route ), 0, -4 );
			$path = realpath( dirname( __FILE__ ) . '/routingDummyTemplate.inc.php' );

			if ( self::$routes[ $route_slug ] ){
				self::$controller_to_load = self::$routes[ $route_slug ];
				return $path;
			}

			return $route;
		});

		$this->init( $this );
	}

	function init( $router ) {
		$router::get( 'index', 'indexController' );
		$router::get( '404', '404Controller' );
	}

	static function get( $route, $controller ) {
		self::$routes[$route] = $controller;
	}

	function instance() {
		if ( !self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	function loadController() {
		if ( self::$controller_to_load ) {
			$loader = new Twig_Loader_Array( array(
				'index.twig' => 'Hello {{ name }}'
			) );

			$cache_dir = realpath( __DIR__ . '/../twig_cache/' );

			if ( !is_dir( $cache_dir ) ) {
				mkdir( $cache_dir, 0777, true );
			}

			$twig = new Twig_Environment( $loader, array(
				'auto_reload' => true,
				'cache' => $cache_dir
			) );

			print $twig->render('index.twig', array(
				'name' => 'david'
			) );
		}
	}
}

ThomasRouter::instance();
// var_dump( ThomasRouter::instance() );
