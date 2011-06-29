<?php
function smarty_block_mtentrycategoryblock ( $args, $content, &$ctx, &$repeat ) {
    $localvars = array( 'category' );
    if (! isset( $content ) ) {
        $ctx->localize( $localvars );
        $app = $ctx->stash( 'bootstrapper' );
        $entry = $ctx->stash( 'entry' );
        $category = $entry->category();
        if ( $category ) {
            $ctx->stash( 'category', $category );
        } else {
            $ctx->restore( $localvars );
            $repeat = false;
        }
    } else {
        $ctx->restore($localvars);
        $repeat = false;
    }
    return $content;
}
?>