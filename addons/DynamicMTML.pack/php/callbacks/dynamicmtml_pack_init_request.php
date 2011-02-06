<?php
# DynamicMTML (C) 2010-2011 Alfasado Inc.
# This program is distributed under the terms of the
# GNU General Public License, version 2.

function dynamicmtml_pack_init_request () {
    global $app;

/*
    // Example1: Required login. using Basic Auth
    if ( isset( $_SERVER[ 'PHP_AUTH_USER' ] ) && ( $_SERVER[ 'PHP_AUTH_USER' ]
            === 'username' && $_SERVER[ 'PHP_AUTH_PW' ]
            === 'password' ) ) {
    } else {
        header( 'WWW-Authenticate: Basic realm=""' );
        header( 'HTTP/1.0 401 Unauthorized' );
        exit();
    }
*/

/*
    // Example2: Use Perl builder.
    global $mt_dir;
    global $app;
    $perlbuilder = $mt_dir . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . 'rebuild-from-fi';
    if ( file_exists( $perlbuilder ) ) {
        $app->stash( 'perlbuild', 1 );
    }
    $perlbuilder = $mt_dir . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . 'build-template-file';
    if ( file_exists( $perlbuilder ) ) {
        $app->stash( 'perlbuild', 1 );
    }
*/
}
?>