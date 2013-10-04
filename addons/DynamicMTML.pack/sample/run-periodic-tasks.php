<?php
# DynamicMTML (C) 2010-2011 Alfasado Inc.
# This program is distributed under the terms of the
# GNU General Public License, version 2.

    $mt_dir = dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) . '/';
    if (! file_exists ( $mt_dir . 'mt-config.cgi' ) ) {
        echo "mt-config.cgi was not found.\n";
        return;
    }
    require_once $mt_dir . 'php/mt.php';
    require_once $mt_dir . 'addons/DynamicMTML.pack/php/dynamicmtml.php';
    $mt_config = $mt_dir . 'mt-config.cgi';
    $app = new DynamicMTML();
    $app->configure( $mt_config );
    $mt = MT::get_instance( NULL, $mt_config );
    $ctx =& $mt->context();
    $app->set_context( $mt, $ctx );
    $app->run_tasks();
    $app->run_workers();
?>
