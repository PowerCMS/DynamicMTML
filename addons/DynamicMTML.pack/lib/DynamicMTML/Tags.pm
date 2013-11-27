package DynamicMTML::Tags;

use strict;

use MT::Util qw( trim remove_html decode_url encode_url );
use PowerCMS::Util qw( is_cms is_application add_slash get_user to_utf8 utf8_on site_url
                       powercms_files_dir is_user_can include_exclude_blogs referral_search_keyword
                       make_seo_basename format_LF get_agent );

sub _hdlr_set_error {
    return _hdlr_pass_tokens( @_ );
}

sub _hdlr_dynamicmtml {
    my ( $ctx, $args, $cond ) = @_;
    my $app = MT->instance();
    my $ref = ref( $app );
    if ( $ref =~ /Search/i ) {
        return '';
    }
    if ( my $key = MT->request( 'dynamicmtml_output_file_key' ) ) {
        return _hdlr_pass_tokens( @_ );
    }
    my $uncompiled = _hdlr_raw_mtml( @_ );
    $uncompiled = "<MTDynamicMTML>$uncompiled</MTDynamicMTML>";
    return $uncompiled;
}

sub _hdlr_nondynamicmtml {
    my $build = _hdlr_pass_tokens( @_ );
    $build = "<MTNonDynamicMTML>$build</MTNonDynamicMTML>";
    return $build;
}

sub _hdlr_raw_mtml {
    my ( $ctx, $args, $cond ) = @_;
    my $app = MT->instance();
    my $uncompiled = $ctx->stash( 'uncompiled' ) || '';
    if ( is_cms( $app ) ) {
        my $mode = $app->mode;
        if ( $mode !~ /preview/ ) {
            return $uncompiled;
        } else {
            my $build = _hdlr_pass_tokens( $ctx, $args, $cond );
            return _filter_build_mtml( $build, $args, $ctx );
        }
    } else {
        return $uncompiled;
    }
}

sub _hdlr_raw_mtml_tag {
    my ( $ctx, $args, $cond ) = @_;
    my $app = MT->instance();
    my $tag = $args->{ tag };
    my $params = $args->{ params };
    $tag = trim( $tag );
    if ( $params ) {
        $tag = "<$tag $params>";
    } else {
        $tag = "<$tag>";
    }
    return $tag;
}

sub _hdlr_comment_out {
    my ( $ctx, $args, $cond ) = @_;
    if ( $args->{ invisible } ) {
        return '';
    }
    my $content = _hdlr_pass_tokens( $ctx, $args, $cond );
    $content = '<!--' . $content . '-->';
    return $content;
}

sub _hdlr_comment_strip {
    my ( $ctx, $args, $cond ) = @_;
    my $content = _hdlr_pass_tokens( $ctx, $args, $cond );
    $content = trim( $content );
    my $begin = quotemeta( '<!--' );
    my $end   = quotemeta( '-->' );
    $content =~ s/$begin//g;
    $content =~ s/$end//g;
    return $content;
}

sub _hdlr_login_author_ctx {
    my ( $ctx, $args, $cond ) = @_;
    my $app = MT->instance();
    my $user = get_user( $app );
    return '' unless defined $user;
    my $tokens = $ctx->stash( 'tokens' );
    my $builder = $ctx->stash( 'builder' );
    $ctx->stash( 'author', $user ) if $user;
    $ctx->var( 'author_archive', 1 );
    my $out = $builder->build( $ctx, $tokens, $cond );
    return $out if $out;
    return '';
}

sub _hdlr_entry_category_block {
    my ( $ctx, $args, $cond ) = @_;
    my $entry = $ctx->stash( 'entry' );
    my $category = $entry->category();
    return '' unless $category;
    my $tokens = $ctx->stash( 'tokens' );
    my $builder = $ctx->stash( 'builder' );
    $ctx->stash( 'category', $category ) if $category;
    my $out = $builder->build( $ctx, $tokens, $cond );
    return $out if $out;
    return '';
}

