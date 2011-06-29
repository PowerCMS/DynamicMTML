<?php
function smarty_function_mtcurrentarchiveurl ( $args, &$ctx ) {
    $app = $ctx->stash( 'bootstrapper' );
    return $app->base() . $app->request;
}
?>