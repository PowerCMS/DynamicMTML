<?php
define( 'WP_CLASS_POSTMETA', WP_PREFIX . 'postmeta' );
require_once( 'class.baseobject.php' );
class Postmeta extends BaseObject {
    public $_table = WP_CLASS_POSTMETA;
    public $_prefix = 'meta_';
}
?>