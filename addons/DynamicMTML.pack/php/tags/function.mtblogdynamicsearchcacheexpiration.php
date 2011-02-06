<?php
# DynamicMTML (C) 2010-2011 Alfasado Inc.
# This program is distributed under the terms of the
# GNU General Public License, version 2.

function smarty_function_mtblogdynamicsearchcacheexpiration ( $args, &$ctx ) {
    $blog = $ctx->stash( 'blog' );
    if ( $blog->search_cache_expiration ) {
        return $blog->search_cache_expiration;
    }
    return '7200';
}
?>