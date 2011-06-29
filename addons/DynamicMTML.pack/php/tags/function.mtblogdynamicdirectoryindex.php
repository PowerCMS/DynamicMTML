<?php
function smarty_function_mtblogdynamicdirectoryindex ( $args, &$ctx ) {
    $blog = $ctx->stash( 'blog' );
    if ( $blog->index_files ) {
        return $blog->index_files;
    }
    return 'index.html,index.mtml';
}
?>