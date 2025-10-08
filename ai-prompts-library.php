<?php
/**
 * Plugin Name: AI Prompts Library
 * Plugin URI: https://bordoni.me/ai-prompts-library
 * Description: A WordPress plugin to create, organize, and reuse AI prompts with advanced management features.
 * Version: 1.0.1
 * Requires at least: 6.7
 * Requires PHP: 7.4
 * Author: Gustavo Bordoni
 * Author URI: https://bordoni.me
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: ai-prompts-library
 * Domain Path: /languages
 *
 * @package AIPromptsLibrary
 */

namespace AIPromptsLibrary;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin constants.
define( 'AI_PROMPTS_LIBRARY_VERSION', '1.0.1' );
define( 'AI_PROMPTS_LIBRARY_FILE', __FILE__ );
define( 'AI_PROMPTS_LIBRARY_PATH', plugin_dir_path( __FILE__ ) );
define( 'AI_PROMPTS_LIBRARY_URL', plugin_dir_url( __FILE__ ) );

// Load Composer autoloader.
if ( file_exists( AI_PROMPTS_LIBRARY_PATH . 'vendor/autoload.php' ) ) {
	require_once AI_PROMPTS_LIBRARY_PATH . 'vendor/autoload.php';
} else {
	/**
	 * Fallback autoloader for plugin classes when Composer is not available.
	 *
	 * @since 1.0.0
	 *
	 * @param string $class The class name to load.
	 */
	function autoloader( $class ) {
		// Check if the class is in our namespace.
		if ( strpos( $class, __NAMESPACE__ ) !== 0 ) {
			return;
		}

		// Remove namespace from class name.
		$class = str_replace( __NAMESPACE__ . '\\', '', $class );

		// Convert class name to filename.
		$file = 'class-' . strtolower( str_replace( '_', '-', $class ) ) . '.php';

		// Build the full file path.
		$path = AI_PROMPTS_LIBRARY_PATH . 'includes/' . $file;

		// Include the file if it exists.
		if ( file_exists( $path ) ) {
			require_once $path;
		}
	}

	spl_autoload_register( __NAMESPACE__ . '\\autoloader' );

	// Include helper functions.
	require_once AI_PROMPTS_LIBRARY_PATH . 'includes/functions-helpers.php';
}

/**
 * Initialize the plugin.
 *
 * @since 1.0.0
 */
function init() {
	// Load plugin text domain.
	load_plugin_textdomain(
		'ai-prompts-library',
		false,
		dirname( plugin_basename( __FILE__ ) ) . '/languages'
	);

	// Initialize main plugin class.
	Plugin::instance();
}

add_action( 'plugins_loaded', __NAMESPACE__ . '\\init' );

/**
 * Plugin activation hook.
 *
 * @since 1.0.0
 */
function activate() {
	// Register post types and taxonomies.
	PostType::register();
	Taxonomy::register();

	// Flush rewrite rules.
	flush_rewrite_rules();

	// Create default taxonomy terms.
	Taxonomy::create_default_terms();
}

register_activation_hook( __FILE__, __NAMESPACE__ . '\\activate' );

/**
 * Plugin deactivation hook.
 *
 * @since 1.0.0
 */
function deactivate() {
	// Flush rewrite rules.
	flush_rewrite_rules();
}

register_deactivation_hook( __FILE__, __NAMESPACE__ . '\\deactivate' );
