<?php
function smarty_function_mterror ( $args, &$ctx ) {
    $message = $args[ 'message' ];
    if (! $message ) $message = 'An error occurs.';
    return $ctx->error( $message );
}
?>