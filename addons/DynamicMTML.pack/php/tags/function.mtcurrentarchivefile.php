<?php
function smarty_function_mtcurrentarchivefile ( $args, &$ctx ) {
    $app = $ctx->stash( 'bootstrapper' );
    return $app->file();
}
?>