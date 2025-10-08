<?php
/**
 * REST API Extensions
 *
 * @package AIPromptsLibrary
 */

namespace AIPromptsLibrary;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * REST API class for custom endpoints.
 *
 * @since 1.0.0
 */
class RestApi {
	/**
	 * Namespace for REST routes.
	 *
	 * @since 1.0.0
	 */
	const NAMESPACE = 'ai-prompts-library/v1';

	/**
	 * Register REST API routes.
	 *
	 * @since 1.0.0
	 */
	public static function register_routes() {
		// Register custom routes.
		register_rest_route(
			self::NAMESPACE,
			'/prompts',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( __CLASS__, 'get_prompts' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'compatibility' => array(
						'description' => __( 'Filter by compatibility term slug', 'ai-prompts-library' ),
						'type'        => 'string',
					),
					'search'        => array(
						'description' => __( 'Search term', 'ai-prompts-library' ),
						'type'        => 'string',
					),
					'per_page'      => array(
						'description' => __( 'Number of items per page', 'ai-prompts-library' ),
						'type'        => 'integer',
						'default'     => 10,
					),
					'page'          => array(
						'description' => __( 'Current page number', 'ai-prompts-library' ),
						'type'        => 'integer',
						'default'     => 1,
					),
				),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/prompts/(?P<id>\d+)',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( __CLASS__, 'get_prompt' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'id' => array(
						'description' => __( 'Prompt ID', 'ai-prompts-library' ),
						'type'        => 'integer',
					),
				),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/prompts/(?P<id>\d+)/duplicate',
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array( __CLASS__, 'duplicate_prompt' ),
				'permission_callback' => array( __CLASS__, 'check_edit_permission' ),
				'args'                => array(
					'id' => array(
						'description' => __( 'Prompt ID to duplicate', 'ai-prompts-library' ),
						'type'        => 'integer',
					),
				),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/compatibilities',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( __CLASS__, 'get_compatibilities' ),
				'permission_callback' => '__return_true',
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/stats',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( __CLASS__, 'get_stats' ),
				'permission_callback' => '__return_true',
			)
		);

		// Extend default post endpoint to include meta.
		add_filter( 'rest_prepare_ai-prompts', array( __CLASS__, 'add_meta_to_response' ), 10, 3 );
	}

	/**
	 * Get prompts list.
	 *
	 * @since 1.0.0
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response Response object.
	 */
	public static function get_prompts( $request ) {
		$args = array(
			'post_type'      => 'ai-prompts',
			'posts_per_page' => $request->get_param( 'per_page' ),
			'paged'          => $request->get_param( 'page' ),
			'post_status'    => 'publish',
		);

		// Filter by compatibility.
		if ( $request->get_param( 'compatibility' ) ) {
			$args['tax_query'] = array(
				array(
					'taxonomy' => 'ai-compatibility',
					'field'    => 'slug',
					'terms'    => $request->get_param( 'compatibility' ),
				),
			);
		}

		// Search.
		if ( $request->get_param( 'search' ) ) {
			$args['s'] = $request->get_param( 'search' );
		}

		$query = new \WP_Query( $args );

		$prompts = array();
		foreach ( $query->posts as $post ) {
			$prompts[] = self::prepare_prompt_data( $post );
		}

		$response = new \WP_REST_Response( $prompts );
		$response->header( 'X-WP-Total', $query->found_posts );
		$response->header( 'X-WP-TotalPages', $query->max_num_pages );

		return $response;
	}

	/**
	 * Get single prompt.
	 *
	 * @since 1.0.0
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error Response object or error.
	 */
	public static function get_prompt( $request ) {
		$post_id = $request->get_param( 'id' );
		$post    = get_post( $post_id );

		if ( ! $post || 'ai-prompts' !== $post->post_type ) {
			return new \WP_Error( 'not_found', __( 'Prompt not found', 'ai-prompts-library' ), array( 'status' => 404 ) );
		}

		return new \WP_REST_Response( self::prepare_prompt_data( $post ) );
	}

	/**
	 * Duplicate a prompt.
	 *
	 * @since 1.0.0
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error Response object or error.
	 */
	public static function duplicate_prompt( $request ) {
		$post_id = $request->get_param( 'id' );
		$post    = get_post( $post_id );

		if ( ! $post || 'ai-prompts' !== $post->post_type ) {
			return new \WP_Error( 'not_found', __( 'Prompt not found', 'ai-prompts-library' ), array( 'status' => 404 ) );
		}

		// Create duplicate.
		$new_post_id = wp_insert_post(
			array(
				'post_title'   => $post->post_title . ' (Copy)',
				'post_content' => $post->post_content,
				'post_status'  => 'draft',
				'post_type'    => 'ai-prompts',
				'post_author'  => get_current_user_id(),
			)
		);

		if ( is_wp_error( $new_post_id ) ) {
			return $new_post_id;
		}

		// Copy meta.
		$prompt_content = get_post_meta( $post_id, '_ai_prompt_content', true );
		if ( $prompt_content ) {
			update_post_meta( $new_post_id, '_ai_prompt_content', $prompt_content );
		}

		// Copy taxonomy terms.
		$terms = wp_get_post_terms( $post_id, 'ai-compatibility', array( 'fields' => 'ids' ) );
		if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
			wp_set_post_terms( $new_post_id, $terms, 'ai-compatibility' );
		}

		$new_post = get_post( $new_post_id );

		return new \WP_REST_Response(
			self::prepare_prompt_data( $new_post ),
			201
		);
	}

	/**
	 * Get compatibility terms.
	 *
	 * @since 1.0.0
	 *
	 * @return \WP_REST_Response Response object.
	 */
	public static function get_compatibilities() {
		$terms = get_terms(
			array(
				'taxonomy'   => 'ai-compatibility',
				'hide_empty' => false,
			)
		);

		$compatibilities = array();
		foreach ( $terms as $term ) {
			$compatibilities[] = array(
				'id'    => $term->term_id,
				'name'  => $term->name,
				'slug'  => $term->slug,
				'count' => $term->count,
			);
		}

		return new \WP_REST_Response( $compatibilities );
	}

	/**
	 * Get statistics.
	 *
	 * @since 1.0.0
	 *
	 * @return \WP_REST_Response Response object.
	 */
	public static function get_stats() {
		$total_prompts = wp_count_posts( 'ai-prompts' );

		$stats = array(
			'total_prompts'     => $total_prompts->publish,
			'draft_prompts'     => $total_prompts->draft,
			'by_compatibility'  => array(),
		);

		// Count by compatibility.
		$terms = get_terms(
			array(
				'taxonomy'   => 'ai-compatibility',
				'hide_empty' => false,
			)
		);

		foreach ( $terms as $term ) {
			$stats['by_compatibility'][ $term->slug ] = $term->count;
		}

		return new \WP_REST_Response( $stats );
	}

	/**
	 * Prepare prompt data for response.
	 *
	 * @since 1.0.0
	 *
	 * @param \WP_Post $post Post object.
	 * @return array Prompt data.
	 */
	private static function prepare_prompt_data( $post ) {
		$prompt_content   = get_post_meta( $post->ID, '_ai_prompt_content', true );
		$character_count  = get_post_meta( $post->ID, '_ai_prompt_character_count', true );
		$word_count       = get_post_meta( $post->ID, '_ai_prompt_word_count', true );
		$compatibility    = wp_get_post_terms( $post->ID, 'ai-compatibility', array( 'fields' => 'names' ) );

		return array(
			'id'              => $post->ID,
			'title'           => $post->post_title,
			'content'         => $prompt_content,
			'excerpt'         => wp_trim_words( $prompt_content, 20 ),
			'character_count' => (int) $character_count,
			'word_count'      => (int) $word_count,
			'compatibility'   => is_array( $compatibility ) ? $compatibility : array(),
			'date'            => $post->post_date,
			'modified'        => $post->post_modified,
			'author'          => $post->post_author,
			'status'          => $post->post_status,
			'link'            => get_permalink( $post->ID ),
		);
	}

	/**
	 * Add meta fields to REST response.
	 *
	 * @since 1.0.0
	 *
	 * @param \WP_REST_Response $response Response object.
	 * @param \WP_Post          $post     Post object.
	 * @param \WP_REST_Request  $request  Request object.
	 * @return \WP_REST_Response Modified response.
	 */
	public static function add_meta_to_response( $response, $post, $request ) {
		$data = $response->get_data();

		$data['prompt_content']   = get_post_meta( $post->ID, '_ai_prompt_content', true );
		$data['character_count']  = (int) get_post_meta( $post->ID, '_ai_prompt_character_count', true );
		$data['word_count']       = (int) get_post_meta( $post->ID, '_ai_prompt_word_count', true );
		$data['compatibility']    = wp_get_post_terms( $post->ID, 'ai-compatibility', array( 'fields' => 'names' ) );

		$response->set_data( $data );

		return $response;
	}

	/**
	 * Check if user can edit posts.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public static function check_edit_permission() {
		return current_user_can( 'edit_posts' );
	}
}
