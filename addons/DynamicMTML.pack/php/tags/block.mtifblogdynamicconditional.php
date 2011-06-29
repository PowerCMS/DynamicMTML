<?php
function smarty_block_mtifblogdynamicconditional( $args, $content, &$ctx, &$repeat ) {
    $blog = $ctx->stash( 'blog' );
    if ( $blog->has_column( 'dynamic_conditional' ) && $blog->dynamic_conditional ) {
        return $ctx->_hdlr_if( $args, $content, $ctx, $repeat, TRUE );
    } else {
        return $ctx->_hdlr_if( $args, $content, $ctx, $repeat, FALSE );
    }
}
?>