<?php
function smarty_block_mtclientauthorblock( $args, $content, &$ctx, &$repeat ) {
    $app = $ctx->stash( 'bootstrapper' );
    if ( $app->mode != 'logout' ) {
        $client_author = $ctx->stash( 'client_author' );
    }
    if (! isset( $client_author ) ) {
        if ( $app->mode != 'logout' ) {
            $client_author = $app->user();
        }
    }
    if ( isset( $client_author ) ) {
        $ctx->stash( 'author', $client_author );
        $ctx->stash( 'client_author', $client_author );
        $app->stash( 'user', $client_author );
    } else {
        $repeat = FALSE;
    }
    return $content;
}
?>