<?php
function smarty_function_mtrand( $args, &$ctx ) {
    $min = $args[ 'min' ];
    $max = $args[ 'max' ];
    return rand( $min, $max );
}
?>