sub _hdlr_search_entries {
    my ( $ctx, $args, $cond ) = @_;
    require MT::Entry;
    require MT::Request;
    my $r = MT::Request->instance();
    my $tokens = $ctx->stash( 'tokens' );
    my $builder = $ctx->stash( 'builder' );
    my $blog = $ctx->stash( 'blog' );
    my $blog_id = $blog->id;
    my $category_id = $args->{ category_id };
    $category_id = $category_id + 0 if ( $category_id );
    my $category = $args->{ category };
    my $tag_name = $args->{ tag };
    my $tag;
    if ( $tag_name ) {
        require MT::Tag;
        $tag = MT::Tag->load( { name => $tag_name }, { binary => { name => 1 } } );
        return '' unless $tag;
    }
    my $count = $args->{ count };
    my $query = $args->{ query };
    my $target = $args->{ target };
    my $status = $args->{ status };
    if (! $status ) {
        $status = 2;
    }
    $target = 'text' if ( $target && (! MT::Entry->has_column( $target ) ) );
    my $operator = $args->{ operator };
    if ( $operator ) {
        $operator = lc ( $operator );
    } else {
        $operator = 'like'
    }
    if ( $operator =~ /like/i ) {
        $query = "%$query%";
    }
    my $unique = $args->{ unique };
    my $file = $ctx->stash( 'current_archive_file' );
    my $not_entry_id = $args->{ not_entry_id };
    my $published_entry_ids;
    if (! $file ) {
        $unique = 0;
    }
    if ( $unique ) {
        require Digest::MD5;
        $file = Digest::MD5::md5_hex( $file ) if $file;
        $published_entry_ids = $r->cache( 'published_entry_ids:' . $file );
    }
    if (! $published_entry_ids ) {
        $published_entry_ids = ();
    }
    if ( $not_entry_id ) {
        if ( $unique ) {
            push ( @$published_entry_ids, $not_entry_id );
        } else {
            @$published_entry_ids = ( $not_entry_id );
            $unique = 1;
        }
    }
    my $lastn = $args->{ lastn };
    $lastn = $blog->entries_on_index unless $lastn;
    my $offset = $args->{ offset };
    $offset = 0 unless $offset;
    my $class = $args->{ class };
    $class = 'entry' unless $class;
    my $sort_by = $args->{ sort_by };
    $sort_by = 'authored_on' unless $sort_by;
    my $sort_order = $args->{ sort_order };
    $sort_order = 'descend' unless $sort_order;
    my $load_args = { limit => $lastn,
                      offset => $offset,
                      sort => $sort_by,
                      direction => $sort_order, };
    my $count_args = undef;
    # TODO::Category and Tag
    my @categories;
    if ( $category ) {
        $category = trim( $category );
        require MT::Category;
        my @cats = MT::Category->load( { label => $category, class => [ 'category', 'folder' ] } );
        for my $cat ( @cats ) {
            push ( @categories, $cat->id );
        }
    }
    if ( $category_id ) {
        push ( @categories, $category_id );
    }
    if ( @categories ) {
        require MT::Placement;
        $load_args->{ join } = [ 'MT::Placement', 'entry_id', { category_id => \@categories }, { unique => 1 } ];
        $count_args->{ join } = [ 'MT::Placement', 'entry_id', { category_id => \@categories }, { unique => 1 } ];
    } elsif ( $tag ) {
        require MT::ObjectTag;
        $load_args->{ join } = [ 'MT::ObjectTag', 'object_id', { tag_id => $tag->id, object_datasource => 'entry' }, { unique => 1 } ];
        $count_args->{ join } = [ 'MT::ObjectTag', 'object_id', { tag_id => $tag->id, object_datasource => 'entry' }, { unique => 1 } ];
    }
    my @entries;
    my $count_entries;
    my @blog_ids = include_exclude_blogs( $ctx, $args );
    if ( $args->{ blog_id } ) {
        push( @blog_ids, $args->{ blog_id } );
    }
    if ( @blog_ids && ! $blog_ids[ 0 ] ) {
        @blog_ids = ();
    }
    if ( $target ) {
        my $terms = { ( @blog_ids ? ( blog_id => \@blog_ids ) : () ),
                      $target => { $operator => $query } };
        if ( $status ne '*' ) {
            $terms->{ status } = $status;
        }
        if ( $unique && $published_entry_ids ) {
            $terms->{ id } = { not => \@$published_entry_ids };
        }
        @entries = MT->model( $class )->load( $terms, $load_args );
        if ( $count ) {
            $count_entries = MT->model( $class )->count( $terms, $count_args );
        }
    } else {
        my %terms1 = ( ( @blog_ids ? ( blog_id => \@blog_ids ) : () ),
                       title   => { $operator => $query } );
        if ( $status ne '*' ) {
            $terms1{ status } = $status;
        }
        if ( $unique && $published_entry_ids ) {
            $terms1{ id } = { not => \@$published_entry_ids };
        }
        my %terms2 = ( ( @blog_ids ? ( blog_id => \@blog_ids ) : () ),
                       text    => { $operator => $query } );
        if ( $status ne '*' ) {
            $terms2{ status } = $status;
        }
        if ( $unique && $published_entry_ids ) {
            $terms2{ id } = { not => \@$published_entry_ids };
        }
        my %terms3 = ( ( @blog_ids ? ( blog_id => \@blog_ids ) : () ),
                       text_more => { $operator => $query } );
        if ( $status ne '*' ) {
            $terms3{ status } = $status;
        }
        if ( $unique && $published_entry_ids ) {
            $terms3{ id } = { not => \@$published_entry_ids };
        }
        my %terms4 = ( ( @blog_ids ? ( blog_id => \@blog_ids ) : () ),
                       keywords => { $operator => $query } );
        if ( $status ne '*' ) {
            $terms4{ status } = $status;
        }
        if ( $unique && $published_entry_ids ) {
            $terms4{ id } = { not => \@$published_entry_ids };
        }
        my %terms5 = ( ( @blog_ids ? ( blog_id => \@blog_ids ) : () ),
                       excerpt => { $operator => $query } );
        if ( $status ne '*' ) {
            $terms5{ status } = $status;
        }
        if ( $unique && $published_entry_ids ) {
            $terms5{ id } = { not => \@$published_entry_ids };
        }
        @entries = MT->model( $class )->load( [ \%terms1, '-or',
                                                \%terms2, '-or',
                                                \%terms3, '-or',
                                                \%terms4, '-or',
                                                \%terms5 ],
                                                 $load_args );
        if ( $count ) {
            $count_entries = MT->model( $class )->count( [ \%terms1, '-or',
                                                           \%terms2, '-or',
                                                           \%terms3, '-or',
                                                           \%terms4, '-or',
                                                           \%terms5 ],
                                                           $count_args );
        }
    }
    my $res = '';
    my $i = 0;
    my $odd = 1; my $even = 0;
    for my $entry ( @entries ) {
        push ( @$published_entry_ids, $entry->id );
        local $ctx->{ __stash }->{ vars }->{ __first__ } = 1 if ( $i == 0 );
        local $ctx->{ __stash }->{ vars }->{ __entries_count__ } = $count_entries if $count;
        local $ctx->{ __stash }{ entry } = $entry;
        local $ctx->{ __stash }{ blog } = $entry->blog;
        local $ctx->{ __stash }{ blog_id } = $entry->blog_id;
        local $ctx->{ __stash }->{ vars }->{ __counter__ } = $i + 1;
        local $ctx->{ __stash }->{ vars }->{ __odd__ } = $odd;
        local $ctx->{ __stash }->{ vars }->{ __even__ } = $even;
        local $ctx->{ __stash }->{ vars }->{ __last__ } = 1 if ( !defined( $entries[ $i + 1 ] ) );
        my $out = $builder->build( $ctx, $tokens, $cond );
        if ( !defined( $out ) ) { return $ctx->error( $builder->errstr ) };
        $res .= $out;
        if ( $odd == 1 ) { $odd = 0 } else { $odd = 1 };
        if ( $even == 1 ) { $even = 0 } else { $even = 1 };
        $i++;
    }
    if ( $file ) {
        $r->cache( 'published_entry_ids:' . $file, $published_entry_ids );
    }
    $res;
}

