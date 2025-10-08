<?php
/**
 * Search Enhancement
 *
 * @package AIPromptsLibrary
 */

namespace AIPromptsLibrary;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Search Enhancement class for extending search to meta fields.
 */
class Search_Enhancement {
	/**
	 * Constructor.
	 */
	public function __construct() {
		add_filter( 'posts_search', array( $this, 'extend_search_to_meta' ), 10, 2 );
		add_filter( 'posts_join', array( $this, 'join_postmeta' ), 10, 2 );
		add_filter( 'posts_distinct', array( $this, 'search_distinct' ), 10, 2 );
	}

	/**
	 * Extend search to include meta fields.
	 *
	 * @param string    $search Search query.
	 * @param \WP_Query $query  Query object.
	 * @return string Modified search query.
	 */
	public function extend_search_to_meta( $search, $query ) {
		global $wpdb;

		// Only modify admin searches for ai-prompts post type.
		if ( ! is_admin() || ! $query->is_search() ) {
			return $search;
		}

		$post_type = $query->get( 'post_type' );
		if ( 'ai-prompts' !== $post_type ) {
			return $search;
		}

		$search_term = $query->get( 's' );
		if ( empty( $search_term ) ) {
			return $search;
		}

		// Add meta field to search.
		$meta_search = $wpdb->prepare(
			" OR ({$wpdb->postmeta}.meta_key = '_ai_prompt_content' AND {$wpdb->postmeta}.meta_value LIKE %s)",
			'%' . $wpdb->esc_like( $search_term ) . '%'
		);

		// Remove the closing parenthesis of the default search and add our meta search.
		if ( ! empty( $search ) ) {
			$search = preg_replace( '/\)$/i', $meta_search . ')', $search );
		}

		return $search;
	}

	/**
	 * Join postmeta table for search.
	 *
	 * @param string    $join  Join query.
	 * @param \WP_Query $query Query object.
	 * @return string Modified join query.
	 */
	public function join_postmeta( $join, $query ) {
		global $wpdb;

		// Only modify admin searches for ai-prompts post type.
		if ( ! is_admin() || ! $query->is_search() ) {
			return $join;
		}

		$post_type = $query->get( 'post_type' );
		if ( 'ai-prompts' !== $post_type ) {
			return $join;
		}

		$search_term = $query->get( 's' );
		if ( empty( $search_term ) ) {
			return $join;
		}

		// Join postmeta table.
		if ( ! strpos( $join, $wpdb->postmeta ) ) {
			$join .= " LEFT JOIN {$wpdb->postmeta} ON {$wpdb->posts}.ID = {$wpdb->postmeta}.post_id";
		}

		return $join;
	}

	/**
	 * Make search results distinct to avoid duplicates.
	 *
	 * @param string    $distinct Distinct clause.
	 * @param \WP_Query $query    Query object.
	 * @return string Modified distinct clause.
	 */
	public function search_distinct( $distinct, $query ) {
		// Only modify admin searches for ai-prompts post type.
		if ( ! is_admin() || ! $query->is_search() ) {
			return $distinct;
		}

		$post_type = $query->get( 'post_type' );
		if ( 'ai-prompts' !== $post_type ) {
			return $distinct;
		}

		$search_term = $query->get( 's' );
		if ( empty( $search_term ) ) {
			return $distinct;
		}

		return 'DISTINCT';
	}
}
