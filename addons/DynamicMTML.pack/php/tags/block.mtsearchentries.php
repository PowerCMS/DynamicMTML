<?php
function smarty_block_mtsearchentries( $args, $content, &$ctx, &$repeat ) {
    $localvars = array( 'entry', '_entries_counter', 'entries', '_entries_lastn',
                        'blog', 'blog_id', 'include_blogs', 'category_expression', 'tag_id' );
    $app = $ctx->stash( 'bootstrapper' );
    $blog_id = intval( $ctx->stash( 'blog_id' ) );
    $blog = $ctx->stash( 'blog' );
    $unique = $args[ 'unique' ];
    $not_entry_id = $args[ 'not_entry_id' ];
    $category_id = $args[ 'category_id' ];
    $category_id = intval( $category_id );
    $category = $args[ 'category' ];
    $category_expression = $ctx->stash( 'category_expression' );
    if (! $category_expression ) {
        $categories = array();
        if ( $category ) {
            $category = trim( $category );
            $cats = $app->load( 'Category', array( 'label' => $category ) );
            if ( $cats ) {
                foreach ( $cats as $cat ) {
                    array_push( $categories, $cat->id );
                }
            }
        }
        if ( $category_id ) {
            array_push( $categories, $category_id );
        }
        $category_expression = NULL;
        if ( $categories ) {
            $categories = array_unique( $categories );
            $category_expression = '';
            foreach ( $categories as $cid ) {
                if ( $category_expression ) {
                    $category_expression .= " OR ";
                }
                $category_expression .= " placement_category_id = {$cid} ";
            }
            $category_expression = " ( $category_expression ) ";
            $ctx->stash( 'category_expression', $category_expression );
        }
    }
    $tag = $args[ 'tag' ];
    $tag_id = NULL;
    if ( $tag ) {
        $tag_id = $ctx->stash( 'tag_id' );
        if (! $tag_id ) {
            $tag = trim( $tag );
            $tag_obj = $app->get_tag_obj( $tag, array( 'no_generate' => 1 ) );
            $tag_id = $tag_obj->id;
            $ctx->stash( 'tag_id', $tag_id );
        }
    }
    $status = $args[ 'status' ];
    if (! $status ) {
        $status = 2;
    }
    if (! isset( $blog ) ) {
        if ( $blog_id ) {
            $blog = $ctx->mt->db()->fetch_blog( $blog_id );
            $ctx->stash( 'blog', $blog );
        }
    }
    $blog_id = $blog->id;
    $ctx->stash( 'blog_id', $blog->id );
    require_once 'class.mt_entry.php';
    if (! isset( $content ) ) {
        $ctx->localize( $localvars );
        $ctx->__stash[ 'entries' ] = NULL;
        $counter = 0;
        $lastn = $args[ 'lastn' ];
        if (! isset( $lastn ) ) {
            $lastn = $blog->entries_on_index;
        }
        $offset = $args[ 'offset' ];
        if (! isset( $offset ) ) {
            $offset = 0;
        }
        $sort_by = $args[ 'sort_by' ];
        if (! isset( $sort_by ) ) {
            $sort_by = 'authored_on';
        }
        $sort_by = 'entry_' . $sort_by;
        $sort_order = $args[ 'sort_order' ];
        if ( (! isset( $sort_order ) ) || ( $sort_order == 'descend' ) ) {
            $sort_order = 'DESC';
        } else {
            $sort_order = 'ASC';
        }
        $target = $args[ 'target' ];
        if (! $target ) {
            $target = 'entry_title||entry_text_more||entry_keywords||entry_excerpt||entry_text';
        } else {
            $_entry = new Entry;
            if ( $_entry->has_column( $target ) ) {
                if (! preg_match( "/^entry_/", $target ) ) {
                    $target = 'entry_' . $target;
                }
            }
        }
        $operator = strtoupper( $args[ 'operator' ] );
        if ( $operator != 'LIKE' and 
             $operator != 'NOT LIKE' and
             $operator != 'IS NULL' and
             $operator != 'IS NOT NULL' and
             $operator != '>' and
             $operator != '<' and
             $operator != '<=' and
             $operator != '>=' and
             $operator != '!=' and
             $operator != '=' ) {
            $operator = NULL;
        }
        if (! $operator ) {
            $operator = 'LIKE';
        }
        $class = $args[ 'class' ];
        if (! isset( $class ) ) {
            $class = 'entry';
        }
        $query = $args[ 'query' ];
        $query = $ctx->mt->db()->escape( $query );
        if ( preg_match( '/LIKE/i', $operator ) ) {
            $query = "%$query%";
        }
        $ctx->stash( '_entries_lastn', $lastn );
    } else {
        $lastn = $ctx->stash( '_entries_lastn' );
        $counter = $ctx->stash( '_entries_counter' );
    }
    if ( preg_match ( "/\|\|/", $target ) ) {
        $targets = explode( '||', $target );
        $expression = '';
        foreach ( $targets as $t ) {
            if ( $expression ) {
                $expression .= " OR ";
            }
            $expression .= " $t $operator '$query' ";
        }
    }
    $entries = $ctx->stash( 'entries' );
    if (! isset( $entries ) ) {
        $include_blogs = $app->include_exclude_blogs( $ctx, $args );
        if (! $include_blogs ) $ctx->error( '' );
        $ctx->stash( 'include_blogs', $include_blogs );
        if ( $args[ 'count' ] ) {
            $sql = "SELECT DISTINCT ( mt_entry.entry_id ) AS CNT FROM mt_entry ";
            if ( $category_expression ) {
                $sql .= ",mt_placement ";
            }
            if ( $tag_id ) {
                $sql .= ",mt_objecttag ";
            }
            $sql .= " WHERE entry_blog_id {$include_blogs} ";
            if ( $query ) {
                if ( $expression ) {
                    $sql .= " AND ( {$expression} )";
                } else { 
                    $sql .= " AND $target $operator '$query'";
                }
            }
            if ( $status != '*' ) {
                $sql .= " AND entry_status = {$status} ";
            }
            if ( $class != '*' ) {
                $sql .= " AND entry_class = '$class' ";
            }
            if ( $unique ) {
                foreach ( $app->entry_ids_published as $id ) {
                    $sql .= " AND entry_id != {$id} ";
                }
            }
            if ( $not_entry_id ) {
                $sql .= " AND entry_id != {$not_entry_id} ";
            }
            if ( $category_expression ) {
                $sql .= " AND placement_entry_id = entry_id ";
                $sql .= " AND $category_expression ";
            }
            if ( $tag_id ) {
                $sql .= " AND objecttag_object_id = entry_id ";
                $sql .= " AND objecttag_tag_id = {$tag_id} ";
            }
            $match = $ctx->mt->db()->Execute( $sql );
            $ctx->__stash[ 'vars' ][ '__entries_count__' ] = $match->_numOfRows;
        }
        $where = " entry_blog_id {$include_blogs} ";
        if ( $query ) {
            if ( $expression ) {
                $where .= " AND ( {$expression} )";
            } else { 
                $where .= " AND $target $operator '$query'";
            }
        }
        if ( $status != '*' ) {
            $where .= " AND entry_status = {$status} ";
        }
        if ( $class != '*' ) {
            $where .= " AND entry_class = '$class' ";
        }
        if ( $unique ) {
            foreach ( $app->entry_ids_published as $id ) {
                $where .= " AND entry_id != {$id} ";
            }
        }
        if ( $not_entry_id ) {
            $where .= " AND entry_id != {$not_entry_id} ";
        }
       if ( $category_expression ) {
           $where .= " AND placement_entry_id = entry_id ";
           $where .= " AND $category_expression ";
       }
        if ( $tag_id ) {
            $where .= " AND objecttag_object_id = entry_id ";
            $where .= " AND objecttag_tag_id = {$tag_id} ";
        }
        $where .= " order by $sort_by $sort_order ";
        if (! $offset ) {
            $offset = 0;
        }
        $extra = array(
            'limit' => $lastn,
            'offset' => $offset,
//            'distinct' => 1,
        );
        if ( $category_expression || $tag_id ) {
            $join = array();
            if ( ( $category_id || $category ) && $category_expression ) {
                $join[ 'mt_placement' ] = array( 'condition' =>
                                                 "$category_expression" );
            }
            if ( $tag_id ) {
                $join[ 'mt_objecttag' ] = array( 'condition' =>
                                                 "objecttag_tag_id={$tag_id}" );
            }
            $extra[ 'join' ] = $join;
        }
        $_entry = new Entry;
        $entries = $_entry->Find( $where, false, false, $extra );
        $ctx->stash( 'entries', $entries );
    }
    if ( empty( $entries ) ) {
        if (! $repeat ) {
            $ctx->restore( $localvars );
        }
        $content = '';
    }
    if ( ( $lastn > count( $entries ) ) || ( $lastn == -1 ) ) {
        $lastn = count( $entries );
        $ctx->stash( '_entries_lastn', $lastn );
    }
    if ( $lastn ? ( $counter < $lastn ) : ( $counter < count( $entries ) ) ) {
        $entry = $entries[ $counter ];
        if (! empty( $entry ) ) {
            array_push ( $app->entry_ids_published, $entry->id );
            $ctx->stash( 'blog', $entry->blog() );
            $ctx->stash( 'blog_id', $entry->blog_id );
            $ctx->stash( 'entry', $entry );
            $ctx->stash( '_entries_counter', $counter + 1 );
            $count = $counter + 1;
            $ctx->__stash[ 'vars' ][ '__counter__' ] = $count;
            $ctx->__stash[ 'vars' ][ '__odd__' ]     = ( $count % 2 ) == 1;
            $ctx->__stash[ 'vars' ][ '__even__' ]    = ( $count % 2 ) == 0;
            $ctx->__stash[ 'vars' ][ '__first__' ]   = $count == 1;
            $ctx->__stash[ 'vars' ][ '__last__' ]    = ( $count == count( $entries ) );
            $repeat = true;
        }
    } else {
        $ctx->restore( $localvars );
        $repeat = false;
    }
    return $content;
}
?>