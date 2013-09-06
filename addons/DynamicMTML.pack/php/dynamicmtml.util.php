<?php

//TODO::Move2Core

function get_param ( $param ) {
    global $ctx;
    if ( isset ( $ctx ) ) {
        $app = $ctx->stash( 'bootstrapper' );
        return $app->get_param( $param );
    }
    $qurey = NULL;
    if ( $qurey = $_GET[ $param ] ) {} elseif ( $qurey = $_POST[ $param ] ) {}
    return $qurey;
}

function get_agent ( $wants = 'Agent', $like = NULL, $exclude = NULL ) {
    // Agent Smartphone Keitai Mobile // TODO::Mobile Safari Apple(Mac)
    global $app;
    $agent = $_SERVER[ 'HTTP_USER_AGENT' ];
    if (! $wants ) $wants = 'Agent';
    if ( $like ) {
        $like = preg_quote( $like, '/' );
        if ( preg_match( "/$like/i", $agent ) ) {
            return 1;
        } else {
            return 0;
        }
    }
    $wants = strtolower( $wants );
    $exclude = strtolower( $exclude );
    $smartphone = array (
        'Android'     => 'Android',
        'dream'       => 'Android',
        'CUPCAKE'     => 'Android',
        'blackberry'  => 'BlackBerry',
        'iPhone'      => 'iPhone',
        'iPod'        => 'iPhone',
        'iPad'        => 'iPad',
        'incognito'   => 'Palm',
        'webOS'       => 'Palm',
        'incognito'   => 'iPhone',
        'webmate'     => 'iPhone',
        'Opera\sMini' => 'Opera Mini',
        'Windows\sPhone' => 'Windows Phone',
    );
    foreach ( $smartphone as $key => $val ) {
        $pattern = "/$key/";
        if ( preg_match( $pattern, $agent ) ) {
            if ( $wants == 'agent' ) {
                return $smartphone[ $key ];
            } else {
                if ( $wants != 'keitai' ) {
                    if ( $wants == 'tablet' ) {
                        if ( $smartphone[ $key ] == 'iPad' ) {
                            return 1;
                        } else if ( $smartphone[ $key ] == 'Android' ) {
                            if (! preg_match( "/\sMobile\s/i", $agent ) ) {
                                return 1;
                            } else {
                                return 0;
                            }
                        } else {
                            return 0;
                        }
                    } else { // SmartPhone
                        if ( $exclude == 'tablet' ) {
                            if ( $smartphone[ $key ] == 'iPad' ) {
                                return 0;
                            } else if ( $smartphone[ $key ] == 'Android' ) {
                                if ( preg_match( "/\sMobile\s/i", $agent ) ) {
                                    return 1;
                                } else {
                                    return 0;
                                }
                            } else {
                                return 1;
                            }
                        }
                        return 1;
                    }
                } else {
                    return 0;
                }
            }
        }
    }
    $keitai = array (
        'DoCoMo'      => 'DoCoMo',
        'UP\.Browser' => 'AU',
        'SoftBank'    => 'SoftBank',
        'Vodafone'    => 'SoftBank',
    );
    foreach ( $keitai as $key => $val ) {
        $pattern = "/$key/";
        if ( preg_match( $pattern, $agent ) ) {
            if ( $wants == 'agent' ) {
                return $keitai[ $key ];
            } else {
                if ( $wants == 'smartphone' ) {
                    return 0;
                    exit();
                }
                return 1;
                exit();
            }
        }
    }
    if ( $wants == 'agent' ) {
        return 'PC';
    } else {
        return 0;
    }
}

function path2url ( $input_uri, $site_url, $url = NULL ) {
    if ( preg_match( "!^/!", $input_uri ) ) {
        $site_url = preg_replace( '!(^https*://.*?)/.*$!', '$1', $site_url );
        return $site_url . $input_uri;
    }
    if ( preg_match( '/^https*:/', $input_uri ) ) {
        return $input_uri;
    }
    require_once( 'postfilter.rel2abs.php' );
    if ( $url ) {
        $output_uri = make_abs( $input_uri, $url );
    } else {
        $output_uri = make_abs( $input_uri, $GLOBALS[ 'tpl_absuri' ] );
    }
    if ( $input_uri != $output_uri ) {
        if (! preg_match( '/^https*:/', $output_uri ) ) {
            return $site_url . $output_uri;
        } else {
            return $output_uri;
        }
    }
    return $input_uri;
}

