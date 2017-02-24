<?php
defined( 'ABSPATH' ) || exit;

/**
 * Topics endpoints.
 *
 * @since 0.1.0
 */
class BB_REST_Topic_Tags_Controller extends WP_REST_Controller {

	/**
	 * Constructor.
	 *
	 * @since 0.1.0
	 */
	public function __construct() {
		$this->namespace = bb_rest_namespace() . '/' . bb_rest_version();
		$this->rest_base = 'tags';
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

		// register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)', array(
		// 	array(
		// 		'methods'             => WP_REST_Server::READABLE,
		// 		'callback'            => array( $this, 'get_item' ),
		// 		// 'permission_callback' => array( $this, 'get_item_permissions_check' ),
		// 		'args'                => array(
		// 			'context' => $this->get_context_param( array( 'default' => 'view' ) ),
		// 		),
		// 	),
		// 	'schema' => array( $this, 'get_public_item_schema' ),
		// ) );
	}

	/**
	 * Get the plugin schema, conforming to JSON Schema.
	 *
	 * @since 0.1.0
	 *
	 * @return array
	 */
	public function get_item_schema() {

		// // http://wordpress.stackexchange.com/questions/37721/whats-the-difference-between-term-id-and-term-taxonomy-id
		// 'id'			'term_id'
		// 				'term_taxonomy_id'
		// // 'taxonomy'		    string(9) "topic-tag"
		// // http://wordpress.stackexchange.com/questions/23169/what-is-term-group-for-order-by-in-get-terms
		// // 'term_group'		    int(0)
		// 'slug'		    string(11) "buoneprassi"
		// 'name'		    string(11) "buonePrassi"
		// // 'description'		    string(0) ""
		// 'count'		    int(1)
		// // 'parent'		int(0)
		// // 'filter'		    string(3) "raw"

		$schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'topic-tag',
			'type'       => 'object',

			'properties' => array(
				'id' => array(
					'context'     => array( 'view', 'edit' ),
					'description' => __( 'A unique alphanumeric ID for the object.', 'bbpress' ),
					'readonly'    => true,
					'type'        => 'integer',
				),

				'slug' => array(
					'context'     => array( 'view', 'edit' ),
					'description' => __( 'The permalink to this object on the site.', 'bbpress' ),
					'type'        => 'string',
				),

				'link' => array(
					'context'     => array( 'view', 'edit' ),
					'description' => __( 'The permalink to this object on the site.', 'bbpress' ),
					'format'      => 'url',
					'type'        => 'string',
				),

				'name' => array(
					'context'     => array( 'view', 'edit' ),
					'description' => __( 'HTML title of the object.', 'bbpress' ),
					'type'        => 'string',
				),

				// 'description' => array(
				// 	'context'     => array( 'view', 'edit' ),
				// 	'description' => __( 'HTML content of the object.', 'bbpress' ),
				// 	'type'        => 'string',
				// ),

				'count' => array(
					'context'     => array( 'view', 'edit' ),
					'description' => __( 'The count of posts this tag is associated with.', 'bbpress' ),
					'readonly'    => true,
					'type'        => 'integer',
				),

				// 'status' => array(
				// 	'context'     => array( 'view', 'edit' ),
				// 	'description' => __( 'Whether the object has been marked as spam or not.', 'bbpress' ),
				// 	'type'        => 'string',
				// 	'enum'        => array( 'published', 'spam' ),
				// ),

				// 'parent' => array(
				// 	'description'  => __( 'The ID of the parent of the object.', 'bbpress' ),
				// 	'type'         => 'integer',
				// 	'context'      => array( 'view', 'edit' ),
				// ),
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

		$params['order'] = array(
			'description'       => __( 'Order sort attribute ascending or descending.', 'bbpress' ),
			'type'              => 'string',
			'default'           => 'desc',
			'enum'              => array( 'asc', 'desc' ),
			'validate_callback' => 'rest_validate_request_arg',
		);

		$params['orderby'] = array(
			'description'       => __( 'Sort collection by object attribute.', 'bbpress' ),
			'type'              => 'string',
			'default'           => 'count',
			'enum'              => array( 'count', 'id', 'name', 'slug' ), // date, relevance, id, include, title, slug
			'validate_callback' => 'rest_validate_request_arg',
		);


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

		$params['forums'] = array(
			'description'       => __( 'Ensure the topics belong to specific forums.', 'bbpress' ),
			'type'              => 'array',
			'default'           => array(),
			'sanitize_callback' => 'wp_parse_id_list',
		);

		return $params;
	}

	/**
	 * Retrieve activities.
	 *
	 * @since 0.1.0
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Request List of topic object data.
	 */
	public function get_items( $request ) {

		$args = $request->get_params();
			// 'action'                => $topic->action,
		// check what the following was doing, as it could be handy elsewhere
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
		// if ( ! bbp_has_topics() )
		//	return;

		// will possibly need to handle topic permissions downstream
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
		
		// $posts_query = new WP_Query();
		// // https://codex.wordpress.org/Class_Reference/WP_Query
		// // https://github.com/WP-API/WP-API/blob/develop/lib/endpoints/class-wp-rest-posts-controller.php
		// $query_args = array(
		// 	'post_type' => 'topic',
		// 	'post_status' => 'any',
		// 	// 'tax_query' => array(
		// 	// 	array(
		// 	// 		'taxonomy' => 'people',
		// 	// 		'field'    => 'slug',
		// 	// 		'terms'    => 'bob',
		// 	// 	),
		// 	// ),
		// );


		$query_args = array(
			// 'orderby'  => 'count',
			// 'order'    => 'DESC',
			'taxonomy' => 'topic-tag',
		);
		
		// note: teh order here is important as we need the "per_page" argument, befor unsetting it
		if ( array_key_exists( 'page', $args ) ) {
			$args['offset'] = $args['page'] *  $args['per_page'];
			unset( $args['page'] );
		}
		if ( array_key_exists( 'per_page', $args ) ) {
			$args['number'] = $args['per_page'];
			unset( $args['per_page'] );
		}
		if ( array_key_exists( 'order', $args ) ) {
			$args['order'] = strtoupper( $args['order'] );
		}
		// if ( array_key_exists( 'forums', $args ) ) {
		// 	$args['post_parent__in'] = $args['forums'];
		// 	unset( $args['forums'] );
		// }
		$query_args = array_merge( $query_args, $args);

		$retval = array();
		// https://developer.wordpress.org/reference/functions/get_terms/
		// order by (defaults to 'name')
		// 'name', 'slug', 'term_group', 'term_id', 'id', 'description', 'count',
		// 'include' (to match the 'order' of the 'include' param), 'meta_value',
		// 'meta_value_num' (the value of meta_key, the array keys of meta_query,
		// 'none' to omit the ORDER BY clause
		$tags = get_terms( $query_args );
		// consider replacing the above call with a lower level query: WP_Meta_Query
		// https://codex.wordpress.org/Class_Reference/WP_Meta_Query
		// var_dump( $tags );

		foreach ( $tags as $tag ) {
			// https://bproots.bbroots.com/reference/functions/bbp_get_topic/
			// http://hookr.io/functions/bbp_get_topic/
			// $topic = bbp_get_topic( $topic );
			$retval[] = $this->prepare_response_for_collection(
				$this->prepare_item_for_response( $tag, $args )
			);
		}

		// https://developer.wordpress.org/reference/functions/rest_ensure_response/
		return rest_ensure_response( $retval );
	}

	/**
	 * Retrieve topic.
	 *
	 * @since 0.1.0
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Request|WP_Error Plugin object data on success, WP_Error otherwise.
	 */
	// public function get_item( $request ) {

	// 	// TODO: query logic. and permissions. and other parameters that might need to be set. etc
	// 	// $topic = bp_topic_get( array(
	// 	// 		'in' => (int) $request['id'],
	// 	// ) );

	// starting point
	// https://bproots.bbroots.com/reference/functions/bbp_get_topic_tag_list/

		
	// 	$query_args = array(
	// 		'post_type' => 'topic',
	// 		'post_status' => 'any',
	// 		'p' => (int) $request['id']
	// 	);
	// 	$post_query = new WP_Query();
	// 	// check if the following is legit or it is required to handle exceptions
	// 	$topic = $post_query->query( $query_args )[0];
	// 	// check if the following is better and implications
	// 	// $topic =  get_post( $request['id'] );

	// 	$retval = array(
	// 		$this->prepare_response_for_collection(
	// 			$this->prepare_item_for_response( $topic, $request )
	// 		),
	// 	);

	// 	return rest_ensure_response( $retval );

	// }

	/**
	 * Check if a given request has access to get information about a specific topic.
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
	 * Check if a given request has access to topic items.
	 *
	 * @since 0.1.0
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|bool
	 */
	public function get_items_permissions_check( $request ) {
		// TODO: handle private topics' tags etc
		return apply_filters( 'bb_rest_topic_tag_items_premission', true, $request );
	}

	/**
	 * Prepares topic data for return as an object.
	 *
	 * @since 0.1.0
	 *
	 * @param stdClass $topic Topic data.
	 * @param array    $request WP_REST_Request.
	 * @param boolean  $is_raw Optional, not used. Defaults to false.
	 * @return WP_REST_Response
	 */
	public function prepare_item_for_response( $tag, $request, $is_raw = false ) {

		// $meta = get_post_meta( $tag->ID );

		$data = array(
			'name'            	    => $tag->name,
			// 'description'           => $tag->description,
			'id'                    => $tag->term_taxonomy_id, // term_id
			'slug'                  => $tag->slug,
			'link'                  => get_site_url() . '/forums/topic-tag/' . $tag->slug,
			// the following will require a check for topics as parents, maybe
			// 'parent'                => $tag->post_parent,
			// 'status'                => $tag->is_spam ? 'spam' : 'published',
			// the following might be redundant
			// 'type'                  => $tag->taxonomy,
			// http://wordpress.stackexchange.com/questions/23169/what-is-term-group-for-order-by-in-get-terms
			// 'term_group'		    int(0)
			// 'slug'		     => $tag->slug,
			'count'		     => $tag->count
		);

		$context = ! empty( $request['context'] ) ? $request['context'] : 'view';
		$data    = $this->add_additional_fields_to_object( $data, $request );
		$data    = $this->filter_response_by_context( $data, $context );

		$response = rest_ensure_response( $data );
		$response->add_links( $this->prepare_links( $tag ) );
		
		// var_dump( $response );

		/**
		 * Filter a topic value returned from the API.
		 *
		 * @param array           $response
		 * @param WP_REST_Request $request Request used to generate the response.
		 */
		// return apply_filters( 'bb_rest_prepare_topic_item', $response, $request );
		return $response;
	}

	/**
	 * Prepare links for the request.
	 *
	 * @since 0.1.0
	 *
	 * @param array $topic Topic.
	 * @return array Links for the given plugin.
	 */
	protected function prepare_links( $tag ) {

		$base = sprintf( '/%s/%s/', $this->namespace, $this->rest_base );

		// Entity meta.
		$links = array(
			'self' => array(
				// the following will require
				// register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)'
				// to be implemented
				'href' => rest_url( $base . $tag->term_taxonomy_id ),
			),
			'collection' => array(
				'href' => rest_url( $base ),
			),
			// check what key should be used for the following
			// check if redundancy is required with the above returned values
			// $links['up'] = array(
			// 	'href' => rest_url( $base . $topic->item_id ),
			// );
		);

		return $links;
	}

}
