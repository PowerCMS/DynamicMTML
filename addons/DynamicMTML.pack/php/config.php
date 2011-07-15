<?php
class DynamicMTML_pack extends MTPlugin {
    var $app;
    var $registry = array(
        'name' => 'DynamicMTML',
        'id'   => 'DynamicMTML',
        'key'  => 'dynamicmtml',
        'author_name' => 'Alfasado Inc.',
        'author_link' => 'http://alfasado.net/',
        'version' => '1.5.2',
        'description' => 'DynamicMTML is PHP extension for Movable Type.',
        'config_settings' => array( // mt-config.cgi
            'DynamicForceCompile' => array( 'default' => 0 ),
            'DisableCompilerWarnings' => array( 'default' => 0 ),
            'UserSessionTimeoutNoCheck' => array( 'default' => 0 ),
            'DynamicSiteBootstrapper' => array( 'default' => '.mtview.php' ),
            'DynamicIncludeStatic' => array( 'default' => 0 ),
            'AllowMagicQuotesGPC' => array( 'default' => 0 ),
            'DynamicPHPFirst' => array( 'default' => 0 ),
        ),
        'settings' => array( // PluginSettings
            'example_setting' => array( 'default' => 1 ),
        ),
        'tags' => array( // Template Tags
            'block'    => array( 'dynamicmtml' => 'dynamicmtml',
                                 'rawmtml' => 'rawmtml',
                                 'nondynamicmtml' => 'nondynamicmtml',
                                 'clientauthorblock' => 'clientauthorblock',
                                 'loginauthorctx' => 'clientauthorblock',
                                 'entrycategoryblock' => 'entrycategoryblock',
                                 'commentstrip' => 'commentstrip',
                                 'commentout' => 'commentout',
                                 'queryloop' => 'queryloop',
                                 'splitvars' => 'splitvars',
                                 'setqueryvars' => 'setqueryvars',
                                 'searchentries' => 'searchentries',
                                 'referralkeywords' => 'referralkeywords',
                                 'queryvars' => 'queryvars',
                                 'ifblogdynamiccache' => 'ifblogdynamiccache',
                                 'ifblogdynamicconditional' => 'ifblogdynamicconditional',
                                 'ifblogdynamicmtml' => 'ifblogdynamicmtml',
                                 'iflogin' => 'iflogin',
                                 'ifuseragent' => 'ifuseragent',
                                 'ifuserhaspermission' => 'ifuserhaspermission',
                                 'seterrorhandler' => 'seterrorhandler',
                                 'striptags' => 'striptags',
                                 'buildrecurs' => 'buildrecurs',
                                 ),
            'function' => array( 'authorlanguage' => 'authorlanguage',
                                 'useragent' => 'useragent',
                                 'blogdynamicdirectoryindex' => 'blogdynamicdirectoryindex',
                                 'blogdynamicexcludeextension' => 'blogdynamicexcludeextension',
                                 'blogdynamicmtmlcache' => 'blogdynamicmtmlcache',
                                 'blogdynamicmtmlconditional' => 'blogdynamicmtmlconditional',
                                 'blogdynamicsearchcacheexpiration' => 'blogdynamicsearchcacheexpiration',
                                 'blogfilesmatch' => 'blogfilesmatch',
                                 'blogfilesmatchdirective' => 'blogfilesmatchdirective',
                                 'currentarchivefile' => 'currentarchivefile',
                                 'currentarchiveurl' => 'currentarchiveurl',
                                 'dynamicsitebootstrapper' => 'dynamicsitebootstrapper',
                                 'entrystatusint' => 'entrystatusint',
                                 'filegetcontents' => 'filegetcontents',
                                 'ml' => 'mtml',
                                 'mtml' => 'mtml',
                                 'pluginpath' => 'pluginpath',
                                 'pluginversion' => 'pluginversion',
                                 'powercmsfilesdir' => 'powercmsfilesdir',
                                 'query' => 'query',
                                 'rawmtmltag' => 'mtml',
                                 'trans' => 'trans',
                                 'referralkeyword' => 'referralkeyword',
                                 'rand' => 'mtrand',
                                 'tablecolumnvalue' => 'tablecolumnvalue',
                                 'error' => 'error',
                                 ),
            'modifier' => array( 'trimwhitespace' => 'trimwhitespace',
                                 'highlightingsearchword' => 'highlightingsearchword',
                                 'make_seo_basename' => 'make_seo_basename',
                                 'intval' => 'intval' ),
        ),
        'tasks' => array( // Tasks
            'FuturePost' => array( 'label' => 'Publish Scheduled Entries',
                                   'code'  => 'publish_scheduled_entries',
                                   'frequency' => 60, ),
            'CleanTemporaryFiles' => array( 'label' => 'Remove Temporary Files',
                                            'code'  => 'clean_temporary_files',
                                            'frequency' => 3600, ),
        ),
        'task_workers' => array( // Workers
            'mt_rebuild' => array( 'label' => 'Publishes content.',
                                   'code'  => 'workers_mt_rebuild',
                                   'class' => 'MT::Worker::Publish', ),
        ),
        'callbacks' => array( // Callbacks
            'build_page' => 'filter_build_page',
            'post_init'  => 'post_init_routine',
        ),
    );

