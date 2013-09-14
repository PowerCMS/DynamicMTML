package PowerCMS::Util;
use strict;
use base qw/Exporter/;

our $powercms_util_version = '3.3';
our @EXPORT_OK = qw(
    build_tmpl save_asset upload convert_gif_png association_link create_entry
    make_entry write2file read_from_file move_file copy_item remove_item
    relative2path path2relative path2url relative2url url2path
    site_path site_url static_or_support support_dir archive_path
    current_ts current_date current_time next_date prev_date valid_ts month2int send_mail get_mail
    mime_type valid_email get_content get_feed extract_content regex_extract
    str_replace regex_replace ftp_new ftp_get ftp_put ftp_mkdir ftp_quit
    make_zip_archive current_user get_user current_blog uniq_filename set_upload_filename
    get_children_filenames file_extension file_label file_basename
    get_agent if_ua_iPhone if_ua_iPad if_ua_Android if_ua_mobile if_ua_keitai if_user_can
    if_power_edit if_cms if_application if_windows if_blog if_plugin if_writable if_image
    uniq_array get_array_uniq ceil floor round format_LF valid_ip get_mobile_id get_utf utf8_on
    utf8_off to_utf8 normalize upload_callback chomp_dir add_slash
    powercms_files_dir register_template register_templates_to
    load_registered_template load_registered_template_for force_background_task
    powercms_files_dir_path make_dir icon_name icon_class get_weblogs
    get_blog_ids listing_blog_ids get_blogs get_all_blogs str2array first_website get_weblog_ids flush_weblog_ids
    mk_multipart_data send_multipart_mail valid_phone_number valid_postal_code
    get_permissions log2text get_config_inheritance powercms_util_version
    is_ua_iPhone is_ua_iPad is_ua_Android is_ua_mobile is_ua_keitai is_user_can is_power_edit
    is_cms is_application is_windows is_blog is_plugin is_writable is_image csv_new
    get_children_files get_childlen_files get_childlen_filenames flush_blog_cmscache
    plugin_template_path get_asset_from_text include_blogs include_exclude_blogs
    convert2thumbnail create_thumbnail program_is_contained permitted_blog_ids
    referral_site referral_search_keyword referral_serch_keyword make_seo_basename
    encode_utf8_string_to_cp932_octets set_powercms_config get_powercms_config
    set_powercms_config_values reset_powercms_config_values charset_is_utf8
    can_edit_entry allow_upload error_log encode_mime_header is_valid_extension is_valid_extention get_ole_extension
    is_oracle trimj_to valid_url is_psgi is_fastcgi is_powered_cgi get_superuser
);

use File::Spec;
use File::Basename;
use File::Temp qw( tempdir );
use File::Copy::Recursive;
use Encode qw( encode decode );
use Image::Size qw( imgsize );

use MT::Log;
use MT::FileMgr;
use MT::Request;
use MT::Permission;
use MT::Util qw( epoch2ts ts2epoch offset_time_list encode_url decode_url
                 perl_sha1_digest_hex is_valid_email remove_html trim is_valid_url );
# use PowerCMS::Config qw( get_powercms_config set_powercms_config );

sub powercms_util_version { $powercms_util_version }

sub icon_name {
    my ( $ext ) = @_;
    return 'default' unless ( defined( $ext ) && $ext ne '' );
    my %icon_name_of = (
        qr/3gp/   => '3gp',
        qr/aac/   => 'aac',
        qr/aiff?/ => 'aiff',
        qr/asf/   => 'asf',
        qr/asx/   => 'asx',
        qr/avi/   => 'avi',
        qr/csv/   => 'csv',
        qr/docx?/ => 'doc',
        qr/flac/  => 'flac',
        qr/flv/   => 'flv',
        qr/html?/ => 'html',
        qr/m4a/   => 'm4a',
        qr/mkv/   => 'mkv',
        qr/mov/   => 'mov',
        qr/mp3/   => 'mp3',
        qr/mp4/   => 'mp4',
        qr/mpe?g/ => 'mpg',
        qr/ogg/   => 'ogg',
        qr/ogm/   => 'ogm',
        qr/pdf/   => 'pdf',
        qr/pptx?/ => 'ppt',
        qr/qt/    => 'qt',
        qr/swf/   => 'swf',
        qr/wav/   => 'wav',
        qr/wma/   => 'wma',
        qr/wmv/   => 'wmv',
        qr/xlsx?/ => 'xls',
        qr/zip/   => 'zip',
    );
    $ext = lc $ext;
    if ( my ($reg) = grep { $ext =~/^$_$/ } keys %icon_name_of ) {
        return $icon_name_of{ $reg };
    }
    return 'default';
}

sub icon_class {
    my ( $asset_class, $ext ) = @_;
    my %icon_class_of = qw(file 1 video 1 audio 1);
    return '' unless $asset_class && exists $icon_class_of{ $asset_class };
    my $name = icon_name( $ext );
    return "ic-${asset_class}-$name";
}

sub build_tmpl {
    my ( $app, $tmpl, $args, $params ) = @_;
#     my %args = ( ctx => $ctx,
#                  blog => $blog,
#                  entry => $entry,
#                  category => $category,
#                  author => $author,
#                  start => 'YYYYMMDDhhmmss',
#                  end => 'YYYYMMDDhhmmss',
#                 );
#     my %params = ( foo => 'bar', # => <mt:var name="foo">
#                    bar =>'buz', # => <mt:var name="bar">
#                  );
#    my $tmpl = MT::Template->load( { foo => 'bar' } ); # or text
#    return build_tmpl( $app, $tmpl, \%args, \%params );
    if ( ( ref $tmpl ) eq 'MT::Template' ) {
        $tmpl = $tmpl->text;
    }
    $tmpl = $app->translate_templatized( $tmpl );
    require MT::Template;
    require MT::Builder;
    my $ctx = $args->{ ctx };
    if (! $ctx ) {
        require MT::Template::Context;
        $ctx = MT::Template::Context->new;
    }
    $app->run_callbacks( ( ref $app ) . '::powercms_pre_build_tmpl' . ( $args->{ callback_label } ? '.' . $args->{ callback_label } : '' ), $app, $tmpl, $args, $params );
    my $blog     = $args->{ blog };
    my $entry    = $args->{ entry };
    my $category = $args->{ category };
    if ( (! $blog ) && ( $entry ) ) {
        $blog = $entry->blog;
    }
    if ( (! $blog ) && ( $category ) ) {
        $blog = MT::Blog->load( $category->blog_id );
    }
    my $author = $args->{ author };
    my $start = $args->{ start };
    my $end = $args->{ end };
    $ctx->stash( 'blog', $blog );
    $ctx->stash( 'blog_id', $blog->id ) if $blog;
    $ctx->stash( 'local_blog_id', $blog->id ) if $blog;
    $ctx->stash( 'entry', $entry );
#    $ctx->stash( 'page', $entry );
    $ctx->stash( 'category', $category );
    $ctx->stash( 'category_id', $category->id ) if $category;
    $ctx->stash( 'author', $author );
    if ( $start && $end ) {
        if ( ( valid_ts( $start ) ) && ( valid_ts( $end ) ) ) {
            $ctx->{ current_timestamp } = $start;
            $ctx->{ current_timestamp_end } = $end;
        }
    }
    for my $stash ( keys %$args ) {
        if (! $ctx->stash( $stash ) ) {
            if ( ( $stash ne 'start' ) && ( $stash ne 'end' ) ) {
                $ctx->stash( $stash, $args->{ $stash } );
            }
        }
    }
    for my $key ( keys %$params ) {
        $ctx->{ __stash }->{ vars }->{ $key } = $params->{ $key };
    }
    if ( is_application( $app ) && ref( $app ) ne 'MT::App::Upgrader' ) {
        $ctx->{ __stash }->{ vars }->{ magic_token } = $app->current_magic if $app->user;
    }
    my $build = MT::Builder->new;
#     my $tokens = $build->compile( $ctx, $tmpl )
#         or return $app->error( $app->translate(
#             "Parse error: [_1]", $build->errstr ) );
#     defined( my $html = $build->build( $ctx, $tokens ) )
#         or return $app->error( $app->translate(
#             "Build error: [_1]", $build->errstr ) );
    my $tokens = $build->compile( $ctx, $tmpl );
    unless ( $tokens ) {
        error_log( $app->translate( "Parse error: [_1]", $build->errstr ), $blog ? $blog->id : undef );
        return;
    }
    my $html = $build->build( $ctx, $tokens );
    unless ( defined $html ) {
        error_log( $app->translate( "Build error: [_1]", $build->errstr ), $blog ? $blog->id : undef );
        return;
    }
    if ( MT->version_number >= 5 ) {
        $html = utf8_on( $html );
    }
    return $html;
}

sub save_asset {
    my ( $app, $blog, $params, $run_callbacks ) = @_;
#    my %params = ( file => $file,
#                   author => $author,
#                   label => $label,
#                   description => $description,
#                   parent => $parent_asset->id,
#                   object => $obj,
#                   tags => \@tags,
#                   );
#    my $asset = save_asset( $app, $blog, \%params );
    my $blog_id = $blog->id;
    my $file_path = $params->{ file };
    my $fmgr = $blog->file_mgr;
    unless ( $fmgr->exists( $file_path ) ) {
        return undef;
    }
    my $file   = $file_path;
    my $author = $params->{ author };
    $author = current_user( $app ) unless defined $author;
    my $parent      = $params->{ parent } || $params->{ parant }; # Backcompat
    my $label       = $params->{ label };
    my $description = $params->{ description };
    my $obj         = $params->{ object };
    my $tags        = $params->{ tags };
    my $basename  = File::Basename::basename( $file_path );
    my $file_ext  = file_extension( $file_path );
    my $mime_type = mime_type( $file_path );
    my $class     = 'file';
    require MT::Asset;
    my $asset_pkg = MT::Asset->handler_for_file( $basename );
    my $asset;
    if ( $asset_pkg eq 'MT::Asset::Image' ) {
        $asset_pkg->isa( $asset_pkg );
        $class = 'image';
    }
    if ( $asset_pkg eq 'MT::Asset::Audio' ) {
        $asset_pkg->isa( $asset_pkg );
        $class = 'audio';
    }
    if ( $asset_pkg eq 'MT::Asset::Video' ) {
        $asset_pkg->isa( $asset_pkg );
        $class = 'video';
    }
    unless ( defined $author ) {
        if ( $parent ) {
            my $parent_asset = $asset_pkg->load( { id => $parent } );
            if ( $parent_asset ) {
                $author = MT::Author->load( $parent_asset->created_by );
            }
        }
        $author = MT::Author->load( undef, { limit => 1 } ) unless ( defined $author );
    }
    my $url = $file_path;
    $url =~ tr!\\!/! if if_windows();
    $url = path2url( $url, $blog, 1 );
    $url = path2relative( $url, $blog, 1 );
    $file_path = path2relative( $file_path, $blog, 1 );
    $asset = $asset_pkg->load( { blog_id => $blog_id,
                                 file_path => $file_path } );
    my $original;
    unless ( $asset ) {
        $asset = $asset_pkg->new();
        $asset->created_on( current_ts( $blog ) ); # for posting from mobile
    } else {
        $original = $asset->clone(); # FIXME
    }
    $original = $asset->clone(); # FIXME
    $asset->blog_id( $blog_id );
    $asset->url( $url );
    $asset->file_path( $file_path );
    $asset->file_name( $basename );
    $asset->mime_type( $mime_type );
    $asset->file_ext( $file_ext );
    $asset->class( $class );
    $asset->created_by( $author->id );
    $asset->modified_by( $author->id );
    if ( $parent ) {
        $asset->parent( $parent );
    }
    my ( $w, $h, $id );
    if ( $class eq 'image' ) {
        ( $w, $h, $id ) = imgsize( $file );
        $asset->image_width( $w );
        $asset->image_height( $h );
    }
    unless ( $label ) {
        $label = file_label( $basename );
    }
    $asset->label( $label );
    if ( $description ) {
        $asset->description( $description );
    }
    if ( $run_callbacks ) {
        $app->run_callbacks( 'cms_pre_save.asset', $app, $asset, $original )
          || return $app->errtrans( "Saving [_1] failed: [_2]", 'asset',
            $app->errstr );
    }
    $asset->set_tags( @$tags );
    $asset->save or die $asset->errstr;
    if ( $run_callbacks ) {
        $app->run_callbacks( 'cms_post_save.asset', $app, $asset, $original );
    } else {
        $app->log(
            {
                message => $app->translate(
                    "File '[_1]' uploaded by '[_2]'", $asset->file_name,
                    $author->name,
                ),
                level    => MT::Log::INFO(),
                class    => 'asset',
                blog_id  => $blog_id,
                category => 'new',
            }
        );
    }
    # my @fstats = stat( $file );
    # my $bytes = $fstats[7];
    if ( $obj ) {
        if ( $obj->id ) {
            require MT::ObjectAsset;
            my $object_asset = MT::ObjectAsset->get_by_key( {
                                                              blog_id => $obj->blog_id,
                                                              asset_id => $asset->id,
                                                              object_id => $obj->id,
                                                              object_ds => $obj->datasource,
                                                            } );
            unless ( $object_asset->id ) {
                $object_asset->save or die $object_asset->errstr;
            }
        }
    }
    my $res = upload_callback( $app, $blog, $asset, $id ) if $run_callbacks;
    return $asset;
}

