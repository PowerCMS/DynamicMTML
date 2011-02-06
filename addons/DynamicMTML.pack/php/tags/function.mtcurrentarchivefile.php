<?php
# DynamicMTML (C) 2010-2011 Alfasado Inc.
# This program is distributed under the terms of the
# GNU General Public License, version 2.

function smarty_function_mtcurrentarchivefile ( $args, &$ctx ) {
    $app = $ctx->stash( 'bootstrapper' );
    return $app->file();
}
?>