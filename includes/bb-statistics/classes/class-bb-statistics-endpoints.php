<?php
defined( 'ABSPATH' ) || exit;

/**
 * Topics endpoints.
 *
 * @since 0.1.0
 */
class BB_REST_Statistics_Controller extends WP_REST_Controller {

	/**
	 * Constructor.
	 *
	 * @since 0.1.0
	 */
	public function __construct() {
		$this->namespace = bb_rest_namespace() . '/' . bb_rest_version();
		$this->rest_base = 'statistics';
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
				// 'callback'            => array( $this, 'get_items' ),
				'callback'            => array( $this, 'get_item' ),
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

		$schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'statistics',
			'type'       => 'object',

			'properties' => array(
				'user_count' => array(
					'context'     => array( 'view', 'edit' ),
					'description' => __( 'The total count of forum users.', 'bbpress' ),
					'readonly'    => true,
					'type'        => 'integer',
				),

				'author' => array(
					'forum_count'     => array( 'view', 'edit' ),
					'description' => __( 'The total count of forums.', 'bbpress' ),
					'readonly'    => true,
					'type'        => 'integer',
				),

				'topic_count' => array(
					'context'     => array( 'view', 'edit' ),
					'description' => __( 'The total count of topics.', 'bbpress' ),
					'readonly'    => true,
					'type'        => 'integer',
				),

				'topic_count_hidden' => array(
					'context'     => array( 'view', 'edit' ),
					'description' => __( 'The total count of hidden topics.', 'bbpress' ),
					'readonly'    => true,
					'type'        => 'integer',
				),

				'reply_count' => array(
					'context'     => array( 'view', 'edit' ),
					'description' => __( 'The total count of replies.', 'bbpress' ),
					'readonly'    => true,
					'type'        => 'integer',
				),

				'reply_count_hidden' => array(
					'context'     => array( 'view', 'edit' ),
					'description' => __( 'The total count of hidden replies.', 'bbpress' ),
					'readonly'    => true,
					'type'        => 'integer',
				),

				'topic_tag_count' => array(
					'context'     => array( 'view', 'edit' ),
					'description' => __( 'The total count of topic tags.', 'bbpress' ),
					'readonly'    => true,
					'type'        => 'integer',
				),

				'empty_topic_tag_count' => array(
					'context'     => array( 'view', 'edit' ),
					'description' => __( 'The total count of empty topic tags.', 'bbpress' ),
					'readonly'    => true,
					'type'        => 'integer',
				),

				// check what this should contain
				'hidden_topic_title' => array(
					'context'     => array( 'view', 'edit' ),
					'description' => __( 'HTML title of the hidden topic.', 'bbpress' ),
					// 'readonly'    => true,
					'type'        => 'string',
				),

				// check what this should contain
				'hidden_reply_title' => array(
					'context'     => array( 'view', 'edit' ),
					'description' => __( 'HTML title of the hidden reply.', 'bbpress' ),
					// 'readonly'    => true,
					'type'        => 'string',
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
		unset( $params['page'] );
		unset( $params['per_page'] );
		unset( $params['search'] );
		
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

		// $params['after'] = array(
		// 	'description'       => __( 'Limit result set to items published after a given ISO8601 compliant date.', 'bbpress' ),
		// 	'type'              => 'string',
		// 	'format'            => 'date-time',
		// 	'validate_callback' => 'rest_validate_request_arg',
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
	 * Retrieve statistics.
	 *
	 * @since 0.1.0
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Request List of topic object data.
	 */
	public function get_item( $request ) {

		$args = $request->get_params();
		
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

		$retval = array();
		// needs to include $args where possible
		$stats = bbp_get_statistics();

		$retval[] = $this->prepare_response_for_collection( $stats, $args );
		// $retval[] = $this->prepare_response_for_collection(
		// 	$this->prepare_item_for_response( $stats, $args )
		// );
		
		// https://developer.wordpress.org/reference/functions/rest_ensure_response/
		return rest_ensure_response( $retval );
	}

	/**
	 * Check if a given request has access to get information about statistics.
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
		// TODO: handle private topics etc
		return apply_filters( 'bb_rest_topic_items_premission', true, $request );
	}

	/**
	 * Prepares statistic data for return as an object.
	 *
	 * @since 0.1.0
	 *
	 * @param stdClass $statistic Topic data.
	 * @param array    $request WP_REST_Request.
	 * @param boolean  $is_raw Optional, not used. Defaults to false.
	 * @return WP_REST_Response
	 */
	public function prepare_item_for_response( $stats, $request, $is_raw = false ) {

		$data = array(
			'user_count'			=> $stats['user_count'],
			'forum_count'			=> $stats['forum_count'],
			'topic_count'			=> $stats['topic_count'],
			'topic_count_hidden'	=> $stats['topic_count_hidden'],
			'reply_count'			=> $stats['reply_count'],
			'reply_count_hidden'	=> $stats['reply_count_hidden'],
			'topic_tag_count'		=> $stats['topic_tag_count'],
			'empty_topic_tag_count'	=> $stats['empty_topic_tag_count'],
			'hidden_topic_title'	=> $stats['hidden_topic_title'],
			'hidden_reply_title'	=> $stats['hidden_reply_title']
		);

		$context = ! empty( $request['context'] ) ? $request['context'] : 'view';
		// $data    = $this->add_additional_fields_to_object( $data, $request );
		$data    = $this->filter_response_by_context( $data, $context );

		$response = rest_ensure_response( $data );
		// $response->add_links( $this->prepare_links( $topic ) );
		
		/**
		 * Filter statistic values returned from the API.
		 *
		 * @param array           $response
		 * @param WP_REST_Request $request Request used to generate the response.
		 */
		// return apply_filters( 'bb_rest_prepare_statistics_item', $response, $request );
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
	// protected function prepare_links( $topic ) {

	// 	$base = sprintf( '/%s/%s/', $this->namespace, $this->rest_base );

	// 	// Entity meta.
	// 	$links = array(
	// 		'self' => array(
	// 			'href' => rest_url( $base . $topic->ID ),
	// 		),
	// 		'collection' => array(
	// 			'href' => rest_url( $base ),
	// 		),
	// 		'author' => array(
	// 			'href' => rest_url( '/wp/v2/users/' . $topic->post_author ),
	// 		),
	// 		// check what key should be used for the following
	// 		// check if redundancy is required with the above returned values
	// 		// $links['up'] = array(
	// 		// 	'href' => rest_url( $base . $topic->item_id ),
	// 		// );
	// 	);

	// 	return $links;
	// }

}
