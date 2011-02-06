<?php
# DynamicMTML (C) 2010-2011 Alfasado Inc.
# This program is distributed under the terms of the
# GNU General Public License, version 2.

function smarty_function_mtblogdynamicexcludeextension ( $args, &$ctx ) {
    $blog = $ctx->stash( 'blog' );
    if ( $blog->exclude_extension ) {
        return $blog->exclude_extension;
    }
    return 'php,cgi,fcgi';
}
?>