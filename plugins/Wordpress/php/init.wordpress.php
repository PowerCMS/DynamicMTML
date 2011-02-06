<?php

    global $app;
    if ( isset( $app ) ) {
        $this->context()->add_token_tag( 'mtwpcategories' );
        $this->context()->add_token_tag( 'mtwplist_categories' );
        $this->context()->add_token_tag( 'mtwpsubcategories' );
    }
?>