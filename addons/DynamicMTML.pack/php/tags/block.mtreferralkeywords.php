<?php
function smarty_block_mtreferralkeywords( $args, $content, $ctx, &$repeat ) {
    $localvars = array( 'keywords', '_counter', '__max' );
    $glue = $args[ 'glue' ];
    require_once ( 'dynamicmtml.util.php' );
    if (! isset( $content ) ) {
        $ctx->localize( $localvars );
        $counter = 0;
    } else {
        $counter = $ctx->stash( '_counter' );
    }
    $vars = $ctx->stash( 'keywords' );
    if (! isset( $vars ) ) {
        $vars = array();
        $phrase = referral_search_keyword( $ctx, $vars );
        $max = count( $vars );
        $ctx->stash( '__max', $max );
        $ctx->stash( 'keywords', $vars );
    } else {
        $counter = $ctx->stash( '_counter' );
        $max = $ctx->stash( '__max' );
    }
    if ( $counter < $max ) {
        $count = $counter + 1;
        $value = trim( $vars[ $counter ] );
        // if ( ( $glue ) && ( $count != $max ) ) {
        //     $value .= $glue;
        // }
        $ctx->__stash[ 'vars' ][ 'keyword' ] = $value;
        $ctx->__stash[ 'vars' ][ '__counter__' ] = $count;
        $ctx->__stash[ 'vars' ][ '__odd__' ]  = ( $count % 2 ) == 1;
        $ctx->__stash[ 'vars' ][ '__even__' ] = ( $count % 2 ) == 0;
        $ctx->__stash[ 'vars' ][ '__first__' ] = $count == 1;
        $ctx->__stash[ 'vars' ][ '__last__' ] = ( $count == $max );
        $ctx->stash( '_counter', $count );
        $repeat = true;
    } else {
        if ( ( $glue ) && (! empty( $content ) ) ) {
             $content = $glue . $content;
        }
        $ctx->restore( $localvars );
        $repeat = false;
    }
    return $content;
}
?>