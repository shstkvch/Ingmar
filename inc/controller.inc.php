<?php
/**
 * Main controller class
 */

class ThomasController {

	/**
	 * Get a model if it exists
	 */
	public function __get( $key ) {
		$key = ucfirst( substr( $key, 0, -1 ) );

		if ( $key == 'Post' ) {
			return new ThomasModel();
		}

		return $key;
	}

}

class IndexController extends ThomasController {

	/**
	 * Main controller
	 */
	public function main( $view ) {
		$view['posts'] = $this->posts->get();

		$new_post = $this->posts->create();
		$new_post->title = 'test';
		$new_post->save();

		return 'index';
	}

}
