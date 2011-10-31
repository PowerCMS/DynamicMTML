<?php
define( 'WP_CLASS_POST', WP_PREFIX . 'posts' );
require_once( 'class.baseobject.php' );
class Posts extends BaseObject {
    public $_table = WP_CLASS_POST;
    public $_prefix = 'post_';

    public $raw_columns = array( 'ID', 'comment_status', 'ping_status', 'to_ping',
                                 'pinged', 'guid', 'menu_order', 'comment_count' );

    function has_column ( $column ) {
        if ( in_array( $column, $this->raw_columns ) ) return TRUE;
        return parent::has_column( $column );
    }

    function nextprev ( $wp, $ctx, $nextprev = 'next', $status = 'publish' ) {
        $direction = 'ascend';
        if ( $nextprev != 'next' ) {
            $direction = 'descend';
        }
        $app = $ctx->stash( 'bootstrapper' );
        $date = __date2ts( $this->date );
        $date_range = array();
        if ( $nextprev == 'next' ) {
            $date_range = array ( $date, NULL );
        } else {
            $date_range = array ( NULL, $date );
        }
        $terms = array( 'type' => $this->type,
                        'ID' => array( 'not' => $this->post_id ),
                        'date' => $date_range,
                        );
        if ( $status != '*' ) {
            $terms[ 'status' ] = $status;
        }
        $extra = array( 'limit' => 1,
                        'sort' => 'date',
                        'range_incl' => array( 'date' => 1 ),
                        'direction' => $direction );
        $nextprev = $app->load( 'Posts', $terms, $extra );
        if ( $nextprev ) {
            return $nextprev;
        }
    }

    function tags ( $wp, &$ctx ) {
        return $this->categories( $wp, $ctx, 'post_tag' );
    }

    function categories ( $wp, &$ctx, $taxonomy = 'category' ) {
        $app = $ctx->stash( 'bootstrapper' );
        $id = $this->post_id;
        if ( $wp_categories = $ctx->stash( "wp_post_taxonomy:{$taxonomy}:{$id}" ) ) {
            return $wp_categories;
        }
        $sql = "SELECT *
                FROM `wp_terms` , `wp_term_taxonomy` , `wp_term_relationships`
                WHERE wp_terms.term_id=wp_term_taxonomy.term_id
                AND wp_term_taxonomy.taxonomy='{$taxonomy}'
                AND wp_term_relationships.term_taxonomy_id=wp_term_taxonomy.term_taxonomy_id
                AND wp_term_relationships.object_id={$id}";
        $categories = $wp->db()->Execute( $sql );
        $wp_categories = array();
        if ( $count = $categories->RecordCount() ) {
            for ( $i = 0; $i < $count; $i++ ) {
                $categories->Move( $i );
                $wp_category = $categories->FetchRow();
                $terms = $app->model( 'Terms' );
                $category = $terms->record2object( $ctx, $wp_category );
                $category_id = $category->term_id;
                $ctx->stash( "wp_category:{$category_id}", $category );
                array_push( $wp_categories, $category );
            }
        }
        $ctx->stash( "wp_taxonomy:{$wp_post_taxonomy}:{$id}", $wp_categories );
        return $wp_categories;
    }

    function get_meta ( &$ctx, $key = NULL ) {
        $app = $ctx->stash( 'bootstrapper' );
        $post_id = $this->post_id;
        if ( $key ) {
            if ( $wp_post_meta = $ctx->stash( "wp_postmeta:{$post_id}:{$key}" ) ) {
                return $wp_post_meta;
            }
        } else {
            if ( $wp_post_meta = $ctx->stash( "wp_post_meta:{$post_id}" ) ) {
                return $wp_post_meta;
            }
        }
        $_meta = $app->model( 'Postmeta' );
        if ( $key ) {
            $meta = $_meta->Find( "post_id={$post_id} AND meta_key='{$key}' ",
                                   FALSE, FALSE, array( 'limit' => 1 ) );
            if ( $meta ) {
                $ctx->stash( "wp_postmeta:{$post_id}:{$key}", $meta[0] );
                return $meta[0];
            }
        } else {
            $metas = $_meta->Find( "post_id={$post_id}", FALSE, FALSE );
            if ( isset( $meta ) ) {
                $meta_hash = array();
                foreach ( $meta as $obj ) {
                    $key = $obj->meta_key;
                    $ctx->stash( "wp_postmeta:{$post_id}:{$key}", $obj );
                }
                $ctx->stash( "wp_post_meta:{$post_id}", $meta );
                return $meta;
            }
        }
    }
}
?>