<?php
function smarty_function_mtuseragent ( $args, &$ctx ) {
    $app = $ctx->stash( 'bootstrapper' );
    if ( $args[ 'raw' ] ) {
        return $_SERVER[ 'HTTP_USER_AGENT' ];
    }
    $like  = $args[ 'like' ];
    $wants = $args[ 'wants' ];
    return $app->get_agent( $wants, $like );
}
?>