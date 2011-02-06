<?php
# DynamicMTML (C) 2010-2011 Alfasado Inc.
# This program is distributed under the terms of the
# GNU General Public License, version 2.

function smarty_modifier_trimwhitespace( $text, $arg ) {
    global $mt;
    require_once( 'outputfilter.trimwhitespace.php' );
    $text = smarty_outputfilter_trimwhitespace( $text, $mt );
    return $text;
}
?>