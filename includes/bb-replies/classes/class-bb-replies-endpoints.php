<?php
defined( 'ABSPATH' ) || exit;

/**
 * Forums endpoints.
 *
 * @since 0.1.0
 */
class BB_REST_Replies_Controller extends WP_REST_Controller {

	/**
	 * Constructor.
	 *
	 * @since 0.1.0
	 */
	public function __construct() {
		// var_dump( bbpress() );
		$this->namespace = bb_rest_namespace() . '/' . bb_rest_version();
		$this->rest_base = 'replies'; // bbpress()->reply_post_type;
	}


}
