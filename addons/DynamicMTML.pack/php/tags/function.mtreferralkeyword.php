<?php
# DynamicMTML (C) 2010-2011 Alfasado Inc.
# This program is distributed under the terms of the
# GNU General Public License, version 2.

function smarty_function_mtreferralkeyword ( $args, $ctx ) {
    require_once ( 'dynamicmtml.util.php' );
    return trim ( referral_search_keyword( $ctx ) );
}
?>