sub upload {
    my ( $app, $blog, $name, $dir, $params ) = @_;
    my $limit = $app->config( 'CGIMaxUpload' ) || 20480000;
    $app->validate_magic() or return 0;
    if ( is_cms() || $params->{ permission_check } ) {
        if ( $blog ) {
    #        return 0 unless $app->can_do( 'save_asset' );
            unless ( $app->mode eq 'do_signup' ) {
                return 0 unless $app->can_do( 'upload' );
            }
        } else {
            my $uploadable_mode = $app->config( 'UploadableMode' );
            unless ( ref( $uploadable_mode ) eq 'ARRAY' ) {
                if ( $uploadable_mode =~ /,/ ) {
                    $uploadable_mode = [ split( /\s*,\s*/, $uploadable_mode ) ];
                } else {
                    $uploadable_mode = [ $uploadable_mode ];
                }
            }
            my $mode = $app->mode;
            return 0 unless grep { $_ eq $mode } @$uploadable_mode;
        }
    }
#    my %params = ( object => $obj,
#                   author => $author,
#                   rename => 1,
#                   label => 'foo',
#                   description => 'bar',
#                   format_LF => 1,
#                   singler => 1,
#                   no_asset => 1,
#                   );
#    my $upload = upload( $app, $blog, $name, $dir, \%params );

    my $obj         = $params->{ object };
    my $rename      = $params->{ 'rename' };
    my $label       = $params->{ label };
    my $format_lf   = $params->{ format_LF };
    my $singler     = $params->{ singler };
    my $no_asset    = $params->{ no_asset };
    my $description = $params->{ description };
    my $force_decode_filename = $params->{ force_decode_filename };
    my $no_decode = $app->config( 'NoDecodeFilename' );
    if (! $force_decode_filename ) {
        if ( $no_decode ) {
            $force_decode_filename = 1;
        }
    }
    my $fmgr = MT::FileMgr->new( 'Local' ) or die MT::FileMgr->errstr;
    my $q = $app->param;
    my @files = $q->upload( $name );
    my @assets;
    my $upload_total;
    for my $file ( @files ) {
        my $size = ( -s $file );
        $upload_total = $upload_total + $size;
        if ( $limit < $upload_total ) {
            return wantarray ? ( undef, 1 ) : undef; # Upload file size over CGIMaxUpload;
        }
    }
    for my $file ( @files ) {
        my $orig_filename = file_basename( $file );
        $orig_filename = decode_url( $orig_filename );
        my $basename = $orig_filename;
        $basename =~ s/%2[Ee]/\./g;
        if ( $basename =~ m!\.\.|\0|\|! ) {
            return wantarray ? ( undef, 1 ) : undef;
        }
        $basename = $orig_filename;
#        $basename = encode_url( $basename );
        $basename =~ tr{\\}{/}; ## Change backslashes to forward slashes
        $basename =~ s!^.*/!!;  ## Get rid of full directory paths
        $basename = encode_url( $basename );
        $basename
            = Encode::is_utf8( $basename )
            ? $basename
            : Encode::decode( $app->charset,
                File::Basename::basename( $basename ) );
        if ( my $deny_exts = $app->config->DeniedAssetFileExtensions ) {
            my @deny_exts = map {
                if   ( $_ =~ m/^\./ ) {qr/$_/i}
                else                  {qr/\.$_/i}
            } split '\s*,\s*', $deny_exts;
            my @ret = File::Basename::fileparse( $basename, @deny_exts );
            if ( $ret[2] ) {
                return wantarray ? ( undef, 1 ) : undef;
            }
        }
        if ( my $allow_exts = $app->config( 'AssetFileExtensions' ) ) {
            my @allow_exts = map {
                if   ( $_ =~ m/^\./ ) {qr/$_/i}
                else                  {qr/\.$_/i}
            } split '\s*,\s*', $allow_exts;
            my @ret = File::Basename::fileparse( $basename, @allow_exts );
            unless ( $ret[2] ) {
                return wantarray ? ( undef, 1 ) : undef;
            }
        }
        $orig_filename = $basename;
        $orig_filename = decode_url( $orig_filename ) if $force_decode_filename;
        my $file_label = file_label( $orig_filename );
        if ( $app->mode =~ /^(?:edit_profile|do_signup)$/ ) { # FIXME: ad-hoc
            if ( MT::I18N::is_utf8( $file_label ) ) {
                $file_label = Encode::decode_utf8( $file_label );
            }
            if ( $no_decode ) {
                $orig_filename = Encode::decode_utf8( $orig_filename );
            }
        }
        if (! $no_decode ) {
            $orig_filename = set_upload_filename( $orig_filename );
        }
        my $out = File::Spec->catfile( $dir, $orig_filename );
        if ( $rename ) {
            $out = uniq_filename( $out );
        }
        $dir =~ s!/$!! unless $dir eq '/';
        if (! is_writable( $dir, $blog ) ) {
            return wantarray ? ( undef, 1 ) : undef;
        }
        unless ( $fmgr->exists( $dir ) ) {
            $fmgr->mkpath( $dir ) or return MT->trans_error( "Error making path '[_1]': [_2]",
                                    $out, $fmgr->errstr );
        }
        my $temp  = "$out.new";
        my $umask = $app->config( 'UploadUmask' );
        my $old   = umask( oct $umask );
        open( my $fh, ">$temp" ) or die "Can't open $temp!";
        if ( is_image( $file ) ) {
            require MT::Image;
            if (! MT::Image::is_valid_image( $fh ) ) {
                close( $fh );
                next;
            }
        }
        binmode( $fh );
        while ( read( $file, my $buffer, 1024 ) ) {
            $buffer = format_LF( $buffer ) if $format_lf;
            print $fh $buffer;
        }
        close( $fh );
        $fmgr->rename( $temp, $out );
        umask( $old );
        my $user = $params->{ author };
        $user = current_user( $app ) unless defined $user;
        if ( $no_asset ) {
            if ( $singler ) {
                return $out;
            }
            push( @assets, $out );
        } else {
            if ( ( $user ) && ( $blog ) ) {
                my %params = ( file => $out,
                               author => $user,
                               label => ( $label || $file_label ),
                               description => $description,
                               object => $obj,
                               );
                my $asset = save_asset( $app, $blog, \%params, 1 ) or die;
                if ( $singler ) {
                    return $asset;
                }
                push( @assets, $asset ) if defined $asset;
            }
        }
    }
    return \@assets;
}

sub convert_gif_png {
    my $image = shift;
    # TODO::Save Asset
    my $new_file = $image;
    my $ext      = file_extension($image);
    if ($ext eq 'gif') {
        $new_file =~ s/\.gif$/.png/i;
    } elsif ($ext eq 'png') {
        $new_file =~ s/\.png$/.gif/i;
    } else {
        return;
    }
    if (! -f $new_file ) {
        if ( copy_item( $image, $new_file ) ) {
            require MT::Image;
            my $image = MT::Image->new( Filename => $new_file );
            if ( my $data = $image->convert( Type =>
                                             file_extension( $new_file ) ) ) {
                write2file( $new_file, $data, 'upload' );
            }
        }
    }
    return $new_file if ( -f $new_file );
}

sub association_link {
    my ( $app, $author, $role, $blog ) = @_;
    eval{ # FIXME: new install needs eval?
        require MT::Association;
        my $assoc = MT::Association->link( $author => $role => $blog );
        if ( $assoc ) {
            my $log = MT::Log->new;
            my $msg = { message => $app->translate(
                        "[_1] registered to the blog '[_2]'",
                        $author->name,
                        $blog->name
                    ),
                    level    => MT::Log::INFO(),
                    class    => 'author',
                    category => 'new',
                    blog_id  => $blog->id,
            };
            if ( ref $app =~ /^MT::App::/ ) {
                $msg->{ ip } = $app->remote_ip;
                if ( my $user = $app->user ) {
                    $log->author_id( $user->id );
                }
            }
            $log->set_values( $msg );
            $log->save or die $log->errstr;
            return $assoc;
        }
    };
    return undef;
}

sub create_entry {
    my ( $app, $blog, $args, $params, $run_callbacks ) = @_;
#     my @categories = MT::Category->load( { foo => 'bar' } );
#     my @tags = ( 'foo', 'bar' );
#     %args = ( title => 'foo',
#               text  => 'bar',
#               author_id => 1, # or current user
#               status => MT::Entry::RELEASE(), # or blog's status_default
#               customfield_basename => 'buz',
#               tags => \@tags, # string array
#               category_id => 1, # or categories => \@categories,
#              );
#     %params = ( rebuildme => 1,
#                 dependencies => 1,
#                 no_save => 1,
#                 background => 1,
#              );
#     my $entry = create_entry( $app, $blog, \%args, \%params[, $run_callbacks] );
    require MT::Entry;
    my $entry; my $is_new; my $original;
    if ( $args->{ id } ) {
        $entry = MT::Entry->load( $args->{ id } );
    }
    unless ( defined $entry ) {
        $entry = MT::Entry->new;
        $is_new = 1;
    } else {
        $original = $entry->clone_all();
    }
    $entry->blog_id( $blog->id );
    my $clumns = $entry->column_names;
    my $fields;
    my $professional = 0;
    eval { require CustomFields::Field;
           require CustomFields::BackupRestore; };
    unless ( $@ ) {
        $professional = 1;
    }
    for my $key ( keys %$args ) {
        if ( grep( /^$key$/, @$clumns ) ) {
            $entry->$key( $args->{ $key } );
        } else {
            if ( $key eq 'tags' ) {
                my $tags = $args->{ $key };
                $entry->set_tags( @$tags );
            } elsif ( $key eq 'category_id' ) {
            } elsif ( $key eq 'categories' ) {
            } else {
                if ( $professional ) {
                    my $cf = CustomFields::Field->load( { blog_id => [ 0, $blog->id ],
                                                          basename => $key } );
                    if ( defined $cf ) {
                        $fields->{ $key } = $args->{ $key };
                    }
                }
            }
        }
    }
    my $user = current_user( $app );
    unless ( $entry->author_id ) {
        unless ( defined $user ) {
            return undef;
        }
        $entry->author_id( $user->id );
    } else {
        unless ( $user ) {
            require MT::Author;
            $user = MT::Author->load( $entry->author_id );
            unless ( defined $user ) {
                return undef;
            }
        }
    }
    unless ( $entry->created_by ) {
        $entry->created_by( $entry->author_id );
    }
    unless ( $entry->modified_by ) {
        $entry->modified_by( $entry->author_id );
    }
    unless ( $entry->status ) {
        $entry->status( $blog->status_default );
    }
    unless ( $entry->allow_comments ) {
        $entry->allow_comments( $blog->allow_comments_default );
    }
    unless ( $entry->allow_pings ) {
        $entry->allow_pings( $blog->allow_pings_default );
    }
    unless ( $entry->class ) {
        $entry->class( 'entry' );
    }
    unless ( $entry->authored_on ) {
        $entry->authored_on( current_ts( $blog ) );
    }
    if ( $entry->allow_pings ) {
        unless ( $entry->atom_id ) {
            $entry->atom_id( $entry->make_atom_id() );
        }
    }
    if ( $run_callbacks ) {
        $app->run_callbacks( 'cms_pre_save.' . $entry->class, $app, $entry, $original );
    }
    if ( $params->{ no_save } ) {
        return $entry;
    }
    $entry->save or die $entry->errstr;
    my @saved_cats;
    if ( $args->{ category_id } ) {
        require MT::Placement;
        my $place = MT::Placement->get_by_key( { blog_id => $blog->id,
                                                 category_id => $args->{ category_id },
                                                 entry_id => $entry->id,
                                                 is_primary => 1,
                                             } );
        $place->save or die $place->errstr;
        push( @saved_cats, $args->{ category_id } );
    }
    if ( $args->{ categories } ) {
        my $categories = $args->{ categories };
        my $i = 1;
        for my $category ( @$categories ) {
            my $is_primary = 0;
            if ( ( $i == 1 ) && (! $args->{ category_id } ) ) {
                $is_primary = 1;
            }
            my $place = MT::Placement->get_by_key( { blog_id => $blog->id,
                                                     category_id => $category->id,
                                                     entry_id => $entry->id,
                                                     is_primary => $is_primary,
                                                 } );
            $place->save or die $place->errstr;
            push( @saved_cats, $category->id );
            $i++;
        }
    }
    if (! $is_new ) {
        my @placement = MT::Placement->load( blog_id => $blog->id,
                                             entry_id => $entry->id, );
        for my $place ( @placement ) {
            my $place_id = $place->id;
            if ( (! scalar @saved_cats ) || (! grep( /^$place_id$/, @saved_cats ) ) ) {
                $place->remove or die $place->errstr;
            }
        }
    }
    if ( $professional ) {
        CustomFields::BackupRestore::_update_meta( $entry, $fields );
    }
    $entry->clear_cache();
    if ( $run_callbacks ) {
        $app->run_callbacks( 'cms_post_save.' . $entry->class, $app, $entry, $original );
    }
    if ( $params->{ rebuildme } ) {
        my $dependencies = $params->{ dependencies };
        if ( $entry->status == MT::Entry::RELEASE() ) {
            my $rebuild_sub = sub {
                $app->rebuild_entry( Entry => $entry->id, BuildDependencies => $dependencies );
            };
            if ( $params->{ background } ) {
                force_background_task($rebuild_sub);
            } else {
                MT::Util::start_background_task($rebuild_sub);
            }
        }
    }
    return $entry;
}

sub make_entry {
    return create_entry( @_ );
}

sub write2file {
    my ( $path, $data, $type, $blog ) = @_;
    my $fmgr = MT::FileMgr->new( 'Local' ) or return 0; # die MT::FileMgr->errstr;
    if ( $blog ) {
        $path = relative2path( $path, $blog );
    }
    my $dir = dirname( $path );
    $dir =~ s!/$!! unless $dir eq '/';
    unless ( $fmgr->exists( $dir ) ) {
        $fmgr->mkpath( $dir ) or return 0; # MT->trans_error( "Error making path '[_1]': [_2]",
                                # $path, $fmgr->errstr );
    }
    $fmgr->put_data( $data, "$path.new", $type );
    if ( $fmgr->rename( "$path.new", $path ) ) {
        if ( $fmgr->exists( $path ) ) {
            return 1;
        }
    }
    return 0;
}

sub read_from_file {
    my ( $path, $type, $blog ) = @_;
    my $fmgr = MT::FileMgr->new( 'Local' ) or die MT::FileMgr->errstr;
    if ( $blog ) {
        $path = relative2path( $path, $blog );
    }
    unless ( $fmgr->exists( $path ) ) {
       return '';
    }
    my $data = $fmgr->get_data( $path, $type );
    return $data;
}

sub move_file {
    my ( $from, $to, $blog ) = @_;
    my $fmgr = MT::FileMgr->new( 'Local' ) or die MT::FileMgr->errstr;
    if ( $blog ) {
        $from = relative2path( $from, $blog );
        $to = relative2path( $to, $blog );
    }
    my $dir = dirname( $to );
    $dir =~ s!/$!! unless $dir eq '/';
    unless ( $fmgr->exists( $dir ) ) {
        $fmgr->mkpath( $dir ) or return MT->trans_error( "Error making path '[_1]': [_2]",
                                $to, $fmgr->errstr );
    }
    $fmgr->rename( $from, $to );
}

sub copy_item {
    my ( $from, $to, $blog ) = @_;
    my $fmgr = MT::FileMgr->new( 'Local' ) or die MT::FileMgr->errstr;
    if ( $blog ) {
        $from = relative2path( $from, $blog );
        $to = relative2path( $to, $blog );
    }
    my $dir = dirname( $to );
    $dir =~ s!/$!! unless $dir eq '/';
    unless ( $fmgr->exists( $dir ) ) {
        $fmgr->mkpath( $dir ) or return MT->trans_error( "Error making path '[_1]': [_2]",
                                $to, $fmgr->errstr );
    }
    if ( File::Copy::Recursive::rcopy( $from, $to ) ) {
        return 1;
    }
    return 0;
}

sub remove_item {
    my ( $remove, $blog ) = @_;
    my $fmgr = MT::FileMgr->new( 'Local' ) or die MT::FileMgr->errstr;
    if ( $blog ) {
        $remove = relative2path( $remove, $blog );
    }
    unless ( $fmgr->exists( $remove ) ) {
        return 0;
    }
    if ( -f $remove ) {
        return $fmgr->delete( $remove );
    }
    if ( -d $remove ) {
        File::Path::rmtree( [ $remove ] );
        unless ( -d $remove ) {
            return 1;
        }
    }
    return 0;
}

sub relative2path {
    my ( $path, $blog ) = @_;
    my $app = MT->instance();
    my $static_file_path = static_or_support();
    my $archive_path = archive_path( $blog );
    my $site_path = site_path( $blog );
    $path =~ s/%s/$static_file_path/;
    $path =~ s/%r/$site_path/;
    if ( $archive_path ) {
        $path =~ s/%a/$archive_path/;
    }
    return $path;
}

