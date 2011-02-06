<?php
# DynamicMTML (C) 2010-2011 Alfasado Inc.
# This program is distributed under the terms of the
# GNU General Public License, version 2.

function smarty_block_mtcommentstrip( $args, $content, $ctx, $repeat ) {
    $begin = preg_quote( '<!--', '/' );
    $end   = preg_quote( '-->', '/' );
    $content = preg_replace( "/$begin/", '', $content );
    $content = preg_replace( "/$end/", '', $content );
    return $content;
}
?>