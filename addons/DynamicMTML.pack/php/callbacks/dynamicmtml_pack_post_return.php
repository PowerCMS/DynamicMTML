<?php
function dynamicmtml_pack_post_return ( $mt, &$ctx, &$args, &$content ) {
    $app = $ctx->stash( 'bootstrapper' );

/*
    // Example 1: Save access log.
    $url = $app->stash( 'url' );
    if ( $url ) {
        $app->log( $url );
    }
*/

/*
    // Example 2: Save Search Engine's keyword to access log.
    $keyword = referral_serch_keyword( $ctx );
    if ( $keyword ) {
        $keyword = trim( $keyword );
        $url = $app->stash( 'url' );
        $referral_site = referral_site();
        $app->log( "url : $url\nreferral_site : $referral_site\nkeyword : $keyword" );
    }
*/

}
?>