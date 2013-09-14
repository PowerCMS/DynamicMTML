<?php

// Moved from mt.php

function is_valid_email($addr) {
    if (preg_match('/[ |\t|\r|\n]*\"?([^\"]+\"?@[^ <>\t]+\.[^ <>\t][^ <>\t]+)[ |\t|\r|\n]*/', $addr, $matches)) {
        return $matches[1];
    } else {
        return 0;
    }
}

$spam_protect_map = array(':' => '&#58;', '@' => '&#64;', '.' => '&#46;');
function spam_protect($str) {
    global $spam_protect_map;
    return strtr($str, $spam_protect_map);
}

function offset_time($ts, $blog = null, $dir = null) {
    if (isset($blog)) {
        if (!is_array($blog)) {
            global $mt;
            $blog = $mt->db()->fetch_blog($blog->id);
        }
        $offset = $blog->blog_server_offset;
    } else {
        global $mt;
        $offset = $mt->config('TimeOffset');
    }
    intval($offset) or $offset = 0;
    $tsa = localtime($ts);

    if ($tsa[8]) {  // daylight savings offset
        $offset++;
    }
    if ($dir == '-') {
        $offset *= -1;
    }
    $ts += $offset * 3600;
    return $ts;
}

function translate_phrase_param($str, $params = null) {
    if (is_array($params)) {
        if (strpos($str, '[_') !== false) {
            for ($i = 1; $i <= count($params); $i++) {
                $str = preg_replace("/\\[_$i\\]/", $params[$i-1], $str);
            }
        }
        $start = 0;
        while (preg_match("/\\[quant,_(\d+),([^\\],]*)(?:,([^\\],]*))?(?:,([^\\],]*))?\\]/", $str, $matches, PREG_OFFSET_CAPTURE, $start)) {
            $id = $matches[1][0];
            $num = $params[$id-1];
            if ( ($num === 0) && (count($matches) > 4) ) { 
                $part = $matches[4][0];
            } 
            elseif ( $num === 1 ) {
                $part = $num . ' ' . $matches[2][0];
            }
            else {
                $part = $num . ' ' . ( count($matches) > 3 ? $matches[3][0] : ( $matches[2][0] . 's' ) );
            }
            $str = substr_replace($str, $part, $matches[0][1], strlen($matches[0][0]));
            $start = $matches[0][1] + strlen($part);
        }
    }
    return $str;
}

?>