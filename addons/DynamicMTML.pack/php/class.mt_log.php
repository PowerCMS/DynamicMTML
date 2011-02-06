<?php
# DynamicMTML (C) 2010-2011 Alfasado Inc.
# This program is distributed under the terms of the
# GNU General Public License, version 2.

require_once( "class.baseobject.php" );
class Log extends BaseObject {
    public $_table = 'mt_log';
    protected $_prefix = "log_";
}
?>