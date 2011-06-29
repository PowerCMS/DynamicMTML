<?php
function smarty_block_mtseterrorhandler( $args, $content, &$ctx, &$repeat ) {
    set_error_handler( 'error_handler_mtseterrorhandler' );
    return $content;
}
function error_handler_mtseterrorhandler( $code, $message, $errline, $errcontext ) {
    require_once( 'class.mt_log.php' );
    $_log = new Log;
    $_log->message = "Error in {$errline}:{$message}(Error code:{$code})";
    $_log->author_id = 0;
    $_log->ip = $_SERVER[ 'REMOTE_ADDR' ];
    $_log->class = 'dynamic';
    $_log->level = 4;
    $ts = gmdate( "YmdHis" );
    $_log->created_on  = $ts;
    $_log->modified_on = $ts;
    $_log->Save();
}
?>