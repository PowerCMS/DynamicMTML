<?php
function smarty_block_mtifblogdynamicmtml( $args, $content, &$ctx, &$repeat ) {
    $blog = $ctx->stash( 'blog' );
    if ( $blog->has_column( 'dynamic_mtml' ) && $blog->dynamic_mtml ) {
        return $ctx->_hdlr_if( $args, $content, $ctx, $repeat, TRUE );
    } else {
        return $ctx->_hdlr_if( $args, $content, $ctx, $repeat, FALSE );
    }
}
?>