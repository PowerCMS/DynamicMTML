<?php
function smarty_function_mtblogdynamicexcludeextension ( $args, &$ctx ) {
    $blog = $ctx->stash( 'blog' );
    if ( $blog->exclude_extension ) {
        return $blog->exclude_extension;
    }
    return 'php,cgi,fcgi';
}
?>