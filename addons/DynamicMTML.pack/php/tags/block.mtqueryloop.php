<?php
function smarty_block_mtqueryloop( $args, $content, &$ctx, &$repeat ) {
    $localvars = array( 'query_params', '__max', '__mtqueryloop_old_vars' );
    $to_encoding = $ctx->mt->config( 'PublishCharset' );
    $to_encoding or $to_encoding = 'UTF-8';
    $key = $args[ 'key' ];
    $glue = $args[ 'glue' ];
    if (! isset( $content ) ) {
        $ctx->localize( $localvars );
        // $ctx->stash( '__mtqueryloop_old_vars', $ctx->__stash[ 'vars' ] );
        $counter = 0;
        $ctx->__stash[ 'vars' ][ '__counter__' ] = 0;
    } else {
        $counter = $ctx->__stash[ 'vars' ][ '__counter__' ];
    }
    $vars = $ctx->stash( 'query_params' );
    if (! isset( $vars ) ) {
        $vars = $_REQUEST[ $key ];
        $max = count( $vars );
        $ctx->stash( '__max', $max );
        $ctx->stash( 'query_params', $vars );
    } else {
        $counter = $ctx->__stash[ 'vars' ][ '__counter__' ];
        $max = $ctx->stash( '__max' );
    }
    if ( $counter < $max ) {
        $count = $counter + 1;
        $value = $vars[ $counter ];
        $from_encoding = mb_detect_encoding( $value, 'UTF-8,EUC-JP,SJIS,JIS' );
        $value = mb_convert_encoding( $value, $to_encoding, $from_encoding );
        $ctx->__stash[ 'vars' ][ $key ] = $value;
        $ctx->__stash[ 'vars' ][ '__key__' ] = $key;
        $ctx->__stash[ 'vars' ][ '__value__' ] = $value;
        $ctx->__stash[ 'vars' ][ '__counter__' ] = $count;
        $ctx->__stash[ 'vars' ][ '__odd__' ]  = ( $count % 2 ) == 1;
        $ctx->__stash[ 'vars' ][ '__even__' ] = ( $count % 2 ) == 0;
        $ctx->__stash[ 'vars' ][ '__first__' ] = $count == 1;
        $ctx->__stash[ 'vars' ][ '__last__' ] = ( $count == $max );
        $repeat = true;
    } else {
        // if ( ( $glue ) && (! empty( $content ) ) ) {
        //      $content = $glue . $content;
        // }
        // $ctx->__stash[ 'vars' ] = $ctx->stash( '__mtqueryloop_old_vars' );
        $ctx->restore( $localvars );
        $repeat = false;
    }
    if ( ( $counter > 1 ) && $glue && (! empty( $content ) ) ) {
         $content = $glue . $content;
    }
    return $content;
}
?>