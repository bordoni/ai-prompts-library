<?php
/**
 * Admin Columns
 *
 * @package AIPromptsLibrary
 */

namespace AIPromptsLibrary;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin Columns class for custom admin list table columns.
 */
class Admin_Columns {
	/**
	 * Constructor.
	 */
	public function __construct() {
		add_filter( 'manage_ai-prompts_posts_columns', array( $this, 'add_custom_columns' ) );
		add_action( 'manage_ai-prompts_posts_custom_column', array( $this, 'render_custom_columns' ), 10, 2 );
		add_filter( 'manage_edit-ai-prompts_sortable_columns', array( $this, 'make_columns_sortable' ) );
	}

	/**
	 * Add custom columns to the admin list table.
	 *
	 * @param array $columns Existing columns.
	 * @return array Modified columns.
	 */
	public function add_custom_columns( $columns ) {
		// Insert our columns after the title.
		$new_columns = array();

		foreach ( $columns as $key => $value ) {
			$new_columns[ $key ] = $value;

			if ( 'title' === $key ) {
				$new_columns['prompt_excerpt']    = __( 'Prompt Excerpt', 'ai-prompts-library' );
				$new_columns['character_count']    = __( 'Characters', 'ai-prompts-library' );
				$new_columns['word_count']         = __( 'Words', 'ai-prompts-library' );
			}
		}

		return $new_columns;
	}

	/**
	 * Render custom columns.
	 *
	 * @param string $column  Column name.
	 * @param int    $post_id Post ID.
	 */
	public function render_custom_columns( $column, $post_id ) {
		switch ( $column ) {
			case 'prompt_excerpt':
				$content = get_post_meta( $post_id, '_ai_prompt_content', true );
				if ( $content ) {
					$excerpt = mb_substr( $content, 0, 100 );
					if ( mb_strlen( $content ) > 100 ) {
						$excerpt .= '...';
					}
					echo '<code>' . esc_html( $excerpt ) . '</code>';
				} else {
					echo '<em>' . esc_html__( 'No content', 'ai-prompts-library' ) . '</em>';
				}
				break;

			case 'character_count':
				$count = get_post_meta( $post_id, '_ai_prompt_character_count', true );
				echo $count ? esc_html( number_format_i18n( $count ) ) : '0';
				break;

			case 'word_count':
				$count = get_post_meta( $post_id, '_ai_prompt_word_count', true );
				echo $count ? esc_html( number_format_i18n( $count ) ) : '0';
				break;
		}
	}

	/**
	 * Make custom columns sortable.
	 *
	 * @param array $columns Sortable columns.
	 * @return array Modified sortable columns.
	 */
	public function make_columns_sortable( $columns ) {
		$columns['character_count'] = 'character_count';
		$columns['word_count']      = 'word_count';

		return $columns;
	}
}
