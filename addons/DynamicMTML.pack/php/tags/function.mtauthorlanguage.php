<?php
# DynamicMTML (C) 2010-2011 Alfasado Inc.
# This program is distributed under the terms of the
# GNU General Public License, version 2.

function smarty_function_mtauthorlanguage ( $args, &$ctx ) {
    $app = $ctx->stash( 'bootstrapper' );
    $user = $app->user();
    $language = $app->config( 'DefaultLanguage' );
    if ( $user ) {
        $language = $user->preferred_language;
        $language = preg_replace( '/\-/', '_', $language );
    }
    if ( $language == 'en_us' ) {
        $language = 'en';
    }
    return $language;
}
?>