<?php
function smarty_function_mtmtml ( $args, &$ctx ) {
    require_once( 'function.mtml.php' );
    return smarty_function_mtml( $args, $ctx );
}
?>