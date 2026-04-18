<?php
namespace AiblocksGeneratorStudio;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AI_Handler {

	private $provider;
	private $api_key;

	public function __construct( $provider ) {
		$this->provider = $provider;
		$this->api_key  = get_option( "aiblocksgen_{$provider}_key" );
	}

	public function generate_block( $prompt ) {
		if ( empty( $this->api_key ) ) {
			return new \WP_Error( 'missing_api_key', __( 'API key is missing for the selected provider.', 'ai-blocks-generator-studio' ) );
		}

		$system_prompt = "You are a WordPress Gutenberg expert. Respond ONLY with a valid JSON representation of a Gutenberg block or a series of blocks.
The JSON structure must strictly follow the WordPress core block format. 
Example for a section with padding and background:
{
  \"blockName\": \"core/group\",
  \"attrs\": {
    \"tagName\": \"section\",
    \"style\": {
      \"spacing\": { \"padding\": { \"top\": \"80px\", \"bottom\": \"80px\", \"left\": \"40px\", \"right\": \"40px\" } },
      \"color\": { \"background\": \"#f8f9fa\" }
    },
    \"layout\": { \"type\": \"constrained\" }
  },
  \"innerBlocks\": [
    {
      \"blockName\": \"core/heading\",
      \"attrs\": { \"content\": \"Section Title\", \"level\": 2, \"textAlign\": \"center\" }
    },
    {
      \"blockName\": \"core/paragraph\",
      \"attrs\": { \"content\": \"Sample description text goes here.\", \"align\": \"center\" }
    }
  ]
}

- Use `core/group` with `tagName`: `section` as a container for sections.
- ALWAYS add generous padding (e.g., 60px-100px top/bottom) to sections to make them look premium.
- Apply background colors using `attrs.style.color.background` (hex) or `attrs.backgroundColor` (slug).
- Do NOT use external images. Use solid colors or gradients for backgrounds.
- Support core blocks: core/group, core/columns, core/column, core/heading, core/paragraph, core/image, core/buttons, core/button, core/list.
- Respond ONLY with the raw JSON object. No markdown, no backticks, no explanations.

Design the section for: ";

		switch ( $this->provider ) {
			case 'openai':
				return $this->call_openai( $system_prompt . $prompt );
			case 'gemini':
				return $this->call_gemini( $system_prompt . $prompt );
			case 'mistral':
				return $this->call_mistral( $system_prompt . $prompt );
			case 'claude':
				return $this->call_claude( $system_prompt . $prompt );
			default:
				return new \WP_Error( 'invalid_provider', __( 'Selected AI provider is not supported.', 'ai-blocks-generator-studio' ) );
		}
	}

	private function call_openai( $prompt ) {
		$response = wp_remote_post( 'https://api.openai.com/v1/chat/completions', array(
			'timeout' => 30,
			'headers' => array(
				'Content-Type'  => 'application/json',
				'Authorization' => 'Bearer ' . $this->api_key,
			),
			'body'    => json_encode( array(
				'model'    => 'gpt-4o-mini',
				'messages' => array(
					array( 'role' => 'user', 'content' => $prompt ),
				),
			) ),
		) );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( isset( $body['choices'][0]['message']['content'] ) ) {
			return $this->clean_json_response( $body['choices'][0]['message']['content'] );
		}

		if ( isset( $body['error']['message'] ) ) {
			return new \WP_Error( 'api_error', $body['error']['message'] );
		}

		return new \WP_Error( 'api_error', __( 'Error from OpenAI API.', 'ai-blocks-generator-studio' ) );
	}

	private function call_gemini( $prompt ) {
		$models     = array( 'gemini-2.0-flash', 'gemini-1.5-flash', 'gemini-1.5-pro', 'gemini-pro' );
		$last_error = null;

		foreach ( $models as $model ) {
			$url = "https://generativelanguage.googleapis.com/v1/models/{$model}:generateContent?key=" . $this->api_key;
			
			$response = wp_remote_post( $url, array(
				'timeout' => 30,
				'headers' => array( 'Content-Type' => 'application/json' ),
				'body'    => json_encode( array(
					'contents' => array(
						array( 'parts' => array( array( 'text' => $prompt ) ) ),
					),
				) ),
			) );

			if ( is_wp_error( $response ) ) {
				return $response;
			}

			$code = wp_remote_retrieve_response_code( $response );
			$body = json_decode( wp_remote_retrieve_body( $response ), true );

			if ( $code === 200 && isset( $body['candidates'][0]['content']['parts'][0]['text'] ) ) {
				return $this->clean_json_response( $body['candidates'][0]['content']['parts'][0]['text'] );
			}

			$last_error = isset( $body['error']['message'] ) ? $body['error']['message'] : __( 'Error from Gemini API.', 'ai-blocks-generator-studio' );
			
			// If not a 404 (model not found), don't try other models
			if ( $code !== 404 ) {
				break;
			}
		}

		return new \WP_Error( 'api_error', $last_error );
	}

	private function call_mistral( $prompt ) {
		$response = wp_remote_post( 'https://api.mistral.ai/v1/chat/completions', array(
			'timeout' => 30,
			'headers' => array(
				'Content-Type'  => 'application/json',
				'Authorization' => 'Bearer ' . $this->api_key,
			),
			'body'    => json_encode( array(
				'model'    => 'mistral-small-latest',
				'messages' => array(
					array( 'role' => 'user', 'content' => $prompt ),
				),
			) ),
		) );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( isset( $body['choices'][0]['message']['content'] ) ) {
			return $this->clean_json_response( $body['choices'][0]['message']['content'] );
		}

		if ( isset( $body['error']['message'] ) ) {
			return new \WP_Error( 'api_error', $body['error']['message'] );
		}

		return new \WP_Error( 'api_error', __( 'Error from Mistral API.', 'ai-blocks-generator-studio' ) );
	}

	private function call_claude( $prompt ) {
		$response = wp_remote_post( 'https://api.anthropic.com/v1/messages', array(
			'timeout' => 30,
			'headers' => array(
				'Content-Type'      => 'application/json',
				'x-api-key'         => $this->api_key,
				'anthropic-version' => '2023-06-01',
			),
			'body'    => json_encode( array(
				'model'     => 'claude-3-5-haiku-latest',
				'max_tokens' => 2048,
				'messages'  => array(
					array( 'role' => 'user', 'content' => $prompt ),
				),
			) ),
		) );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( isset( $body['content'][0]['text'] ) ) {
			return $this->clean_json_response( $body['content'][0]['text'] );
		}

		if ( isset( $body['error']['message'] ) ) {
			return new \WP_Error( 'api_error', $body['error']['message'] );
		}

		return new \WP_Error( 'api_error', __( 'Error from Claude API.', 'ai-blocks-generator-studio' ) );
	}

	private function clean_json_response( $text ) {
		// Remove markdown backticks if present
		$text = preg_replace( '/^```json/i', '', $text );
		$text = preg_replace( '/```$/', '', $text );
		$text = trim( $text );

		$json = json_decode( $text, true );
		if ( json_last_error() === JSON_ERROR_NONE ) {
			return $json;
		}

		return new \WP_Error( 'invalid_json', __( 'The AI returned an invalid JSON response.', 'ai-blocks-generator-studio' ) );
	}
}
