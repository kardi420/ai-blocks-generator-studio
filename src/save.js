import { useBlockProps } from '@wordpress/block-editor';

export default function save() {
	return null; // The block is replaced in the editor, so it doesn't need a save function for the placeholder.
}
