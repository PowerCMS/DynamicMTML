<?php
# DynamicMTML (C) 2010-2011 Alfasado Inc.
# This program is distributed under the terms of the
# GNU General Public License, version 2.

function smarty_function_mtuseragent ( $args, &$ctx ) {
    $app = $ctx->stash( 'bootstrapper' );
    if ( $args[ 'raw' ] ) {
        return $_SERVER[ 'HTTP_USER_AGENT' ];
    }
    $like  = $args[ 'like' ];
    $wants = $args[ 'wants' ];
    return $app->get_agent( $wants, $like );
}
?>