    function tags_dir () {
        return dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'tags' . DIRECTORY_SEPARATOR;
    }

    // Callbacks
    function post_init_routine ( $mt, &$ctx ) {
        return 1;
    }

    function filter_build_page ( $mt, &$ctx, &$args, &$content ) {
        return 1;
    }

    // Tasks
    function clean_temporary_files ( &$app ) {
        $do;
        $ts = time() - 3600;
        $extra = array( 'sort' => 'start',
                        'direction' => 'descend',
                        'start_val' => $ts );
        $files = $this->app->load( 'Session', array( 'kind' => 'TF' ), $extra );
        foreach ( $files as $file ) {
            if ( file_exists( $file->name ) ) {
                unlink( $file->name );
            }
            if (! file_exists( $file->name ) ) {
                $file->Delete();
                $do = 1;
            }
        }
        return $do;
    }

    function publish_scheduled_entries ( &$app ) {
        $do;
        $blogs = $app->load( 'Blog',
                              array( 'class' => array( 'blog', 'website' ) ),
                              array( 'sort' => 'id', 'sort_order' => 'ascend' ) );
        if (! $blogs ) return 0;
        $mt = $app->mt();
        $ctx = $app->ctx();
        $orig_blog_id = $app->blog_id;
        foreach ( $blogs as $blog ) {
            $update;
            $app->init_mt( $mt, $ctx, $blog->id );
            $ts = $app->current_ts( $blog );
            $extra = array( 'sort' => 'authored_on',
                            'direction' => 'descend',
                            'start_val' => $ts );
            $entries = $app->load( 'Entry', 
                                   array( 'class' => array( 'entry', 'page' ),
                                          'status' => 4,
                                          'blog_id' => $blog->id ), $extra );
            foreach ( $entries as $entry ) {
                $original = $app->___clone( 'Entry', $entry );
                $entry->status = 2;
                $app->save_entry( $entry, array( 'rebuild' => 0 ) );
                $app->stash( 'original', $original );
                $app->stash( 'entry', $entry );
                $app->stash( 'obj', $entry );
                $app->run_callbacks( 'scheduled_post_published', $mt, $ctx );
                $do = 1;
                $update = 1;
            }
            if ( $update ) {
                $app->rebuild( array ( 'Blog' => $blog ), 'updated' );
            }
        }
        if ( $do ) {
            if ( $orig_blog_id ) {
                $app->init_mt( $mt, $ctx, $orig_blog_id );
            }
        }
        return $do;
    }

