<?php
/*
This is a temporary, brutal hack of:
https://github.com/starfishmod/WPAPI-SwaggerGenerator
to allow usage of ReDoc for auto-generated docs


Original License:

	The MIT License (MIT)

	Copyright (c) 2016 Andrew

	Permission is hereby granted, free of charge, to any person obtaining a copy
	of this software and associated documentation files (the "Software"), to deal
	in the Software without restriction, including without limitation the rights
	to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
	copies of the Software, and to permit persons to whom the Software is
	furnished to do so, subject to the following conditions:

	The above copyright notice and this permission notice shall be included in all
	copies or substantial portions of the Software.

	THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
	IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
	FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
	AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
	LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
	OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
	SOFTWARE.

*/



/**
 * Swagger base class.
 */
class BB_REST_Swagger_Controller extends WP_REST_Controller {
	
	/**
	 * Constructor.
	 *
	 * @since 0.1.0
	 */
	public function __construct() {
		$this->namespace = bb_rest_namespace() . '/' . bb_rest_version();
		$this->rest_base = 'swagger';
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
				'permission_callback' => array( $this, 'get_items_permissions_check' ),
				'args'                => $this->get_collection_params(),
			),
			'schema' => array( $this, 'get_public_item_schema' ),
		) );
	}
	
	public function get_collection_params() {
		$new_params = array();
		return $new_params;
	}
	
	public function get_items_permissions_check( $request ) {
		// return $this->get_items_permissions_check( $request );
		return true;
	}

	/**
	 * Retrieve custom swagger object.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Request|WP_Error Meta object data on success, WP_Error otherwise
	 */
	public function get_items( $request ) {

		global $wp_rewrite;
		 
		$basePath = '/';
		if ( $wp_rewrite->root != '/' ){
			$basePath = '/' . $wp_rewrite->root; //'/'.$matches[1].'/';
		}

		$host = preg_replace( '/(^https?:\/\/|\/$)/', '', site_url('/') );
		// $title = wp_title( '', 0 );
		$title = get_bloginfo('name');
		$title = 'BB REST API';
		if( empty( $title ) ) {
			$title = $host;
		}

		$swagger = array(
			'swagger' => '2.0',
			'info' => array(
				'version' => '1.0',
				'title' => $title
			),
			'host' => $host,
			// 'basePath' => "{$basePath}wp-json",
			// 'basePath' => "{$basePath}",
			'basePath' => "",
			'schemes' => array( ( is_ssl() | force_ssl_admin() ) ? 'https' : 'http' ),
			'consumes' => array('multipart/form-data'),
			'produces' => array('application/json'),
			'paths' => array(),
			'definitions' => array(
				'error' => array(
					'properties' => array(
						'code' => array(
							'type' => 'string'
						),
						'message' => array(
							'type' => 'string'
						),
						'data' => array(
							'type' => 'object',
							'properties' => array(
								'status' => array(
									'type' => 'integer'
								)
							)
						)
					)
				)
			),
			// 'securityDefinitions' => array(
			// 	"cookieAuth" => array(
			// 		"type" => "apiKey",
			// 		"name" => "X-WP-Nonce",
			// 		"in" => "header",
			// 		"description" => "Please see http://v2.wp-api.org/guide/authentication/"
			// 	)
			// )
		);
		
		$security = array(
			array('cookieAuth' => array())
		);
		
		if( function_exists( 'rest_oauth1_init' ) ) {
			$swagger['securityDefinitions']['oauth'] = array(
				'type' => 'oauth2',
				'x-oauth1' => true,
				'flow' => 'accessCode',
				'authorizationUrl' => site_url() . $basePath . 'oauth1/authorize',
				'tokenUrl' => site_url() . $basePath . 'oauth1/request',
				'x-accessUrl' => site_url() . $basePath . 'oauth1/access',
				'scopes' => array(
					'basic' => 'OAuth authentication uses the OAuth 1.0a specification (published as RFC5849)'
				)
			);
			$security[] = array( 'oauth' => array( 'basic' ) );
		}
		if( class_exists( 'Application_Passwords' ) || function_exists( 'json_basic_auth_handler' ) ) {
			$swagger['securityDefinitions']['basicAuth'] = array(
				'type' => 'basic'
			);
			$security[] = array( 'basicAuth' => array( '' ) );
		}


		$restServer = rest_get_server();


		// print $wp_rewrite->root . "\xA";
		// print $basePath . "\xA";
		// print $title . "\xA";
		// print $host . "\xA";
		// print site_url('/') . "\xA";
		// print wp_title( '', 0 ) . "\xA";
		// // print bloginfo('description');
		// print bloginfo('name') . "\xA";
		// // var_dump( $restServer->get_routes() );
		// foreach( $restServer->get_routes() as $endpointName => $endpoint ) {
		// 	echo $endpointName . ' : ' . $endpoint . "\xA";
		// }

		$tags = array();
		
		foreach( $restServer->get_routes() as $endpointName => $endpoint ) {
			
			// don't include self
			if ( ( '/' . $this->namespace . '/swagger' == $endpointName ) ||
			// don't include anything else than bbpress
			( '/' . $this->namespace . '/' != substr( $endpointName, 0, 12 ) ) )
				continue;
			// print $endpointName . "\xA";
			
			$routeopt = $restServer->get_route_options( $endpointName );

			if ( empty($routeopt['schema'][1] ) ) {
				// if there is no schema then it's a safe bet that this API call 
				// will not work - move to the next one.
				continue;
			}

			$schema = call_user_func(
				array(
					$routeopt['schema'][0],
					$routeopt['schema'][1]
				)
			);
			$swagger['definitions'][$schema['title']] = $this->schemaIntoDefinition( $schema );
			$tags[] = array(
				'name' => $schema['title'],
				'description' => 'endpoint to retrieve ' . $schema['title'] . ' objects',
				// 'externalDocs' => array(
				// 	'description' => 'Find out more',
				// 	'url' => 'http://swagger.io'
				// )
			);

			// var_dump( $routeopt );
			// echo $routeopt['schema'][1];
			// continue;


			$outputSchema = array( '$ref' => '#/definitions/' . $schema['title'] );
			
			$defaultidParams = array();
			//Replace endpoints var and add to the parameters required
			$endpointName = preg_replace_callback(
				'#\(\?P<(\w+?)>.*?\)#',
				function ( $matches ) use ( &$defaultidParams ) {
					$defaultidParams[]=array(
							'name' => $matches[1],
							'type' => 'string',
							'in' => 'path',
							'required' => true
						);
					return '{'.$matches[1].'}';
				},
				$endpointName
			);
			$endpointName = str_replace(
				site_url(), '', rest_url( $endpointName )
			);
			
			if ( empty( $swagger['paths'][$endpointName] ) ) {
				$swagger['paths'][$endpointName] = array();
			}
			
			
			foreach( $endpoint as $endpointPart ){
			
				foreach($endpointPart['methods'] as $methodName=>$method){
					if( in_array( $methodName, array( 'PUT','PATCH' ) ) )
						continue; //duplicated by post
					
					$parameters = $defaultidParams;
					
					//Clean up parameters
					foreach ($endpointPart['args'] as $pname=>$pdetails){
						
						$parameter=array(
							'name' => $pname,
							'type' => 'string',
							'in' => $methodName=='POST'?'formData':'query'
						);
						if ( !empty ( $pdetails['description']))$parameter['description'] = $pdetails['description'];
						if ( !empty ( $pdetails['format']))$parameter['format'] = $pdetails['format'];
						if ( !empty ( $pdetails['default']))$parameter['default'] = $pdetails['default'];
						if ( !empty ( $pdetails['enum']))$parameter['enum'] = $pdetails['enum'];
						if ( !empty ( $pdetails['required']))$parameter['required'] = $pdetails['required'];
						if ( !empty ( $pdetails['minimum'])){
							$parameter['minimum'] = $pdetails['minimum'];
							$parameter['format'] = 'number';
						}
						if ( !empty ( $pdetails['maximum'])){
							$parameter['maximum'] = $pdetails['maximum'];
							$parameter['format'] = 'number';
						}
						if ( !empty ( $pdetails['type'])){
							if($pdetails['type']=='array'){
								$parameter['type'] = $pdetails['type'];
								$parameter['items']=array('type' => 'string');
							}elseif($pdetails['type']=='object'){
								$parameter['type'] = 'string';
							
							}elseif($pdetails['type']=='date-time'){
								$parameter['type'] = 'string';
								$parameter['format'] = 'date-time';
							}else{
								$parameter['type'] = $pdetails['type'];
							}
						}
						
						$parameters[] = $parameter;
					}
					
					// If the endpoint is not grabbing a specific object then 
					// assume it's returning a list
					$outputSchemaForMethod = $outputSchema;
					if( $methodName == 'GET' && !preg_match( '/}$/',$endpointName ) ) {
						$outputSchemaForMethod = array(
							'type' => 'array',
							'items' => $outputSchemaForMethod
						);
					}
					
					$responses = array(
						200 => array(
							'description'=> "successful operation",
							'schema' => $outputSchemaForMethod
						),
						'default' => array(
							'description'=> "error",
							'schema' => array( '$ref' => '#/definitions/error' )
						)
					);
					
					if ( in_array( $methodName, array( 'POST', 'PATCH', 'PUT' ) )
						&& !preg_match( '/}$/', $endpointName ) ) {
						// This are actually 201's in the default API
						// but joy of joys this is unreliable
						$responses[201] = array(
							'description'=> "successful operation",
							'schema' => $outputSchemaForMethod
						);
					}
					
					// temporary addition for documentation
					$summary = ucfirst( strtolower( $methodName ) );
					$description = 'Returns';
					$terms = explode( '/', $endpointName );
					if ('{id}' == end( $terms ) ) {
						$itemname = $terms[sizeof( $terms ) - 2];
						$summary .=  ' ' . $schema['title'] . ' by ID';
						$description .= ' a single ' . $schema['title'];
					} else {
						$summary .= ' list of ' . end( $terms );
						$description .= ' a list of ' . $schema['title'] . ' objects';
					}
					// echo $summary . $description;

					$swagger['paths'][$endpointName][strtolower($methodName)] = array(
						'parameters' => $parameters,
						'security' => $security,
						'responses' => $responses,
						// @todo setup a better logic for addition,
						// for documentation purposes
						'tags' => array( $schema['title'] ),
						'summary' => $summary,
						'description' => $description
					);
				}
			}
		}

		$swagger['tags'] = $tags;

		$response = rest_ensure_response( $swagger );
		
		// return;
		return apply_filters( 'rest_prepare_meta_value', $response, $request );
	}
	
	/**
	 * Turns the schema set up by the endpoint into a swagger definition.
	 *
	 * @param array $schema
	 * @return array Definition
	 */
	private function schemaIntoDefinition( $schema ) {

		if ( ! empty( $schema['$schema'] ) )
			unset( $schema['$schema'] );
		if ( ! empty( $schema['title'] ) )
			unset( $schema['title'] );

		foreach( $schema['properties'] as $name => &$prop ) {
						
			if ( ! empty( $prop['properties'] ) ) {
				$prop = $this->schemaIntoDefinition( $prop );
			}

			if ( $prop['type'] == 'array' ) {
				// @todo check what is happening here
				$prop['items'] = array( 'type' => 'string' );
			} else if ( $prop['type'] == 'date-time' ) {
				$prop['type'] = 'string';
				$prop['format'] = 'date-time';
			}
			// else if ( !empty ( $prop['context']) && $prop['format']!='date-time'){
			// 	//$prop['enum'] = $prop['context'];
			// }
			if ( isset( $prop['required'] ) )
				unset( $prop['required'] );
			
			if( isset( $prop['readonly'] ) )
				unset( $prop['readonly'] );

			if( isset( $prop['context'] ) )
				unset( $prop['context'] );
		}

		// var_dump( $schema );
		return $schema;
	}
	
	/**
	 * Get the meta schema, conforming to JSON Schema
	 *
	 * @return array
	 */
	public function get_item_schema() {
		$schema = json_decode( file_get_contents( dirname( __FILE__ ) . '/schema.json' ), 1 );
		return $schema;
	}
	
}
