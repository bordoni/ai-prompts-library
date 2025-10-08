<?php
/**
 * Block Bindings Integration
 *
 * @package AIPromptsLibrary
 */

namespace AIPromptsLibrary;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Block Bindings class for registering custom bindings source.
 */
class BlockBindings {
	/**
	 * Register block bindings source.
	 */
	public static function register() {
		// Check if function exists (WordPress 6.7+).
		if ( ! function_exists( 'register_block_bindings_source' ) ) {
			return;
		}

		register_block_bindings_source(
			'ai-prompts-library/prompt-meta',
			array(
				'label'              => __( 'AI Prompt Metadata', 'ai-prompts-library' ),
				'get_value_callback' => array( __CLASS__, 'get_value_callback' ),
				'uses_context'       => array( 'postId' ),
			)
		);
	}

	/**
	 * Get value callback for block bindings.
	 *
	 * @param array    $source_args    Source arguments.
	 * @param WP_Block $block_instance Block instance.
	 * @return string The meta value.
	 */
	public static function get_value_callback( $source_args, $block_instance ) {
		$post_id = $block_instance->context['postId'] ?? get_the_ID();

		if ( ! $post_id ) {
			return '';
		}

		// Get the meta key from source args.
		if ( ! isset( $source_args['key'] ) ) {
			return '';
		}

		$meta_key = $source_args['key'];

		// Get the meta value.
		$meta_value = get_post_meta( $post_id, $meta_key, true );

		return $meta_value;
	}
}
