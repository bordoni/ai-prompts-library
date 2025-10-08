<?php
/**
 * Helper Functions
 *
 * @package AIPromptsLibrary
 */

namespace AIPromptsLibrary;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get prompt content by post ID.
 *
 * @param int $post_id The post ID.
 * @return string The prompt content.
 */
function get_prompt_content( $post_id ) {
	return get_post_meta( $post_id, '_ai_prompt_content', true );
}

/**
 * Get all AI prompts.
 *
 * @param array $args Query arguments.
 * @return \WP_Post[] Array of post objects.
 */
function get_ai_prompts( $args = array() ) {
	$defaults = array(
		'post_type'      => 'ai-prompts',
		'posts_per_page' => -1,
		'orderby'        => 'title',
		'order'          => 'ASC',
	);

	$args = wp_parse_args( $args, $defaults );

	$query = new \WP_Query( $args );

	return $query->posts;
}

/**
 * Get compatibility terms for a prompt.
 *
 * @param int $post_id The post ID.
 * @return array Array of term objects.
 */
function get_prompt_compatibility( $post_id ) {
	return wp_get_post_terms( $post_id, 'ai-compatibility' );
}

/**
 * Calculate character count for prompt content.
 *
 * @param string $content The prompt content.
 * @return int Character count.
 */
function calculate_character_count( $content ) {
	return mb_strlen( $content );
}

/**
 * Calculate word count for prompt content.
 *
 * @param string $content The prompt content.
 * @return int Word count.
 */
function calculate_word_count( $content ) {
	return str_word_count( strip_tags( $content ) );
}

/**
 * Sanitize prompt content.
 *
 * @param string $content The prompt content.
 * @return string Sanitized content.
 */
function sanitize_prompt_content( $content ) {
	return sanitize_textarea_field( $content );
}

/**
 * Check if current user can edit prompts.
 *
 * @return bool
 */
function current_user_can_edit_prompts() {
	return current_user_can( 'edit_posts' );
}

/**
 * Check if current user can publish prompts.
 *
 * @return bool
 */
function current_user_can_publish_prompts() {
	return current_user_can( 'publish_posts' );
}
