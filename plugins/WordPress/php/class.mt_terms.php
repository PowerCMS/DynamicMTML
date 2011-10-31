<?php
define( 'WP_CLASS_TERMS', WP_PREFIX . 'terms' );
require_once( 'class.baseobject.php' );
class Terms extends BaseObject {
    public $_table = WP_CLASS_TERMS;
    public $_prefix = 'term_';

    var $term_id;
    var $name;
    var $slug;
    var $term_group;
    var $term_taxonomy_id;
    var $taxonomy;
    var $description;
    var $parent;
    var $count;
    var $parent_obj;
    var $children;

    function load_category ( $wp, $ctx, $terms = array(), $args = array(), $taxonomy = 'category' ) {
        $app = $ctx->stash( 'bootstrapper' );
        $hide_empty = FALSE;
        if ( isset( $args[ 'hide_empty' ] ) ) {
            $hide_empty = $args[ 'hide_empty' ];
        }
        $prefix = WP_PREFIX;
        $sql = "SELECT DISTINCT wp_terms.term_id, wp_terms.name, wp_terms.slug, wp_terms.term_group, wp_term_taxonomy.taxonomy, wp_term_taxonomy.term_taxonomy_id, wp_term_taxonomy.description, wp_term_taxonomy.parent, wp_term_taxonomy.count ";
        $sql .= " FROM `{$prefix}terms` , `{$prefix}term_taxonomy` ";
        $sql .= " WHERE {$prefix}terms.term_id={$prefix}term_taxonomy.term_id ";
        $sql .= " AND {$prefix}term_taxonomy.taxonomy='$taxonomy' ";
        if ( is_array( $terms ) ) {
            foreach ( $terms as $key => $value ) {
                if ( $this->has_column( $key ) ) {
                    $sql .= " AND {$prefix}terms.{$key}='{$value}' ";
                } else {
                    $sql .= " AND {$prefix}term_taxonomy.{$key}='{$value}' ";
                }
            }
        } else {
            // Get Category by name.
            if ( is_string( $terms ) ) {
                $sql .= " AND {$prefix}terms.name='{$terms}' ";
            }
        }
        if ( $hide_empty ) {
            $sql .= " AND {$prefix}term_taxonomy.count !=0 ";
        }
        $toplevel = $args[ 'toplevel' ];
        if ( $toplevel ) {
            $sql .= " AND {$prefix}term_taxonomy.parent=0 ";
        }
        if ( isset( $args[ 'sort' ] ) ) {
            if ( $this->has_column( $sort ) ) {
                $_prefix = $prefix;
                if ( in_array( $key, $raw_columns ) ) $_prefix = '';
                $sort_by = " {$_prefix}{$sort}";
                if ( isset( $args[ 'direction' ] ) ) {
                    $sort_order = $args[ 'direction' ];
                }
                if ( $sort_order != 'descend' ) {
                    $sort_order = 'ASC';
                } else {
                    $sort_order = 'DESC';
                }
            }
            $sql .= " order by $sort_by $sort_order ";
        }
        if ( isset( $args[ 'limit' ] ) ) {
            $limit = $args[ 'limit' ];
            $sql .= " LIMIT {$limit} ";
        }
        if ( isset( $args[ 'offset' ] ) ) {
            $offset = $args[ 'offset' ];
            $sql .= " OFFSET {$offset} ";
        }
        if ( isset( $args[ 'taxonomy' ] ) ) {
            $taxonomy = $args[ 'taxonomy' ];
        }
        $wantarray = FALSE;
        if ( isset( $args[ 'wantarray' ] ) ) {
            $wantarray = TRUE;
        }
        $categories = $wp->db()->Execute( $sql );
        $wp_categories = array();
        if ( $count = $categories->RecordCount() ) {
            for ( $i = 0; $i < $count; $i++ ) {
                $categories->Move( $i );
                $wp_category = $categories->FetchRow();
                $category = $this->record2object( $ctx, $wp_category );
                $term_id = $category->term_id;
                $ctx->stash( "wp_category:{$term_id}", $category );
                if ( $limit == 1 && $wantarray == FALSE ) {
                    return $category;
                }
                array_push( $wp_categories, $category );
            }
        }
        return $wp_categories;
    }

    function parent ( $wp, &$ctx, $taxonomy = 'category' ) {
        $parent_id = $this->parent;
        if ( $category = $ctx->stash( "wp_category:{$parent_id}" ) ) {
            return $category;
        }
        if ( $category = $this->parent_obj ) {
            return $category;
        }
        $wp_parent = $this->load_category( $wp, $ctx, array( 'term_id' => $parent_id ),
                                             NULL, $taxonomy );
        if ( $wp_parent ) {
            $ctx->stash( "wp_category:{$parent_id}", $wp_parent );
            $this->parent_obj = $wp_parent;
            return $wp_parent;
        }
    }

    function children ( $wp, &$ctx, $taxonomy = 'category', $args = array() ) {
        if ( $children = $this->children ) {
            return $children;
        }
        if (! $taxonomy ) {
            $taxonomy = 'category';
        }
        if ( $args[ 'toplevel' ] ) {
            unset( $args[ 'toplevel' ] );
        }
        $term_id = $this->term_id;
        $wp_children = $this->load_category( $wp, $ctx, array( 'parent' => $term_id ),
                                             $args, $taxonomy );
        $this->children = $wp_children;
        return $wp_children;
    }

    function record2object ( &$ctx, &$record ) {
        $app = $ctx->stash( 'bootstrapper' );
        $terms = $app->model( 'Terms' );
        foreach ( $record as $key => $val ) {
            if (! preg_match( '/^[0-9]*$/', $key ) ) {
                $terms->$key = $val;
            }
        }
        return $terms;
    }

}
?>