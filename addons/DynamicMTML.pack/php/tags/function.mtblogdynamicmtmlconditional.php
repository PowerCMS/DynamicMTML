<?php
function smarty_function_mtblogdynamicmtmlconditional ( $args, &$ctx ) {
    $blog = $ctx->stash( 'blog' );
    if ( $blog->dynamic_conditional ) {
        return '$mt->conditional( true );';
    }
    return '';
}
?>