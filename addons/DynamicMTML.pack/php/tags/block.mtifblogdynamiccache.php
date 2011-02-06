<?php
# DynamicMTML (C) 2010-2011 Alfasado Inc.
# This program is distributed under the terms of the
# GNU General Public License, version 2.

function smarty_block_mtifblogdynamiccache( $args, $content, &$ctx, &$repeat ) {
    $blog = $ctx->stash( 'blog' );
    if ( $blog->has_column( 'dynamic_cache' ) && $blog->dynamic_cache ) {
        return $ctx->_hdlr_if( $args, $content, $ctx, $repeat, TRUE );
    } else {
        return $ctx->_hdlr_if( $args, $content, $ctx, $repeat, FALSE );
    }
}
?>