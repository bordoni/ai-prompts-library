<?php
/**
 * Meta Fields Registration
 *
 * @package AIPromptsLibrary
 */

namespace AIPromptsLibrary;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Meta Fields class for registering post meta.
 */
class MetaFields {
	/**
	 * Register post meta fields.
	 */
	public static function register() {
		// Register the main prompt content meta field.
		register_post_meta(
			'ai-prompts',
			'_ai_prompt_content',
			array(
				'show_in_rest'  => true,
				'single'        => true,
				'type'          => 'string',
				'description'   => __( 'The prompt content', 'ai-prompts-library' ),
				'auth_callback' => function () {
					return current_user_can( 'edit_posts' );
				},
			)
		);

		// Register character count meta field.
		register_post_meta(
			'ai-prompts',
			'_ai_prompt_character_count',
			array(
				'show_in_rest'  => true,
				'single'        => true,
				'type'          => 'integer',
				'description'   => __( 'Character count of the prompt', 'ai-prompts-library' ),
				'auth_callback' => function () {
					return current_user_can( 'edit_posts' );
				},
			)
		);

		// Register word count meta field.
		register_post_meta(
			'ai-prompts',
			'_ai_prompt_word_count',
			array(
				'show_in_rest'  => true,
				'single'        => true,
				'type'          => 'integer',
				'description'   => __( 'Word count of the prompt', 'ai-prompts-library' ),
				'auth_callback' => function () {
					return current_user_can( 'edit_posts' );
				},
			)
		);

		// Register target model meta field (optional).
		register_post_meta(
			'ai-prompts',
			'_ai_prompt_model',
			array(
				'show_in_rest'  => true,
				'single'        => true,
				'type'          => 'string',
				'description'   => __( 'Target AI model for the prompt', 'ai-prompts-library' ),
				'auth_callback' => function () {
					return current_user_can( 'edit_posts' );
				},
			)
		);

		// Add hooks for meta updates.
		self::add_meta_update_hooks();
	}

	/**
	 * Add meta update hooks.
	 */
	private static function add_meta_update_hooks() {
		// Update character and word count when prompt content is saved via REST API.
		add_filter( 'rest_pre_insert_ai-prompts', array( __CLASS__, 'update_counts_rest' ), 10, 2 );

		// Update character and word count when prompt content is saved.
		add_action( 'updated_post_meta', array( __CLASS__, 'update_counts' ), 10, 4 );
		add_action( 'added_post_meta', array( __CLASS__, 'update_counts' ), 10, 4 );

		// Also hook into save_post to ensure counts are updated on post save.
		add_action( 'save_post_ai-prompts', array( __CLASS__, 'update_counts_on_save' ), 10, 1 );
	}

	/**
	 * Update character and word counts when prompt content changes.
	 *
	 * @param int    $meta_id    ID of updated metadata entry.
	 * @param int    $post_id    Post ID.
	 * @param string $meta_key   Meta key.
	 * @param mixed  $meta_value Meta value.
	 */
	public static function update_counts( $meta_id, $post_id, $meta_key, $meta_value ) {
		// Only process for our prompt content meta key.
		if ( '_ai_prompt_content' !== $meta_key ) {
			return;
		}

		// Only process for ai-prompts post type.
		if ( 'ai-prompts' !== get_post_type( $post_id ) ) {
			return;
		}

		// Calculate and update character count.
		$character_count = mb_strlen( $meta_value );
		update_post_meta( $post_id, '_ai_prompt_character_count', $character_count );

		// Calculate and update word count.
		$word_count = str_word_count( strip_tags( $meta_value ) );
		update_post_meta( $post_id, '_ai_prompt_word_count', $word_count );
	}

	/**
	 * Update counts when post is saved.
	 *
	 * @param int $post_id Post ID.
	 */
	public static function update_counts_on_save( $post_id ) {
		// Avoid running during autosave.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Check for revisions.
		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}

		// Get the prompt content.
		$prompt_content = get_post_meta( $post_id, '_ai_prompt_content', true );

		// Always update counts, even if empty (to ensure they're 0).
		$character_count = $prompt_content ? mb_strlen( $prompt_content ) : 0;
		$word_count = $prompt_content ? str_word_count( strip_tags( $prompt_content ) ) : 0;

		update_post_meta( $post_id, '_ai_prompt_character_count', $character_count );
		update_post_meta( $post_id, '_ai_prompt_word_count', $word_count );
	}

	/**
	 * Update counts when post is saved via REST API.
	 *
	 * @param \stdClass        $prepared_post Prepared post object.
	 * @param \WP_REST_Request $request       Request object.
	 * @return \stdClass Modified post object.
	 */
	public static function update_counts_rest( $prepared_post, $request ) {
		$params = $request->get_params();

		// Check if meta is being updated and contains prompt content.
		if ( isset( $params['meta']['_ai_prompt_content'] ) ) {
			$prompt_content = $params['meta']['_ai_prompt_content'];

			// Calculate counts.
			$character_count = $prompt_content ? mb_strlen( $prompt_content ) : 0;
			$word_count = $prompt_content ? str_word_count( strip_tags( $prompt_content ) ) : 0;

			// Add counts to the meta being saved.
			if ( ! isset( $params['meta'] ) ) {
				$params['meta'] = array();
			}

			$params['meta']['_ai_prompt_character_count'] = $character_count;
			$params['meta']['_ai_prompt_word_count'] = $word_count;

			// Update the request parameters.
			$request->set_param( 'meta', $params['meta'] );
		}

		return $prepared_post;
	}
}
