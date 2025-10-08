# AI Prompts Library - Agent Instructions

## Plugin Overview

**AI Prompts Library** is a WordPress plugin for managing, organizing, and reusing AI prompts. It provides a complete system for storing prompts with compatibility tracking, advanced search, import/export, and REST API access.

**Location**: `/wp-content/plugins/ai-prompts-library/`

**Current Version**: 1.0.0

**WordPress Version**: 6.7+ required (for Block Bindings API)

**PHP Version**: 7.4+

**Node Version**: 20 (specified in `.nvmrc`)

---

## Technology Stack

### Backend (PHP)
- **Custom Post Type**: `ai-prompts`
- **Custom Taxonomy**: `ai-compatibility` (non-hierarchical)
- **Meta Fields**: `_ai_prompt_content`, `_ai_prompt_character_count`, `_ai_prompt_word_count`, `_ai_prompt_model`
- **REST API**: Custom namespace `ai-prompts-library/v1`
- **Block Bindings**: WordPress 6.7+ Block Bindings API

### Frontend (JavaScript/React)
- **Build Tool**: @wordpress/scripts (webpack-based)
- **Block Editor**: Gutenberg custom block
- **Dependencies**: @wordpress/blocks, @wordpress/block-editor, @wordpress/components, @wordpress/data
- **Styling**: SCSS (compiled to CSS)

---

## Architecture

### File Structure

```
ai-prompts-library/
├── ai-prompts-library.php          # Main plugin file, autoloader, hooks
├── readme.txt                      # WordPress.org readme
├── package.json                    # npm dependencies
├── .nvmrc                         # Node version (20)
├── .gitignore                     # Git ignore rules
│
├── includes/                       # PHP classes (autoloaded)
│   ├── class-plugin.php           # Main plugin class, initialization
│   ├── class-post-type.php        # CPT registration
│   ├── class-taxonomy.php         # Taxonomy registration
│   ├── class-meta-fields.php      # Meta field registration & hooks
│   ├── class-block-bindings.php   # Block Bindings API integration
│   ├── class-admin-columns.php    # Custom admin list table columns
│   ├── class-rest-api.php         # REST API endpoints
│   ├── class-import-export.php    # Import/Export functionality
│   ├── class-search-enhancement.php # Search meta field integration
│   └── functions-helpers.php      # Helper functions
│
├── src/                           # Source files (JavaScript/React)
│   ├── index.js                   # Main entry point
│   └── prompt-content/            # Prompt Content block
│       ├── block.json            # Block metadata
│       ├── index.js              # Block registration
│       ├── edit.js               # Edit component (dual-mode)
│       ├── save.js               # Save component (returns null)
│       ├── render.php            # Server-side rendering
│       ├── style.scss            # Frontend styles
│       └── editor.scss           # Editor-only styles
│
├── build/                         # Compiled assets (gitignored)
│   └── prompt-content/           # Compiled block files
│       ├── block.json
│       ├── index.js
│       ├── index.asset.php
│       ├── index.css
│       ├── index-rtl.css
│       ├── render.php
│       ├── style-index.css
│       └── style-index-rtl.css
│
└── assets/                        # Static assets
    ├── css/
    │   └── admin.css             # Admin-specific styles
    └── js/
        └── admin.js              # Admin-specific scripts
```

---

## Key Components

### 1. Custom Post Type: `ai-prompts`

**File**: `includes/class-post-type.php`

**Registration**: `Post_Type::register()`

**Features**:
- Public with archive at `/prompts/`
- Block editor support
- Predefined block template (Heading → Prompt Content → Paragraph)
- Template lock: `false` (allows adding more blocks)
- Supports: title, editor, author, revisions, custom-fields

**Template Structure**:
```php
[
    ['core/heading', ['level' => 2, 'placeholder' => 'Prompt Title (optional)']],
    ['ai-prompts-library/prompt-content', []],
    ['core/paragraph', ['placeholder' => 'Add a description or usage notes...']],
]
```

### 2. Custom Taxonomy: `ai-compatibility`

**File**: `includes/class-taxonomy.php`

**Registration**: `Taxonomy::register()`

**Type**: Non-hierarchical (tag-like)

**Default Terms** (created on activation):
- Claude
- ChatGPT
- Cursor
- GitHub Copilot
- Gemini
- Perplexity
- Generic

**Usage**: Tag prompts with compatible AI tools

### 3. Meta Fields

**File**: `includes/class-meta-fields.php`

**Registered Fields**:

