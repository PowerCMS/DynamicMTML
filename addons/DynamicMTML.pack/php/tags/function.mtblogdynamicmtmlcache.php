<?php
function smarty_function_mtblogdynamicmtmlcache ( $args, &$ctx ) {
    $blog = $ctx->stash( 'blog' );
    if ( $blog->dynamic_cache ) {
        return '$mt->caching( true );';
    }
    return '';
}
?>