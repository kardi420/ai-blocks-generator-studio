import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import {
	PanelBody,
	TextareaControl,
	Button,
	Spinner,
	Placeholder,
	SelectControl,
} from '@wordpress/components';
import { useState } from '@wordpress/element';
import { useDispatch } from '@wordpress/data';
import { store as blockEditorStore } from '@wordpress/block-editor';
import apiFetch from '@wordpress/api-fetch';

export default function Edit( { attributes, setAttributes, clientId } ) {
	const { prompt, provider, isGenerated } = attributes;
	const [ loading, setLoading ] = useState( false );
	const [ error, setError ] = useState( '' );
	const [ localPrompt, setLocalPrompt ] = useState( prompt );
	const [ localProvider, setLocalProvider ] = useState( provider );

	const { replaceBlock } = useDispatch( blockEditorStore );

	const generateBlock = async () => {
		if ( ! localPrompt.trim() ) {
			setError( __( 'Please enter a prompt', 'ai-blocks-generator-studio' ) );
			return;
		}

		try {
			setLoading( true );
			setError( '' );

			const response = await apiFetch( {
				path: '/ai-blocks-generator-studio/v1/generate',
				method: 'POST',
				data: {
					prompt: localPrompt,
					provider: localProvider,
				},
			} );

			if ( ! response.success || ! response.blockContent ) {
				throw new Error(
					__( 'Error during block generation', 'ai-blocks-generator-studio' )
				);
			}

			// Recursive function to build blocks from JSON structure
			const buildBlocks = ( data ) => {
				const { blockName, attrs = {}, innerBlocks = [] } = data;
				return wp.blocks.createBlock(
					blockName,
					attrs,
					innerBlocks.map( buildBlocks )
				);
			};

			const blocks = buildBlocks( response.blockContent );

			// Replace the current PromptBlock with the generated blocks
			replaceBlock( clientId, blocks );
		} catch ( err ) {
			setError( err.message || __( 'An error occurred', 'ai-blocks-generator-studio' ) );
			console.error( 'AI Generation error:', err );
		} finally {
			setLoading( false );
		}
	};

	if ( isGenerated ) {
		return (
			<div { ...useBlockProps() }>
				<Placeholder
					icon="admin-generic"
					label={ __( 'AI Blocks Studio - Content Generated', 'ai-blocks-generator-studio' ) }
				>
					<Button
						variant="secondary"
						onClick={ () => setAttributes( { isGenerated: false } ) }
					>
						{ __( 'Generate Again', 'ai-blocks-generator-studio' ) }
					</Button>
				</Placeholder>
			</div>
		);
	}

	return (
		<div { ...useBlockProps() }>
			<InspectorControls>
				<PanelBody title={ __( 'AI Settings', 'ai-blocks-generator-studio' ) }>
					<SelectControl
						label={ __( 'AI Provider', 'ai-blocks-generator-studio' ) }
						value={ localProvider }
						options={ [
							{ label: 'OpenAI (GPT-4o)', value: 'openai' },
							{ label: 'Google Gemini', value: 'gemini' },
							{ label: 'Mistral AI', value: 'mistral' },
							{ label: 'Anthropic Claude', value: 'claude' },
						] }
						onChange={ ( val ) => {
							setLocalProvider( val );
							setAttributes( { provider: val } );
						} }
					/>
				</PanelBody>
			</InspectorControls>

			<Placeholder
				icon="admin-generic"
				label={ __( 'AI Blocks Studio', 'ai-blocks-generator-studio' ) }
				instructions={ __(
					'Describe the section or block you want to create.',
					'ai-blocks-generator-studio'
				) }
			>
				<TextareaControl
					value={ localPrompt }
					onChange={ ( val ) => {
						setLocalPrompt( val );
						setAttributes( { prompt: val } );
					} }
					placeholder={ __(
						'e.g. A hero section with a title, description, and a CTA button.',
						'ai-blocks-generator-studio'
					) }
					rows={ 4 }
				/>
				<Button
					variant="primary"
					onClick={ generateBlock }
					isBusy={ loading }
					disabled={ loading || ! localPrompt.trim() }
				>
					{ loading
						? __( 'Generating...', 'ai-blocks-generator-studio' )
						: __( 'Generate Block', 'ai-blocks-generator-studio' ) }
				</Button>
				{ error && (
					<div
						style={ { color: '#cc0000', marginTop: '10px', fontSize: '13px' } }
					>
						{ error }
					</div>
				) }
			</Placeholder>
		</div>
	);
}
