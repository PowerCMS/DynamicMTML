<?php
function smarty_block_mtseterrorhandler( $args, $content, &$ctx, &$repeat ) {
    # ERROR LEVEL LIST: http://php.net/manual/ja/errorfunc.constants.php
    $level = $ctx->mt->config( 'DynamicErrorHandlerLevel' )
                ? $ctx->mt->config( 'DynamicErrorHandlerLevel' )
                : E_ALL & ~E_NOTICE;
    set_error_handler( 'error_handler_mtseterrorhandler', $level );
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