sub path2relative {
    my ( $path, $blog, $exclude_archive_path ) = @_;
    my $app = MT->instance();
    my $static_file_path = static_or_support();
    my $archive_path = archive_path( $blog );
    my $site_path = site_path( $blog, $exclude_archive_path );
    if ( is_windows() ) {
        $path             =~ tr!\\!/!;
        $static_file_path =~ tr!\\!/!;
        $archive_path     =~ tr!\\!/!;
        $site_path        =~ tr!\\!/!;
    }
    $static_file_path = quotemeta( $static_file_path );
    $archive_path = quotemeta( $archive_path );
    $site_path = quotemeta( $site_path );
    $path =~ s/$static_file_path/%s/;
    $path =~ s/$site_path/%r/;
    if ( $archive_path ) {
        $path =~ s/$archive_path/%a/;
    }
    if ( $path =~ m!^https{0,1}://! ) {
        my $site_url = quotemeta( site_url( $blog ) );
        $path =~ s/$site_url/%r/;
    }
    return $path;
}

sub path2url {
    my ( $path, $blog, $exclude_archive_path ) = @_;
    my $site_path = quotemeta( site_path( $blog, $exclude_archive_path ) );
    my $site_url = site_url( $blog );
    $path =~ s/^$site_path/$site_url/;
    if ( is_windows() ) {
        $path =~ tr!\\!/!;
    }
    return $path;
}

# BACKWARD
# sub path2relative {
#     my ( $path, $blog, $exclude_archive_path ) = @_;
#     my $app = MT->instance();
#     my $static_file_path = quotemeta( static_or_support() );
#     my $archive_path = quotemeta( archive_path( $blog ) );
#     my $site_path = quotemeta( site_path( $blog, $exclude_archive_path ) );
#     $path =~ s/$static_file_path/%s/;
#     $path =~ s/$site_path/%r/;
#     if ( $archive_path ) {
#         $path =~ s/$archive_path/%a/;
#     }
#     if ( $path =~ m!^https{0,1}://! ) {
#         my $site_url = quotemeta( site_url( $blog ) );
#         $path =~ s/$site_url/%r/;
#     }
#     return $path;
# }
#
# sub path2url {
#     my ( $path, $blog, $exclude_archive_path ) = @_;
#     my $site_path = quotemeta( site_path( $blog, $exclude_archive_path ) );
#     my $site_url = site_url( $blog );
#     $path =~ s/^$site_path/$site_url/;
#     if ( is_windows() ) {
#         $path =~ tr!/!\\!;
#     }
#     return $path;
# }

sub relative2url {
    my ( $path, $blog ) = @_;
    return path2url( relative2path( $path,$blog ), $blog );
}

sub url2path {
    my ( $url, $blog ) = @_;
    my $site_url = quotemeta( site_url( $blog ) );
    my $site_path = site_path( $blog );
    $url =~ s/^$site_url/$site_path/;
    if ( is_windows() ) {
        $url =~ tr!/!\\!;
    }
    return $url;
}

sub site_path {
#     my $blog = shift;
#     my $site_path = $blog->archive_path;
#     $site_path = $blog->site_path unless $site_path;
#     return chomp_dir( $site_path );
    my ( $blog, $exclude_archive_path ) = @_;
    my $site_path;
    unless ( $exclude_archive_path ) {
        $site_path = $blog->archive_path;
    }
    $site_path = $blog->site_path unless $site_path;
    return chomp_dir( $site_path );
}

sub archive_path {
    my $blog = shift;
    my $archive_path = $blog->archive_path;
    return chomp_dir( $archive_path );
}

sub site_url {
    my $blog = shift;
    my $site_url = $blog->site_url;
    $site_url =~ s{/+$}{};
    return $site_url;
}

sub static_or_support {
    my $app = MT->instance();
    my $static_or_support;
    if ( MT->version_number < 5 ) {
        $static_or_support = $app->static_file_path;
    } else {
        $static_or_support = $app->support_directory_path;
    }
    return $static_or_support;
}

sub support_dir {
    my $app = MT->instance();
    my $support_dir;
    if ( MT->version_number < 5 ) {
        $support_dir = File::Spec->catdir( $app->static_file_path, 'support' );
    } else {
        $support_dir = $app->support_directory_path;
    }
    return $support_dir;
}

sub current_ts {
    my $blog = shift;
    my @tl = offset_time_list( time, $blog );
    my $ts = sprintf '%04d%02d%02d%02d%02d%02d', $tl[5]+1900, $tl[4]+1, @tl[3,2,1,0];
    return $ts;
}

sub current_date {
    my $blog = shift;
    my @tl = offset_time_list( time, $blog );
    my $ts = sprintf '%04d-%02d-%02d', $tl[5]+1900, $tl[4]+1, $tl[3];
    return $ts;
}

sub current_time {
    my $blog = shift;
    my @tl = offset_time_list( time, $blog );
    my $ts = sprintf '%02d:%02d:%02d', @tl[2,1,0];
    return $ts;
}

sub next_date {
    my ( $blog, $ts ) = @_;
    $ts = ts2epoch( $blog, $ts );
    $ts += 86400;
    return epoch2ts( $blog, $ts );
}

sub prev_date {
    my ( $blog, $ts ) = @_;
    $ts = ts2epoch( $blog, $ts );
    $ts -= 86400;
    return epoch2ts( $blog, $ts );
}

sub valid_ts {
    my $ts = shift;
    if ( ( ref $ts ) eq 'ARRAY' ) {
        $ts = @$ts[0];
    }
    return 0 unless ( $ts =~ m/^[0-9]{14}$/ );
    my $year = substr( $ts, 0, 4 );
    my $month = substr( $ts, 4, 2 );
    my $day = substr( $ts, 6, 2 );
    my ( @mlast ) = ( 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31 );
    if ( $month < 1 || 12 < $month ) {
        return 0;
    }
    if ( $month == 2 ) {
        if ( ( ( $year % 4 == 0 ) && ( $year % 100 != 0 ) ) || ( $year % 400 == 0 ) ) {
            $mlast[1]++;
        }
    }
    if ( $day < 1 || $mlast[$month-1] < $day ) {
        return 0;
    }
    my $hour = substr( $ts, 8, 2 );
    my $min = substr( $ts, 10, 2 );
    my $sec = substr( $ts, 12, 2 );
    if ( ( $hour < 25 ) && ( $min < 61 ) && ( $sec < 61 ) ) {
        return 1;
    }
    return 0;
}

sub valid_phone_number { # TODO: L10N
    my $str = shift;
    if ( ( ref $str ) eq 'ARRAY' ) {
        $str = @$str[0];
    }
    $str =~ /\A(?:0\d{1,4}-?\d{1,4}-?\d{3,5}|\+[1-9][-\d]+\d)\z/ ? 1 : 0; # TODO
}

sub valid_postal_code { # TODO: L10N
    my $str = shift;
    if ( ( ref $str ) eq 'ARRAY' ) {
        $str = @$str[0];
    }
    $str =~ /\A[0-9]{3}-?[0-9]{4}\z/ ? 1 : 0;
}

sub month2int {
    my $month = lc substr( shift, 0, 3 );
    $month eq 'jan' ? 1 :
    $month eq 'feb' ? 2 :
    $month eq 'mar' ? 3 :
    $month eq 'apr' ? 4 :
    $month eq 'mar' ? 5 :
    $month eq 'jun' ? 6 :
    $month eq 'jul' ? 7 :
    $month eq 'aug' ? 8 :
    $month eq 'sep' ? 9 :
    $month eq 'oct' ? 10 :
    $month eq 'nov' ? 11 :
    $month eq 'dec' ? 12 :
    0;
}

sub send_multipart_mail {
    my ( $args ) = @_;
    # $args->{ from };
    # $args->{ to };
    # $args->{ subject };
    # $args->{ body };
    # $args->{ cc };
    # $args->{ bcc };
    # $args->{ cb_params };
    # $args->{ attaches };
    my ( $ctype, $body ) = mk_multipart_data( {
        Body     => [ $args->{ body } ],
        Attaches => $args->{ attaches },
    } );
    send_mail(
        {
            from    => $args->{ from },
            to      => $args->{ to },
            subject => $args->{ subject },
            body    => $body,
            cc      => $args->{ cc },
            bcc     => $args->{ bcc },
            content_type => $ctype,
        },
        $args->{ cb_params }
    );
}

sub mk_multipart_data {
    my ( $args ) = @_;
    my $org_body = $args->{ Body };
    my $attaches = $args->{ Attaches };
    my $ent_args = $args->{ 'MIME::Entity::build' } || {};
    return unless ( ref $org_body eq 'ARRAY' );
    require MIME::Entity;
    my $charset = $ent_args->{ Charset } && $ent_args->{ Charset } ne ''
                ? $ent_args->{ Charset } : '';
    my $encoding = $ent_args->{ Encoding } && $ent_args->{ Encoding } ne ''
                 ? $ent_args->{ Encoding } : '';
    my $mime = MIME::Entity->build(
        From    => 'from_address_dummy',
        To      => 'to_address_dummy',
        Subject => 'subject_dummy',
        Data    => $org_body,
        ( $charset ne '' ? ( Charset => $charset ) : () ),
        ( $encoding ne '' ? ( Encoding => $encoding ) : () ),
    );
    if ( ref $attaches eq 'ARRAY' ) {
        foreach my $at ( @$attaches ) {
            next unless ( ref $at eq 'HASH' );
            my $path = $at->{ Path };
            next unless ( $path && -e $path );
            my $type = $at->{ Type } || mime_type( $path );
            next unless $type;
            my $enc  = $at->{ Encoding } || '-SUGGEST';
            my $disp = $at->{ Disposition } && $at->{ Disposition } ne ''
                     ? $at->{ Disposition } : '';
            $mime->attach(
                Path     => $path,
                Type     => $type,
                Encoding => $enc,
                ( $disp ne '' ? ( Disposition => $disp ) : () ),
            );
        }
    }
    return ( $mime->head->get( 'Content-Type' ), join( '', @{ $mime->body } ) );
}

sub send_mail {
    my ( $from, $to, $subject, $body,
                     $cc, $bcc, $params4cb ) = @_; #old interface
    my ( $args, $params );
    my $content_type;
    if ( ref $from eq 'HASH' ) { # new interface
        $args    = $from;
        $params  = $to;
        $from    = $args->{ from };
        $to      = $args->{ to };
        $subject = $args->{ subject };
        $body    = $args->{ body };
        $cc      = $args->{ cc };
        $bcc     = $args->{ bcc };
        $content_type = $args->{ content_type };
    }
    else {
        $args = {
            from    => $from,
            to      => $to,
            subject => $subject,
            body    => $body,
            cc      => $cc,
            bcc     => $bcc,
        };
        $params = $params4cb;
    }
    my $plugin = MT->component( 'PowerCMS' );
    unless ( $subject ) {
        error_log( $plugin->translate( 'Sending mail failed: [_1]', $plugin->translate( 'Subject is empty.' ) ) );
        return;
    }
    unless ( $body ) {
        error_log( $plugin->translate( 'Sending mail failed: [_1]', $plugin->translate( 'Body is empty.' ) ) );
        return;
    }
    unless ( $to ) {
        error_log( $plugin->translate( 'Sending mail failed: [_1]', $plugin->translate( 'To is empty.' ) ) );
        return;
    }
    unless ( $from ) {
        error_log( $plugin->translate( 'Sending mail failed: [_1]', $plugin->translate( 'From is empty.' ) ) );
        return;
    }
    $params = { key => 'default' } unless defined $params;
    $params->{ key } = 'default' unless defined $params->{ key };
    my $app = MT->instance();
    my $mgr = MT->config;
    my $enc = $mgr->PublishCharset;
    my $mail_enc = lc( $mgr->MailEncoding || $enc );
    $body = MT::I18N::encode_text( $body, $enc, $mail_enc );
    return unless
        $app->run_callbacks( ( ref $app ) . '::pre_send_mail', $app, \$args, \$params );
    $from = $args->{ from },
    $to = $args->{ to },
    $subject = $args->{ subject },
    $body = $args->{ body },
    $cc = $args->{ cc },
    $bcc = $args->{ bcc },
    my @mailto = split( /,/, $to );
    my %head;
    %head = (
        To => \@mailto,
        From => $from,
        Subject => $subject,
        ( ref $cc eq 'ARRAY' ? ( Cc => $cc ) : () ),
        ( ref $bcc eq 'ARRAY' ? ( Bcc => $bcc ) : () ),
        ( $content_type ? ( 'Content-Type' => $content_type ) : () ),
#        ( MT->config->MailReturnPath ? ( 'Return-Path' => MT->config->MailReturnPath ) : () ),
        ( MT->config->MailReplyTo ? ( 'Reply-To' => MT->config->MailReplyTo ) : () ),
    );
    my $send = sub {
        require MT::Mail;
        my $res = MT::Mail->send( \%head, $body );
        unless ( $res ) {
            MT->log( MT::Mail->errstr );
            return 0;
        }
    };
    if ( is_application( $app ) ) {
        return force_background_task( $send );
    } else {
        return $send->();
    }
}

sub get_mail {
    my ( $server, $id, $passwd, $protocol, $delete ) = @_;
    my $app = MT->instance();
    eval { require Net::POP3;
           require MIME::Parser; } || return undef;
    my $pop3 = Net::POP3->new( $server )
        or die "Could not open POP3. (Server: $server)";
    next unless ( defined $pop3 );
    my $login = ( lc( $protocol ) eq 'apop' ) ? 'apop' : 'login';
    $login = $pop3->$login( $id, $passwd );
    return undef unless $login;
    my $messages = $pop3->list();
    my $charset = lc $app->config( 'PublishCharset' );
    $charset = $charset eq 'utf-8'     ? 'utf8'
             : $charset eq 'euc-jp'    ? 'eucJP-ms'
             : $charset eq 'shift_jis' ? 'cp932'
             : $charset;
    my $tempdir = $app->config( 'TempDir' );
    my @emails;
    for my $id ( sort( keys %$messages ) ) {
        mkdir( $tempdir, 0755 ) unless ( -d $tempdir );
        my $message = $pop3->get( $id );
        my $parser = new MIME::Parser;
        my $workdir = tempdir( DIR => $tempdir );
        $parser->output_dir( $workdir );
        my $entity = $parser->parse_data( $message );
        # for multipart
        my @parts = $entity->parts;
        my %parsed_files;
        my $i = 0;
        for my $part ( @parts ) {
            my $type = $part->mime_type;
            my $bhandle = $part->bodyhandle;
            my $file_path = $bhandle->{ MB_Path };
            my $attach = $entity->parts( $i )->head->as_string;
            my @attach_file = split( /\n/, $attach );
            my $attach_file = '';
            foreach ( @attach_file ) {
                $attach_file .= $_;
            }
            if ( $attach_file ) {
                if ( $attach_file =~ /name="([^"]+)"/ ) {
                    $attach_file = decode_mime_header( $1 );
                    $parsed_files{ $file_path } = $attach_file;
                }
            }
            $i++;
        }
        # /for multipart
        my $header = $entity->head;
        my $from = $header->get( 'From' );
        my $to = $header->get( 'to' );
        my $subject = $header->get( 'Subject' );
        $subject = encode( $charset, decode( 'MIME-Header', $subject ) );
        if ( $charset eq 'utf8' ) {
            if ( MT->version_number >= 5 ) {
                $subject = utf8_on( $subject );
            }
        }
        $from = encode( $charset, decode( 'MIME-Header', $from ) );
        $from =~ s/\n+//g;
        $subject =~ s/\n+//g if $subject;
        $from = $1 if ( $from =~ /<([^>]+)>/ );
        my $body = ''; my @f;
        if ( $entity->is_multipart ) {
            opendir( my $fh, $workdir );
            my @files = readdir( $fh );
            closedir( $fh );
            for my $file ( @files ) {
                if ( $file !~ /^\./ ) {
                    if ( $file =~ /^msg-[0-9]{1,}-[0-9]{1,}\.txt/ ) {
                        $body .= read_from_file( File::Spec->catfile( $workdir, $file ) );
                    } else {
                        push( @f, File::Spec->catfile( $workdir, $file ) );
                    }
                }
            }
        } else {
            $body = $entity->bodyhandle;
            $body = $body->as_string;
        }
        $body = encode( $charset, decode( 'iso-2022-jp', $body ) );
        if ( $charset eq 'utf8' ) {
            if ( MT->version_number >= 5 ) {
                $body = utf8_on( $body );
            }
        }
        my $mail = { from => $from,
                     subject => $subject,
                     body => $body,
                     files => \@f,
                     directory => $workdir,
                     parsed_files => \%parsed_files,
                   };
        push( @emails, $mail );
        $pop3->delete( $id ) if $delete;
    }
    $pop3->quit;
    return \@emails;
}

