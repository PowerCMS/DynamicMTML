<?php

require_once( dirname( dirname( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'wp-prefix.php' );

class Wordpress extends MTPlugin {

    var $registry = array(
        'name' => 'Wordpress',
        'id'   => 'Wordpress',
        'key'  => 'wordpress',
        'author_name' => 'Alfasado Inc.',
        'author_link' => 'http://alfasado.net/',
        'version' => '0.1',
        'description' => 'MTML for Wordpress.',
        'tags' => array(
            'block'    => array( 'wpget_posts' => 'wp_get_posts',
                                 'wpentries' => 'wp_get_posts',
                                 'wpposts' => 'wp_get_posts',
                                 'wpnext_post' => 'wp_post_nextprev',
                                 'wpprevious_post' => 'wp_post_nextprev',
                                 'wpentrynext' => 'wp_post_nextprev',
                                 'wpentryprevious' => 'wp_post_nextprev',
                                 'wpcategories' => 'wp_list_categories',
                                 'wptags' => 'wp_list_categories',
                                 'wplist_categories' => 'wp_list_categories',
                                 'wpsubcategories' => 'wp_list_categories',
                                 'wpget_the_category' => 'wp_postcategories',
                                 'wpentrycategories' => 'wp_postcategories',
                                 'wpget_the_tags' => 'wp_postcategories',
                                 'wpentrytags' => 'wp_postcategories',
                                 'wpsubcatisfirst' => 'wp_subcatisfirst',
                                 'wpsubcatislast' => 'wp_subcatislast',
                                 'wparchivelist' => 'wp_archivelist',
                                 'wpifcommentsaccepted' => 'if_wp_comment_open',
                                 'wpentryifallowcomments' => 'if_wp_comment_open',
                                 'wpentryifallowpings' => 'if_wp_comment_open',
                                 'wpifcommentsactive' => 'if_wp_comment_open',
                                 'wpifpingsaccepted' => 'if_wp_comment_open',
                                ),
            'function' => array( 'wpbloginfo' => 'wp_bloginfo',
                                 'wpblogname' => 'wp_bloginfo',
                                 'wpsite_url' => 'wp_site_url',
                                 'wpblogurl' => 'wp_site_url',
                                 'wpthe_ID' => 'wp_post_value',
                                 'wpthe_author' => 'wp_post_value',
                                 'wpthe_userid' => 'wp_post_value',
                                 'wpthe_date' => 'wp_post_date',
                                 'wpthe_dategmt' => 'wp_post_date',
                                 'wpthe_content' => 'wp_post_value',
                                 'wpthe_title' => 'wp_post_value',
                                 'wpthe_excerpt' => 'wp_post_value',
                                 'wpthe_status' => 'wp_post_value',
                                 'wpthe_modified' => 'wp_post_date',
                                 'wpthe_guid' => 'wp_post_value',
                                 'wpthe_type' => 'wp_post_value',
                                 'wpthe_mimetype' => 'wp_post_value',
                                 'wpthe_comment_count' => 'wp_post_value',
                                 'wpthe_meta' => 'wp_post_meta',
                                 'wppost_meta' => 'wp_post_meta',
                                 'wpthe_permalink' => 'wp_get_permalink',
                                 'wpget_category_link' => 'wp_get_categorylink',
                                 'wpget_tag_link' => 'wp_get_categorylink',
                                 'wpget_month_link' => 'wp_archivelink',
                                 'wpentryid' => 'wp_post_value',
                                 'wpentryauthor' => 'wp_post_value',
                                 'wpentryauthordisplayname' => 'wp_post_value',
                                 'wpentryauthorid' => 'wp_post_value',
                                 'wpentrydate' => 'wp_post_date',
                                 'wpentrydategmt' => 'wp_post_date',
                                 'wpentrycontent' => 'wp_post_value',
                                 'wpentrybody' => 'wp_post_value',
                                 'wpentrytitle' => 'wp_post_value',
                                 'wpentryexcerpt' => 'wp_post_value',
                                 'wpentrystatus' => 'wp_post_value',
                                 'wpentrymodifieddate' => 'wp_post_date',
                                 'wpentryguid' => 'wp_post_value',
                                 'wpentrytype' => 'wp_post_value',
                                 'wpentrymimetype' => 'wp_post_value',
                                 'wpentrycommentcount' => 'wp_post_value',
                                 'wpentrymeta' => 'wp_post_meta',
                                 'wpentrypermalink' => 'wp_get_permalink',
                                 'wpcategorylink' => 'wp_get_categorylink',
                                 'wptaglink' => 'wp_get_categorylink',
                                 'wpcategoryarchivelink' => 'wp_get_categorylink',
                                 'wptagarchivelink' => 'wp_get_categorylink',
                                 'wparchivelink' => 'wp_archivelink',
                                 'wpentriescount' => 'wp_entriescount',
                                 'wpcount_posts' => 'wp_entriescount',
                                 'wpcategoryid' => 'wp_category_value',
                                 'wpcat_ID' => 'wp_category_value',
                                 'wpcat_name' => 'wp_category_value',
                                 'wpcategorylabel' => 'wp_category_value',
                                 'wpcategoryslug' => 'wp_category_value',
                                 'wpcategorynickname' => 'wp_category_value',
                                 'wpcategorygroup' => 'wp_category_value',
                                 'wpcategorytaxonomy_id' => 'wp_category_value',
                                 'wpcategorytaxonomy' => 'wp_category_value',
                                 'wpcategorydescription' => 'wp_category_value',
                                 'wpcategoryparent' => 'wp_category_value',
                                 'wpcategorycount' => 'wp_category_value',
                                 'wptagid' => 'wp_category_value',
                                 'wptagname' => 'wp_category_value',
                                 'wptagslug' => 'wp_category_value',
                                 'wptaggroup' => 'wp_category_value',
                                 'wptagtaxonomy_id' => 'wp_category_value',
                                 'wptagtaxonomy' => 'wp_category_value',
                                 'wptagdescription' => 'wp_category_value',
                                 'wptagcount' => 'wp_category_value',
                                 'wpsubcatsrecurse' => 'wp_subcatsrecurse',
                                 'wparchivetitle' => 'wp_archivetitle',
                                 'wparchivecount' => 'wp_archivecount',
                                 'wptest' => 'wp_test',
                                 ),
        ),
    );

    var $app;
    var $wordpress;

    function wp_test ( $args, &$ctx ) {
        // for Debug
        return 'Hello Wordpress.';
    }

    function wp_bloginfo ( $args, &$ctx ) {
        $app = $this->app;
        $wp = $this->get_wp( $ctx );
        $this_tag = $ctx->this_tag();
        $name = $args[ 'name' ];
        if (! $name ) {
            $name = 'blogname';
        }
        if ( $option = $ctx->stash( "wp_option{$name}" ) ) {
        } else {
            $option = $app->load( 'Options' , array( 'name' => $name  ), array( 'limit' => 1 ) );
            $ctx->stash( "wp_option:{$name}", $option );
        }
        if ( isset( $option ) ) {
            return $option->value;
        }
    }

    function wp_site_url ( $args, &$ctx ) {
        if ( $site_url = $ctx->stash( 'wp_site_url' ) ) {
            return $site_url;
        }
        $args[ 'name' ] = 'siteurl';
        $site_url = $this->wp_bloginfo( $args, $ctx );
        if (! preg_match( '!/$!', $site_url ) ) {
            $site_url .= '/';
        }
        $ctx->stash( 'wp_site_url', $site_url );
        return $site_url;
    }

    function wp_list_categories ( $args, $content, &$ctx, &$repeat ) {
        $localvars = array( 'wp_category', '__wp_categories', '__wp_categories_count' );
        $this_tag = $ctx->this_tag();
        if ( $this_tag == 'mtwp_subcategories' ) {
            $args[ 'toplevel' ] = 1;
        } elseif ( $this_tag == 'mtwp_tags' ) {
            $args[ 'taxonomy' ] = 'post_tag';
        }
        $app = $this->app;
        if (! isset( $content ) ) {
            $wp = $this->get_wp( $ctx );
            $token_fn = $args[ 'token_fn' ];
            $ctx->stash( 'subCatTokens', $token_fn );
            $ctx->localize( $localvars );
            $ctx->stash( '__wp_list_categories_old_vars', $ctx->__stash[ 'vars' ] );
            $ctx->__stash[ 'vars' ][ '__counter__' ] = 0;
            $taxonomy = $args[ 'taxonomy' ];
            if (! $taxonomy ) {
                $taxonomy = 'category';
            }
            $category = $app->model( 'Terms' );
            $wp_categories = $category->load_category( $wp, $ctx, NULL, $args, $taxonomy );
            $ctx->stash( '__wp_categories', $wp_categories );
            $ctx->stash( '__wp_categories_count', count( $wp_categories ) );
        } else {
            $wp_categories = $ctx->stash( '__wp_categories' );
        }
        if ( isset( $wp_categories ) ) {
            if (! $wp_categories ) {
                $repeat = FALSE;
                return;
            }
            $lastn = $args[ 'lastn' ];
            $wp_categories_count = $ctx->stash( '__wp_categories_count' );
            $counter = $ctx->__stash[ 'vars' ][ '__counter__' ];
            $category = $wp_categories[ $counter ];
            $wp = $this->get_wp( $ctx );
            $children = $category->children( $wp, $ctx, $taxonomy, $args );
            $ctx->stash( 'wp_category', $category );
            $count = $counter + 1;
            $last = 0;
            $__last = 0;
            if ( $lastn ) {
                if ( $counter == $lastn ) $last = 1;
                if ( $count == $lastn ) $__last = 1;
            } else {
                if ( $count == $wp_categories_count ) $last = 1;
                if ( $count == $wp_categories_count - 1 ) $__last = 1;
            }
            $ctx->stash( 'subCatIsFirst', $count == 1 );
            $ctx->stash( 'subCatIsLast', $__last );
            $ctx->__stash[ 'vars' ][ '__counter__' ] = $count;
            $ctx->__stash[ 'vars' ][ '__odd__' ]     = ( $count % 2 ) == 1;
            $ctx->__stash[ 'vars' ][ '__even__' ]    = ( $count % 2 ) == 0;
            $ctx->__stash[ 'vars' ][ '__first__' ]   = $count == 1;
            $ctx->__stash[ 'vars' ][ '__last__' ]    = $__last;
            if (! $last ) {
                $repeat = TRUE;
            } else {
                $ctx->__stash[ 'vars' ] = $ctx->stash[ '__wp_list_categories_old_vars' ];
                $ctx->restore( $localvars );
                $repeat = FALSE;
            }
        }
        if ( ( $counter > 1 ) && $args[ 'glue' ] && (! empty( $content ) ) ) {
             $content = $args[ 'glue' ] . $content;
        }
        return $content;
    }

    function wp_archivelist ( $args, $content, &$ctx, &$repeat ) {
        $localvars = array( 'wp_archivetitle', 'wp_archivecount', '__wp_archivelist',
                            '__wp_archivelistcount', '__wp_current_archivetype',
                            '__wp_archivelist_old_vars' );
        $archive_type = $args[ 'archive_type' ];
        $sort_order = $args[ 'sort_order' ];
        if (! $archive_type ) $archive_type = 'Monthly';
        if (! $sort_order ) $sort_order = 'descend';
        if (! isset( $content ) ) {
            $ctx->localize( $localvars );
            $ctx->stash( '__wp_archivelist_old_vars', $ctx->__stash[ 'vars' ] );
            $wp = $this->get_wp( $ctx );
            $wp_archivelist = $this->get_archive_list( $ctx, $archive_type );
            if ( $sort_order == 'descend' ) {
                krsort( $wp_archivelist );
            }
            $ctx->__stash[ 'vars' ][ '__counter__' ] = 0;
            $ctx->stash( '__wp_archivelist', $wp_archivelist );
            $ctx->stash( '__wp_archivelistcount', count( $wp_archivelist ) );
        } else {
            $wp_archivelist = $ctx->stash( '__wp_archivelist' );
        }
        if ( isset( $wp_archivelist ) ) {
            $lastn = $args[ 'lastn' ];
            $wp_archivelist_count = $ctx->stash( '__wp_archivelistcount' );
            $counter = $ctx->__stash[ 'vars' ][ '__counter__' ];
            $count = $counter + 1;
            $last = 0;
            $__last = 0;
            if ( $lastn ) {
                if ( $counter == $lastn ) $last = 1;
                if ( $count == $lastn ) $__last = 1;
            } else {
                if ( $counter == $wp_archivelist_count ) $last = 1;
                if ( $count == $wp_archivelist_count ) $__last = 1;
            }
            $keys = array_keys( $wp_archivelist );
            $archive_title = $keys[ $counter ];
            $archive_count = $wp_archivelist[ $archive_title ];
            $ctx->stash( 'wp_archivetitle', $archive_title );
            $ctx->stash( 'wp_archivecount', $archive_count );
            $ctx->stash( '__wp_current_archivetype', $archive_type );
            $ctx->__stash[ 'vars' ][ '__counter__' ] = $count;
            $ctx->__stash[ 'vars' ][ '__odd__' ]     = ( $count % 2 ) == 1;
            $ctx->__stash[ 'vars' ][ '__even__' ]    = ( $count % 2 ) == 0;
            $ctx->__stash[ 'vars' ][ '__first__' ]   = $count == 1;
            $ctx->__stash[ 'vars' ][ '__last__' ]    = $__last;
            if (! $last ) {
                $repeat = TRUE;
            } else {
                $ctx->__stash[ 'vars' ] = $ctx->stash( '__wp_archivelist_old_vars' );
                $ctx->restore( $localvars );
                $repeat = FALSE;
            }
        }
        if ( ( $counter > 1 ) && $args[ 'glue' ] && (! empty( $content ) ) ) {
             $content = $args[ 'glue' ] . $content;
        }
        return $content;
    }

    function if_wp_comment_open ( $args, $content, &$ctx, &$repeat ) {
        $post = $ctx->stash( 'wp_post' );
        if (! isset( $post ) ) {
            return '';
        }
        $this_tag = $ctx->this_tag();
        $status = NULL;
        if ( preg_match( '/comment/', $this_tag ) ) {
            $status = $post->comment_status;
        } elseif ( preg_match( '/ping/', $this_tag ) ) {
            $status = $post->ping_status;
        }
        if ( $status == 'open' ) {
            return $ctx->_hdlr_if( $args, $content, $ctx, $repeat, TRUE );
        } else {
            return $ctx->_hdlr_if( $args, $content, $ctx, $repeat, FALSE );
        }
    }

    function wp_postcategories ( $args, $content, &$ctx, &$repeat ) {
        $localvars = array( '__wp_categories', '__wp_categories_count', 'wp_category',
                            '__wp_postcategories_old_vars' );
        $this_tag = $ctx->this_tag();
        if ( preg_match( '/tags$/', $this_tag ) ) {
            $args[ 'taxonomy' ] = 'post_tag';
        }
        $app = $this->app;
        $post = $ctx->stash( 'wp_post' );
        if (! isset( $content ) ) {
            $ctx->localize( $localvars );
            $ctx->stash( '__wp_postcategories_old_vars', $ctx->__stash[ 'vars' ] );
            $wp = $this->get_wp( $ctx );
            $taxonomy = $args[ 'taxonomy' ];
            if (! $taxonomy ) {
                $taxonomy = 'category';
            }
            $wp_categories = $post->categories( $wp, $ctx, $taxonomy );
            $ctx->__stash[ 'vars' ][ '__counter__' ] = 0;
            $ctx->stash( '__wp_categories', $wp_categories );
            $ctx->stash( '__wp_categories_count', count( $wp_categories ) );
        } else {
            $wp_categories = $ctx->stash( '__wp_categories' );
        }
        if ( isset( $wp_categories ) ) {
            $wp_categories_count = $ctx->stash( '__wp_categories_count' );
            $counter = $ctx->__stash[ 'vars' ][ '__counter__' ];
            $count = $counter + 1;
            if ( $counter < $wp_categories_count ) {
                $repeat = TRUE;
                $category = $wp_categories[ $counter ];
                $ctx->stash( 'wp_category', $category );
                $ctx->__stash[ 'vars' ][ '__counter__' ] = $count;
                $ctx->__stash[ 'vars' ][ '__odd__' ]     = ( $count % 2 ) == 1;
                $ctx->__stash[ 'vars' ][ '__even__' ]    = ( $count % 2 ) == 0;
                $ctx->__stash[ 'vars' ][ '__first__' ]   = $count == 1;
                $ctx->__stash[ 'vars' ][ '__last__' ]    = $wp_categories_count == $count;
            } else {
                $ctx->__stash[ 'vars' ] = $ctx->stash( '__wp_postcategories_old_vars' );
                $ctx->restore( $localvars );
                $repeat = FALSE;
            }
        }
        if ( ( $counter > 1 ) && $args[ 'glue' ] && (! empty( $content ) ) ) {
             $content = $args[ 'glue' ] . $content;
        }
        return $content;
    }

    function wp_subcatsrecurse ( $args, &$ctx ) {
        $localvars = array( 'subCatsDepth', 'category', 'subCatIsFirst', 'subCatIsLast',
                            '__wp_subcatsrecurse_old_vars' );
        $ctx->stash( '__wp_subcatsrecurse_old_vars', $ctx->__stash[ 'vars' ] );
        $fn = $ctx->stash( 'subCatTokens' );
        $cat = $ctx->stash( 'wp_category' );
        $max_depth = $args[ 'max_depth' ];
        $depth = $ctx->stash( 'subCatsDepth' ) or 0;
        $sort_method = $ctx->stash( 'subCatsSortMethod' );
        $sort_order = $ctx->stash( 'subCatsSortOrder' );
        $cats = $cat->children;
        if (! $cats ) {
            return '';
        }
        $count = 0;
        $res = '';
        require_once( 'function.mtsetvar.php' );
        $ctx->localize( $localvars );
        $ctx->stash( 'subCatsDepth', $depth + 1 );
        $count = count( $cats );
        $i = 1;
        while ( $c = array_shift( $cats ) ) {
            smarty_function_mtsetvar( array( 'name' => '__depth__', 'value' => ( $depth + 1 ) ), $ctx );
            $ctx->stash( 'wp_category', $c );
            $ctx->stash( 'subCatIsFirst', $i == 1 );
            $ctx->stash( 'subCatIsLast', ! count( $cats ) );
            $ctx->__stash[ 'vars' ][ '__counter__' ] = $i;
            $ctx->__stash[ 'vars' ][ '__odd__' ]     = ( $i % 2 ) == 1;
            $ctx->__stash[ 'vars' ][ '__even__' ]    = ( $i % 2 ) == 0;
            $ctx->__stash[ 'vars' ][ '__first__' ]   = $i == 1;
            $ctx->__stash[ 'vars' ][ '__last__' ]    = (! count( $cats ) );
            ob_start();
            $fn( $ctx, array() );
            $res .= ob_get_contents();
            ob_end_clean();
            $i++;
        }
        $ctx->__stash[ 'vars' ] = $ctx->stash( '__wp_subcatsrecurse_old_vars' );
        $ctx->restore( $localvars );
        return $res;
    }

    function wp_subcatisfirst ( $args, $content, &$ctx, &$repeat ) {
        if (! isset( $content ) ) {
            return $ctx->_hdlr_if( $args, $content, $ctx, $repeat, 'subCatIsFirst' );
        } else {
            return $ctx->_hdlr_if( $args, $content, $ctx, $repeat );
        }
    }

    function wp_subcatislast ( $args, $content, &$ctx, &$repeat ) {
        if (! isset( $content ) ) {
            return $ctx->_hdlr_if( $args, $content, $ctx, $repeat, 'subCatIsLast' );
        } else {
            return $ctx->_hdlr_if( $args, $content, $ctx, $repeat );
        }
    }

    function wp_archivetitle ( $args, &$ctx ) {
        $archivetitle = $ctx->stash( 'wp_archivetitle' );
        $current_archivetype = $ctx->stash( '__wp_current_archivetype' );
        if ( preg_match( '/ly$/', $current_archivetype ) ) {
            if ( preg_match( '/^[0-9]{14}$/', $archivetitle ) ) {
                $args[ 'ts' ] = $archivetitle;
                return $ctx->_hdlr_date( $args, $ctx );
            }
        }
        return $archivetitle;
    }

    function wp_archivecount ( $args, &$ctx ) {
        return $ctx->stash( 'wp_archivecount' );
    }

    function wp_category_value ( $args, &$ctx ) {
        $category = $ctx->stash( 'wp_category' );
        if (! isset( $category ) ) {
            return '';
        }
        $this_tag = $ctx->this_tag();
        if ( preg_match( '/^mtwpcategory/', $this_tag ) ) {
            $tag = preg_replace( '/^mtwpcategory/', '', $this_tag );
            if ( $tag == 'label' ) {
                $tag = 'name';
            }
        } elseif ( preg_match( '/^mtwptag/', $this_tag ) ) {
            $tag = preg_replace( '/^mtwptag/', '', $this_tag );
        } elseif ( preg_match( '/^mtwpcat_/', $this_tag ) ) {
            $tag = preg_replace( '/^mtwpcat_/', '', $this_tag );
        }
        if ( $tag == 'nickname' ) {
            return $tag = 'slug';
        }
        return $category->$tag;
    }

    function wp_entriescount ( $args, &$ctx ) {
        $category = $args[ 'category' ];
        $category_id = $args[ 'category_id' ];
        if ( $category ) {
            if ( $cat = $this->get_category( $ctx, $category ) ) {
                $category_id = $cat->term_id;
            } else {
                return 0;
            }
        }
        $tag = $args[ 'tag' ];
        if ( $tag ) {
            if ( $cat = $this->get_category( $ctx, $tag, 'post_tag' ) ) {
                $category_id = $cat->term_id;
            } else {
                return 0;
            }
        }
        $extra = array();
        if ( $category_id ) {
            $join = array( 'wp_term_relationships', 'object_id',
                            array( 'term_taxonomy_id' => $category_id ) );
            $extra[ 'join' ] = $join;
        }
        $terms = array();
        $type = $args[ 'type' ];
        if (! $type ) {
            $type = 'post';
        }
        if ( $type != '*' ) {
            $terms[ 'type' ] = $type;
        }
        $status = $args[ 'status' ];
        if (! $status ) {
            $status = 'publish';
        }
        if ( $status != '*' ) {
            $terms[ 'status' ] = $status;
        }
        list( $start, $end ) = $this->get_start_end_from_args( $args );
        if ( $start && $end ) {
            $terms[ 'date' ] = array( $start, $end );
            $extra[ 'range_incl' ] = array( 'date' => 1 );
        }
        if (! $terms ) {
            // Where statement is required.
            $terms = array( 'author' => array( 'not_null' => 1 ) );
        }
        return $this->app->count( 'Posts', $terms, $extra );
    }

    function wp_get_posts ( $args, $content, &$ctx, &$repeat ) {
        $localvars = array( '__wp_posts', '__wp_posts_count', 'wp_post',
                            '__wp_get_posts_old_vars' );
        $app = $this->app;
        $wp = $this->get_wp( $ctx );
        $id = $args[ 'id' ];
        if (! isset( $content ) ) {
            $ctx->localize( $localvars );
            $ctx->stash( '__wp_get_posts_old_vars', $ctx->__stash[ 'vars' ] );
            $ctx->__stash[ 'vars' ][ '__counter__' ] = 0;
            $counter = 0;
            $category = $args[ 'category' ];
            $category_id = $args[ 'category_id' ];
            if ( $category ) {
                if ( $cat = $this->get_category( $ctx, $category ) ) {
                    $category_id = $cat->term_id;
                } else {
                    $repeat = FALSE;
                    return;
                }
            }
            $tag = $args[ 'tag' ];
            if ( $tag ) {
                if ( $cat = $this->get_category( $ctx, $tag, 'post_tag' ) ) {
                    $category_id = $cat->term_id;
                } else {
                    $repeat = FALSE;
                    return;
                }
            }
            $limit = $args[ 'limit' ];
            $offset = $args[ 'offset' ];
            if (! isset( $offset ) ) {
                $offset = 0;
            }
            if (! isset( $limit ) ) {
                $limit = 10000;
            }
            $sort_by = $args[ 'sort_by' ];
            if (! isset( $sort_by ) ) {
                $sort_by = 'date';
            }
            $sort_by = strtolower( $sort_by );
            if ( $sort_by == 'id' ) {
                $sort_by = 'ID';
            }
            $sort_order = $args[ 'sort_order' ];
            if ( (! isset( $sort_order ) ) || ( $sort_order == 'descend' ) ) {
                $sort_order = 'descend';
            } else {
                $sort_order = 'ascend';
            }
            $extra = array( 'limit' => $limit,
                            'offset' => $offset,
                            'sort' => $sort_by,
                            'direction' => $sort_order );
            if ( $category_id ) {
                $join = array( 'wp_term_relationships', 'object_id',
                                array( 'term_taxonomy_id' => $category_id ) );
                $extra[ 'join' ] = $join;
            }
            $terms = array();
            $type = $args[ 'type' ];
            if (! $type ) {
                $type = 'post';
            }
            if ( $type != '*' ) {
                $terms[ 'type' ] = $type;
            }
            $status = $args[ 'status' ];
            if (! $status ) {
                $status = 'publish';
            }
            if ( $status != '*' ) {
                $terms[ 'status' ] = $status;
            }
            list( $start, $end ) = $this->get_start_end_from_args( $args );
            if ( $start && $end ) {
                $terms[ 'date' ] = array( $start, $end );
                $extra[ 'range_incl' ] = array( 'date' => 1 );
            }
            if (! $terms ) {
                // Where statement is required.
                $terms = array( 'author' => array( 'not_null' => 1 ) );
            }
            if (! $id ) {
                $wp_posts = $app->load( 'Posts', $terms, $extra, 'wantarray' );
            } else {
                if ( $wp_post = $ctx->stash( "wp_posts:{$id}" ) ) {
                    $wp_posts = array( $wp_post );
                } else {
                    $_entry = $app->model( 'Posts' );
                    $wp_posts = $app->load( 'Posts', array( 'ID' => $id ) );
                }
            }
            $ctx->stash( '__wp_posts', $wp_posts );
            $ctx->stash( '__wp_posts_count', count( $wp_posts ) );
        } else {
            $wp_posts = $ctx->stash( '__wp_posts' );
            $counter = $ctx->__stash[ 'vars' ][ '__counter__' ];
        }
        if ( isset( $wp_posts ) ) {
            $lastn = $args[ 'lastn' ];
            $wp_posts_count = $ctx->stash( '__wp_posts_count' );
            $post = $wp_posts[ $counter ];
            if ( $post_id = $post->post_id ) {
                $ctx->stash( "wp_posts:{$post_id}", $post );
            }
            $ctx->stash( 'wp_post', $post );
            $count = $counter + 1;
            $last = 0;
            $__last = 0;
            if ( $lastn ) {
                if ( $counter == $lastn ) $last = 1;
                if ( $count == $lastn ) $__last = 1;
            } else {
                if ( $counter == $wp_posts_count ) $last = 1;
                if ( $count == $wp_posts_count ) $__last = 1;
            }
            $ctx->__stash[ 'vars' ][ '__counter__' ] = $count;
            $ctx->__stash[ 'vars' ][ '__odd__' ]     = ( $count % 2 ) == 1;
            $ctx->__stash[ 'vars' ][ '__even__' ]    = ( $count % 2 ) == 0;
            $ctx->__stash[ 'vars' ][ '__first__' ]   = $count == 1;
            $ctx->__stash[ 'vars' ][ '__last__' ]    = $__last;
            if (! $last ) {
                $repeat = TRUE;
            } else {
                $ctx->__stash[ 'vars' ] = $ctx->stash( '__wp_get_posts_old_vars' );
                $ctx->restore( $localvars );
                $repeat = FALSE;
            }
        }
        if ( ( $counter > 1 ) && $args[ 'glue' ] && (! empty( $content ) ) ) {
             $content = $args[ 'glue' ] . $content;
        }
        return $content;
    }

    function wp_post_nextprev ( $args, $content, &$ctx, &$repeat ) {
        $localvars = array( 'wp_post' );
        $post = $ctx->stash( 'wp_post' );
        // $ctx->stash( '__wp_nextprev_old_post', $post );
        if (! isset( $post ) ) {
            $repeat = FALSE;
            return '';
        }
        if (! isset( $content ) ) {
            $ctx->localize( $localvars );
            $this_tag = $ctx->this_tag();
            $nextprev = 'next';
            if ( preg_match( '/previous/', $this_tag ) ) {
                $nextprev = 'previous';
            }
            $wp = $this->get_wp( $ctx );
            $nextprev_post = $post->nextprev( $wp, $ctx, $nextprev );
            if ( $nextprev_post ) {
                $ctx->stash( 'wp_post', $nextprev_post );
                $repeat = TRUE;
            } else {
                $repeat = FALSE;
                return '';
            }
        } else {
            $ctx->restore( $localvars );
            //$ctx->stash( 'wp_post', $ctx->stash( '__wp_nextprev_old_post' ) );
            $repeat = FALSE;
        }
        return $content;
    }

    function wp_post_value ( $args, &$ctx ) {
        $post = $ctx->stash( 'wp_post' );
        if (! isset( $post ) ) {
            return '';
        }
        $this_tag = $ctx->this_tag();
        if ( preg_match( '/^mtwpthe_/', $this_tag ) ) {
            $tag = preg_replace( '/^mtwpthe_/', '', $this_tag );
        } elseif ( preg_match( '/^mtwpentry/', $this_tag ) ) {
            $tag = preg_replace( '/^mtwpentry/', '', $this_tag );
            if ( $tag == 'body' ) {
                $tag = 'content';
            }
            if ( $tag == 'authordisplayname' ) {
                $tag = 'author';
            }
            if ( $tag == 'commentcount' ) {
                $tag = 'comment_count';
            }
        }
        if ( $tag == 'authorid' ) {
            return $post->author;
        }
        if ( $post->has_column( $tag ) ) {
            if ( $tag == 'author' ) {
                $user_id = $post->author;
                if ( $user = $ctx->stash( "wp_user:{$user_id}" ) ) {
                } else {
                    $app = $this->app;
                    $_user = $app->model( 'Users' );
                    $user = $app->load( 'Users', array( 'ID' => $user_id ), array( 'limit' => 1 ) );
                    if ( $user ) {
                        $ctx->stash( "wp_user:{$user_id}", $user );
                    }
                }
                if ( $user ) {
                    return $user->user_display_name;
                }
            } else {
                return $post->$tag;
            }
        } else {
            $foreign_name = preg_replace( '/^mtwp/', '', $this_tag );
            if ( isset( $post->$foreign_name ) ) {
                return $post->$foreign_name;
            }
        }
    }

    function wp_post_date ( $args, &$ctx ) {
        $post = $ctx->stash( 'wp_post' );
        if (! isset( $post ) ) {
            return '';
        }
        $this_tag = $ctx->this_tag();
        if ( preg_match( '/^mtwpthe_/', $this_tag ) ) {
            $tag = preg_replace( '/^mtwpthe_/', '', $this_tag );
        } elseif ( preg_match( '/^mtwpentry/', $this_tag ) ) {
            $tag = preg_replace( '/^mtwpentry/', '', $this_tag );
        }
        if ( $tag == 'modifieddate' ) {
            $tag = 'modified';
        }
        if ( $post->has_column( $tag ) ) {
            $args[ 'ts' ] = $post->$tag;
            return $ctx->_hdlr_date( $args, $ctx );
        }
    }

    function wp_post_meta ( $args, &$ctx ) {
        $post = $ctx->stash( 'wp_post' );
        if (! isset( $post ) ) {
            return '';
        }
        $key = $args[ 'key' ];
        if ( $meta = $post->get_meta( $ctx, $key ) ) {
            return $meta->meta_value;
        }
    }

    function wp_get_permalink ( $args, &$ctx ) {
        $post = $ctx->stash( 'wp_post' );
        if (! isset( $post ) ) {
            return '';
        }
        $id = $post->post_id;
        $site_url = $this->wp_site_url( $args, $ctx );
        $permalink = $site_url . '?p=' . $id;
        return $permalink;
    }

    function wp_get_categorylink ( $args, &$ctx ) {
        $category = $ctx->stash( 'wp_category' );
        if (! isset( $category ) ) {
            return '';
        }
        $taxonomy = $category->taxonomy;
        $site_url = $this->wp_site_url( $args, $ctx );
        if ( $taxonomy == 'category' ) {
            $id = $category->term_id;
            $permalink = $site_url . '?cat=' . $id;
        } elseif ( $taxonomy == 'post_tag' ) {
            $slug = $category->slug;
            $permalink = $site_url . '?tag=' . $slug;
        }
        return $permalink;
    }

    function wp_archivelink ( $args, &$ctx ) {
        $this_tag = $ctx->this_tag();
        if ( $this_tag = 'wp_get_month_link' ) {
            $archve_type = 'Monthly';
        } elseif ( $this_tag = 'mtwp_archivelink' ) {
            $archve_type = $args[ 'archve_type' ];
        }
        $archve_type = strtolower( $archve_type );
        $site_url = $this->wp_site_url( $args, $ctx );
        if ( $archve_type == 'monthly' ) {
            $archivetitle = $ctx->stash( 'wp_archivetitle' );
            $archivetitle = substr( $archivetitle, 0, 6 );
            $permalink = $site_url . '?m=' . $archivetitle;
            return $permalink;
        }
        return '';
    }

    function get_category ( &$ctx, $name, $taxonomy = 'category' ) {
        if ( $category = $ctx->stash( "wp_get_category:{$taxonomy}:{$name}" ))  {
            return $category;
        }
        $app = $this->app;
        $wp = $this->get_wp( $ctx );
        $category = $app->model( 'Terms' );
        $category = $category->load_category( $wp, $ctx, $name,
                                              array( 'limit' => 1 ), $taxonomy );
        if ( $category ) {
            $term_id = $category->term_id;
            if ( $term_id ) {
                $ctx->stash( "wp_category:{$term_id}", $category );
                $ctx->stash( "wp_get_category:{$taxonomy}:{$name}", $category );
            }
            return $category;
        }
    }

    function get_archive_list ( $ctx, $at = 'Monthly', $args = array(), $load = FALSE,
                                $type = 'post', $status = 'publish', $sort = 'date', $exists = 1 ) {
        $at = strtolower( $at );
        if (! $load ) {
            if ( $archive_list = $ctx->stash( "wp_archive_list:{$at}:{$type}:{$status}:{$sort}" ) ) {
                return $archive_list;
            }
        }
        $wp = $this->get_wp( $ctx );
        $app = $this->app;
        $terms = array();
        if ( $type != '*' ) {
            $terms[ 'type' ] = $type;
        }
        if ( $status != '*' ) {
            $terms[ 'status' ] = $status;
        }
        if (! $terms ) {
            // Where statement is required.
            $terms = array( 'author' => array( 'not_null' => 1 ) );
        }
        $extra = array( 'sort' => $sort,
                        'direction' => 'ascend',
                        'limit' => 1 );
        if ( $oldest = $ctx->stash( "wp_oldestpost:{$type}:{$status}:{$sort}" ) ) {
        } else {
            $oldest = $app->load( 'Posts', $terms, $extra );
            $ctx->stash( "wp_oldestpost:{$type}:{$status}:{$sort}", $oldest );
            if ( $post_id = $oldest->post_id ) {
                $ctx->stash( "wp_posts:{$post_id}", $oldest );
            }
        }
        $extra[ 'direction' ] = 'descend';
        if ( $newest = $ctx->stash( "wp_newestpost:{$type}:{$status}:{$sort}" ) ) {
        } else {
            $newest = $app->load( 'Posts', $terms, $extra );
            $ctx->stash( "wp_newestpost:{$type}:{$status}:{$sort}", $newest );
            if ( $post_id = $newest->post_id ) {
                $ctx->stash( "wp_posts:{$post_id}", $newest );
            }
        }
        if (! $oldest ) {
            return array();
        }
        $first_ts = __date2ts( $oldest->$sort );
        $last_ts = __date2ts( $newest->$sort );
        $archive_list = array();
        $object_list = array();
        if ( $at == 'monthly' ) {
            $first_ts = start_end_month( $first_ts );
            $first_ts = $first_ts[0];
            $last_ts = start_end_month( $last_ts );
            $last_ts = $last_ts[0];
            $current_ts = $first_ts;
            do {
                $month = $app->db()->ts2db( $current_ts );
                $month = preg_replace( '/^(([0-9][^0-9]*){6}).*$/', '$1', $month );
                $terms[ $sort ] = array( 'like' => $month . '%' );
                if ( $load ) {
                    $entries = $app->load( 'Posts', $terms, $args, 'wantarray' );
                    if ( (! $exists ) || $entries ) {
                        $object_list[ $current_ts ] = $entries;
                        $archive_list[ $current_ts ] = count( $entries );
                    }
                } else {
                    $count = $app->count( 'Posts', $terms );
                    if ( (! $exists ) || $count ) {
                        $archive_list[ $current_ts ] = $count;
                    }
                }
                $current_ts = __get_next_month( $current_ts );
            } while( $current_ts != __get_next_month( $last_ts ) );
        } elseif ( $at == 'yearly' ) {
            $first_ts = start_end_year( $first_ts );
            $first_ts = $first_ts[0];
            $last_ts = start_end_year( $last_ts );
            $last_ts = $last_ts[0];
            $current_ts = $first_ts;
            do {
                $year = $app->db()->ts2db( $current_ts );
                $year = preg_replace( '/^(([0-9][^0-9]*){4}).*$/', '$1', $year );
                $terms[ $sort ] = array( 'like' => $year . '%' );
                if ( $load ) {
                    $entries = $app->load( 'Posts', $terms, $args, 'wantarray' );
                    if ( (! $exists ) || $entries ) {
                        $object_list[ $current_ts ] = $entries;
                        $archive_list[ $current_ts ] = count( $entries );
                    }
                } else {
                    $count = $app->count( 'Posts', $terms );
                    if ( (! $exists ) || $count ) {
                        $archive_list[ $current_ts ] = $count;
                    }
                }
                $current_ts = __get_next_year( $current_ts );
            } while( $current_ts != __get_next_year( $last_ts ) );
        }
        $ctx->stash( "wp_archive_list:{$at}:{$type}:{$status}:{$sort}", $archive_list );
        if ( $load ) {
            return $object_list;
        }
        return $archive_list;
    }

    function get_start_end_from_args ( $args ) {
        $start = $args[ 'start' ];
        $end = $args[ 'end' ];
        $at = $args[ 'archive_type' ];
        if ( $at ) {
            $at = strtolower( $at );
            if ( $start && (! $end ) ) {
                if ( $at == 'monthly' ) {
                    if ( preg_match( '/^[0-9]{6}$/', $start ) ) {
                        $start .= '01000000';
                    }
                    $ts = start_end_month( $start );
                    $start = $ts[0];
                    $end = $ts[1];
                } elseif ( $at == 'yearly' ) {
                    if ( preg_match( '/^[0-9]{4}$/', $start ) ) {
                        $start .= '0101000000';
                    }
                    $ts = start_end_year( $start );
                    $start = $ts[0];
                    $end = $ts[1];
                }
            }
        }
        if ( $start && (! preg_match( '/^[0-9]{14}$/', $start ) ) ) {
            $start = NULL;
        }
        if ( $end && ! preg_match( '/^[0-9]{14}$/', $end ) ) {
            $end = NULL;
        }
        return array( $start, $end );
    }

    function get_wp ( &$ctx ) {
        $wp = $ctx->stash( 'Wordpress' );
        $app = $this->app;
        // $wp_dir = $ctx->mt->config( 'mtdir' );
        if ( isset( $wp ) ) {
            return $wp;
        } else {
            $dynamicmtml = $app->component( 'DynamicMTML' );
            $lib = __cat_file( array( $dynamicmtml->plugin_path, 'php', 'mt.php' ) );
            require_once( $lib );
        }
        $cfg_file =  dirname( dirname( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'wp-config.cgi';
        // $cfg_file = __cat_file( $wp_dir, 'wp-config.cgi' );
        $wp = new MT( NULL, $cfg_file );
        $db = $wp->db();
        $db->set_names( $wp );
        $wp->db = $db;
        $ctx->mt->db = $db;
        $app->init_models( array( 'Users', 'Terms', 'Posts', 'Postmeta', 'Options' ) );
        $ctx->stash( 'Wordpress', $wp );
        $this->wordpress = $wp;
        return $wp;
    }

}

?>