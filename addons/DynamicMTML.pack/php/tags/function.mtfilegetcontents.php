<?php
function smarty_function_mtfilegetcontents ( $args, &$ctx ) {
    $url = $args[ 'url' ];
    if ( preg_match( '!^https{0,1}://!', $url ) ) {
        $to_encoding = $ctx->mt->config( 'PublishCharset' );
        $to_encoding or $to_encoding = 'UTF-8';
        ini_set( 'user_agent', 'User-Agent: Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)');
        if ( $contents = file_get_contents( $url ) ) {
            $from_encoding = mb_detect_encoding( $contents, 'UTF-8,EUC-JP,SJIS,JIS' );
            return mb_convert_encoding( $contents, $to_encoding, $from_encoding );
        }
    }
}
?>