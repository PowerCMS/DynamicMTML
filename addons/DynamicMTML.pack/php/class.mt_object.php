<?php
require_once( "class.baseobject.php" );
class MTObject extends BaseObject {

    var $app;
    var $_table; // 'mt_entry'
    var $_prefix; // 'entry_'
    var $primary_key = 'id';
    var $child_classes;
        //  array( 'Placement',
        //      # array( 'Placement' => array ( $this->primary_key => "{$_prefix}{$primary_key}" ),
        //        array( 'ObjectTag' => array ( 'id' => 'object_id',
        //                                      'object_datasource' => 'entry' ) ),
        //   ...
        //  );

    var $raw_columns;
        // array( 'ID', 'comment_status', 'ping_status', 'to_ping',
        //        'pinged', 'guid', 'menu_order', 'comment_count' );

    function has_column ( $column ) {
        if ( $this->raw_columns ) {
            if ( in_array( $column, $this->raw_columns ) ) return TRUE;
        }
        return parent::has_column( $column );
    }

    function app () {
        if ( $app = $this->app ) {
            return $app;
        }
        $mt = MT::get_instance();
        $ctx =& $mt->context();
        if ( $app = $ctx->stash( 'bootstrapper' ) ) {
            $this->app = $app;
            return $app;
        } else {
            global $app;
            $this->app = $app;
            return $app;
        }
    }

    // $obj = $app->model( 'ClassFoo' )->load( $terms, $args );

    function load ( $terms, $args = array(), $wantarray = FALSE ) {
        $app = $this->app();
        $class = get_class( $this );
        return $app->load( $class, $terms, $args, $wantarray );
    }

    function count ( $terms, $args = array() ) {
        // TODO:: AS CNT
        $app = $this->app();
        $class = get_class( $this );
        $objects = $app->load( $class, $terms, $args );
        if ( $objects ) {
            return count( $objects );
        }
        return 0;
    }

    function get_by_key ( $terms ) {
        $app = $this->app();
        $class = get_class( $this );
        return $app->get_by_key( $class, $terms );
    }

    function exist ( $terms ) {
        $app = $this->app();
        $class = get_class( $this );
        return $app->exist( $class, $terms );
    }

    function column_values () {
        return $this->GetArray();
    }

    function column_names () {
        return $this->GetAttributeNames();
    }

    function save ( $do = 'save' ) {
        $app = $this->app();
        return $app->save( $this, $do );
    }

    function remove () {
        $app = $this->app();
        $children = NULL;
        if ( $child_classes = $this->child_classes ) {
            $primary_key = $this->primary_key;
            $prefix = $this->_prefix;
            foreach ( $child_classes as $child ) {
                // TODO:: Remove Children.
                if ( is_string( $child ) ) {
                    $terms = array( "{$prefix}id" => $this->$primary_key );
                    $children = $app->load( $child, $terms );
                } elseif ( is_array( $child ) ) {
                    list( $class, $params ) = each( $child ); // 'ObjectTag'
                    $column = $params[ $primary_key ]; //'object_id'
                    if ( $column ) {
                        $terms = array( $column => $this->$primary_key );
                        foreach ( $params as $param => $child_column ) {
                            if ( $param != $primary_key ) {
                                $terms[ $param ] = $child_column;
                            }
                        }
                        $children = $app->load( $child, $terms );
                    }
                }
            }
        }
        if ( $children ) {
            foreach ( $children as $child ) {
                $app->save( $child, 'delete' );
            }
        }
        return $app->save( $this, 'delete' );
    }
}

?>