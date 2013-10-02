<?php
define( 'WP_CLASS_USERS', WP_PREFIX . 'users' );
require_once( 'class.baseobject.php' );
class Users extends BaseObject {
    public $_table = WP_CLASS_USERS;
    public $_prefix = 'user_';

    public $raw_columns = array( 'ID', 'display_name' );

    function has_column ( $column ) {
        if ( in_array( $column, $this->raw_columns ) ) return TRUE;
        return parent::has_column( $column );
    }
}
?>