| Meta Key | Type | Description | Auto-calculated |
|----------|------|-------------|-----------------|
| `_ai_prompt_content` | string | Main prompt content | No |
| `_ai_prompt_character_count` | integer | Character count | Yes (on save) |
| `_ai_prompt_word_count` | integer | Word count | Yes (on save) |
| `_ai_prompt_model` | string | Target AI model (optional) | No |

**Auto-calculation**: Hooked to `updated_post_meta` and `added_post_meta` actions for `_ai_prompt_content`

### 4. Custom Block: Prompt Content

**Block Name**: `ai-prompts-library/prompt-content`

**File**: `src/prompt-content/edit.js`

**Dual-Mode Behavior**:

#### Mode 1: Edit Mode (in `ai-prompts` CPT)
- Detects post type using `useSelect` and `getCurrentPostType()`
- Renders editable `<PlainText>` component
- Bound to `_ai_prompt_content` meta field via `useEntityProp`
- Shows character/word count in Inspector Controls
- Copy to clipboard button in BlockControls toolbar

#### Mode 2: Display Mode (in other post types)
- Two sub-modes:
  - **Auto Mode** (default): Uses Query Loop context (`postId` from context)
  - **Manual Mode**: User selects specific prompt via dropdown
- Shows prompt content in `<pre><code>` tags
- Optional copy button (controlled by `showCopyButton` attribute)

**Attributes**:
```json
{
  "displayMode": "auto" | "manual",
  "selectedPromptId": number,
  "showCopyButton": boolean
}
```

**Server-Side Rendering**: `src/prompt-content/render.php`
- Handles both auto and manual display modes
- Gets content from post meta
- Renders copy button if enabled
- Escapes output for security

### 5. REST API

**File**: `includes/class-rest-api.php`

**Namespace**: `ai-prompts-library/v1`

**Endpoints**:

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/prompts` | List all prompts (with filtering) | No |
| GET | `/prompts/{id}` | Get single prompt | No |
| POST | `/prompts/{id}/duplicate` | Duplicate a prompt | Yes (edit_posts) |
| GET | `/compatibilities` | List compatibility terms | No |
| GET | `/stats` | Get statistics | No |

**Query Parameters for `/prompts`**:
- `compatibility`: Filter by term slug
- `search`: Search term
- `per_page`: Items per page (default: 10)
- `page`: Page number (default: 1)

**Response Format**:
```json
{
  "id": 123,
  "title": "System Prompt Example",
  "content": "You are a helpful assistant...",
  "excerpt": "You are a helpful...",
  "character_count": 100,
  "word_count": 15,
  "compatibility": ["claude", "chatgpt"],
  "date": "2025-10-07 12:00:00",
  "modified": "2025-10-07 12:00:00",
  "author": 1,
  "status": "publish",
  "link": "https://example.com/prompts/system-prompt-example/"
}
```

### 6. Import/Export

**File**: `includes/class-import-export.php`

**Admin Page**: AI Prompts → Import/Export

**Export Features**:
- Export all prompts or filter by compatibility
- JSON format with metadata
- Bulk action in admin list table
- Downloads as `ai-prompts-export-YYYY-MM-DD-His.json`

**Import Features**:
- Upload JSON file
- Three import modes:
  - **Skip duplicates**: Don't import if slug exists
  - **Update existing**: Update prompt matching slug
  - **Always create new**: Create with unique slug
- Imports meta fields and taxonomy terms

**JSON Format**:
```json
{
  "version": "1.0",
  "exported_at": "2025-10-07 12:00:00",
  "prompts": [
    {
      "title": "Prompt Title",
      "slug": "prompt-slug",
      "content": "Prompt content here...",
      "compatibility": ["claude", "chatgpt"],
      "meta": {
        "character_count": 100,
        "word_count": 15
      }
    }
  ]
}
```

### 7. Search Enhancement

**File**: `includes/class-search-enhancement.php`

**Functionality**:
- Extends WordPress admin search to include `_ai_prompt_content` meta field
- Only active for `ai-prompts` post type in admin
- Joins postmeta table and adds meta search to query
- Returns distinct results to avoid duplicates

**Filters Used**:
- `posts_search`: Adds meta field to search query
- `posts_join`: Joins postmeta table
- `posts_distinct`: Ensures no duplicate results

### 8. Admin Columns

**File**: `includes/class-admin-columns.php`

**Custom Columns**:
- **Prompt Excerpt**: First 100 characters of prompt content
- **Characters**: Character count (sortable)
- **Words**: Word count (sortable)
- **Compatibility**: Already shown by taxonomy (via `show_admin_column`)

**Styling**: `assets/css/admin.css`

### 9. Block Bindings

**File**: `includes/class-block-bindings.php`

**Source Name**: `ai-prompts-library/prompt-meta`

**Usage**: Allows binding any block attribute to prompt meta fields

**Example**:
```json
{
  "bindings": {
    "content": {
      "source": "ai-prompts-library/prompt-meta",
      "args": {
        "key": "_ai_prompt_content"
      }
    }
  }
}
```

---

## Development Workflow

### Building the Plugin

```bash
# Ensure correct Node version
nvm use

