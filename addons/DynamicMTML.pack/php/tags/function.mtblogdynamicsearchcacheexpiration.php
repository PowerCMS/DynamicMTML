<?php
function smarty_function_mtblogdynamicsearchcacheexpiration ( $args, &$ctx ) {
    $blog = $ctx->stash( 'blog' );
    if ( $blog->search_cache_expiration ) {
        return $blog->search_cache_expiration;
    }
    return '7200';
}
?>