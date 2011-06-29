<?php
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