function referral_site () {
    $referer  = strip_tags( $_SERVER[ 'HTTP_REFERER' ] );
    if (! $referer ) return '';
    if ( preg_match ( "!(^https{0,1}://.*?/)!", $referer, $match ) ) {
        return $match[0];
    }
    return '';
}

function referral_search_keyword ( $ctx, &$keywords = array() ) {
    $app = $ctx->stash( 'bootstrapper' );
    $referer  = strip_tags( $_SERVER[ 'HTTP_REFERER' ] );
    $site_url = NULL;
    if ( $blog = $app->blog ) {
        $site_url = $blog->site_url();
    }
    $charset = $app->config( 'PublishCharset' );
    $from    = mb_detect_encoding( $referer, 'UTF-8,EUC-JP,SJIS,JIS' );
    $charset = $ctx->mt->config( 'PublishCharset' );
    $charset or $charset = 'UTF-8';
    $phrase  = '';
    $request = NULL;
    $query   = NULL;
    $params  = NULL;
    $domain  = NULL;
    if ( preg_match( '/\?/', $referer ) ) {
        list ( $request, $query ) = explode( '?', $referer );
        parse_str( $query, $params );
    }
    if ( $params ) {
        if ( /**/FALSE &&/**/ $site_url ) {
            $site_url = preg_quote( $site_url, '/' );
            if ( preg_match ( "/^$site_url/", $request ) ) {
                $phrase = $params[ 'query' ];
            }
        }
        if ( preg_match ( "!^https{0,1}://(.*?)/!", $request, $domain ) ) {
            $domain = $domain[1];
        }
        if ( ( strpos( $domain, '.google.' ) )
            || ( strpos( $domain, '.bing.' ) )
            || ( strpos( $domain, '.msn.' ) ) ) {
            $phrase = $params[ 'q' ];
        } elseif ( strpos( $domain, '.yahoo.' ) ) {
            $phrase = $params[ 'p' ];
        } elseif ( strpos( $domain, '.goo.' ) ) {
            $phrase = $params[ 'MT' ];
        }
        if ( $phrase ) {
            $phrase = urldecode( $phrase );
            $phrase = mb_convert_encoding( $phrase, $charset, $from );
            $phrase = mb_convert_kana( $phrase, "s" );
            $phrase = trim( $phrase );
            $phrase = preg_replace( '/\s{1,}/', ' ', $phrase );
        }
    }
    if (! $phrase ) {
        $phrase = $params[ 'search' ];
    }
    if ( $phrase ) {
        $keywords = preg_split ( '/\s/', $phrase );
    }
    return $phrase;
}

function referral_serch_keyword ( $ctx, &$keywords = array() ) {
    // compatible
    return referral_search_keyword( $ctx, $keywords );
}

function regex_not_tag ( $phrase ) {
    $pattern = '/(<[^>]*>[^<]*?)(' . $phrase . ')/';
    return $pattern;
}

function make_seo_basename ( $phrase, $length = NULL ) {
    $invalid  = preg_quote( '\'"|*`^><)(}{][,/! ', '/' );
    $phrase = preg_replace( "/[$invalid]/", '_', $phrase );
    $phrase = trim( $phrase, '_' );
    if ( $length ) {
        if ( extension_loaded( 'mbstring' ) ) {
            $phrase = mb_substr( $phrase, 0, $length );
        }
    }
    $phrase = urlencode( $phrase );
    return $phrase;
}

function __get_next_year ( $ts ) {
    $y = substr( $ts, 0, 4 );
    $y++;
    return $y . "0101000000";
}

function __get_previous_year ( $ts ) {
    $y = substr( $ts, 0, 4 );
    $y--;
    return $y . "0101000000";
}

function __get_next_month ( $ts ) {
    $y = substr( $ts, 0, 4 );
    $mo = substr( $ts, 4, 2 );
    if ( $mo == '12' ) {
        $y++;
        $mo = '1';
    } else {
        $mo++;
    }
    return sprintf( "%04d%02d01000000", $y, $mo );
}

function __get_previous_month ( $ts ) {
    $y = substr( $ts, 0, 4 );
    $mo = substr( $ts, 4, 2 );
    if ( $mo == '01' ) {
        $y--;
        $mo = '12';
    } else {
        $mo--;
    }
    return sprintf( "%04d%02d01000000", $y, $mo );
}

