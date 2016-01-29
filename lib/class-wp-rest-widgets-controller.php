<?php

/**
 * Manage Menu Items for a WordPress site
 */
class WP_REST_Widgets_Controller extends WP_REST_Controller {

	/**
	 * Widget objects.
	 *
	 * @see WP_Widget_Factory::$widgets
	 * @var WP_Widget[]
	 */
	public $widgets;

	/**
	 * WP_REST_Widgets_Controller constructor.
	 *
	 * @param WP_Widget[] $widgets Widget objects.
	 */
	public function __construct( $widgets ) {
		$this->namespace = 'wp/v2';
		$this->rest_base = 'widgets';
		$this->widgets = $widgets;

		// @todo Now given $this->widgets, inject schema information for Core widgets in lieu of them being in core now. See #35574.

		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	public function register_routes() {

		register_rest_route( $this->namespace, '/' . $this->rest_base, array(
			array(
				'methods' => WP_REST_Server::READABLE,
				'callback' => array( $this, 'get_items' ),
				'permission_callback' => array( $this, 'get_items_permissions_check' ),
				'args' => $this->get_collection_params(),
			),
			array(
				'methods' => WP_REST_Server::CREATABLE,
				'callback' => array( $this, 'create_item' ),
				'permission_callback' => array( $this, 'create_item_permissions_check' ),
				'args' => $this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE ),
			),

			'schema' => array( $this, 'get_public_item_schema' ),
		) );
		register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)', array(
			array(
				'methods'         => WP_REST_Server::READABLE,
				'callback'        => array( $this, 'get_item' ),
				'permission_callback' => array( $this, 'get_item_permissions_check' ),
				'args'            => array(
					'context'          => $this->get_context_param( array( 'default' => 'view' ) ),
				),
			),
			array(
				'methods'         => WP_REST_Server::EDITABLE,
				'callback'        => array( $this, 'update_item' ),
				'permission_callback' => array( $this, 'update_item_permissions_check' ),
				'args'            => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
			),
			array(
				'methods'  => WP_REST_Server::DELETABLE,
				'callback' => array( $this, 'delete_item' ),
				'permission_callback' => array( $this, 'delete_item_permissions_check' ),
			),

			'schema' => array( $this, 'get_public_item_schema' ),
		) );
		register_rest_route( $this->namespace, '/' . $this->rest_base .'/types', array(
			array(
				'methods' => WP_REST_Server::READABLE,
				'callback' => array( $this, 'get_types' ),
				'permission_callback' => array( $this, 'get_types_permissions_check' ),
			),
			'schema' => array( $this, 'get_public_item_schema' ),
		) );
	}

	public function get_items_permissions_check( $request ) {

	}

	public function get_items( $request ) {

	}

	public function get_item_permissions_check( $request ) {

	}

	public function get_item( $request ) {

	}

	public function delete_item_permission_check( $request ) {

	}

	public function delete_item( $request ) {

	}

	public function prepare_item_for_response( $item, $request ) {

	}

	public function get_item_schema() {

	}

	public function get_collection_params() {
		return array();
	}

	/**
	 * Get the available widget type schemas
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_types( $request ) {
		if ( empty( $this->widgets ) ) {
			return rest_ensure_response( array() );
		}

		$schemas = array();
		foreach ( $this->widgets as $key => $type ) {
			if ( empty( $type->id_base ) ) {
				continue;
			}
			$schemas[] = $this->get_type_schema( $type->id_base );
		}

		$response = rest_ensure_response( $schemas );

		return $response;
	}

	/**
	 * Check if a given request has access to read /widgets/types
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return WP_Error|boolean
	 */
	public function get_types_permissions_check( $request ) {
		return current_user_can( 'edit_theme_options' );
	}

	/**
	 * Return a schema matching this widget type
	 *
	 * TODO this is a placeholder. A final implementation needs to look up a
	 * schema which would specify the visibility and type of widget control options.
	 *
	 * @param string $id_base Registered widget type
	 * @return array $schema
	 */
	public function get_type_schema( $id_base ) {

		$widget = null;
		foreach ( $this->widgets as $this_widget ) {
			if ( $id_base === $this_widget->id_base ) {
				$widget = $this_widget;
				break;
			}
		}
		if ( empty( $widget ) ) {
			return false;
		}

		$properties = array(
			'id'              => array(
				'description' => __( 'Unique identifier for the object.' ),
				'type'        => 'string',
				'context'     => array( 'view', 'edit', 'embed' ),
				'readonly'    => true,
			),
			'type'            => array(
				'description' => __( 'Type of Widget for the object.' ),
				'type'        => 'string',
				'context'     => array( 'view', 'edit', 'embed' ),
				'readonly'    => true,
			),
		);

		if ( in_array( $id_base, array( 'pages', 'calendar', 'archives', 'meta', 'search', 'text', 'categories', 'recent-posts', 'recent-comments', 'rss', 'tag_cloud', 'nav_menu', 'next_recent_posts' ), true ) ) {
			$properties['title'] = array(
				'description' => __( 'The title for the object.' ),
				'type'        => 'string',
				'context'     => array( 'view', 'edit', 'embed' ),
			);
		}

		$schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => $widget->id_base,
			'type'       => 'object',

			/*
			 * Base properties for every Widget.
			 */
			'properties' => $properties,
		);

		return $schema;
	}
}