    // Workers
    function workers_mt_rebuild ( &$app, $jobs ) {
        $do;
        $start = time();
        $files = 0;
        foreach ( $jobs as $job ) {
            $uniqkey = intval( $job->uniqkey );
            if ( $uniqkey ) {
                $fileinfo = $app->load( 'FileInfo', $uniqkey );
                if ( $fileinfo ) {
                    if ( $file_path = $fileinfo->file_path ) {
                        if ( $output = $app->rebuild_from_fileinfo( $fileinfo, 1 ) ) {
                            if ( $output != NULL ) {
                                if ( $app->content_is_updated( $file_path, $output ) ) {
                                    $app->put_data( $output, $file_path );
                                    $args = $app->get_args();
                                    $app->run_callbacks( 'rebuild_file', $app->mt(), $app->ctx(),
                                                                                     $args, $output );
                                    $do = 1;
                                    $files ++;
                                }
                            }
                        }
                    }
                }
            }
            $job->Delete();
        }
        if ( $do ) {
            $end = time();
            $time = $end - $start;
            $app->log( $app->translate( '-- set complete ([quant,_1,file,files] in [_2] seconds)',
                                        array( $files, $time ) ) );
        }
        return $do;
    }

    // Template Tags
    // Block Tags
    function dynamicmtml ( $args, $content, &$ctx, &$repeat ) {
        require_once( $this->tags_dir() . 'block.mtdynamicmtml.php' );
        return smarty_block_mtdynamicmtml( $args, $content, $ctx, $repeat );
    }

    function nondynamicmtml ( $args, $content, &$ctx, &$repeat ) {
        require_once( $this->tags_dir() . 'block.mtnondynamicmtml.php' );
        return smarty_block_mtnondynamicmtml( $args, $content, $ctx, $repeat );
    }

    function splitvars ( $args, $content, &$ctx, &$repeat ) {
        require_once( $this->tags_dir() . 'block.mtsplitvars.php' );
        return smarty_block_mtsplitvars( $args, $content, $ctx, $repeat );
    }

    function clientauthorblock ( $args, $content, &$ctx, &$repeat ) {
        require_once( $this->tags_dir() . 'block.mtclientauthorblock.php' );
        return smarty_block_mtclientauthorblock( $args, $content, $ctx, $repeat );
    }

    function setqueryvars ( $args, $content, &$ctx, &$repeat ) {
        require_once( $this->tags_dir() . 'block.mtsetqueryvars.php' );
        return smarty_block_mtsetqueryvars( $args, $content, $ctx, $repeat );
    }

    function searchentries ( $args, $content, &$ctx, &$repeat ) {
        require_once( $this->tags_dir() . 'block.mtsearchentries.php' );
        return smarty_block_mtsearchentries( $args, $content, $ctx, $repeat );
    }

    function referralkeywords ( $args, $content, &$ctx, &$repeat ) {
        require_once( $this->tags_dir() . 'block.mtreferralkeywords.php' );
        return smarty_block_mtreferralkeywords( $args, $content, $ctx, $repeat );
    }

    function rawmtml ( $args, $content, &$ctx, &$repeat ) {
        require_once( $this->tags_dir() . 'block.mtrawmtml.php' );
        return smarty_block_mtrawmtml( $args, $content, $ctx, $repeat );
    }

    function queryvars ( $args, $content, &$ctx, &$repeat ) {
        require_once( $this->tags_dir() . 'block.mtqueryvars.php' );
        return smarty_block_mtqueryvars( $args, $content, $ctx, $repeat );
    }

    function queryloop ( $args, $content, &$ctx, &$repeat ) {
        require_once( $this->tags_dir() . 'block.mtqueryloop.php' );
        return smarty_block_mtqueryloop( $args, $content, $ctx, $repeat );
    }

    function ifuserhaspermission ( $args, $content, &$ctx, &$repeat ) {
        require_once( $this->tags_dir() . 'block.mtifuserhaspermission.php' );
        return smarty_block_mtifuserhaspermission( $args, $content, $ctx, $repeat );
    }

    function seterrorhandler ( $args, $content, &$ctx, &$repeat ) {
        require_once( $this->tags_dir() . 'block.mtseterrorhandler.php' );
        return smarty_block_mtseterrorhandler( $args, $content, $ctx, $repeat );
    }

    function striptags ( $args, $content, &$ctx, &$repeat ) {
        require_once( $this->tags_dir() . 'block.mtstriptags.php' );
        return smarty_block_mtstriptags( $args, $content, $ctx, $repeat );
    }

    function buildrecurs ( $args, $content, &$ctx, &$repeat ) {
        return $content;
    }

