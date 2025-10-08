/**
 * WordPress dependencies
 */
import { registerBlockType } from '@wordpress/blocks';

/**
 * Internal dependencies
 */
import edit from './edit';
import save from './save';
import metadata from './block.json';
import './style.scss';
import './editor.scss';

/**
 * Register the Prompt Content block.
 *
 * @since 1.0.0
 */
registerBlockType( metadata.name, {
	...metadata,
	edit,
	save,
} );
