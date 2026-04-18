<?php
namespace AiblocksGeneratorStudio;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class REST_API {

	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	public function register_routes() {
		register_rest_route( 'ai-blocks-generator-studio/v1', '/generate', array(
			'methods'             => 'POST',
			'callback'            => array( $this, 'generate_block' ),
			'permission_callback' => array( $this, 'check_permission' ),
		) );
	}

	public function check_permission() {
		return current_user_can( 'edit_posts' );
	}

	public function generate_block( $request ) {
		$params   = $request->get_params();
		$prompt   = isset( $params['prompt'] ) ? sanitize_text_field( $params['prompt'] ) : '';
		$provider = isset( $params['provider'] ) ? sanitize_text_field( $params['provider'] ) : get_option( 'aiblocksgen_default_provider', 'openai' );

		if ( empty( $prompt ) ) {
			return new \WP_Error( 'missing_prompt', __( 'Prompt is required.', 'ai-blocks-generator-studio' ), array( 'status' => 400 ) );
		}

		$handler = new AI_Handler( $provider );
		$result  = $handler->generate_block( $prompt );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return rest_ensure_response( array(
			'success'      => true,
			'blockContent' => $result,
		) );
	}
}
