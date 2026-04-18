<?php
/**
 * Plugin Name: AI Blocks Generator Studio
 * Plugin URI:  https://wordpress.org/plugins/ai-blocks-generator-studio
 * Description: Design Gutenberg blocks sections with AI (OpenAI, Gemini, Mistral, Claude).
 * Version:     1.0.0
 * Author:      Ibrahim
 * Author URI : https://github.com/ikardi420
 * Text Domain: ai-blocks-generator-studio
 * License:     GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

namespace AiblocksGeneratorStudio;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define constants
define( 'AIBLOCKSGEN_VERSION', '1.0.0' );
define( 'AIBLOCKSGEN_PATH', plugin_dir_path( __FILE__ ) );
define( 'AIBLOCKSGEN_URL', plugin_dir_url( __FILE__ ) );

// Load dependencies
require_once AIBLOCKSGEN_PATH . 'includes/class-admin-settings.php';
require_once AIBLOCKSGEN_PATH . 'includes/class-ai-handler.php';
require_once AIBLOCKSGEN_PATH . 'includes/class-rest-api.php';

/**
 * Initialize the plugin blocks.
 */
function aiblocksgen_init_blocks() {
	register_block_type( AIBLOCKSGEN_PATH . 'build' );
}
add_action( 'init', __NAMESPACE__ . '\\aiblocksgen_init_blocks' );

// Initialize Settings
if ( is_admin() ) {
	new Admin_Settings();
}

// Initialize REST API
new REST_API();
