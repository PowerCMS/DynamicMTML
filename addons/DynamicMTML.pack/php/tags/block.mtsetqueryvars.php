<?php
function smarty_block_mtsetqueryvars( $args, $content, &$ctx, &$repeat ) {
    $localvars = array( '__old_vars' );
    $to_encoding = $ctx->mt->config( 'PublishCharset' );
    $to_encoding or $to_encoding = 'UTF-8';
    if (! isset( $content ) ) {
        $ctx->stash[ '__old_vars' ] = $ctx->__stash[ 'vars' ];
        $vars = $_REQUEST;
        $request_keys = array();
        foreach ( $vars as $key => $val ) {
            if ( $_GET[ $key ] || $_POST[ $key ] ) {
                array_push( $request_keys, $key );
            }
        }
        foreach ( $request_keys as $key ) {
            $value = $vars[ $key ];
            if ( is_array( $value ) ) {
                $value = $value[0];
            }
            $from_encoding = mb_detect_encoding( $key, 'UTF-8,EUC-JP,SJIS,JIS' );
            $key = mb_convert_encoding( $key, $to_encoding, $from_encoding );
            $from_encoding = mb_detect_encoding( $value, 'UTF-8,EUC-JP,SJIS,JIS' );
            $value = mb_convert_encoding( $value, $to_encoding, $from_encoding );
            $ctx->__stash[ 'vars' ][ $key ] = $value;
        }
    } else {
        $ctx->__stash[ 'vars' ] = $ctx->stash[ '__old_vars' ];
        $ctx->restore( $localvars );
    }
    return $content;
}
?>