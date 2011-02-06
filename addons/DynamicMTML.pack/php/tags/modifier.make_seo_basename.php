<?php
# DynamicMTML (C) 2010-2011 Alfasado Inc.
# This program is distributed under the terms of the
# GNU General Public License, version 2.

function smarty_modifier_make_seo_basename( $text, $arg ) {
    require_once ( 'dynamicmtml.util.php' );
    return make_seo_basename( $text, $arg );
}
?>