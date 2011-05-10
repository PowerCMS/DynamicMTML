<?php
function smarty_block_mtiflogin( $args, $content, &$ctx, &$repeat ) {
    $app = $ctx->stash( 'bootstrapper' );
    if ( $app->mode == 'logout' ) {
        return $ctx->_hdlr_if( $args, $content, $ctx, $repeat, FALSE );
    }
    $client_author = $ctx->stash( 'client_author' );
    if (! isset( $client_author ) ) {
        $client_author = $app->user();
    }
    if (! isset( $client_author ) ) {
        return $ctx->_hdlr_if( $args, $content, $ctx, $repeat, FALSE );
    }
    $ctx->stash( 'author', $client_author );
    $ctx->stash( 'client_author', $client_author );
    $app->stash( 'user', $client_author );
    return $ctx->_hdlr_if( $args, $content, $ctx, $repeat, TRUE );
}
?>