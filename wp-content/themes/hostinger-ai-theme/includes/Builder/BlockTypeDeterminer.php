<?php

namespace Hostinger\AiTheme\Builder;

use Hostinger\AiTheme\Data\SectionData;
use Hostinger\WpHelper\Requests\Client;
use Hostinger\AiTheme\Rest\Endpoints;

defined( 'ABSPATH' ) || exit;

class BlockTypeDeterminer {
    private Client $client;
    private array $block_types;

    public function __construct( Client $client ) {
        $this->client = $client;
        $this->load_block_types();
    }

    private function load_block_types(): void {
        $website_type      = get_option( 'hostinger_ai_website_type', '' );
        $this->block_types = SectionData::get_sections_for_website_type( $website_type );
    }

    public function determine_block_type( string $description ): array {
        if ( empty( $this->block_types ) ) {
            return [
                'success' => false,
                'error'   => 'Block types not loaded',
            ];
        }

        $system_message = [
            'role'    => 'system',
            'content' => 'You are a block type classifier. Your task is to analyze the user description and return the most appropriate block type from the following list: '
                         . json_encode( $this->block_types )
                         . ' Respond ONLY with a JSON object in this format: {"blockType": "blockType"}. Do not include any other text or explanation.',
        ];

        $user_message = [
            'role'    => 'user',
            'content' => $description,
        ];

        $domain = parse_url( get_site_url(), PHP_URL_HOST );
	    $domain = preg_replace('/^www\./', '', $domain);

        $request_body = [
            'domain'   => $domain,
            'messages' => [ $system_message, $user_message ],
        ];

        $response = $this->client->post( Endpoints::GENERATE_BLOCKS_ENDPOINT, json_encode( $request_body ) );

        if ( is_wp_error( $response ) ) {
            return [
                'success' => false,
                'error'   => $response->get_error_message(),
            ];
        }

        $response_code = wp_remote_retrieve_response_code( $response );
        if ( $response_code !== 200 ) {
            return [
                'success' => false,
                'error'   => 'Invalid response from AI service',
            ];
        }

        $body        = json_decode( wp_remote_retrieve_body( $response ), true );
        $contentData = json_decode( $body['data']['content'], true );
        $blockType   = $contentData['blockType'];

        if ( ! isset( $blockType ) || ! array_key_exists( $blockType, $this->block_types ) ) {
            return [
                'success' => false,
                'error'   => 'Invalid block type returned',
            ];
        }

        return [
            'success'   => true,
            'blockType' => $blockType,
        ];
    }
}
