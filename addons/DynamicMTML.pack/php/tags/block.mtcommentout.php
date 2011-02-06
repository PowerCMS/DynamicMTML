<?php
# DynamicMTML (C) 2010-2011 Alfasado Inc.
# This program is distributed under the terms of the
# GNU General Public License, version 2.

function smarty_block_mtcommentout( $args, $content, $ctx, $repeat ) {
    return '<!--' . $content . '-->';
}
?>