sub _hdlr_query {
    my ( $ctx, $args, $cond ) = @_;
    my $app = MT->instance();
    return '' unless is_application( $app );
    my $key = $args->{ key };
    my $val = $app->param( $key );
    return '' unless $val;
    $val = to_utf8( $val );
    $val = utf8_on( $val );
    return $val;
}

sub _hdlr_query_loop {
    my ( $ctx, $args, $cond ) = @_;
    my $app = MT->instance();
    return '' unless is_application( $app );
    my $key  = $args->{ key };
    my $glue = $args->{ glue };
    my @vals = $app->param( $key );
    return '' unless @vals;
    my $tokens = $ctx->stash( 'tokens' );
    my $builder = $ctx->stash( 'builder' );
    my $vars = $ctx->{ __stash }{ vars } ||= {};
    my $res = '';
    my $i = 1;
    for my $val ( @vals ) {
        $val = to_utf8( $val );
        $val = utf8_on( $val );
        local $vars->{ $key } = $val;
        local $vars->{ __key__ } = $key;
        local $vars->{ __value__ } = $val;
        local $vars->{ __counter__ } = $i;
        local $vars->{ __first__ } = 1 if $i == 1;
        local $vars->{ __last__ }  = 1 if $i == scalar @vals;
        local $vars->{ __odd__ }   = ( $i % 2 ) == 1;
        local $vars->{ __even__ }  = ( $i % 2 ) == 0;
        my $out = $builder->build( $ctx, $tokens, $cond );
        $res .= $out;
        $res .= $glue if $glue && $i != scalar @vals;
        $i++;
    }
    $res;
}

