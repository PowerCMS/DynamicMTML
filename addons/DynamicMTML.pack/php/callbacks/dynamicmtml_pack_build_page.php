<?php
function dynamicmtml_pack_build_page ( $mt, &$ctx, &$args, &$content ) {
    $app = $ctx->stash( 'bootstrapper' );
    // $content = 'New content';
    // $app->stash( 'foo', 'bar' );

/*
// Example 1: Displays highlighting site visitor's search word.
    if ( preg_match( '/(^.*?<body.*?>)(.*?$)/si', $content, $html ) ) {
        $head = $html[1];
        $text = $html[2];
        require_once ( 'dynamicmtml.util.php' );
        $tag_start  = '<strong class="search-word" style="background-color:yellow">';
        $tag_end    = '</strong>';
        $qtag_start = preg_quote( $tag_start, '/' );
        $qtag_end   = preg_quote( $tag_end, '/' );
        $keywords   = array();
        $phrase = referral_search_keyword( $ctx, $keywords );
        foreach ( $keywords as $keyword ) {
            $keyword = htmlspecialchars( $keyword );
            $keyword = trim( $keyword );
            $keyword = preg_quote( $keyword, '/' );
            $pattern1 = "/(<[^>]*>[^<]*?)($keyword)/i";
            $replace1 = '$1' . $tag_start. '$2' . $tag_end;
            $pattern2 = "/($qtag_start)$qtag_start($keyword)$qtag_end($qtag_end)/i";
            $replace2 = '$1$2$3';
            $i = 0;
            while (! $end ) {
                $original = $text;
                $text = preg_replace( $pattern1, $replace1, $text );
                //Nest tag
                $text = preg_replace( $pattern2, $replace2, $text );
                if ( $text == $original ) {
                    $end = 1;
                }
                $i++;
                //Infinite loop
                if ( $i > 20 ) $end = 1;
            }
            unset( $end );
        }
        $content = $head . $text;
    }
*/

/*
    // Example 2: Phone number replace to the link.
    if ( $app->get_agent( 'keitai' ) || $app->get_agent( 'smartphone' ) ) {
        if ( preg_match( '/(^.*?<body.*?>)(.*?$)/si', $content, $html ) ) {
            $head = $html[1];
            $text = $html[2];
            require_once ( 'dynamicmtml.util.php' );
            $tag_1 = '<a href ="tel:';
            $tag_2 = '">';
            $tag_3 = '</a>';
            $pattern1 = '/(<[^>]*>[^<]*?)(0\d{1,4}-\d{1,4}-\d{3,4})/';
            $replace1 = '$1' . $tag_1 . '$2' . $tag_2 . '$2' . $tag_3;
            $pattern2 = '/(<a.*?>\/*)<a.*?>(0\d{1,4}-\d{1,4}-\d{3,4})<\/a>([^<]*?<\/a>)/';
            $replace2 = '$1$2$3';
            $i = 0;
            while (! $end ) {
                $original = $text;
                $text = preg_replace( $pattern1, $replace1, $text );
                //Nest tag
                $text = preg_replace( $pattern2, $replace2, $text );
                if ( $text == $original ) {
                    $end = 1;
                }
                $i++;
                //Infinite loop
                if ( $i > 20 ) $end = 1;
            }
            unset( $end );
            $content = $head . $text;
        }
    }
*/

/*
    // Example 3: Convert to thumbnail for keitai browser.
    require_once ( 'dynamicmtml.util.php' );
    if ( $app->get_agent( 'smartphone' ) ) {
        $agent = $app->get_agent();
        $type = 'auto';
        // if ( $agent == 'DoCoMo' ) {
        //     $type = 'gif';
        // } else {
        //     $type = 'png';
        // }
        $content = convert2thumbnail( $content, $type, 400, 640 );
    }
*/

/*
    // Example 4: Trim White space.
    require_once( 'outputfilter.trimwhitespace.php' );
    $content = smarty_outputfilter_trimwhitespace( $content, $mt );
*/

/*
    // Example 5: Convert to Shift_JIS for keitai browser.
    if ( $app->get_agent( 'keitai' ) ) {
        $charset = strtolower( $ctx->mt->config( 'PublishCharset' ) );
        $charset = preg_replace( '/\-/', '_', $charset );
        if ( $charset != 'shift_jis' ) {
            $pattern = '/<\?xml\s*version\s*=\s*"1.0"\s*encoding\s*=\s*"UTF-8"\s*\?>/s';
            $replace = '<?xml version="1.0" encoding="Shift_JIS"?>';
            $content = preg_replace( $pattern, $replace, $content );
            $pattern = '/<meta\s*http\-equiv\s*=\s*"Content\-Type"\s*content\s*=\s*"text\/html;\s*charset=UTF\-8"\s*\/>/s';
            $replace = '<meta http-equiv="Content-Type" content="text/html; charset=Shift_JIS" />';
            $content = preg_replace( $pattern, $replace, $content );
            $content = mb_convert_encoding( $content, 'SJIS-WIN', 'UTF-8' );
        }
    }
*/

}
?>