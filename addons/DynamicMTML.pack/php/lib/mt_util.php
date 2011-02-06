<?php

// Moved from mt.php

function is_valid_email( $addr ) {
    if ( preg_match('/[ |\t|\r|\n]*\"?([^\"]+\"?@[^ <>\t]+\.[^ <>\t][^ <>\t]+)[ |\t|\r|\n]*/', $addr, $matches ) ) {
        return $matches[1];
    } else {
        return 0;
    }
}

$spam_protect_map = array( ':' => '&#58;', '@' => '&#64;', '.' => '&#46;' );

function spam_protect ( $str ) {
    global $spam_protect_map;
    return strtr( $str, $spam_protect_map );
}

function offset_time ( $ts, $blog = NULL, $dir = NULL ) {
    if ( isset( $blog ) ) {
        if (! is_array( $blog ) ) {
            global $mt;
            $blog = $mt->db()->fetch_blog( $blog->id );
        }
        $offset = $blog->blog_server_offset;
    } else {
        global $mt;
        $offset = $mt->config( 'TimeOffset' );
    }
    intval( $offset ) or $offset = 0;
    $tsa = localtime( $ts );
    if ( $tsa[8] ) {  // daylight savings offset
        $offset++;
    }
    if ( $dir == '-' ) {
        $offset *= -1;
    }
    $ts += $offset * 3600;
    return $ts;
}

function translate_phrase_param( $str, $params = NULL ) {
    if ( is_array( $params ) && ( strpos( $str, '[_' ) !== FALSE ) ) {
        for ( $i = 1; $i <= count( $params ); $i++ ) {
            $str = preg_replace( "/\\[_$i\\]/", $params[$i-1], $str );
        }
    }
    return $str;
}

?>