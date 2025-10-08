<?php
/**
 * Post Type Registration
 *
 * @package AIPromptsLibrary
 */

namespace AIPromptsLibrary;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Post Type class for registering the ai-prompts custom post type.
 *
 * @since 1.0.0
 */
class PostType {
	/**
	 * Register the custom post type.
	 *
	 * @since 1.0.0
	 */
	public static function register() {
		$labels = array(
			'name'                  => _x( 'AI Prompts', 'Post type general name', 'ai-prompts-library' ),
			'singular_name'         => _x( 'AI Prompt', 'Post type singular name', 'ai-prompts-library' ),
			'menu_name'             => _x( 'AI Prompts', 'Admin Menu text', 'ai-prompts-library' ),
			'name_admin_bar'        => _x( 'AI Prompt', 'Add New on Toolbar', 'ai-prompts-library' ),
			'add_new'               => __( 'Add New', 'ai-prompts-library' ),
			'add_new_item'          => __( 'Add New AI Prompt', 'ai-prompts-library' ),
			'new_item'              => __( 'New AI Prompt', 'ai-prompts-library' ),
			'edit_item'             => __( 'Edit AI Prompt', 'ai-prompts-library' ),
			'view_item'             => __( 'View AI Prompt', 'ai-prompts-library' ),
			'all_items'             => __( 'All AI Prompts', 'ai-prompts-library' ),
			'search_items'          => __( 'Search AI Prompts', 'ai-prompts-library' ),
			'parent_item_colon'     => __( 'Parent AI Prompts:', 'ai-prompts-library' ),
			'not_found'             => __( 'No AI prompts found.', 'ai-prompts-library' ),
			'not_found_in_trash'    => __( 'No AI prompts found in Trash.', 'ai-prompts-library' ),
			'featured_image'        => _x( 'AI Prompt Image', 'Overrides the "Featured Image" phrase', 'ai-prompts-library' ),
			'set_featured_image'    => _x( 'Set prompt image', 'Overrides the "Set featured image" phrase', 'ai-prompts-library' ),
			'remove_featured_image' => _x( 'Remove prompt image', 'Overrides the "Remove featured image" phrase', 'ai-prompts-library' ),
			'use_featured_image'    => _x( 'Use as prompt image', 'Overrides the "Use as featured image" phrase', 'ai-prompts-library' ),
			'archives'              => _x( 'AI Prompt archives', 'The post type archive label used in nav menus', 'ai-prompts-library' ),
			'insert_into_item'      => _x( 'Insert into prompt', 'Overrides the "Insert into post" phrase', 'ai-prompts-library' ),
			'uploaded_to_this_item' => _x( 'Uploaded to this prompt', 'Overrides the "Uploaded to this post" phrase', 'ai-prompts-library' ),
			'filter_items_list'     => _x( 'Filter prompts list', 'Screen reader text for the filter links', 'ai-prompts-library' ),
			'items_list_navigation' => _x( 'Prompts list navigation', 'Screen reader text for the pagination', 'ai-prompts-library' ),
			'items_list'            => _x( 'Prompts list', 'Screen reader text for the items list', 'ai-prompts-library' ),
		);

		$args = array(
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'prompts' ),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => 20,
			'menu_icon'          => 'dashicons-editor-code',
			'show_in_rest'       => true,
			'supports'           => array( 'title', 'editor', 'author', 'revisions', 'custom-fields' ),
			'template'           => array(
				array(
					'core/paragraph',
					array(
						'placeholder' => __( 'Add a description or usage notes...', 'ai-prompts-library' ),
					),
				),
				array(
					'core/paragraph',
					array(
						'content' => __( '<strong>Prompt:</strong>', 'ai-prompts-library' ),
						'lock'    => array(
							'move'   => true,
							'remove' => true,
						),
					),
				),
				array( 'ai-prompts-library/prompt-content', array() ),
			),
			'template_lock'      => false,
		);

		register_post_type( 'ai-prompts', $args );
	}
}
