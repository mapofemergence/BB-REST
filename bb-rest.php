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

	// if ( bb_is_active( 'forums' ) ) {
		require_once( dirname( __FILE__ ) . '/includes/bb-forums/classes/class-bb-forums-endpoints.php' );
		$controller = new BB_REST_Forums_Controller();
		$controller->register_routes();
	// }

	// if ( bb_is_active( 'topics' ) ) {
		require_once( dirname( __FILE__ ) . '/includes/bb-topics/classes/class-bb-topics-endpoints.php' );
		$controller = new BB_REST_Topics_Controller();
		$controller->register_routes();

		// require_once( dirname( __FILE__ ) . '/includes/bb-topics/bb-topics-filters.php' );
	// }

	// if ( bb_is_active( 'replies' ) ) {
		require_once( dirname( __FILE__ ) . '/includes/bb-replies/classes/class-bb-replies-endpoints.php' );
		$controller = new BB_REST_Replies_Controller();
		$controller->register_routes();
	// }

	require_once( dirname( __FILE__ ) . '/includes/bb-statistics/classes/class-bb-statistics-endpoints.php' );
	$controller = new BB_REST_Statistics_Controller();
	$controller->register_routes();

	require_once( dirname( __FILE__ ) . '/includes/bb-topic-tags/classes/class-bb-topic-tags-endpoints.php' );
	$controller = new BB_REST_Topic_Tags_Controller();
	$controller->register_routes();

	// if ( bb_is_active( 'xprofile' ) ) {
	// 	require_once( dirname( __FILE__ ) . '/includes/bb-xprofile/classes/class-bb-xprofile-groups-endpoints.php' );
	// 	$controller = new BB_REST_XProfile_Groups_Controller();
	// 	$controller->register_routes();

	// 	require_once( dirname( __FILE__ ) . '/includes/bb-xprofile/classes/class-bb-xprofile-fields-endpoints.php' );
	// 	$controller = new BB_REST_XProfile_Fields_Controller();
	// 	$controller->register_routes();
	// }

}


// temporary solution to get forums via WP-REST API
// will need to be replaced by direct calls to bbPress, if possible
// add_filter( 'bbp_register_forum_post_type', 'api_expose_forum' );
// function api_expose_forum( $vars ){
// 	$vars['show_in_rest'] = true;
// 	$vars['rest_base'] = 'forums';
// 	return $vars;
// }
// add_filter( 'bbp_register_topic_post_type', 'api_expose_topic' );
// function api_expose_topic( $vars ){
// 	$vars['show_in_rest'] = true;
// 	$vars['rest_base'] = 'topics';
// 	return $vars;
// }
// add_filter( 'bbp_register_reply_post_type', 'api_expose_reply' );
// function api_expose_reply( $vars ){
// 	$vars['show_in_rest'] = true;
// 	$vars['rest_base'] = 'replies';
// 	return $vars;
// }

// http://wordpress.stackexchange.com/questions/240459/wordpress-rest-api-call-to-member-function-register-route
// http://stv.whtly.com/2011/09/03/forcing-a-wordpress-plugin-to-be-loaded-before-all-other-plugins/
add_action( 'rest_api_init', 'bb_rest_api_endpoints' );
// the following might be implemented in the bbPress plugin to expose the hook in the right place
// add_action( 'bb_rest_api_init', 'bb_rest_api_endpoints' );
