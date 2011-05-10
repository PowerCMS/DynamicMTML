<?php
    global $app;
    global $mt;
    global $ctx;
    global $dmtml_exception;
    // init plugins tags
    if ( isset( $app ) ) {
        $tags_kind = array( 'block', 'function' );
        $block_methods = array();
        $function_methods = array();
        foreach ( $tags_kind as $kind ) {
            $kind_tags = $app->stash( "{$kind}_tags" );
            if (! $kind_tags || ! is_array( $kind_tags ) ) {
                break;
            }
            foreach ( $kind_tags as $tag => $funcs ) {
                foreach ( $funcs as $plugin => $meth ) {
                    $component = $app->component( $plugin );
                    if ( $component && is_object( $component ) ) {
                        $class_name = get_class( $component );
                        if ( method_exists( $component, $meth ) ) {
                            if ( $kind == 'block' ) {
                                if ( preg_match( '/^if/i', $tag ) ) {
                                    $app->ctx->add_conditional_tag( $tag, 'smarty_block_mtif_mtml_block' );
                                } else {
                                    $app->ctx->add_container_tag( $tag, 'smarty_block_mt_mtml_block' );
                                }
                                $block_methods[ $tag ] = array( $plugin, $meth );
                            } elseif ( $kind == 'function' ) {
                                $app->ctx->add_tag( $tag, 'smarty_function_mt_mtml_function' );
                                $function_methods[ $tag ] = array( $plugin, $meth );
                            }
                        }
                    }
                }
            }
        }
        $app->stash( 'block_methods', $block_methods );
        $app->stash( 'function_methods', $function_methods );
        $modifiers = $app->stash( 'modifiers' );
        if (! $modifiers ) return 1;
        if ( $blog = $app->blog ) {
            $templates_c = $blog->site_path() . DIRECTORY_SEPARATOR . 'templates_c';
        } else {
            $templates_c = dirname( $app->root . $_SERVER[ 'PHP_SELF' ] ) . DIRECTORY_SEPARATOR . 'templates_c';
        }
        $app->stash( 'templates_c', $templates_c );
        if ( $templates_c ) $lib = $templates_c . DIRECTORY_SEPARATOR . 'smarty_modifier_dynamicmtml_global.php';
        if ( $templates_c && is_writable( $templates_c ) ) {
            $modifier_methods = array();
            if ( $modifiers && is_array( $modifiers ) ) {
                foreach ( $modifiers as $tag => $funcs ) {
                    if ( is_array( $funcs ) ) {
                        foreach ( $funcs as $plugin => $modifier ) {
                            $component = $app->component( $plugin );
                            if ( $component && is_object( $component ) ) {
                                $class_name = get_class( $component );
                                if ( method_exists( $component, $modifier ) ) {
                                    if ( (! function_exists( "smarty_modifier_{$modifier}" ) ) &&
                                        (! function_exists( "smarty_modifier_{$plugin}_{$modifier}" ) ) ) {
                                        $modifier = "smarty_modifier_{$plugin}_{$modifier}";
                                        $app->ctx->add_global_filter( $tag, $modifier );
                                        $modifier_methods[ $modifier ] = $tag;
                                    }
                                }
                            }
                        }
                    }
                }
            }
            if ( $modifier_methods ) {
                if (! file_exists( $lib ) || $ctx->force_compile || $app->config( 'DynamicForceCompile' ) ) {
                    $code = "<?php\n";
                    $func = '__FUNCTION__';
                    foreach ( $modifier_methods as $meth => $tag ) {
                        $code .= "    function {$meth} ( \$text, \$arg ) {\n";
                        $code .= "        return smarty_modifier_mt_mtml_modifier( {$func}, \$text, \$arg );\n";
                        $code .= "    }\n";
                    }
                    $code .= "?>";
                    if ( $app->content_is_updated( $lib, $code ) ) {
                        $app->put_data( $code, $lib );
                    }
                }
                $app->stash( 'modifier_methods', $modifier_methods );
            }
        }
        if ( $lib && file_exists( $lib ) ) {
            require_once( $lib );
        }
        return 1;
    }
    $mt_config = $mt->cfg_file;
    $static_path = $mt->config[ 'staticfilepath' ];
    if (! preg_match( "/DIRECTORY_SEPARATOR$/", $static_path ) ) {
        $static_path .= DIRECTORY_SEPARATOR;
    }
    $mt_dir = dirname( $mt_config );
    $blog_id = $mt->blog_id;
    $blog = $mt->db()->fetch_blog( $blog_id );
    $size_limit = 524288;
    $server_cache = $blog->search_cache_expiration;
    $indexes = $blog->index_files;
    $excludes = $blog->exclude_extension;
    if (! isset( $server_cache ) ) $server_cache = 7200;
    if (! $indexes ) $indexes = 'index.html,index.mtml';
    if (! $excludes ) $excludes ='php,cgi,fcgi';
    $plugin_path = dirname( __FILE__ ) . DIRECTORY_SEPARATOR;
    $powercms_files = $mt->config[ 'PowerCMSFilesDir' ];
    if (! $powercms_files ) {
        $powercms_files = $mt_dir . DIRECTORY_SEPARATOR . 'powercms_files' . DIRECTORY_SEPARATOR;
    } else {
        if (! preg_match( "/DIRECTORY_SEPARATOR$/", $powercms_files ) ) {
            $powercms_files .= DIRECTORY_SEPARATOR;
        }
    }
    $cache_dir = $powercms_files . 'cache';
    $extension = $blog->file_extension;
    $use_cache = $blog->search_cache;
    $conditional  = $blog->search_conditional;
    $dynamic_caching = $blog->dynamic_cache;
    $dynamic_conditional = $blog->dynamic_conditional;
    $require_login = NULL;
    if ( $blog->has_column( 'is_members' ) ) {
        if ( $blog->is_members ) {
            $require_login = 1;
        }
    }
    if ( $blog->dynamic_mtml ) {
        $dmtml_exception = 1;
    }
    require_once ( $plugin_path . 'dynamicmtml.run.php' );
    return 1;

    function smarty_block_mt_mtml_block ( $args, $content, &$ctx, &$repeat ) {
        return smarty_dynamic_tag_dynamicmtml( 'block', $args, $content, $ctx, $repeat );
    }

    function smarty_block_mtif_mtml_block ( $args, $content, &$ctx, &$repeat ) {
        return smarty_dynamic_tag_dynamicmtml( 'block', $args, $content, $ctx, $repeat );
    }

    function smarty_function_mt_mtml_function ( $args, &$ctx ) {
        return smarty_dynamic_tag_dynamicmtml( 'function', $args, $ctx );
    }

    function smarty_modifier_mt_mtml_modifier ( $function, &$text, &$arg ) {
        global $mt;
        $ctx = &$mt->context();
        $app = $ctx->stash( 'bootstrapper' );
        $target_tags = $app->stash( 'modifier_methods' );
        if ( $target_tags && $target_tags[ $function ] ) {
            $path = explode( '_', $function );
            $plugin = $path[2];
            $meth = preg_replace( "/^smarty_modifier_{$plugin}_/", '', $function );
            $component = $app->component( $plugin );
            if ( $component && $meth && method_exists( $component, $meth ) ) {
                return $component->$meth( $text, $arg );
            }
        }
    }

    function smarty_dynamic_tag_dynamicmtml ( $kind, &$arg1, &$arg2, &$arg3 = NULL, &$arg4 = NULL ) {
        $args    = NULL;
        $content = NULL;
        $ctx     = NULL;
        $repeat  = NULL;
        if ( $kind == 'block' ) {
            $ctx = $arg3;
        } elseif ( $kind == 'function' ) {
            $ctx = $arg2;
        }
        $app = $ctx->stash( 'bootstrapper' );
        $this_tag = $ctx->this_tag();
        if (! $this_tag ) return;
        $this_tag = preg_replace( '/^mt/i', '', $this_tag );
        $target_tags = $app->stash( "{$kind}_methods" );
        if ( $target_tags && $target_tags[ $this_tag ] ) {
            $plugin = $target_tags[ $this_tag ][0];
            $meth   = $target_tags[ $this_tag ][1];
            $component = $app->component( $plugin );
            if ( $component && $meth && method_exists( $component, $meth ) ) {
                if ( $kind == 'block' ) {
                    return $component->$meth( $arg1, $arg2, $arg3, $arg4 );
                } elseif ( $kind == 'function' ) {
                    return $component->$meth( $arg1, $arg2 );
                }
            }
        }
    }
?>