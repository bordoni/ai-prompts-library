=== AI Prompts Library ===
Contributors: bordoni
Tags: ai, prompts, gutenberg, blocks, chatgpt, claude
Requires at least: 6.7
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A WordPress plugin to create, organize, and reuse AI prompts with advanced management features.

== Description ==

AI Prompts Library helps you manage and organize your AI prompts in WordPress. Create a searchable library of prompts for ChatGPT, Claude, and other AI tools, with features like:

* **Custom Post Type** - Dedicated post type for managing AI prompts
* **Compatibility Taxonomy** - Tag prompts with compatible AI tools (Claude, ChatGPT, Cursor, etc.)
* **Custom Block** - Gutenberg block for displaying and editing prompt content
* **Dual Mode Display** - Automatic context-aware display or manual prompt selection
* **Copy to Clipboard** - Easy one-click copying of prompts
* **Import/Export** - Backup and share prompts via JSON
* **REST API** - Programmatic access to your prompts
* **Advanced Search** - Search across prompt titles and content
* **Character/Word Count** - Automatic tracking of prompt statistics

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/ai-prompts-library` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Use the AI Prompts menu item to create and manage your prompts.

== Frequently Asked Questions ==

= What AI tools are supported? =

The plugin works with any AI tool. Default compatibility terms include Claude, ChatGPT, Cursor, GitHub Copilot, Gemini, Perplexity, and Generic.

= Can I import prompts from other tools? =

Yes! Use the Import/Export feature to import prompts from JSON files.

= How do I display a prompt on my site? =

Use the "Prompt Content" block in the block editor. You can either select a specific prompt or use it within a Query Loop to automatically display prompts.

= Can I search prompt content? =

Yes, the search functionality in the admin area searches both titles and prompt content.

== Screenshots ==

1. Prompt editing interface with character/word count
2. Admin list view with custom columns
3. Prompt Content block settings
4. Import/Export interface
5. Compatibility taxonomy management

== Changelog ==

= 1.0.0 =
* Initial release
* Custom post type for AI prompts
* Compatibility taxonomy
* Prompt Content custom block
* Import/Export functionality
* REST API endpoints
* Copy to clipboard
* Advanced search
* Custom admin columns

== Upgrade Notice ==

= 1.0.0 =
Initial release of AI Prompts Library.

== Developer Notes ==

**REST API Endpoints:**

* `GET /wp-json/ai-prompts-library/v1/prompts` - List prompts
* `GET /wp-json/ai-prompts-library/v1/prompts/{id}` - Get single prompt
* `POST /wp-json/ai-prompts-library/v1/prompts/{id}/duplicate` - Duplicate prompt
* `GET /wp-json/ai-prompts-library/v1/compatibilities` - List compatibility terms
* `GET /wp-json/ai-prompts-library/v1/stats` - Get statistics

**Block Bindings:**

Use the `ai-prompts-library/prompt-meta` source to bind blocks to prompt metadata.

**Filters and Actions:**

* `ai_prompts_library_export_data` - Filter export data
* `ai_prompts_library_import_data` - Filter import data

For full documentation, visit the GitHub repository.