sub _hdlr_query_vars {
    my ( $ctx, $args, $cond ) = @_;
    my $app = MT->instance();
    return '' unless is_application( $app );
    my $glue = $args->{ glue };
    my $q = $app->param;
    my $tokens = $ctx->stash( 'tokens' );
    my $builder = $ctx->stash( 'builder' );
    my $vars = $ctx->{ __stash }{ vars } ||= {};
    my $old_vars = $vars;
    my $res = '';
    my $i = 1;
    my $count = scalar $q->param;
    for my $key ( $q->param ) {
        my $val = $q->param( $key );
        $val = to_utf8( $val );
        $val = utf8_on( $val );
        local $vars->{ key } = $key;
        local $vars->{ value } = $val;
        local $vars->{ __counter__ } = $i;
        local $vars->{ __first__ } = 1 if $i == 1;
        local $vars->{ __last__ }  = 1 if $i == $count;
        local $vars->{ __odd__ }   = ( $i % 2 ) == 1;
        local $vars->{ __even__ }  = ( $i % 2 ) == 0;
        my $out = $builder->build( $ctx, $tokens, $cond );
        $res .= $out;
        $res .= $glue if $glue && $i != $count;
        $i++;
    }
    $res;
}

sub _hdlr_set_query_vars {
    my ( $ctx, $args, $cond ) = @_;
    my $app = MT->instance();
    return '' unless is_application( $app );
    my $q = $app->param;
    my $tokens = $ctx->stash( 'tokens' );
    my $builder = $ctx->stash( 'builder' );
    my $vars = $ctx->{ __stash }{ vars } ||= {};
    my $old_vars = $vars;
    for my $key ( $q->param ) {
        my $val = $q->param( $key );
        $val = to_utf8( $val );
        $val = utf8_on( $val );
        $vars->{ $key } = $val;
    }
    my $out = $builder->build( $ctx, $tokens, $cond );
    $vars = $ctx->{ __stash }{ vars } = $old_vars;
    return $out;
}

sub _hdlr_splitvars {
    my ( $ctx, $args, $cond ) = @_;
    my $text = $args->{ text };
    my $delimiter = $args->{ delimiter };
    my $name  = $args->{ name };
    my $glue = $args->{ glue };
    if (! $name ) {
        $name = 'value';
    }
    if (! $delimiter ) {
        $delimiter = ',';
    }
    $delimiter = quotemeta( $delimiter );
    my @vals = split( /$delimiter/, $text );
    return '' unless @vals;
    my $tokens = $ctx->stash( 'tokens' );
    my $builder = $ctx->stash( 'builder' );
    my $vars = $ctx->{ __stash }{ vars } ||= {};
    my $res = '';
    my $i = 1;
    for my $val ( @vals ) {
        local $vars->{ $name } = $val;
        local $vars->{ __counter__ } = $i;
        local $vars->{ __first__ } = 1 if $i == 1;
        local $vars->{ __last__ }  = 1 if $i == scalar @vals;
        local $vars->{ __odd__ }   = ( $i % 2 ) == 1;
        local $vars->{ __even__ }  = ( $i % 2 ) == 0;
        my $out = $builder->build( $ctx, $tokens, $cond );
        $res .= $out;
        $res .= $glue if $glue && $i != scalar @vals;
        $i++;
    }
    $res;
}

