<?php
# DynamicMTML (C) 2010-2011 Alfasado Inc.
# This program is distributed under the terms of the
# GNU General Public License, version 2.

function smarty_block_mtloginauthorctx( $args, $content, &$ctx, &$repeat ) {
    require_once ( 'block.mtclientauthorblock.php' );
    return smarty_block_mtclientauthorblock( $args, $content, $ctx, $repeat );
}
?>