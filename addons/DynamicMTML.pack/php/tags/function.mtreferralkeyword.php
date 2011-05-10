<?php
function smarty_function_mtreferralkeyword ( $args, $ctx ) {
    require_once ( 'dynamicmtml.util.php' );
    return trim ( referral_search_keyword( $ctx ) );
}
?>