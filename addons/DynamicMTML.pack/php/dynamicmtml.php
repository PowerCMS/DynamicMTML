<?php
class DynamicMTML {

    var $mt;
    var $ctx;
    var $__stash;
    var $db;
    var $config;
    var $cfg_file;
    var $mode;
    var $mod_rewrite;
    var $blog;
    var $blog_id;
    var $user;
    var $user_cookie;
    var $session;
    var $url;
    var $root;
    var $is_secure;
    var $file;
    var $base;
    var $path;
    var $static_path;
    var $script;
    var $request;
    var $cache;
    var $cache_dir;
    var $conditional;
    var $force_compile;
    var $param;
    var $query_string;
    var $contenttype;
    var $extension;
    var $build_type;
    var $result_type;
    var $fileinfo;
    var $template;
    var $basename;
    var $filemtime;
    var $cache_saved;
    var $text;
    var $contents;
    var $templates_c;
    var $preview;
    var $protocol;
    var $remote_ip;
    var $request_method;
    var $entry_ids_published;
    var $plugin_path;
    protected $debugging = FALSE;

    function init ( $args = array() ) {
        require_once( 'MTUtil.php' );
        $this->protocol  = $_SERVER[ 'SERVER_PROTOCOL' ];
        $this->remote_ip = $_SERVER[ 'REMOTE_ADDR' ];
        if (! $this->request_method ) {
            $this->request_method = $_SERVER[ 'REQUEST_METHOD' ];
        }
        $this->static_path = $this->config( 'StaticWebPath' );
        foreach ( $args as $key => $val ) {
            $this->$key = $args[ $key ];
            if ( $key === 'param' ) {
                $this->query_string = $args[ $key ];
            }
        }
        if (! $mode = $this->get_param( '__mode' ) ) {
            $mode = 'default';
        }
        $this->mode = $mode;
        $this->entry_ids_published = array();
    }

    function set_context ( &$mt, &$ctx, $blog_id = NULL ) {
        $this->stash( 'mt', $mt );
        $this->stash( 'ctx', $ctx );
        if ( isset( $ctx ) ) {
            $ctx->stash( 'bootstrapper', $this );
        }
        if (! $this->no_database ) {
            $mt->db()->set_names( $mt );
            $this->stash( 'db', $mt->db() );
            $blog = $ctx->stash( 'blog' );
            if (! isset( $blog ) ) {
                $blog = $this->stash( 'blog' );
            }
        }
        if (! isset( $blog ) ) {
            // if (! $blog_id ) {
            //     $blog_id = $this->stash( 'blog_id' );
            // }
            if (! $blog_id ) {
                // $blog = $this->load( 'Blog',
                //                      array( 'class' => 'website', 'blog' ),
                //                      array( 'limit' => 1,
                //                             'sort_order' => 'ascend',
                //                             'sort' => 'id' ) );
                // $blog_id = $blog->id;
            } else {
                if (! $this->no_database ) {
                    $blog = $mt->db()->fetch_blog( $blog_id );
                }
            }
        }
        $templates_c = NULL;
        $cache = NULL;
        if ( isset( $blog ) ) {
            $ctx->stash( 'blog', $blog );
            $ctx->stash( 'blog_id', $blog_id );
            $this->stash( 'blog', $blog );
            $this->stash( 'blog_id', $blog_id );

            $site_path = $blog->site_path();
            $self = $site_path;
            $templates_c = $self . DIRECTORY_SEPARATOR . 'templates_c';
            $cache = $self . DIRECTORY_SEPARATOR . 'cache';
        } else {
            $self = $this->root . dirname( $_SERVER[ 'PHP_SELF' ] );
            $templates_c = $self . DIRECTORY_SEPARATOR . 'templates_c';
            $cache = $self . DIRECTORY_SEPARATOR . 'cache';
        }
        if (! is_dir( $templates_c ) ) {
            if (! $this->stash( 'no_generate_directories' ) ) {
                mkdir( $templates_c, 0755 );
            }
        }
        $this->stash( 'templates_c', $templates_c );
        $cache = $self . DIRECTORY_SEPARATOR . 'cache';
        if (! is_dir( $cache ) ) {
            if (! $this->stash( 'no_generate_directories' ) ) {
                mkdir( $cache, 0755 );
            }
        }
        $powercms_files_dir = NULL;
        if (! $powercms_files_dir = $mt->config( 'PowerCMSFilesDir' ) ) {
            $powercms_files_dir = dirname( $this->cfg_file ) . DIRECTORY_SEPARATOR . 'powercms_files';
        }
        $powercms_files_dir = rtrim( $powercms_files_dir, DIRECTORY_SEPARATOR );
        if (! is_dir( $powercms_files_dir ) ) {
            if (! $this->stash( 'no_generate_directories' ) ) {
                mkdir( $powercms_files_dir, 0755 );
            }
        }
        $this->stash( 'powercms_files_dir', $powercms_files_dir );
        $powercms_cache = $powercms_files_dir . DIRECTORY_SEPARATOR . 'cache';
        if (! is_dir( $powercms_cache ) ) {
            if (! $this->stash( 'no_generate_directories' ) ) {
                mkdir( $powercms_cache, 0755 );
            }
        }
        $language = $mt->config( 'DefaultLanguage' );
        $l10n_dir = $this->stash( 'l10n_dir' );
        if ( is_array( $l10n_dir ) ) {
            foreach ( $l10n_dir as $plugin_l10n_dir ) {
                if ( is_dir( $plugin_l10n_dir ) ) {
                    $l10n_file = $plugin_l10n_dir . DIRECTORY_SEPARATOR . 'l10n_' . $language . '.php';
                    if ( file_exists( $l10n_file ) ) {
                        require( $l10n_file );
                        if ( isset( $Lexicon ) && is_array( $Lexicon ) ) {
                            $this->add_lexicon( $language, $Lexicon );
                            unset( $Lexicon );
                        }
                    }
                }
            }
        }
        if (! function_exists( 'is_valid_email' ) ) {
            require_once( dirname(__FILE__) . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'mt_util.php' );
        }
    }

    function user () {
        if (! $timeout = $this->config( 'UserSessionTimeout' ) ) {
            $timeout = 14400;
        }
        $author = $this->get_author( $this->ctx, $timeout );
        return $author;
    }

    function mt () {
        if ( $this->mt ) return $this->mt;
        global $mt;
        if ( $mt ) {
            return $this->stash( 'mt', $mt );
        }
        if ( $this->ctx() ) {
            return $this->stash( 'mt', $this->ctx()->mt );
        }
        global $ctx;
        if ( $ctx ) {
            return $this->stash( $ctx->mt );
        }
        return $this;
    }

    function ctx () {
        if ( $this->ctx ) return $this->ctx;
        global $ctx;
        if ( $ctx ) {
            return $this->stash( 'ctx', $ctx );
        }
    }

    function db () {
        if ( $this->db ) return $this->db;
        if ( $this->ctx() ) {
            return $this->stash( 'db', $this->ctx()->mt->db() );
        }
    }

    function init_mt ( &$mt, &$ctx, $blog_id ) {
        if ( is_object( $blog_id ) ) {
            $blog = $blog_id;
            $blog_id = $blog->id;
        } else {
            if ( ( $this->stash( 'blog_id' ) ) != $blog_id ) {
                $blog = $this->load( 'Blog', $blog_id );
            }
        }
        if ( (! $mt->blog_id() ) || ( $mt->blog_id() != $blog_id ) ) {
            $mt->init( $blog_id, $this->cfg_file );
            $mt->db()->set_names( $mt );
            $ctx->stash( 'blog', $blog );
            $ctx->stash( 'blog_id', $blog_id );
            $this->stash( 'blog', $blog );
            $this->stash( 'blog_id', $blog_id );
        }
    }

    function user_cookie () {
        return $this->user_cookie;
    }

    function blog ( $id = NULL ) {
        if ( $this->no_database ) {
            return NULL;
        }
        $blog_id = $id;
        $mt = $this->mt();
        if (! $blog_id ) {
            if ( $blog = $this->blog ) {
                return $blog;
            }
            $blog_id = $mt->blog_id();
        }
        if (! $mt->db() ) {
            return NULL;
        }
        if (! $blog_id ) {
            return NULL;
        }
        $blog = $mt->db()->fetch_blog( $blog_id );
        if (! $blog ) {
            $blog = $this->load( 'Blog', $blog_id );
        }
        if (! $blog ) {
            $terms = array( 'id' => $blog_id, 'class' => array( 'website', 'blog' ) );
            $blog = $this->load( 'Blog', $terms, array( 'limit' => 1 ) );
            if (! $blog ) {
                $ctx = $this->ctx();
                if ( $ctx ) {
                    if ( $ctx->stash( 'blog' ) ) {
                        $blog = $ctx->stash( 'blog' );
                    }
                }
            }
        }
        if (! $id ) {
            $this->stash( 'blog', $blog );
            $ctx->stash( 'blog', $blog );
            if ( $blog ) {
                $this->stash( 'blog_id', $blog->id );
                $ctx->stash( 'blog_id', $blog->id );
            }
        }
        return $blog;
    }

    function blog_id () {
        if ( $this->blog_id ) {
            return $this->blog_id;
        }
        $mt = $this->mt();
        return $this->stash( 'blog_id', $mt->blog_id() );
    }

    function mode () {
        return $this->mode;
    }

    function base () {
        return $this->base;
    }

    function path () {
        return $this->path;
    }

    function remote_ip () {
        return $this->remote_ip;
    }

    function is_secure () {
        return $this->is_secure;
    }

    function query_string () {
        if ( $query_string = $this->query_string ) {
            return $query_string;
        }
        if ( $params = $this->param() ) {
            $params_array = array();
            if ( is_array( $params ) ) {
                foreach ( $params as $key => $value ) {
                    array_push( $params_array, "$key=$value" );
                }
                if ( $params_array ) {
                    return join( '&', $params_array );
                }
            }
        }
    }

    function static_path () {
        return $this->static_path;
    }

    function request_method () {
        return $this->request_method;
    }

    function log ( $msg = NULL, $args = NULL ) {
        if ( ( is_array( $msg ) ) || ( is_object( $msg ) ) ) {
            ob_start();
            var_dump( $msg );
            $msg = ob_get_contents();
            ob_end_clean();
        }
        require_once( 'dynamicmtml.util.php' );
        require_once( 'class.mt_log.php' );
        $_log = new Log;
        $_log->message = $msg;
        if ( $user = $this->user() ) {
            $_log->author_id = $user->id;
            $_log->created_by = $user->id;
        } else {
            $_log->author_id = 0;
        }
        if ( $args && __is_hash( $args ) ) {
            foreach ( $args as $key => $val ) {
                if ( $_log->has_column( $key ) ) {
                    $_log->$key = $val;
                }
            }
        }
        $_log->ip = $this->remote_ip;
        if (! $_log->class ) {
            $_log->class = 'system';
        }
        if (! $_log->level ) {
            $_log->level = 1;
        }
        if (! $_log->blog_id ) {
            if ( $blog = $this->blog ) {
                $_log->blog_id = $blog->id;
            }
        }
        if (! $_log->blog_id ) {
            $_log->blog_id = 0;
        }
        $ts = gmdate( "YmdHis" );
        $_log->created_on  = $ts;
        $_log->modified_on = $ts;
        $_log->Save();
    }

    function cache ( $key, $val = NULL ) {
        return $this->stash( $key, $val );
    }

    function stash ( $key, $val = NULL ) {
        if ( isset ( $val ) ) {
            $this->$key = $val;
        }
        if ( isset( $this->$key ) ) {
            return $this->$key;
        }
        return NULL;
    }

    function param ( $param = NULL ) {
        $to_encoding = $this->config( 'PublishCharset' );
        $to_encoding or $to_encoding = 'UTF-8';
        if ( $param ) {
            if ( $value = $this->get_param( $param ) ) {
                if (! is_array( $value ) ) {
                    $from_encoding = mb_detect_encoding( $value, 'UTF-8,EUC-JP,SJIS,JIS' );
                    $value = mb_convert_encoding( $value, $to_encoding, $from_encoding );
                    return $value;
                } else {
                    $new_array = array();
                    foreach ( $value as $val ) {
                        $from_encoding = mb_detect_encoding( $val, 'UTF-8,EUC-JP,SJIS,JIS' );
                        $val = mb_convert_encoding( $val, $to_encoding, $from_encoding );
                        array_push ( $new_array, $val );
                    }
                    return $new_array;
                }
            }
        } else {
            $vars = $_REQUEST;
            $params = array();
            foreach ( $vars as $key => $value ) {
                if ( $_GET[ $key ] || $_POST[ $key ] ) {
                    if ( is_string( $value ) ) {
                        $from_encoding = mb_detect_encoding( $value, 'UTF-8,EUC-JP,SJIS,JIS' );
                        $value = mb_convert_encoding( $value, $to_encoding, $from_encoding );
                    }
                    $params[ $key ] = $value;
                }
            }
            return $params;
        }
    }

    function config ( $id, $value = NULL ) {
        $orig_id = $id;
        $id = strtolower( $id );
        if ( isset( $value ) ) {
            $this->config[ $id ] = $value;
            return $value;
        }
        if ( isset( $this->config[ $id ] ) ) {
            return $this->config[ $id ];
        }
        if ( isset( $this->mt ) ) {
            if ( $config = $this->mt->config( $id ) ) {
                $this->config[ $id ] = $config;
                return $config;
            }
        }
        $plugins_config = $this->stash( 'plugins_config' );
        if ( is_array( $plugins_config ) ) {
            foreach ( $plugins_config as $key => $val ) {
                if ( isset( $val[ 'config_settings' ] ) ) {
                    if ( isset( $val[ 'config_settings' ][ $id ] ) ) {
                        if ( isset( $val[ 'config_settings' ][ $id ][ 'default' ] ) ) {
                            $this->config[ $id ] = $val[ 'config_settings' ][ $id ][ 'default' ];
                            return $val[ 'config_settings' ][ $id ][ 'default' ];
                            break;
                        }
                    }
                    if ( isset( $val[ 'config_settings' ][ $orig_id ] ) ) {
                        if ( isset( $val[ 'config_settings' ][ $orig_id ][ 'default' ] ) ) {
                            $this->config[ $id ] = $val[ 'config_settings' ][ $orig_id ][ 'default' ];
                            return $val[ 'config_settings' ][ $orig_id ][ 'default' ];
                            break;
                        }
                    }
                }
            }
        }
        return NULL;
    }

    function configure ( $file = NULL ) {
        // if ( isset( $this->config ) ) return $config;
        $this->cfg_file = $file;
        $cfg = array();
        if ( file_exists( $file ) ) {
            if ( $fp = file( $file ) ) {
                foreach ( $fp as $line ) {
                    if (! preg_match('/^\s*\#/i', $line ) ) {
                        if ( preg_match( '/^\s*(\S+)\s+(.*)$/', $line, $regs ) ) {
                            $key = strtolower( trim( $regs[1] ) );
                            $value = trim( $regs[2] );
                            if ( $key === 'pluginpath' ) {
                                $cfg[ $key ][] = $value;
                            } else {
                                $cfg[ $key ] = $value;
                            }
                        }
                    }
                }
            } else {
                // die( "Unable to open configuration file $file" );
            }
        }
        $cfg[ 'mtdir' ] = realpath( dirname( $file ) );
        $cfg[ 'phpdir' ] = $cfg[ 'mtdir' ] . DIRECTORY_SEPARATOR . 'php';
        $cfg[ 'phplibdir' ] = $cfg[ 'phpdir' ] . DIRECTORY_SEPARATOR . 'lib';
        $cfg[ 'powercmsdir' ] = realpath( dirname( __FILE__ ) );
        $this->config =& $cfg;
        isset( $cfg[ 'pluginpath' ] ) or
            $cfg[ 'pluginpath' ] = array( $this->config( 'MTDir' ) . DIRECTORY_SEPARATOR . 'plugins' );
            $cfg[ 'pluginpath' ][] = $this->config( 'MTDir' ) . DIRECTORY_SEPARATOR . 'addons';
        if ( strtoupper( substr( PHP_OS, 0, 3 ) === 'WIN' ) ) {
            $path_sep = ';';
        } else {
            $path_sep = ':';
        }
        ini_set( 'include_path',
            $cfg[ 'powercmsdir' ] . $path_sep .
            $cfg[ 'powercmsdir' ] . DIRECTORY_SEPARATOR . "callbacks" . $path_sep .
            $cfg[ 'phpdir' ] . DIRECTORY_SEPARATOR . "lib" . $path_sep .
            $cfg[ 'phpdir' ] . DIRECTORY_SEPARATOR . "extlib" . $path_sep .
            $cfg[ 'phpdir' ] . DIRECTORY_SEPARATOR . "extlib" . DIRECTORY_SEPARATOR . "smarty" . DIRECTORY_SEPARATOR . "libs" . $path_sep .
            $cfg[ 'phpdir' ] . DIRECTORY_SEPARATOR . "extlib" . DIRECTORY_SEPARATOR . "smarty" . DIRECTORY_SEPARATOR . "libs" . DIRECTORY_SEPARATOR . 'plugins' . $path_sep .
            $cfg[ 'phpdir' ] . DIRECTORY_SEPARATOR . "extlib" . DIRECTORY_SEPARATOR . "adodb5" . $path_sep .
            $cfg[ 'phpdir' ] . DIRECTORY_SEPARATOR . "extlib" . DIRECTORY_SEPARATOR . "FirePHPCore" . $path_sep .
            ini_get( 'include_path' )
        );
        $this->init_plugin_dir();
        if (! $powercms_files_dir = $this->config( 'PowerCMSFilesDir' ) ) {
            $powercms_files_dir = dirname( $this->cfg_file ) . DIRECTORY_SEPARATOR . 'powercms_files';
        }
        $powercms_files_dir = $this->__add_slash( $powercms_files_dir, FALSE );
        $this->stash( 'powercms_files_dir', $powercms_files_dir );
        $this->run_callbacks( 'init_request' );
    }

    function get_args () {
        $args = get_class_vars( 'DynamicMTML' );
        foreach ( $args as $key => $val ) {
            $args[ $key ] = $this->$key;
        }
        return $args;
    }

    function file () {
        return $this->file;
    }

    function url () {
        return $this->url;
    }

    function cache_filename ( $blog_id, $file, $param ) {
        $basename = "blog_id_{$blog_id}_" . md5( $file );
        if ( $param ) {
            $basename .= '_' . md5( $param );
        }
        $pinfo = pathinfo( $file );
        $extension = NULL;
        if ( isset ( $pinfo[ 'extension' ] ) ) {
            $extension = $pinfo[ 'extension' ];
        }
        $cache = $this->cache_dir . DIRECTORY_SEPARATOR . $basename . '.' . $extension;
        return $cache;
    }

    function get_smarty_template ( &$ctx, $data = NULL, $basename, $filemtime = NULL ) {
        $template = $ctx->_get_compile_path( 'var:' . $basename );
        if (! isset( $data ) ) {
            $blog = $ctx->stash( 'blog' );
            // if ( isset ( $blog ) ) {
            //     $template = $blog->site_path() . DIRECTORY_SEPARATOR . $template;
            // } else {
                if (! $this->no_database ) {
                    $ctx->force_compile = TRUE;
                }
                $template = dirname( $this->templates_c ) . DIRECTORY_SEPARATOR . $template;
            // }
        }
        if ( $filemtime && file_exists( $template ) ) {
            if ( $filemtime > filemtime( $template ) ) {
                $ctx->force_compile = TRUE;
            }
        }
        return $template;
    }

