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
			'title'      => 'reply',
			'type'       => 'object',

			'properties' => array(
				'id' => array(
					'context'     => array( 'view', 'edit' ),
					'description' => __( 'A unique alphanumeric ID for the object.', 'bbpress' ),
					'readonly'    => true,
					'type'        => 'integer',
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

				'title' => array(
					'context'     => array( 'view', 'edit' ),
					'description' => __( 'HTML title of the object.', 'bbpress' ),
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

				// 'status' => array(
				// 	'context'     => array( 'view', 'edit' ),
				// 	'description' => __( 'Whether the object has been marked as spam or not.', 'bbpress' ),
				// 	'type'        => 'string',
				// 	'enum'        => array( 'published', 'spam' ),
				// ),

				'parent' => array(
					'description'  => __( 'The ID of the parent of the object.', 'bbpress' ),
					'type'         => 'integer',
					'context'      => array( 'view', 'edit' ),
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

		$params['topic'] = array(
			'description'       => __( 'Ensure the topics belong to specific topics.', 'bbpress' ),
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
	 * @return WP_REST_Request List of reply object data.
	 */
	public function get_items( $request ) {

		$args = $request->get_params();

		// https://codex.wordpress.org/Class_Reference/WP_Query
		$posts_query = new WP_Query();
		// https://github.com/WP-API/WP-API/blob/develop/lib/endpoints/class-wp-rest-posts-controller.php
		$query_args = array(
			'post_type' => 'reply',
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
		if ( array_key_exists( 'topic', $args ) ) {
			$args['post_parent__in'] = $args['topic'];
			unset( $args['topic'] );
		}
		$query_args = array_merge( $query_args, $args);

		$retval = array();
		$replys = $posts_query->query( $query_args );

		foreach ( $replys as $reply ) {
			// https://bproots.bbroots.com/reference/functions/bbp_get_reply/
			// http://hookr.io/functions/bbp_get_reply/
			$reply = bbp_get_reply( $reply );
			$retval[] = $this->prepare_response_for_collection(
				$this->prepare_item_for_response( $reply, $args )
			);
		}

		// https://developer.wordpress.org/reference/functions/rest_ensure_response/
		return rest_ensure_response( $retval );
	}

	/**
	 * Retrieve reply.
	 *
	 * @since 0.1.0
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Request|WP_Error Plugin object data on success, WP_Error otherwise.
	 */
	public function get_item( $request ) {
		// @todo: query logic, permissions and other parameters that might need to be set
		
		// $reply = bb_reply_get( array(
		// 		'in' => (int) $request['id'],
		// ) );
		
		$query_args = array(
			'post_type' => 'reply',
			'post_status' => 'any',
			'p' => (int) $request['id']
		);
		// check if the following is legit or it is required to handle exceptions
		$post_query = new WP_Query();
		$reply = $post_query->query( $query_args )[0];
		// check if the following is better and which are the implications
		// $reply =  get_post( $request['id'] );

		$retval = array(
			$this->prepare_response_for_collection(
				$this->prepare_item_for_response( $reply, $request )
			),
		);

		return rest_ensure_response( $retval );

	}

	/**
	 * Check if a given request has access to get information about a specific reply.
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
	 * Check if a given request has access to reply items.
	 *
	 * @since 0.1.0
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|bool
	 */
	public function get_items_permissions_check( $request ) {
		// TODO: handle private replies etc
		return apply_filters( 'bb_rest_reply_items_premission', true, $request );
	}

	/**
	 * Prepares reply data for return as an object.
	 *
	 * @since 0.1.0
	 *
	 * @param stdClass $reply Topic data.
	 * @param array    $request WP_REST_Request.
	 * @param boolean  $is_raw Optional, not used. Defaults to false.
	 * @return WP_REST_Response
	 */
	public function prepare_item_for_response( $reply, $request, $is_raw = false ) {

		// @todo retrieve additional data with the following:
		// $meta = get_post_meta( $reply->ID );

		$data = array(
			'title'            	    => $reply->post_title,
			'author'                => $reply->post_author,
			'content'               => $reply->post_content,
			'timestamp'             => $reply->post_date, // $reply->post_date_gmt
			'date'                  => bbp_get_time_since ( $reply->post_date ),
			'id'                    => $reply->ID,
			'link'                  => $reply->guid,
			// the following might require a check if the parent is a forurm, maybe
			// if that's the case it'll be something like the following:
			// 'parent'                => is_forum( $reply->post_parent ) ? $reply->post_parent : 0,
			'parent'                => $reply->post_parent,
			// 'status'                => $reply->is_spam ? 'spam' : 'published',
			// the following might be redundant
			'type'                  => $reply->post_type,
		);

		$context = ! empty( $request['context'] ) ? $request['context'] : 'view';
		$data    = $this->add_additional_fields_to_object( $data, $request );
		$data    = $this->filter_response_by_context( $data, $context );

		$response = rest_ensure_response( $data );
		$response->add_links( $this->prepare_links( $reply ) );
		
		/**
		 * Filter a reply value returned from the API.
		 *
		 * @param array           $response
		 * @param WP_REST_Request $request Request used to generate the response.
		 */
		// return apply_filters( 'bb_rest_prepare_reply_item', $response, $request );
		return $response;
	}

	/**
	 * Prepare links for the request.
	 *
	 * @since 0.1.0
	 *
	 * @param array $reply Reply.
	 * @return array Links for the given plugin.
	 */
	protected function prepare_links( $reply ) {

		$base = sprintf( '/%s/%s/', $this->namespace, $this->rest_base );

		// Entity meta.
		$links = array(
			'self' => array(
				'href' => rest_url( $base . $reply->ID ),
			),
			'collection' => array(
				'href' => rest_url( $base ),
			),
			'author' => array(
				'href' => rest_url( '/wp/v2/users/' . $reply->post_author ),
			),
			// check what key should be used for the following
			// and if redundancy is required with the above returned values
			// $links['up'] = array(
			// 	'href' => rest_url( $base . $reply->item_id ),
			// );
		);

		return $links;
	}

}