function __get_next_week ( $ts ) {
    require_once( 'MTUtil.php' );
    $ts = start_end_week( $ts );
    $ts = $ts[0];
    $epoch = datetime_to_timestamp( $ts );
    $epoch += 86400 * 7;
    $ts = date( 'YmdHis', $epoch );
    return $ts;
}

function __get_previous_week ( $ts ) {
    require_once( 'MTUtil.php' );
    $ts = start_end_week( $ts );
    $ts = $ts[0];
    $epoch = datetime_to_timestamp( $ts );
    $epoch -= 86400 * 7;
    $ts = date( 'YmdHis', $epoch );
    return $ts;
}

function __get_next_day ( $ts ) {
    require_once( 'MTUtil.php' );
    $ts = start_end_day( $ts );
    $ts = $ts[0];
    $epoch = datetime_to_timestamp( $ts );
    $epoch += 86400;
    $ts = date( 'YmdHis', $epoch );
    return $ts;
}

function __get_previous_day ( $ts ) {
    require_once( 'MTUtil.php' );
    $ts = start_end_day( $ts );
    $ts = $ts[0];
    $epoch = datetime_to_timestamp( $ts );
    $epoch -= 86400;
    $ts = date( 'YmdHis', $epoch );
    return $ts;
}

function __date2ts ( $ts ) {
    $ts = preg_replace( '/[^0-9]/', '', $ts );
    if ( strlen( $ts ) == 14 ) {
        return $ts;
    } else {
        $add = 14 - strlen( $ts );
        while ( $add > 0 ) {
            $ts .= '0';
            $add--;
        }
        return $ts;
    }
}

function __umask2permission ( $umask ) {
    $umask = intval( $umask );
    $umask = sprintf( "%03d", $umask );
    if ( preg_match( '/^[0-9]{3}$/', $umask ) ) {
        $umask1 = substr( $umask, 0, 1 );
        $umask2 = substr( $umask, 1, 1 );
        $umask3 = substr( $umask, 2, 1 );
        $umask1 = 7 - $umask1;
        $umask2 = 7 - $umask2;
        $umask3 = 7 - $umask3;
        $umask = $umask1 . $umask2 . $umask3;
        $umask = sprintf( "%04d", $umask );
        return intval( $umask );
    }
    return 666;
}

function __is_hash ( &$array ) {
    if (! is_array( $array ) ) {
        return 0;
    }
    reset( $array );
    list( $k ) = each( $array );
    return $k !== 0;
}

function __cat_file ( $dir, $path = NULL ) {
    if (! is_array( $dir ) ) {
        $dir = rtrim( $dir, DIRECTORY_SEPARATOR );
    } else {
        $directory = '';
        foreach ( $dir as $item ) {
            if ( $directory ) $directory .= DIRECTORY_SEPARATOR;
            $directory .= $item;
        }
        $dir = $directory;
    }
    if ( isset( $path ) ) {
        if (! is_array( $path ) ) {
            $path = rtrim( $path, DIRECTORY_SEPARATOR );
            return $dir . DIRECTORY_SEPARATOR . $path;
        } else {
            foreach ( $path as $item ) {
                $dir .= DIRECTORY_SEPARATOR . $item;
            }
        }
    }
    return $dir;
}

function __cat_dir ( $dir, $path = NULL ) {
    return __cat_file( $dir, $path );
}

function get_fileinfo_from_ctx ( $ctx ) {
    $app = $ctx->stash( 'bootstrapper' );
    if ( $app ) {
        $data = $app->stash( 'fileinfo' );
        if ( isset( $data ) ) {
            return $data;
        }
    }
    if ( $data = $ctx->stash( 'fileinfo' ) ) {
        return $data;
    }
    $path = NULL;
    if ( !$path && $_SERVER[ 'REQUEST_URI' ] ) {
        $path = $_SERVER[ 'REQUEST_URI' ];
        // strip off any query string...
        $path = preg_replace( '/\?.*/', '', $path );
        // strip any duplicated slashes...
        $path = preg_replace( '!/+!', '/', $path );
    }
    if ( preg_match( '/IIS/', $_SERVER[ 'SERVER_SOFTWARE' ] ) ) {
        if ( preg_match( '/^\d+;( .* )$/', $_SERVER[ 'QUERY_STRING' ], $matches ) ) {
            $path = $matches[1];
            $path = preg_replace( '!^http://[^/]+!', '', $path );
            if ( preg_match( '/\?( .+ )?/', $path, $matches ) ) {
                $_SERVER[ 'QUERY_STRING' ] = $matches[1];
                $path = preg_replace( '/\?.*$/', '', $path );
            }
        }
    }
    $path = preg_replace( '/\\\\/', '\\\\\\\\', $path );
    $pathinfo = pathinfo( $path );
    $ctx->stash( '_basename', $pathinfo[ 'filename' ] );
    if ( isset( $_SERVER[ 'REDIRECT_QUERY_STRING' ] ) ) {
        $_SERVER[ 'QUERY_STRING' ] = getenv( 'REDIRECT_QUERY_STRING' );
    }
    if ( preg_match( '/\.( \w+ )$/', $path, $matches ) ) {
        $req_ext = strtolower( $matches[1] );
    }
    $data = $ctx->mt->resolve_url( $path );
    if ( $app ) {
        $app->stash( 'fileinfo', $data );
    }
    $ctx->stash( 'fileinfo', $data );
    return $data;
}

