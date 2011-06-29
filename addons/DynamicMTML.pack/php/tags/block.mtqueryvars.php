<?php
function smarty_block_mtqueryvars( $args, $content, &$ctx, &$repeat ) {
    $app = $ctx->stash( 'bootstrapper' );
    $localvars = array( 'query_params', '__mtqueryvars_max', '__mtqueryvars_old_vars' );
    $to_encoding = $ctx->mt->config( 'PublishCharset' );
    $to_encoding or $to_encoding = 'UTF-8';
    $glue = $args[ 'glue' ];
    if (! isset( $content ) ) {
        $ctx->stash( '__mtqueryvars_old_vars', $ctx->__stash[ 'vars' ] );
        $ctx->localize( $localvars );
        $counter = 0;
        $ctx->__stash[ 'vars' ][ '__counter__' ] = 0;
    } else {
        // $counter = $ctx->stash( '_counter' );
        $counter = $ctx->__stash[ 'vars' ][ '__counter__' ];
    }
    $vars = $ctx->stash( 'query_params' );
    if (! isset( $vars ) ) {
        $params = $app->param();
        $vars = array();
        $i = 0;
        foreach ( $params as $key => $val ) {
            $vars[ $i ] = array( $key, $val );
            $i++;
        }
        $max = count( $vars );
        $ctx->stash( '__mtqueryvars_max', $max );
        $ctx->stash( 'query_params', $vars );
        $ctx->__stash[ 'vars' ][ '__counter__' ] = 0;
    } else {
        // $counter = $ctx->stash( '_counter' );
        $counter = $ctx->__stash[ 'vars' ][ '__counter__' ];
        $max = $ctx->stash( '__mtqueryvars_max' );
    }
    if ( $counter < $max ) {
        $count = $counter + 1;
        $value = $vars[ $counter ];
        $key = $value[0];
        $value = $value[1];
        if ( is_string( $value ) ) {
            $from_encoding = mb_detect_encoding( $value, 'UTF-8,EUC-JP,SJIS,JIS' );
            $value = mb_convert_encoding( $value, $to_encoding, $from_encoding );
        }
        $ctx->__stash[ 'vars' ][ 'key' ] = $key;
        $ctx->__stash[ 'vars' ][ 'value' ] = $value;
        $ctx->__stash[ 'vars' ][ '__counter__' ] = $count;
        $ctx->__stash[ 'vars' ][ '__odd__' ]  = ( $count % 2 ) == 1;
        $ctx->__stash[ 'vars' ][ '__even__' ] = ( $count % 2 ) == 0;
        $ctx->__stash[ 'vars' ][ '__first__' ] = $count == 1;
        $ctx->__stash[ 'vars' ][ '__last__' ] = ( $count == $max );
        $repeat = TRUE;
    } else {
        // if ( ( $glue ) && (! empty( $content ) ) ) {
        //      $content = $glue . $content;
        // }
        $ctx->__stash[ 'vars' ] = $ctx->stash( '__mtqueryvars_old_vars' );
        $ctx->restore( $localvars );
        $repeat = FALSE;
    }
    if ( ( $counter > 1 ) && $glue && (! empty( $content ) ) ) {
         $content = $glue . $content;
    }
    return $content;
}
?>