sub make_zip_archive {
    my ( $directory, $out, $files, $encoding ) = @_;
    eval { require Archive::Zip } || return undef;
    my $archiver = Archive::Zip->new();
    my $fmgr = MT::FileMgr->new( 'Local' ) or die MT::FileMgr->errstr;
    my $dir = dirname( $out );
    $dir =~ s!/$!! unless $dir eq '/';
    unless ( $fmgr->exists( $dir ) ) {
        $fmgr->mkpath( $dir ) or return undef;
    }
    if (-f $directory ) {
        my $basename = File::Basename::basename( $directory );
        $archiver->addFile( utf8_on( $directory ), $basename );
        return $archiver->writeToFileNamed( $out );
    }
    $directory =~ s!/$!!;
    unless ( $files ) {
        @$files = get_children_filenames( $directory );
    }
    $encoding ||= 'utf-8'; # TODO
    my $re = qr{^(?:\Q$directory\E)?[/\\]*};
    for my $file ( @$files ) {
        $file = Encode::encode($encoding, $file)
            if Encode::is_utf8($file);
        my $new = $file;
        $new =~ s/$re//;
        $archiver->addFile( $file, $new );
    }
    return $archiver->writeToFileNamed( $out );
}

sub current_user {
    my $app = shift || MT->instance();
    my $user;
    eval { $user = $app->user };
    unless ( $@ ) {
        return $user if defined $user;
    }
    return undef;
}

sub get_user {
    my $app = shift || MT->instance();
    my $user; my $sess;
    if ( is_application( $app ) ) {
        require MT::Session;
        eval { $user = $app->user };
        unless ( defined $user ) {
            eval { ( $sess, $user ) = $app->get_commenter_session() };
            unless ( defined $user ) {
                if ( $app->param( 'sessid' ) ) {
                    my $sess = MT::Session->load( { id => $app->param( 'sessid' ),
                                                    kind => 'US' } );
                    if ( defined $sess ) {
                       my $sess_timeout = $app->config->UserSessionTimeout;
                       if ( ( time - $sess->start ) < $sess_timeout ) {
                            $user = MT::Author->load( { name => $sess->name, status => MT::Author::ACTIVE() } );
                            $sess->start( time );
                            $sess->save or die $sess->errstr;
                        }
                    }
                }
            }
        }
        unless ( defined $user ) {
            if ( my $mobile_id = get_mobile_id( $app ) ) {
                my @authors = MT::Author->search_by_meta( mobile_id => $mobile_id );
                if ( my $author = $authors[0] ) {
                    if ( $author->status == MT::Author::ACTIVE() ) {
                        $user = $author;
                    }
                }
            }
        }
    }
    return $user if defined $user;
    return undef;
}

sub current_blog {
    my $app = shift || MT->instance();
    my $blog;
    eval { $blog = $app->blog };
    if ( $@ ) {
        return undef;
    }
    return $blog;
}

sub get_content {
    my ( $uri, $utf8, $file ) = @_;
    my $app = MT->instance();
    my $remote_ip;
    eval { $remote_ip = $app->remote_ip };
    my $agent;
    if ( $remote_ip ) {
        $agent = "Mozilla/5.0 (PowerCMS X_FORWARDED_FOR:$remote_ip)";
    } else {
        $agent = 'Mozilla/5.0 (PowerCMS)';
    }
    my $ua = MT->new_ua( { agent => $agent } ) or return undef;
    my $req = HTTP::Request->new( GET => $uri );
    my $res = $ua->request( $req );
    if ( $res ) {
        my $content = $res->content;
        if ( $utf8 ) {
            $content = # MT::I18N::encode_text( $content, undef, 'utf-8' );
            $content = to_utf8( $content );
        }
        if ( $file ) {
            write2file( $file, $content ) or return undef;
        }
        if ( $utf8 && MT->version_number >= 5 ) {
            $content = utf8_on( $content );
        }
        return $content;
    }
    # my $code = $res->code;
    # my @response = [ $content, $code ];
    # return \@response;
    return undef;
}

sub get_feed {
    my ( $uri, $utf8 ) = @_;
    return undef unless $uri;
    require MT::Feeds::Lite;
    my $lite  = MT::Feeds::Lite->fetch( $uri ) or return '';
    my $title = $lite->find_title( $lite->feed );
    my $link  = $lite->find_link( $lite->feed );
    my $entries = $lite->entries;
    my $count = scalar @$entries;
    if ( $utf8 ) {
        $title = to_utf8( $title );
        $link  = to_utf8( $link );
        if ( MT->version_number >= 5 ) {
            $title = utf8_on( $title );
            $link  = utf8_on( $link );
        }
    }
    my %feed = (
        LITE    => $lite,
        TITLE   => $title,
        LINK    => $link,
        ENTRIES => \%$entries,
        COUNT   => $count,
    );
    return \%feed;
}

sub extract_content {
    my ( $start, $end, $data, $cont ) = @_;
    $start = quotemeta( $start );
    $end   = quotemeta( $end );
    if ( $data =~ /($start)(.*?)($end)/s ) {
        if (! $cont ) {
            return $2;
        }
        return $1 . $2 . $3;
    }
    return '';
}

sub regex_extract {
    my ( $pattern, $data ) = @_;
    if ( $pattern =~ m!^/! ) {
        if ( $pattern !~ /[()]/ ) {
            $pattern =~ s!^/!/(!;
            $pattern =~ s{(?=/[^/]*$)}{)};
        }
        $pattern =~ s!^/!/.*?!;
        $pattern =~ s!(?=/[^/]*$)!.*\$!;
    }
    return regex_replace( $pattern, '$1', $data );
}

sub str_replace {
    my ( $from, $to, $data ) = @_;
    $from = quotemeta( $from );
    $data =~ s/$from/$to/g;
    return $data;
}

sub regex_replace {
    my ( $pattern, $replace, $data ) = @_;
    require MT::Template::Context;
    my $ctx = MT::Template::Context->new;
    my $val = [ $pattern, $replace ];
    if ( MT->version_number < 5 ) {
        $data = MT::Template::Context::_fltr_regex_replace( $data, $val, $ctx );
    } else {
        require MT::Template::Tags::Filters;
        $data = MT::Template::Tags::Filters::_fltr_regex_replace( $data, $val, $ctx );
    }
    return $data;
}

sub csv_new {
    my $csv = do {
    eval { require Text::CSV_XS };
    unless ( $@ ) { Text::CSV_XS->new( { binary => 1 } ); }
    else { eval { require Text::CSV };
        return undef if $@; Text::CSV->new( { binary => 1 } ); } };
    return $csv;
}

sub ftp_new {
    my ( $server, $account, $password, $param ) = @_;
    eval { require Net::FTP } || return undef;
    my $ftp = Net::FTP->new( $server, %$param ) or die;
    my $login = $ftp->login( $account, $password ) or return undef;
    return $ftp;
}

sub ftp_get {
    my ( $ftp, $cwd, $rfile, $lfile, $mode ) = @_;
    $mode = 'binary' unless $mode;
    $ftp->$mode;
    $ftp->cwd( $cwd ) or return undef;
    return $ftp->get( $rfile, $lfile );
}

sub ftp_put {
    my ( $ftp, $cwd, $file, $mode, $params ) = @_;
    my $app = MT->instance();
    return unless
        $app->run_callbacks( 'pre_ftp_put',
                                   $app, \$ftp, \$cwd, \$file, \$mode, \$params );
    $mode = 'binary' unless $mode;
    $ftp->$mode;
    my $pwd = $ftp->pwd();
    $ftp->cwd( $cwd ) or return undef;
    my $ftp_put = $ftp->put( $file );
    if ( $ftp_put ) {
        $app->run_callbacks( 'post_ftp_put', $app, $ftp, $cwd, $file, $mode, $params );
    }
    $ftp->cwd( $pwd );
    return $ftp_put;
}

sub ftp_mkdir {
    my ( $ftp, $cwd, $dir ) = @_;
    $ftp->cwd( $cwd ) or return undef;
    return $ftp->mkdir( $dir );
}

sub ftp_quit {
    my $ftp = shift;
    return $ftp->quit;
}

sub set_upload_filename {
    my $file = shift;
    $file = File::Basename::basename( $file );
    my $ctext = encode_url( $file );
    if ( $ctext ne $file ) {
        if ( MT->version_number >= 5 ) {
            $file = utf8_off( $file );
        }
        my $extension = file_extension( $file );
        my $ext_len   = length( $extension ) + 1;
        require Digest::MD5;
        $file = Digest::MD5::md5_hex( $file );
        $file = substr( $file, 0, 255 - $ext_len );
        if ( $extension ) {
            $file .= '.' . $extension;
        }
    }
    return $file;
}

sub uniq_filename {
    my $file = shift;
    #require File::Basename;
    my $dir = File::Basename::dirname( $file );
    $file =~ s/%7[Ee]//g;
    my $no_decode = MT->config( 'NoDecodeFilename' );
    $file = $no_decode ? file_basename($file)
                       : set_upload_filename($file);
    $file = File::Spec->catfile($dir, $file);
    return $file unless ( -f $file );
    my $file_extension = file_extension( $file );
    my $base           = $file;
#    $base =~ s/(.{1,})\.$file_extension$/$1/;
    if ( $file_extension ) {
        $base =~ s/(.{1,})\.$file_extension$/$1/;
    }
    $base = $1 if ( $base =~ /(^.*)_[0-9]{1,}$/ );
    my $i = 0;
    do { $i++;
         $file = $base . '_' . $i;
         if ( $file_extension ) {
            $file .= '.' . $file_extension;
         }
       } while ( -e $file );
    return $file;
}

sub get_children_filenames {
    my ( $directory, $pattern ) = @_;
    my @wantedFiles;
    require File::Find;
    if ( $pattern ) {
        if ( $pattern =~ m!^(/)(.+)\1([A-Za-z]+)?$! ) {
            $pattern = $2;
            if ( my $opt = $3 ) {
                $opt =~ s/[ge]+//g;
                $pattern = "(?$opt)" . $pattern;
            }
            my $regex = eval { qr/$pattern/ };
            if ( defined $regex ) {
                my $command = 'File::Find::find( sub { push( @wantedFiles, $File::Find::name ) if ( /' . $pattern. '/ ) && -f; }, $directory );';
                eval $command;
                if ( $@ ) {
                    return undef;
                }
            } else {
                return undef;
            }
        }
    } else {
        File::Find::find( sub { push( @wantedFiles, $File::Find::name ) unless (/^\./) || ! -f; }, $directory );
    }
    return @wantedFiles;
}

sub get_children_files     { goto &get_children_filenames }
sub get_childlen_files     { goto &get_children_filenames } # Backcompat
sub get_childlen_filenames { goto &get_children_filenames } # Backcompat

sub get_permissions {
    my $app = MT->instance();
    return undef if (! is_cms( $app ) );
    return undef if (! $app->user );
    my $r = MT::Request->instance;
    my $perms;
    $perms = $r->cache( 'powercms_get_permissions' );
    return $perms if $perms;
    #require MT::Permission;
    @$perms = MT::Permission->load( { author_id => $app->user->id } );
    $r->cache( 'powercms_get_permissions', $perms );
    return $perms;
}

sub uniq_array {
    my $array = shift;
    my %hash  = ();
    for my $value ( @$array ) {
        $hash{ $value } = 1;
    }
    return (
        keys %hash
    );
}

sub get_array_uniq {
    my @array = do { my %h; grep { ! $h{ $_ }++ } @_ };
    return @array;
}

sub ceil {
    my $var = shift;
    my $a = 0;
    $a = 1 if ( $var > 0 and $var != int( $var ) );
    return int( $var + $a );
}

sub floor {
    my $var = shift;
    return int( $var );
}

sub round {
    my $var = shift;
    return int( $var + 0.5 );
}

sub format_LF {
    my $data = shift;
    $data =~ s/\r\n?/\n/g;
    return $data;
}

sub get_agent {
    my $app = shift || MT->instance();
    # Agent Smartphone Keitai Mobile // TODO::Mobile Safari Apple(Mac)
    my $wants = shift;
    my $like  = shift;
    my $exclude = shift || '';
    $wants = 'Agent' unless $wants;
    $wants = lc( $wants );
    $exclude = lc( $exclude ) if $exclude;
    my $agent = $app->get_header( 'User-Agent' );
    if ( $like ) {
        if ( $agent =~ /$like/i ) {
            return 1;
        } else {
            return 0;
        }
    }
    my %smartphone = (
        'Android'       => 'Android',
        'dream'         => 'Android',
        'CUPCAKE'       => 'Android',
        'blackberry'    => 'BlackBerry',
        'iPhone'        => 'iPhone',
        'iPod'          => 'iPhone',
        'iPad'          => 'iPad',
        'webOS'         => 'Palm',
        'incognito'     => 'iPhone',
        'webmate'       => 'iPhone',
        'Opera Mini'    => 'Opera Mini',
        'Windows Phone' => 'Windows Phone',
    );
    for my $key ( keys %smartphone ) {
        if ( $agent =~ /$key/ ) {
            if ( $wants eq 'agent' ) {
                return $smartphone{ $key };
            } else {
                if ( $wants ne 'keitai' ) {
                    if ( $wants eq 'tablet' ) {
                        if ( $smartphone{ $key } eq 'iPad' ) {
                            return 1;
                        } elsif ( $smartphone{ $key } eq 'Android' ) {
                            if ( $agent !~ /\sMobile\s/i ) {
                                return 1;
                            } else {
                                return 0;
                            }
                        } else {
                            return 0;
                        }
                    } else {
                        if ( $exclude eq 'tablet' ) {
                            if ( $smartphone{ $key } eq 'iPad' ) {
                                return 0;
                            } elsif ( $smartphone{ $key } eq 'Android' ) {
                                if ( $agent =~ /\sMobile\s/i ) {
                                    return 1;
                                } else {
                                    return 0;
                                }
                            } else {
                                return 1;
                            }
                        }
                    }
                    return 1;
                } else {
                    return 0;
                }
            }
        }
    }
    my %keitai = (
        'DoCoMo'      => 'DoCoMo',
        'UP\.Browser' => 'AU',
        'SoftBank'    => 'SoftBank',
        'Vodafone'    => 'SoftBank',
    );
    for my $key ( keys %keitai ) {
        if ( $agent =~ /$key/ ) {
            if ( $wants eq 'agent' ) {
                return $keitai{ $key };
            } else {
                return 1;
            }
        }
    }
    if ( $wants eq 'agent' ) {
        return 'PC';
    } else {
        return 0;
    }
}

