<?php
/**
 * Import/Export Functionality
 *
 * @package AIPromptsLibrary
 */

namespace AIPromptsLibrary;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Import/Export class for prompts.
 *
 * @since 1.0.0
 */
class ImportExport {
	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_import_export_page' ) );
		add_action( 'admin_post_ai_prompts_export', array( $this, 'handle_export' ) );
		add_action( 'admin_post_ai_prompts_import', array( $this, 'handle_import' ) );
		add_filter( 'bulk_actions-edit-ai-prompts', array( $this, 'add_bulk_export_action' ) );
		add_filter( 'handle_bulk_actions-edit-ai-prompts', array( $this, 'handle_bulk_export' ), 10, 3 );
	}

	/**
	 * Add import/export admin page.
	 *
	 * @since 1.0.0
	 */
	public function add_import_export_page() {
		add_submenu_page(
			'edit.php?post_type=ai-prompts',
			__( 'Import/Export Prompts', 'ai-prompts-library' ),
			__( 'Import/Export', 'ai-prompts-library' ),
			'manage_options',
			'ai-prompts-import-export',
			array( $this, 'render_import_export_page' )
		);
	}

	/**
	 * Render import/export page.
	 *
	 * @since 1.0.0
	 */
	public function render_import_export_page() {
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Import/Export AI Prompts', 'ai-prompts-library' ); ?></h1>

			<div class="ai-prompt-export-form">
				<h2><?php esc_html_e( 'Export Prompts', 'ai-prompts-library' ); ?></h2>
				<p>
					<?php esc_html_e( 'Export all prompts to a JSON file.', 'ai-prompts-library' ); ?>
					<button type="button" class="button button-link" id="show-export-example">
						<?php esc_html_e( 'View Example', 'ai-prompts-library' ); ?>
					</button>
				</p>

				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<?php wp_nonce_field( 'ai_prompts_export', 'ai_prompts_export_nonce' ); ?>
					<input type="hidden" name="action" value="ai_prompts_export">

					<p>
						<label for="export_compatibility">
							<?php esc_html_e( 'Filter by compatibility (optional):', 'ai-prompts-library' ); ?>
						</label>
						<?php
						$terms = get_terms(
							array(
								'taxonomy'   => 'ai-compatibility',
								'hide_empty' => false,
							)
						);
						?>
						<select name="export_compatibility" id="export_compatibility">
							<option value=""><?php esc_html_e( 'All compatibilities', 'ai-prompts-library' ); ?></option>
							<?php foreach ( $terms as $term ) : ?>
								<option value="<?php echo esc_attr( $term->slug ); ?>">
									<?php echo esc_html( $term->name ); ?> (<?php echo esc_html( $term->count ); ?>)
								</option>
							<?php endforeach; ?>
						</select>
					</p>

					<?php submit_button( __( 'Export Prompts', 'ai-prompts-library' ) ); ?>
				</form>
			</div>

			<div class="ai-prompt-import-form">
				<h2><?php esc_html_e( 'Import Prompts', 'ai-prompts-library' ); ?></h2>
				<p>
					<?php esc_html_e( 'Import prompts from a JSON file.', 'ai-prompts-library' ); ?>
					<button type="button" class="button button-link" id="show-import-example">
						<?php esc_html_e( 'View Example', 'ai-prompts-library' ); ?>
					</button>
				</p>

				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data">
					<?php wp_nonce_field( 'ai_prompts_import', 'ai_prompts_import_nonce' ); ?>
					<input type="hidden" name="action" value="ai_prompts_import">

					<p>
						<label for="import_file">
							<?php esc_html_e( 'Select JSON file:', 'ai-prompts-library' ); ?>
						</label>
						<input type="file" name="import_file" id="import_file" accept=".json" required>
					</p>

					<p>
						<label>
							<input type="radio" name="import_mode" value="skip" checked>
							<?php esc_html_e( 'Skip duplicates', 'ai-prompts-library' ); ?>
						</label><br>
						<label>
							<input type="radio" name="import_mode" value="update">
							<?php esc_html_e( 'Update existing (match by slug)', 'ai-prompts-library' ); ?>
						</label><br>
						<label>
							<input type="radio" name="import_mode" value="create">
							<?php esc_html_e( 'Always create new', 'ai-prompts-library' ); ?>
						</label>
					</p>

					<?php submit_button( __( 'Import Prompts', 'ai-prompts-library' ) ); ?>
				</form>
			</div>

			<!-- Export Example Modal -->
			<div id="export-example-modal" class="ai-prompt-modal" style="display:none;">
				<div class="ai-prompt-modal-content">
					<span class="ai-prompt-modal-close" data-modal="export-example-modal">&times;</span>
					<h2><?php esc_html_e( 'Export JSON Format Example', 'ai-prompts-library' ); ?></h2>
					<p><?php esc_html_e( 'This is the structure of the exported JSON file:', 'ai-prompts-library' ); ?></p>
					<pre><code>{
  "version": "1.0",
  "exported_at": "2025-10-07T12:00:00Z",
  "prompts": [
    {
      "title": "System Prompt for Code Review",
      "slug": "system-prompt-code-review",
      "content": "You are an expert code reviewer. Review the following code for:\n- Best practices\n- Security issues\n- Performance optimization\n- Code readability",
      "compatibility": ["claude", "chatgpt", "gemini"],
      "meta": {
        "character_count": 156,
        "word_count": 22
      }
    },
    {
      "title": "Creative Writing Assistant",
      "slug": "creative-writing-assistant",
      "content": "You are a creative writing assistant. Help the user develop compelling narratives with:\n- Character development\n- Plot structure\n- Dialogue improvement\n- Description enhancement",
      "compatibility": ["chatgpt", "claude"],
      "meta": {
        "character_count": 182,
        "word_count": 24
      }
    }
  ]
}</code></pre>
					<p><strong><?php esc_html_e( 'Field Descriptions:', 'ai-prompts-library' ); ?></strong></p>
					<ul>
						<li><code>version</code>: <?php esc_html_e( 'Format version for compatibility', 'ai-prompts-library' ); ?></li>
						<li><code>exported_at</code>: <?php esc_html_e( 'Timestamp of export', 'ai-prompts-library' ); ?></li>
						<li><code>title</code>: <?php esc_html_e( 'Prompt title', 'ai-prompts-library' ); ?></li>
						<li><code>slug</code>: <?php esc_html_e( 'URL-friendly identifier (must be unique)', 'ai-prompts-library' ); ?></li>
						<li><code>content</code>: <?php esc_html_e( 'The actual prompt text', 'ai-prompts-library' ); ?></li>
						<li><code>compatibility</code>: <?php esc_html_e( 'Array of compatible AI tools', 'ai-prompts-library' ); ?></li>
					</ul>
				</div>
			</div>

			<!-- Import Example Modal -->
			<div id="import-example-modal" class="ai-prompt-modal" style="display:none;">
				<div class="ai-prompt-modal-content">
					<span class="ai-prompt-modal-close" data-modal="import-example-modal">&times;</span>
					<h2><?php esc_html_e( 'Import JSON Format Example', 'ai-prompts-library' ); ?></h2>
					<p><?php esc_html_e( 'Create a JSON file with this structure to import prompts:', 'ai-prompts-library' ); ?></p>
					<pre><code>{
  "version": "1.0",
  "prompts": [
    {
      "title": "Your Prompt Title",
      "slug": "your-prompt-slug",
      "content": "Your prompt content goes here...",
      "compatibility": ["claude", "chatgpt"]
    }
  ]
}</code></pre>
					<p><strong><?php esc_html_e( 'Minimum Required Fields:', 'ai-prompts-library' ); ?></strong></p>
					<ul>
						<li><code>title</code>: <?php esc_html_e( 'The prompt title (required)', 'ai-prompts-library' ); ?></li>
						<li><code>slug</code>: <?php esc_html_e( 'Unique identifier (required)', 'ai-prompts-library' ); ?></li>
						<li><code>content</code>: <?php esc_html_e( 'The prompt text (required)', 'ai-prompts-library' ); ?></li>
						<li><code>compatibility</code>: <?php esc_html_e( 'Array of AI tools (optional)', 'ai-prompts-library' ); ?></li>
					</ul>
					<p><strong><?php esc_html_e( 'Available Compatibility Terms:', 'ai-prompts-library' ); ?></strong></p>
					<ul>
						<li><code>claude</code>, <code>chatgpt</code>, <code>cursor</code>, <code>github-copilot</code>, <code>gemini</code>, <code>perplexity</code>, <code>generic</code></li>
					</ul>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Handle export.
	 *
	 * @since 1.0.0
	 */
	public function handle_export() {
		// Check nonce and permissions.
		if ( ! isset( $_POST['ai_prompts_export_nonce'] ) || ! wp_verify_nonce( $_POST['ai_prompts_export_nonce'], 'ai_prompts_export' ) ) {
			wp_die( esc_html__( 'Security check failed', 'ai-prompts-library' ) );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Insufficient permissions', 'ai-prompts-library' ) );
		}

		// Build query args.
		$args = array(
			'post_type'      => 'ai-prompts',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
		);

		// Filter by compatibility if provided.
		if ( ! empty( $_POST['export_compatibility'] ) ) {
			$args['tax_query'] = array(
				array(
					'taxonomy' => 'ai-compatibility',
					'field'    => 'slug',
					'terms'    => sanitize_text_field( $_POST['export_compatibility'] ),
				),
			);
		}

		// Get prompts.
		$prompts = get_posts( $args );

		$export_data = array(
			'version'     => '1.0',
			'exported_at' => current_time( 'mysql' ),
			'prompts'     => array(),
		);

		foreach ( $prompts as $prompt ) {
			$compatibility = wp_get_post_terms( $prompt->ID, 'ai-compatibility', array( 'fields' => 'slugs' ) );

			$export_data['prompts'][] = array(
				'title'          => $prompt->post_title,
				'slug'           => $prompt->post_name,
				'content'        => get_post_meta( $prompt->ID, '_ai_prompt_content', true ),
				'compatibility'  => is_array( $compatibility ) ? $compatibility : array(),
				'meta'           => array(
					'character_count' => get_post_meta( $prompt->ID, '_ai_prompt_character_count', true ),
					'word_count'      => get_post_meta( $prompt->ID, '_ai_prompt_word_count', true ),
				),
			);
		}

		// Output JSON file.
		$filename = 'ai-prompts-export-' . gmdate( 'Y-m-d-His' ) . '.json';

		header( 'Content-Type: application/json' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
		header( 'Pragma: no-cache' );

		echo wp_json_encode( $export_data, JSON_PRETTY_PRINT );
		exit;
	}

	/**
	 * Handle import.
	 *
	 * @since 1.0.0
	 */
	public function handle_import() {
		// Check nonce and permissions.
		if ( ! isset( $_POST['ai_prompts_import_nonce'] ) || ! wp_verify_nonce( $_POST['ai_prompts_import_nonce'], 'ai_prompts_import' ) ) {
			wp_die( esc_html__( 'Security check failed', 'ai-prompts-library' ) );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Insufficient permissions', 'ai-prompts-library' ) );
		}

		// Check if file was uploaded.
		if ( empty( $_FILES['import_file']['tmp_name'] ) ) {
			wp_die( esc_html__( 'No file uploaded', 'ai-prompts-library' ) );
		}

		// Read and parse JSON file.
		$json_content = file_get_contents( $_FILES['import_file']['tmp_name'] );
		$import_data  = json_decode( $json_content, true );

		if ( ! $import_data || ! isset( $import_data['prompts'] ) ) {
			wp_die( esc_html__( 'Invalid JSON file format', 'ai-prompts-library' ) );
		}

		$import_mode = isset( $_POST['import_mode'] ) ? sanitize_text_field( $_POST['import_mode'] ) : 'skip';

		$imported_count = 0;
		$skipped_count  = 0;
		$updated_count  = 0;

		foreach ( $import_data['prompts'] as $prompt_data ) {
			// Check if prompt exists by slug.
			$existing_post = get_page_by_path( $prompt_data['slug'], OBJECT, 'ai-prompts' );

			if ( $existing_post ) {
				if ( 'skip' === $import_mode ) {
					$skipped_count++;
					continue;
				} elseif ( 'update' === $import_mode ) {
					// Update existing prompt.
					$post_id = wp_update_post(
						array(
							'ID'         => $existing_post->ID,
							'post_title' => $prompt_data['title'],
						)
					);
					$updated_count++;
				} else {
					// Create new with unique slug.
					$prompt_data['slug'] = wp_unique_post_slug(
						$prompt_data['slug'],
						0,
						'publish',
						'ai-prompts',
						0
					);
					$post_id = $this->create_prompt( $prompt_data );
					$imported_count++;
				}
			} else {
				// Create new prompt.
				$post_id = $this->create_prompt( $prompt_data );
				$imported_count++;
			}

			// Update meta and taxonomy.
			if ( $post_id && ! is_wp_error( $post_id ) ) {
				update_post_meta( $post_id, '_ai_prompt_content', $prompt_data['content'] );

				// Set compatibility terms.
				if ( ! empty( $prompt_data['compatibility'] ) ) {
					wp_set_post_terms( $post_id, $prompt_data['compatibility'], 'ai-compatibility' );
				}
			}
		}

		// Redirect with success message.
		$redirect_url = add_query_arg(
			array(
				'page'     => 'ai-prompts-import-export',
				'imported' => $imported_count,
				'updated'  => $updated_count,
				'skipped'  => $skipped_count,
			),
			admin_url( 'edit.php?post_type=ai-prompts' )
		);

		wp_safe_redirect( $redirect_url );
		exit;
	}

	/**
	 * Create a new prompt.
	 *
	 * @since 1.0.0
	 *
	 * @param array $prompt_data Prompt data.
	 * @return int|WP_Error Post ID on success, WP_Error on failure.
	 */
	private function create_prompt( $prompt_data ) {
		return wp_insert_post(
			array(
				'post_title'   => $prompt_data['title'],
				'post_name'    => $prompt_data['slug'],
				'post_status'  => 'publish',
				'post_type'    => 'ai-prompts',
				'post_author'  => get_current_user_id(),
			)
		);
	}

	/**
	 * Add bulk export action.
	 *
	 * @since 1.0.0
	 *
	 * @param array $actions Bulk actions.
	 * @return array Modified bulk actions.
	 */
	public function add_bulk_export_action( $actions ) {
		$actions['export_prompts'] = __( 'Export to JSON', 'ai-prompts-library' );
		return $actions;
	}

	/**
	 * Handle bulk export.
	 *
	 * @since 1.0.0
	 *
	 * @param string $redirect_to Redirect URL.
	 * @param string $doaction    Action name.
	 * @param array  $post_ids    Post IDs.
	 * @return string Modified redirect URL.
	 */
	public function handle_bulk_export( $redirect_to, $doaction, $post_ids ) {
		if ( 'export_prompts' !== $doaction ) {
			return $redirect_to;
		}

		$export_data = array(
			'version'     => '1.0',
			'exported_at' => current_time( 'mysql' ),
			'prompts'     => array(),
		);

		foreach ( $post_ids as $post_id ) {
			$prompt = get_post( $post_id );
			if ( ! $prompt || 'ai-prompts' !== $prompt->post_type ) {
				continue;
			}

			$compatibility = wp_get_post_terms( $prompt->ID, 'ai-compatibility', array( 'fields' => 'slugs' ) );

			$export_data['prompts'][] = array(
				'title'          => $prompt->post_title,
				'slug'           => $prompt->post_name,
				'content'        => get_post_meta( $prompt->ID, '_ai_prompt_content', true ),
				'compatibility'  => is_array( $compatibility ) ? $compatibility : array(),
				'meta'           => array(
					'character_count' => get_post_meta( $prompt->ID, '_ai_prompt_character_count', true ),
					'word_count'      => get_post_meta( $prompt->ID, '_ai_prompt_word_count', true ),
				),
			);
		}

		// Store export data in transient for download.
		$transient_key = 'ai_prompts_export_' . wp_generate_password( 12, false );
		set_transient( $transient_key, $export_data, 300 );

		return add_query_arg(
			array(
				'exported'       => count( $post_ids ),
				'export_key'     => $transient_key,
			),
			$redirect_to
		);
	}
}
