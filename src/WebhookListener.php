<?php
namespace phputil\flags\webhooks;

use phputil\flags\FlagException;
use phputil\flags\FlagListener;
use phputil\flags\FlagData;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;

const KEY_BASE = '_';
const KEY_POST = 'POST';
const KEY_PUT = 'PUT';
const KEY_DELETE = 'DELETE';

class WebhookListener implements FlagListener {

    private Client $client;

    /** @var array<string, WebOptions> */
    private array $options = [];

    public function __construct(
        $baseOptionsOrUrl = null,
        array $guzzleConfig = []
    ) {
        if ( is_string( $baseOptionsOrUrl ) ) {
            $this->options[ KEY_BASE ] = new WebOptions( $baseOptionsOrUrl );
        } else if ( is_a( $baseOptionsOrUrl, WebOptions::class ) ) {
            $this->options[ KEY_BASE ] = $baseOptionsOrUrl;
        }
        $this->client = new Client( $guzzleConfig );
    }

    public function baseOptions(): WebOptions {
        return $this->getOptions( KEY_BASE );
    }

    public function creationOptions(): WebOptions {
        return $this->getOptions( KEY_POST );
    }

    public function changeOptions(): WebOptions {
        return $this->getOptions( KEY_PUT );
    }

    public function removalOptions(): WebOptions {
        return $this->getOptions( KEY_DELETE );
    }

    public function notify( string $event, FlagData $flag ): void {
        if ( 'removal' === $event ) {
            $this->notifyRemoved( $flag );
            return;
        }
        if ( $flag->metadata->id == 0 && 'change' === $event ) {
            $this->notifyCreated( $flag );
            return;
        }
        if ( 'change' === $event ) {
            $this->notifyChanged( $flag );
        }
    }

    public function notifyCreated( FlagData $flag ): void {

        $url = $this->getUrl( KEY_POST );
        $headers = $this->getHeaders( KEY_POST );

        $options = [ 'json' => $flag ];
        if ( $headers !== null ) {
            $options[ 'headers' ] = $headers;
        }

        try {
            $this->client->request( 'POST', $url, $options );
        } catch ( BadResponseException $e ) {
            $msg = 'Flag creation error. Returned body: ' . $e->getResponse()->getBody();
            throw new FlagException( $msg, $e->getResponse()->getStatusCode(), $e );
        }
    }

    public function notifyChanged( FlagData $flag ): void {

        $url = $this->getUrl( KEY_PUT );
        $url .= '/' . $flag->metadata->id; // Add id

        $headers = $this->getHeaders( KEY_PUT );

        $options = [ 'json' => $flag ];
        if ( $headers !== null ) {
            $options[ 'headers' ] = $headers;
        }

        try {
            $this->client->request( 'PUT', $url, $options );
        } catch ( BadResponseException $e ) {
            $msg = 'Flag update error. Returned body: ' . $e->getResponse()->getBody();
            throw new FlagException( $msg, $e->getResponse()->getStatusCode(), $e );
        }
    }

    public function notifyRemoved( FlagData $flag ): void {

        $url = $this->getUrl( KEY_DELETE );
        $url .= '/' . $flag->metadata->id; // Add id

        $headers = $this->getHeaders( KEY_DELETE );

        $options = [];
        if ( $headers !== null ) {
            $options[ 'headers' ] = $headers;
        }

        try {
            $this->client->request( 'DELETE', $url, $options );
        } catch ( BadResponseException $e ) {
            $msg = 'Flag removal error. Returned body: ' . $e->getResponse()->getBody();
            throw new FlagException( $msg, $e->getResponse()->getStatusCode(), $e );
        }
    }

    private function getOptions( string $key ): WebOptions {
        if ( isset( $this->options[ $key ] ) ) {
            return $this->options[ $key ];
        }
        $this->options[ $key ] = isset( $this->options[ KEY_BASE ] )
            ? $this->options[ KEY_BASE ]->clone() : new WebOptions();

        return $this->options[ $key ];
    }

    private function getUrl( string $key ): string {
        return $this->getOptions( $key )->getUrl() ??
            $this->baseOptions()->getUrl() ?? '';
    }

    private function getHeaders( string $key ): array {
        return $this->getOptions( $key )->getHeaders() ??
            $this->baseOptions()->getHeaders() ?? [];
    }

}
