<?php
    $fi_path = $data->fileinfo_url;
    $fid = $data->id;
    $at = $data->archive_type;
    $ts = $data->startdate;
    $tpl_id = $data->template_id;
    $cat = $data->category_id;
    $auth = $data->author_id;
    $entry_id = $data->entry_id;
    if ( $at == 'index' ) {
        $at = NULL;
        $ctx->stash( 'index_archive', true );
    } else {
        $ctx->stash( 'index_archive', false );
    }
    if (! $tmpl = $ctx->stash( 'template' ) ) {
        $tmpl = $data->template();
        $ctx->stash( 'template', $tmpl );
    }
    $ctx->stash( 'template', $tmpl );
    $tts = $tmpl->template_modified_on;
    if ( $tts ) {
        $tts = offset_time( datetime_to_timestamp( $tts ), $blog );
    }
    $ctx->stash( 'template_timestamp', $tts );
    $ctx->stash( 'template_created_on', $tmpl->template_created_on );
    $page_layout = $blog->blog_page_layout;
    $columns = get_page_column( $page_layout );
    $vars = $ctx->__stash[ 'vars' ];
    $vars[ 'page_columns' ] = $columns;
    $vars[ 'page_layout' ] = $page_layout;
    if ( isset( $tmpl->template_identifier ) )
        $vars[ $tmpl->template_identifier ] = 1;
    $mt->configure_paths( $blog->site_path() );
    $ctx->stash( 'build_template_id', $tpl_id );
    if ( isset( $at ) && ( $at != 'Category' ) ) {
        require_once( 'archive_lib.php' );
        try {
            $archiver = ArchiverFactory::get_archiver( $at );
        } catch ( Execption $e ) {
            $mt->http_errr = 404;
            header( 'HTTP/1.1 404 Not Found' );
            return $ctx->error(
                $mt->translate( 'Page not found - [_1]', $at ), E_USER_ERROR );
        }
        $archiver->template_params( $ctx );
    }
    if ( $cat ) {
        if (! $archive_category = $ctx->stash( 'category' ) ) {
            $archive_category = $mt->db()->fetch_category( $cat );
        }
        $ctx->stash( 'category', $archive_category );
        $ctx->stash( 'archive_category', $archive_category );
    }
    if ( $auth ) {
        if (! $archive_author = $ctx->stash( 'author' ) ) {
            $archive_author = $mt->db()->fetch_author( $auth );
        }
        $ctx->stash( 'author', $archive_author );
        $ctx->stash( 'archive_author', $archive_author );
    }
    if ( isset( $at ) ) {
        if ( ( $at != 'Category' ) && isset( $ts ) ) {
            list( $ts_start, $ts_end ) = $archiver->get_range( $ts );
            $ctx->stash( 'current_timestamp', $ts_start );
            $ctx->stash( 'current_timestamp_end', $ts_end );
        }
        $ctx->stash( 'current_archive_type', $at );
    }
    if ( isset( $entry_id ) && ( $entry_id )
        && ( $at == 'Individual' || $at == 'Page' ) ) {
        if (! $entry = $ctx->stash( 'entry' ) ) {
            if ( $at == 'Individual' ) {
                $entry = $mt->db()->fetch_entry( $entry_id );
            } elseif( $at == 'Page' ) {
                $entry = $mt->db()->fetch_page( $entry_id );
            }
        }
        $ctx->stash( 'entry', $entry );
        $ctx->stash( 'current_timestamp', $entry->entry_authored_on );
    }
    if ( $at == 'Category' ) {
        $vars = $ctx->__stash[ 'vars' ];
        $vars[ 'archive_class' ]    = "category-archive";
        $vars[ 'category_archive' ] = 1;
        $vars[ 'archive_template' ] = 1;
        $vars[ 'archive_listing' ]  = 1;
        $vars[ 'module_category_archives' ] = 1;
    }
    $basename = '_' . md5( $file ) . '_mtml_tpl_id_' . $tpl_id;
    ${$basename} = $text;
?>