<?php
# DynamicMTML (C) 2010-2011 Alfasado Inc.
# This program is distributed under the terms of the
# GNU General Public License, version 2.

function smarty_function_mtrawmtmltag ( $args, &$ctx ) {
    require_once( 'function.mtml.php' );
    return smarty_function_mtml( $args, $ctx );
}
?>