# Install dependencies
npm install

# Development build (watch mode)
npm start

# Production build
npm run build

# Linting
npm run lint:js
npm run lint:css

# Format code
npm run format
```

### Adding New Features

#### Adding a New Meta Field

1. Register in `includes/class-meta-fields.php`:
```php
register_post_meta(
    'ai-prompts',
    '_ai_prompt_custom_field',
    array(
        'show_in_rest' => true,
        'single'       => true,
        'type'         => 'string',
        'description'  => __( 'Description', 'ai-prompts-library' ),
        'auth_callback' => function() {
            return current_user_can( 'edit_posts' );
        },
    )
);
```

2. Add to REST API response in `includes/class-rest-api.php` → `prepare_prompt_data()` method

3. Add to export format in `includes/class-import-export.php` → `handle_export()` method

#### Adding a New Block

1. Create directory in `src/`
2. Add `block.json` with metadata
3. Create `index.js`, `edit.js`, `save.js`, `render.php`
4. Import in `src/index.js`
5. Register in `includes/class-plugin.php` → `register_blocks()` method

#### Adding a New REST Endpoint

1. Add route in `includes/class-rest-api.php` → `register_routes()` method:
```php
register_rest_route(
    self::NAMESPACE,
    '/custom-endpoint',
    array(
        'methods'             => \WP_REST_Server::READABLE,
        'callback'            => array( __CLASS__, 'custom_endpoint_callback' ),
        'permission_callback' => '__return_true',
    )
);
```

2. Implement callback method in same class

---

## Common Tasks

### Modifying the Block Template

**File**: `includes/class-post-type.php`

Edit the `template` array in `Post_Type::register()`:
```php
'template' => array(
    array('core/heading', ['level' => 2, 'placeholder' => 'Title']),
    array('ai-prompts-library/prompt-content', []),
    // Add more blocks here
),
```

### Adding Default Compatibility Terms

**File**: `includes/class-taxonomy.php`

Edit the `$default_terms` array in `Taxonomy::create_default_terms()`:
```php
$default_terms = array(
    'Claude',
    'ChatGPT',
    'New Tool', // Add here
);
```

### Customizing Export Format

**File**: `includes/class-import-export.php`

Modify the export data structure in `handle_export()` method:
```php
$export_data['prompts'][] = array(
    'title'         => $prompt->post_title,
    'slug'          => $prompt->post_name,
    'content'       => get_post_meta( $prompt->ID, '_ai_prompt_content', true ),
    'compatibility' => $compatibility,
    'custom_field'  => get_post_meta( $prompt->ID, '_custom_field', true ), // Add here
    'meta'          => array(
        'character_count' => get_post_meta( $prompt->ID, '_ai_prompt_character_count', true ),
        'word_count'      => get_post_meta( $prompt->ID, '_ai_prompt_word_count', true ),
    ),
);
```

### Modifying Block Styles

**Frontend**: `src/prompt-content/style.scss` (affects both editor and frontend)

**Editor Only**: `src/prompt-content/editor.scss`

After editing, run `npm run build` to compile.

### Adding Custom Admin Columns

**File**: `includes/class-admin-columns.php`

1. Add column in `add_custom_columns()`:
```php
$new_columns['custom_column'] = __( 'Custom Column', 'ai-prompts-library' );
```

2. Render in `render_custom_columns()`:
```php
case 'custom_column':
    $value = get_post_meta( $post_id, '_custom_meta', true );
    echo esc_html( $value );
    break;
