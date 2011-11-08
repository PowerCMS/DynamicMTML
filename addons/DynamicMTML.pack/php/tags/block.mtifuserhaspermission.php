<?php
function smarty_block_mtifuserhaspermission( $args, $content, &$ctx, &$repeat ) {
    $app = $ctx->stash( 'bootstrapper' );
    $client_author = $ctx->stash( 'client_author' );
    if (! isset( $client_author ) ) {
        $client_author = $app->user();
    }
    if (! isset( $client_author ) ) {
        return $ctx->_hdlr_if( $args, $content, $ctx, $repeat, FALSE );
    }
    $permission = $app->can_do( $ctx, $args[ 'permission' ], $client_author, $ctx->stash[ 'blog' ] );
    if ( $permission ) {
        return $ctx->_hdlr_if( $args, $content, $ctx, $repeat, TRUE );
    } else {
        return $ctx->_hdlr_if( $args, $content, $ctx, $repeat, FALSE );
    }
}
?>