    function init_plugin_dir () {
        if ( $this->stash( 'init_plugin_dir' ) ) {
            return;
        }
        require_once( 'dynamicmtml.util.php' );
        require_once( 'class.dynamicmtml_plugin.php' );
        $plugins_config = array();   // Config from config.yaml or config.php
                                     // 'dynamicmtml' => array( 'id' => 'DynamicMTML', ...)
        $plugins_dir_path = array(); // 'dynamicmtml' => '/path/to/DynamicMTML.pack'
        $callback_dir = array();     // '/path/to/DynamicMTML.pack/php/callbacks', ...
        $l10n_dir = array();         // '/path/to/DynamicMTML.pack/php/l10n', ...
        $publisher_dir = array();    // '/path/to/DynamicMTML.pack/php/publishers', ...
        $callbacks = array();        // array ( 'post_init' => array( 'pluginkey' => 'routine_name' ), ...
        $blocks = array();           // array ( 'tagname' => array( 'pluginkey' => 'hdlr_name' ), ...
        $functions = array();        // array ( 'tagname' => array( 'pluginkey' => 'hdlr_name' ), ...
        $modifiers = array();        // array ( 'tagname' => array( 'pluginkey' => 'hdlr_name' ), ...
        $pluginpath = $this->config( 'pluginpath' );
        $spyc = __cat_file( array( dirname( __FILE__ ), 'extlib', 'spyc', 'spyc.php' ) );
        if ( file_exists( $spyc ) ) {
            require_once( $spyc );
        } else {
            $spyc = NULL;
        }
        sort( $pluginpath );
        foreach ( $pluginpath as $plugin_dir ) {
            if ( is_dir( $plugin_dir ) ) {
                if ( $dh = opendir( $plugin_dir ) ) {
                    while ( ( $dir = readdir( $dh ) ) !== FALSE ) {
                        if (! preg_match ( '/^\./', $dir ) ) {
                            $plugin_base = $plugin_dir . DIRECTORY_SEPARATOR . $dir;
                            $plugin = NULL;
                            $config = array();
                            if ( is_dir( $plugin_base ) ) {
                                $split_dirs = explode( DIRECTORY_SEPARATOR, $plugin_base );
                                $plugin_class = $split_dirs[ count( $split_dirs ) - 1 ];
                                $plugin_key = strtolower( $split_dirs[ count( $split_dirs ) - 1 ] );
                                if ( preg_match( '/\.pack$/', $plugin_class ) ) {
                                    $plugin_class = strtr( $plugin_class, '.', '_' );
                                    $plugin_key = preg_replace( '/\.pack$/', '', $plugin_key );
                                }
                                $plugins_dir_path[ $plugin_key ] = $plugin_base;
                                $plugin_php_dir = $plugin_base . DIRECTORY_SEPARATOR . 'php';
                                $config_php     = $plugin_php_dir . DIRECTORY_SEPARATOR . 'config.php';
                                $config_yaml    = $plugin_base . DIRECTORY_SEPARATOR . 'config.yaml';
                                if ( file_exists( $config_yaml ) ) {
                                    if ( $spyc ) {
                                        $config = Spyc::YAMLLoad( $config_yaml );
                                        if ( $config ) {
                                            $config[ 'plugin_path' ] = $plugin_base;
                                            if (! file_exists( $config_php ) ) {
                                                $config = $this->__adjust_callbacks( $config );
                                            }
                                            $plugins_config[ $plugin_key ] = $config;
                                        } else {
                                            $config = array();
                                        }
                                    }
                                }
                                if ( is_dir( $plugin_php_dir ) ) {
                                    if ( file_exists( $config_php ) ) {
                                        require_once( $config_php );
                                        $plugin_registry = NULL;
                                        if ( class_exists( $plugin_class ) ) {
                                            $plugin = new $plugin_class;
                                            $plugin->app = $this;
                                            if ( isset( $plugin->registry ) ) {
                                                $plugin_registry = $plugin->registry;
                                            }
                                            // $config = $plugin->registry;
                                        } else {
                                            $cfg_key = 'mt_plugin_' . $plugin_key;
                                            if ( isset( $$cfg_key ) ) {
                                                $plugin_registry = $$cfg_key;
                                            }
                                            // $config = $$cfg_key;
                                        }
                                        if ( $plugin_registry ) {
                                            if (! $config ) {
                                                $config = $plugin_registry;
                                            } else {
                                                foreach ( $plugin_registry as $key => $val ) {
                                                    $config[ $key ] = $val;
                                                }
                                            }
                                        }
                                        $config = $this->__adjust_callbacks( $config );
                                        $config[ 'plugin_path' ] = $plugin_base;
                                        $config[ 'plugin' ] = $plugin;
                                        // $plugins_config[ $plugin_key ] = $config;
                                    }
                                    if ( $config ) {
                                        if ( isset( $config[ 'callbacks' ] ) ) {
                                            $cb = $config[ 'callbacks' ];
                                            foreach ( $cb as $key => $val ) {
                                                if ( isset( $callbacks[ $key ] ) ) {
                                                    $cbs = $callbacks[ $key ]; //array
                                                    $cbs[ $plugin_key ] = $val;
                                                    $callbacks[ $key ] = $cbs;
                                                } else {
                                                    $callbacks[ $key ] = array( $plugin_key => $val );
                                                }
                                            }
                                        }
                                        if ( isset( $plugin ) ) {
                                            if ( is_object( $plugin ) ) {
                                                foreach ( $config as $cfg => $value ) {
                                                    $plugin->$cfg = $value;
                                                }
                                                $plugin->app = $this;
                                            }
                                        }
                                        $config[ 'plugin_key' ] = $plugin_key;
                                        $plugins_config[ $plugin_key ] = $config;
                                        if ( isset( $config[ 'tags' ] ) ) {
                                            $this->__adjust_tags( $config, $blocks, $functions, $modifiers );
                                        }
                                    }
                                    $plugin_callback_dir = $plugin_php_dir . DIRECTORY_SEPARATOR . 'callbacks';
                                    if ( is_dir( $plugin_callback_dir ) ) {
                                        array_push( $callback_dir, $plugin_callback_dir );
                                    }
                                    $plugin_l10n_dir = $plugin_php_dir . DIRECTORY_SEPARATOR . 'l10n';
                                    if ( is_dir( $plugin_l10n_dir ) ) {
                                        array_push( $l10n_dir, $plugin_l10n_dir );
                                    }
                                    $plugin_publisher_dir = $plugin_php_dir . DIRECTORY_SEPARATOR . 'publishers';
                                    if ( is_dir( $plugin_publisher_dir ) ) {
                                        array_push( $publisher_dir, $plugin_publisher_dir );
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        $this->stash( 'plugins_config', $plugins_config );
        $this->stash( 'plugins_directories', $plugins_dir_path );
        $this->stash( 'callback_dir', $callback_dir );
        $this->stash( 'l10n_dir', $l10n_dir );
        $this->stash( 'publisher_dir', $publisher_dir );
        $this->stash( 'plugins_callbacks', $callbacks );
        $this->stash( 'block_tags', $blocks );
        $this->stash( 'function_tags', $functions );
        $this->stash( 'modifiers', $modifiers );
        $this->stash( 'init_plugin_dir', 1 );
    }

    function __adjust_tags ( $config, &$blocks, &$functions, &$modifiers ) {
        $plugin_key = $config[ 'plugin_key' ];
        if ( isset( $config[ 'tags' ] ) ) {
            $config_tags = $config[ 'tags' ];
            if ( is_array( $config_tags ) ) {
                foreach ( $config_tags as $kind => $tags ) {
                    if ( is_array( $tags ) ) {
                        foreach ( $tags as $tag => $funk ) {
                            $tag = strtolower( $tag );
                            if ( preg_match( '/::/', $funk ) ) {
                                $path = explode( '::', $funk );
                                $funk = $path[ count( $path ) - 1 ];
                            }
                            if ( $kind === 'block' ) {
                                $tag = preg_replace( '/\?$/', '', $tag );
                                $blocks[ $tag ] = array( $plugin_key => $funk );
                            } elseif ( $kind === 'function' ) {
                                $functions[ $tag ] = array( $plugin_key => $funk );
                            } elseif ( $kind === 'modifier' ) {
                                $modifiers[ $tag ] = array( $plugin_key => $funk );
                            }
                        }
                    }
                }
            }
        }
    }

    function __adjust_callbacks ( $config ) {
        if ( isset( $config[ 'callbacks' ] ) ) {
            $new_config = array();
            $cb = $config[ 'callbacks' ];
            foreach ( $cb as $key => $val ) {
                if ( is_array( $val ) ) {
                    if ( isset( $val[ 'handler' ] ) ) {
                        $val = $val[ 'handler' ];
                    } else {
                        $val = NULL;
                    }
                }
                if ( $val ) {
                    if ( preg_match( '/::/', $val ) ) {
                        $path = explode( '::', $val );
                        $val = $path[ count( $path ) - 1 ];
                    }
                    $new_config[ $key ] = $val;
                }
            }
            if ( $new_config ) {
                $config[ 'callbacks' ] = $new_config;
            } else {
                unset( $config[ 'callbacks' ] );
            }
        }
        return $config;
    }

    function run_tasks ( $task = NULL, $prefix = 'tasks', $sleep = NULL ) {
        $do;
        require_once( 'dynamicmtml.util.php' );
        $mt_dir = dirname( $this->cfg_file );
        $mt_dir = preg_replace( '/[^A-Za-z0-9]+/', '_', $mt_dir );
        $lock_name = "mt-tasks-{$mt_dir}.lock";
        $temp_dir = $this->config( 'TempDir' );
        if (! $temp_dir ) {
            $temp_dir = __cat_dir( $this->powercms_files_dir, 'lock' ) ;
        }
        $lock_file = __cat_file( $temp_dir, $lock_name );
        if ( file_exists( $lock_file ) ) {
            $mtime = filemtime( $lock_file );
            $frequency = time() - $mtime;
            if ( $frequency < 86400 ) {
                $msg = $this->translate( 'The process is skipped the lock file [_1] exists.', $lock_file );
                $args = array( 'categoey' => $prefix,
                               'level' => 4,
                               'class' => 'system',
                            );
                $this->log( $msg, $args );
                return 0;
            }
        }
        $plugins_config = $this->stash( 'plugins_config' );
        $do_tasks = array();
        if (! isset( $plugins_config ) ) {
            return 0;
        }
        if (! is_array( $plugins_config ) ) {
            return 0;
        }
        if (! touch( $lock_file ) ) {
            $msg = $this->translate( 'Unable to secure lock for executing system tasks. Make sure your TempDir location ([_1]) is writable.', $temp_dir );
            $args = array( 'categoey' => $prefix,
                           'level' => 4,
                           'class' => 'system',
                        );
            $this->log( $msg, $args );
            return 0;
        }
        foreach ( $plugins_config as $config ) {
            if ( isset( $config[ $prefix ] ) ) {
                $plugins_tasks = $config[ $prefix ];
                foreach ( $plugins_tasks as $plugin_key => $settings ) {
                    if ( (! $task ) || ( $task === $plugin_key ) ) {
                        $label  = isset ( $settings[ 'label' ] ) ?
                                  $label = $this->translate( $settings[ 'label' ] ) : $label = $plugin_key;
                        $plugin = NULL;
                        $code   = NULL;
                        $plugin = $config[ 'plugin' ];
                        if ( $plugin && is_object( $plugin ) ) {
                            $code = $settings[ 'code' ];
                            if ( $code ) {
                                if ( preg_match( '/::/', $code ) ) {
                                    $path = explode( '::', $code );
                                    $code = $path[ count( $path ) - 1 ];
                                }
                                if (! method_exists( $plugin, $code ) ) {
                                    $plugin = NULL;
                                }
                            } else {
                                $plugin = NULL;
                            }
                        }
                        if (! $plugin ) {
                            $plugin = __cat_file( $config[ 'plugin_path' ],
                                                  array( 'php', $prefix, "{$plugin_key}.php" ) );
                        }
                        if ( $plugin ) {
                            if ( $do ) {
                                if ( $sleep ) sleep ( $sleep );
                            }
                            if ( $prefix === 'tasks' ) {
                                $frequency = isset ( $settings[ 'frequency' ] ) ?
                                             $frequency = $settings[ 'frequency' ] : $frequency = 86400;
                                if ( $plugin_key === 'FuturePost' ) {
                                    $futurepostfrequency = $this->config( 'FuturePostFrequency' );
                                    if ( $futurepostfrequency ) {
                                        $frequency = $futurepostfrequency;
                                        $frequency *= 60;
                                    }
                                }
                                $session = $this->get_by_key( 'Session', array( 'id' => "Task:{$plugin_key}",
                                                                                'kind' => 'PT' ) );
                                $start = $session->start;
                                if ( (! $start ) || ( ( time() - $start ) > $frequency ) ) {
                                    $run_task = FALSE;
                                    $task_error = FALSE;
                                    if ( is_object ( $plugin ) ) {
                                        if ( $code ) {
                                            try {
                                                if ( $do = $plugin->$code( $this ) ) {
                                                    array_push( $do_tasks, $label );
                                                }
                                                $run_task = TRUE;
                                            } catch ( Exception $e ) {
                                                $task_error = $e->getMessage();
                                            }
                                        }
                                    } else {
                                        if ( file_exists( $plugin ) ) {
                                            require_once( $plugin );
                                        }
                                        $function = "{$prefix}_{$plugin_key}";
                                        if ( function_exists( $function ) ) {
                                            try {
                                                if ( $do = $function( $this ) ) {
                                                    array_push( $do_tasks, $label );
                                                }
                                                $run_task = TRUE;
                                            } catch ( Exception $e ) {
                                                $task_error = $e->getMessage();
                                            }
                                        }
                                    }
                                    if ( $run_task ) {
                                        $session->start = time();
                                        if ( $session->_saved ) {
                                            $session->Update();
                                        } else {
                                            $session->Save();
                                        }
                                    }
                                }
                            } elseif ( $prefix === 'task_workers' ) {
                                $class = isset ( $settings[ 'class' ] ) ?
                                         $class = $settings[ 'class' ] : $class = NULL;
                                if (! $class ) break;
                                $func_map = $this->load( 'Ts_Func_Map', array( 'ts_funcmap_funcname' => $class ), array( 'limit' => 1 ) );
                                if (! $func_map ) break;
                                $func_id = $func_map->ts_funcmap_funcid;
                                if (! $func_id ) break;
                                $jobs = $this->load( 'Ts_Job', array( 'ts_job_funcid' => $func_id ),
                                                               array( 'sort' => 'priority','direction' => 'ascend' ) );
                                if (! count( $jobs ) ) break;
                                if ( is_object ( $plugin ) ) {
                                    if ( $code && $jobs ) {
                                        try {
                                            if ( $do = $plugin->$code( $this, $jobs ) ) {
                                                array_push( $do_tasks, $label );
                                            }
                                            $run_task = TRUE;
                                        } catch ( Exception $e ) {
                                            $task_error = $e->getMessage();
                                        }
                                    }
                                } else {
                                    if ( file_exists( $plugin ) ) {
                                        require_once( $plugin );
                                    }
                                    $function = "{$prefix}_{$plugin_key}";
                                    if ( function_exists( $function ) ) {
                                        if ( $jobs ) {
                                            try {
                                                if ( $do = $function( $this, $jobs ) ) {
                                                    array_push( $do_tasks, $label );
                                                }
                                                $run_task = TRUE;
                                            } catch ( Exception $e ) {
                                                $task_error = $e->getMessage();
                                            }
                                        }
                                    }
                                }
                            }
                            if ( $task_error ) {
                                $msg = $this->translate( 'Error during task \'[_1]\': [_2]', array( $label, $task_error ) );
                                $args = array( 'categoey' => $prefix,
                                               'level' => 4,
                                               'class' => 'system',
                                            );
                                $this->log( $msg, $args );
                            }
                        }
                    }
                }
            }
        }
        if ( $do_tasks ) {
            $msg = $this->translate( 'Scheduled Tasks Update' );
            $ts = gmdate( "Y-m-d H:i:s" );
            $metadata = "[{$ts}] " . $this->translate( 'The following tasks were run:' );
            $ts = gmdate( "Y-m-d H:i:s" );
            $metadata .= ' ' . join( ', ', $do_tasks );
            $args = array( 'categoey' => $prefix,
                           'level' => 1,
                           'class' => 'system',
                           'metadata' => $metadata,
                        );
            $this->log( $msg, $args );
        }
        if ( file_exists( $lock_file ) ) {
            unlink ( $lock_file );
        }
        return $do;
    }

    function run_workers ( $sleep = 5, $worker = NULL ) {
        return $this->run_tasks( $worker, 'task_workers', $sleep );
    }

    public function &context () {
        // TODO:
        require_once( 'dynamicmtml.util.php' );
        $ctx = $this->ctx;
        if ( isset( $ctx ) ) return $ctx;
        $mtphpdir = $this->config( 'PHPDir' );
        $mtlibdir = $this->config( 'PHPLibDir' );
        require_once( 'MTViewer.php' );
        $ctx = new MTViewer( $this );
        $ctx->mt =& $this;
        $ctx->compile_check = 1;
        $ctx->caching = FALSE;
        $ctx->plugins_dir[] = $mtlibdir;
        $ctx->plugins_dir[] = $mtphpdir . DIRECTORY_SEPARATOR . "plugins";
        if ( $this->debugging ) {
            $ctx->debugging_ctrl = 'URL';
            $ctx->debug_tpl = __cat_file( $mtphpdir, array( 'extlib', 'smarty', 'libs', 'debug.tpl' ) );
        }
        return $ctx;
    }

    function run_callbacks ( $callback, $mt = NULL, &$ctx = NULL, &$args = NULL, &$content = NULL ) {
        $class = get_class( $this );
        $callback_dir = array();
        $plugins_callbacks = array();
        if ( $class != 'DynamicMTML' ) {
            $callback_dir = $this->app->stash( 'callback_dir' );
            $plugins_callbacks = $this->app->stash( 'plugins_callbacks' );
        } else {
            $callback_dir = $this->stash( 'callback_dir' );
            $plugins_callbacks = $this->stash( 'plugins_callbacks' );
        }
        $do_filter = TRUE;
        if (! $args ) {
            $args = $this->get_args();
        }
        $orig_contenttype = NULL;
        if ( isset ( $args ) ) {
            if ( isset( $args[ 'contenttype' ] ) ) {
                $orig_contenttype = $args[ 'contenttype' ];
            }
        }
        if ( isset( $ctx ) ) {
            $ctx->stash( 'bootstrapper', $this );
        }
        if ( is_array( $plugins_callbacks ) ) {
            if ( isset( $plugins_callbacks[ $callback ] ) ) {
                $callbacks = $plugins_callbacks[ $callback ];
                if ( $callbacks ) {
                    foreach ( $callbacks as $plugin => $method ) {
                        $component = $this->component( $plugin );
                        if ( method_exists( $component, $method ) ) {
                            $res = $component->$method( $mt, $ctx, $args, $content );
                            if (! $res ) {
                                $do_filter = FALSE;
                            }
                            if ( isset ( $mt ) ) {
                                $this->set_args_all( $args );
                            }
                        }
                    }
                }
            }
        }
        if ( is_array( $callback_dir ) ) {
            foreach ( $callback_dir as $plugin_callback_dir ) {
                if ( is_dir( $plugin_callback_dir ) ) {
                    $dirs = explode( DIRECTORY_SEPARATOR, $plugin_callback_dir );
                    $plugin = strtolower( $dirs[ count( $dirs ) - 3 ] );
                    $function = $plugin . '_' . $callback;
                    $function = strtr( $function, '.', '_' );
                    $require = $plugin_callback_dir . DIRECTORY_SEPARATOR . $function . '.php';
                    if ( file_exists( $require ) ) {
                        require_once $require;
                        $res = $function( $mt, $ctx, $args, $content );
                        if (! $res ) {
                            $do_filter = FALSE;
                        }
                        if ( isset ( $mt ) ) {
                            $this->set_args_all( $args );
                        }
                    }
                }
            }
        }
        if ( isset( $ctx ) ) {
            if ( isset( $orig_contenttype ) ) {
                if ( $orig_contenttype != $args[ 'contenttype' ] ) {
                    $ctx->stash( 'content_type', $args[ 'contenttype' ] );
                }
            }
            $ctx->stash( 'bootstrapper', $this );
        }
        return $do_filter;
    }

    function set_args_all ( &$args ) {
        foreach ( $args as $key => $val ) {
            $args[ $key ] = $this->stash( $key );
            global $$key;
            $$key = $args[ $key ];
        }
    }

    function set_args ( $args, $param, $set = NULL ) {
        if ( isset( $set ) ) {
            global $$set;
            $$set = $args[ $param ];
        } else {
            global $$param;
            $$param = $args[ $param ];
        }
    }

    function echo_file_get_contents ( $file, $limit = 524288, $buff = 8192 ) {
        if ( filesize( $file ) < $limit ) {
            echo file_get_contents( $file );
        } else {
            $fp = fopen( $file, "r" );
            while (! feof( $fp ) ) {
                echo fread( $fp, $buff );
            }
            fclose( $fp );
        }
    }

    function adjust_file ( $file, $indexes, $original, $replace ) {
        if ( DIRECTORY_SEPARATOR != '/' ) {
            $file = str_replace( '\\\\', '\\', $file );
        } else {
            $file = str_replace( '//', '/', $file );
        }
        $file = strtr( $file, '../', '' );
        if ( DIRECTORY_SEPARATOR != '/' ) {
            $file = strtr( $file, '/', DIRECTORY_SEPARATOR );
        }
        if ( is_dir ( $file ) ) {
            foreach ( explode( ',', $indexes ) as $index ) {
                if ( file_exists( $file . $index ) ) {
                    $file = $file . $index;
                    break;
                } elseif ( file_exists( urldecode( $file ) . $index ) ) {
                    $file = urldecode( $file ) . $index;
                    break;
                }
            }
        } elseif ( file_exists( $file ) ) {
            $file = $file;
        } elseif ( file_exists( urldecode( $file ) ) ) {
            $file = urldecode( $file );
        }
        if ( $original && $replace ) {
            $file = preg_replace( '/' . preg_quote( $original, '/' ) . '/', $replace, $file );
        }
        if ( preg_match( '/' . preg_quote( DIRECTORY_SEPARATOR, '/' ) . '$/', $file ) ) {
            $file = preg_replace( '/\/$/', '', $file );
            if ( is_dir ( $file ) ) {
                $file = $file . DIRECTORY_SEPARATOR . 'index.html';
            }
        }
        $file = urldecode( $file );
        if ( DIRECTORY_SEPARATOR != '/' ) {
            $file = strtr( $file, '\\\\', '\\' );
        } else {
            $file = strtr( $file, '//', '/' );
        }
        return $file;
    }

    function check_excludes ( $file, $excludes = 'php', $mt_dir = NULL, $static_path = NULL ) {
        $self = $_SERVER[ 'SCRIPT_FILENAME' ];
        $self = preg_quote( $self, '/' );
        if ( preg_match( "/^$self/i", $file ) ) {
            exit();
        }
        if ( $mt_dir && $static_path ) {
            $mt_dir = $this->__add_slash( $mt_dir );
            $check_mt = preg_quote( $mt_dir, '/' );
            $check_static = preg_quote( $static_path, '/' );
            if ( preg_match( "/^$check_mt/i", $file ) ) {
                if (! preg_match( "/^$check_static/i", $file ) ) {
                    exit();
                }
            }
        }
        $basename = explode(DIRECTORY_SEPARATOR, $file);
        $basename = end($basename);
        if ($basename[0] === '.') {
            exit();
        }
        foreach ( explode( ',', $excludes ) as $extension ) {
            if ( preg_match( "/\.$extension\$/", $basename ) ) {
                exit();
            }
        }
    }

    function __add_slash ( $path, $add = TRUE ) {
        $sep = preg_quote( DIRECTORY_SEPARATOR, '/' );
        $path = preg_replace( "/$sep$/", '', $path );
        if ( $add ) {
            $path .= DIRECTORY_SEPARATOR;
        }
        return $path;
    }

    function include_php ( $file ) {
        if ( file_exists( $file ) ) {
            if ( preg_match( "/\.php$/", $file ) ) {
                include( $file );
                exit();
            }
        }
    }

    function get_mime_type ( $extension ) {
        $extension = preg_replace( '/^\./', '', strtolower( $extension ) );
        if ( isset( $_SERVER[ 'HTTP_USER_AGENT' ] ) ) {
            if ( preg_match( '/\ADoCoMo\/2\.0 /', $_SERVER[ 'HTTP_USER_AGENT' ] ) ) {
                if ( $extension === 'html' ) {
                    return 'application/xhtml+xml';
                }
            }
        }
        $mime_type = array (
            'css'     => 'text/css',
            'html'    => 'text/html',
            'mtml'    => 'text/html',
            'xhtml'   => 'application/xhtml+xml',
            'htm'     => 'text/html',
            'txt'     => 'text/plain',
            'rtx'     => 'text/richtext',
            'tsv'     => 'text/tab-separated-values',
            'csv'     => 'text/csv',
            'hdml'    => 'text/x-hdml; charset=Shift_JIS',
            'xml'     => 'application/xml',
            'rdf'     => 'application/rss+xml',
            'xsl'     => 'text/xsl',
            'mpeg'    => 'video/mpeg',
            'mpg'     => 'video/mpeg',
            'mpe'     => 'video/mpeg',
            'qt'      => 'video/quicktime',
            'avi'     => 'video/x-msvideo',
            'movie'   => 'video/x-sgi-movie',
            'qt'      => 'video/quicktime',
            'ice'     => 'x-conference/x-cooltalk',
            'svr'     => 'x-world/x-svr',
            'vrml'    => 'x-world/x-vrml',
            'wrl'     => 'x-world/x-vrml',
            'vrt'     => 'x-world/x-vrt',
            'spl'     => 'application/futuresplash',
            'hqx'     => 'application/mac-binhex40',
            'doc'     => 'application/msword',
            'pdf'     => 'application/pdf',
            'ai'      => 'application/postscript',
            'eps'     => 'application/postscript',
            'ps'      => 'application/postscript',
            'ppt'     => 'application/vnd.ms-powerpoint',
            'rtf'     => 'application/rtf',
            'dcr'     => 'application/x-director',
            'dir'     => 'application/x-director',
            'dxr'     => 'application/x-director',
            'js'      => 'application/javascript',
            'dvi'     => 'application/x-dvi',
            'gtar'    => 'application/x-gtar',
            'gzip'    => 'application/x-gzip',
            'latex'   => 'application/x-latex',
            'lzh'     => 'application/x-lha',
            'swf'     => 'application/x-shockwave-flash',
            'sit'     => 'application/x-stuffit',
            'tar'     => 'application/x-tar',
            'tcl'     => 'application/x-tcl',
            'tex'     => 'application/x-texinfo',
            'texinfo' => 'application/x-texinfo',
            'texi'    => 'application/x-texi',
            'src'     => 'application/x-wais-source',
            'zip'     => 'application/zip',
            'au'      => 'audio/basic',
            'snd'     => 'audio/basic',
            'midi'    => 'audio/midi',
            'mid'     => 'audio/midi',
            'kar'     => 'audio/midi',
            'mpga'    => 'audio/mpeg',
            'mp2'     => 'audio/mpeg',
            'mp3'     => 'audio/mpeg',
            'ra'      => 'audio/x-pn-realaudio',
            'ram'     => 'audio/x-pn-realaudio',
            'rm'      => 'audio/x-pn-realaudio',
            'rpm'     => 'x-pn-realaudio-plugin',
            'wav'     => 'audio/x-wav',
            'bmp'     => 'image/bmp',
            'gif'     => 'image/gif',
            'jpeg'    => 'image/jpeg',
            'jpg'     => 'image/jpeg',
            'jpe'     => 'image/jpeg',
            'png'     => 'image/png',
            'tiff'    => 'image/tiff',
            'tif'     => 'image/tiff',
            'pnm'     => 'image/x-portable-anymap',
            'ras'     => 'image/x-cmu-raster',
            'pnm'     => 'image/x-portable-anymap',
            'pbm'     => 'image/x-portable-bitmap',
            'pgm'     => 'image/x-portable-graymap',
            'ppm'     => 'image/x-portable-pixmap',
            'rgb'     => 'image/x-rgb',
            'xbm'     => 'image/x-xbitmap',
            'xls'     => 'application/vnd.ms-excel',
            'xpm'     => 'image/x-pixmap',
            'xwd'     => 'image/x-xwindowdump',
        );
        return isset( $mime_type[ $extension ] ) ? $mime_type[ $extension ] : 'text/plain';
    }

    function type_text ( $contenttype ) {
        $type = explode( '/', $contenttype );
        if ( $type[0] === 'text' || preg_match( '/xml$/', $contenttype ) ) {
            return 1;
        }
        return 0;
    }

    function chomp_dir ( $dir ) {
        if ( DIRECTORY_SEPARATOR != '/' ) {
            $dir = preg_replace( '/\\$/', '', $dir );
            $dir = strtr( $dir, '\\\\', '\\' );
        } else {
            $dir = preg_replace( '/\/$/', '', $dir );
            $dir = strtr( $dir, '//', '/' );
        }
        return $dir;
    }

    function get_author ( &$ctx, $timeout = NULL, $permission = NULL ) {
        if ( ! $ctx ) {
            return NULL;
        }
        if ( $client_author = $ctx->stash( 'client_author' ) ) {
            $this->stash( 'user', $client_author );
        }
        if ( $this->config( 'UserSessionTimeoutNoCheck' ) ) {
            unset( $timeout );
        }
        $session = $this->session();
        if (! isset( $session ) ) { return NULL; }
        if ( isset( $timeout ) ) {
            $session_start = $session->start;
            $elapsed_time = time() - $session_start;
            if ( $elapsed_time > $timeout ) {
                $this->logout();
                $this->user = NULL;
                $session->Delete();
                return NULL;
            }
        }
        if ( $client_author ) return $client_author;
        $data = $session->data();
        if (! isset( $data ) ) { return NULL; }
        $author_id = $data[ 'author_id' ];
        if (! $author_id ) { return NULL; }
        require_once( 'class.mt_author.php' );
        $_author = new Author;
        $where = " author_id = $author_id and author_status = 1";
        $extra = array(
            'limit' => 1,
        );
        $client_author = $_author->Find( $where, FALSE, FALSE, $extra );
        if ( isset( $client_author ) ) {
            $client_author = $client_author[ 0 ];
            if ( isset( $client_author ) ) {
                $language = $client_author->preferred_language;
                $language = strtr( $language, '-', '_' );
                if ( $language === 'en_us' ) {
                    $language = 'en';
                }
                $l10n_dir = $this->stash( 'l10n_dir' );
                if ( is_array( $l10n_dir ) ) {
                    foreach ( $l10n_dir as $plugin_l10n_dir ) {
                        if ( is_dir( $plugin_l10n_dir ) ) {
                            $l10n_file = $plugin_l10n_dir . DIRECTORY_SEPARATOR . 'l10n_' . $language . '.php';
                            if ( file_exists( $l10n_file ) ) {
                                require( $l10n_file );
                                if ( is_array( $Lexicon ) ) {
                                    $this->add_lexicon( $language, $Lexicon );
                                    unset( $Lexicon );
                                }
                            }
                        }
                    }
                }
                $author_id = $client_author->id;
                $ctx->stash( 'client_author_id', $author_id );
                $ctx->stash( 'client_author', $client_author );
                $this->stash( 'user', $client_author );
                # Require permission check?
                # => $this->can_do( $ctx, 'permission' );
                if ( isset( $permission ) ) {
                    if (! $this->can_do( $ctx, $permission ) ) {
                        $ctx->stash( 'client_author_id', NULL );
                        $ctx->stash( 'client_author', NULL );
                        return NULL;
                    }
                }
                return $client_author;
            }
        }
        return NULL;
    }

    function login () {
        if ( $this->request_method != 'POST' ) {
            return 0;
        }
        $base = $this->base;
        $q_base = preg_quote( $base, '/' );
        $referer = $_SERVER[ 'HTTP_REFERER' ];
        if ( $referer && (! preg_match( "/^$q_base/", $referer ) ) ) {
            return 0;
        }
        $ctx = $this->ctx;
        $username = $this->param( 'username' );
        $password = $this->param( 'password' );
        $remember = $this->param( 'remember' );
        $return_url = $this->param( 'return_url' );
        if ( preg_match( '/^http/', $return_url ) ) {
            if (! preg_match( "/^$q_base/", $return_url ) ) {
                $return_url = '';
            }
        } elseif ( $return_url ) {
            if (! preg_match( '!^/!', $return_url ) ) {
                $return_url = '/' . $return_url;
            }
            $return_url = $base . $return_url;
        }
        if ( $this->is_valid_author( $ctx, $username, $password ) ) {
            if ( $user = $this->user ) {
                $session = NULL;
                if (! $session = $this->session ) {
                    $session = $this->session();
                }
                // Save or Touch mt_session
                if (! isset( $session ) ) {
                    require_once( 'class.mt_session.php' );
                    $session = new Session;
                    $sessid = $this->make_magic_token();
                    $email = $user->email;
                    $name = $user->name;
                    $author_id = $user->id;
                    $terms = array ( 'id'    => $sessid,
                                     'kind'  => 'SI',
                                     'email' => $email,
                                     'name'  => $name,
                                    );
                    $session->set_values( $terms );
                    $meta = array( 'author_id' => $author_id );
                    $session->data( $meta );
                    if ( $session->has_column( 'author_id' ) ) {
                        // PowerCMS
                        $session->author_id = $author_id;
                    }
                    $this->stash( 'user_cookie', 'mt_commenter' );
                } else {
                    $sessid = $session->id;
                }
                $expires = NULL;
                if ( $remember ) {
                    $expires = time() + 60 * 60 * 24 * 365;
                }
                if (! $path = $this->config( 'CookiePath' ) ) {
                    if ( $this->blog ) {
                        $site_url = $this->blog->site_url();
                        $path = preg_replace( '!^https{0,1}://.{1,}?(/)!', '/', $site_url );
                    } else {
                        $path = NULL;
                    }
                }
                $user_cookie = $this->user_cookie;
                if ( $user_cookie != 'sessid' ) {
                    if (! setcookie( $user_cookie, $sessid, $expires, $path ) ) {
                        $this->stash( 'user_cookie', 'sessid' );
                    }
                }
                $session->start = time();
                $session->Save();
            }
            if ( $this->user_cookie == 'sessid' ) {
                if (! $return_url ) {
                    $return_url = $this->base . $this->path . $this->script;
                }
                if (! preg_match( '/\?/', $return_url ) ) {
                    $return_url .= '?sessid=' . $sessid;
                } else {
                    $return_url .= '&sessid=' . $sessid;
                }
            }
            if ( $return_url ) {
                $this->redirect( $return_url );
                exit();
            }
            return 1;
        }
        return 0;
    }

    function logout () {
        if (! $path = $this->config( 'CookiePath' ) ) {
            $path = NULL;
        }
        if ( $session = $this->session ) {
            if ( $session ) {
                $session->Delete();
            }
        }
        setcookie( 'mt_user', '', time() - 1800, $path );
        setcookie( 'mt_commenter', '', time() - 1800, $path );
        setcookie( 'mt_blog_user', '', time() - 1800, $path );
        setcookie( 'commenter_id', '', time() - 1800, $path );
        setcookie( 'commenter_name', '', time() - 1800, $path );
        if ( $sessid = $this->param( 'sessid' ) ) {
            $query = $this->query_string;
            if ( $query ) {
                if ( preg_match( '/(^.*&{0,1})(sessid=.*$)/', $query, $match ) ) {
                    $query = $this->delete_parameter( $query, 'sessid' );
                    $url  = $this->base . $this->path . $this->script;
                    $url .= '?' . $query;
                    $this->redirect( $url );
                }
            }
        }
        return 1;
    }

    function translate ( $str, $params = NULL ) {
        if ( ( $params !== NULL ) && (! is_array( $params ) ) ) {
            $params = array( $params );
        }
        return $this->translate_phrase( $str, $params );
    }

    function translate_phrase ( $str, $params = NULL ) {
        // Translate phrase using user's language.
        $user = $this->user();
        $lang = $this->config( 'DefaultLanguage' );
        if ( $lang === 'jp' ) {
            $lang = 'ja';
        } elseif ( $lang === 'en_us' ) {
            $lang = 'en';
        }
        if ( $user ) {
            $language = $user->preferred_language;
            $language = strtr( $language, '-', '_' );
            if ( $language === 'en_us' ) {
                $language = 'en';
            }
        } else {
            $language = $lang;
        }
        $Lexicon_lang = 'Lexicon_' . $lang;
        if ( $lang === $language ) {
            global $$Lexicon_lang;
        } else {
            require_once( 'l10n' . DIRECTORY_SEPARATOR . 'l10n_' . $language . '.php' );
            $Lexicon_lang = 'Lexicon_' . $language;
            global $$Lexicon_lang;
        }
        $l10n_str = isset( ${$Lexicon_lang}[ $str ] ) ? ${$Lexicon_lang}[ $str ] : ( isset( $Lexicon[ $str ] ) ? $Lexicon[ $str ] : $str );
        if ( extension_loaded( 'mbstring' ) ) {
            $str = mb_convert_encoding( $l10n_str, mb_internal_encoding(), "UTF-8" );
        } else {
            $str = $l10n_str;
        }
        return translate_phrase_param( $str, $params );
    }

    function add_lexicon ( $lang, $params = array() ) {
        $Lexicon_lang = 'Lexicon_' . $lang;
        global $$Lexicon_lang;
        if ( is_array( $params ) ){
            foreach ( $params as $key => $val ) {
                ${$Lexicon_lang}[ $key ] = $val;
            }
        }
    }

    function escape ( $string, $urldecode = NULL ) {
        if ( $urldecode ) {
            $string = urldecode( $string );
        }
        if ( $mt = $this->mt ) {
            return $this->mt->db()->escape( $string );
        } else {
            return addslashes( $string );
        }
    }

    function session ( $sessid = NULL, $name = NULL ) {
        if ( $this->stash( 'no_database' ) ) {
            return NULL;
        }
        if ( $session = $this->stash( 'session' ) ) {
            return $session;
        }
        $vars =& $this->ctx->__stash[ 'vars' ];
        require_once( 'class.mt_session.php' );
        $_session = new Session;
        $extras = array(
                'order by' => '`session_start` DESC',
                'limit' => 1,
        );
        if ( (! $sessid ) || (! $name ) ) {
            $session_cookie = $this->get_session_cookie();
            if (! isset( $session_cookie ) ) {
                if ( $user = $this->user ) {
                    $this->stash( 'user_cookie', 'mt_commenter' );
                    $username = $user->name;
                    $where = "session_name ='{$username}' and session_kind='SI'";
                    $username = $this->escape( $username );
                    $session = $_session->Find( $where, FALSE, FALSE, $extras );
                    if ( isset( $session ) ) {
                        $session = $session[0];
                        $vars[ 'magic_token' ] = $session->id;
                        $this->stash( 'session', $sess_obj );
                        return $session;
                    }
                }
                return NULL;
            }
            $sessid = $session_cookie[ 'sessid' ];
            $name = $session_cookie[ 'name' ];
        }
        if ( (! $sessid ) || (! $name ) ) {
            return NULL;
        }
        $sessid = $this->escape( $sessid );
        $where = "session_id ='{$sessid}' and session_kind='{$name}'";
        $session = $_session->Find( $where, FALSE, FALSE, $extras );
        if ( isset( $session ) ) {
            $session = $session[0];
            $vars[ 'magic_token' ] = $session->id;
        }

        $this->stash( 'session', $session );
        return $session;
    }

    function cookie_val ( $name ) {
        if ( isset( $_COOKIE[ $name ] ) ) {
            return $_COOKIE[ $name ];
        }
        return '';
    }

    function get_session_cookie () {
        $cookie = NULL;
        $sessid = NULL;
        $name = NULL;
        if ( $cookie = $this->cookie_val( 'mt_commenter' ) ) {
            $sessid = $cookie;
            $name = 'SI';
            $this->stash( 'user_cookie', 'mt_commenter' );
        } elseif ( $cookie = $this->cookie_val( 'mt_user' ) ) {
            if ( preg_match( '/^(.*?)::(.*?)::.*$/', $cookie, $match ) ) {
                $sessid = $match[ 2 ];
            }
            $name = 'US';
            $this->stash( 'user_cookie', 'mt_user' );
        } elseif ( $cookie = $this->cookie_val( 'mt_blog_user' ) ) {
            if ( preg_match( "/sid:.'(.*?).'.*?$/", $cookie, $match ) ) {
                $sessid = $match[ 1 ];
                $this->stash( 'user_cookie', 'mt_blog_user' );
            }
            $name = 'US';
        } elseif( $cookie = $_REQUEST[ 'sessid' ] ) {
            $sessid = $cookie;
            $name = 'US';
            $this->stash( 'user_cookie', 'sessid' );
        } else {
            return NULL;
        }
        return array ( 'name' => $name, 'sessid' => $sessid );
    }

    function make_magic_token () {
        return md5( uniqid( '', 1 ) );
    }

    function current_magic () {
        if ( $this->user() ) {
            $session = $this->session();
            if ( isset( $session ) ) {
                return $session->id;
            }
        }
    }

    function validate_magic () {
        $magic_token = $this->param( 'magic_token' );
        if ( $magic_token ) {
            if ( $this->current_magic() == $magic_token ) {
                return 1;
            }
        }
        return 0;
    }

    function component ( $component ) {
        $component = strtolower( $component );
        if ( $this->stash( 'component:' . $component ) ) {
            return $this->stash( 'component:' . $component );
        }
        $plugins_config = $this->stash( 'plugins_config' );
        if ( isset ( $plugins_config[ $component ] ) ) {
            if ( isset ( $plugins_config[ $component ][ 'plugin' ] ) ) {
                if ( $plugin = $plugins_config[ $component ][ 'plugin' ] ) {
                    $plugin->app = $this;
                    return $plugin;
                }
            }
            require_once( 'class.dynamicmtml_plugin.php' );
            $plugin = new MTPlugin;
            $config = $plugins_config[ $component ];
            foreach ( $config as $cfg => $value ) {
                $plugin->$cfg = $value;
            }
            $plugin->app = $this;
            //$plugin->callback_dir = $this->stash( 'callback_dir' );
            $this->stash( 'component:' . $component, $plugin );
            return $plugin;
        }
        return FALSE;
    }

    function plugin_get_config_value ( $component, $key, $blog_id = NULL ) {
        // $component = strtolower( $component );
        $plugin = $this->component( $component );
        $get_from = NULL;
        if ( $blog_id ) {
            $get_from = "configuration:blog:$blog_id";
        }
        $data = $plugin->get_config_value( $key, $get_from );
        if ( $data ) return $data;
        return NULL;
    }

    function get_plugin_config ( $plugin, $key = 'version' ) {
        $plugin = strtolower( $plugin );
        $plugin = $this->component( $plugin );
        return $plugin->$key;
    }

    function is_valid_author ( &$ctx, $author, $password, $permission = NULL, $set_context = 1 ) {
        if ( (! $author ) || (! $password ) ) return 0;
        if ( is_string( $author ) ) {
            $author = $this->escape( $author );
            require_once( 'class.mt_author.php' );
            $_author = new Author;
            $where = " author_name = '{$author}' and author_status = 1";
            $extra = array(
                'limit' => 1,
            );
            $client_author = $_author->Find( $where, FALSE, FALSE, $extra );
            if ( isset( $client_author ) ) {
                $client_author = $client_author[ 0 ];
            }
        } else {
            $client_author = $author;
        }
        if ( isset( $client_author ) ) {
            $real_pass = $client_author->password;
            $password = crypt( $password, $real_pass );
            if ( $real_pass === $password ) {
                if ( isset( $permission ) ) {
                    if (! $this->can_do( $ctx, $permission, $client_author ) ) {
                        $client_author = NULL;
                    }
                }
                if ( isset( $client_author ) ) {
                    if ( $set_context ) {
                        $author_id = $client_author->id;
                        $ctx->stash( 'client_author_id', $author_id );
                        $ctx->stash( 'client_author', $client_author );
                        $this->stash( 'user', $client_author );
                    }
                    return 1;
                }
            }
        }
        return 0;
    }

    function can_do ( $ctx, $permission, $author = NULL, $blog = NULL ) {
        if (! isset( $blog ) ) {
            $blog = $ctx->stash( 'blog' );
        }
        if (! isset( $author ) ) {
            $author = $ctx->stash( 'client_author' );
        }
        $blog_id   = ( is_numeric( $blog ) ) ? $blog : $blog->id;
        $author_id = ( is_numeric( $author ) ) ? $author : $author->id;
        if ( $permission === 'comment' ) {
            if ( is_numeric( $author ) ) {
                $author = get_user( $ctx );
            }
            if ( isset( $author ) ) {
                if ( $author->auth_type != 'MT' ) {
                    return 1;
                }
            } else {
                return 0;
            }
        }
        require_once( 'class.mt_permission.php' );
        $Permission = new Permission;
        $where = "permission_author_id = '{$author_id}'"
               . " and ("
               . " permission_blog_id = '{$blog_id}'"
               . " or permission_blog_id = 0";
        if (! is_numeric( $blog ) ) {
            if ( $blog->parent_id ) {
                $website_id = $blog->parent_id;
                $where .= " or permission_blog_id = {$website_id}";
            }
        }
        $where .= ")";
        $results = $Permission->Find( $where );
        if ( empty ( $results ) ) {
            return 0;
        }
        foreach ( $results as $perm_obj ) {
            if (! isset( $permission ) ) {
                if ( $perm_obj->blog_id == $blog_id ) {
                    return 1;
                    break;
                } elseif ( preg_match( "/('administer'|'administer_website')/",
                        $perm_obj->permission_permissions, $match ) ) {
                    return 1;
                    break;
                }
            } else {
                if ( preg_match( "/('$permission'|'administer'|'administer_website'|'administer_blog')/",
                        $perm_obj->permission_permissions, $match ) ) {
                    return 1;
                    break;
                }
            }
        }
        return 0;
    }

    function get_agent ( $wants = 'Agent', $like = NULL, $exclude = NULL ) {
        require_once( 'dynamicmtml.util.php' );
        return get_agent ( $wants, $like, $exclude );
    }

    function get_param ( $param ) {
        $qurey = NULL;
        if ( isset ( $_GET[ $param ] ) ) {
            $qurey = $_GET[ $param ];
        } elseif ( isset ( $_POST[ $param ] ) ) {
            $qurey = $_POST[ $param ];
        }
        return $qurey;
    }

    function delete_params ( $params = array() ) {
        foreach ( $params as $param ) {
            if ( $_GET[ $param ] || $_POST[ $param ] ) {
                $this->delete_param( $param );
            }
        }
    }

    function delete_param ( $param ) {
        $redirect_query_string = $_SERVER[ 'REDIRECT_QUERY_STRING' ];
        $query_string = $_SERVER[ 'QUERY_STRING' ];
        if ( $redirect_query_string ) {
            $_SERVER[ 'REDIRECT_QUERY_STRING' ] = $this->delete_parameter( $redirect_query_string, $param );
        }
        if ( $query_string ) {
            $_SERVER[ 'QUERY_STRING' ] = $this->delete_parameter( $query_string, $param );
        }
        if ( isset ( $_GET[ $param ] ) ) {
            $_GET[ $param ] = NULL;
        } elseif ( isset ( $_POST[ $param ] ) ) {
            $_POST[ $param ] = NULL;
        }
    }

    function delete_parameter ( $query, $param ) {
        parse_str( $query, $params );
        $query = '&';
        foreach ( $params as $key => $val ) {
            if ( $key != $param ) {
                $query .= $key . '=' . $val . '&';
            }
        }
        $query = preg_replace( '/&$/', '', $query );
        if ( preg_match( '/(^.*&)(' . $param . '=.*$)/', $query, $match ) ) {
            $pre = $match[1];
            $after;
            $next = $match[2];
            if ( preg_match( '/&(.*$)/', $next, $match ) ) {
                $after = $match[1];
            }
            $query = $pre . $after;
            if ( preg_match( '/(^.*&)(' . $param . '=.*$)/', $query ) ) {
                $query = $this->delete_parameter( $query, $param );
            }
        }
        $query = preg_replace( '/^&/', '', $query );
        return $query;
    }

    function check_params ( $params = array() ) {
        foreach ( $params as $param ) {
            if ( $_GET[ $param ] || $_POST[ $param ] ) {
                return 0;
            }
        }
        return 1;
    }

    function include_blogs ( $blog, $args ) {
        $include_blogs = $args[ 'include_blogs' ];
        if (! isset ( $include_blogs ) ) {
            $include_blogs = $args[ 'blog_ids' ];
        }
        $include_ids = array();
        if ( $include_blogs ) {
            if ( $include_blogs === 'all' ) {
            } elseif ( $include_blogs === 'children' ) {
                $blog_id = $blog->id;
                array_push( $include_ids, $blog_id );
                $children = $blog->blogs();
                foreach ( $children as $child ) {
                    $child_id = $child->id;
                    array_push( $include_ids, $child_id );
                }
            } elseif ( $include_blogs === 'siblings' ) {
                $website = $blog->website();
                if ( isset ( $website ) ) {
                    $website_id = $website->id;
                    array_push( $include_ids, $website_id );
                    $children = $website->blogs();
                    foreach ( $children as $child ) {
                        $child_id = $child->id;
                        if ( $website_id != $child_id ) {
                            array_push( $include_ids, $child_id );
                        }
                    }
                }
            } else {
                $blog_ids = preg_split( '/\s*,\s*/', $include_blogs );
                foreach ( $blog_ids as $child_id ) {
                    array_push( $include_ids, $child_id );
                }
            }
        } else {
            if ( $blog->class == 'website' ) {
                array_push( $include_ids, $blog_id );
                require_once( 'class.mt_blog.php' );
                $blog_class = new Blog();
                $children = $blog_class->Find( ' blog_parent_id = ' . $blog_id );
                foreach ( $children as $child ) {
                    array_push( $include_ids, $child->id );
                }
            } else {
                $blog_id = $blog->id;
                array_push( $include_ids, $blog_id );
            }
        }
        if ( $include_blogs ) {
            $include_blogs = join( ',', $include_ids );
            return " in ({$include_blogs}) ";
        }
    }

    function include_exclude_blogs ( $ctx, $args ) {
        if ( isset( $args[ 'blog_ids' ] ) ||
             isset( $args[ 'include_blogs' ] ) ||
             isset( $args[ 'include_websites' ] ) ) {
            $args[ 'blog_ids' ] and $args[ 'include_blogs' ] = $args[ 'blog_ids' ];
            $args[ 'include_websites' ] and $args[ 'include_blogs' ] = $args[ 'include_websites' ];
            $attr = $args[ 'include_blogs' ];
            unset( $args[ 'blog_ids' ] );
            unset( $args[ 'include_websites' ] );
            $is_excluded = 0;
        } elseif ( isset( $args[ 'exclude_blogs' ] ) ||
                   isset( $args[ 'exclude_websites' ] ) ) {
            $attr = $args[ 'exclude_blogs' ];
            $attr or $attr = $args[ 'exclude_websites' ];
            $is_excluded = 1;
        } elseif ( isset( $args[ 'blog_id' ] ) && is_numeric( $args[ 'blog_id' ] ) ) {
            return ' = ' . $args[ 'blog_id' ];
        } else {
            $blog = $ctx->stash( 'blog' );
            if ( isset ( $blog ) ) return ' = ' . $blog->id;
        }
        if ( preg_match( '/-/', $attr ) ) {
            $list = preg_split( '/\s*,\s*/', $attr );
            $attr = '';
            foreach ( $list as $item ) {
                if ( preg_match('/(\d+)-(\d+)/', $item, $matches ) ) {
                    for ( $i = $matches[1]; $i <= $matches[2]; $i++ ) {
                        if ( $attr != '' ) $attr .= ',';
                        $attr .= $i;
                    }
                } else {
                    if ( $attr != '' ) $attr .= ',';
                    $attr .= $item;
                }
            }
        }
        $blog_ids = preg_split( '/\s*,\s*/', $attr, -1, PREG_SPLIT_NO_EMPTY );
        $sql = '';
        if ( $is_excluded ) {
            $sql = ' not in ( ' . join( ',', $blog_ids ) . ' )';
        } elseif ( $args[ include_blogs ] == 'all' ) {
            $sql = ' > 0 ';
        } elseif ( ( $args[ include_blogs ] == 'site' )
                || ( $args[ include_blogs ] == 'children' )
                || ( $args[ include_blogs ] == 'siblings' )
        ) {
            $blog = $ctx->stash( 'blog' );
            if (! empty( $blog ) && $blog->class == 'blog' ) {
                require_once( 'class.mt_blog.php' );
                $blog_class = new Blog();
                $blogs = $blog_class->Find( ' blog_parent_id = ' . $blog->parent_id );
                $blog_ids = array();
                foreach ( $blogs as $b ) {
                    array_push( $ids, $b->id );
                }
                if ( $args[ 'include_with_website' ] )
                    array_push( $blog_ids, $blog->parent_id );
                if ( count( $blog_ids ) ) {
                    $sql = ' in ( ' . join( ',', $blog_ids ) . ' ) ';
                } else {
                    $sql = ' > 0 ';
                }
            } else {
                $sql = ' > 0 ';
            }
        } else {
            if ( count( $blog_ids ) ) {
                $sql = ' in ( ' . join( ',', $blog_ids ) . ' ) ';
            } else {
                $sql = ' > 0 ';
            }
        }
        return $sql;
    }

    function path2index ( $path, $index = 'index.html' ) {
        $basename = end( explode( DIRECTORY_SEPARATOR, $path ) );
        $basename = preg_quote( $basename, '/' );
        $path = preg_replace( "/$basename\/{0,1}$/", $index, $path );
        return $path;
    }

    function do_conditional ( $ts ) {
        if ( $mode = $this->mode() ) {
            if ( $mode == 'logout' ) {
                return;
            }
        }
        $if_modified  = isset( $_SERVER[ 'HTTP_IF_MODIFIED_SINCE' ] )
                        ? strtotime( stripslashes( $_SERVER[ 'HTTP_IF_MODIFIED_SINCE' ] ) ) : FALSE;
        $if_nonematch = isset( $_SERVER[ 'HTTP_IF_NONE_MATCH' ] )
                        ? stripslashes( $_SERVER[ 'HTTP_IF_NONE_MATCH' ] ) : FALSE;
        $conditional;
        $last_modified = gmdate( "D, d M Y H:i:s", $ts ) . ' GMT';
        $etag = '"' . md5( $last_modified ) . '"';
        if ( $if_nonematch && ( $if_nonematch == $etag ) ) {
            $conditional = 1;
        }
        if ( $if_modified && ( $if_modified >= $ts ) ) {
            $conditional = 1;
        }
        if ( $this->request_method == 'POST' ) {
            $conditional = 0;
        }
        if ( $conditional ) {
            header( "Last-Modified: $last_modified" );
            header( "ETag: $etag" );
            header( $this->protocol . ' 304 Not Modified' );
            exit();
        }
        return;
    }

    function send_http_header ( $type = NULL, $ts = NULL, $length = NULL ) {
        if (! $type ) {
            $type = 'text/html';
        }
//        if ( preg_match( '/IIS/', $_SERVER[ 'SERVER_SOFTWARE' ] ) ) {
        if ( $this->config( 'SendHTTPHeaderMethod' ) == 'echo' ) {
            $headers[] = "content-type: $type";
        } else {
            header( "content-type: $type");
        }
        if ( $ts ) {
            $last_modified = gmdate( "D, d M Y H:i:s", $ts ) . ' GMT';
            $etag = '"' . md5( $last_modified ) . '"';
//            if ( preg_match( '/IIS/', $_SERVER[ 'SERVER_SOFTWARE' ] ) ) {
            if ( $this->config( 'SendHTTPHeaderMethod' ) == 'echo' ) {
                $headers[] = "Last-Modified: $last_modified";
                $headers[] = "ETag: $etag";
            } else {
                header( "Last-Modified: $last_modified" );
                header( "ETag: $etag" );
            }
        }
        if ( $length ) {
//            if ( preg_match( '/IIS/', $_SERVER[ 'SERVER_SOFTWARE' ] ) ) {
            if ( $this->config( 'SendHTTPHeaderMethod' ) == 'echo' ) {
               $headers[] = "Content-Length: $length";
            } else {
               header( "Content-Length: $length" );
            }
        }
        if ( isset( $headers ) ) {
            echo implode( "\n", $headers ) . "\n\n";
        }
        return;
    }

    function redirect ( $url ) {
        header( $this->stash( 'protocol' ) . ' 302 Redirect' );
        header( 'Status: 302 Redirect' );
        header( 'Location: ' . $url );
        exit();
    }

    function moved_permanently ( $url ) {
        header( $this->stash( 'protocol' ) . ' 301 Moved Permanently' );
        header( 'Status: 301 Moved Permanently' );
        header( 'Location: ' . $url );
        exit();
    }

    function file_not_found ( $msg = NULL ) {
        if (! $msg ) {
            $msg = '404 File Not Found.';
        }
        $status_code = 404;
        if ( file_exists( 'error.php' ) ) {
            include( 'error.php' );
        } elseif ( file_exists( 'error.html' ) ) {
            header( $this->protocol. ' 404 Not Found' );
            echo file_get_contents( 'error.html' );
        } else {
            header( $this->protocol . ' 404 Not Found' );
            echo $msg;
        }
        exit();
    }

    function access_forbidden ( $msg = NULL ) {
        if (! $msg ) {
            $msg = '403 Access Forbidden.';
        }
        $status_code = 403;
        if ( file_exists( 'error.php' ) ) {
            include( 'error.php' );
        } elseif ( file_exists( 'error.html' ) ) {
            header( $this->protocol . ' 403 Access Forbidden' );
            echo file_get_contents( 'error.html' );
        } else {
            header( $this->protocol . ' 403 Access Forbidden' );
            echo $msg;
        }
        exit();
    }

    function service_unavailable ( $msg = NULL ) {
        if (! $msg ) {
            $msg = '503 Service Unavailable.';
        }
        $status_code = 503;
        if ( file_exists( 'error.php' ) ) {
            include( 'error.php' );
        } elseif ( file_exists( 'error.html' ) ) {
            header( $this->protocol . ' 503 Service Unavailable' );
            echo file_get_contents( 'error.html' );
        } else {
            header( $this->protocol . ' 503 Service Unavailable' );
            echo $msg;
        }
        exit();
    }

    function make_atom_id ( $entry ) {
        $blog = $this->blog;
        $blog_id = $this->blog_id;
        $entry_id = $entry->id;
        $url = $blog->site_url();
        if (! preg_match( "!/$!", $url ) ) {
            $url .= '/';
        }
        preg_match( "!^https?://([^/:]+)(?::\d+)?(/.*)$!", $url, $match );
        $host = $match[ 1 ];
        if (! $host ) return '';
        $path = $match[ 2 ];
        $year = substr( $entry->authored_on, 0, 4 );
        if (! $host ) return '';
        if (! $year ) return '';
        if (! $path ) return '';
        if (! $blog_id ) return '';
        return "tag:$host,$year:$path/$blog_id.$entry_id";
    }

    function ___clone ( $class, $obj ) {
        $clone = $this->model( $class );
        $column_values = $this->column_values( $obj );
        $clone->set_values( $column_values );
        $clone->id = NULL;
        $clone->_saved = FALSE;
        return $clone;
    }

    function save_entry ( $entry, $params = array( 'rebuild' => 1 ) ) {
        require_once( 'MTUtil.php' );
        $blog    = $this->blog;
        $blog_id = $this->blog_id;
        $user    = $this->user();
        $ctx     = $this->ctx;
        if (! $this->can_edit_entry( $entry ) ) {
            return $ctx->error( $this->translate( 'Permission denied.' ) );
        }
        $id = $entry->id;
        if ( $id ) {
            $original = $this->___clone( 'Entry', $entry );
            $orig_cats = $this->entry_categories( $entry );
            $this->set_changed_categories( $orig_cats );
        }
        $ts = $this->current_ts();
        $epoch = datetime_to_timestamp( $ts );
        if (! $entry->basename ) {
            $basename = dirify( $entry->title );
            if (! $basename ) {
                $basename = dirify( $entry->text );
                if (! $basename ) {
                    if ( $entry->class == 'entry' ) {
                        $basename = uniqid( 'post_' );
                    } else {
                        $basename = uniqid( 'page_' );
                    }
                }
            }
            $entry->basename = $basename;
        }
        if (! $entry->blog_id ) $entry->blog_id = $blog_id;
        if (! $entry->author_id ) $entry->author_id = $user->id;
        if (! $entry->created_by ) $entry->created_by = $user->id;
        if (! $entry->created_by ) $entry->blog_id = $blog_id;
        if (! $entry->week_number ) {
            $week_number = date( o, $epoch ) . date( W, $epoch );
            $entry->week_number = $week_number;
        }
        if (! $entry->authored_on ) $entry->authored_on = $ts;
        if (! $entry->created_on ) $entry->created_on = $ts;
        if (! $entry->allow_comments ) $entry->allow_comments = $blog->allow_comments_default;
        if (! $entry->allow_pings ) $entry->allow_pings = $blog->allow_pings_default;
        if (! $entry->convert_breaks ) $entry->convert_breaks = $blog->convert_paras;
        if (! $entry->current_revision ) $entry->current_revision = 0;
        if (! $entry->atom_id ) $entry->atom_id = $this->make_atom_id( $entry );
        if (! $entry->status ) {
            if ( $this->can_do( $ctx, 'publish_post' ) ) {
                $entry->status = $blog->status_default;
            } else {
                $entry->status = 1;
            }
        }
        $entry->modified_on = $ts;
        $this->save( $entry );
        // if ( $id ) {
        //     $entry->Update();
        // } else {
        //     $entry->Save();
        // }
        if ( $params && isset( $params[ 'categories' ] ) ) {
            $this->set_entry_categories( $entry, $params[ 'categories' ] );
        }
        if ( $params && isset( $params[ 'tags' ] ) ) {
            $this->set_tags( $entry, $params[ 'tags' ] );
        }
        $at = 'Individual';
        if ( $entry->class == 'page' ) {
            $at = 'Page';
        }
        $ctx->stash( 'entry', $entry );
        $ctx->stash( 'archive_type', $at );
        if ( isset( $params ) && isset( $params[ 'rebuild' ] ) && $params[ 'rebuild' ] ) {
            $this->rebuild_entry( array( 'entry' => $entry ) );
        } else {
            // Only dynamic.
            $this->rebuild_entry( array( 'entry' => $entry, 'build_type' => array( 3 ) ) );
        }
        require_once 'function.mtentrypermalink.php';
        require_once 'function.mtentryexcerpt.php';
        $permalink = smarty_function_mtentrypermalink( array(), $ctx );
        $trackback = $this->get_by_key( 'Trackback',
                                        array( 'entry_id' => $entry->id ),
                                        array( 'limit' => 1 ) );
        $trackback->blog_id = $blog_id;
        $trackback->category_id = 0;
        $trackback->modified_on = $ts;
        $trackback->title = $entry->title;
        $trackback->description = smarty_function_mtentryexcerpt( array(), $ctx );
        $trackback->url = $permalink;
        if ( $entry->status == 2 ) {
            if (! $trackback->id ) {
                $trackback->created_by = $user->id;
                $trackback->created_on = $ts;
                $trackback->is_disabled = 0;
                $trackback->Save();
            } else {
                $trackback->Update();
            }
        } else {
            if ( $trackback->id ) {
                $trackback->Delete();
            }
        }
        $entry_id = $entry->id;
        if ( $entry->class == 'entry' ) {
            if ( $original ) {
                $original_ts = substr( $original->authored_on, 0, 6 );
                $entry_ts = substr( $entry->authored_on, 0, 6 );
                if ( $original_ts != $entry_ts ) {
                    $changed_entries_ts = $this->stash( 'changed_entries_ts' );
                    if (! $changed_entries_ts ) {
                        $changed_entries_ts = array();
                    }
                    array_push( $changed_entries_ts, $original_ts );
                    $this->stash( 'changed_entries_ts', $original->authored_on );
                }
            }
        }
        $this->stash( "entry:cached_object:{$entry_id}", $entry );
        $this->set_changed_entries( $entry );
        $this->touch_blog();
    }

    function entry_categories ( $entry ) {
        $places = $entry->placement( FALSE );
        if ( empty( $places ) ) return NULL;
        $cats = array();
        foreach( $places as $p ) {
            $cat = $p->category();
            $cats[] = $cat;
        }
        return $cats;
    }

    function site_path ( $blog = NULL, $exclude_archive_path = NULL, $add_slash = NULL ) {
        if (! $blog ) {
            $blog = $this->blog();
        }
        if (! $exclude_archive_path ) {
            $site_path = $blog->archive_path();
        }
        if (! $site_path ) {
            $site_path = $blog->site_path();
        }
        if (! $add_slash ) {
            $site_path = rtrim( $site_path, DIRECTORY_SEPARATOR );
        } elseif ( empty( $site_path ) ||
                  $site_path[ strlen( $site_path ) - 1 ] !== DIRECTORY_SEPARATOR ) {
            $site_path .= DIRECTORY_SEPARATOR;
        }
        return $site_path;
    }

    function site_url ( $blog = NULL, $add_slash = NULL ) {
        if (! $blog ) {
            $blog = $this->blog();
        }
        $site_url = $blog->site_url();
        if (! $add_slash ) {
            $site_url = preg_replace( "/\/$/", '', $site_url );
        } else {
            if (! preg_match( "/\/$/", $site_url ) ) {
                $site_url .= '/';
            }
        }
        return $site_url;
    }

    function rebuild_indexes ( $param = array( 'build_type' => array( 1, 3, 4 ) ) ) {
        $do = NULL;
        $build_type = $param[ 'build_type' ];
        if (! $build_type ) {
            $build_type = array( 1, 3, 4 );
        }
        $limit  = $param[ 'limit' ];
        $offset = $param[ 'offset' ];
        if ( $limit ) {
            if (! $offset ) {
                $offset = 0;
            }
        }
        $ctx = $this->ctx();
        $mt  = $this->mt();
        if ( isset( $param ) && is_object( $param ) ) {
            $blog = $param;
        } else {
            if ( isset( $param ) && isset( $param[ 'blog' ] ) ) {
                $blog = $param[ 'blog' ];
            }
            if ( isset( $param ) && isset( $param[ 'Blog' ] ) ) {
                $blog = $param[ 'Blog' ];
            }
        }
        if ( $blog && is_numeric( $blog ) ) {
            $blog = $this->blog( $blog );
        }
        if (! $blog ) {
            $blog = $this->blog();
        }
        if (! $blog ) {
            return;
        }
        $blog_id = $blog->id;
        $site_path = $this->site_path( $blog, NULL, 1 );
        $site_url  = $this->site_url( $blog, 1 );
        $extra = array();
        if ( $limit ) {
            $extra[ 'limit' ] = $limit;
            $extra[ 'offset' ] = $offset;
        }
        $templates = $this->load( 'Template', array( 'blog_id' => $blog_id,
                                                     'build_type' => $build_type,
                                                     'type' => 'index' ),
                                                     $extra );
        if ( isset( $templates ) ) {
            foreach ( $templates as $template ) {
                $template_id = $template->id;
                $file_path = $site_path . $template->outfile;
                $url = $site_url . $template->outfile;
                $url = preg_replace( "!https{0,1}://.*?/!", '/', $url );
                $terms = array( 'archive_type' => 'index',
                                'blog_id' => $blog_id,
                                'template_id' => $template_id,
                                );
                $fileinfo = $this->get_by_key( 'FileInfo', $terms );
                $update = NULL;
                $is_new = NULL;
                if (! $fileinfo->id ) {
                    $is_new = 1;
                } else {
                    if ( $fileinfo->build_type != $template->build_type ) {
                        $update = 1;
                    }
                    if ( $fileinfo != $fileinfo->file_path ) {
                        $update = 1;
                    }
                    if ( $url != $fileinfo->url ) {
                        $fileinfo->url = $url;
                        $update = 1;
                    }
                    if ( $template->build_type == 3 ) {
                        if ( $fileinfo->virtual != 1 ) {
                            $update = 1;
                        }
                    } else {
                        if ( $fileinfo->virtual == 1 ) {
                            $update = 1;
                        }
                    }
                }
                if ( $template->build_type == 3 ) {
                    $fileinfo->virtual = 1;
                } else {
                    $fileinfo->virtual = NULL;
                }
                $fileinfo->build_type = $template->build_type;
                $fileinfo->file_path = $file_path;
                $fileinfo->url = $url;
                if ( $is_new ) {
                    $fileinfo->Save();
                    $do = 1;
                } elseif ( $update ) {
                    $fileinfo->Update();
                    $do = 1;
                }
                if ( $template->build_type == 3 ) {
                    if ( file_exists( $file_path ) ) {
                        rename( $file_path, "$file_path.static" );
                    }
                }
                $this->stash( 'fileinfo_template:' . $fileinfo->id, $template );
                $build_type = $fileinfo->build_type;
                if ( ( $build_type == 1 ) || ( $build_type == 4 ) ) {
                    $output = $this->rebuild_from_fileinfo( $fileinfo );
                    if ( ( $output != NULL ) && ( $build_type == 1 ) ) {
                        if ( $this->content_is_updated( $file_path, $output ) ) {
                            $this->write2file( $file_path, $output );
                            $args = $this->get_args();
                            $this->run_callbacks( 'rebuild_file', $mt, $ctx, $args, $output );
                            $do = 1;
                        }
                    }
                }
            }
        }
        return $do;
    }

    function rebuild_entry ( $param = array( 'build_type' => array( 1, 3, 4 ) ) ) {
        if ( isset( $param ) && is_array( $param ) ) {
            $build_type = $param[ 'build_type' ];
            $archives = $param[ 'recipe' ];
        }
        if (! $build_type ) {
            $build_type = array( 1, 3, 4 );
        }
        $ctx = $this->ctx();
        $mt  = $this->mt();
        if ( isset( $param ) && is_object( $param ) ) {
            $entry = $param;
        } else {
            if ( isset( $param ) && isset( $param[ 'entry' ] ) ) {
                $entry = $param[ 'entry' ];
            }
            if ( isset( $param ) && isset( $param[ 'Entry' ] ) ) {
                $entry = $param[ 'Entry' ];
            }
            if ( $entry && is_numeric( $entry ) ) {
                // $entry = $mt->db()->fetch_entry( $entry );
                $entry = $this->load( 'Entry', $entry );
            }
        }
        if (! $entry ) {
            $entry = $ctx->stash( 'entry' );
        }
        if (! $entry ) {
            return;
        }
        $res = $this->rebuild_object( $entry, $build_type );
        $builddependencies = NULL;
        if ( isset( $param ) && isset( $param[ 'BuildDependencies' ] ) ) {
            $builddependencies = 1;
        }
        if ( isset( $param ) && isset( $param[ 'builddependencies' ] ) ) {
            $builddependencies = 1;
        }
        if ( $builddependencies ) {
            if (! $archives ) {
                if ( $entry->class == 'entry' ) {
                    $archives = array( 'Index', 'Category', 'Monthly',
                                       'Yearly', 'Weekly', 'Daily', 'Author',
                                       'Author-Daily', 'Author-Monthly',
                                       'Author-Weekly', 'Author-Yearly',
                                       'Category-Daily', 'Category-Monthly',
                                       'Category-Weekly', 'Category-Yearly', );
                } elseif ( $entry->class == 'page' ) {
                    $archives = array( 'Index' );
                }
            }
            $this->rebuild_archives( array( 'recipe' => $archives, 'entry' => $entry ) );
        }
        return $res;
    }

    function rebuild_category ( $param = array( 'build_type' => array( 1, 3, 4 ) ) ) {
        if ( isset( $param ) && is_array( $param ) ) {
            $build_type = $param[ 'build_type' ];
        }
        if (! $build_type ) {
            $build_type = array( 1, 3, 4 );
        }
        $ctx = $this->ctx();
        $mt  = $this->mt();
        if ( isset( $param ) && is_object( $param ) ) {
            $category = $param;
        } else {
            if ( isset( $param ) && isset( $param[ 'category' ] ) ) {
                $category = $param[ 'category' ];
            }
            if ( $category && is_numeric( $category ) ) {
                $category = $mt->db()->fetch_category( $category );
            }
        }
        if (! $category ) {
            $category = $ctx->stash( 'category' );
        }
        if (! $category ) {
            return;
        }
        return $this->rebuild_object( $category, $build_type );
    }

    function get_permalink ( $obj, $args = array( 'wants' => 'url', 'with_index' => 1 ) ) {
        $ctx = $this->ctx();
        $class = $obj->class;
        $wants = $args[ 'wants' ];
        $wants = strtolower( $wants );
        $blog = $ctx->stash( 'blog' );
        $blog_id = $obj->blog_id;
        if ( $blog_id && ( $blog_id != $blog->id ) ) {
            $blog = $ctx->mt->db()->fetch_blog( $blog_id );
        }
        $path = NULL;
        if ( $class == 'category' ) {
            $path = $ctx->mt->db()->category_link( $obj->id, $args );
            if ( $args[ 'with_index' ] && $path && preg_match( '/\/(#.*)*$/', $path ) ) {
                $index = $ctx->mt->config( 'IndexBasename' );
                $ext = $blog->blog_file_extension;
                if ( $ext ) $ext = '.' . $ext;
                $index .= $ext;
                $path = preg_replace( '/\/(#.*)?$/', "/$index\$1", $path );
            }
        } elseif ( $class == 'entry' ) {
            $path = $ctx->mt->db()->entry_link( $obj->id, 'Individual', $args );
        } elseif ( $class == 'page' ) {
            $path = $ctx->mt->db()->entry_link( $obj->id, 'Page', $args );
        }
        if ( $wants == 'url' ) {
            return $path;
        } elseif ( preg_match ( '/path$/', $wants ) ) {
            $site_url = $this->site_url( $blog, 1 );
            $site_path = $this->site_path( $blog, 1 );
            $search = preg_quote( $site_url, '/' );
            $path = preg_replace( "/^$search/", $site_path );
            return $path;
        }
    }

    function count_datebased_archive ( $blog, $params = NULL ) {
        require_once( 'MTUtil.php' );
        require_once( 'dynamicmtml.util.php' );
        if ( isset( $params[ 'archive_type' ] ) ) $at = $params[ 'archive_type' ];
        if (! $at ) return 0;
        list ( $first_entry, $last_entry ) = $this->start_end_entry( $blog, $params );
        if (! $first_entry ) {
            return 0;
        }
        $first_ts = __date2ts( $first_entry->authored_on );
        $last_ts = __date2ts( $last_entry->authored_on );
        if ( preg_match( '/Yearly/i', $at ) ) {
            $first_y = substr( $first_ts, 0, 4 );
            $last_y  = substr( $last_ts, 0, 4 );
            $count = ( $last_y - $first_y ) + 1;
        } elseif ( preg_match( '/Monthly/i', $at ) ) {
            $first_ts = start_end_month( $first_ts );
            $last_ts = start_end_month( $last_ts );
            $first_ts = $first_ts[0];
            $last_ts = $last_ts[0];
            $first_y = substr( $first_ts, 0, 4 );
            $last_y  = substr( $last_ts, 0, 4 );
            $first_m = substr( $first_ts, 4, 2 );
            $last_m  = substr( $last_ts, 4, 2 );
            if ( $first_y == $last_y ) {
                $count = $last_m - $last_m + 1;
            } else {
                $year2month = ( $last_y - $first_y - 1 ) * 12;
                $count = $year2month + $last_m + 12 - $first_m + 1;
            }
        } elseif ( preg_match( '/Weekly/i', $at ) ) {
            $first_ts = start_end_week( $first_ts );
            $last_ts = start_end_week( $last_ts );
            $first_ts = $first_ts[0];
            $last_ts = $last_ts[0];
            $first_epoch = datetime_to_timestamp( $first_ts );
            $last_epoch = datetime_to_timestamp( $last_ts );
            $period = $last_epoch - $first_epoch;
            $count = $period / 86400 / 7;
            $count++;
        } elseif ( preg_match( '/Daily/i', $at ) ) {
            $first_ts = start_end_day( $first_ts );
            $last_ts = start_end_day( $last_ts );
            $first_ts = $first_ts[0];
            $last_ts = $last_ts[0];
            $first_epoch = datetime_to_timestamp( $first_ts );
            $last_epoch = datetime_to_timestamp( $last_ts );
            $period = $last_epoch - $first_epoch;
            $count = $period / 86400 + 1;
        }
        return $count;
    }

    function start_end_entry ( $blog, $params = NULL ) {
        if (! $blog ) {
            $blog = $this->blog();
        } else {
            if ( is_numeric( $blog ) ) {
                $blog = $this->blog( $blog );
            }
        }
        if (! $blog ) return;
        $blog_id = $blog->id;
        if ( isset( $params[ 'category' ] ) ) $category = $params[ 'category' ];
        if ( isset( $params[ 'author' ] ) ) $author = $params[ 'author' ];
        if ( isset( $params[ 'status' ] ) ) $status = $params[ 'status' ];
        if ( isset( $params[ 'class' ] ) ) $class = $params[ 'class' ];
        if (! $class ) {
            $class = 'entry';
        } elseif ( $class == '*' ) {
            $class = array( 'entry', 'page' );
        }
        $terms = array( 'blog_id' => $blog_id,
                        'class'   => $class );
        if ( $author ) {
            if ( is_numeric( $author ) ) {
                $author_id = $author;
                $terms[ 'author_id' ] = $author;
            } else {
                $author_id = $author->id;
                $terms[ 'author_id' ] = $author->id;
            }
        }
        if ( $status ) {
            $terms[ 'status' ] = $status;
        }
        $extra = array( 'sort' => 'authored_on',
                        'direction' => 'ascend',
                        'limit' => 1 );
        if ( $category ) {
            if ( is_numeric( $category ) ) {
                $category_id = $category;
            } else {
                $category_id = $category->id;
            }
            // TODO:: ( array( 'mt_placement', 'entry_id', array( 'category_id' => $category_id ) ) );
            $join = array( 'mt_placement' => array( 'condition' =>
                           "entry_id=placement_entry_id AND placement_category_id={$category_id}" ) );
            $extra[ 'join' ] = $join;
        }
        if ( (! is_array( $class ) ) && (! $status ) ) {
            if ( $category_id ) {
                if ( $start_end_entry = $this->stash( "start_end_{$class}:{$blog_id}:category:{$category_id}" ) ) {
                    return $start_end_entry;
                }
            } elseif ( $author_id ) {
                if ( $start_end_entry = $this->stash( "start_end_{$class}:{$blog_id}:author:{$author_id}" ) ) {
                    return $start_end_entry;
                }
            } else {
                if ( $start_end_entry = $this->stash( "start_end_{$class}:{$blog_id}" ) ) {
                    return $start_end_entry;
                }
            }
        }
        $first_entry = $this->load( 'Entry', $terms, $extra );
        $extra[ 'direction' ] = 'descend';
        $last_entry = $this->load( 'Entry', $terms, $extra );
        $start_end_entry = array( $first_entry, $last_entry );
        if ( (! is_array( $class ) ) && (! $status ) ) {
            if ( $category_id ) {
                $this->stash( "start_end_{$class}:{$blog_id}:category:{$category_id}", $start_end_entry );
            } elseif ( $author_id ) {
                $this->stash( "start_end_{$class}:{$blog_id}:author:{$author_id}", $start_end_entry );
            } else {
                $this->stash( "start_end_{$class}:{$blog_id}", $start_end_entry );
            }
        }
        return $start_end_entry;
    }

    function create_fileinfo_from_map ( $blog, $map, $param ) {
        $ctx = $this->ctx();
        if ( isset( $param[ 'startdate' ] ) ) $ts = $param[ 'startdate' ];
        if ( isset( $param[ 'entry' ] ) ) $entry = $param[ 'entry' ];
        if ( isset( $param[ 'category' ] ) ) $category = $param[ 'category' ];
        if ( isset( $param[ 'author' ] ) ) $author = $param[ 'author' ];
        $at = $map->archive_type;
        $template_id = $map->template_id;
        $templatemap_id = $map->id;
        $blog_id = $map->blog_id;
        $terms = array( 'blog_id' => $blog_id,
                        'archive_type' => $at,
                        'template_id' => $template_id,
                        'templatemap_id' => $templatemap_id );
        require_once 'function.mtfiletemplate.php';
        if ( preg_match ( '/Category/', $at ) ) {
            $ctx->stash( 'category', $category );
            $terms[ 'category_id' ] = $category->id;
        } elseif ( preg_match ( '/Author/', $at ) ) {
            $ctx->stash( 'author', $author );
            $ctx->stash( 'archive_author', $author );
            $terms[ 'author_id' ] = $author->id;
        } else {
            if ( $entry ) {
                $ctx->stash( 'entry', $entry );
            }
        }
        $ctx->stash( 'archive_type', $at );
        $ctx->stash( 'current_archive_type', $at );
        $ctx->stash( 'build_template_id', $tpl_id );
        if ( isset( $at ) && ( $at != 'Category' ) ) {
            require_once( 'archive_lib.php' );
            try {
                $archiver = ArchiverFactory::get_archiver( $at );
            } catch ( Execption $e ) {
                return NULL;
            }
            $archiver->template_params( $ctx );
            list( $ts_start, $ts_end ) = $archiver->get_range( $ts );
            $ctx->stash( 'current_timestamp', $ts_start );
            // $ctx->stash( 'current_timestamp', $ts );
            $ctx->stash( 'current_timestamp_end', $ts_end );
            $terms[ 'startdate' ] = $ts;
        }
        $fileinfo = $this->get_by_key( 'FileInfo', $terms );
        $formats = array(
            'Individual' => '%y/%m/%f',
            'Category' => '%c/%f',
            'Monthly' => '%y/%m/%f',
            'Weekly' => '%y/%m/%d-week/%f',
            'Daily' => '%y/%m/%d/%f',
            'Page' => '%-c/%-f',
            'Yearly' => '%y/%i',
        );
        if (! $format = $map->file_template ) {
            $format = $formats[ $at ];
        }
        if ( $at === 'Page' ) {
            $format = preg_replace( '/f$/', 'b%x', $format );
        }
        $params[ 'format' ] = $format;
        $path = smarty_function_mtfiletemplate( $params, $ctx );
        $site_path = $this->site_path( $blog, NULL, 1 );
        $site_url = $this->site_url( $blog, 1 );
        $site_url = preg_replace( "!^https{0,1}://.*?/!i", '/', $site_url );
        $fileinfo->url = $site_url . $path;
        $fileinfo->file_path = $site_path . $path;
        if ( $author ) {
            $fileinfo->author_id = $author->id;
        }
        if ( $entry ) {
            $fileinfo->entry_id = $entry->id;
        }
        if ( $category ) {
            $fileinfo->category_id = $category->id;
        }
        if ( $ts ) {
            $fileinfo->startdate = $ts;
        }
        if ( $map->build_type == 3 ) {
            $fileinfo->virtual = 1;
        }
        if ( $fileinfo->id ) {
            $fileinfo->Update();
        } else {
            $fileinfo->Save();
        }
        return $fileinfo;
    }

    function rebuild ( $params, $updated = NULL ) {
        if ( $params ) {
            if ( isset( $params[ 'Blog' ] ) ) {
                $blog = $params[ 'Blog' ];
            } elseif ( isset( $params[ 'blog' ] ) ) {
                $blog = $params[ 'blog' ];
            } else {
                if ( isset( $params[ 'BlogID' ] ) ) {
                    $blog_id = $params[ 'BlogID' ];
                } elseif ( isset( $params[ 'blog_id' ] ) ) {
                    $blog_id = $params[ 'blog_id' ];
                }
                if ( $blog_id ) {
                    $blog = $this->blog( $blog_id );
                    $this->ctx()->stash( 'blog', $blog );
                    $this->ctx()->stash( 'blog_id', $blog_id );
                    $this->stash( 'blog', $blog );
                    $this->stash( 'blog_id', $blog_id );
                }
            }
            if ( isset( $params[ 'ArchiveType' ] ) ) {
                $at = $params[ 'ArchiveType' ];
            } elseif ( isset( $params[ 'archivetype' ] ) ) {
                $at = $params[ 'archivetype' ];
            } elseif ( isset( $params[ 'archive_type' ] ) ) {
                $at = $params[ 'archive_type' ];
            }
            if ( isset( $params[ 'Force' ] ) ) {
                $force = $params[ 'Force' ];
            } elseif ( isset( $params[ 'force' ] ) ) {
                $force = $params[ 'force' ];
            }
            if ( isset( $params[ 'NoStatic' ] ) ) {
                $nostatic = $params[ 'NoStatic' ];
            } elseif ( isset( $params[ 'nostatic' ] ) ) {
                $nostatic = $params[ 'nostatic' ];
            }
            if ( isset( $params[ 'NoIndexes' ] ) ) {
                $noindexes = $params[ 'NoIndexes' ];
            } elseif ( isset( $params[ 'noindexes' ] ) ) {
                $noindexes = $params[ 'noindexes' ];
            }
            if ( isset( $params[ 'Limit' ] ) ) {
                $limit = $params[ 'Limit' ];
            } elseif ( isset( $params[ 'limit' ] ) ) {
                $limit = $params[ 'limit' ];
            }
            if ( $limit ) {
                if ( isset( $params[ 'Offset' ] ) ) {
                    $offset = $params[ 'Offset' ];
                } elseif ( isset( $params[ 'offset' ] ) ) {
                    $offset = $params[ 'offset' ];
                }
                if (! $offset ) {
                    $offset = 0;
                }
            }
            // TODO:: TemplateMap, TemplateID
        }
        if (! $blog ) {
            $blog = $this->blog();
            if (! $blog ) return 0;
        }
        if ( $at ) {
            $at = explode( ',', $at );
        } else {
            if ( $blog->class == 'blog' ) {
                $at = array( 'Individual', 'Page', 'Category', 'Author', 'Daily', 'Weekly',
                             'Monthly', 'Yearly', 'Author-Daily', 'Author-Weekly',
                             'Author-Monthly', 'Author-Yearly', 'Category-Daily',
                             'Category-Weekly', 'Category-Monthly', 'Category-Yearly', );
            } elseif ( $blog->class == 'website' ) {
                $at = array( 'Page' );
            }
        }
        if (! $noindexes ) {
            if (! isset( $at[ 'Index' ] ) ) {
            // if (! in_array( 'Index', $at ) ) {
                array_push( $at, 'Index' );
            }
        }
        $build_type = array( 3, 4 );
        if (! $nostatic ) {
            array_push( $build_type, 1 );
            if ( $force ) {
                array_push( $build_type, 2 );
            }
        }
        $params = array ( 'blog' => $blog, 'build_type' => $build_type );
        $do = 0;
        foreach ( $at as $archive_type ) {
            $orig_params = $params;
            $orig_params[ 'recipe' ] = array( $archive_type );
            if ( ( $archive_type === 'Individual' ) || ( $archive_type === 'Page' ) ) {
                if ( $limit ) {
                    $orig_params[ 'limit' ] = $limit;
                    $orig_params[ 'offset' ] = $offset;
                }
            }
            if ( $updated ) {
                $orig_params[ 'updated' ] = 1;
            }
            $do = $this->rebuild_archives( $orig_params );
        }
        return $do;
    }

    function rebuild_archives ( $params = array (
                                'blog' => NULL,
                                'recipe' => array( 'Index', 'Category', 'Monthly' ),
                                'updated' => 1,
                                'build_type' => array( 1, 3, 4 ),
                                'offset' => NULL,
                                'limit' => NULL,
                                ) ) {
        $do = NULL;
        $mt = $this->mt();
        $blog = $params[ 'blog' ];
        if (! $blog ) {
            $blog = $this->blog();
        } else {
            if ( is_numeric( $blog ) ) {
                $blog = $this->blog( $blog );
            }
        }
        if (! $blog ) return;
        $updated = $params[ 'updated' ];
        $entry = $params[ 'entry' ];
        if ( $entry ) {
            $updated = 1;
        }
        $blog_id = $blog->id;
        $ats = $params[ 'recipe' ];
        if (! $ats ) {
            $ats = $params[ 'Recipe' ];
        }
        if (! $ats ) {
            return;
        }
        $build_type = $params[ 'build_type' ];
        if (! $build_type ) {
            $build_type = array( 1, 3, 4 );
        }
        $offset = $params[ 'offset' ];
        $limit = $params[ 'limit' ];
        if (! $offset ) $offset = 0;
        require_once( 'MTUtil.php' );
        foreach ( $ats as $at ) {
            $at = trim( $at );
            $publish_type = $at;
            if ( ( $at === 'Individual' ) || ( $at === 'Entry' ) || ( $at === 'Page' ) ) {
                $publish_type = 'Entry';
            }
            if ( preg_match( '/ly$/', $at ) ) {
                if (! preg_match( '/\-/', $at ) ) {
                    $publish_type = 'Date';
                }
            }
            $publisher_dir = $this->stash( 'publisher_dir' );
            if ( is_array( $publisher_dir ) ) {
                foreach ( $publisher_dir as $plugin_publisher_dir ) {
                    if ( is_dir( $plugin_publisher_dir ) ) {
                        $publisher_file = $plugin_publisher_dir . DIRECTORY_SEPARATOR . $publish_type . '.php';
                        if ( file_exists( $publisher_file ) ) {
                            require( $publisher_file );
                            break;
                        }
                    }
                }
            }
        }
        return $do;
    }

    function __get_object_context ( $obj ) {
        $table = $obj->_table;
        $table = preg_replace( '/^mt_/', '', $table );
        $fileinfo_key = $table . '_id';
        $blog = $obj->blog();
        $at = NULL;
        if ( $obj->class == 'entry' ) {
            $at = 'Individual';
            $_type = 'entry';
        } elseif ( $obj->class == 'page' ) {
            $at = 'Page';
            $_type = 'entry';
        } elseif ( $obj->class == 'category' ) {
            $at = 'Category';
            $_type = 'category';
        } else {
            return NULL;
        }
        return array( 'datasource'   => $_type,
                      'archive_type' => $at,
                      'fileinfo_key' => $fileinfo_key );
    }

    function rebuild_object ( $obj, $build_type = array( 1, 3, 4 ) ) {
        $do  = NULL;
        $ctx = $this->ctx();
        $mt  = $this->mt();
        if (! $obj ) {
            return;
        }
        $blog = $blog = $obj->blog();
        $blog_id = $obj->blog_id;
        $object_context = $this->__get_object_context( $obj );
        $at = $object_context[ 'archive_type' ];
        $fileinfo_key = $object_context[ 'fileinfo_key' ];
        $_type = $object_context[ 'datasource' ];
        if (! $at ) {
            return;
        }
        if ( $at != 'Category' ) {
            $ctx->stash( 'entry', $obj );
        } else {
            $ctx->stash( 'category', $obj );
        }
        $ctx->stash( 'archive_type', $at );
        $ctx->stash( 'current_archive_type', $at );
        $terms = array( 'blog_id' => $blog_id,
                        'archive_type' => $at );
        if ( $build_type ) {
            $terms[ 'build_type' ] = $build_type;
        }
        $maps = $this->load( 'TemplateMap', $terms );
        $formats = array(
            'Individual' => '%y/%m/%f',
            'Category' => '%c/%f',
            'Monthly' => '%y/%m/%f',
            'Weekly' => '%y/%m/%d-week/%f',
            'Daily' => '%y/%m/%d/%f',
            'Page' => '%-c/%-f',
            'Yearly' => '%y/%i',
        );
        if ( isset( $maps ) ) {
            require_once 'function.mtfiletemplate.php';
            $site_path = $blog->site_path();
            if ( empty( $site_path ) ||
                $site_path[ strlen( $site_path ) - 1 ] !== DIRECTORY_SEPARATOR ) {
                $site_path .= DIRECTORY_SEPARATOR;
            }
            $site_url = $this->site_url( $blog, 1 );
            $site_url = preg_replace( "!^https{0,1}://.*?/!i", '/', $site_url );
            foreach ( $maps as $map ) {
                if (! $format = $map->file_template ) {
                    $format = $formats[ $at ];
                }
                if ( $at === 'Page' ) {
                    $format = preg_replace( '/f$/', 'b%x', $format );
                }
                $params[ 'format' ] = $format;
                $path = smarty_function_mtfiletemplate( $params, $ctx );
                $templatemap_id = $map->id;
                $template_id = $map->template_id;
                $build_type = $map->build_type;
                $terms = array( 'blog_id' => $blog_id,
                                'archive_type' => $at,
                                $fileinfo_key => $obj->id,
                                'templatemap_id' => $templatemap_id,
                                );
                $fileinfo = $this->get_by_key( 'FileInfo', $terms );
                $file_path = $site_path . $path;
                $fileinfo->file_path = $file_path;
                $fileinfo->url = $site_url . $path;
                $fileinfo->template_id = $template_id;
                if ( $build_type == 3 ) {
                    $fileinfo->virtual = 1;
                }
                $fileinfo_id = $fileinfo->id;
                $published;
                if ( $_type == 'entry' ) {
                    if ( $obj->status == 2 ) {
                        $published = 1;
                    }
                } else {
                    // Count category's entry.
                    $category_id = $obj->id;
                    $terms = array( 'status' => 2, 'blog_id' => $blog_id );
                    $extra = array( 'limit' => 1 );
                    $join = array( 'mt_placement' => array( 'condition' =>
                                   "entry_id=placement_entry_id AND placement_category_id={$category_id}" ) );
                    $extra[ 'join' ] = $join;
                    $count = $this->count( 'Entry', $terms, $extra );
                    if ( $count ) {
                        $published = 1;
                    }
                }
                $do = 1;
                if ( $published ) {
                    if ( $fileinfo_id ) {
                        $fileinfo->Update();
                    } else {
                        $fileinfo->Save();
                    }
                    $this->stash( 'fileinfo_tmap:' . $fileinfo->id, $map );
                } else {
                    if ( $fileinfo_id ) {
                        if ( file_exists( $file_path ) ) {
                            unlink( $file_path );
                        }
                        $fileinfo->Delete();
                    }
                }
                if ( $build_type != 3 ) {
                    if ( $build_type == 1 ) {
                        $file_out = $fileinfo->file_path;
                        if ( $published ) {
                            $output = $this->rebuild_from_fileinfo( $fileinfo );
                            if ( $output != NULL ) {
                                if ( $this->content_is_updated( $file_out, $output ) ) {
                                    $this->write2file( $file_out, $output );
                                    $args = $this->get_args();
                                    $this->run_callbacks( 'rebuild_file', $mt, $ctx, $args, $output );
                                }
                            }
                        } else {
                            if ( file_exists( $file_out ) ) {
                                unlink( $file_out );
                            }
                        }
                    } elseif ( $build_type == 4 ) {
                        // ASYNC
                        if ( $published ) {
                            $this->rebuild_from_fileinfo( $fileinfo );
                        }
                    }
                } else {
                    if ( file_exists( $file_path ) ) {
                        rename( $file_path, "$file_path.static" );
                    }
                }
            }
        }
        return $do;
    }

    function set_changed_entries ( $entries = array() ) {
        $changed_entries = $this->stash( 'changed_entries' );
        $changed_entry_ids = $this->stash( 'changed_entry_ids' );
        $changed_pages = $this->stash( 'changed_pages' );
        $changed_page_ids = $this->stash( 'changed_page_ids' );
        if (! $changed_entries ) {
            $changed_entries = array();
        }
        if (! $changed_entry_ids ) {
            $changed_entry_ids = array();
        }
        if (! $changed_pages ) {
            $changed_pages = array();
        }
        if (! $changed_page_ids ) {
            $changed_page_ids = array();
        }
        if ( isset( $entries ) ) {
            if (! is_array( $entries ) ) {
                $entries = array( $entries );
            }
            foreach ( $entries as $entry ) {
                if ( $entry->class == 'entry' ) {
                    if (! in_array( $entry->id, $changed_entry_ids ) ) {
                        array_push( $changed_entry_ids, $entry->id );
                        array_push( $changed_entries, $entry );
                    }
                } elseif ( $entry->class == 'page' ) {
                    if (! in_array( $entry->id, $changed_page_ids ) ) {
                        array_push( $changed_page_ids, $entry->id );
                        array_push( $changed_pages, $entry );
                    }
                }
            }
            $this->stash( 'changed_entries', $changed_entries );
            $this->stash( 'changed_entry_ids', $changed_entry_ids );
            $this->stash( 'changed_pages', $changed_pages );
            $this->stash( 'changed_page_ids', $changed_page_ids );
        }
    }

    function set_changed_categories ( $categories = array() ) {
        $changed_categories = $this->stash( 'changed_categories' );
        $changed_category_ids = $this->stash( 'changed_category_ids' );
        $changed_folders = $this->stash( 'changed_folders' );
        $changed_folder_ids = $this->stash( 'changed_folder_ids' );
        if (! $changed_categories ) {
            $changed_categories = array();
        }
        if (! $changed_category_ids ) {
            $changed_category_ids = array();
        }
        if (! $changed_folders ) {
            $changed_folders = array();
        }
        if (! $changed_folder_ids ) {
            $changed_folder_ids = array();
        }
        if ( isset( $categories ) ) {
            if (! is_array( $categories ) ) {
                $categories = array( $categories );
            }
            foreach ( $categories as $category ) {
                if ( $category->class == 'category' ) {
                    if (! in_array( $category->id, $changed_category_ids ) ) {
                        array_push( $changed_category_ids, $category->id );
                        array_push( $changed_categories, $category );
                    }
                } elseif ( $category->class == 'folder' ) {
                    if (! in_array( $category->id, $changed_folder_ids ) ) {
                        array_push( $changed_folder_ids, $category->id );
                        array_push( $changed_folders, $category );
                    }
                }
            }
            $this->stash( 'changed_categories', $changed_categories );
            $this->stash( 'changed_category_ids', $changed_category_ids );
            $this->stash( 'changed_folders', $changed_folders );
            $this->stash( 'changed_folder_ids', $changed_folder_ids );
        }
    }

    function put ( $src, $path, $mode = 'output' ) {
        return $this->write2file( $path, file_get_contents( $src ), $mode );
    }

    function put_data ( $data, $path, $mode = 'output' ) {
        return $this->write2file( $path, $data, $mode );
    }

    function get_data ( $path, $mode = 'output' ) {
        //TODO:: $mode
        return file_get_contents( $path );
    }

    function delete ( $path ) {
        if (! file_exists( $path ) ) {
            return TRUE;
        }
        if ( is_link( $path ) ) {
            return TRUE;
        }
        if ( unlink( $path ) ) {
            return TRUE;
        }
        return FALSE;
    }

    function write2file ( $path, $data, $mode = 'output' ) {
        require_once( 'dynamicmtml.util.php' );
        if ( $mode === 'upload' ) {
            $umask = $this->config( 'UploadUmask' );
            if ( $umask ) {
                $perms = __umask2permission( $umask );
            }
        } else {
            $perms = $this->config( 'HTMLPerms' );
            if (! $perms ) {
                $umask = $this->config( 'HTMLUmask' );
                if ( $umask ) {
                    $perms = __umask2permission( $umask );
                }
            }
        }
        if (! $perms ) {
            $perms = 666;
        }
        $dirname = dirname( $path );
        if ( $this->mkpath( $dirname ) ) {
            if ( $mode === 'upload' ) {
                if ( $fh = fopen( "$path.new", 'w' ) ) {
                    fwrite( $fh, $data, 128000 );
                    fclose( $fh );
                }
            } else {
                file_put_contents( "$path.new", $data );
            }
            if ( rename( "$path.new", $path ) ) {
                chmod( $path, octdec( $perms ) );
                return filesize( $path );
            }
        }
        return FALSE;
    }

    function content_is_updated ( $file, $html ) {
        if ( file_exists( $file ) ) {
            $orig_content = file_get_contents( $file );
            $orig_content = md5( $orig_content );
            $html = md5( $html );
            if ( $orig_content != $html ) {
                return 1;
            } else {
                return 0;
            }
        }
        return 1;
    }

    function mkpath ( $path, $perms = NULL ) {
        if (! is_dir( $path ) ) {
            if (! file_exists( $path ) ) {
                require_once( 'dynamicmtml.util.php' );
                if (! $perms ) {
                    $umask = $this->config( 'DirUmask' );
                    if ( $umask ) {
                        if ( $umask ) {
                            $perms = __umask2permission( $umask );
                        }
                    }
                    if (! $perms ) {
                        $perms = 755;
                    }
                }
                mkdir( $path, octdec( $perms ), TRUE );
            } else {
                return 0;
            }
        }
        return ( is_writable( $path ) );
    }

    function rebuild_from_fileinfo ( $fileinfo, $force = NULL ) {
        $ctx  = $this->ctx();
        $mt   = $this->mt();
        $blog = $this->blog();
        $blog_id = $fileinfo->blog_id;
        $data = $fileinfo;
        if ( $blog_id != $blog->id ) {
            $blog = $this->blog( $blog_id );
        }
        $this->init_mt( $mt, $ctx, $blog );
        if ( $fileinfo->templatemap_id ) {
            if (! $map = $this->stash( 'fileinfo_tmap:' . $fileinfo->id ) ) {
                $map = $this->load( 'TemplateMap', $fileinfo->templatemap_id );
            }
            if (! $map ) {
                return NULL;
            }
            $build_type = $map->build_type;
        } elseif ( $template = $this->stash( 'fileinfo_template:' . $fileinfo->id ) ) {
            $build_type = $template->build_type;
        } else {
            if ( $template = $this->load( 'Template', $fileinfo->template_id ) ) {
                $build_type = $template->build_type;
            }
        }
        $this->stash( 'file', $fileinfo->file_path );
        $this->stash( 'fileinfo', $fileinfo );
        if ( $build_type == 1 || $build_type == 2 ) {
            $this->stash( 'build_type', 'rebuild_static' );
        } elseif ( $build_type == 4 ) {
            $this->stash( 'build_type', 'publish_queue' );
        }
        $content = NULL;
        $this->stash( 'basename', $fileinfo->template_id );
        if (! $this->run_callbacks( 'build_file_filter', $mt, $ctx ) ) {
            return NULL;
        }
        if ( $build_type == 1 || $build_type == 2 || $force ) {
            $this->set_context_from_fileinfo( $mt, $ctx, $fileinfo );
            $template = $ctx->_get_compile_path( 'mt:' . $fileinfo->template_id );
            $this->stash( 'template', $template );
            if (! $force ) {
                $tmpl = $fileinfo->template();
                if ( $tmpl ) {
                    if ( $tmpl->type == 'index' ) {
                        if ( $identifier = $tmpl->identifier ) {
                            if ( ( $identifier == 'htaccess' ) || ( $identifier == 'dynamic_mtml_bootstrapper' ) ) {
                                return NULL;
                            }
                        }
                        $outfile = $tmpl->outfile;
                        if ( $outfile && ( preg_match( '/^\./', $outfile ) ) ) {
                            return NULL;
                        }
                    }
                    $text = $tmpl->text;
                    $this->stash( 'text', $text );
                    $type = $tmpl->type;
                }
            }
            $output = $ctx->fetch( 'mt:' . $fileinfo->template_id );
            $args = $this->get_args();
            $this->run_callbacks( 'build_page', $mt, $ctx, $args, $output );
            return $output;
        } elseif ( $build_type == 4 ) {
            if ( $this->stash( 'func_map_id' ) ) {
                $func_map = $this->load( 'Ts_Func_Map', array( 'name' => 'MT::Worker::Publish' ),
                                                       array( 'limit' => 1 ) );
                if ( $func_map ) {
                    $func_map_id = $func_map->id;
                    $this->stash( 'func_map_id', $func_map_id );
                }
            }
            $job = $this->get_by_key( 'Ts_Job', array( 'uniqkey' => $fileinfo->id ) );
            $pid = getmypid();
            $at = $fileinfo->archive_type;
            if ( ( $at === 'Individual' ) || ( $at === 'Page' ) ) {
                if ( $map->is_preferred ) {
                    $priority = 10;
                } else {
                    $priority = 5;
                }
            } elseif ( $at === 'index' ) {
                if ( preg_match( "!/(index|default|atom|feed)!i", $fileinfo->file_path ) ) {
                    $priority = 9;
                } else {
                    $priority = 8;
                }
            } elseif ( ( preg_match( '/Category|Author/', $at ) ) || ( preg_match( '/Yearly/', $at ) ) ) {
                $priority = 1;
            } elseif ( preg_match( '/Monthly/', $at ) ) {
                $priority = 2;
            } elseif ( preg_match( '/Weekly/', $at ) ) {
                $priority = 3;
            } elseif ( preg_match( '/Daily/', $at ) ) {
                $priority = 4;
            }
            $time = time();
            $coalesce = $blog_id . ':' . $pid . ':' . $priority . ':' . ( $time - ( $time % 10 ) );
            $job->run_after = $time;
            $job->insert_time = $time;
            $job->coalesce = $coalesce;
            $job->grabbed_until = 0;
            $job->priority = $priority;
            $job->funcid = $this->stash( 'func_map_id' );
            if ( $job->id ) {
                $job->_saved = TRUE;
            }
            $job->Save();
            return TRUE;
        }
    }

    function build_page ( $template, $params = array() ) {
        $ctx = $this->ctx();
        $regex = '<\${0,1}' . 'mt';
        if (! preg_match( "/$regex/i", $template ) ) {
            if ( file_exists( $template ) ) {
                $template = file_get_contents( $template );
            } else {
                return $template;
            }
        }
        if (! $template ) return '';
        if ( is_array( $params ) ) {
            require_once( 'dynamicmtml.util.php' );
            if ( __is_hash( $params ) ) {
                $vars =& $ctx->__stash[ 'vars' ];
                foreach ( $params as $key => $val ) {
                    $vars[ $key ] = $val;
                }
            }
        }
        return $this->build_tmpl( $ctx, $template );
    }

    function build_tmpl ( $ctx, $text, $params = array() ) {
        if ( $params ) {
            if ( isset( $params[ 'fileinfo' ] ) ) $fileinfo = $params[ 'fileinfo' ];
            if ( isset( $params[ 'basename' ] ) ) $basename = $params[ 'basename' ];
            if ( isset( $params[ 'archive_type' ] ) ) $at = $params[ 'archive_type' ];
            if ( isset( $params[ 'blog' ] ) ) $blog = $params[ 'blog' ];
        }
        require_once( 'MTUtil.php' );
        $mt = $this->mt();
        if ( get_class( $ctx ) === 'stdClass' ) {
            $ctx = new MTViewer( $this );
        }
        $ctx->MTViewer( $mt );
        if (! $blog ) $blog = $ctx->stash( 'blog' );
        if (! $blog ) $blog = $this->blog();
        if (! $at ) $at = 'index';
        if ( $fileinfo ) {
            $this->set_context_from_fileinfo( $mt, $ctx, $fileinfo );
        } else {
            if ( $at === 'index' ) {
                $ctx->stash( 'index_archive', TRUE );
            } else {
                $ctx->stash( 'index_archive', FALSE );
            }
            $vars =& $ctx->__stash[ 'vars' ];
            if ( $blog ) {
                $page_layout = $blog->blog_page_layout;
                $columns = get_page_column( $page_layout );
                $vars[ 'page_columns' ] = $columns;
                $vars[ 'page_layout' ] = $page_layout;
                $mt->configure_paths( $blog->site_path() );
            }
        }
        if ( $basename ) {
            $id = 'var:' . $basename;
        } else {
            $id = 'var:' . $this->make_magic_token();
        }
        // require_once( 'prefilter.mt_to_smarty.php' );
        $plugin_dirs = $ctx->plugins_dir;
        if (! $plugin_dirs ) {
            $this_plugins_dir = $this->stash( 'plugins_dir' );
            foreach ( $this_plugins_dir as $dir ) {
                array_push( $plugin_dirs, $dir );
            }
            $ctx->plugins_dir = $plugin_dirs;
        }
        $template = $ctx->_get_compile_path( $id );
        $template = $this->stash( 'template' );
        if (! $template ) {
            $template = $ctx->_get_compile_path( $id );
            $self = $this->root . dirname( $_SERVER[ 'PHP_SELF' ] );
            $template = $self . DIRECTORY_SEPARATOR . $template;
        }
        if ( file_exists( $template ) ) {
            if ( ( $ctx->force_compile ) && ( $basename ) ) {
                unlink ( $template );
            }
        }
        $this->stash( 'build_type', 'build_tmpl' );
        $this->stash( 'text', $text );
        $this->stash( 'template', $template );
        $this->stash( 'basename', $basename );
        if (! $this->run_callbacks( 'rebuild_file_filter', $mt, $ctx ) ) {
            return NULL;
        }
        if (! file_exists( $template ) ) {
            require_once( 'prefilter.mt_to_smarty.php' );
            $build = smarty_prefilter_mt_to_smarty( $text, $ctx );
            $build = $ctx->_compile_source( $id, $build, $_compiled_content );
            $this->write2file( $template, $_compiled_content );
        }
        $ctx->force_compile = FALSE;
        $output = $ctx->fetch( $id );
        $args = $this->get_args();
        $this->run_callbacks( 'rebuild_page', $mt, $ctx, $args, $output );
        if (! $basename ) {
            if ( file_exists( $template ) ) {
                unlink( $template );
            }
        }
        return $output;
    }

    function set_context_from_fileinfo ( &$mt, &$ctx, &$data ) {
        $blog = $this->blog();
        require_once( 'MTUtil.php' );
        $fi_path = $data->fileinfo_url;
        $fid = $data->id;
        $at = $data->archive_type;
        $ts = $data->startdate;
        $tpl_id = $data->template_id;
        $cat = $data->category_id;
        $auth = $data->author_id;
        $entry_id = $data->entry_id;
        if ( $at === 'index' ) {
            $at = NULL;
            $ctx->stash( 'index_archive', TRUE );
        } else {
            $ctx->stash( 'index_archive', FALSE );
        }
        $tmpl = $data->template();
        $ctx->stash( 'template', $tmpl );
        $tts = $tmpl->template_modified_on;
        if ( $tts ) {
            $tts = offset_time( datetime_to_timestamp( $tts ), $blog );
        }
        $ctx->stash( 'template_timestamp', $tts );
        $ctx->stash( 'template_created_on', $tmpl->template_created_on );
        $page_layout = $blog->blog_page_layout;
        $columns = get_page_column( $page_layout );
        $vars =& $ctx->__stash[ 'vars' ];
        $vars[ 'page_columns' ] = $columns;
        $vars[ 'page_layout' ] = $page_layout;
        if ( isset( $tmpl->template_identifier ) )
            $vars[ $tmpl->template_identifier ] = 1;
        $mt->configure_paths( $blog->site_path() );
        $ctx->stash( 'build_template_id', $tpl_id );
        if ( isset( $at ) && ( $at != 'Category' ) ) {
            require_once( 'archive_lib.php' );
            try {
                $archiver = ArchiverFactory::get_archiver( $at );
            } catch ( Execption $e ) {
                $mt->http_errr = 404;
                header( 'HTTP/1.1 404 Not Found' );
                return $ctx->error(
                    $this->translate( 'Page not found - [_1]', $at ), E_USER_ERROR );
            }
            $archiver->template_params( $ctx );
        }
        if ( $cat ) {
            $archive_category = $mt->db()->fetch_category( $cat );
            $ctx->stash( 'category', $archive_category );
            $ctx->stash( 'archive_category', $archive_category );
        }
        if ( $auth ) {
            $archive_author = $mt->db()->fetch_author( $auth );
            $ctx->stash( 'author', $archive_author );
            $ctx->stash( 'archive_author', $archive_author );
        }
        if ( isset( $at ) ) {
            if ( ( $at != 'Category' ) && isset( $ts ) ) {
                list( $ts_start, $ts_end ) = $archiver->get_range( $ts );
                $ctx->stash( 'current_timestamp', $ts_start );
                $ctx->stash( 'current_timestamp_end', $ts_end );
            }
            $ctx->stash( 'current_archive_type', $at );
        }
        if ( isset( $entry_id ) && ( $entry_id )
            && ( $at === 'Individual' || $at === 'Page' ) ) {
            if ( $at === 'Individual' ) {
                // $entry =& $mt->db()->fetch_entry( $entry_id );
                $entry =& $this->load( 'Entry', $entry_id );
            } elseif( $at === 'Page' ) {
                // $entry =& $mt->db()->fetch_page( $entry_id );
                $entry =& $this->load( 'Entry', $entry_id );
            }
            $ctx->stash( 'entry', $entry );
            $ctx->stash( 'current_timestamp', $entry->entry_authored_on );
        }
        if ( $at === 'Category' ) {
            $vars =& $ctx->__stash[ 'vars' ];
            $vars[ 'archive_class' ]    = "category-archive";
            $vars[ 'category_archive' ] = 1;
            $vars[ 'archive_template' ] = 1;
            $vars[ 'archive_listing' ]  = 1;
            $vars[ 'module_category_archives' ] = 1;
        }
    }

    function set_entry_categories ( $entry, $categories ) {
        $class = $entry->class;
        $user = $this->user;
        $container = NULL;
        if ( $class === 'page' ) {
            $container = 'Folder';
        } else {
            $container = 'Category';
        }
        $container_class = strtolower( $container );
        $orig_placements = $entry->placement();
        if (! $categories ) {
            if ( $orig_placements ) {
                foreach( $orig_placements as $old_placenent ) {
                    $old_placenent->Delete();
                }
            }
            return NULL;
        }
        $entry_categories = array();
        if (! is_array( $categories ) ) {
            $category;
            if ( is_object( $categories ) ) {
                $category = $categories;
            } elseif ( is_numeric( $categories ) ) {
                $category = $this->load( $container, $categories );
                if ( $category->blog_id != $entry->blog_id ) {
                    $category = NULL;
                }
                if ( $category->class != $container_class ) {
                    $category = NULL;
                }
            } else {
                $categories = trim( $categories );
                $terms = array( 'label' => $categories,
                                'blog_id' => $entry->blog_id,
                                'class' => $container_class );
                $category = $this->get_by_key( $container, $terms );
                if (! $category->id ) {
                    $basename = dirify( $categories );
                    if (! $basename ) {
                        $basename = uniqid( 'cat_' );
                    }
                    $category->basename = $basename;
                    $category->allow_pings = 0;
                    $category->parent = 0;
                    if ( $user ) {
                        $category->author_id = $user->id;
                        $category->created_by = $user->id;
                    }
                    $ts = $this->current_ts();
                    $category->created_on = $ts;
                    $category->modified_on = $ts;
                    $category->Save();
                }
            }
            if ( $category ) {
                array_push( $entry_categories, $category );
            }
        } else {
            // Object or Numeric
            foreach ( $categories as $category ) {
                if ( is_object( $category ) ) {
                    array_push( $entry_categories, $category );
                } elseif ( is_numeric( $category ) ) {
                    $category = $this->load( $container, $category );
                    if ( $category->blog_id != $entry->blog_id ) {
                        $category = NULL;
                    }
                    if ( $category->class != $container_class ) {
                        $category = NULL;
                    }
                    if ( $category ) {
                        array_push( $entry_categories, $category );
                    }
                }
            }
        }
        $set_ids = array();
        $set_cat_ids = array();
        if ( $entry_categories ) {
            $i = 0;
            foreach ( $entry_categories as $category ) {
                if ( in_array( $category->id, $set_cat_ids ) ) {
                    continue;
                }
                array_push( $set_cat_ids, $category->id );
                $terms = array( 'blog_id'     => $entry->blog_id,
                                'entry_id'    => $entry->id,
                                'category_id' => $category->id );
                $placement = $this->get_by_key( 'Placement', $terms );
                $changed = FALSE;
                if (! $placement->id ) {
                    $changed = TRUE;
                }
                if (! $i ) {
                    if (! $placement->is_primary ) {
                        $placement->is_primary = 1;
                        $changed = TRUE;
                    } else {
                        $placement->is_primary = 1;
                    }
                } else {
                    if ( $placement->is_primary ) {
                        $placement->is_primary = 0;
                        $changed = TRUE;
                    } else {
                        $placement->is_primary = 0;
                    }
                }
                if ( $changed ) {
                    if (! $placement->id ) {
                        $placement->Save();
                    } else {
                        $placement->Update();
                    }
                }
                array_push( $set_ids, $placement->id );
                $i++;
            }
        }
        if ( $orig_placements ) {
            foreach( $orig_placements as $old_placenent ) {
                if (! in_array( $old_placenent->id, $set_ids ) ) {
                    $old_placenent->Delete();
                }
            }
        }
        $this->stash( 'entry_categories:' . $entry->id, $entry_categories );
        $this->set_changed_categories( $entry_categories );
        return $entry_categories;
    }

    function set_tags ( $object, $tags ) {
        $object_ds = $object->_table;
        $object_id = $object->id;
        $object_ds = preg_replace( '/^mt_/', '', $object_ds );
        $orig_tags = $this->fetch_tags( $object, array( 'include_private' => 1 ) );
        if (! $tags ) {
            if ( $orig_tags ) {
                foreach ( $orig_tags as $old_tag ) {
                    $old_tag->Delete();
                }
            }
            $this->stash( $object_ds . "_tag_cache_with_private[{$object_id}]", NULL );
            return NULL;
        }
        $tags_array = array();
        if ( is_array( $tags ) ) {
            foreach ( $tags as $tag ) {
                if ( is_object ( $tag ) ) {
                    array_push( $tags_array, $tag );
                } else {
                    array_push( $tags_array, $this->get_tag_obj( $tag ) );
                }
            }
        } else {
            if ( is_object ( $tag ) ) {
                array_push( $tags_array, $tag );
            } else {
                $tag_split = explode( ',', $tags );
                if ( is_array( $tag_split ) ) {
                    foreach ( $tag_split as $tag ) {
                        array_push( $tags_array, $this->get_tag_obj( trim( $tag ) ) );
                    }
                }
            }
        }
        $set_ids = array();
        if ( $tags_array ) {
            foreach ( $tags_array as $tag_obj ) {
                array_push( $set_ids, $tag_obj->id );
                $terms = array( 'object_id' => $object->id,
                                'object_datasource' => $object_ds,
                                'tag_id' => $tag_obj->id,
                                );
                $objecttag = $this->get_by_key( 'ObjectTag', $terms );
                if (! $objecttag->id ) {
                    if ( $object->has_column( 'blog_id' ) ) {
                        $objecttag->blog_id = $object->blog_id;
                    } else {
                        $objecttag->blog_id = 0;
                    }
                    $objecttag->Save();
                }
            }
        }
        if ( $orig_tags ) {
            foreach ( $orig_tags as $old_tag ) {
                if (! in_array( $old_tag->id, $set_ids ) ) {
                    $old_tag->Delete();
                }
            }
        }
        $this->stash( $object_ds . "_tag_cache_with_private[{$object_id}]", $tags_array );
        return $tags_array;
    }

    function fetch_tags ( $object, $args = NULL ) {
        $ctx = $this->ctx;
        $object_id = $object->id;
        $object_ds = $object->_table;
        $object_ds = preg_replace( '/^mt_/', '', $object_ds );
        if (! isset( $args ) ) {
            if ( $cache = $this->stash( $object_ds . "_tag_cache[{$object_id}]" ) ) {
                return $cache;
            }
        }
        if ( isset( $args[ 'include_private' ] ) ) {
            if ( count( $args ) === 1 ) {
                if ( $cache = $this->stash( $object_ds . "_tag_cache_with_private[{$object_id}]" ) ) {
                    return $cache;
                }
            }
        }
        if (! isset( $args[ 'include_private' ] ) ) {
            $private_filter = 'and (tag_is_private = 0 or tag_is_private is null)';
        }
        $object_filter = 'and objecttag_object_id = ' . $object_id;
        $sort_col = isset( $args[ 'sort_by' ] ) ? $args[ 'sort_by' ] : 'name';
        $sort_col = "tag_$sort_col";
        if ( isset( $args[ 'sort_order' ] ) and $args[ 'sort_order' ] == 'descend' ) {
            $order = 'desc';
        } else {
            $order = 'asc';
        }
        $id_order = '';
        if ( $sort_col == 'tag_name' ) {
            $sort_col = 'lower(tag_name)';
        } else {
            $id_order = ', lower(tag_name)';
        }
        $sql = "
            select tag_id, tag_name, count(*) as tag_count
            from mt_tag, mt_objecttag, mt_{$object_ds}
            where objecttag_tag_id = tag_id
                and {$object_ds}_id = objecttag_object_id and objecttag_object_datasource='{$object_ds}'
                $private_filter
                $object_filter
            group by tag_id, tag_name
            order by $sort_col $order $id_order
        ";
        $rs = $this->db()->SelectLimit( $sql );
        require_once( 'class.mt_tag.php' );
        $tags = array();
        while(! $rs->EOF ) {
            $tag = new Tag;
            $tag->tag_id = $rs->Fields( 'tag_id' );
            $tag->tag_name = $rs->Fields( 'tag_name' );
            $tag->tag_count = $rs->Fields( 'tag_count' );
            $tags[] = $tag;
            $rs->MoveNext();
        }
        if (! isset( $args ) ) {
            $this->stash( $object_ds . "_tag_cache[{$object_id}]", $tags );
        }
        if ( isset( $args[ 'include_private' ] ) ) {
            if ( count( $args ) === 1 ) {
                $this->stash( $object_ds . "_tag_cache_with_private[{$object_id}]", $tags );
            }
        }
        return $tags;
    }

    function get_tag_obj ( $str, $params = NULL ) {
        $str = trim( $str );
        require_once( 'MTUtil.php' );
        $n8d_str = tag_normalize( $str );
        if ( $params && isset( $params[ 'no_generate' ] ) ) {
            $no_generate = 1;
        }
        $tag = $this->get_by_key( 'Tag', array( 'name' => $str ), array( 'limit' => 1 ) );
        $private = preg_match( '/^@/', $str ) ? 1 : 0;
        if ( isset( $tag ) ) {
            if ( $tag->id ) {
                if ( $str != $tag->name ) {
                    $n8d = $this->get_by_key( 'Tag', array( 'n8d_id'     => $tag->id,
                                                            'name'       => $str,
                                                            'is_private' => $private, ) );
                    if (! $n8d->id ) {
                        if (! $no_generate ) {
                            $n8d->Save();
                        } else {
                            return $tag;
                        }
                    }
                    return $n8d;
                }
                return $tag;
            }
            $tag->is_private = $private;
            if ( $n8d_str != $str ) {
                $n8d_tag = $this->get_by_key( 'Tag', array( 'name' => $n8d_str ) );
                if (! $n8d_tag->id ) {
                    $n8d_tag->is_private = $private;
                    $n8d_tag->n8d_id = 0;
                    if (! $no_generate ) {
                        $n8d_tag->Save();
                    }
                }
                $tag->n8d_id = $n8d_tag->id;
            } else {
                $tag->n8d_id = 0;
            }
            if (! $no_generate ) {
                $tag->Save();
            }
            return $tag;
        }
    }

    function model ( $class ) {
        $classname = strtolower( $class );
        $class = ucwords( $class );
        if ( $obj = $this->stash( "model:{$class}" ) ) {
            return clone $obj;
        }
        require_once 'class.mt_' . $classname . '.php';
        $obj = new $class;
        $this->stash( "model:{$class}", $obj );
        $obj->app = $this;
        return $obj;
    }

    function init_models ( $models ) {
        foreach ( $models as $model ) {
            $this->model( $model );
        }
    }

    function load ( $class, $terms, $args = array(), $wantarray = FALSE ) {
        require_once( 'dynamicmtml.util.php' );
        $class = $this->escape( $class );
        $_obj = $this->model( $class );
        if (! $_obj ) {
            return NULL;
        }
        $classname = strtolower( $class );
        $prefix = $classname;
        if ( $classname === 'website' ) {
            $prefix = 'blog';
        } elseif ( $classname === 'folder' ) {
            $prefix = 'category';
        } elseif ( $classname === 'page' ) {
            $prefix = 'entry';
        }
        if ( isset( $_obj->_prefix ) ) {
            $prefix = $_obj->_prefix;
        } else {
            $prefix .= '_';
        }
        $raw_columns = $_obj->raw_columns;
        if (! $raw_columns ) {
            $raw_columns = array();
        }
        $set_class = 0;
        $where = '';
        if ( is_numeric( $terms ) ) {
            if ( $cached_object = $this->stash( "{$prefix}:cached_object:{$terms}" ) ) {
                return $cached_object;
            }
            $where = " $prefix" . "id={$terms} ";
            $obj = $_obj->Find( $where, FALSE, FALSE, array( limit => 1 ) );
            if ( isset( $obj ) ) {
                $this->stash( "{$prefix}:cached_object:" . $obj->id, $obj[0] );
                return $obj[0];
            } else {
                return NULL;
            }
        } elseif ( is_array( $terms ) ) {
            $operators = array( 'like', 'not_like', 'not_null', 'not', '>', '>=', '<', '<=', '!=' );
            $extra_terms = array();
            // TODO:: -and or -or
            foreach ( $terms as $key => $val ) {
                $key = $this->escape( $key );
                if ( $_obj->has_column( $key ) ) {
                    $_prefix = $prefix;
                    if ( preg_match( "/^${_prefix}/", $key ) ) {
                        $key = preg_replace( "/^${_prefix}/", '', $key );
                    }
                    if ( in_array( $key, $raw_columns ) ) $_prefix = '';
                    if ( $key === 'class' ) {
                        $set_class = 1;
                    }
                    if ( $where ) $where .= " AND ";
                    if (! is_array( $val ) ) {
                        $val = $this->escape( $val );
                        if ( $key === 'blog_id' ) {
                            if (! is_numeric( $val ) ) {
                                // include_exclude_blogs
                                $where .= " {$_prefix}{$key} {$val} ";
                            } else {
                                $where .= " {$_prefix}blog_id={$val} ";
                            }
                        } else {
                            $where .= " {$_prefix}{$key}='{$val}' ";
                        }
                    } else {
                        $expression = '';
                        if ( __is_hash( $val ) ) {
                            foreach ( $val as $op => $value ) {
                                $value = $this->escape( $value );
                                if ( $expression ) $expression .= " OR ";
                                if ( in_array( $op, $operators ) ) {
                                    // 'like', 'not_like', 'not_null', 'not', '>', '>=',
                                    // '<', '<=', '!='
                                    if ( $op === 'not' ) {
                                        $op = '!=';
                                    }
                                    if ( $op === 'not_null' ) {
                                        $op = 'IS NOT NULL';
                                        $expression .= " {$_prefix}{$key} {$op} ";
                                    } else {
                                        $op = strtoupper( $op );
                                        $op = strtr( $op, '_', ' ' );
                                        // $op = preg_replace( '/_/', ' ', $op );
                                        $expression .= " {$_prefix}{$key} {$op} '{$value}' ";
                                    }
                                }
                            }
                        } else {
                            $range_expression = '';
                            if ( count( $val ) === 2 ) {
                                $eq = NULL;
                                $range = NULL;
                                if ( $args[ 'range' ] && $args[ 'range' ][ $key ] ) {
                                    $range = 1;
                                } elseif ( $args[ 'range_incl' ] && $args[ 'range_incl' ][ $key ] ) {
                                    $range = 1;
                                    $eq = '=';
                                }
                                if ( $range ) {
                                    $start = $val[0];
                                    $start = $this->escape( $start );
                                    if ( $start ) {
                                        $start = $this->__ts2db( $key, $start );
                                        $range_expression .= " {$_prefix}{$key} >{$eq} '{$start}' ";
                                    }
                                    $end = $val[1];
                                    $end = $this->escape( $end );
                                    if ( $end ) {
                                        $end = $this->__ts2db( $key, $end );
                                        if ( $range_expression ) $range_expression .= " AND ";
                                        $range_expression .= " {$_prefix}{$key} <{$eq} '{$end}' ";
                                    }
                                }
                            }
                            if ( $range_expression ) {
                                $expression = $range_expression;
                            } else {
                                foreach ( $val as $value ) {
                                    if ( $expression ) $expression .= " OR ";
                                    $value = $this->escape( $value );
                                    $expression .= " {$_prefix}{$key}='{$value}' ";
                                }
                            }
                        }
                        $where .= " ( {$expression} ) ";
                    }
                } else {
                    array_push( $extra_terms, array( $key => $val ) );
                }
            }
            if (! $set_class ) {
                if ( $_obj->has_column( 'class' ) ) {
                    if ( $where ) $where .= " AND ";
                    $where .= " {$prefix}class='{$classname}' ";
                }
            }
            if ( $extra_terms ) {
                // TODO:: Search meta and more expression.
            }
            if (! $where ) {
                // Where statement is required.
                $where .= " {$prefix}id IS NOT NULL ";
            }
            if ( isset( $args[ 'start_val' ] ) ) {
                if ( isset( $args[ 'sort' ] ) ) {
                    $key = $args[ 'sort' ];
                    $start_val = $args[ 'start_val' ];
                    $start_val = $this->escape( $start_val );
                    $start_val = $this->__ts2db( $key, $start_val );
                    if ( $where ) $where .= ' AND ';
                    $op = '>';
                    if ( isset( $args[ 'direction' ] ) ) {
                        if ( $args[ 'direction' ] == 'descend' ) {
                            $op = '<';
                        }
                    }
                    $_prefix = $prefix;
                    if ( in_array( $key, $raw_columns ) ) $_prefix = '';
                    $where .= " {$_prefix}{$key} {$op} '{$start_val}' ";
                }
                unset( $args[ 'start_val' ] );
            }
            if ( isset( $args[ 'sort' ] ) ) {
                $sort = $this->escape( $args[ 'sort' ] );
                if ( $_obj->has_column( $sort ) ) {
                    $_prefix = $prefix;
                    if ( in_array( $key, $raw_columns ) ) $_prefix = '';
                    $sort_by = " {$_prefix}{$sort}";
                    if ( isset( $args[ 'direction' ] ) ) {
                        $sort_order = $args[ 'direction' ];
                        unset( $args[ 'direction' ] );
                    }
                    if ( $sort_order != 'descend' ) {
                        $sort_order = 'ASC';
                    } else {
                        $sort_order = 'DESC';
                    }
                }
                unset( $args[ 'sort' ] );
                $where .= " order by $sort_by $sort_order ";
            }
            if ( isset( $args[ 'wantarray' ] ) ) {
                $wantarray = TRUE;
                unset( $args[ 'wantarray' ] );
            }
            // TODO:: If 'join' and 'unique' => 1 ?
            if ( isset( $args[ 'join' ] ) ) {
                $args[ 'distinct' ] = 1;
                // ( array( 'mt_placement', 'entry_id', array( 'category_id' => $category_id ) ) );
                //   or
                // ( array( 'mt_placement' => array( 'condition' =>
                //          "entry_id=placement_entry_id AND ( placement_category_id={$category_id}" ) ) );
                $arg_array = $args[ 'join' ];
                if ( is_array( $arg_array ) ) {
                    if ( isset( $arg_array[0] ) && is_string( $arg_array[0] ) ) {
                        $class_name = $arg_array[0];
                        if ( isset( $arg_array[1] ) && is_string( $arg_array[1] ) ) {
                            if ( is_array( $arg_array[2] ) ) {
                                $column = preg_replace( '/^mt_/', '', $arg_array[0] );
                                // $extras = $arg_array[1] . '=' . $column . '_' . $arg_array[1] . ' AND ';
                                $extras = $arg_array[1] . '=' . $column . '.' . $arg_array[1] . ' AND ';
                                $expression = '';
                                if ( is_array( $arg_array[2] ) && __is_hash( $arg_array[2] ) ) {
                                    foreach ( $arg_array[2] as $key => $val ) {
                                        if ( $expression ) $expression .= ' AND ';
                                        if ( is_array( $val ) && __is_hash( $val ) && ( count( $val ) === 1 ) ) {
                                            foreach ( $val as $op => $value ) {
                                                $value = $this->escape( $value );
                                                $value = $this->__ts2db( $key, $value );
                                                if ( in_array( $op, $operators ) ) {
                                                    if ( $op === 'not' ) {
                                                        $op = '!=';
                                                    }
                                                    if ( $op === 'not_null' ) {
                                                        $op = 'IS NOT NULL';
                                                        $expression .= "$column.{$key} {$op} ";
                                                    } else {
                                                        $op = strtoupper( $op );
                                                        // $op = preg_replace( '/_/', ' ', $op );
                                                        $op = strtr( $op, '_', ' ' );
                                                        $expression .= " {$column}.{$key} {$op} '{$value}' ";
                                                    }
                                                }
                                            }
                                        } else {
                                            $val = $this->escape( $val );
                                            $val = $this->__ts2db( $key, $val );
                                            //$expression .= " {$column}_{$key}='$val'";
                                            $expression .= " {$column}.{$key}='$val'";
                                        }
                                    }
                                }
                                $extras .= " ( {$expression} ) ";
                                $join = array( $class_name => array( 'condition' => $extras ) );
                                $args[ 'join' ] = $join;
                            }
                        }
                    }
                }
            }
            $obj = $_obj->Find( $where, FALSE, FALSE, $args );
            if ( isset( $obj ) ) {
                if ( isset( $args[ 'limit' ] ) && $args[ 'limit' ] == 1 && $wantarray == FALSE ) {
                    $this->stash( "{$prefix}:cached_object:" . $obj[0]->id, $obj[0] );
                    return $obj[0];
                }
                return $obj;
            } else {
                if ( isset( $args[ 'limit' ] ) && $args[ 'limit' ] == 1 && $wantarray == FALSE ) {
                    return NULL;
                } else {
                    return array();
                }
            }
        }
        return NULL;
    }

    function save ( $obj, $do = 'save' ) {
        $class = get_class( $obj );
        $original = $this->___clone( $class, $obj );
        $res = NULL;
        $key = NULL;
        $this->stash( 'obj', $obj );
        $this->stash( 'original', $original );
        if ( isset( $obj->_prefix ) ) {
            $prefix = $obj->_prefix;
        } else {
            $prefix = $obj->_table;
            $prefix = preg_replace( '/^mt_/', '', $prefix );
            $prefix .= '_';
        }
        if (! $this->run_callbacks( "{$do}_permission_filter.{$class}", $this->mt(), $this->ctx(), $this->args ) ) {
            return $res;
        }
        if ( $do === 'save' ) {
            if ( $obj->id ) {
                $res = $obj->Update();
            } else {
                $res = $obj->Save();
            }
            $this->stash( 'obj', $obj );
            $this->stash( "{$prefix}:cached_object:" . $obj->id, $obj );
        } elseif ( $do === 'delete' ) {
            $res = $obj->Delete();
            $this->stash( 'obj', $obj );
            $this->stash( "{$prefix}:cached_object:" . $obj->id, NULL );
            // TODO:: Remove children and related objects.
        }
        $this->run_callbacks( "post_{$do}.{$class}", $this->mt(), $this->ctx() );
        return $res;
    }

    function remove ( $obj ) {
        $this->save( $obj, 'delete' );
    }

    function __ts2db ( $key, $value ) {
        $db = $this->db();
        if ( preg_match( '/_on$/', $key ) ) {
            if ( preg_match( '/^[0-9]{14}$/', $value ) ) {
                $value = $db->ts2db( $value );
            }
        }
        return $value;
    }

    function count ( $class, $terms, $args = array() ) {
        // TODO:: AS CNT
        $objects = $this->load( $class, $terms, $args, 'wantarray' );
        if ( $objects ) {
            return count( $objects );
        }
        return 0;
    }

    function get_by_key ( $class, $terms ) {
        $args = array( 'limit' => 1 );
        $obj = $this->load( $class, $terms, $args );
        if (! $obj ) {
            $obj = $this->model( $class );
            if (! $obj ) {
                return NULL;
            }
            $obj->set_values( $terms );
        }
        return $obj;
    }

    function exist ( $class, $terms ) {
        $obj = $this->get_by_key( $class, $terms );
        if ( $obj->id ) {
            return 1;
        }
        return 0;
    }

    function column_values ( $obj ) {
        return $obj->GetArray();
    }

    function column_names ( $obj ) {
        return $obj->GetAttributeNames();
    }

    function delete_entry ( $entry ) {
        $ctx = $this->ctx;
        $id = $entry->id;
        $blog = $this->blog;
        if (! $this->can_edit_entry( $entry ) ) {
            return $ctx->error( $this->translate( 'Permission denied.' ) );
        }
        $this->set_changed_entries( $entry );
        $related = array( 'ObjectTag', 'ObjectScore', 'ObjectAsset' );
        $children = array( 'Trackback', 'Comment', 'FileInfo', 'Placement' );
        foreach ( $related as $class ) {
            $ds = NULL;
            if ( $class === 'ObjectTag' ) {
                $ds = 'object_datasource';
            } else {
                $ds = 'object_ds';
            }
            $classname = strtolower( $class );
            require_once 'class.mt_' . $classname . '.php';
            $_related_obj = new $class;
            $where = " $classname" . "_object_id={$id} and $classname" . "_$ds='entry'";
            $related_obj = $_related_obj->Find( $where, FALSE, FALSE, array() );
            if ( isset( $related_obj ) ) {
                foreach ( $related_obj as $rel_obj ) {
                    $rel_obj->Delete();
                }
            }
        }
        require_once 'class.mt_tbping.php';
        foreach ( $children as $child ) {
            $classname = strtolower( $child );
            require_once 'class.mt_' . $classname . '.php';
            $_child_obj = new $child;
            $where = " $classname" . "_entry_id={$id} ";
            $children_obj = $_child_obj->Find( $where, FALSE, FALSE, array() );
            if ( isset( $children_obj ) ) {
                foreach ( $children_obj as $child_obj ) {
                    $child_id = $child_obj->id;
                    if ( $child == 'Trackback' ) {
                        $_tbping = new TBPing;
                        $where = " tbping_tb_id={$child_id} ";
                        $tbping = $_tbping->Find( $where, FALSE, FALSE, array() );
                        if ( isset( $tbping ) ) {
                            foreach ( $tbping as $ping ) {
                                $ping->Delete();
                            }
                        }
                    }
                    if ( $child == 'FileInfo' ) {
                        $file_path = $child->file_path;
                        if ( file_exists( $file_path ) ) {
                            unlink( $file_path );
                        }
                    }
                    $child_obj->Delete();
                }
            }
        }
        $this->remove( $entry );
        // $entry->Delete();
        $this->touch_blog();
    }

    function can_edit_entry( $entry ) {
        $user = $this->user();
        $ctx = $this->ctx;
        if (! $user ) {
            return 0;
        }
        $can_edit = 0;
        if (! $this->can_do( $ctx, 'edit_all_posts' ) ) {
            if ( $this->can_do( $ctx, 'create_post' ) ) {
                if ( $entry->author_id == $user->id ) {
                    $can_edit = 1;
                }
            }
            if (! $this->can_do( $ctx, 'publish_post' ) ) {
                if ( $entry->status != 1 ) {
                    $can_edit = 0;
                }
            }
        } else {
            $can_edit = 1;
        }
        return $can_edit;
    }

    function touch_blog ( $blog = NULL ) {
        if (! $blog ) {
            $blog = $this->blog;
        }
        $ts = $this->current_ts();
        if ( $ts != $blog->children_modified_on ) {
            $blog->children_modified_on = $ts;
            $blog->Update();
        }
    }

    function current_ts ( $blog = NULL ) {
        if (! $blog ) {
            $blog = $this->blog;
        }
        require_once( 'MTUtil.php' );
        $t = time();
        $ts = offset_time_list( $t, $blog );
        $ts = sprintf( "%04d%02d%02d%02d%02d%02d",
                        $ts[5]+1900, $ts[4]+1, $ts[3], $ts[2], $ts[1], $ts[0] );
        return $ts;
    }

    function non_dynamic_mtml ( $content ) {
        $regex = '<\${0,1}mt:{0,1}DynamicMTML.*?>.*?<\/\${0,1}mt:{0,1}DynamicMTML.*?>';
        $content = preg_replace( "/$regex/is", '', $content );
        $regex = '<\/{0,1}\${0,1}mt:{0,1}NonDynamicMTML.*?>';
        $content = preg_replace( "/$regex/is", '', $content );
        $regex = '<\/{0,1}\${0,1}mt:{0,1}.*?>';
        $content = preg_replace( "/$regex/is", '', $content );
        return $content;
    }
}
?>