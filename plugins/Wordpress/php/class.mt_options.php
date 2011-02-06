<?php
define( 'WP_CLASS_OPTIONS', WP_PREFIX . 'options' );
require_once( 'class.baseobject.php' );
class Options extends BaseObject {
    public $_table = WP_CLASS_OPTIONS;
    public $_prefix = 'option_';
}
?>