```

3. Make sortable in `make_columns_sortable()` (optional):
```php
$columns['custom_column'] = 'custom_meta_key';
```

---

## Security Considerations

### Capability Checks

All write operations check `current_user_can('edit_posts')` or `manage_options`

**Locations**:
- Meta field auth callbacks: `includes/class-meta-fields.php`
- REST API permissions: `includes/class-rest-api.php`
- Import/Export: `includes/class-import-export.php`

### Data Sanitization

**Input**:
- `sanitize_textarea_field()` for prompt content
- `sanitize_text_field()` for text inputs
- `wp_kses_post()` if HTML is needed

**Output**:
- `esc_html()` for text
- `esc_attr()` for attributes
- `wp_kses_post()` for allowed HTML

**Locations**: Throughout all PHP files

### Nonce Verification

- All forms use `wp_nonce_field()` and `wp_verify_nonce()`
- REST API handles nonces automatically

### SQL Injection Prevention

- Use `$wpdb->prepare()` for custom queries
- WordPress meta API handles escaping automatically

**Example**: `includes/class-search-enhancement.php`

---

## Helper Functions

**File**: `includes/functions-helpers.php`

Available functions in `AIPromptsLibrary` namespace:

| Function | Description |
|----------|-------------|
| `get_prompt_content($post_id)` | Get prompt content by post ID |
| `get_ai_prompts($args)` | Get all prompts with query args |
| `get_prompt_compatibility($post_id)` | Get compatibility terms for a prompt |
| `calculate_character_count($content)` | Calculate character count |
| `calculate_word_count($content)` | Calculate word count |
| `sanitize_prompt_content($content)` | Sanitize prompt content |
| `current_user_can_edit_prompts()` | Check if user can edit prompts |
| `current_user_can_publish_prompts()` | Check if user can publish prompts |

**Usage Example**:
```php
use AIPromptsLibrary;

$content = AIPromptsLibrary\get_prompt_content( 123 );
$prompts = AIPromptsLibrary\get_ai_prompts( array( 'posts_per_page' => 10 ) );
```

---

## Testing

### Manual Testing Checklist

- [ ] Activate plugin without errors
- [ ] Create a new AI prompt
- [ ] Add prompt content in the Prompt Content block
- [ ] Verify character/word count auto-calculation
- [ ] Assign compatibility terms
- [ ] Test copy to clipboard in editor
- [ ] Publish prompt and view on frontend
- [ ] Test search functionality (search prompt content)
- [ ] Test import/export with JSON file
- [ ] Test bulk export from admin list
- [ ] Test REST API endpoints with Postman/cURL
- [ ] Test block in Query Loop context
- [ ] Test block with manual prompt selection
- [ ] Verify custom admin columns display correctly
- [ ] Test prompt duplication via REST API

### REST API Testing

```bash
# List prompts
curl https://example.com/wp-json/ai-prompts-library/v1/prompts

# Get single prompt
curl https://example.com/wp-json/ai-prompts-library/v1/prompts/123

# Filter by compatibility
curl https://example.com/wp-json/ai-prompts-library/v1/prompts?compatibility=claude

# Get compatibilities
curl https://example.com/wp-json/ai-prompts-library/v1/compatibilities

# Get stats
curl https://example.com/wp-json/ai-prompts-library/v1/stats
```

---

## Troubleshooting

### Block Not Appearing in Editor

1. Check build output: `ls -la build/prompt-content/`
2. Rebuild: `npm run build`
3. Clear browser cache
4. Check browser console for errors

### Meta Fields Not Saving

1. Verify REST API registration in `includes/class-meta-fields.php`
2. Check `show_in_rest => true` is set
3. Verify auth callback returns `true`
4. Check browser network tab for API errors

### Search Not Working

1. Verify post type is `ai-prompts`
2. Check if search is in admin area
3. Inspect SQL query with Query Monitor plugin
4. Verify `Search_Enhancement` class is initialized in `Plugin` class

### Import/Export Errors

1. Check JSON format validity
2. Verify file upload permissions
3. Check PHP error logs
4. Ensure `manage_options` capability
5. Verify nonce is valid

### Build Errors

1. Check Node version: `node -v` (should be 20.x)
2. Use nvm: `nvm use`
3. Clear node_modules: `rm -rf node_modules && npm install`
4. Check for JavaScript syntax errors

---

## Performance Considerations

### Caching

The plugin does not implement custom caching. Consider:
- WordPress object cache for frequent queries
- Transients API for expensive operations
- Page caching for frontend display

### Database Queries

- Custom admin columns add minimal overhead (single meta query per row)
- Search enhancement joins postmeta (indexed by default)
- REST API limits results with pagination

### Asset Loading

- Block assets only load when block is present (handled by WordPress)
- Admin assets only load in admin area
- Frontend styles are minimal (< 2KB)

---

## Internationalization

**Text Domain**: `ai-prompts-library`

**Translation Functions Used**:
- `__()` - Returns translated string
- `_e()` - Echoes translated string
- `_x()` - Translates with context
- `_n()` - Translates plurals
- `esc_html__()` - Translates and escapes for HTML
- `esc_html_e()` - Echoes translated and escaped HTML
- `esc_attr__()` - Translates and escapes for attributes

**JavaScript i18n**:
```javascript
import { __ } from '@wordpress/i18n';

