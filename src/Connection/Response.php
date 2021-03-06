<?php namespace Hyyppa\FluentFM\Connection;

use Hyyppa\FluentFM\Exception\FilemakerException;
use Psr\Http\Message\ResponseInterface;

/**
 * Class Response
 *
 * @package Hyyppa\FluentFM\Connection
 */
class Response
{

    /**
     * Get response body contents
     *
     * @param ResponseInterface $response
     *
     * @return mixed
     */
    public static function body( ResponseInterface $response )
    {
        $response->getBody()->rewind();

        return json_decode( $response->getBody()->getContents() );
    }


    /**
     * Get response returned records
     *
     * @param ResponseInterface $response
     * @param bool              $with_portals
     *
     * @return array
     */
    public static function records( ResponseInterface $response, bool $with_portals = false ) : array
    {
        $records = [];

        foreach( static::body( $response )->response->data as $record ) {
            $records[ $record->recordId ] = $with_portals ? (array)$record : (array)$record->fieldData;
        }

        return $records;
    }


    /**
     * Get response returned message
     *
     * @param ResponseInterface $response
     *
     * @return mixed
     */
    public static function message( ResponseInterface $response )
    {
        $message = static::body( $response )->messages[ 0 ];

        if( $message->code === '0' ) {
            return null;
        }

        return $message;
    }


    /**
     * @param ResponseInterface $response
     *
     * @throws FilemakerException
     */
    public static function check( ResponseInterface $response ) : void
    {
        switch( $response->getStatusCode() ) {
            case( 200 ):
                break;
        }

        $body = static::body( $response );

        if( ! isset( $body->messages ) ) {
            return;
        }

        $message = $body->messages[ 0 ];

        switch( $message->code ) {
            case( 0 ):
                return;
            default:
                throw new FilemakerException(
                    sprintf( 'FileMaker returned error %d - %s', $message->code, $message->message ),
                    $message->code
                );
        }

    }

}
