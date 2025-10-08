<?php
/**
 * Taxonomy Registration
 *
 * @package AIPromptsLibrary
 */

namespace AIPromptsLibrary;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Taxonomy class for registering the ai-compatibility taxonomy.
 *
 * @since 1.0.0
 */
class Taxonomy {
	/**
	 * Register the custom taxonomy.
	 *
	 * @since 1.0.0
	 */
	public static function register() {
		$labels = array(
			'name'                       => _x( 'Compatibilities', 'taxonomy general name', 'ai-prompts-library' ),
			'singular_name'              => _x( 'Compatibility', 'taxonomy singular name', 'ai-prompts-library' ),
			'search_items'               => __( 'Search Compatibilities', 'ai-prompts-library' ),
			'popular_items'              => __( 'Popular Compatibilities', 'ai-prompts-library' ),
			'all_items'                  => __( 'All Compatibilities', 'ai-prompts-library' ),
			'parent_item'                => null,
			'parent_item_colon'          => null,
			'edit_item'                  => __( 'Edit Compatibility', 'ai-prompts-library' ),
			'update_item'                => __( 'Update Compatibility', 'ai-prompts-library' ),
			'add_new_item'               => __( 'Add New Compatibility', 'ai-prompts-library' ),
			'new_item_name'              => __( 'New Compatibility Name', 'ai-prompts-library' ),
			'separate_items_with_commas' => __( 'Separate compatibilities with commas', 'ai-prompts-library' ),
			'add_or_remove_items'        => __( 'Add or remove compatibilities', 'ai-prompts-library' ),
			'choose_from_most_used'      => __( 'Choose from the most used compatibilities', 'ai-prompts-library' ),
			'not_found'                  => __( 'No compatibilities found.', 'ai-prompts-library' ),
			'menu_name'                  => __( 'Compatibilities', 'ai-prompts-library' ),
		);

		$args = array(
			'hierarchical'          => false,
			'labels'                => $labels,
			'show_ui'               => true,
			'show_admin_column'     => true,
			'show_in_quick_edit'    => true,
			'update_count_callback' => '_update_post_term_count',
			'query_var'             => 'ai_compatibility',
			'rewrite'               => array( 'slug' => 'compatibility' ),
			'show_in_rest'          => true,
			'public'                => true,
		);

		register_taxonomy( 'ai-compatibility', array( 'ai-prompts' ), $args );
	}

	/**
	 * Create default terms on plugin activation.
	 *
	 * @since 1.0.0
	 */
	public static function create_default_terms() {
		// No default terms created - users can add their own
	}
}
