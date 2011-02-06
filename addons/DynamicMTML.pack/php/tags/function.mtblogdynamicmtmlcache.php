<?php
# DynamicMTML (C) 2010-2011 Alfasado Inc.
# This program is distributed under the terms of the
# GNU General Public License, version 2.

function smarty_function_mtblogdynamicmtmlcache ( $args, &$ctx ) {
    $blog = $ctx->stash( 'blog' );
    if ( $blog->dynamic_cache ) {
        return '$mt->caching( true );';
    }
    return '';
}
?>