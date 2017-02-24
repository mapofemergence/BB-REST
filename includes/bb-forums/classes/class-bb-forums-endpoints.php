<?php
defined( 'ABSPATH' ) || exit;

/**
 * Forums endpoints.
 *
 * @since 0.1.0
 */
class BB_REST_Forums_Controller extends WP_REST_Controller {

	/**
	 * Constructor.
	 *
	 * @since 0.1.0
	 */
	public function __construct() {
		$this->namespace = bb_rest_namespace() . '/' . bb_rest_version();
		$this->rest_base = 'forums'; // bbpress()->forum_post_type . 's'
	}

	/**
	 * Register the plugin routes.
	 *
	 * @since 0.1.0
	 */
	public function register_routes() {
		register_rest_route( $this->namespace, '/' . $this->rest_base, array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_items' ),
				// 'permission_callback' => array( $this, 'get_items_permissions_check' ),
				'args'                => $this->get_collection_params(),
			),
			'schema' => array( $this, 'get_public_item_schema' ),
		) );

		register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_item' ),
				// 'permission_callback' => array( $this, 'get_item_permissions_check' ),
				'args'                => array(
					'context' => $this->get_context_param( array( 'default' => 'view' ) ),
				),
			),
			'schema' => array( $this, 'get_public_item_schema' ),
		) );
	}

	/**
	 * Get the plugin schema, conforming to JSON Schema.
	 *
	 * @since 0.1.0
	 *
	 * @return array
	 */
	public function get_item_schema() {

		$schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'forum',
			'type'       => 'object',

			'properties' => array(
				'id' => array(
					'context'     => array( 'view', 'edit' ),
					'description' => __( 'A unique alphanumeric ID for the object.', 'bbpress' ),
					'readonly'    => true,
					'type'        => 'integer',
				),

				'name' => array(
					'context'     => array( 'view', 'edit' ),
					'description' => __( 'The name of the forum.', 'bbpress' ),
					'type'        => 'string',
				),

				'author' => array(
					'context'     => array( 'view', 'edit' ),
					'description' => __( 'The ID for the creator of the object.', 'bbpress' ),
					'type'        => 'integer',
				),

				'link' => array(
					'context'     => array( 'view', 'edit' ),
					'description' => __( 'The permalink to this object on the site.', 'bbpress' ),
					'format'      => 'url',
					'type'        => 'string',
				),

				'content' => array(
					'context'     => array( 'view', 'edit' ),
					'description' => __( 'HTML content of the object.', 'bbpress' ),
					'type'        => 'string',
				),

				'date' => array(
					'description' => __( 'The human readable date time since item posted', 'bbpress' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
				),

				'timestamp' => array(
					'description' => __( "The date the object was published, in the site's timezone.", 'bbpress' ),
					'type'        => 'string',
					'format'      => 'date-time',
					'context'     => array( 'view', 'edit' ),
				),

				'status' => array(
					'context'     => array( 'view', 'edit' ),
					'description' => __( 'Whether the object has been marked as spam or not.', 'bbpress' ),
					'type'        => 'string',
					'enum'        => array( 'published', 'spam' ),
				),

				'parent' => array(
					'description'  => __( 'The ID of the parent of the object.', 'bbpress' ),
					'type'         => 'integer',
					'context'      => array( 'view', 'edit' ),
				),

				'type' => array(
					'context'     => array( 'view', 'edit' ),
					'description' => __( 'The activity type of the object.', 'bbpress' ),
					'type'        => 'string',
					'enum'        => array_keys( bp_activity_get_types() ),
				),
			),
		);

		return $schema;
	}

	/**
	 * Get the query params for collections of plugins.
	 *
	 * @since 0.1.0
	 *
	 * @return array
	 */
	public function get_collection_params() {

		$params = parent::get_collection_params();
		$params['context']['default'] = 'view';

		// $params['exclude'] = array(
		// 	'description'       => __( 'Ensure result set excludes specific IDs.', 'bbpress' ),
		// 	'type'              => 'array',
		// 	'default'           => array(),
		// 	'sanitize_callback' => 'wp_parse_id_list',
		// );

		// $params['include'] = array(
		// 	'description'       => __( 'Ensure result set includes specific IDs.', 'bbpress' ),
		// 	'type'              => 'array',
		// 	'default'           => array(),
		// 	'sanitize_callback' => 'wp_parse_id_list',
		// );

		// $params['order'] = array(
		// 	'description'       => __( 'Order sort attribute ascending or descending.', 'bbpress' ),
		// 	'type'              => 'string',
		// 	'default'           => 'desc',
		// 	'enum'              => array( 'asc', 'desc' ),
		// 	'validate_callback' => 'rest_validate_request_arg',
		// );

		// $params['after'] = array(
		// 	'description'       => __( 'Limit result set to items published after a given ISO8601 compliant date.', 'bbpress' ),
		// 	'type'              => 'string',
		// 	'format'            => 'date-time',
		// 	'validate_callback' => 'rest_validate_request_arg',
		// );

		$params['per_page'] = array(
			'description'       => __( 'Maximum number of results returned per result set.', 'bbpress' ),
			'default'           => 20,
			'type'              => 'integer',
			'sanitize_callback' => 'absint',
			'validate_callback' => 'rest_validate_request_arg',
		);

		$params['page'] = array(
			'description'       => __( 'Offset the result set by a specific number of pages of results.', 'bbpress' ),
			'default'           => 1,
			'type'              => 'integer',
			'sanitize_callback' => 'absint',
			'validate_callback' => 'rest_validate_request_arg',
		);

		// $params['author'] = array(
		// 	'description'       => __( 'Limit result set to items created by specific authors.', 'bbpress' ),
		// 	'type'              => 'array',
		// 	'default'           => array(),
		// 	'sanitize_callback' => 'wp_parse_id_list',
		// 	'validate_callback' => 'rest_validate_request_arg',
		// );

		// $params['status'] = array(
		// 	'default'           => 'published',
		// 	'description'       => __( 'Limit result set to items with a specific status.', 'bbpress' ),
		// 	'type'              => 'string',
		// 	'enum'              => array( 'published', 'spam' ),
		// 	'sanitize_callback' => 'sanitize_key',
		// 	'validate_callback' => 'rest_validate_request_arg',
		// );

		// $params['primary_id'] = array(
		// 	'description'       => __( 'Limit result set to items with a specific prime assocation.', 'bbpress' ),
		// 	'type'              => 'array',
		// 	'default'           => array(),
		// 	'sanitize_callback' => 'wp_parse_id_list',
		// );

		// $params['secondary_id'] = array(
		// 	'description'       => __( 'Limit result set to items with a specific secondary assocation.', 'bbpress' ),
		// 	'type'              => 'array',
		// 	'default'           => array(),
		// 	'sanitize_callback' => 'wp_parse_id_list',
		// );

		// $params['component'] = array(
		// 	'description'       => __( 'Limit result set to items with a specific BuddyPress component.', 'bbpress' ),
		// 	'type'              => 'string',
		// 	'enum'              => array_keys( bp_core_get_components() ),
		// 	'sanitize_callback' => 'sanitize_key',
		// 	'validate_callback' => 'rest_validate_request_arg',
		// );

		// $params['search'] = array(
		// 	'description'       => __( 'Limit result set to items that match this search query.', 'bbpress' ),
		// 	'default'           => '',
		// 	'type'              => 'string',
		// 	'sanitize_callback' => 'sanitize_text_field',
		// 	'validate_callback' => 'rest_validate_request_arg',
		// );

		return $params;
	}

	/**
	 * Retrieve activities.
	 *
	 * @since 0.1.0
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Request List of activity object data.
	 */
	public function get_items( $request ) {

		$args = $request->get_params();
		// the following is used in BP-REST, check if useful here (as logic)
		// 'action'                => $topic->action,
		// $filters = array( 'object', 'action', 'user_id', 'primary_id', 'secondary_id' );
		// foreach ( $filters as $filter ) {
		// 	if ( isset( $args[ $filter ] ) ) {
		// 		$args['filter'][ $filter ] = $args[ $filter ];
		// 	}
		// }

		// if ( $args['in'] ) {
		// 	$args['count_total'] = false;
		// }


		// the following might be desirable for performance
		// if ( ! bbp_has_forums() )
		//	return;

		// will possibly need to handle forum permissions downstream
				// // Override certain options for security.
				// // @TODO: Verify and confirm this show_hidden logic, and check core for other edge cases.
				// if ( 'groups' === $args['component']  &&
				// 	(
				// 		groups_is_user_member( get_current_user_id(), $args['primary_id'] ) ||
				// 		bp_current_user_can( 'bp_moderate' )
				// 	)
				// ) {
				// 	$args['show_hidden'] = true;
				// }
		$posts_query = new WP_Query();
		// https://codex.wordpress.org/Class_Reference/WP_Query
		// https://github.com/WP-API/WP-API/blob/develop/lib/endpoints/class-wp-rest-posts-controller.php
		$query_args = array(
			'post_type' => 'forum',
			'post_status' => 'any',
			// 'tax_query' => array(
			// 	array(
			// 		'taxonomy' => 'people',
			// 		'field'    => 'slug',
			// 		'terms'    => 'bob',
			// 	),
			// ),
		);

		if ( array_key_exists( 'per_page', $args ) ) {
			$args['posts_per_page'] = $args['per_page'];
			unset( $args['per_page'] );
		}
		if ( array_key_exists( 'page', $args ) ) {
			$args['paged'] = $args['page'];
			unset( $args['page'] );
		}
		$query_args = array_merge( $query_args, $args);

		$retval = array();
		$forums = $posts_query->query( $query_args );
		
		foreach ( $forums as $forum ) {
			// https://bproots.bbroots.com/reference/functions/bbp_get_forum/
			// http://hookr.io/functions/bbp_get_forum/
			$forum = bbp_get_forum( $forum );
			$retval[] = $this->prepare_response_for_collection(
				$this->prepare_item_for_response( $forum, $args )
			);
		}

		// https://developer.wordpress.org/reference/functions/rest_ensure_response/
		return rest_ensure_response( $retval );
	}

	/**
	 * Retrieve forum.
	 *
	 * @since 0.1.0
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Request|WP_Error Plugin object data on success, WP_Error otherwise.
	 */
	public function get_item( $request ) {
		// TODO: query logi, permissions and other parameters that might need to be set.
		// $forum = bb_forum_get( array(
		// 		'in' => (int) $request['id'],
		// ) );
		$query_args = array(
			'post_type' => 'forum',
			'post_status' => 'any',
			'p' => (int) $request['id']
		);
		$post_query = new WP_Query();
		// http://wordpress.stackexchange.com/questions/176949/wp-query-by-post-id-doesnt-work-if-post-is-in-custom-post-status
		$forum =  get_post( $request['id'] );
		// check if the following is legit or it is required to handle exceptions
		// $forum = $post_query->query( $query_args )[0];

		$retval = array(
			$this->prepare_response_for_collection(
				$this->prepare_item_for_response( $forum, $request )
			),
		);

		return rest_ensure_response( $retval );
	}

	/**
	 * Check if a given request has access to get information about a specific activity.
	 *
	 * @since 0.1.0
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return bool
	 */
	public function get_item_permissions_check( $request ) {
		return $this->get_items_permissions_check( $request );
	}

	/**
	 * Check if a given request has access to activity items.
	 *
	 * @since 0.1.0
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|bool
	 */
	public function get_items_permissions_check( $request ) {
		// @todo: handle private forums etc
		return apply_filters( 'bb_rest_activity_items_premission', true, $request );
	}

	/**
	 * Prepares forum data for return as an object.
	 *
	 * @since 0.1.0
	 *
	 * @param stdClass $forum Forum data.
	 * @param array    $request WP_REST_Request.
	 * @param boolean  $is_raw Optional, not used. Defaults to false.
	 * @return WP_REST_Response
	 */
	public function prepare_item_for_response( $forum, $request, $is_raw = false ) {

		// $meta = get_post_meta( $forum->ID );

		// add slug
		// also: go through https://codex.wordpress.org/Class_Reference/WP_Post
		$data = array(
			'name'            	    => $forum->post_title,
			'author'                => $forum->post_author,
			'content'               => $forum->post_content,
			'timestamp'             => $forum->post_date, // $forum->post_date_gmt
			'date'                  => bbp_get_time_since ( $forum->post_date ),
			'id'                    => $forum->ID,
			'link'                  => $forum->guid,
			// the following will require a check if the parent is a forum, maybe
			// if that's the case it'll be something like the following:
			// 'parent'                => 'forum_comment' === $forum->type ? $forum->item_id : 0,
			'parent'                => $forum->post_parent,
			'status'                => $forum->post_status,
			// 'status'                => $forum->is_spam ? 'spam' : 'published',
			// the following might be redundant
			'type'                  => $forum->post_type,
		);

		$context = ! empty( $request['context'] ) ? $request['context'] : 'view';
		$data    = $this->add_additional_fields_to_object( $data, $request );
		$data    = $this->filter_response_by_context( $data, $context );

		$response = rest_ensure_response( $data );
		$response->add_links( $this->prepare_links( $forum ) );
		
		/**
		 * Filter a forum value returned from the API.
		 *
		 * @param array           $response
		 * @param WP_REST_Request $request Request used to generate the response.
		 */
		// return apply_filters( 'bb_rest_prepare_activity_item', $response, $request );
		return $response;
	}

	/**
	 * Prepare links for the request.
	 *
	 * @since 0.1.0
	 *
	 * @param array $forum Forum.
	 * @return array Links for the given plugin.
	 */
	protected function prepare_links( $forum ) {

		$base = sprintf( '/%s/%s/', $this->namespace, $this->rest_base );

		// Entity meta.
		$links = array(
			'self' => array(
				'href' => rest_url( $base . $forum->ID ),
			),
			'collection' => array(
				'href' => rest_url( $base ),
			),
			'author' => array(
				'href' => rest_url( '/wp/v2/users/' . $forum->post_author ),
			),
		);

		return $links;
	}

}
