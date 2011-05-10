<?php
function smarty_function_mttablecolumnvalue ( $args, &$ctx ) {
    $app = $ctx->stash( 'bootstrapper' );
    $blog = $ctx->stash( 'blog' );
    $stash = $args[ 'stash' ];
    $model = $args[ 'class' ];
    if (! $model ) $model = $stash;
    if (! $model ) return '';
    $column = $args[ 'column' ];
    if (! $column ) return '';
    if (! $model == 'author' ) return '';
    if ( preg_match( '/password/', $column ) ) {
        return '';
    }
    $obj = $app->model( $model );
    if ( $obj ) {
        if ( $value = $obj->has_column( $column ) ) {
            return $value;
        }
    }
    return '';
}
?>