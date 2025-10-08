# AI Prompts Library

A WordPress plugin to create, organize, and reuse AI prompts with advanced management features.

[![License](https://img.shields.io/badge/license-GPL--2.0%2B-blue.svg)](https://www.gnu.org/licenses/gpl-2.0.html)
[![WordPress](https://img.shields.io/badge/wordpress-6.7%2B-blue.svg)](https://wordpress.org/)
[![PHP](https://img.shields.io/badge/php-7.4%2B-blue.svg)](https://www.php.net/)

## Description

AI Prompts Library helps you manage and organize your AI prompts in WordPress. Create a searchable library of prompts for ChatGPT, Claude, and other AI tools.

## Features

- **Custom Post Type** - Dedicated post type for managing AI prompts
- **Compatibility Taxonomy** - Tag prompts with compatible AI tools (Claude, ChatGPT, Cursor, etc.)
- **Custom Gutenberg Block** - Block for displaying and editing prompt content with dual-mode behavior
- **Copy to Clipboard** - Easy one-click copying of prompts from the block toolbar
- **Import/Export** - Backup and share prompts via JSON format
- **REST API** - Programmatic access to your prompts
- **Advanced Search** - Search across prompt titles and content
- **Character/Word Count** - Automatic tracking of prompt statistics
- **Block Bindings API** - Seamless integration with WordPress 6.7+ Block Bindings

## Requirements

- WordPress 6.7 or higher (required for Block Bindings API)
- PHP 7.4 or higher
- Node.js 20 (for development)

## Installation

### From GitHub

1. Download the latest release from the [releases page](https://github.com/bordoni/ai-prompts-library/releases)
2. Upload the plugin files to `/wp-content/plugins/ai-prompts-library/`
3. Activate the plugin through the 'Plugins' screen in WordPress
4. Use the AI Prompts menu item to create and manage your prompts

### For Development

```bash
git clone git@github.com:bordoni/ai-prompts-library.git
cd ai-prompts-library
npm install
npm run build
```

## Usage

### Creating a Prompt

1. Go to **AI Prompts** > **Add New** in your WordPress admin
2. Enter a title for your prompt
3. Add a description or usage notes in the first paragraph
4. Enter your prompt content in the Prompt Content block
5. Assign compatibility tags (Claude, ChatGPT, etc.)
6. Publish your prompt

### Displaying a Prompt

**Auto Mode (Context-Aware)**
- Add the Prompt Content block to any post/page
- When used in a Query Loop with AI Prompts, it automatically displays the current prompt

**Manual Mode**
- Add the Prompt Content block anywhere
- In block settings, select "Manual selection"
- Choose the specific prompt you want to display

### Import/Export

Navigate to **AI Prompts** > **Import/Export**

**Export:**
- Click "Export Prompts" to download all prompts as JSON

**Import:**
- Upload a JSON file containing prompts
- Click "Import Prompts" to add them to your library

## REST API

The plugin provides REST API endpoints for programmatic access:

### Endpoints

```
GET    /wp-json/ai-prompts-library/v1/prompts
GET    /wp-json/ai-prompts-library/v1/prompts/{id}
POST   /wp-json/ai-prompts-library/v1/prompts/{id}/duplicate
GET    /wp-json/ai-prompts-library/v1/compatibilities
GET    /wp-json/ai-prompts-library/v1/stats
```

### Example: Get All Prompts

```bash
curl https://yoursite.com/wp-json/ai-prompts-library/v1/prompts
```

### Example: Duplicate a Prompt

```bash
curl -X POST https://yoursite.com/wp-json/ai-prompts-library/v1/prompts/123/duplicate \
  -H "Content-Type: application/json" \
  -u username:password
```

## Development

### Building Assets

```bash
# Development build with watch mode
npm run start

# Production build
npm run build
```

### Code Quality

```bash
# Lint JavaScript
npm run lint:js

# Lint CSS
npm run lint:css

# Format code
npm run format
```

### Using Pup for Releases

This plugin uses [stellarwp/pup](https://github.com/stellarwp/pup) for build automation:

```bash
# Build release version
pup build

# Create a new release
pup release
```

## Block Bindings API

The plugin registers a custom Block Bindings source (`ai-prompts-library/prompt-meta`) that allows you to bind blocks to prompt metadata fields:

- `_ai_prompt_content` - The main prompt content
- `_ai_prompt_character_count` - Character count
- `_ai_prompt_word_count` - Word count
- `_ai_prompt_model` - Target AI model

## Filters and Actions

### Filters

```php
// Filter export data before creating JSON
add_filter( 'ai_prompts_library_export_data', function( $prompts ) {
    // Modify $prompts array
    return $prompts;
} );

// Filter import data before processing
add_filter( 'ai_prompts_library_import_data', function( $data ) {
    // Modify $data array
    return $data;
} );
```

## File Structure

```
ai-prompts-library/
├── assets/
│   ├── css/
│   │   └── admin.css
│   └── js/
│       └── admin.js
├── build/                      # Compiled assets (generated)
├── includes/
│   ├── class-admin-columns.php
│   ├── class-block-bindings.php
│   ├── class-import-export.php
│   ├── class-meta-fields.php
│   ├── class-plugin.php
│   ├── class-post-type.php
│   ├── class-rest-api.php
│   ├── class-search-enhancement.php
│   ├── class-taxonomy.php
│   └── functions-helpers.php
├── languages/                  # Translation files
├── src/
│   ├── prompt-content/
│   │   ├── block.json
│   │   ├── edit.js
│   │   ├── editor.scss
│   │   ├── index.js
│   │   ├── render.php
│   │   ├── save.js
│   │   └── style.scss
│   └── index.js
├── .gitignore
├── .nvmrc
├── .puprc
├── AGENTS.md
├── ai-prompts-library.php
├── package.json
├── readme.txt
└── README.md
```

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'feat: add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

This plugin is licensed under the GPL v2 or later.

```
AI Prompts Library
Copyright (C) 2025 Gustavo Bordoni

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
```

## Author

**Gustavo Bordoni**
- Website: [bordoni.me](https://bordoni.me)
- GitHub: [@bordoni](https://github.com/bordoni)

## Support

For bugs and feature requests, please use the [GitHub issues](https://github.com/bordoni/ai-prompts-library/issues) page.

---

Made with ❤️ for the AI and WordPress communities.