sub if_ua_keitai {
    my $app = shift || MT->instance();
    return get_agent( $app, 'Keitai' );
}

sub if_ua_mobile {
    my $app = shift || MT->instance();
    return get_agent( $app, 'Mobile' );
}

sub if_ua_iPhone {
    my $app = shift || MT->instance();
    return get_agent( $app ) eq 'iPhone'
        ? 1 : 0;
}

sub if_ua_iPad {
    my $app = shift || MT->instance();
    return get_agent( $app ) eq 'iPad'
        ? 1 : 0;
}

sub if_ua_Android {
    my $app = shift || MT->instance();
    return get_agent( $app ) eq 'Android'
        ? 1 : 0;
}

sub if_user_can {
    my ( $blog, $user, $permission ) = @_;
    return unless $user;
    unless ( $permission =~ /^can_/ ) {
        $permission = 'can_' . $permission;
    }
    my $perm = $user->is_superuser;
    unless ( $perm ) {
        if ( $blog ) {
            my $admin = 'can_administer_blog';
            $perm = $user->permissions( $blog->id )->$admin;
            $perm = $user->permissions( $blog->id )->$permission unless $perm;
        } else {
            $perm = $user->permissions()->$permission;
        }
    }
    return $perm;
}

sub if_power_edit {
    my $app = shift || MT->instance();
    if ( if_cms( $app ) ) {
        my $return_args = $app->param( 'return_args' );
        if ( $return_args =~ /__mode=open_batch_editor/ ) {
            return 1;
        }
    }
    return 0;
}

sub if_application {
    my $app = shift || MT->instance();
    return (ref $app) =~ /^MT::App::/ ? 1 : 0;
}

sub if_cms {
    my $app = shift || MT->instance();
    return ( ref $app eq 'MT::App::CMS' ) ? 1 : 0;
}

sub if_windows { $^O eq 'MSWin32' ? 1 : 0 }

sub if_blog {
    my $app = shift || MT->instance();
    return current_blog( $app ) ? 1 : 0;
}

sub if_plugin {
    my $component = shift;
    if ( $component ) {
        my $plugin = MT->component( $component );
        if ( defined $plugin ) {
            return 1;
        }
    }
    return 0;
}

sub if_writable {
    my ( $path, $blog ) = @_;
    my $app = MT->instance();
    $path = File::Spec->canonpath( $path );
    my $tempdir = quotemeta( chomp_dir( $app->config( 'TempDir' ) ) );
    my $importdir = quotemeta( chomp_dir( $app->config( 'ImportPath' ) ) );
    my $powercms_files_dir = quotemeta( chomp_dir( powercms_files_dir() ) );
    my $support_dir = quotemeta( chomp_dir( support_dir() ) );
    if ( $path =~ /\A(?:$tempdir|$importdir|$powercms_files_dir|$support_dir)/i ) {
        return 1;
    }
    if ( defined $blog ) {
        my $site_path = quotemeta( site_path( $blog ) );
        if ( $path =~ /^$site_path/ ) {
            return 1;
        }
    }
    return 0;
}

sub is_ua_iPhone   { goto &if_ua_iPhone }
sub is_ua_iPad     { goto &if_ua_iPad }
sub is_ua_Android  { goto &if_ua_Android }
sub is_ua_mobile   { goto &if_ua_mobile }
sub is_ua_keitai   { goto &if_ua_keitai }
sub is_user_can    { goto &if_user_can }
sub is_power_edit  { goto &if_power_edit }
sub is_cms         { goto &if_cms }
sub is_application { goto &if_application }
sub is_windows     { goto &if_windows }
sub is_blog        { goto &if_blog }
sub is_plugin      { goto &if_plugin }
sub is_writable    { goto &if_writable }

sub file_extension {
    my ( $file, $nolc ) = @_;
    $file = file_basename( $file );
    my $extension = '';
    if ( $file =~ /\.([^.]+)\z/ ) {
        $extension = $1;
        $extension = lc( $extension ) unless $nolc;
    }
    return $extension;
}

sub file_label {
    my $file = shift;
    $file = file_basename( $file );
    my $file_extension = file_extension( $file, 1 );
    my $base = $file;
    $base =~ s/(.{1,})\.$file_extension$/$1/;
    $base = Encode::decode_utf8( $base ) unless Encode::is_utf8( $base );
    return $base;
}

sub file_basename {
    my $file = shift
        or return;
    if ( !is_windows() && $file =~ /\\/ ) { # Windows Style Path on Not-Win
        my $prev = File::Basename::fileparse_set_fstype( 'MSWin32' );
        $file = File::Basename::basename( $file );
        File::Basename::fileparse_set_fstype( $prev );
    } else {
        $file = File::Basename::basename( $file );
    }
    return $file;
}

sub mime_type {
    my $file = shift;
    my %mime_type = (
        'css'   => 'text/css',
        'html'  => 'text/html',
        'mtml'  => 'text/html',
        'xhtml' => 'application/xhtml+xml',
        'htm'   => 'text/html',
        'txt'   => 'text/plain',
        'rtx'   => 'text/richtext',
        'tsv'   => 'text/tab-separated-values',
        'csv'   => 'text/csv',
        'hdml'  => 'text/x-hdml; charset=Shift_JIS',
        'xml'   => 'application/xml',
        'atom'  => 'application/atom+xml',
        'rss'   => 'application/rss+xml',
        'rdf'   => 'application/rdf+xml',
        'xsl'   => 'text/xsl',
        'mpeg'  => 'video/mpeg',
        'mpg'   => 'video/mpeg',
        'mpe'   => 'video/mpeg',
        'qt'    => 'video/quicktime',
        'avi'   => 'video/x-msvideo',
        'movie' => 'video/x-sgi-movie',
        'mov'   => 'video/quicktime',
        'ice'   => 'x-conference/x-cooltalk',
        'svr'   => 'x-world/x-svr',
        'vrml'  => 'x-world/x-vrml',
        'wrl'   => 'x-world/x-vrml',
        'vrt'   => 'x-world/x-vrt',
        'spl'   => 'application/futuresplash',
        'js'    => 'application/javascript',
        'json'  => 'application/json',
        'hqx'   => 'application/mac-binhex40',
        'doc'   => 'application/msword',
        'pdf'   => 'application/pdf',
        'ai'    => 'application/postscript',
        'eps'   => 'application/postscript',
        'ps'    => 'application/postscript',
        'rtf'   => 'application/rtf',
        'ppt'   => 'application/vnd.ms-powerpoint',
        'xls'   => 'application/vnd.ms-excel',
        'dcr'   => 'application/x-director',
        'dir'   => 'application/x-director',
        'dxr'   => 'application/x-director',
        'dvi'   => 'application/x-dvi',
        'gtar'  => 'application/x-gtar',
        'gzip'  => 'application/x-gzip',
        'latex' => 'application/x-latex',
        'lzh'   => 'application/x-lha',
        'swf'   => 'application/x-shockwave-flash',
        'sit'   => 'application/x-stuffit',
        'tar'   => 'application/x-tar',
        'tcl'   => 'application/x-tcl',
        'tex'   => 'application/x-texinfo',
        'texinfo'=>'application/x-texinfo',
        'texi'  => 'application/x-texi',
        'src'   => 'application/x-wais-source',
        'zip'   => 'application/zip',
        'au'    => 'audio/basic',
        'snd'   => 'audio/basic',
        'midi'  => 'audio/midi',
        'mid'   => 'audio/midi',
        'kar'   => 'audio/midi',
        'mpga'  => 'audio/mpeg',
        'mp2'   => 'audio/mpeg',
        'mp3'   => 'audio/mpeg',
        'ra'    => 'audio/x-pn-realaudio',
        'ram'   => 'audio/x-pn-realaudio',
        'rm'    => 'audio/x-pn-realaudio',
        'rpm'   => 'audio/x-pn-realaudio-plugin',
        'wav'   => 'audio/x-wav',
        'bmp'   => 'image/x-ms-bmp',
        'gif'   => 'image/gif',
        'jpeg'  => 'image/jpeg',
        'jpg'   => 'image/jpeg',
        'jpe'   => 'image/jpeg',
        'png'   => 'image/png',
        'tiff'  => 'image/tiff',
        'tif'   => 'image/tiff',
        'ico'   => 'image/vnd.microsoft.icon',
        'pnm'   => 'image/x-portable-anymap',
        'ras'   => 'image/x-cmu-raster',
        'pnm'   => 'image/x-portable-anymap',
        'pbm'   => 'image/x-portable-bitmap',
        'pgm'   => 'image/x-portable-graymap',
        'ppm'   => 'image/x-portable-pixmap',
        'rgb'   => 'image/x-rgb',
        'xbm'   => 'image/x-xbitmap',
        'xpm'   => 'image/x-pixmap',
        'xwd'   => 'image/x-xwindowdump',
    );
    my $extension = file_extension( $file );
    my $type = $mime_type{ $extension };
    $type = 'text/plain' unless $type;
    return $type;
}

sub valid_email {
    my $email = shift;
    if ( ( ref $email ) eq 'ARRAY' ) {
        $email = @$email[0];
    }
    return 0 unless is_valid_email( $email );
    if ( $email =~ /^[^\@]+\@[^.]+\../ ) {
        return 1;
    }
    return 0;
}

sub valid_url {
    my $url = shift;
    if ( ( ref $url ) eq 'ARRAY' ) {
        $url = @$url[0];
    }
    if ( $url !~ m!^https{0,1}://! ) {
        return 0;
    }
    return is_valid_url( $url );
}

sub get_mobile_id {
    my ( $app, $to_hash ) = @_;
    my $mobile_id;
    my $user_agent = $app->get_header( 'User-Agent' );
    my @browswer = split( m!/!, $user_agent );
    my $ua = $browswer[0];
    if ( $ua eq 'DoCoMo' ) {
        if ( $user_agent =~ /^.*(ser[0-9]{11,}).*$/ ) {
            $mobile_id = $1;
        }
    } elsif ( $ua =~ /UP\.Browser/ ) {
        my $x_up_subno = $user_agent = $app->get_header( 'X_UP_SUBNO' );
        # AU
        if ( $x_up_subno ) {
            $mobile_id = $x_up_subno;
        }
    } elsif ( ( $ua eq 'SoftBank' ) || ( $ua eq 'Vodafone' ) ) {
        # SoftBank
        my $x_jphone_uid = $app->get_header( 'X_JPHONE_UID' );
        if ( $x_jphone_uid ) {
            $mobile_id = $x_jphone_uid;
        }
    }
    if ( $mobile_id && $to_hash ) {
        $mobile_id = perl_sha1_digest_hex( $mobile_id );
    }
    return $mobile_id if $mobile_id;
    return '';
}

sub valid_ip {
    my ( $remote_ip, $table ) = @_;
    # valid_ip( $app->remote_ip, \@ip_table )
    $table = format_LF( $table );
    return 1 if ( grep( /^$remote_ip$/, @$table ) );
    my $ip_table = join( "\n", @$table );
    if ( $remote_ip =~ /(^[0-9]{1,}\.[0-9]{1,}\.[0-9]{1,}\.)([0-9]{1,}$)/ ) {
        my @bits = qw/ 0 126 62 30 14 6 2 /;
        my $check = quotemeta( $1 );
        my $last = $2;
        if ( $ip_table =~ m!$check([0-9]{1,})/([0-9]{1,})! ) {
            my $begin = $1;
            my $bit = $2;
            if ( ( $begin eq '0' ) && ( $bit eq '24' ) ) {
                if ( ( 0 < $last ) && ( 255 > $last ) ) {
                    return 1;
                }
            } else {
                $bit = $bit - 24;
                my $range = $bits[ $bit ];
                my $end = $begin + $range;
                if ( ( $last >= $begin ) && ( $last <= $end ) ) {
                    return 1;
                }
            }
        }
    }
    return 0;
}

sub get_utf {
    my $text = shift;
    eval { require Unicode::Japanese } || return undef;
    my $t = Unicode::Japanese->new( $text, 'utf8' );
    $text = $t->getu();
    return $text;
}

sub utf8_on {
    my $text = shift;
    if (! Encode::is_utf8( $text ) ) {
        Encode::_utf8_on( $text );
    }
    return $text;
}

sub utf8_off {
    my $text = shift;
    return MT::I18N::utf8_off( $text );
}

sub to_utf8 {
    my $text = shift;
    return MT::I18N::encode_text( $text, undef, 'utf-8' );
}

sub normalize {
    my $text = shift;
    require Unicode::Normalize;
    $text = Unicode::Normalize::NFKC( $text );
    return $text;
}

sub upload_callback {
    my ( $app, $blog, $asset, $id ) = @_;
    my $file = $asset->file_path;
    my @fstats = stat( $file );
    my $bytes = $fstats[7];
    my $url = $asset->url;
    $app->run_callbacks(
        'cms_upload_file.' . $asset->class,
        File  => $file,
        file  => $file,
        Url   => $url,
        url   => $url,
        Size  => $bytes,
        size  => $bytes,
        Asset => $asset,
        asset => $asset,
        Type  => $asset->class,
        type  => $asset->class,
        Blog  => $blog,
        blog  => $blog
    );
    if ( $asset->class eq 'image' ) {
        unless ( $id ) {
            my ( $w, $h );
            ( $w, $h, $id ) = imgsize( $file );
        }
        $app->run_callbacks(
            'cms_upload_image',
            File       => $file,
            file       => $file,
            Url        => $url,
            url        => $url,
            Size       => $bytes,
            size       => $bytes,
            Asset      => $asset,
            asset      => $asset,
            Height     => $asset->image_height,
            height     => $asset->image_height,
            Width      => $asset->image_width,
            width      => $asset->image_width,
            Type       => 'image',
            type       => 'image',
            ImageType  => $id,
            image_type => $id,
            Blog       => $blog,
            blog       => $blog
        );
    }
    return 1;
}

sub is_image { goto &if_image }

sub if_image {
    my $file = shift;
    my $basename = File::Basename::basename( $file );
    require MT::Asset;
    my $asset_pkg = MT::Asset->handler_for_file( $basename );
    if ( $asset_pkg eq 'MT::Asset::Image' ) {
        return 1;
    }
    return 0;
}

sub chomp_dir {
    my $dir = shift;
    my @path = File::Spec->splitdir( $dir );
    $dir = File::Spec->catdir( @path );
    return $dir;
}

