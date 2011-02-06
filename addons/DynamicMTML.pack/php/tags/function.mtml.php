<?php
# DynamicMTML (C) 2010-2011 Alfasado Inc.
# This program is distributed under the terms of the
# GNU General Public License, version 2.

function smarty_function_mtml ( $args, &$ctx ) {
    $tag = $args[ 'tag' ];
    $params = $args[ 'params' ];
    $tag = trim( $tag );
    if ( $params ) {
        $tag = "<$tag $params>";
    } else {
        $tag = "<$tag>";
    }
    return $tag;
}
?>