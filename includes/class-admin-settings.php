<?php
namespace AiblocksGeneratorStudio;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Admin_Settings {

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	public function add_admin_menu() {
		add_menu_page(
			__( 'AI Blocks Studio', 'ai-blocks-generator-studio' ),
			__( 'AI Blocks Studio', 'ai-blocks-generator-studio' ),
			'manage_options',
			'ai-blocks-generator-studio',
			array( $this, 'render_settings_page' ),
			'dashicons-superhero',
			100
		);
	}

	public function register_settings() {
		register_setting( 'aiblocksgen_options', 'aiblocksgen_openai_key', array( 'sanitize_callback' => 'sanitize_text_field' ) );
		register_setting( 'aiblocksgen_options', 'aiblocksgen_gemini_key', array( 'sanitize_callback' => 'sanitize_text_field' ) );
		register_setting( 'aiblocksgen_options', 'aiblocksgen_mistral_key', array( 'sanitize_callback' => 'sanitize_text_field' ) );
		register_setting( 'aiblocksgen_options', 'aiblocksgen_claude_key', array( 'sanitize_callback' => 'sanitize_text_field' ) );
		register_setting( 'aiblocksgen_options', 'aiblocksgen_default_provider', array( 'sanitize_callback' => 'sanitize_text_field' ) );
	}

	public function render_settings_page() {
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'AI Blocks Studio Settings', 'ai-blocks-generator-studio' ); ?></h1>
			<p><?php esc_html_e( 'Configure your AI API keys to enable block generation.', 'ai-blocks-generator-studio' ); ?></p>
			
			<form method="post" action="options.php">
				<?php
				settings_fields( 'aiblocksgen_options' );
				do_settings_sections( 'aiblocksgen_options' );
				?>
				<table class="form-table">
					<tr valign="top">
						<th scope="row"><?php esc_html_e( 'OpenAI API Key', 'ai-blocks-generator-studio' ); ?></th>
						<td><input type="text" name="aiblocksgen_openai_key" value="<?php echo esc_attr( get_option( 'aiblocksgen_openai_key' ) ); ?>" class="regular-text" /></td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php esc_html_e( 'Gemini API Key', 'ai-blocks-generator-studio' ); ?></th>
						<td><input type="text" name="aiblocksgen_gemini_key" value="<?php echo esc_attr( get_option( 'aiblocksgen_gemini_key' ) ); ?>" class="regular-text" /></td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php esc_html_e( 'Mistral API Key', 'ai-blocks-generator-studio' ); ?></th>
						<td><input type="text" name="aiblocksgen_mistral_key" value="<?php echo esc_attr( get_option( 'aiblocksgen_mistral_key' ) ); ?>" class="regular-text" /></td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php esc_html_e( 'Claude API Key', 'ai-blocks-generator-studio' ); ?></th>
						<td><input type="text" name="aiblocksgen_claude_key" value="<?php echo esc_attr( get_option( 'aiblocksgen_claude_key' ) ); ?>" class="regular-text" /></td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php esc_html_e( 'Default Provider', 'ai-blocks-generator-studio' ); ?></th>
						<td>
							<select name="aiblocksgen_default_provider">
								<option value="openai" <?php selected( get_option( 'aiblocksgen_default_provider' ), 'openai' ); ?>>OpenAI</option>
								<option value="gemini" <?php selected( get_option( 'aiblocksgen_default_provider' ), 'gemini' ); ?>>Gemini</option>
								<option value="mistral" <?php selected( get_option( 'aiblocksgen_default_provider' ), 'mistral' ); ?>>Mistral</option>
								<option value="claude" <?php selected( get_option( 'aiblocksgen_default_provider' ), 'claude' ); ?>>Claude</option>
							</select>
						</td>
					</tr>
				</table>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}
}
