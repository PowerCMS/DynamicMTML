<?php
function smarty_block_mtsplitvars( $args, $content, &$ctx, &$repeat ) {
    $localvars = array( 'split_vars', '__mtsplit_vars_max', '__mtsplit_vars_old_vars', '__mtsplit_vars_counter' );
    $name = $args[ 'name' ];
    $delimiter = $args[ 'delimiter' ];
    $text = $args[ 'text' ];
    if (! $name ) {
        $name = 'value';
    }
    if (! $delimiter ) {
        $delimiter = ',';
    }
    $delimiter = preg_quote( $delimiter, '/' );
    $glue = $args[ 'glue' ];
    if (! isset( $content ) ) {
        $ctx->localize( $localvars );
        $ctx->stash( '__mtsplit_vars_old_vars', $ctx->__stash[ 'vars' ] );
        $counter = 0;
    } else {
        $counter = $ctx->stash( '__mtsplit_vars_counter' );
    }
    $vars = $ctx->stash( 'split_vars' );
    if (! isset( $vars ) ) {
        $vars = preg_split( "/$delimiter/", $text );
        $max = count( $vars );
        $ctx->stash( '__mtsplit_vars_max', $max );
        $ctx->stash( 'split_vars', $vars );
    } else {
        $counter = $ctx->stash( '__mtsplit_vars_counter' );
        $max = $ctx->stash( '__mtsplit_vars_max' );
    }
    if ( $counter < $max ) {
        $count = $counter + 1;
        $value = $vars[ $counter ];
        $ctx->__stash[ 'vars' ][ $name ] = $value;
        $ctx->__stash[ 'vars' ][ '__counter__' ] = $count;
        $ctx->__stash[ 'vars' ][ '__odd__' ]  = ( $count % 2 ) == 1;
        $ctx->__stash[ 'vars' ][ '__even__' ] = ( $count % 2 ) == 0;
        $ctx->__stash[ 'vars' ][ '__first__' ] = $count == 1;
        $ctx->__stash[ 'vars' ][ '__last__' ] = ( $count == $max );
        $ctx->stash( '__mtsplit_vars_counter', $count );
        if ( $content && $glue ) {
            $content .= $glue;
        }
        $repeat = TRUE;
    } else {
        $ctx->__stash[ 'vars' ] = $ctx->stash( '__mtsplit_vars_old_vars' );
        $ctx->restore( $localvars );
        $repeat = FALSE;
    }
    return $content;
}
?>