sub _hdlr_table_column_value {
    my ( $ctx, $args, $cond ) = @_;
    my $stash = $ctx->stash( $args->{ stash } ) || return '';
    my $model = $args->{ class } || $args->{ stash } || return '';
    my $column = $args->{ column } || return '';
    return if ( $model eq 'author' );
    return '' if ( $column =~ /password/ );
    if ( MT->model( $model )->has_column( $column ) ) {
        return $stash->$column if $stash->$column;
    }
    return '';
}

sub _hdlr_user_agent {
    my ( $ctx, $args, $cond ) = @_;
    my $app = MT->instance();
    if ( $args->{ raw } ) {
        return $app->get_header( 'User-Agent' );
    }
    return get_agent( $app, $args->{ wants }, $args->{ like }, $args->{ exclude } );
}

sub _hdlr_if_login {
    my ( $ctx, $args, $cond ) = @_;
    my $app = MT->instance();
    my $user = get_user( $app );
    if ( defined $user ) {
        $ctx->stash( 'author', $user );
        return 1;
    }
    return 0;
}

sub _hdlr_if_user_has_permission {
    my ( $ctx, $args, $cond ) = @_;
    my $app = MT->instance();
    my $user = get_user( $app );
    return 0 unless defined $user;
    my $blog = $ctx->stash( 'blog' );
    return is_user_can( $blog, $user, $args->{ permission } );
}

sub _hdlr_plugin_path {
    my ( $ctx, $args, $cond ) = @_;
    my $component = $args->{ component };
    my $folder = $args->{ folder };
    my @option = split( /,/, $folder );
    if ( $component ) {
        my $component = MT->component( $component );
        if ( defined $component ) {
            my $component_path = $component->path;
            if ( $component_path =~ /^addons/ ) {
                require Cwd;
                $component_path = File::Spec->catdir( Cwd::getcwd(), $component_path );
            }
            if ( @option ) {
                require File::Spec;
                return add_slash( File::Spec->catdir( $component_path, @option ) );
            } else {
                return add_slash( $component_path );
            }
        }
    }
    return '';
}

sub _hdlr_pass_tokens {
    my ( $ctx, $args, $cond ) = @_;
    $ctx->stash( 'builder' )->build( $ctx, $ctx->stash( 'tokens' ), $cond );
}

sub _hdlr_blog_files_match_directive {
    my ( $ctx, $args, $cond ) = @_;
    my $blog = $ctx->stash( 'blog' );
    my $exclude_extension = $blog->exclude_extension || 'php,cgi,fcgi';
    $exclude_extension =~ s/\s//g;
    my $lc = lc ( $exclude_extension );
    my $uc = uc ( $exclude_extension );
    my @extensions = split( /,/, $lc );
    my @extensions_uc = split( /,/, $uc );
    push ( @extensions, @extensions_uc );
    $exclude_extension = join( '|', @extensions );
    my $FilesMatch = '<FilesMatch .*\.(?!' . $exclude_extension . ')$>';
    return $FilesMatch;
}

sub _hdlr_blog_files_directive {
    my ( $ctx, $args, $cond ) = @_;
    my $blog = $ctx->stash( 'blog' );
    my $dynamic_extension = $blog->dynamic_extension || 'html,mtml';
    $dynamic_extension =~ s/\s//g;
    my $lc = lc ( $dynamic_extension );
    my $uc = uc ( $dynamic_extension );
    my @extensions = split( /,/, $lc );
    my @extensions_uc = split( /,/, $uc );
    push ( @extensions, @extensions_uc );
    $dynamic_extension = join( '|', @extensions );
    my $Files = '<Files ~ "\.(' . $dynamic_extension . ')$">';
    return $Files;
}

sub _hdlr_blog_files_match {
    my ( $ctx, $args, $cond ) = @_;
    my $blog = $ctx->stash( 'blog' );
    my $dynamic_extension = $blog->dynamic_extension || 'html,mtml';
    $dynamic_extension =~ s/\s//g;
    my $lc = lc ( $dynamic_extension );
    my $uc = uc ( $dynamic_extension );
    my @extensions = split( /,/, $lc );
    my @extensions_uc = split( /,/, $uc );
    push ( @extensions, @extensions_uc );
    $dynamic_extension = join( '|', @extensions );
    my $Files = $dynamic_extension;
    return $Files;
}

