<?php
    $plugin_path = dirname( __File__ ) . DIRECTORY_SEPARATOR;
    require_once( $plugin_path . 'dynamicmtml.util.php' );
    require_once( $plugin_path . 'dynamicmtml.php' );
    if (! isset( $mt_dir ) ) $mt_dir = dirname( dirname( dirname( $plugin_path ) ) );
    require_once( $mt_dir . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'MTUtil.php' );
    if (! isset( $mt_config ) ) $mt_config = $mt_dir . DIRECTORY_SEPARATOR . 'mt-config.cgi';
    global $mt;
    global $ctx;
    $ctx = NULL;
    $app = new DynamicMTML();
    $app->configure( $mt_config );
    $no_database = FALSE;
    $dynamic_config = $app->config;
    if (! $app->config( 'Database' ) || (! isset( $blog_id ) ) ) {
        $no_database = TRUE;
        $app->stash( 'no_database', 1 );
        // require_once( $mt_dir . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'dynamic_mt.php' );
        require_once( $plugin_path . 'mt.php' );
        $mt = new MT();
    }
    $include_static   = $app->config( 'DynamicIncludeStatic' );
    $dynamicphpfirst  = $app->config( 'DynamicPHPFirst' );
    $allow_magicquote = $app->config( 'AllowMagicQuotesGPC' );
    if (! $allow_magicquote ) {
        if ( get_magic_quotes_gpc() ) {
            function strip_magic_quotes_slashes ( $arr ) {
                return is_array( $arr ) ?
                array_map( 'strip_magic_quotes_slashes', $arr ) :
                stripslashes( $arr );
            }
            $_GET = strip_magic_quotes_slashes( $_GET );
            $_POST = strip_magic_quotes_slashes( $_POST );
            $_REQUEST = strip_magic_quotes_slashes( $_REQUEST );
            $_COOKIE = strip_magic_quotes_slashes( $_COOKIE );
        }
    }
    if ( isset( $_SERVER[ 'REDIRECT_STATUS' ] ) ) {
        $status = $_SERVER[ 'REDIRECT_STATUS' ];
        if ( ( $status == 403 ) || ( $status == 404 ) ) {
            if ( isset( $_SERVER[ 'REDIRECT_QUERY_STRING' ] ) ) {
                if (! $_GET ) {
                    parse_str( $_SERVER[ 'REDIRECT_QUERY_STRING' ], $_GET );
                }
            }
            if (! $_POST ) {
                if ( $params = file_get_contents( "php://input" ) ) {
                    parse_str( $params, $_POST );
                }
            }
            $app->request_method = $_SERVER[ 'REDIRECT_REQUEST_METHOD' ];
            $app->mod_rewrite = 0;
        } else {
            $app->request_method = $_SERVER[ 'REQUEST_METHOD' ];
            $app->mod_rewrite = 1;
        }
    } else {
        $app->mod_rewrite = 1;
    }
    $app->run_callbacks( 'init_app' );
    $secure = !empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off'
              /* || isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443 */
            ? 's' : '';
    $base   = "http{$secure}://{$_SERVER[ 'HTTP_HOST' ]}";
    $port   = (int) $_SERVER[ 'SERVER_PORT' ];
    if (! empty( $port ) && $port !== ( $secure === '' ? 80 : 443 ) ) $base .= ":$port";
    $request_uri = NULL;
    if ( isset( $_SERVER[ 'HTTP_X_REWRITE_URL' ] ) ) {
        // IIS with ISAPI_Rewrite
        $request_uri  = $_SERVER[ 'HTTP_X_REWRITE_URL' ];
    } elseif ( isset( $_SERVER[ 'REQUEST_URI' ] ) ) {
        // Apache and others.
        $request_uri  = $_SERVER[ 'REQUEST_URI' ];
    } elseif ( isset( $_SERVER[ 'HTTP_X_ORIGINAL_URL' ] ) ) {
        // Other IIS.
        $request_uri = $_SERVER[ 'HTTP_X_ORIGINAL_URL' ];
        $_SERVER[ 'REQUEST_URI' ] = $_SERVER[ 'HTTP_X_ORIGINAL_URL' ];
        if ( isset( $_SERVER[ 'QUERY_STRING' ] ) ) {
            $request_uri .= '?' . $_SERVER[ 'QUERY_STRING' ];
            if (! $_GET ) {
                parse_str( $_SERVER[ 'QUERY_STRING' ], $_GET );
            }
            if (! $_COOKIE ) {
                $cookies = explode( ';', $_SERVER[ 'HTTP_COOKIE' ] );
                foreach ( $cookies as $cookie_str ) {
                    list( $key, $value ) = explode( '=', trim( $cookie_str ) );
                    $_COOKIE[ $key ] = trim( $value );
                }
            }
        }
    } elseif ( isset( $_SERVER[ 'ORIG_PATH_INFO' ] ) ) {
        // IIS 5.0, PHP as CGI.
        $request_uri = $_SERVER[ 'ORIG_PATH_INFO' ];
        if (! empty( $_SERVER[ 'QUERY_STRING' ] ) ) {
            $request_uri .= '?' . $_SERVER[ 'QUERY_STRING' ];
        }
    }
    $root = $app->config( 'ServerDocumentRoot' );
    if (! isset( $root ) ) {
        $root = $_SERVER[ 'DOCUMENT_ROOT' ];
    }
    $root = $app->chomp_dir( $root );
    if ( isset( $alias_name ) ) {
        $alias_original = $root . $app->chomp_dir( $alias_name );
    }
    $ctime        = empty( $_SERVER[ 'REQUEST_TIME' ] )
                  ? time() : $_SERVER[ 'REQUEST_TIME' ];
    $request      = NULL;
    $text         = NULL;
    $param        = NULL;
    $orig_mtime   = NULL;
    $clear_cache  = NULL;
    $result_type  = NULL;
    $build_type   = NULL;
    $data         = NULL;
    $dynamicmtml  = FALSE;
    $is_secure    = NULL; if ( $secure ) { $is_secure = 1; }
    if (! isset( $extension ) ) $extension = 'html';
    if (! isset( $use_cache ) ) $use_cache = 0;
    if (! isset( $conditional ) ) $conditional = 0;
    if (! isset( $indexes ) ) $indexes = 'index.html';
    if (! isset( $size_limit ) ) $size_limit = 524288;
    if (! isset( $server_cache ) ) $server_cache = 7200;
    if (! isset( $excludes ) ) $excludes = 'php';
    if (! isset( $require_login ) ) $require_login = FALSE;
    if (! isset( $dynamic_caching ) ) $dynamic_caching = FALSE;
    if (! isset( $dynamic_conditional ) ) $dynamic_conditional = FALSE;
    // ========================================
    // Check Request and Set Parameter
    // ========================================
    if ( strpos( $request_uri, '?' ) ) {
    // if ( preg_match( '/\?/', $request_uri ) ) {
        list( $request, $param ) = explode( '?', $request_uri );
        $app->stash( 'query_string', $param );
        $pos = strpos( $request, '/mt-preview-' );
        if ( $pos !== FALSE ) {
        // if ( preg_match( '/\/mt\-preview\-/', $request ) ) {
            if ( preg_match( '/^[0-9]{1,}$/', $param ) ) {
                $use_cache = 0;
                $clear_cache = 1;
                $app->stash( 'preview', 1 );
            }
        }
    } else {
        $request = $request_uri;
        $param = NULL;
    }
    $url = $base . $request_uri;
    // ========================================
    // Set File and Content_type
    // ========================================
    $file = $root . DIRECTORY_SEPARATOR . $request;
    $cache_dir = $app->stash( 'powercms_files_dir' ) . DIRECTORY_SEPARATOR . 'cache';
    // $app->chomp_dir( $cache_dir );
    $file = $app->adjust_file( $file, $indexes, $alias_original, $alias_path );
    $static_path = $app->__add_slash( $app->config( 'StaticFilePath' ) );
    $app->check_excludes( $file, $excludes, $mt_dir, $static_path );
    if (! is_null( $file ) ) {
        $pinfo = pathinfo( $file );
        if ( isset( $pinfo[ 'extension' ] ) ) {
            $extension = $pinfo[ 'extension' ];
        }
    }
    $contenttype = $app->get_mime_type( $extension );
    $type_text = $app->type_text( $contenttype );
    $path = preg_replace( '!(/[^/]*$)!', '', $request );
    $path .= '/';
    $script = preg_replace( '!(^.*?/)([^/]*$)!', '$2', $request );
    if ( file_exists( $file ) ) {
        $orig_mtime = filemtime( $file );
    }
    // ========================================
    // Include DPAPI
    // ========================================
    $blog          = NULL;
    $force_compile = NULL;
    $args = array( 'blog_id' => $blog_id,
                   'conditional' => $conditional,
                   'use_cache' => $use_cache,
                   'root' => $root,
                   'cache_dir' => $cache_dir,
                   'plugin_path' => $plugin_path,
                   'file' => $file,
                   'base' => $base,
                   'path' => $path,
                   'script' => $script,
                   'request' => $request,
                   'param' => $param,
                   'is_secure' => $is_secure,
                   'url' => $url,
                   'contenttype' => $contenttype,
                   'extension' => $extension );
    $app->init( $args );
    $app->run_callbacks( 'pre_run', $mt, $ctx, $args );
    require_once $mt_dir . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'class.exception.php';
    if (! $mt ) {
        //require_once( $mt_dir . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'mt.php' );
        require_once( 'mt.php' );
        try {
            $mt = MT::get_instance( $blog_id, $mt_config );
        } catch ( MTInitException $e ) {
            $app->run_callbacks( 'mt_init_exception', $mt, $ctx, $args, $e );
            if ( (! isset( $mt ) ) && $require_login ) {
                // for PowerCMS Professional
                $app->service_unavailable();
            }
        }
    }
    if ( isset( $mt ) ) {
        $ctx =& $mt->context();
        if (! $no_database ) {
            set_error_handler( array( &$mt, 'error_handler' ) );
            $driver = $app->config( 'objectdriver' );
            $driver = preg_replace( '/^DB[ID]::/', '', $driver );
            $driver or $driver = 'mysql';
            $driver = strtolower($driver);
            $cfg =& $app->config;
            $cfg[ 'dbdriver' ] = $driver;
            if ( $driver == 'mysql' or $driver == 'postgres' ) {
                $mt->db()->set_names( $mt );
            }
            $app->run_callbacks( 'init_db', $mt, $ctx, $args );
            if (! $blog = $ctx->stash( 'blog' ) ) {
                $blog = $mt->db()->fetch_blog( $blog_id );
            }
            $ctx->stash( 'blog', $blog );
            $ctx->stash( 'blog_id', $blog_id );
            $app->stash( 'blog', $blog );
            $app->stash( 'blog_id', $blog_id );
            $app->set_context( $mt, $ctx, $blog_id );
        } else {
            $ctx->stash( 'no_database', 1 );
            $app->set_context( $mt, $ctx );
            //$mt->init_plugins();
            //require_once( 'init.dynamicmtml.php' );
        }
        $mt->init_plugins();
        // TODO::Create Blog object.
        $ctx->stash( 'callback_dir', $app->stash( 'callback_dir' ) );
        $ctx->stash( 'preview', $app->stash( 'preview' ) );
        $ctx->stash( 'content_type', $contenttype );
        $base_original = $root;
        $request_original = $request;
        $app->run_callbacks( 'post_init', $mt, $ctx, $args );
        if ( ( $base_original != $root ) || ( $request_original != $request ) ) {
            $file = $root . DIRECTORY_SEPARATOR . $request;
            $file = $app->adjust_file( $file, $indexes, $alias_original, $alias_path );
            $app->stash( 'file', $file );
            $app->stash( 'root', $root );
            $app->stash( 'request', $request );
        }
        $cfg_forcecompile = $app->config( 'DynamicForceCompile' );
        if ( $cfg_forcecompile ) {
            $force_compile = 1;
        }
    }
    // ========================================
    // Search Cache
    // ========================================
    $cache = $app->cache_filename( $blog_id, $file, $param );
    $app->stash( 'cache', $cache );
    $args[ 'cache' ] = $cache;
    if ( $use_cache && file_exists( $cache ) ) {
        require_once( $plugin_path . 'dynamicmtml.check_cache.php' );
    }
    // ========================================
    // Include PHP if Directory Index
    // ========================================
    $app->include_php( $file );
    // ========================================
    // Run DynamicMTML
    // ========================================
    if ( isset( $mt ) && $force_compile ) {
        $app->stash( 'force_compile', 1 );
        $ctx->force_compile = TRUE;
    }
    if ( $app->stash( 'perlbuild' ) ) {
        require_once( $plugin_path . 'dynamicmtml.perlbuilder.php' );
        $app->run_callbacks( 'post_perlbuild', $mt, $ctx, $args, $text );
    }
    if ( $blog ) {
        if ( $blog->dynamic_mtml || $require_login ) {
            $dynamicmtml = TRUE;
        }
    } else {
        $dynamicmtml = TRUE;
    }
    if ( file_exists( $file ) && $dynamicmtml ) {
        if ( $app->config( 'Database' ) ) {
            if ( $app->config( 'PermCheckAtPreview' ) ) {
                if (! isset( $mt ) ) {
                    $app->access_forbidden();
                } else {
                    $client_author = $app->user();
                    if (! $client_author || $client_author->type == 2 ) {
                        $app->access_forbidden();
                    }
                }
            }
        }
        if (! $orig_mtime ) {
            $orig_mtime = filemtime( $file );
        }
        if ( $type_text && ( filesize( $file ) < $size_limit ) ) {
            $regex = '<\${0,1}' . 'mt';
            if (! $text ) {
                if ( $dynamicphpfirst ) {
                    ob_start();
                    include( $file );
                    $text = ob_get_contents();
                    ob_end_clean();
                } else {
                    $text = file_get_contents( $file );
                }
            }
            $app->stash( 'text', $text );
            // require_once( $plugin_path . 'resource.var.php' );
            if ( isset( $mt ) && ( preg_match( "/$regex/i", $text ) ) ) {
                require_once( 'MTUtil.php' );
                $last_ts = NULL;
                $file_ts = NULL;
                $file_ts = filemtime( $file );
                if (! $no_database ) {
                    $last_ts = $blog->blog_children_modified_on;
                    $last_ts = strtotime( $last_ts );
                    if ( $file_ts > $last_ts ) {
                        $last_ts = $file_ts;
                    }
                } else {
                    $last_ts = $file_ts;
                }
                if ( $conditional ) {
                    $app->do_conditional( $last_ts );
                }
                $orig_mtime = $last_ts;
                // Set Context
                $ctx->stash( 'dynamicmtml', 1 );
                $ctx->stash( 'blog', $blog );
                $ctx->stash( 'blog_id', $blog_id );
                $ctx->stash( 'local_blog_id', $blog_id );
                $filemtime = $orig_mtime;
                $build_type = 'dynamic_mtml';
                $app->stash( 'build_type', $build_type );
                if (! $no_database ) {
                    $app->run_callbacks( 'pre_resolve_url', $mt, $ctx, $args );
                    $data = $app->stash( 'fileinfo' );
                    if (! isset( $data ) ) {
                        $data = $mt->db()->resolve_url( $mt->db()->escape( urldecode( $request ) ),
                                                        $blog_id, array( 1, 2, 4 ) );
                    }
                    $app->stash( 'fileinfo', $data );
                    $app->run_callbacks( 'post_resolve_url', $mt, $ctx, $args );
                }
                $template = NULL;
                if ( $force_compile ) {
                    $ctx->force_compile = true;
                }
                if ( isset( $data ) ) {
                    require_once( $plugin_path . 'dynamicmtml.set_context.php' );
                } else {
                    $ctx->stash( 'no_fileinfo', 1 );
                    $basename = '_' . md5( $file ) . '_';
                    ${$basename} = $text;
                }
                $template = $app->get_smarty_template( $ctx, $data, $basename, $filemtime );
                $app->stash( 'template', $template );
                $app->stash( 'basename', $basename );
                $app->run_callbacks( 'pre_build_page', $mt, $ctx, $args );
                if ( $force_compile ) {
                    $ctx->force_compile = TRUE;
                }
                if ( $dmtml_exception || $no_database ) {
                    $content = $app->build_tmpl( $ctx, $text, array( 'fileinfo'  => $data,
                                                                     'basename'  => $basename,
                                                                     'filemtime' => $filemtime ) );
                } else {
                    $content = $mt->fetch( 'var:' . $basename );
                }
                $app->run_callbacks( 'build_page', $mt, $ctx, $args, $content );
                $app->send_http_header( $contenttype, $filemtime, strlen( $content ) );
                echo $content;
                $result_type = $build_type;
                $app->stash( 'result_type', $result_type );
                $app->run_callbacks( 'post_return', $mt, $ctx, $args, $content );
                if ( file_exists( $template ) ) {
                    if ( $clear_cache ) {
                        unlink ( $template );
                    }
                }
            } else {
                if ( $conditional ) {
                    $app->do_conditional( filemtime( $file ) );
                }
                $filemtime = $orig_mtime;
                $build_type = 'static_text';
                if ( isset( $mt ) ) {
                    $app->stash( 'build_type', $build_type );
                    $app->stash( 'filemtime', $filemtime );
                    $app->run_callbacks( 'pre_build_page', $mt, $ctx, $args );
                }
                $content = $text;
                // if ( preg_match( "/$regex/i", $text ) ) {
                // TODO:: Build without DB!
                // $context =& $app->context();
                // $template = $app->get_smarty_template( $context, NULL, $basename, $filemtime );
                // }
                if ( $include_static ) {
                    ob_start();
                    include( $file );
                    $content = ob_get_contents();
                    ob_end_clean();
                }
                if ( isset( $mt ) ) {
                    $app->run_callbacks( 'build_page', $mt, $ctx, $args, $content );
                } else {
                    $content = $app->non_dynamic_mtml( $content );
                    $app->send_http_header( $contenttype, $filemtime, strlen( $content ) );
                    echo $content;
                    exit();
                }
                $app->send_http_header( $contenttype, $filemtime, strlen( $content ) );
                echo $content;
                $result_type = $build_type;
                $app->stash( 'result_type', $result_type );
                $app->run_callbacks( 'post_return', $mt, $ctx, $args, $content );
            }
        } else {
            if ( $conditional ) {
                $app->do_conditional( filemtime( $file ) );
            }
            $build_type = 'binary_data';
            if ( $type_text ) {
                $build_type = 'large_text';
            }
            $filemtime = $orig_mtime;
            $app->stash( 'filemtime', $filemtime );
            $app->stash( 'build_type', $build_type );
            if ( isset( $mt ) ) {
                $app->run_callbacks( 'pre_build_page', $mt, $ctx, $args );
            }
            $app->send_http_header( $contenttype, $filemtime, filesize( $file ) );
            $content = NULL;
            if ( filesize( $file ) < $size_limit ) {
                $content = file_get_contents( $file );
                $app->run_callbacks( 'build_page', $mt, $ctx, $args, $content );
                echo $content;
            } else {
                $app->echo_file_get_contents( $file, $size_limit );
            }
            if (! isset( $mt ) ) {
                exit();
            } else {
                $app->stash( 'result_type', $build_type );
                $app->run_callbacks( 'post_return', $mt, $ctx, $args, $content );
            }
        }
    } else {
        if ( isset( $mt ) && (! $no_database ) ) {
            $build_type = 'mt_dynamic';
            $app->stash( 'build_type', $build_type );
            $app->stash( 'filemtime', NULL );
            $app->run_callbacks( 'pre_build_page', $mt, $ctx, $args );
            if ( $force_compile ) {
                $ctx->force_compile = TRUE;
            }
            if ( $dynamic_caching ) {
                $mt->caching( TRUE );
            }
            if ( $dynamic_conditional ) {
                $mt->conditional( TRUE );
            }
            ob_start();
            $mt->view();
            $content = ob_get_contents();
            ob_end_clean();
            $app->run_callbacks( 'build_page', $mt, $ctx, $args, $content );
            $app->send_http_header( $contenttype, $ctime, strlen( $content ) );
            echo $content;
            $result_type = $build_type;
            $app->stash( 'result_type', $result_type );
            $app->run_callbacks( 'post_return', $mt, $ctx, $args, $content );
        } else {
            $app->file_not_found();
        }
    }
    // ========================================
    // Save Cache
    // ========================================
    $filemtime = $orig_mtime;
    $app->stash( 'filemtime', $filemtime );
    if ( $use_cache && $cache && $content ) {
        if ( isset( $mt ) ) {
            $app->run_callbacks( 'pre_save_cache', $mt, $ctx, $args, $content );
        }
        if (! ( $fh = fopen( $cache, 'w' ) ) ) {
            return;
        }
        fwrite( $fh, $content, 128000 );
        fclose( $fh );
        touch( $cache, $ctime );
        $app->stash( 'cache_saved', 1 );
    }
    if ( $app->stash( 'preview' ) ) {
        if ( $file && ( file_exists( $file ) ) ) {
            unlink( $file );
        }
    }
    if ( isset( $mt ) ) {
        $app->run_callbacks( 'take_down', $mt, $ctx, $args, $content );
    } else {
        $app->run_callbacks( 'take_down_error' );
    }
    // ========================================
    // 503 Service Unavailable
    // ========================================
    if (! isset( $mt ) ) {
        $app->service_unavailable();
    }
    exit();
?>