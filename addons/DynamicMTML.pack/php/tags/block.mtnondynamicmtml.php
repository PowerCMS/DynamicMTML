<?php
# DynamicMTML (C) 2010-2011 Alfasado Inc.
# This program is distributed under the terms of the
# GNU General Public License, version 2.

function smarty_block_mtnondynamicmtml( $args, $content, &$ctx, &$repeat ) {
    $app = $ctx->stash( 'bootstrapper' );
    $build_type = $app->build_type;
    if ( $build_type == 'rebuild_static' ) {
        if ( isset ( $content ) ) {
            return "<MTNonDynamicMTML>$content</MTNonDynamicMTML>";
        }
    } else {
        return '';
    }
}
?>