sub _hdlr_if_dynamic_cache {
    my ( $ctx, $args, $cond ) = @_;
    my $blog = $ctx->stash( 'blog' );
    if ( $blog->dynamic_cache ) {
        return 1;
    }
    return 0;
}

sub _hdlr_if_blog_dynamicmtml {
    my ( $ctx, $args, $cond ) = @_;
    my $blog = $ctx->stash( 'blog' );
    if ( $blog->dynamic_mtml ) {
        return 1;
    }
    return 0;
}

sub _hdlr_plugin_version {
    my ( $ctx, $args, $cond ) = @_;
    my $component = $args->{ plugin };
    if (! $component ) {
        $component = $args->{ component };
    }
    my $scope = $args->{ scope } || 'version';
    if ( $scope =~ /version$/ ) {
        $component = MT->component( $component );
        if ( $component ) {
            return $component->$scope;
        }
    }
    return 0;
}

sub _hdlr_dynamic_cache {
    my ( $ctx, $args, $cond ) = @_;
    my $blog = $ctx->stash( 'blog' );
    if ( $blog->dynamic_cache ) {
        return '$mt->caching( true );';
    }
    return '';
}

sub _hdlr_if_dynamic_conditional {
    my ( $ctx, $args, $cond ) = @_;
    my $blog = $ctx->stash( 'blog' );
    if ( $blog->dynamic_conditional ) {
        return 1;
    }
    return 0;
}

sub _hdlr_dynamic_conditional {
    my ( $ctx, $args, $cond ) = @_;
    my $blog = $ctx->stash( 'blog' );
    if ( $blog->dynamic_conditional ) {
        return '$mt->conditional( true );';
    }
    return '';
}

sub _hdlr_dynamic_search_cache {
    my ( $ctx, $args, $cond ) = @_;
    my $blog = $ctx->stash( 'blog' );
    if ( $blog->search_cache ) {
        return '1';
    }
    return '0';
}

sub _hdlr_dynamic_search_conditional {
    my ( $ctx, $args, $cond ) = @_;
    my $blog = $ctx->stash( 'blog' );
    if ( $blog->search_conditional ) {
        return '1';
    }
    return '0';
}

sub _hdlr_dynamic_search_cache_expiration {
    my ( $ctx, $args, $cond ) = @_;
    my $blog = $ctx->stash( 'blog' );
    if ( $blog->search_cache_expiration ) {
        return $blog->search_cache_expiration;
    }
    return '7200';
}

sub _hdlr_dynamic_directory_index {
    my ( $ctx, $args, $cond ) = @_;
    my $blog = $ctx->stash( 'blog' );
    if ( $blog->index_files ) {
        return $blog->index_files;
    }
    return 'index.html,index.mtml';
}

sub _hdlr_dynamic_extension {
    my ( $ctx, $args, $cond ) = @_;
    my $blog = $ctx->stash( 'blog' );
    if ( $blog->dynamic_extension ) {
        return $blog->dynamic_extension;
    }
    return 'html,mtml';
}

sub _hdlr_dynamic_exclude_extension {
    my ( $ctx, $args, $cond ) = @_;
    my $blog = $ctx->stash( 'blog' );
    if ( $blog->exclude_extension ) {
        return $blog->exclude_extension;
    }
    return 'php,cgi,fcgi';
}

sub _hdlr_current_archive_url {
    my ( $ctx, $args, $cond ) = @_;
    return $ctx->stash( 'current_archive_url' ) || '';
}

sub _hdlr_current_archive_file {
    my ( $ctx, $args, $cond ) = @_;
    return $ctx->stash( 'current_archive_file' ) || '';
}

sub _hdlr_author_language {
    my ( $ctx, $args, $cond ) = @_;
    my $app = MT->instance();
    my $language;
    if ( my $user = $app->user ) {
        $language = $user->preferred_language;
    } else {
        $language = MT->config->DefaultLanguage;
    }
    $language =~ s/\-/_/g;
    if ( $language eq 'en_us' ) {
        $language = 'en';
    }
    return $language;
}