const label = __( 'Prompt Content', 'ai-prompts-library' );
```

**Translation File Location**: `languages/` (if added)

---

## Hooks & Filters

### Available Filters

**Note**: These are potential extension points. Custom filters can be added as needed.

**Suggested Filters to Add**:
- `ai_prompts_library_export_data` - Filter export data before JSON encode
- `ai_prompts_library_import_data` - Filter import data after JSON decode
- `ai_prompts_library_prompt_content` - Filter prompt content before display
- `ai_prompts_library_rest_response` - Filter REST API response

**Example Implementation**:
```php
// In includes/class-import-export.php
$export_data = apply_filters( 'ai_prompts_library_export_data', $export_data, $prompts );
```

### WordPress Core Hooks Used

- `plugins_loaded` - Initialize plugin
- `init` - Register post types, taxonomies, meta, blocks
- `rest_api_init` - Register REST routes
- `admin_menu` - Add admin pages
- `admin_enqueue_scripts` - Enqueue admin assets
- `wp_enqueue_scripts` - Enqueue frontend assets (currently unused)
- `manage_{post_type}_posts_columns` - Add custom columns
- `manage_{post_type}_posts_custom_column` - Render custom columns
- `manage_edit-{post_type}_sortable_columns` - Make columns sortable
- `updated_post_meta`, `added_post_meta` - Auto-calculate counts
- `posts_search`, `posts_join`, `posts_distinct` - Search enhancement
- `rest_prepare_{post_type}` - Extend REST response
- `bulk_actions-edit-{post_type}` - Add bulk actions
- `handle_bulk_actions-edit-{post_type}` - Handle bulk actions

---

## Future Enhancements

Based on the original plan, these features are not yet implemented:

### High Priority
- [ ] Frontend JavaScript for copy button (currently editor-only)
- [ ] Syntax highlighting with Prism.js
- [ ] Line numbers in code display
- [ ] Prompt variables system (e.g., `{{variable_name}}`)

### Medium Priority
- [ ] Version control for prompts (track revisions)
- [ ] Prompt Collections (custom taxonomy for grouping)
- [ ] Analytics (usage tracking, popular prompts)
- [ ] Block patterns for common prompt structures

### Low Priority
- [ ] Custom capabilities (beyond default post capabilities)
- [ ] Advanced search UI (beyond native WordPress search)
- [ ] Shortcode support for prompt display
- [ ] AI integration (test prompts, token estimation, cost calculation)

---

## Agent Best Practices

### When Modifying This Plugin

1. **Always read existing code** before making changes
2. **Follow WordPress coding standards**
3. **Maintain namespace**: All classes in `AIPromptsLibrary\`
4. **Test after changes**: Run `npm run build` after JS/CSS changes
5. **Check for errors**: Look at browser console and PHP error log
6. **Update this file** if you add new features or change architecture
7. **Use existing patterns**: Follow the structure of existing classes

### When Adding Features

1. **Check if similar code exists** (e.g., another meta field, REST endpoint)
2. **Use helper functions** from `functions-helpers.php`
3. **Follow security practices**: Sanitize input, escape output, check capabilities
4. **Add translation functions**: Wrap all user-facing strings
5. **Document complex logic**: Add PHPDoc/JSDoc comments
6. **Consider performance**: Use efficient queries, avoid N+1 problems

### When Debugging

1. **Enable WordPress debug mode**: `WP_DEBUG`, `WP_DEBUG_LOG` in `wp-config.php`
2. **Use browser DevTools**: Check Console and Network tabs
3. **Install Query Monitor**: Helps debug queries, hooks, and API calls
4. **Check error logs**: `wp-content/debug.log` for PHP errors
5. **Inspect REST API**: Use browser DevTools Network tab or Postman
6. **Rebuild assets**: `npm run build` if block changes don't appear

---

## Related Documentation

- [WordPress Block Editor Handbook](https://developer.wordpress.org/block-editor/)
- [WordPress REST API Handbook](https://developer.wordpress.org/rest-api/)
- [WordPress Plugin Handbook](https://developer.wordpress.org/plugins/)
- [@wordpress/scripts Documentation](https://developer.wordpress.org/block-editor/reference-guides/packages/packages-scripts/)
- [Block Bindings API](https://developer.wordpress.org/block-editor/reference-guides/block-api/block-bindings/)

---

## Support & Contribution

For issues, bugs, or feature requests, please consult the original plan document at:
`/Users/bordoni/workspace/srv/plan/ai-prompts-plugin-plan.md`

**Maintainer**: Based on plan by Gustavo Bordoni (bordoni)

**Repository**: Local development (not yet on GitHub/WordPress.org)

**License**: GPL v2 or later
