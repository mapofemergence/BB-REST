<?php
/**
 * Plugin Name: bbPress REST API
 * Plugin URI: https://bbpress.org
 * Description: bbPress extension for WordPress' JSON-based REST API.
 * Version:	    0.1.0
 * Author:	    bbPress
 * Author URI:  https://bbpress.org
 * Donate link: https://bbpress.org
 * License:	    GPLv2 or later
 * Text Domain: bp-rest
 * Domain Path: /languages
 */

/**
 * The following is just a placeholder; licensing should be discussed
 * with the bbPress team, to see what are the most desirable options
 *
 * Copyright (c) 2017 bbPress (email: info@bbpress.org)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2 or, at
 * your discretion, any later version, as published by the Free
 * Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

defined( 'ABSPATH' ) || exit;

/**
 * bbPress rest api namespace.
 *
 * @since 0.1.0
 * @return string
 */
function bb_rest_namespace() {
	/**
	 * Filters api namespace.
	 *
	 * @since 0.1.0
	 */
	return apply_filters( 'bb_rest_namespace', 'bbpress' );
}

/**
 * bbPress rest api version.
 *
 * @since 0.1.0
 * @return string
 */
function bb_rest_version() {
	return 'v1';
}

/**
 * Register bbPress endpoints.
 *
 * @since 0.1.0
 * @return void
 */
function bb_rest_api_endpoints() {

	// Requires https://wordpress.org/plugins/rest-api/
	if ( ! class_exists( 'WP_REST_Controller' ) ) {
		return;
	}

	require_once( dirname( __FILE__ ) . '/includes/bb-core/classes/class-bb-core-endpoints.php' );
	$controller = new BB_REST_Core_Controller();
	$controller->register_routes();

	require_once( dirname( __FILE__ ) . '/includes/bb-forums/classes/class-bb-forums-endpoints.php' );
	$controller = new BB_REST_Forums_Controller();
	$controller->register_routes();
	// the following is a promemo to see what (if) filters might be needed
	// need to check BP-REST and figure out how filters are used there
	// require_once( dirname( __FILE__ ) . '/includes/bb-forums/bb-forums-filters.php' );

	require_once( dirname( __FILE__ ) . '/includes/bb-topics/classes/class-bb-topics-endpoints.php' );
	$controller = new BB_REST_Topics_Controller();
	$controller->register_routes();
	
	require_once( dirname( __FILE__ ) . '/includes/bb-replies/classes/class-bb-replies-endpoints.php' );
	$controller = new BB_REST_Replies_Controller();
	$controller->register_routes();

	require_once( dirname( __FILE__ ) . '/includes/bb-topic-tags/classes/class-bb-topic-tags-endpoints.php' );
	$controller = new BB_REST_Topic_Tags_Controller();
	$controller->register_routes();

	require_once( dirname( __FILE__ ) . '/includes/bb-statistics/classes/class-bb-statistics-endpoints.php' );
	$controller = new BB_REST_Statistics_Controller();
	$controller->register_routes();

	require_once( dirname( __FILE__ ) . '/includes/bb-swagger/classes/class-bb-swagger-endpoints.php' );
	$controller = new BB_REST_Swagger_Controller();
	$controller->register_routes();

	do_action( 'bb_rest_api_init' );
}

// http://wordpress.stackexchange.com/questions/240459/wordpress-rest-api-call-to-member-function-register-route
add_action( 'rest_api_init', 'bb_rest_api_endpoints' );
// the following might be required in bbPress core, to expose the hook in the right place
// will need to check when the above is resolved
// add_action( 'bb_rest_api_init', 'bb_rest_api_endpoints' );