sub _hdlr_dynamic_site_bootstrapper {
    return MT->config->DynamicSiteBootstrapper || '.mtview.php';
}

sub _hdlr_file_get_contents {
    my ( $ctx, $args, $cond ) = @_;
    require LWP::UserAgent;
    my $url = $args->{ url };
    my $ua = LWP::UserAgent->new;
    $ua->agent( 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)' );
    my $req = HTTP::Request->new( GET => $url );
    my $res = $ua->request( $req );
    if ( $res ) {
        my $content = $res->content;
        require Encode;
        $content = MT::I18N::encode_text( $content, undef, 'utf-8' );
        if (! Encode::is_utf8( $content ) ) {
            Encode::_utf8_on( $content );
        }
        return $content;
    }
    return '';
}

sub _hdlr_rand {
    my ( $ctx, $args, $cond ) = @_;
    my $min = $args->{ min };
    my $max = $args->{ max } - $min + 1;
    my $rand = int( rand( $max ) );
    return $rand + $min;
}

sub _filter_build_mtml {
    my ( $str, $arg, $ctx ) = @_;
    my $builder = $ctx->stash( 'builder' );
    my $tokens = $builder->compile( $ctx, $str );
    return $ctx->error( $builder->errstr ) unless defined $tokens;
    my $out = $builder->build( $ctx, $tokens );
    return $ctx->error( $builder->errstr ) unless defined $out;
    return $out;
}

sub _filter_pickup_extension {
    my ( $str, $arg, $ctx ) = @_;
    $str =~ s/\.//g;
    if ( $str =~ /,/ ) {
        my @extensions = split( /,/, $str );
        $str = $extensions[0];
    }
    $str =~ s/\s//g;
    return $str;
}

sub _hdlr_referralkeywords {
    my ( $ctx, $args, $cond ) = @_;
    my $tokens = $ctx->stash( 'tokens' );
    my $builder = $ctx->stash( 'builder' );
    my $glue = $args->{ glue };
    my @keywords = referral_search_keyword();
    return '' unless @keywords;
    my $vars = $ctx->{ __stash }{ vars } ||= {};
    my $res = '';
    my $i = 1;
    for my $val ( @keywords ) {
        next unless $val;
        $val = utf8_on( $val );
        $val = trim( $val );
        local $vars->{ keyword } = $val;
        local $vars->{ __counter__ } = $i;
        local $vars->{ __first__ } = 1 if $i == 1;
        local $vars->{ __last__ }  = 1 if $i == scalar @keywords;
        local $vars->{ __odd__ }   = ( $i % 2 ) == 1;
        local $vars->{ __even__ }  = ( $i % 2 ) == 0;
        my $out = $builder->build( $ctx, $tokens, $cond );
        $res .= $out;
        $res .= $glue if $glue && $i != scalar @keywords;
        $i++;
    }
    $res;
}

sub _hdlr_referralkeyword {
    my $keyword = referral_search_keyword();
    return '' unless $keyword;
    $keyword = utf8_on( $keyword );
    $keyword = trim( $keyword );
    return $keyword;
}

sub _hdlr_trans {
    my ( $ctx, $args, $cond ) = @_;
    my $user = get_user();
    my $lang = MT->config( 'DefaultLanguage' );
    if ( $lang eq 'jp' ) {
        $lang = 'ja';
    } elsif ( $lang eq 'en_us' ) {
        $lang = 'en';
    }
    my $language;
    my $app = MT->instance();
    my $current_language = $app->current_language();
    if ( $user ) {
        $language = $user->preferred_language;
        $language =~ s/\-/_/;
        if ( $language eq 'en_us' ) {
            $language = 'en';
        }
    } else {
        $language = $lang;
    }
    $app->set_language( $language );
    my $phrase = $args->{ phrase };
    my $component = $args->{ component };
    my @params;
    my $param = $args->{ params };
    if ( $param && $param =~ /\%\%/ ) {
        @params = split( /\%\%/, $param );
    } else {
        push ( @params, $param );
    }
    if ( $component ) {
        my $plugin = MT->component( $component );
        if ( $plugin ) {
            return $plugin->translate( $phrase, @params );
        }
    }
    my $trans_phrase = $app->translate( $phrase, @params );
    $app->set_language( $current_language );
    return $trans_phrase;
}

