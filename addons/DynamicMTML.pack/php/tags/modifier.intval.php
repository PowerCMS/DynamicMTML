<?php
# DynamicMTML (C) 2010-2011 Alfasado Inc.
# This program is distributed under the terms of the
# GNU General Public License, version 2.

function smarty_modifier_intval( $text, $arg ) {
    $val = intval( trim( $text ) );
    if (! $val ) $val = 0;
    return $val;
}
?>