sub add_slash {
    my ( $path, $os ) = @_;
    return $path if $path eq '/';
    if ( $path =~ m!^https?://! ) {
        $path =~ s{/*\z}{/};
        return $path;
    }
    $path = chomp_dir( $path );
    my $windows;
    if ( $os ) {
        if ( $os eq 'windows' ) {
            $windows = 1;
        }
    } else {
        if ( is_windows() ) {
            $windows = 1;
        }
    }
    if ( $windows ) {
        $path .= '\\';
    } else {
        $path .= '/';
    }
    return $path;
}

sub powercms_files_dir {
    my $powercms_files = powercms_files_dir_path();
    my $fmgr = MT::FileMgr->new( 'Local' ) or die MT::FileMgr->errstr;
    if (-d $powercms_files ) {
        if (-w $powercms_files ) {
            my $do = _create_powercms_subdir( $powercms_files );
            return chomp_dir( $powercms_files );
        }
        chmod( 0755, $powercms_files );
        my $do = _create_powercms_subdir( $powercms_files );
        return chomp_dir( $powercms_files ) if (-w $powercms_files );
    }
    $powercms_files =~ s!/$!! unless $powercms_files eq '/';
    unless ( $fmgr->exists( $powercms_files ) ) {
        $fmgr->mkpath( $powercms_files );
        if (-d $powercms_files ) {
            unless (-w $powercms_files ) {
                chmod( 0755, $powercms_files );
            }
        }
    }
    unless (-d $powercms_files ) {
        return undef;
    }
    if (-w $powercms_files ) {
        my $do = _create_powercms_subdir( $powercms_files );
        return chomp_dir( $powercms_files );
    }
    return undef;
}

sub powercms_files_dir_path {
    return MT->instance()->config( 'PowerCMSFilesDir' ) ||
        File::Spec->catdir( MT->instance()->mt_dir, 'powercms_files' );
}

sub _create_powercms_subdir {
    my $powercms_files = shift;
    if (-d $powercms_files ) {
        if (-w $powercms_files ) {
            my @dirs = qw( cache backup log mail lock report cmscache logrotate );
            for my $dir ( @dirs ) {
                my $directory = File::Spec->catdir( $powercms_files, $dir );
                unless (-e $directory ) {
                    if ( make_dir( $directory ) ) {
                        unless (-w $directory ) {
                            chmod( 0755, $directory );
                        }
                    }
                } else {
                    unless (-w $directory ) {
                        chmod( 0755, $directory );
                    }
                }
                return 0 unless (-w $directory );
            }
        }
    }
    return 1;
}

sub make_dir {
    my $path = shift;
    return 1 if (-d $path );
    my $fmgr = MT::FileMgr->new( 'Local' ) or return 0;# die MT::FileMgr->errstr;
    $path =~ s!/$!! unless $path eq '/';
    unless ( $fmgr->exists( $path ) ) {
        $fmgr->mkpath( $path );
        if (-d $path ) {
            # chmod ( 0755, $path );
            return 1;
        }
    }
    return 0;
}

sub _slurp {
    my ( $path ) = @_;
    require IO::File;
    my $fh = IO::File->new( $path, 'r' );
    local $/ unless wantarray;
    return <$fh>;
}

sub _abs_template_path {
    my ( $plugin, $path ) = @_;
    my $tmpl_path = File::Spec->canonpath( $path );
    unless ( File::Spec->file_name_is_absolute( $tmpl_path ) ) {
        if ( $plugin->can( 'path' ) ) {
            $tmpl_path = File::Spec->catdir( $plugin->path, 'tmpl', $tmpl_path );
        }
    }
    return $tmpl_path;
}

sub _check_template_name {
    my ( $app, $blog_id, $name ) = @_;
    unless ( MT::Template->exist( { name => $name, blog_id => $blog_id } ) ) {
        return $name; # OK, template object with same name doesn't exist
    }
    # see MT::CMS::Template::clone_templates
    my $new_basename = $app->translate( "Copy of [_1]", $name );
    my $new_name = $new_basename;
    my $i = 0;
    while ( MT::Template->exist( { name => $new_name, blog_id => $blog_id } ) ) {
        $new_name = $new_basename . ' (' . ++$i . ')';
    }
    return $new_name;
}

sub register_template {
    my ( $identifier, $path, $params ) = @_;
    # identifier is required
    return unless ( defined( $identifier ) && $identifier ne '' );
    $params = ref $params eq 'HASH' ? { %$params } : {}; # for safe
    my $terms = { identifier => $identifier };
    $terms->{ blog_id } = $params->{ blog_id } ? delete $params->{ blog_id } : 0;
    require MT::Template;
    my $tmpl = MT::Template->get_by_key( $terms );
    return if ( $tmpl->id ); # Do nothing if already exists
    # if $path is scalar, $path is for 'text' column
    # if $path is hash must have 'text' key
    my $path_info = ref $path ? $path : { text => $path };
    return unless ( ref $path_info eq 'HASH' && defined( $path_info->{ text } ) );
    # make values for MT::Template object
    my $plugin = $params->{ component }       ? delete $params->{ component } : MT->app;
    my $name   = defined( $params->{ name } ) ? delete $params->{ name }      : $identifier;
    $name = _check_template_name( $plugin, $terms->{ blog_id }, $name );
    my %values;
    $values{ type }       = $params->{ type }       ? delete $params->{ type }       : 'custom';
    $values{ rebuild_me } = $params->{ rebuild_me } ? delete $params->{ rebuild_me } : 0;
    $values{ name }       = $plugin->translate( $name );
    for my $col ( keys( %$params ) ) {
        if ( MT::Template->has_column( $col ) ) {
            $values{ $col } = $params->{ $col };
        }
    }
    for my $col ( keys( %$path_info ) ) {
        my $tmpl_path = _abs_template_path( $plugin, $path_info->{ $col } );
        next unless -f $tmpl_path; # cannot find template file
        $values{ $col } = $plugin->translate_templatized( scalar( _slurp( $tmpl_path ) ) )
            unless exists $values{ $col }; # dont override exists column like type
    }
    return unless exists $values{ text }; # at least needs 'text' column
    $tmpl->set_values( \%values );
    $tmpl->save
        or die $tmpl->errstr;
}

sub register_templates_to {
    my ( $blog_id, $component, $templates ) = @_;
    return unless ( ref $templates eq 'HASH' );
    $blog_id ||= 0;
    return unless $blog_id =~ m/^(?:0|[1-9]\d*)$/;
    my $ret = 1;
    for my $ident ( keys( %$templates ) ) {
        my $v = $templates->{ $ident };
        next unless ref $v eq 'HASH';
        $v = { %$v };
        my $path = delete $v->{ path };
        next unless $path;
        my %param = (
            blog_id => $blog_id,
            ( $component ? ( component => $component ) : () ),
            %$v,
        );
        $ret = 0 unless register_template( $ident, $path, \%param );
    }
    return $ret;
}

sub load_registered_template {
    my ( $identifier, $path, $params ) = @_;
    # identifier is required
    return unless ( defined( $identifier ) && $identifier ne '' );
    my $terms = { identifier => $identifier };
    $terms->{ blog_id } = $params->{ blog_id } ? $params->{ blog_id } : 0;
    my $path_info = ref $path ? $path : { text => $path };
    return unless ( ref $path_info eq 'HASH' && defined( $path_info->{ text } ) );
    require MT::Template;
    my $tmpl = MT::Template->load( $terms );
    if ( $tmpl ) {
        return $tmpl unless wantarray;
        my @ret;
        push @ret, $tmpl for keys %$path_info;
        return @ret;
    }
    # default
    my $plugin = $params->{ component } ? $params->{ component } : MT->app;
    if ( wantarray ) {
        my @ret;
        for my $col ( sort( keys( %$path_info ) ) ) {
            push( @ret, _abs_template_path( $plugin, $path_info->{ $col } ) );
        }
        return @ret;
    }
    return _abs_template_path( $plugin, $path_info->{ text } );
}

sub load_registered_template_for {
    my ( $blog_id, $component, $ident, $templates ) = @_;
    return unless ( ref $templates eq 'HASH' );
    $blog_id ||= 0;
    return unless $blog_id =~ m/^(?:0|[1-9]\d*)$/;
    my $v = $templates->{ $ident };
    return unless ref $v eq 'HASH';
    $v = { %$v };
    my $path = delete $v->{ path };
    return unless $path;
    my %param = (
        blog_id => $blog_id,
        ( $component ? ( component => $component ) : () ),
        %$v,
    );
    return load_registered_template( $ident, $path, \%param );
}

sub force_background_task {
    my $app = MT->instance();
    my $force = $app->config->FourceBackgroundTasks ||
                $app->config->ForceBackgroundTasks;
    if ( $force ) {
        my $default = $app->config->LaunchBackgroundTasks;
        $app->config( 'LaunchBackgroundTasks', 1 );
        my $res = MT::Util::start_background_task( @_ );
        $app->config( 'LaunchBackgroundTasks', $default );
        return $res;
    }
    return MT::Util::start_background_task( @_ );
}

sub get_weblogs {
    my $blog = shift;
    my @blogs;
    if ( MT->version_number < 5 ) {
        push( @blogs, $blog );
        return @blogs;
    }
    push( @blogs, $blog );
    if ( $blog->class eq 'website' ) {
        my $weblogs = $blog->blogs || [];
        push( @blogs, @$weblogs );
    }
    return @blogs;
}

sub get_blog_ids {
    my $blog = shift;
    my @blog_ids;
    if ( MT->version_number < 5 ) {
        push( @blog_ids, $blog->id );
        return @blog_ids;
    }
    push( @blog_ids, $blog->id );
    if ( $blog->class eq 'blog' ) {
        push( @blog_ids, $blog->parent_id ) if $blog->parent_id;
    }
    return @blog_ids;
}

sub listing_blog_ids {
    my $blog = shift;
    my $blog_ids;
    if ( $blog->class eq 'website' ) {
        $blog_ids = get_weblog_ids( $blog );
    } else {
        push( @$blog_ids, $blog->id );
    }
    return $blog_ids;
}

sub get_weblog_ids {
    my $website = shift;
    my $plugin = MT->component( 'PowerCMS' );
    my $app = MT->instance();
    if ( $website && ( $website->class eq 'blog' ) ) {
        $website = $website->website;
    }
    my $r = MT::Request->instance();
    my $blog_ids;
    my $cache;
    if ( $website ) {
        $blog_ids = $r->cache( 'powercms_get_weblog_ids_blog:' . $website->id );
        # $cache = $plugin->get_config_value( 'get_weblog_ids_cache', 'blog:'. $website->id );
        $cache = get_powercms_config( 'powercms', 'get_weblog_ids_cache', $website );
    } else {
        $blog_ids = $r->cache( 'powercms_get_weblog_ids_system' );
        # $cache = $plugin->get_config_value( 'get_weblog_ids_cache' );
        $cache = get_powercms_config( 'powercms', 'get_weblog_ids_cache' );
    }
    return $blog_ids if $blog_ids;
    if ( $cache ) {
        @$blog_ids = split( /,/, $cache );
        return $blog_ids;
    }
    my $weblogs;
    if (! $website ) {
        $weblogs = $r->cache( 'powercms_all_weblogs' );
        if (! $weblogs ) {
            @$weblogs = MT::Blog->load( { class => '*' } );
            $r->cache( 'powercms_all_weblogs', $weblogs );
        }
    } else {
        @$weblogs = get_weblogs( $website );
    }
    for my $blog ( @$weblogs ) {
        push( @$blog_ids, $blog->id );
    }
    if ( $website ) {
        $r->cache( 'powercms_get_weblog_ids_blog:' . $website->id, $blog_ids );
        # $plugin->set_config_value( 'get_weblog_ids_cache', join( ',', @$blog_ids ), 'blog:'. $website->id );
        set_powercms_config( 'powercms', 'get_weblog_ids_cache', join( ',', @$blog_ids ), $website );
    } else {
        $r->cache( 'powercms_get_weblog_ids_system', $blog_ids );
        # $plugin->set_config_value( 'get_weblog_ids_cache', join( ',', @$blog_ids ) );
        set_powercms_config( 'powercms', 'get_weblog_ids_cache', join( ',', @$blog_ids ) );
    }
#     if ( wantarray ) {
#         return @$blog_ids;
#     }
    return $blog_ids;
}

sub include_exclude_blogs {
    my ( $ctx, $args ) = @_;
    unless ( $args->{ blog_id } || $args->{ blog_ids } || $args->{ include_blogs } || $args->{ exclude_blogs } ) {
        $args->{ include_blogs } = $ctx->stash( 'include_blogs' );
        $args->{ exclude_blogs } = $ctx->stash( 'exclude_blogs' );
        $args->{ blog_ids } = $ctx->stash( 'blog_ids' );
    }
    my ( %blog_terms, %blog_args );
    $ctx->set_blog_load_context( $args, \%blog_terms, \%blog_args ) or return $ctx->error($ctx->errstr);
    my @blog_ids = $blog_terms{ blog_id };
    return if ! @blog_ids;
    if ( wantarray ) {
        return @blog_ids;
    } else {
        return \@blog_ids;
    }
}

sub include_blogs {
    my ( $blog, $include_blogs ) = @_;
    $include_blogs = '' unless $include_blogs;
    my @blog_ids;
    if ( $include_blogs eq 'all' ) {
        return undef;
    } elsif ( $include_blogs eq 'children' ) {
        my $children = $blog->blogs;
        push( @blog_ids, $blog->id );
        for my $child ( @$children ) {
            push( @blog_ids, $child->id );
        }
    } elsif ( $include_blogs eq 'siblings' ) {
        my $website = $blog->website;
        if ( $website ) {
            my $children = $website->blogs;
            my @blog_ids;
            push( @blog_ids, $website->id );
            for my $child ( @$children ) {
                push( @blog_ids, $child->id );
            }
        } else {
            push( @blog_ids, $blog->id );
        }
    } else {
        if ( $include_blogs ) {
            @blog_ids = split( /\s*,\s*/, $include_blogs );
            # push( @blog_ids, $blog->id );
        } else {
            if ( $blog->class eq 'website' ) {
                my @children = $blog->blogs;
                push( @blog_ids, $blog->id );
                for my $child( @children ) {
                    push( @blog_ids, $child->id );
                }
            }
        }
    }
    if ( wantarray ) {
        return @blog_ids;
    }
    return \@blog_ids;
}

sub flush_weblog_ids {
    my $website = shift;
    if ( $website ) {
        $website = $website->website if $website->is_blog;
        set_powercms_config( 'powercms', 'get_weblog_ids_cache', '', $website );
    } else {
        set_powercms_config( 'powercms', 'get_weblog_ids_cache', '' );
    }
}

sub get_blogs {
    my $blog = shift;
    my @blogs;
    if ( MT->version_number < 5 ) {
        push( @blogs, $blog );
        return @blogs;
    }
    push( @blogs, $blog );
    if ( $blog->class eq 'blog' ) {
        my $website = $blog->website;
        push( @blogs, $website ) if $website;
    }
    return @blogs;
}

sub get_all_blogs {
    require MT::Blog;
    my $blogs;
    my $r = MT::Request->instance;
    $blogs = $r->cache( 'powercms_get_all_blogs' );
    return $blogs if $blogs;
    @$blogs = MT::Blog->load( { class => '*' } );
    $r->cache( 'powercms_get_all_blogs', $blogs );
    return $blogs;
}

sub first_website {
    my $r = MT::Request->instance();
    my $website = $r->cache( 'powercms_first_website' );
    return $website if $website;
    $website = MT::Website->load( undef, { limit => 1 } );
    $r->cache( 'powercms_first_website', $website );
    return $website;
}

