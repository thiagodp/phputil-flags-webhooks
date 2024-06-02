<?php

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use phputil\flags\FlagData;
use phputil\flags\FlagException;
use phputil\flags\FlagMetadata;
use phputil\flags\webhooks\WebhookListener;
use phputil\flags\webhooks\WebOptions;

describe( 'WebhookListener', function() {

    beforeEach( function() {
        // @see https://docs.guzzlephp.org/en/stable/testing.html#mock-handler

        $this->container = [];
        $this->history = Middleware::history( $this->container );

        $this->mock = new MockHandler();
        $this->handlerStack = HandlerStack::create( $this->mock );

        $this->handlerStack->push( $this->history );

        $this->config = [ 'handler' => $this->handlerStack ];
    } );

    it( 'triggers a POST request for a new flag', function() {

        $this->mock->append( new Response( 200 ) );

        $listener = new WebhookListener( null, $this->config );
        $flag = new FlagData( 'foo', false, new FlagMetadata() );
        $listener->notify( 'change', $flag );

        expect( $this->container )->toHaveLength( 1 );
        $request = $this->container[ 0 ][ 'request' ];
        expect( $request->getMethod() )->toBe( 'POST' );

    } );


    it( 'throws FlagException when the server returns with 400', function() {

        $this->mock->append( new Response( 400 ) );

        $listener = new WebhookListener( null, $this->config );
        $flag = new FlagData( 'foo', false, new FlagMetadata() );

        expect( function() use ( $listener, $flag ) {
            $listener->notify( 'change', $flag );
        } )->toThrowAnExceptionWithClassName( FlagException::class );
    } );


    it( 'throws FlagException when the server returns with 500', function() {

        $this->mock->append( new Response( 500 ) );

        $listener = new WebhookListener( null, $this->config );
        $flag = new FlagData( 'foo', false, new FlagMetadata() );

        expect( function() use ( $listener, $flag ) {
            $listener->notify( 'change', $flag );
        } )->toThrowAnExceptionWithClassName( FlagException::class );
    } );


    it( 'triggers a PUT request for an existing flag', function() {

        $this->mock->append( new Response( 200 ) );

        $listener = new WebhookListener( null, $this->config );
        $flag = new FlagData( 'foo', false, new FlagMetadata( 1 ) );
        $listener->notify( 'change', $flag );

        expect( $this->container )->toHaveLength( 1 );
        $request = $this->container[ 0 ][ 'request' ];
        expect( $request->getMethod() )->toBe( 'PUT' );
    } );

    it( 'add the flag id to a PUT request URL', function() {

        $this->mock->append( new Response( 200 ) );

        $listener = new WebhookListener( null, $this->config );

        $flag = new FlagData( 'foo', false, new FlagMetadata( 1 ) );
        $listener->notify( 'change', $flag );

        expect( $this->container )->toHaveLength( 1 );
        $request = $this->container[ 0 ][ 'request' ];
        expect( $request->getUri()->getPath() )->toBe( '/1' );
    } );


    it( 'triggers a DELETE request for a flag removal', function() {

        $this->mock->append( new Response( 200 ) );

        $listener = new WebhookListener( null, $this->config );
        $flag = new FlagData( 'foo', false, new FlagMetadata( 1 ) );
        $listener->notify( 'removal', $flag );

        expect( $this->container )->toHaveLength( 1 );
        $request = $this->container[ 0 ][ 'request' ];
        expect( $request->getMethod() )->toBe( 'DELETE' );
    } );


    it( 'add the flag id to a DELETE request URL', function() {

        $this->mock->append( new Response( 200 ) );

        $listener = new WebhookListener( null, $this->config );

        $flag = new FlagData( 'foo', false, new FlagMetadata( 1 ) );
        $listener->notify( 'removal', $flag );

        expect( $this->container )->toHaveLength( 1 );
        $request = $this->container[ 0 ][ 'request' ];
        expect( $request->getUri()->getPath() )->toBe( '/1' );
    } );


    describe( 'options', function() {

        it( 'assumes the base option when the creation option is not defined', function() {
            $url = 'https://foo.com';
            $l = new WebhookListener( new WebOptions( $url ) );
            expect( $l->creationOptions()->getUrl() )->toEqual( $url );
        } );

        it( 'assumes the base option when the change option is not defined', function() {
            $url = 'https://foo.com';
            $l = new WebhookListener( new WebOptions( $url ) );
            expect( $l->changeOptions()->getUrl() )->toEqual( $url );
        } );

        it( 'assumes the base option when the removal option is not defined', function() {
            $url = 'https://foo.com';
            $l = new WebhookListener( new WebOptions( $url ) );
            expect( $l->removalOptions()->getUrl() )->toEqual( $url );
        } );

    } );

    it( 'can be instantiated with a url', function() {
        $url = 'https://foo.com';
        $l = new WebhookListener( $url );
        expect( $l->baseOptions()->getUrl() )->toEqual( $url );
    } );
} );

?>