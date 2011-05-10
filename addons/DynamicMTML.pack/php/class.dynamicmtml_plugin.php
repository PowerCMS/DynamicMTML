<?php
class MTPlugin extends DynamicMTML {
    
    var $app;

    function app () {
        return $this->app;
    }
    
    function get_config_obj ( $scope ) {
        $component = $this->key();
        $get_from = 'configuration';
        $blog_id = NULL;
        if ( $scope ) {
            if ( preg_match( '/blog:([0-9]*)/', $scope, $result ) ) {
                $blog_id = $result[1];
            }
        }
        if ( $blog_id ) {
            $get_from .= ":blog:$blog_id";
        }
        if ( $data = $this->stash( "plugin_config:{$get_from}:{$component}" ) ) {
            if ( is_object( $data ) ) return $data;
        }
        $data = $this->get_by_key( 'PluginData', array( 'key' => $get_from, 'plugin' => $component ),
                                                 array( 'limit' => 1 )  );
        if (! $data->id ) {
            $settings = $this->settings;
            require_once ( 'dynamicmtml.util.php' );
            if ( $settings && __is_hash( $settings ) ) {
                $plugin_data = array();
                foreach ( $settings as $key => $val ) {
                    if ( __is_hash( $val ) && isset( $val[ 'default' ] ) ) {
                        $plugin_data[ $key ] = $val[ 'default' ];
                    }
                }
                $data->data = $this->db()->serialize( $plugin_data );
            }
        }
        $this->stash( "plugin_config:{$get_from}:{$component}" , $data );
        return $data;
    }

    function get_config_hash ( $scope ) {
        $data = $this->get_config_obj( $scope );
        if ( is_object( $data ) ) {
            return $data->data();
        }
    }

    function reset_config ( $scope ) {
        $data = $this->get_config_obj( $scope );
        if (! is_object( $data ) ) {
            return 0;
        }
        if (! $data->id ) {
            return 0;
        }
        $this->app()->remove( $data );
        $component = $this->key();
        $get_from = 'configuration';
        $blog_id = NULL;
        if ( $scope ) {
            if ( preg_match( '/blog:([0-9]*)/', $scope, $result ) ) {
                $blog_id = $result[1];
            }
        }
        if ( $blog_id ) {
            $get_from .= ":blog:$blog_id";
        }
        $this->stash( "plugin_config:{$get_from}:{$component}", NULL );
        return 1;
    }

    function config_vars () {
        $settings = $this->settings;
        require_once ( 'dynamicmtml.util.php' );
        $config_vars = array();
        if ( $settings && __is_hash( $settings ) ) {
            foreach ( $settings as $key => $val ) {
                array_push( $config_vars, $key );
            }
        }
        return $config_vars;
    }

    function get_config_value ( $key, $scope = NULL ) {
        //$plugin->get_config_value( 'key', 'blog:1' );
        $data = $this->get_config_obj( $scope );
        if ( $data ) {
            $data = $data->data();
            if ( isset( $data[ $key ] ) ) {
                return $data[ $key ];
            }
        }
        return NULL;
    }

    function set_config_value ( $variable, $value = NULL, $scope = NULL ) {
        require_once ( 'dynamicmtml.util.php' );
        if ( __is_hash( $variable ) ) {
            $scope = $value;
        }
        $data = $this->get_config_obj( $scope );
        $plugin_data = $data->data();
        $isset = NULL;
        if ( __is_hash( $variable ) ) {
            foreach ( $variable as $key => $val ) {
                if ( isset( $plugin_data[ $key ] ) ) {
                    if ( $plugin_data[ $key ] != $val ) {
                        $plugin_data[ $key ] = $val;
                        $isset = 1;
                    }
                }
            }
        } else {
            if ( isset( $plugin_data[ $variable ] ) ) {
                if ( $plugin_data[ $variable ] != $value ) {
                    $plugin_data[ $variable ] = $value;
                    $isset = 1;
                }
            }
        }
        if ( $isset ) {
            $data->data = $this->db()->serialize( $plugin_data );
            $this->app()->save( $data );
            $component = $this->key();
            $get_from = 'configuration';
            $blog_id = NULL;
            if ( $scope ) {
                if ( preg_match( '/blog:([0-9]*)/', $scope, $result ) ) {
                    $blog_id = $result[1];
                }
            }
            if ( $blog_id ) {
                $get_from .= ":blog:$blog_id";
            }
            $this->stash( "plugin_config:{$get_from}:{$component}" , $data );
            return 1;
        }
        return 0;
    }

    function translate ( $str, $params = NULL ) {
        return $this->app->translate( $str, $params);
    }

    function key () {
        $component = $this->key;
        if ( $component ) return $component;
        if (! $component ) $component = $this->id;
        if (! $component ) $component = $this->name;
        return strtolower( $component );
    }
}
?>