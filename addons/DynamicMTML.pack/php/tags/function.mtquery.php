<?php
function smarty_function_mtquery ( $args, &$ctx ) {
    $app = $ctx->stash( 'bootstrapper' );
    $query = $app->param( $args[ 'key' ] );
    $from_encoding = mb_detect_encoding( $query,'UTF-8,EUC-JP,SJIS,JIS' );
    $to_encoding = $ctx->mt->config( 'PublishCharset' );
    $to_encoding or $to_encoding = 'UTF-8';
    return mb_convert_encoding( $query, $to_encoding, $from_encoding );
}
?>