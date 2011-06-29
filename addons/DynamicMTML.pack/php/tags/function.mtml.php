<?php
function smarty_function_mtml ( $args, &$ctx ) {
    $tag = $args[ 'tag' ];
    $params = $args[ 'params' ];
    $tag = trim( $tag );
    if ( $params ) {
        $tag = "<$tag $params>";
    } else {
        $tag = "<$tag>";
    }
    return $tag;
}
?>