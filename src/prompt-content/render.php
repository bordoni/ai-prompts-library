<?php
/**
 * Server-side rendering for the Prompt Content block.
 *
 * @package AIPromptsLibrary
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block content.
 * @var WP_Block $block      Block instance.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Determine which post to get content from.
$display_mode = $attributes['displayMode'] ?? 'auto';
$post_id      = null;

if ( 'manual' === $display_mode && ! empty( $attributes['selectedPromptId'] ) ) {
	// Manual mode: use selected prompt.
	$post_id = $attributes['selectedPromptId'];
} else {
	// Auto mode: use context or current post.
	$post_id = $block->context['postId'] ?? get_the_ID();
}

// Validate post ID and post type.
if ( ! $post_id || 'ai-prompts' !== get_post_type( $post_id ) ) {
	return '';
}

// Get prompt content from meta.
$prompt_content = get_post_meta( $post_id, '_ai_prompt_content', true );

if ( empty( $prompt_content ) ) {
	return '';
}

// Get block wrapper attributes.
$wrapper_attributes = get_block_wrapper_attributes(
	array(
		'class' => 'ai-prompt-content',
	)
);

// Determine if copy button should be shown.
$show_copy_button = $attributes['showCopyButton'] ?? true;
$copy_button_html = '';

if ( $show_copy_button ) {
	$copy_button_html = sprintf(
		'<button class="ai-prompt-copy-button" data-prompt-id="%d" aria-label="%s">%s</button>',
		esc_attr( $post_id ),
		esc_attr__( 'Copy prompt to clipboard', 'ai-prompts-library' ),
		esc_html__( 'Copy', 'ai-prompts-library' )
	);
}

// Output the block.
printf(
	'<div %s>%s<pre><code>%s</code></pre></div>',
	$wrapper_attributes,
	$copy_button_html,
	esc_html( $prompt_content )
);
