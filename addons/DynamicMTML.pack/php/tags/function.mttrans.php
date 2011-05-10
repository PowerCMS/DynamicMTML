<?php
function smarty_function_mttrans ( $args, &$ctx ) {
    $app = $ctx->stash( 'bootstrapper' );
    $phrase  = $args[ 'phrase' ];
    $params  = $args[ 'params' ];
    if ( preg_match( '/%%/', $params ) ) {
        $params = explode( '%%', $params );
    }
    if ( isset ( $app ) ) {
        return $app->translate( $phrase, $params );
    } else {
        return $ctx->mt->translate( $phrase, $params );
    }
}
?>