sub _hdlr_entry_statusint {
    my ( $ctx, $args, $cond ) = @_;
    my $entry = $ctx->stash( 'entry' )
        or return $ctx->_no_entry_error();
    return $entry->status;
}

sub _filter_highlightingsearchword {
    my ( $text, $arg, $ctx ) = @_;
    my $class = $arg || 'search-word';
    my $tag_start  = "<strong class=\"$arg\">";
    my $tag_end    = '</strong>';
    my $qtag_start = quotemeta( $tag_start );
    my $qtag_end   = quotemeta( $tag_end );
    my @keywords   = referral_search_keyword();
    return $text unless @keywords;
    for my $keyword ( @keywords ) {
        next unless $keyword;
        $keyword = utf8_on( $keyword );
        $keyword = trim( $keyword );
        $keyword = quotemeta( $keyword );
        my $end;
        while (! $end ) {
            my $original = $text;
            $text =~ s/(<[^>]*>[^<]*?)($keyword)/$1$tag_start$2$tag_end/ig;
            $text =~ s/($qtag_start)$qtag_start($keyword)$qtag_end($qtag_end)/$1$2$3/ig;
            if ( $text eq $original ) {
                $end = 1;
            }
        }
    }
    return $text;
}

sub _filter_make_seo_basename {
    my ( $text, $arg ) = @_;
    $text = make_seo_basename( $text, $arg );
    return $text;
}

sub _filter_trimwhitespace {
    my ( $text, $arg, $ctx ) = @_;
    $text = format_LF( $text );
    my @lines = split ( "\n", $text );
    my $res = '';
    my $in_extra = 0;
    for my $line ( @lines ) {
        my $extra_start = 0;
        my $extra_end = 0;
        my @extras = qw( pre textarea script );
        for my $extra ( @extras ) {
            if ( $line =~ /<$extra.*?>/i || $line =~ /<\/$extra>/i ) {
                my $tmp = $line;
                $tmp =~ s/<$extra.*?>.*?<\/$extra>//g;
                if ( $tmp =~ /<\/$extra>/i ) {
                    $extra_end = 1;
                    $in_extra = 0;
                }
                if ( $tmp =~ /<$extra.*?>/i ) {
                    $extra_start = 1;
                    $in_extra = 1;
                }
            }
        }
        $line =~ s/^\s+//
            if (! $extra_end && (! $in_extra && ! $extra_start ) || ( $in_extra && $extra_start ) );
        $line =~ s/\s+$// if (! $in_extra && ! $extra_start );
        $res .= $line . "\n" if ( $in_extra || $line !~ /^\s*$/ );
    }
    return $res;
}

sub _filter_intval {
    my ( $text, $arg, $ctx ) = @_;
    return $text + 0;
}

sub _hdlr_powercms_files_dir {
    return MT->config->PowerCMSFilesDir || powercms_files_dir();
}

sub _hdlr_strip_tags {
    my($ctx, $args, $cond) = @_;
    my $text = &_hdlr_pass_tokens(@_);
    return $text if index($text || '', '<') == -1;
    my $allowable_tags = defined $args->{allowable_tags}
                       ? $args->{allowable_tags}
                       : $ctx->{config}->AllowableTags;
                         #|| '<a><br><b><i><p><strong><em><img><ul><ol><li><blockquote><pre>';
    require HTML::StripTags;
    HTML::StripTags::strip_tags($text, $allowable_tags);
}

sub _hdlr_error {
    my ( $ctx, $args, $cond ) = @_;
    my $message = $args->{ message };
    return $ctx->error( $message );
}

sub _hdlr_build_recurs {
    my ( $ctx, $args, $cond ) = @_;
    my $res = _hdlr_pass_tokens( @_ );
    if ( $ctx->stash( 'is_file' ) ) {
        return $res;
    }
    my $exclude = $args->{ exclude } || 'CMS';
    my $app = MT->instance;
    if ( is_application( $app ) ) {
        if ( ref $app ne 'MT::App::' . $exclude ) {
            require MT::Template::Tags::Filters;
            return MT::Template::Tags::Filters::_fltr_mteval( $res, 1, $ctx );
        }
    }
    return $res;
}

1;