sub str2array {
    my ( $str, $separator, $remove_space ) = @_;
    return unless defined $str && length $str;
    $separator ||= ',';
    my @items = split( $separator, $str );
    if ( $remove_space ) {
        @items = map { $_ =~ s/\s+//g; $_ } @items;
    }
    if ( wantarray ) {
        return @items;
    }
    return \@items;
}

sub log2text {
    my ( $msg, $out ) = @_;
    open( my $fh, '>>', $out )
        or die "Can't open $out!";
    print $fh "$msg\n";
    close( $fh );
}

sub get_config_inheritance {
    my ( $plugin, $key, $blog ) = @_;
    my $get_from = 'system';
    if ( $blog ) {
        $get_from = 'blog:' . $blog->id;
    }
    my $plugin_data = $plugin->get_config_value( $key, $get_from );
    if ( (! $plugin_data ) && $blog ) {
        my $website;
        if ( MT->version_number < 5 || !$blog->is_blog ) {
            $get_from = 'system';
        } elsif ( $website = $blog->website ) {
            $get_from = 'blog:' . $website->id;
        } else {
            $website  = $blog;
            $get_from = 'blog:' . $blog->id;
        }
        $plugin_data = $plugin->get_config_value( $key, $get_from );
        if ( (! $plugin_data ) && $website ) {
            $get_from = 'system';
            $plugin_data = $plugin->get_config_value( $key, $get_from );
        }
    }
    return $plugin_data;
}

sub flush_blog_cmscache {
    # TODO::CMSCache Plugin is not Exists at 1st release.
    my $blog = shift;
    return unless $blog;
    if ( my $cmscache = MT->component( 'CMSCache' ) ) {
        require CMSCache::Plugin;
        return CMSCache::Plugin::__flush_blog_cache( $blog );
    }
}

sub plugin_template_path {
    my ( $component, $dirname ) = @_;
    return unless $component;
    $dirname ||= 'tmpl';
    return File::Spec->catdir( $component->path, $dirname );
}

sub get_asset_from_text {
    my ( $text, $blog ) = @_;
    require MT::Asset;
    my @assets;
    my $match = '<[^>]+\s(src|href|action)\s*=\s*\"';
    for my $url ( $text =~ m!$match(.{1,}?)"!g ) {
        if ( $url =~ m{^https?://} ) {
            my $file_path = path2relative( $url, $blog );
            my $asset = MT::Asset->load( { blog_id => $blog->id, class => '*',
                                           file_path => $file_path } );
            push( @assets, $asset ) if $asset;
        }
    }
    if ( wantarray ) {
        return @assets;
    }
    return \@assets;
}

sub convert2thumbnail {
    my ( $blog, $text, $type, $embed, $link,
         $dimension, $convert_gif_png ) = @_;
    my $site_url    = site_url( $blog );
    my $site_path   = site_path( $blog );
    my $re_site_path = $site_url =~ m{\A((?i:https?)://[^/]+)(.*)}s
                     ? qr{\A(?:\Q$1\E)?\Q$2\E/*}
                     : qr{\A\Q$site_url\E/*};
    my $test        = $text;
    $dimension = lc($dimension || 'width');
    $type      = lc($type || 'auto');
    require MT::Asset;
    #require File::Basename;
    for my $img ( $text =~ m/(<(?i:img)\s[^>]+>)/g ) {
        next unless $img =~ /\s(?i:src)\s*=\s*(["']?)([^"'\s]+)\1/;
        my $src  = $2;
        my $path = MT::Util::decode_url( $src );
        my $size;
        my $scope = $dimension;
        next unless $img =~ /\s(?i:width)\s*=\s*(["']?)([^"'\s]*)\1/;
        my $width = $2;
        next unless $img =~ /\s(?i:height)\s*=\s*(["']?)([^"'\s]*)\1/;
        my $height = $2;
        if ( $dimension eq 'auto' ) {
            if ( $width < $height ) {
                $scope = 'height';
            } else {
                $scope = 'width';
            }
        }
        if ( $scope eq 'height' ) {
            $size = $height;
        } else {
            $size = $width;
        }
        my $scope_tc = ucfirst( $scope );
        next if ( $embed >= $size );
        $path =~ s/$re_site_path//;
        $path = File::Spec->catfile( $site_path, $path );
        if ( -f $path ) {
            $path =~ s/\\\\/\\/g;
            $path =~ s!//!/!g;
            my $basename  = File::Basename::basename( $path );
            my $asset_pkg = MT::Asset->handler_for_file( $basename );
            if ( $asset_pkg eq 'MT::Asset::Image' ) {
                require MT::Asset::Image;
                $asset_pkg->isa( $asset_pkg );
                my $file_path = path2relative( $path, $blog );
                my $asset = $asset_pkg->load( { blog_id   => $blog->id,
                                                file_path => $file_path } );
                next unless defined $asset;
                my $orig = quotemeta( $img );
                my %param = ( $scope_tc => $embed, Path => undef, convert_gif_png => $convert_gif_png );
                my ( $thumb, $w, $h ) = create_thumbnail( $blog, $asset, %param );
                # my $thumb_new = _convert_gif_png( $thumb );
                my $url = path2url( $thumb, $blog );
                $img =~ s/(\ssrc\s*=\s*(["']?))[^"'\s]+\2/$1$url$2/;
                $img =~ s/(\swidth\s*=\s*(["']?))[^"'\s]*\2/$1$w$2/;
                $img =~ s/(\sheight\s*=\s*(["']?))[^"'\s]*\2/$1$h$2/;
                my $no_link = $img;
                if ( $link ) {
                    my $link_path;
                    if ( $link >= $size ) {
                        $link_path = $src;
                    } else {
                        my %link_param = ( $scope_tc => $link, Path => undef, convert_gif_png => $convert_gif_png );
                        my ( $link_thumb, $link_w, $link_h ) = create_thumbnail( $blog, $asset, %link_param );
                        # _convert_gif_png( $link_thumb );
                        $link_path = path2url( $link_thumb, $blog );
                    }
                    $img = '<a href="' . $link_path . '">' . $img . '</a>';
                }
                $test =~ s/$orig/$img/g;
                my $check = quotemeta( $img );
                for my $anchor ( $test =~ m{(<[Aa](?:\s[^>]*|)>(.*?)</[Aa]\s*>)}sg ) {
                    next unless $anchor =~ /$check/;
                    my $count = $anchor;
                    $count = $count =~ s/<[Aa][\s>]//g;
                    if ( $count > 1 ) {
                        $img = $no_link;
                        $anchor = quotemeta( $anchor );
                        $test =~ s/$anchor//;
                        last;
                    }
                }
                $text =~ s/$orig/$img/g;
            }
        }
    }
    return $text;
}

sub create_thumbnail {
    my ( $blog, $asset, %param ) = @_;
    my $app = MT->instance();
    my ( $thumb, $w, $h );
    my $orig_update = ( stat( $asset->file_path ) )[9];
    $thumb = File::Spec->catfile( $asset->_make_cache_path( $param{ Path } ),
                                  $asset->thumbnail_filename( %param ) );
    my $is_new; my $new_thumb;
    if (-f $thumb ) {
        my $thumb_update = ( stat( $thumb ) )[9];
        if ( $thumb_update < $orig_update ) {
            unlink $thumb;
            $is_new = 1;
            $new_thumb = convert_gif_png( $thumb );
            unlink $new_thumb if (-f $new_thumb );
        }
    } else {
        $is_new = 1;
    }
    ( $thumb, $w, $h ) = $asset->thumbnail_file( %param );
    if ( $is_new ) {
        my %params = ( file   => $thumb,
                       label  => $asset->label,
                       parent => $asset->id,
                      );
        my $asset = save_asset( $app, $blog, \%params );
        $new_thumb = convert_gif_png( $thumb );
        if (-f $new_thumb ) {
            $params{ file } = $new_thumb;
            save_asset( $app, $blog, \%params );
        }
    }
    return ( $thumb, $w, $h );
}

sub program_is_contained {
    my $code = shift;
    my $check;
    $check = quotemeta( '<?php' );
    return 1 if ( $code =~ /$check/i );
    $check = quotemeta( '<!--#exec' );
    return 1 if ( $code =~ /$check/i );
    for my $program ( $code =~ m/<\?(.{3})/isg ) {
        return 1 if ( $program ne 'xml' );
    }
    if ( $code =~ /<%/ || $code =~ /%>/ ) {
        return 1;
    }
    for my $program ( $code =~ m/<script(.*?)>/isg ) {
        if ( $program =~ /language\s*=\s*"php"/ ) {
            return 1;
        } elsif ( $program =~ /type\s*=\s*"text\/php"/ ) {
            return 1;
        }
    }
    return 0;
}

sub referral_site {
    my $app = MT->instance();
    my $referer = $app->get_header( 'REFERER' )
        or return '';
    if ( $referer =~ m{^((?i:https?)://[^/]+/)} ) {
        return $1;
    }
    return '';
}

sub referral_search_keyword {
    my $app = MT->instance();
    my $referer = $app->get_header( 'REFERER' );
    return undef unless $referer;
    $referer = decode_url( $referer );
    $referer = remove_html( $referer );
    $referer = to_utf8( $referer );
    my $query = '';
    if ( $referer =~ m{^((?i:https?)://[^/]+)/[^?]*\?(.*)$} ) {
        my $request = lc $1;
        my $param   = "&$2";
        if ( $request =~ /\.(?:bing|google|msn)\./ ) {
            if ( $param =~ /&q=([^&]*)/ ) {
                $query = $1;
            }
        } elsif ( $request =~ /\.yahoo\./ ) {
            if ( $param =~ /&p=([^&]*)/ ) {
                $query = $1;
            }
        } elsif ( $request =~ /\.goo\.ne\.jp$/ ) {
            if ( $param =~ /&MT=([^&]*)/ ) {
                $query = $1;
            }
        } elsif ( my $blog = $app->blog ) {
            my $site_url = site_url( $blog );
            my $search   = quotemeta( $request );
            if ( $site_url =~ /^$search/ ) {
                if ( $param =~ /&query=([^&]*)/ ) {
                    $query = $1;
                }
            }
        }
    }
    $query = trim( $query );
    return undef unless $query;
    if ( wantarray ) {
        my @keywords = split( /[\s+]+/, $query );
        return @keywords;
    }
    return $query;
}
sub referral_serch_keyword { goto &referral_search_keyword } # Backcompat

sub make_seo_basename {
    my ( $text, $length ) = @_;
    my $invalid = quotemeta( '\'"|*`^><)(}{][,/! ' );
    $text =~ s/[$invalid]/_/g;
    $text =~ s/^_*//;
    $text =~ s/_*$//;
    if ( $length ) {
        $text = substr( $text, 0, $length );
    }
    $text = encode_url( $text );
    return $text;
}

sub encode_utf8_string_to_cp932_octets {
    my ( $str ) = @_;
    $str = Encode::encode_utf8( $str );
    Encode::from_to( $str, 'utf8', 'cp932' );
    return $str;
}

sub permitted_blog_ids {
    my ( $app, $permissions ) = @_;
    my @permissions = ref $permissions eq 'ARRAY' ? @$permissions : $permissions;
    my @blog_ids;
    my $blog = $app->blog;
    if ( $blog ) {
        push( @blog_ids, $blog->id );
        unless ( $blog->is_blog ) {
            push( @blog_ids, map { $_->id } @{ $blog->blogs } );
        }
    }
    my $user = $app->user;
    if ( $user->is_superuser ) {
        unless ( @blog_ids ) {
            my @all_blogs = MT::Blog->load( { class => '*' } );
            @blog_ids = map { $_->id } @all_blogs;
        }
        if ( @blog_ids ) {
            @blog_ids = uniq_array( \@blog_ids );
            return wantarray ? @blog_ids : \@blog_ids;
        }
    }
    #require MT::Permission;
    my $iter = MT->model( 'permission' )->load_iter( { author_id => $user->id,
                                                       ( @blog_ids ? ( blog_id => \@blog_ids ) : ( blog_id => { not => 0 } ) ),
                                                     }
                                                   );
    my @permitted_blog_ids;
    while ( my $p = $iter->() ) {
        for my $permission ( @permissions ) {
            next unless $p->blog;
            if ( is_user_can( $p->blog, $user, $permission ) ) {
                push( @permitted_blog_ids, $p->blog->id );
                last;
            }
        }
    }
    if ( @permitted_blog_ids ) {
        @permitted_blog_ids = uniq_array( \@permitted_blog_ids );
        return wantarray ? @permitted_blog_ids : \@permitted_blog_ids;
    }
    return;
}

sub powercms_config_param {
    my ( $cb, $app, $param, $tmpl ) = @_;
    my $plugin = MT->component( 'PowerCMS' );
    #require File::Spec;
    my $powercms_config_templates = MT->registry( 'powercms_config_template' );
    my @templates = keys( %$powercms_config_templates );
    my %init_configs;
    for my $key ( @templates ) {
        $init_configs{ $key } = $powercms_config_templates->{ $key }->{ order };
    }
    my @configs;
    foreach my $key ( sort { $init_configs{ $b } <=> $init_configs{ $a } } keys %init_configs ) {
        push( @configs, $powercms_config_templates->{ $key } );
    }
    my $blog = $app->blog;
    my $scope = 'system';
    if ( $blog ) {
        if ( $blog->is_blog ) {
            $scope = 'blog';
        } else {
            $scope = 'website';
        }
    }
    my $powercms_settings = MT->registry( 'powercms_settings' );
    my $template = File::Spec->catfile( plugin_template_path( $plugin, 'tmpl' ), 'powercms_config_plugin.tmpl' );
    $template = read_from_file( $template );
    my $pointer_field = $tmpl->getElementById( 'config_loop' );
    my $return_args = '__mode=powercms_config';
    if ( $blog ) {
        $return_args .= '&blog_id=' . $blog->id;
    }
    my $count = 0;
    for my $cfg ( @configs ) {
        my $nodeset = $tmpl->createElement( 'for' );
        my $component_key = $cfg->{ component };
        my $component = MT->component( $component_key );
        my $tmpl_path = File::Spec->catfile( plugin_template_path( $component, 'tmpl' ), $cfg->{ $scope } );
        my $innerHTML = $template;
        my $inner = read_from_file( $tmpl_path );
        $component_key = lc( $component_key );
        my %tmpl_args = ( blog => $app->blog );
        my %tmpl_params = ( plugin_key => $component_key, plugin_tmpl => $inner,
                            script_url => $app->uri,
                            return_args => $return_args );
        my $settings = $powercms_settings->{ $component_key };
        for my $key ( keys %$settings ) {
            $tmpl_params{ $key } = get_powercms_config( $component_key, $key, $blog );
        }
        if ( $blog ) {
            $tmpl_params{ blog_id } = $blog->id;
        }
        $innerHTML = build_tmpl( $app, $innerHTML, \%tmpl_args, \%tmpl_params );
        $innerHTML .= '<mt:include name="include/actions_bar.tmpl" bar_position="bottom" hide_pager="1"></fieldset></form>';
        $nodeset->innerHTML( $innerHTML );
        $tmpl->insertAfter( $nodeset, $pointer_field );
        $count++;
    }
    if (! $count ) {
        $param->{ no_config } = 1;
    }
}

sub powercms_config {
    my $app = shift;
    my $blog_id = $app->param( 'blog_id' );
    return $app->trans_error( 'Permission denied.' ) if ! $app->user->is_superuser && ! $blog_id;
    return $app->trans_error( 'Permission denied.' ) if ! $app->can_do( 'administer_blog' );
    my $plugin = MT->component( 'PowerCMS' );
    $app->{ plugin_template_path } = plugin_template_path( $plugin );
    my $tmpl = 'powercms_config.tmpl';
    my %param;
    $param{ saved } = $app->param( 'saved' );
    return $app->build_page( $tmpl, \%param );
}

sub save_powercms_config {
    my $app = shift;
    my $blog_id = $app->param( 'blog_id' );
    my $blog = $app->blog;
    return $app->trans_error( 'Permission denied.' ) if ! $app->user->is_superuser && ! $blog_id;
    return $app->trans_error( 'Permission denied.' ) if ! $app->can_do( 'administer_blog' );
    $app->validate_magic or return $app->trans_error( 'Permission denied.' );
    my $action = $app->param( 'action' );
    my $plugin_key = $app->param( 'plugin_key' );
    if ( $action && ( $action eq 'reset' ) ) {
        __reset_config( $plugin_key, $blog );
        $app->add_return_arg( reset => 1 );
        $app->call_return;
    } else {
        my $powercms_settings = MT->registry( 'powercms_settings' );
        my $plugin_settings = $powercms_settings->{ $plugin_key };
        my @settings = keys( %$plugin_settings );
        my $configs;
        for my $setting ( @settings ) {
            $configs->{ $setting } = $app->param( $setting );
        }
        __save_config( $plugin_key, $configs, $blog );
        $app->add_return_arg( saved => 1 );
        $app->call_return;
    }
}

sub __reset_config {
    my ( $plugin_key, $blog ) = @_;
    return __save_config( $plugin_key, undef, $blog );
}

sub set_powercms_config_values {
    return __save_config( @_ );
}

sub reset_powercms_config_values {
    return __reset_config( @_ );
}

sub __save_config {
    my ( $plugin_key, $configs, $blog ) = @_;
    if ( $blog ) {
        if (! ref $blog ) {
            if ( $blog =~ /^[1-9]\d*$/ ) {
                require MT::Blog;
                $blog = MT::Blog->load( $blog );
                return unless $blog;
            }
        }
    }
    my $powercms_config = read_powercms_config( $blog );
    if ( $configs ) {
        $powercms_config->{ $plugin_key } = $configs;
    } else {
        delete( $powercms_config->{ $plugin_key } );
    }
    require MT::Serialize;
    my $ser = MT::Serialize->serialize( \$powercms_config );
    if (! $blog ) {
#         my $cfg_class = MT->model( 'config' ) or return;
#         my $config = $cfg_class->load() || $cfg_class->new;
#         $config->powercms_config( $ser );
        my $cfg_class = MT->model( 'powercmsconfig' ) or return;
        my $config = $cfg_class->load() || $cfg_class->new;
        $config->data( $ser );
        $config->save or die $config->errstr;
    } else {
        $blog->powercms_config( $ser );
        $blog->save or die $blog->errstr;
    }
    return 1;
}

sub read_powercms_config {
    my $blog = shift;
    if ( $blog ) {
        if (! ref $blog ) {
            if ( $blog =~ /^[1-9]\d*$/ ) {
                require MT::Blog;
                $blog = MT::Blog->load( $blog );
                return () unless $blog;
            }
        }
    }
    #require MT::Request;
    my $r = MT::Request->instance;
    my $data;
    my $params = ();
    if (! $blog ) {
        my $cfg_class = MT->model( 'config' ) or return;
        if ( $r->cache( 'PowerCMSConfig' ) ) {
            return $r->cache( 'PowerCMSConfig' );
        } else {
#             my $cfg_class = MT->model( 'config' ) or return;
#             my $config = $cfg_class->load() || return;
#             $data = $config->powercms_config;
            my $cfg_class = MT->model( 'powercmsconfig' ) or return;
            my $config = $cfg_class->load() || return;
            $data = $config->data;
            require MT::Serialize;
            $data = MT::Serialize->unserialize( $data );
            $params = $$data;
            $r->cache( 'PowerCMSConfig', $params );
        }
    } else {
        if ( $r->cache( 'PowerCMSConfig:' . $blog->id ) ) {
            return $r->cache( 'PowerCMSConfig:' . $blog->id );
        } else {
            $data = $blog->powercms_config;
            require MT::Serialize;
            $data = MT::Serialize->unserialize( $data );
            $params = $$data;
            $r->cache( 'PowerCMSConfig:' . $blog->id, $params );
        }
    }
    return $params;
}

sub set_powercms_config {
    my ( $plugin_key, $key, $value, $blog ) = @_;
    if ( $blog && ( $blog ne 'system' ) ) {
        if (! ref $blog ) {
            if ( $blog =~ /^[1-9]\d*$/ ) {
                require MT::Blog;
                $blog = MT::Blog->load( $blog );
                return unless $blog;
            }
        }
    }
    my $powercms_config = read_powercms_config( $blog );
    my $configs;
    if ( $powercms_config ) {
        $configs = $powercms_config->{ $plugin_key };
    }
    $configs->{ $key } = $value;
    return __save_config( $plugin_key, $configs, $blog );
}

sub get_powercms_config {
    my ( $plugin_key, $key, $blog ) = @_;
    if ( $blog ) {
        if (! ref $blog ) {
            if ( $blog =~ /^[1-9]\d*$/ ) {
                require MT::Blog;
                $blog = MT::Blog->load( $blog );
                return unless $blog;
            }
        }
    }
    #require MT::Request;
    my $r = MT::Request->instance;
    my $params = ();
    if (! $blog ) {
        my $cfg_class = MT->model( 'config' ) or return;
        if ( $r->cache( 'PowerCMSConfig' ) ) {
            $params = $r->cache( 'PowerCMSConfig' );
        } else {
#             my $cfg_class = MT->model( 'config' ) or return;
#             my $config = $cfg_class->load() || return;
#             my $data = $config->powercms_config;
            if ( $key eq 'get_weblog_ids_cache' ) { # FIXME: adhoc...
                $params = {};
            } else {
                if ( $r->cache( 'NoPowerCMSConfig' ) ) {
                    return get_default( $plugin_key, $key );
                }
                my $cfg_class = MT->model( 'powercmsconfig' ) or return;
                my $config = $cfg_class->load();
                if (! $config ) {
                    $r->cache( 'NoPowerCMSConfig', 1 );
                    return get_default( $plugin_key, $key ); # not set yet.
                }
                my $data = $config->data;
                require MT::Serialize;
                $data = MT::Serialize->unserialize( $data );
                $params = $$data;
                $r->cache( 'PowerCMSConfig', $params );
            }
        }
    } else {
        if ( $r->cache( 'PowerCMSConfig:' . $blog->id ) ) {
            $params = $r->cache( 'PowerCMSConfig:' . $blog->id );
        } else {
            if ( $key eq 'get_weblog_ids_cache' ) { # FIXME: adhoc...
                $params = {};
            } else {
                my $data = $blog->powercms_config;
                require MT::Serialize;
                $data = MT::Serialize->unserialize( $data );
                $params = $$data;
                $r->cache( 'PowerCMSConfig:' . $blog->id, $params );
            }
        }
    }
    my $settings = $params->{ $plugin_key };
    if (! $settings ) {
        return get_default( $plugin_key, $key );
    }
    my $value = defined( $settings->{ $key } ) ? $settings->{ $key } : '';
    if ( defined $value && $value eq '' ) {
        return get_default( $plugin_key, $key );
    }
    return $value;
}

sub get_default {
    my ( $plugin_key, $key ) = @_;
    my $powercms_settings = MT->registry( 'powercms_settings' );
    if ( $powercms_settings->{ $plugin_key } ) {
        if ( $powercms_settings->{ $plugin_key }->{ $key } ) {
            return $powercms_settings->{ $plugin_key }->{ $key }->{ default };
        }
    }
}

sub charset_is_utf8 {
    my $charset = lc(MT->config->PublishCharset);
    if ( $charset =~ /^utf-?8$/ ) {
        return 1;
    }
    return 0;
}

sub can_edit_entry {
    my ( $entry, $author ) = @_;
    my $can_edit_entry = 1;
    my $permission_checkers = MT->registry( 'permission_checker' );
    if ( $permission_checkers ) {
        my $checkers = $permission_checkers->{ edit_entry };
        my @checkers = ref $checkers eq 'ARRAY' ? @$checkers : $checkers;
        for my $checker ( @checkers ) {
            for my $key ( keys %$checker ) {
                next if $key eq 'plugin';
                my $code = MT->handler_to_coderef( $permission_checkers->{ edit_entry }->{ $key } );
                unless ( $code->( $entry, $author ) ) {
                    $can_edit_entry = 0;
                }
            }
        }
    }
    return $can_edit_entry;
}

sub allow_upload {
    my $file = shift;
    my $app = MT->instance;
    if ( my $deny_exts = $app->config->DeniedAssetFileExtensions ) {
        my @deny_exts = map {
            if   ( $_ =~ m/^\./ ) {qr/$_/i}
            else                  {qr/\.$_/i}
        } split '\s?,\s?', $deny_exts;
        my @ret = File::Basename::fileparse( $file, @deny_exts );
        if ( $ret[2] ) {
            return 0;
        }
    }
    if ( my $allow_exts = $app->config( 'AssetFileExtensions' ) ) {
        my @allow_exts = map {
            if   ( $_ =~ m/^\./ ) {qr/$_/i}
            else                  {qr/\.$_/i}
        } split '\s?,\s?', $allow_exts;
        my @ret = File::Basename::fileparse( $file, @allow_exts );
        unless ( $ret[2] ) {
            return 0;
        }
    }
    if ( -f $file && is_image( $file ) ) {
        open( my $fh, '<', $file );
        require MT::Image;
        unless ( MT::Image::is_valid_image( $fh ) ) {
            close( $fh ); # FIXME
            return 0;
        }
        close( $fh );
    }
    return 1;
}

sub error_log {
    my ( $message, $blog_id ) = @_;
    my $log = MT->model( 'log' )->new;
    $log->message( $message );
    $log->level( MT::Log::ERROR() );
    $log->blog_id( $blog_id ? ( blog_id => $blog_id ) : () );
    $log->class( 'system' );
    $log->category( 'powercms' );
    $log->save or die $log->errstr;
    return 1;
}

sub decode_mime_header {
    my ( $str ) = @_;
    if ( $str ) {
        if ( $str =~ /name="([^"]+)"/ ) {
            $str = $1;
        }
        return decode( 'MIME-Header', $str );
    }
}

sub encode_mime_header {
    my ( $str ) = @_;
    if ( $str ) {
        return encode( 'MIME-Header', $str );
    }
}

sub is_valid_extention { goto &is_valid_extension } # Backcompat
sub is_valid_extension {
    my ( $path ) = @_;
    return 0 unless $path && -f $path;
    eval { require File::Extension::Validate } || return 0;
    my $file_extension = file_extension($path); # Returns lower case.
    my $suggest;
    if ( $file_extension =~ /^(?:doc|xls|ppt)x$/ ) {
        $suggest = lc get_ole_extension( $path );
        if ( $file_extension eq $suggest ) {
            return 1;
        }
    } elsif ( File::Extension::Validate->validate( $path ) ) {
        if ( $file_extension !~ /^(?:doc|xls|ppt)x?$/ ) {
            return 1;
        }
        $suggest = lc get_ole_extension( $path );
        if ( $file_extension eq $suggest ||
             $file_extension eq "${suggest}x" ) {
            return 1;
        }
    } elsif ( $file_extension eq 'txt' && -T $path ) {
        return 1;
    } else {
        $suggest = File::Extension::Validate->suggest( $path );
    }
    return wantarray ? ( 0, $suggest ) : 0;
}

sub get_ole_extension {
    my ( $path ) = @_;
    return 0 unless $path;
    return 0 unless -f $path;
    my $file_extension = file_extension( $path );
    if ( $file_extension =~ /^(?:doc|xls|ppt)x$/ ) {
        return _get_ole_extension_x( $path );
    }
    eval { require OLE::Storage_Lite } || return 0;
    my $oOl = OLE::Storage_Lite->new( $path );
    my $oPps = $oOl->getPpsTree();
    return _get_ole_extension( $oPps );
}

sub _get_ole_extension_x {
    my ( $path ) = @_;
    my $fmgr = MT::FileMgr->new( 'Local' ) or die MT::FileMgr->errstr;
    my $renamed = $path;
    my $tempdir;
    if ( $renamed =~ /^(.*)\..*?$/ ) {
        $renamed = "$1.zip";
        $tempdir = $1;
        make_dir( $tempdir );
        unless ( -w $tempdir ) {
            chmod( 0755, $tempdir );
        }
    }
    if ( copy_item( $path, $renamed ) ) {
        eval { require Archive::Zip } || return undef;
        my $zip = Archive::Zip->new( $renamed );
        my @members = $zip->members();
        foreach ( @members ) {
            my $name = $_->fileName();
            if ( $name =~ /\[Content_Types\]\.xml/i ) {
                my $xml_file_path = File::Spec->catfile( $tempdir, $name );
                $zip->extractMemberWithoutPaths( $name, $xml_file_path );
                my $content = read_from_file( $xml_file_path );
                if ( $content =~ m{.*?<Override PartName="/([^/]+)/.*} ) {
                    my $t = $1;
                    my %exts_set = (
                        'word' => 'docx',
                        'xl'   => 'xlsx',
                        'ppt'  => 'pptx',
                    );
                    $fmgr->delete( $renamed );
                    remove_item( $tempdir );
                    return $exts_set{ $t };
                }
            }
        }
    }
}

sub _get_ole_extension {
    my ( $oPps ) = @_;
    eval { require OLE::Storage_Lite } || return;
    my $sName = OLE::Storage_Lite::Ucs2Asc( $oPps->{ Name } );
    $sName =~ s/\W/ /g;
    my $res;
    if ( $sName eq 'WordDocument' ) {
        $res = 'doc';
    } elsif ( $sName eq 'Workbook' || $sName eq 'Book' ) {
        $res = 'xls';
    } elsif ( $sName eq 'PowerPoint Document' ) {
        $res = 'ppt';
    } elsif ( $sName eq 'VisioDocument' ) {
        $res = 'vnd';
    } else {
        for my $iItem ( @{ $oPps->{ Child } } ) {
            if ( $res = _get_ole_extension( $iItem ) ) {
                last;
            }
        }
    }
    return $res;
}

sub is_oracle {
    return lc( MT->config( 'ObjectDriver' ) ) =~ /oracle/ ? 1 : 0;
}

sub trimj_to {
    my ( $text, $trim_witdth, $ellipsis ) = @_;
    if (! $ellipsis ) {
        $ellipsis = '';
    }
    if (! $text ) {
        return $ellipsis;
    }
    $trim_witdth = $trim_witdth * 2;
    my @strs = split( //, $text );
    my $length = 0;
    my $out = '';
    for my $str ( @strs ) {
        $out .= $str;
        if ( bytes::length( $str ) > 1 ) {
            $length += 2;
        } else {
            $length += 1;
        }
        if ( $length >= $trim_witdth ) {
            last;
        }
    }
    if ( $out ne $text ) {
        $out .= $ellipsis;
    }
    return $out;
}

sub is_psgi {
    return MT->config->PIDFilePath ? 1 : 0;
}

sub is_fastcgi {
    return $ENV{FAST_CGI} ? 1 : 0;
}

sub is_powered_cgi {
    return ( is_psgi() || is_fastcgi() ) ? 1 : 0;
}

sub get_superuser {
    my ( %terms, %args );
    $terms{ type } = MT::Author::AUTHOR();
    $terms{ status } = MT::Author::ACTIVE();
    $args{ 'sort' } = 'id';
    $args{ direction } = 'ascend';
    $args{ limit } = 1;
    $args{ 'join' } = MT->model( 'permission' )->join_on( 'author_id',
                                                          { blog_id => 0,
                                                            permissions => { like => "\%'administer\%" },
                                                          }, {
                                                            unique => 1,
                                                          },
                                                        );
    
    return MT->model( 'author' )->load( \%terms, \%args );
}

1;