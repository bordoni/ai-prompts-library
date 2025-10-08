/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import {
	useBlockProps,
	InspectorControls,
	BlockControls,
	PlainText
} from '@wordpress/block-editor';
import { useSelect, useDispatch } from '@wordpress/data';
import { useEntityProp } from '@wordpress/core-data';
import {
	SelectControl,
	PanelBody,
	ToggleControl,
	ToolbarGroup,
	ToolbarButton,
} from '@wordpress/components';
import { useState, useEffect } from '@wordpress/element';
import { copy } from '@wordpress/icons';

/**
 * Edit component for the Prompt Content block.
 *
 * @param {Object}   props               Component props.
 * @param {Object}   props.attributes    Block attributes.
 * @param {Function} props.setAttributes Function to set attributes.
 * @param {Object}   props.context       Block context.
 * @return {Element} React component.
 */
export default function Edit( { attributes, setAttributes, context } ) {
	const { displayMode, selectedPromptId, showCopyButton } = attributes;
	const [ characterCount, setCharacterCount ] = useState( 0 );
	const [ wordCount, setWordCount ] = useState( 0 );

	const blockProps = useBlockProps();

	// Get current post type from the editor.
	const { postType, currentPostId } = useSelect(
		( select ) => {
			const editorSelect = select( 'core/editor' );
			if ( ! editorSelect ) {
				return { postType: null, currentPostId: null };
			}
			return {
				postType: editorSelect.getCurrentPostType ? editorSelect.getCurrentPostType() : null,
				currentPostId: editorSelect.getCurrentPostId ? editorSelect.getCurrentPostId() : null,
			};
		},
		[]
	);

	// Use context postId if available, otherwise use editor postId
	const postId = context.postId || currentPostId;

	// Determine if we're in edit mode (inside ai-prompts CPT).
	const isEditMode = postType === 'ai-prompts' && postId;

	// Get meta field value for edit mode (only if in edit mode).
	const [ meta, setMeta ] = useEntityProp(
		'postType',
		'ai-prompts',
		'meta',
		isEditMode ? postId : undefined
	);

	const promptContent = meta?._ai_prompt_content || '';

	// Update meta field.
	const updatePromptContent = ( value ) => {
		if ( meta ) {
			setMeta( { ...meta, _ai_prompt_content: value } );
		}
	};

	// Calculate counts when content changes.
	useEffect( () => {
		if ( promptContent ) {
			setCharacterCount( promptContent.length );
			setWordCount( promptContent.trim().split( /\s+/ ).filter( Boolean ).length );
		} else {
			setCharacterCount( 0 );
			setWordCount( 0 );
		}
	}, [ promptContent ] );

	// Get available prompts for manual mode.
	const prompts = useSelect(
		( select ) => {
			const { getEntityRecords } = select( 'core' );
			return getEntityRecords( 'postType', 'ai-prompts', {
				per_page: -1,
				orderby: 'title',
				order: 'asc',
			} );
		},
		[]
	);

	// Get selected prompt content for display mode.
	const selectedPrompt = useSelect(
		( select ) => {
			if ( ! isEditMode && displayMode === 'manual' && selectedPromptId ) {
				const { getEntityRecord } = select( 'core' );
				return getEntityRecord( 'postType', 'ai-prompts', selectedPromptId );
			}
			return null;
		},
		[ displayMode, selectedPromptId, isEditMode ]
	);

	const displayContent = selectedPrompt?.meta?._ai_prompt_content || '';

	// Copy to clipboard functionality.
	const { createSuccessNotice } = useDispatch( 'core/notices' );

	const copyToClipboard = () => {
		const textToCopy = isEditMode ? promptContent : displayContent;
		if ( textToCopy ) {
			navigator.clipboard.writeText( textToCopy )
				.then( () => {
					createSuccessNotice(
						__( 'Prompt copied to clipboard', 'ai-prompts-library' ),
						{ type: 'snackbar' }
					);
				} )
				.catch( () => {
					createSuccessNotice(
						__( 'Failed to copy to clipboard', 'ai-prompts-library' ),
						{ type: 'snackbar' }
					);
				} );
		}
	};

	// Render edit mode (editable textarea).
	if ( isEditMode ) {
		return (
			<div { ...blockProps }>
				<BlockControls>
					<ToolbarGroup>
						<ToolbarButton
							icon={ copy }
							label={ __( 'Copy prompt to clipboard', 'ai-prompts-library' ) }
							onClick={ copyToClipboard }
							disabled={ ! promptContent }
						/>
					</ToolbarGroup>
				</BlockControls>

				<InspectorControls>
					<PanelBody title={ __( 'Prompt Statistics', 'ai-prompts-library' ) }>
						<p>
							{ __( 'Character count:', 'ai-prompts-library' ) } { characterCount }
						</p>
						<p>
							{ __( 'Word count:', 'ai-prompts-library' ) } { wordCount }
						</p>
					</PanelBody>
				</InspectorControls>

				<div className="ai-prompt-content-editor">
					<PlainText
						value={ promptContent }
						onChange={ updatePromptContent }
						placeholder={ __( 'Enter your AI prompt here...', 'ai-prompts-library' ) }
						className="ai-prompt-textarea"
					/>
				</div>
			</div>
		);
	}

	// Render display mode (manual or auto).
	return (
		<div { ...blockProps }>
			<InspectorControls>
				<PanelBody title={ __( 'Prompt Settings', 'ai-prompts-library' ) }>
					<SelectControl
						label={ __( 'Display Mode', 'ai-prompts-library' ) }
						value={ displayMode }
						options={ [
							{ label: __( 'Auto (from context)', 'ai-prompts-library' ), value: 'auto' },
							{ label: __( 'Manual selection', 'ai-prompts-library' ), value: 'manual' },
						] }
						onChange={ ( value ) => setAttributes( { displayMode: value } ) }
					/>

					{ displayMode === 'manual' && prompts && (
						<SelectControl
							label={ __( 'Select Prompt', 'ai-prompts-library' ) }
							value={ selectedPromptId }
							options={ [
								{ label: __( '— Select a prompt —', 'ai-prompts-library' ), value: 0 },
								...prompts.map( ( prompt ) => ( {
									label: prompt.title.rendered,
									value: prompt.id,
								} ) ),
							] }
							onChange={ ( value ) => setAttributes( { selectedPromptId: parseInt( value ) } ) }
						/>
					) }

					<ToggleControl
						label={ __( 'Show copy button', 'ai-prompts-library' ) }
						checked={ showCopyButton }
						onChange={ ( value ) => setAttributes( { showCopyButton: value } ) }
					/>
				</PanelBody>
			</InspectorControls>

			<div className="ai-prompt-content-display">
				{ displayMode === 'manual' && selectedPromptId === 0 ? (
					<p className="ai-prompt-placeholder">
						{ __( 'Please select a prompt from the block settings.', 'ai-prompts-library' ) }
					</p>
				) : (
					<>
						{ displayContent ? (
							<pre><code>{ displayContent }</code></pre>
						) : (
							<p className="ai-prompt-placeholder">
								{ displayMode === 'auto'
									? __( 'Prompt content will be displayed from context.', 'ai-prompts-library' )
									: __( 'No content available for the selected prompt.', 'ai-prompts-library' )
								}
							</p>
						) }
					</>
				) }
			</div>
		</div>
	);
}
