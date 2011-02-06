<?php
# DynamicMTML (C) 2010-2011 Alfasado Inc.
# This program is distributed under the terms of the
# GNU General Public License, version 2.

function smarty_function_mtblogfilesmatch ( $args, &$ctx ) {
    $app = $ctx->stash( 'bootstrapper' );
    $blog = $ctx->stash( 'blog' );
    $dynamic_extension = $blog->dynamic_extension;
    if (! $dynamic_extension ) {
        $dynamic_extension = 'html,mtml';
    }
    $dynamic_extension = preg_replace( '/\s/', '', $dynamic_extension );
    $lc = strtolower ( $dynamic_extension );
    $uc = strtoupper ( $dynamic_extension );
    $extensions = explode( ',', $lc );
    $extensions_uc = split( ',', $uc );
    $extensions = array_merge( $extensions, $extensions_uc );
    $dynamic_extension = implode( '|', $extensions );
    $FilesMatch = $dynamic_extension;
    return $FilesMatch;
}
?>