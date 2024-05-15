<?php
namespace phputil\flags\webhooks;

class WebOptions {
    private ?string $url = null;
    private ?array $headers = null;
    private bool $async = false;

    public function __construct(
        ?string $url = null,
        ?array $headers = null,
        bool $async = false
    ) {
        $this->withUrl( $url );
        $this->withHeaders( $headers );
        $this->withAsync( $async );
    }

    public function getUrl(): ?string {
        return $this->url;
    }

    public function withUrl( ?string $url ): WebOptions {
        $this->url = $url;
        return $this;
    }

    public function getHeaders(): ?array {
        return $this->headers;
    }

    public function withHeaders( ?array $headers ): WebOptions {
        $this->headers = $headers;
        return $this;
    }

    public function getAsync(): bool {
        return $this->async;
    }

    public function withAsync( bool $async ): WebOptions {
        $this->async = $async;
        return $this;
    }
}

?>