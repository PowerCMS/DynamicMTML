<?php
function smarty_modifier_highlightingsearchword( $text, $arg ) {
    global $mt;
    $ctx = $mt->context();
    $text = "<span>$text</span>";
    $class = $arg;
    if ( preg_match( "/^[0-9]+$/", $class ) ) {
        $class = 'search-word';
    }
    require_once ( 'dynamicmtml.util.php' );
    $tag_start  = "<strong class=\"$class\">";
    $tag_end    = '</strong>';
    $qtag_start = preg_quote( $tag_start, '/' );
    $qtag_end   = preg_quote( $tag_end, '/' );
    $keywords   = array();
    $phrase = referral_serch_keyword( $ctx, $keywords );
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
    $text = preg_replace( '/^<span>/s', '', $text );
    $text = preg_replace( '/<\/span>$/s', '', $text );
    return $text;
}
?>