function convert2thumbnail ( $text, $type = 'auto', $embed_px,
                             $link_px = NULL, $dimension = 'width' ) {
    global $app;
    $ctx = $app->ctx;
    $url = $app->url;
    $agent = get_agent();
    $keitai = get_agent( 'keitai' );
    $test = $text;
    $blog = $ctx->stash( 'blog' );
    $separator = DIRECTORY_SEPARATOR;
    $site_path = $blog->archive_path();
    $site_path = rtrim( $site_path, $separator );
    if (! $site_path || $site_path === $separator ) {
        $site_path = $blog->site_path();
    }
    $site_path = rtrim( $site_path, $separator );
    $site_url  = $blog->site_url();
    $site_url  = preg_replace( '{/+$}', '', $site_url );
    $search_path = quotemeta( $site_url );
    preg_match_all( '/<img(?:[ \t\n\r][^>]*)?>/i', $text, $match );
    //require_once 'thumbnail_lib.php';
    require_once 'thumbnail_lib_alt.php';
    set_error_handler( 'convertthumbnail_error' );
    $match = $match[0];
    foreach ( $match as $tag ) {
        $embed = $embed_px;
        $link = $link_px;
        if ( preg_match( '/src\s*=\s*"(.*?)"/is', $tag, $src ) ) {
            //Create thumbnail and replace path;
            $path = $src[1];
            $path = path2url( $path, $site_url, $url );
            $pinfo = pathinfo( $path );
            $extension = $pinfo[ 'extension' ];
            $extension = strtolower( $extension );
            $src = $path;
            $scope = $dimension;
            if ( $dimension == 'auto' ) {
                preg_match( "/width\s*=\"(.*?)\"/is", $tag, $width );
                $width = $width[1];
                preg_match( "/height\s*=\"(.*?)\"/is", $tag, $height );
                $height = $height[1];
                if ( $width < $height ) {
                    $scope = 'height';
                } else {
                    $scope = 'width';
                }
            }
            preg_match( "/$scope\s*=\"(.*?)\"/is", $tag, $size );
            $size = $size[1];
            //if ( $embed >= $size ) { continue; }
            if ( $keitai ) {
                if ( $size <= $embed ) {
                    $embed = $size;
                    $link = NULL;
                }
            } else {
                if ( $embed >= $size ) { continue; }
            }
            if ( preg_match( "!^$search_path!", $path ) ) {
                $path = preg_replace( "!^$search_path!", '', $path );
                $path = preg_replace( "!/!", DIRECTORY_SEPARATOR, $path );
                $path = $site_path . $path;
                if (! file_exists( $path ) ) { continue; }
                $mtime = filemtime( $path );
                $pinfo = pathinfo( $path );
                $extension = $pinfo[ 'extension' ];
                $extension = strtolower( $extension );
                $new_ext = $extension;
                $format = $type;
                if ( isset( $type ) && ( $type != 'auto' ) ) {
                    $new_ext = $type;
                } else {
                    // auto
                    $contenttype = $app->get_mime_type( $extension );
                    if ( ( $contenttype != 'image/jpeg' ) && ( $keitai ) ) {
                        if ( $agent == 'DoCoMo' ) {
                            $format  = 'gif';
                            $new_ext = 'gif';
                        } else {
                            $format  = 'png';
                            $new_ext = 'png';
                        }
                    }
                }
                // Thumbnail
                //$thumb = new Thumbnail();
                $thumb = new Thumbnail_alt();
                //$thumb->type( $format );
                $thumb->$scope( $embed );
                $thumb->src_file( $path );
                $dest_file = preg_replace( "/\.$extension$/", '-thumb-' . $embed . 'x.' . $new_ext, $path );
                $f_path = preg_replace( "/\.$extension$/", '-thumb-' . $embed . 'x.' . $new_ext, $src );
                $thumb->dest_file( $dest_file );
                if ( file_exists( $dest_file ) ) {
                    $t_time = filemtime( $dest_file );
                    if ( $mtime > $t_time ) {
                        unlink ( $dest_file );
                    }
                }
                $args = array ( 'format' => $format,
                                'dest_type' => $format );
                $thumb->get_thumbnail( $args );
                $thumb_w = $thumb->width();
                $thumb_h = $thumb->height();
                $thumb_path = $f_path;
                $new_tag = preg_replace( '/(src\s*=").*?(")/is', "src=\"$thumb_path\"", $tag );
                $new_tag = preg_replace( '/(width\s*=").*?(")/is',  "width=\"$thumb_w\"", $new_tag );
                $new_tag = preg_replace( '/(height\s*=").*?(")/is', "height=\"$thumb_h\"", $new_tag );
                $no_link = $new_tag;
                if ( isset ( $link ) ) {
                    $link_path = NULL;
                    if ( ( $link >= $size ) && ( $extension == $new_ext ) ) {
                        $link_path = $src;
                    } else {
                        if ( $link >= $size ) {
                            $link = $size;
                        }
                        //$thumb = new Thumbnail();
                        $thumb = new Thumbnail_alt();
                        //$thumb->type( $format );
                        $thumb->$scope( $link );
                        $thumb->src_file( $path );
                        $dest_file = preg_replace( "/\.$extension$/", '-thumb-' . $link . 'x.' . $new_ext, $path );
                        $f_path = preg_replace( "/\.$extension$/", '-thumb-' . $link . 'x.' . $new_ext, $src );
                        $thumb->dest_file( $dest_file );
                        if ( file_exists( $dest_file ) ) {
                            $t_time = filemtime( $dest_file );
                            if ( $mtime > $t_time ) {
                                unlink ( $dest_file );
                            }
                        }
                        $thumb->get_thumbnail( $args );
                        $link_w = $thumb->width();
                        $link_h = $thumb->height();
                        $link_path = $f_path;
                    }
                    $new_tag = '<a href="' . $link_path . '">' . $new_tag . '</a>';
                }
                $tag  = preg_quote( $tag, '/' );
                $test = preg_replace( "/$tag/", $new_tag, $test, 1 );
                $is_link = NULL;
                if ( isset ( $link ) ) {
                    $check = preg_quote( $new_tag, '/' );
                    preg_match_all( '/<a(>| ).*?<\/a>/i', $test, $atag );
                    $atag = $atag[0];
                    foreach ( $atag as $anchor ) {
                        if ( preg_match( "/(^.*)$check/si", $anchor, $pre ) ) {
                            if ( preg_match( "/(<a.*?$)/si", $pre[1], $p_tag ) ) {
                                if (! preg_match ( "/<\/a>/", $p_tag[1] ) ) {
                                    preg_match ( '/href\s*=\s*"(.*?)"/si', $p_tag[1], $resource );
                                    $is_image;
                                    if ( isset( $resource[1] ) ) {
                                        $extension = strtolower( pathinfo( $resource[1], PATHINFO_EXTENSION ) );
                                        if ( $extension ) {
                                            $file_type = $app->get_mime_type( $extension );
                                            $file_type = explode( '/', $file_type );
                                            if ( $file_type[0] == 'image' ) {
                                                $is_image = 1;
                                            }
                                        }
                                    }
                                    if ( $is_image ) {
                                        preg_match ( '/<a.*?>/', $pre[1], $search );
                                        $search = preg_quote( $search[0], '/' );
                                        $text = preg_replace( "/$search(.*?$tag.*?)<\/a>/si", '$1', $text, 1 );
                                    } else {
                                        $new_tag = $no_link;
                                    }
                                    $anchor = preg_quote( $anchor, '/' );
                                    $test = preg_replace( "/$anchor/", '', $test, 1 );
                                    break 1;
                                }
                            }
                        }
                    }
                }
                $text = preg_replace( "!$tag!", $new_tag, $text, 1 );
            }
        }
    }
    global $mt;
    if ( isset ( $mt ) ) {
        set_error_handler( array( &$mt, 'error_handler' ) );
    }
    return $text;
}

function convertthumbnail_error() {}

?>