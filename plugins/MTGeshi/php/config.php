<?php
# DynamicMTML (C) 2010-2011 Alfasado Inc.
# This program is distributed under the terms of the
# GNU General Public License, version 2.

class MTGeshi extends MTPlugin {
    function filter_geshi ( $text, $arg ) {
        if ( $arg == '1' ) $arg = 'Perl';
        $lib = __cat_file( array( dirname( __FILE__ ), 'extlib', 'geshi', 'geshi.php' ) );
        require_once( $lib );
        $geshi = new GeSHi( $text, $arg );
        return $geshi->parse_code();
    }
}
?>