    function ifuseragent ( $args, $content, &$ctx, &$repeat ) {
        require_once( $this->tags_dir() . 'block.mtifuseragent.php' );
        return smarty_block_mtifuseragent( $args, $content, $ctx, $repeat );
    }

    function iflogin ( $args, $content, &$ctx, &$repeat ) {
        require_once( $this->tags_dir() . 'block.mtiflogin.php' );
        return smarty_block_mtiflogin( $args, $content, $ctx, $repeat );
    }

    function ifblogdynamicmtml ( $args, $content, &$ctx, &$repeat ) {
        require_once( $this->tags_dir() . 'block.mtifblogdynamicmtml.php' );
        return smarty_block_mtifblogdynamicmtml( $args, $content, $ctx, $repeat );
    }

    function ifblogdynamicconditional ( $args, $content, &$ctx, &$repeat ) {
        require_once( $this->tags_dir() . 'block.mtifblogdynamicconditional.php' );
        return smarty_block_mtifblogdynamicconditional( $args, $content, $ctx, $repeat );
    }

    function ifblogdynamiccache ( $args, $content, &$ctx, &$repeat ) {
        require_once( $this->tags_dir() . 'block.mtifblogdynamiccache.php' );
        return smarty_block_mtifblogdynamiccache( $args, $content, $ctx, $repeat );
    }

    function entrycategoryblock ( $args, $content, &$ctx, &$repeat ) {
        require_once( $this->tags_dir() . 'block.mtentrycategoryblock.php' );
        return smarty_block_mtentrycategoryblock( $args, $content, $ctx, $repeat );
    }

    function commentstrip ( $args, $content, &$ctx, &$repeat ) {
        require_once( $this->tags_dir() . 'block.mtcommentstrip.php' );
        return smarty_block_mtcommentstrip( $args, $content, $ctx, $repeat );
    }

    function commentout ( $args, $content, &$ctx, &$repeat ) {
        require_once( $this->tags_dir() . 'block.mtcommentout.php' );
        return smarty_block_mtcommentout( $args, $content, $ctx, $repeat );
    }

    // Function Tags
    function useragent ( $args, &$ctx ) {
        require_once( $this->tags_dir() . 'function.mtuseragent.php' );
        return smarty_function_mtuseragent( $args, $ctx );
    }

    function authorlanguage ( $args, &$ctx ) {
        require_once( $this->tags_dir() . 'function.mtauthorlanguage.php' );
        return smarty_function_mtauthorlanguage( $args, $ctx );
    }

    function trans ( $args, &$ctx ) {
        require_once( $this->tags_dir() . 'function.mttrans.php' );
        return smarty_function_mttrans( $args, $ctx );
    }

    function tablecolumnvalue ( $args, &$ctx ) {
        require_once( $this->tags_dir() . 'function.mttablecolumnvalue.php' );
        return smarty_function_mttablecolumnvalue( $args, $ctx );
    }

    function error ( $args, &$ctx ) {
        require_once( $this->tags_dir() . 'function.mterror.php' );
        return smarty_function_mterror( $args, $ctx );
    }

    function referralkeyword ( $args, &$ctx ) {
        require_once( $this->tags_dir() . 'function.mtreferralkeyword.php' );
        return smarty_function_mtreferralkeyword( $args, $ctx );
    }

    function query ( $args, &$ctx ) {
        require_once( $this->tags_dir() . 'function.mtquery.php' );
        return smarty_function_mtquery( $args, $ctx );
    }

    function powercmsfilesdir ( $args, &$ctx ) {
        require_once( $this->tags_dir() . 'function.mtpowercmsfilesdir.php' );
        return smarty_function_mtpowercmsfilesdir( $args, $ctx );
    }

    function pluginversion ( $args, &$ctx ) {
        require_once( $this->tags_dir() . 'function.mtpluginversion.php' );
        return smarty_function_mtpluginversion( $args, $ctx );
    }

    function pluginpath ( $args, &$ctx ) {
        require_once( $this->tags_dir() . 'function.mtpluginpath.php' );
        return smarty_function_mtpluginpath( $args, $ctx );
    }

    function mtml ( $args, &$ctx ) {
        require_once( $this->tags_dir() . 'function.mtml.php' );
        return smarty_function_mtml( $args, $ctx );
    }
    
    function mtrand ( $args, &$ctx ) {
        require_once( $this->tags_dir() . 'function.mtrand.php' );
        return smarty_function_mtrand( $args, $ctx );
    }

    function filegetcontents ( $args, &$ctx ) {
        require_once( $this->tags_dir() . 'function.mtfilegetcontents.php' );
        return smarty_function_mtfilegetcontents( $args, $ctx );
    }

    function entrystatusint ( $args, &$ctx ) {
        require_once( $this->tags_dir() . 'function.mtentrystatusint.php' );
        return smarty_function_mtentrystatusint( $args, $ctx );
    }

    function dynamicsitebootstrapper ( $args, &$ctx ) {
        require_once( $this->tags_dir() . 'function.mtdynamicsitebootstrapper.php' );
        return smarty_function_mtdynamicsitebootstrapper( $args, $ctx );
    }

    function currentarchiveurl ( $args, &$ctx ) {
        require_once( $this->tags_dir() . 'function.mtcurrentarchiveurl.php' );
        return smarty_function_mtcurrentarchiveurl( $args, $ctx );
    }

    function currentarchivefile ( $args, &$ctx ) {
        require_once( $this->tags_dir() . 'function.mtcurrentarchivefile.php' );
        return smarty_function_mtcurrentarchivefile( $args, $ctx );
    }

    function blogfilesmatchdirective ( $args, &$ctx ) {
        require_once( $this->tags_dir() . 'function.mtblogfilesmatchdirective.php' );
        return smarty_function_mtblogfilesmatchdirective( $args, $ctx );
    }

    function blogfilesmatch ( $args, &$ctx ) {
        require_once( $this->tags_dir() . 'function.mtblogfilesmatch.php' );
        return smarty_function_mtblogfilesmatch( $args, $ctx );
    }

    function blogdynamicsearchcacheexpiration ( $args, &$ctx ) {
        require_once( $this->tags_dir() . 'function.mtblogdynamicsearchcacheexpiration.php' );
        return smarty_function_mtblogdynamicsearchcacheexpiration( $args, $ctx );
    }

    function blogdynamicmtmlconditional ( $args, &$ctx ) {
        require_once( $this->tags_dir() . 'function.mtblogdynamicmtmlconditional.php' );
        return smarty_function_mtblogdynamicmtmlconditional( $args, $ctx );
    }

    function blogdynamicmtmlcache ( $args, &$ctx ) {
        require_once( $this->tags_dir() . 'function.mtblogdynamicmtmlcache.php' );
        return smarty_function_mtblogdynamicmtmlcache( $args, $ctx );
    }

    function blogdynamicexcludeextension ( $args, &$ctx ) {
        require_once( $this->tags_dir() . 'function.mtblogdynamicexcludeextension.php' );
        return smarty_function_mtblogdynamicexcludeextension( $args, $ctx );
    }

    function blogdynamicdirectoryindex ( $args, &$ctx ) {
        require_once( $this->tags_dir() . 'function.mtblogdynamicdirectoryindex.php' );
        return smarty_function_mtblogdynamicdirectoryindex( $args, $ctx );
    }

    // Modifiers
    function trimwhitespace ( $text, $arg ) {
        require_once( $this->tags_dir() . 'modifier.trimwhitespace.php' );
        return smarty_modifier_trimwhitespace( $text, $arg );
    }
    
    function highlightingsearchword ( $text, $arg ) {
        require_once( $this->tags_dir() . 'modifier.highlightingsearchword.php' );
        return smarty_modifier_highlightingsearchword( $text, $arg );
    }

    function make_seo_basename ( $text, $arg ) {
        require_once( $this->tags_dir() . 'modifier.make_seo_basename.php' );
        return smarty_modifier_make_seo_basename( $text, $arg );
    }

    function intval ( $text, $arg ) {
        require_once( $this->tags_dir() . 'modifier.intval.php' );
        return smarty_modifier_intval( $text, $arg );
    }

}

?>