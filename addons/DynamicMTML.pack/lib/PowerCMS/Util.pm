# DynamicMTML (C) 2010-2011 Alfasado Inc.
# This program is distributed under the terms of the
# GNU General Public License, version 2.

package PowerCMS::Util;
use strict;
use Exporter;
our $powercms_util_version = '2.051';
@PowerCMS::Util::ISA = qw( Exporter );
use vars qw( @EXPORT_OK );
@EXPORT_OK = qw( build_tmpl save_asset upload convert_gif_png association_link create_entry
                 make_entry write2file read_from_file move_file copy_item remove_item
                 relative2path path2relative path2url relative2url url2path
                 site_path site_url static_or_support support_dir archive_path
                 current_ts next_date prev_date valid_ts month2int send_mail get_mail
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
                 convert2thumbnail create_thumbnail program_is_contained
                 referral_site referral_search_keyword referral_serch_keyword make_seo_basename
                 encode_utf8_string_to_cp932_octets
               );

use MT::Util qw( epoch2ts ts2epoch offset_time_list format_ts encode_url decode_url
                 perl_sha1_digest_hex is_valid_email remove_html trim );

use MT::Log;
use MT::FileMgr;
use File::Basename;
use File::Spec;
use Image::Size qw( imgsize );
use File::Temp qw( tempdir );
use Encode;
use Encode qw( encode decode );
use File::Copy::Recursive qw( rcopy );
use MT::Request;
use MT::Permission;

sub powercms_util_version {
    return $powercms_util_version;
}

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
    return '' unless( $asset_class && exists( $icon_class_of{ $asset_class } ) );
    my $name = icon_name( $ext );
    return "ic-${asset_class}-$name";
}

sub build_tmpl {
    my ( $app, $tmpl, $args, $params ) = @_;
#     my %args = ( blog => $blog,
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
    require MT::Template::Context;
    my $ctx = MT::Template::Context->new;
    my $blog = $args->{ blog };
    my $entry = $args->{ entry };
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
    $ctx->stash( 'entry', $entry );
    $ctx->stash( 'page', $entry );
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
    if ( is_application( $app ) ) {
        $ctx->{ __stash }->{ vars }->{ magic_token } = $app->current_magic if $app->user;
    }
    my $build = MT::Builder->new;
    my $tokens = $build->compile( $ctx, $tmpl )
        or return $app->error( $app->translate(
            "Parse error: [_1]", $build->errstr ) );
    defined( my $html = $build->build( $ctx, $tokens ) )
        or return $app->error( $app->translate(
            "Build error: [_1]", $build->errstr ) );
    unless ( MT->version_number < 5 ) {
        $html = utf8_on( $html );
    }
    return $html;
}

sub save_asset {
    my ( $app, $blog, $params, $cb ) = @_;
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
    my $file = $file_path;
    my $author = $params->{ author };
    my $parent = $params->{ parent };
    $parent = $params->{ parant } unless $parent; # compatible
    $author = current_user( $app ) unless ( defined $author );
    my $label = $params->{ label };
    my $description = $params->{ description };
    my $obj = $params->{ object };
    my $tags = $params->{ tags };
    my $basename = File::Basename::basename( $file_path );
    my $file_ext = file_extension( $file_path );
    my $mime_type = mime_type( $file_path );
    my $class = 'file'; my $is_image;
    require MT::Asset;
    my $asset_pkg = MT::Asset->handler_for_file( $basename );
    my $asset;
    if ( $asset_pkg eq 'MT::Asset::Image' ) {
        $asset_pkg->isa( $asset_pkg );
        $class = 'image';
        $is_image = 1;
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
    $url =~ s!\\!/!g if if_windows();
    $url = path2url( $url, $blog, 1 );
    $url = path2relative( $url, $blog, 1 );
    $file_path = path2relative( $file_path, $blog, 1 );
    $asset = $asset_pkg->load( { blog_id => $blog_id,
                                 file_path => $file_path } );
    my $original;
    unless ( $asset ) {
        $asset = $asset_pkg->new();
    } else {
        $original = $asset->clone();
    }
    $original = $asset->clone();
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
        require Image::Size;
        ( $w, $h, $id ) = Image::Size::imgsize( $file );
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
    if ( $cb ) {
        $app->run_callbacks( 'cms_pre_save.asset', $app, $asset, $original )
          || return $app->errtrans( "Saving [_1] failed: [_2]", 'asset',
            $app->errstr );
    }
    $asset->set_tags( @$tags );
    $asset->save or die $asset->errstr;
    if ( $cb ) {
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
    my $res = upload_callback( $app, $blog, $asset, $id ) if $cb;
    return $asset;
}

sub upload {
    my ( $app, $blog, $name, $dir, $params ) = @_;
    my $limit = $app->config( 'CGIMaxUpload' ) || 20480000;
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
    my $obj = $params->{ object };
    my $rename = $params->{ 'rename' };
    my $label = $params->{ label };
    my $format_LF = $params->{ format_LF };
    my $singler = $params->{ singler };
    my $no_asset = $params->{ no_asset };
    my $description = $params->{ description };
    my $force_decode_filename = $params->{ force_decode_filename };
    my $fmgr = MT::FileMgr->new( 'Local' ) or die MT::FileMgr->errstr;
    my $q = $app->param;
    my @files = $q->upload( $name );
    my @assets;
    my $upload_total;
    for my $file ( @files ) {
        my $size = ( -s $file );
        $upload_total = $upload_total + $size;
        if ( $limit < $upload_total ) {
            return ( undef, 1 ); # Upload file size over CGIMaxUpload;
        }
    }
    for my $file ( @files ) {
        my $orig_filename = file_basename( $file );
        $orig_filename = decode_url( $orig_filename ) if $force_decode_filename;
        my $file_label = file_label( $orig_filename );
        $orig_filename = set_upload_filename( $orig_filename );
        my $out = File::Spec->catfile( $dir, $orig_filename );
        if ( $rename ) {
            $out = uniq_filename( $out );
        }
        $dir =~ s!/$!! unless $dir eq '/';
        unless ( $fmgr->exists( $dir ) ) {
            $fmgr->mkpath( $dir ) or return MT->trans_error( "Error making path '[_1]': [_2]",
                                    $out, $fmgr->errstr );
        }
        my $temp = "$out.new";
        open ( my $fh, ">$out" ) or die "Can't open $out!";
        binmode ( $fh );
        while( read ( $file, my $buffer, 1024 ) ) {
            $buffer = format_LF( $buffer ) if $format_LF;
            print $fh $buffer;
        }
        close ( $fh );
        $fmgr->rename( $temp, $out );
        my $user = $params->{ author };
        $user = current_user( $app ) unless defined $user;
        if ( $no_asset ) {
            if ( $singler ) {
                return $out;
            }
            push ( @assets, $out );
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
                push ( @assets, $asset ) if defined $asset;
            }
        }
    }
    return \@assets;
}

sub convert_gif_png {
    my $image = shift;
    # TODO::Save Asset
    my $new_file = $image;
    if ( file_extension( $image ) eq 'gif' ) {
        $new_file =~ s/\.gif$/.png/i;
    } elsif ( file_extension( $image ) eq 'png' ) {
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
    require MT::Association;
    my $assoc = MT::Association->link( $author => $role => $blog );
    if ( $assoc ) {
        my $log = MT::Log->new;
        my $msg = { message => $app->translate(
                    '[_1] registered to the blog \'[_2]\'',
                    $author->name,
                    $blog->name
                ),
                level    => MT::Log::INFO(),
                class    => 'author',
                category => 'new',
                blog_id => $blog->id,
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
    return undef;
}

sub create_entry {
    my ( $app, $blog, $args, $params, $cb ) = @_;
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
#     my $entry = create_entry( $app, $blog, \%args, \%params[, $cb] );
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
    $entry->blog_id ( $blog->id );
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
    if ( $cb ) {
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
        push ( @saved_cats, $args->{ category_id } );
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
            push ( @saved_cats, $category->id );
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
    if ( $cb ) {
        $app->run_callbacks( 'cms_post_save.' . $entry->class, $app, $entry, $original );
    }
    if ( $params->{ rebuildme } ) {
        my $dependencies = $params->{ dependencies };
        if ( $entry->status == MT::Entry::RELEASE() ) {
            if ( $params->{ background } ) {
                force_background_task(
                    sub { $app->rebuild_entry( Entry => $entry->id,
                                               BuildDependencies => $dependencies );
                    }
                );
            } else {
                MT::Util::start_background_task(
                    sub { $app->rebuild_entry( Entry => $entry->id,
                                               BuildDependencies => $dependencies );
                    }
                );
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
    if ( File::Copy::Recursive::rcopy ( $from, $to ) ) {
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
    my $static_file_path = quotemeta( static_or_support() );
    my $archive_path = quotemeta( archive_path( $blog ) );
    my $site_path = quotemeta( site_path( $blog, $exclude_archive_path ) );
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
    my $site_path = quotemeta ( site_path( $blog, $exclude_archive_path ) );
    my $site_url = site_url( $blog );
    $path =~ s/^$site_path/$site_url/;
    if ( is_windows() ) {
        $path =~ s!/!\\!g;
    }
    return $path;
}

sub relative2url {
    my ( $path, $blog ) = @_;
    return path2url( relative2path( $path,$blog ), $blog );
}

sub url2path {
    my ( $url, $blog ) = @_;
    my $site_url = quotemeta ( site_url( $blog ) );
    my $site_path = site_path( $blog );
    $url =~ s/^$site_url/$site_path/;
    if ( is_windows() ) {
        $url =~ s!/!\\!g;
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
    shift =~ /\A(?:0\d{1,4}-?\d{1,4}-?\d{3,5}|\+[1-9][-\d]+\d)\z/ ? 1 : 0; # TODO
}

sub valid_postal_code { # TODO: L10N
    shift =~ /\A[0-9]{3}-?[0-9]{4}\z/ ? 1 : 0;
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
    my $attaches  = $args->{ Attaches };
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
    return unless defined( $subject );
    return unless defined( $body );
    return unless ( $from && $to && $subject ne '' && $body ne '' );
    $params = { key => 'default' } unless defined $params;
    $params->{ key } = 'default' unless defined $params->{ key };
    my $app = MT->instance();
    my $mgr = MT->config;
    my $enc = $mgr->PublishCharset;
    my $mail_enc = lc ( $mgr->MailEncoding || $enc );
    $body = MT::I18N::encode_text( $body, $enc, $mail_enc );
    return unless
        $app->run_callbacks( ( ref $app ) . '::pre_send_mail', $app, \$args, \$params );
    $from = $args->{ from },
    $to = $args->{ to },
    $subject = $args->{ subject },
    $body = $args->{ body },
    $cc = $args->{ cc },
    $bcc = $args->{ bcc },
    my %head;
    %head = (
        To => $to,
        From => $from,
        Subject => $subject,
        ( ref $cc eq 'ARRAY' ? ( Cc => $cc ) : () ),
        ( ref $bcc eq 'ARRAY' ? ( Bcc => $bcc ) : () ),
        ( $content_type ? ( 'Content-Type' => $content_type ) : () ),
    );
    require MT::Mail;
    if ( is_application( $app ) ) {
        force_background_task(
           sub { MT::Mail->send( \%head, $body )
                or return ( 0, "The error occurred.", MT::Mail->errstr ); } );
    } else {
        MT::Mail->send( \%head, $body )
                or return ( 0, "The error occurred.", MT::Mail->errstr );
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
    for my $id ( sort ( keys %$messages ) ) {
        mkdir( $tempdir, 0777 ) unless ( -d $tempdir );
        my $message = $pop3->get( $id );
        my $parser = new MIME::Parser;
        my $workdir = tempdir ( DIR => $tempdir );
        $parser->output_dir( $workdir );
        my $entity = $parser->parse_data( $message );
        my $header = $entity->head;
        my $from = $header->get( 'From' );
        my $to = $header->get( 'to' );
        my $subject = $header->get( 'Subject' );
        $subject = encode( $charset, decode( 'MIME-Header', $subject ) );
        unless ( MT->version_number < 5 ) {
            $subject = utf8_on( $subject );
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
                        $body .= read_from_file( File::Spec->catfile ( $workdir, $file ) );
                    } else {
                        push ( @f, File::Spec->catfile ( $workdir, $file ) );
                    }
                }
            }
        } else {
            $body = $entity->bodyhandle;
            $body = $body->as_string;
        }
        $body = encode( $charset, decode( 'iso-2022-jp', $body ) );
        unless ( MT->version_number < 5 ) {
            $body = utf8_on( $body );
        }
        my $mail = { from => $from,
                     subject => $subject,
                     body    => $body,
                     files   => \@f,
                     directory => $workdir };
        push( @emails, $mail );
        $pop3->delete( $id ) if $delete;
    }
    $pop3->quit;
    return \@emails;
}

sub make_zip_archive {
    my ( $directory, $out, $files ) = @_;
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
        $archiver->addFile( $directory, $basename );
        return $archiver->writeToFileNamed( $out );
    }
    $directory =~ s!/$!!;
    unless ( $files ) {
        @$files = get_children_filenames( $directory );
    }
    $directory = quotemeta( $directory );
    for my $file ( @$files ) {
        my $new = $file;
        $new =~ s/^$directory//;
        $new =~ s!^/!!;
        $new =~ s!^\\!!;
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
                    my $sess = MT::Session->load ( { id => $app->param( 'sessid' ),
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
    eval { require LWP::UserAgent } || return undef;
    my $remote_ip;
    eval { $remote_ip = $app->remote_ip };
    my $agent;
    if ( $remote_ip ) {
        $agent = "Mozilla/5.0 (Power CMS for MT X_FORWARDED_FOR:$remote_ip)";
    } else {
        $agent = 'Mozilla/5.0 (Power CMS for MT)';
    }
    my $protcol = $1 if $uri =~ /^([^:]*):/;
    my $ua = LWP::UserAgent->new;
    $ua->agent( $agent );
    my $proxy = MT->instance->config->PingProxy;
    $ua->proxy( $protcol, $proxy ) if ( $protcol && $proxy );
    my $no_proxy = MT->instance->config->PingNoProxy;
    $ua->no_proxy( split( /,\s*/, $no_proxy ) ) if $no_proxy;
    my $timeout = MT->instance->config->PingTimeout;
    $ua->timeout( $timeout ) if $timeout;
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
        unless ( MT->version_number < 5 ) {
            $content = utf8_on( $content ) if $utf8;
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
    my $link  = $lite->find_link ( $lite->feed );
    my $entries = $lite->entries;
    my $count = scalar @$entries;
    if ( $utf8 ) {
        $title = to_utf8( $title );
        $link  = to_utf8( $link );
        unless ( MT->version_number < 5 ) {
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
    unless ( $@ ) { Text::CSV_XS->new ( { binary => 1 } ); } else
    { eval { require Text::CSV };
        return undef if $@; Text::CSV->new ( { binary => 1 } ); } };
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
    $ftp->cwd( $cwd ) or return undef;
    my $ftp_put = $ftp->put( $file );
    if ( $ftp_put ) {
        $app->run_callbacks( 'post_ftp_put', $app, $ftp, $cwd, $file, $mode, $params );
    }
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
        unless ( MT->version_number < 5 ) {
            $file = utf8_off( $file );
        }
        my $extension = file_extension( $file );
        my $ext_len = length( $extension ) + 1;
        if ( eval { require Digest::MD5 } ) {
            $file = Digest::MD5::md5_hex( $file );
        } else {
            $file = perl_sha1_digest_hex( $file );
        }
        $file = substr ( $file, 0, 255 - $ext_len );
        $file .= '.' . $extension;
    }
    return $file;
}

sub uniq_filename {
    my $file = shift;
    require File::Basename;
    my $dir = File::Basename::dirname( $file );
    my $tilda = quotemeta( '%7E' );
    $file =~ s/$tilda//g;
    $file = File::Spec->catfile( $dir, set_upload_filename( $file ) );
    return $file unless ( -f $file );
    my $file_extension = file_extension( $file );
    my $base = $file;
    $base =~ s/(.{1,})\.$file_extension$/$1/;
    $base = $1 if ( $base =~ /(^.*)_[0-9]{1,}$/ );
    my $i = 0;
    do { $i++;
         $file = $base . '_' . $i . '.' . $file_extension;
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
                my $command = 'File::Find::find ( sub { push ( @wantedFiles, $File::Find::name ) if ( /' . $pattern. '/ ) && -f ; }, $directory );';
                eval $command;
                if ( $@ ) {
                    return undef;
                }
            } else {
                return undef;
            }
        }
    } else {
        File::Find::find ( sub { push ( @wantedFiles, $File::Find::name ) unless (/^\./) || ! -f ; }, $directory );
    }
    return @wantedFiles;
}

sub get_children_files     { goto &get_children_filenames }
sub get_childlen_files     { goto &get_children_filenames }
sub get_childlen_filenames { goto &get_children_filenames }

sub get_permissions {
    my $app = MT->instance();
    return undef if (! is_cms( $app ) );
    return undef if (! $app->user );
    my $r = MT::Request->instance;
    my $perms;
    $perms = $r->cache( 'powercms_get_permissions' );
    return $perms if $perms;
    require MT::Permission;
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
   $a = 1 if ( $var > 0 and $var != int ( $var ) );
   return int ( $var + $a );
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
    $wants = 'Agent' unless $wants;
    $wants = lc( $wants );
    my $agent = $app->get_header( 'User-Agent' );
    if ( $like ) {
        if ( $agent =~ /$like/i ) {
            return 1;
        } else {
            return 0;
        }
    }
    my %smartphone = (
        'Android'     => 'Android',
        'dream'       => 'Android',
        'CUPCAKE'     => 'Android',
        'blackberry'  => 'BlackBerry',
        'iPhone'      => 'iPhone',
        'iPod'        => 'iPhone',
        'iPad'        => 'iPad',
        'incognito'   => 'Palm',
        'webOS'       => 'Palm',
        'incognito'   => 'iPhone',
        'webmate'     => 'iPhone',
        'Opera\sMini' => 'Opera Mini',
    );
    for my $key ( keys %smartphone ) {
        if ( $agent =~ /$key/ ) {
            if ( $wants eq 'agent' ) {
                return $smartphone{ $key };
            } else {
                if ( $wants ne 'keitai' ) {
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
    $permission = 'can_' . $permission;
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
        if ( $return_args =~ /&is_power_edit=1/ ) {
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
    my $tempdir = quotemeta( $app->config( 'TempDir' ) );
    my $importdir = quotemeta( $app->config( 'ImportPath' ) );
    my $powercms_files_dir = quotemeta( powercms_files_dir() );
    my $support_dir = quotemeta( support_dir() );
    if ( $path =~ /\A(?:$tempdir|$importdir|$powercms_files_dir|$support_dir)/ ) {
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
    my $file = shift;
    if ( !is_windows() && $file =~ m/\\/ ) { # Windows Style Path on Not-Win
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
    return 0 unless is_valid_email( $email );
    if ( $email =~ /^[^@]+@[^.]+\../ ) {
        return 1;
    }
    return 0;
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
        # Softbank
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
    my $ip_table = join ( "\n", @$table );
    if ( $remote_ip =~ /(^[0-9]{1,}\.[0-9]{1,}\.[0-9]{1,}\.)([0-9]{1,}$)/ ) {
        my @bits = qw/ 0 126 62 30 14 6 2 /;
        my $check = quotemeta( $1 );
        my $last = $2;
        if ( $ip_table =~ /$check([0-9]{1,})\/([0-9]{1,})/ ) {
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
        chmod ( 0777, $powercms_files );
        my $do = _create_powercms_subdir( $powercms_files );
        return chomp_dir( $powercms_files ) if (-w $powercms_files );
    }
    $powercms_files =~ s!/$!! unless $powercms_files eq '/';
    unless ( $fmgr->exists( $powercms_files ) ) {
        $fmgr->mkpath( $powercms_files );
        if (-d $powercms_files ) {
            unless (-w $powercms_files ) {
                chmod ( 0777, $powercms_files );
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
                            chmod ( 0777, $directory );
                        }
                    }
                } else {
                    unless (-w $directory ) {
                        chmod ( 0777, $directory );
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
            # chmod ( 0777, $path );
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
    return unless ( $blog_id =~ m/^\d+$/ );
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
    return unless ( $blog_id =~ m/^\d+$/ );
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
    my $fource = $app->config->FourceBackgroundTasks;
    if ( $fource ) {
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
        push ( @blogs, $blog );
        return @blogs;
    }
    push ( @blogs, $blog );
    if ( $blog->class eq 'website' ) {
        my $weblogs = $blog->blogs || [];
        push ( @blogs, @$weblogs );
    }
    return @blogs;
}

sub get_blog_ids {
    my $blog = shift;
    my @blog_ids;
    if ( MT->version_number < 5 ) {
        push ( @blog_ids, $blog->id );
        return @blog_ids;
    }
    push ( @blog_ids, $blog->id );
    if ( $blog->class eq 'blog' ) {
        push ( @blog_ids, $blog->parent_id ) if $blog->parent_id;
    }
    return @blog_ids;
}

sub listing_blog_ids {
    my $blog = shift;
    my $blog_ids;
    if ( $blog->class eq 'website' ) {
        $blog_ids = get_weblog_ids( $blog );
    } else {
        push ( @$blog_ids, $blog->id );
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
        $cache = $plugin->get_config_value( 'get_weblog_ids_cache', 'blog:'. $website->id );
    } else {
        $blog_ids = $r->cache( 'powercms_get_weblog_ids_system' );
        $cache = $plugin->get_config_value( 'get_weblog_ids_cache' );
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
        push ( @$blog_ids, $blog->id );
    }
    if ( $website ) {
        $r->cache( 'powercms_get_weblog_ids_blog:' . $website->id, $blog_ids );
        $plugin->set_config_value( 'get_weblog_ids_cache', join ( ',', @$blog_ids ), 'blog:'. $website->id );
    } else {
        $r->cache( 'powercms_get_weblog_ids_system', $blog_ids );
        $plugin->set_config_value( 'get_weblog_ids_cache', join ( ',', @$blog_ids ) );
    }
#     if ( wantarray ) {
#         return @$blog_ids;
#     }
    return $blog_ids;
}

sub include_exclude_blogs {
    my ( $ctx, $args ) = @_;
    unless ( $args->{ blog_id } || $args->{ include_blogs } || $args->{ exclude_blogs } ) {
        $args->{ include_blogs } = $ctx->stash( 'include_blogs' );
        $args->{ exclude_blogs } = $ctx->stash( 'exclude_blogs' );
        $args->{ blog_ids } = $ctx->stash( 'blog_ids' );
    }
    my ( %blog_terms, %blog_args );
    $ctx->set_blog_load_context( $args, \%blog_terms, \%blog_args ) or return $ctx->error($ctx->errstr);
    my @blog_ids = $blog_terms{ blog_id };
    return undef if ! @blog_ids;
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
        push ( @blog_ids, $blog->id );
        for my $child ( @$children ) {
            push ( @blog_ids, $child->id );
        }
    } elsif ( $include_blogs eq 'siblings' ) {
        my $website = $blog->website;
        if ( $website ) {
            my $children = $website->blogs;
            my @blog_ids;
            push ( @blog_ids, $website->id );
            for my $child ( @$children ) {
                push ( @blog_ids, $child->id );
            }
        } else {
            push ( @blog_ids, $blog->id );
        }
    } else {
        if ( $include_blogs ) {
            @blog_ids = split( /\s*,\s*/, $include_blogs );
            # push ( @blog_ids, $blog->id );
        } else {
            if ( $blog->class eq 'website' ) {
                my @children = $blog->blogs;
                push ( @blog_ids, $blog->id );
                for my $child( @children ) {
                    push ( @blog_ids, $child->id );
                }
            }
        }
    }
    if ( wantarray ) {
        return @blog_ids;
    } else {
        return \@blog_ids;
    }
}

sub flush_weblog_ids {
    my $website = shift;
    return unless $website;
    my $plugin = MT->component( 'PowerCMS' );
    return unless $plugin;
    $website = $website->website if $website->class eq 'blog';
    if ( $website ) {
        $plugin->set_config_value( 'get_weblog_ids_cache', '', 'blog:'. $website->id );
    } else {
        $plugin->set_config_value( 'get_weblog_ids_cache', '' );
    }
}

sub get_blogs {
    my $blog = shift;
    my @blogs;
    if ( MT->version_number < 5 ) {
        push ( @blogs, $blog );
        return @blogs;
    }
    push ( @blogs, $blog );
    if ( $blog->class eq 'blog' ) {
        my $website = $blog->website;
        push ( @blogs, $website ) if $website;
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
    return unless $str;
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
    open  ( my $fh, ">> $out" ) || die "Can't open $out!";
    print $fh "$msg\n";
    close ( $fh );
}

sub get_config_inheritance {
    my ( $plugin, $key, $blog ) = @_;
    my $get_from;
    if ( $blog ) {
        $get_from = 'blog:' . $blog->id;
    } else {
        $get_from = 'system';
    }
    my $plugin_data = $plugin->get_config_value( $key, $get_from );
    if ( (! $plugin_data ) && $blog ) {
        my $website;
        if ( MT->version_number < 5 ) {
            $get_from = 'system';
        } else {
            if (! $blog->is_blog ) {
                $get_from = 'system';
            } else {
                if ( $website = $blog->website ) {
                    if ( $website ) {
                        $get_from = 'blog:' . $website->id;
                    } else {
                        $website = $blog;
                        $get_from = 'blog:' . $blog->id;
                    }
                }
            }
        }
        $plugin_data = $plugin->get_config_value( $key, $get_from );
        if ( (! $plugin_data ) && $website ) {
            $plugin_data = $plugin->get_config_value( 'analytics_profile_id', 'system' );
        }
    }
    return $plugin_data;
}

sub flush_blog_cmscache {
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
        if ( $url =~ /^http/ ) {
            my $file_path = path2relative( $url, $blog );
            my $asset = MT::Asset->load( { blog_id => $blog->id, class => '*',
                                           file_path => $file_path } );
            push ( @assets, $asset ) if $asset;
        }
    }
    if ( wantarray ) {
        return @assets;
    } else {
        return \@assets;
    }
}

sub convert2thumbnail {
    my ( $blog, $text, $type, $embed, $link,
         $dimension, $convert_gif_png ) = @_;
    my $test = $text;
    my $site_url = site_url( $blog );
    my $site_path = site_path( $blog );
    my $search_path = quotemeta( $site_url );
    require MT::Asset;
    require File::Basename;
    if (! $dimension ) {
        $dimension = 'width';
    } else {
        $dimension = lc( $dimension );
    }
    if (! $type ) {
        $type = 'auto';
    } else {
        $type = lc( $type );
    }
    for my $img ( $text =~ m/(<img.*?>)/isg ) {
        my $src = $1 if ( $img =~ /src\s*="(.*?)"/is );
        if ( $src ) {
            my $path = $src;
            my $size;
            my $scope = $dimension;
            my $width = $1 if ( $img =~ /width\s*="(.*?)"/is );
            my $height = $1 if ( $img =~ /height\s*="(.*?)"/is );
            next if ( (! $width ) || (! $height ) );
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
            if ( $path =~ m!^/! ) {
                $path = $site_url . $path;
            }
            $path =~ s/^$search_path//;
            $path = $site_path . $path;
            if (-f $path ) {
                my $basename = File::Basename::basename( $path );
                my $asset_pkg = MT::Asset->handler_for_file( $basename );
                if ( $asset_pkg eq 'MT::Asset::Image' ) {
                    my $orig_update = ( stat( $path ) )[9];
                    require MT::Asset::Image;
                    $asset_pkg->isa( $asset_pkg );
                    my $file_path = path2relative( $path, $blog );
                    my $asset = $asset_pkg->load( { blog_id => $blog->id,
                                                    file_path => $file_path } );
                    next unless defined $asset;
                    my $orig = quotemeta( $img );
                    my %param = ( $scope_tc => $embed, Path => undef, convert_gif_png => $convert_gif_png );
                    my ( $thumb, $w, $h ) = create_thumbnail( $blog, $asset, %param );
                    # my $thumb_new = _convert_gif_png( $thumb );
                    my $url = path2url( $thumb, $blog );
                    $img =~ s/(src\s*=").*?(")/$1$url$2/;
                    $img =~ s/(width\s*=").*?(")/$1$w$2/;
                    $img =~ s/(height\s*=").*?(")/$1$h$2/;
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
                    for my $anchor( $test =~ m/(<a[^>]*>(.*?)<\/a>)/isg ) {
                        if ( $anchor =~ /$check/ ) {
                            my $count = $anchor;
                            $count = $count =~ s/<a//g;
                            if ( $count > 1 ) {
                                $img = $no_link;
                                $anchor = quotemeta( $anchor );
                                $test =~ s/$anchor//;
                                last;
                            }
                        }
                    }
                    $text =~ s/$orig/$img/g;
                }
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
    if ( $code =~ /<\%/ || $code =~ /\%>/ ) {
        return 1;
    }
    for my $program ( $code =~ m/<script(.*?)>/isg ) {
        if ( $program =~ /language\s*=\s*\"php"/ ) {
            return 1;
        } elsif ( $program =~ /type\s*=\s*\"text\/php"/ ) {
            return 1;
        }
    }
    return 0;
}

sub referral_site {
    my $app = MT->instance();
    my $referer = $app->get_header( 'REFERER' );
    return '' unless $referer;
    if ( $referer =~ m!(^https{0,1}://.*?/)! ) {
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
    my $query;
    if ( $referer =~ m!(^https{0,1}://.*?)/.*?\?(.*$)! ) {
        my $request = $1;
        my $param = '&' . $2;
        if ( ( $request =~ /\.google\./ ) || ( $request =~ /\.bing\./ ) || ( $request =~ /\.msn\./ ) ) {
            if ( $param =~ /&q=([^&]*)/ ) {
                $query = 1;
            }
        } elsif ( $request =~ /\.yahoo\./ ) {
            if ( $param =~ /&p=([^&]*)/ ) {
                $query = 1;
            }
        } elsif ( $request =~ /\.goo\./ ) {
            if ( $param =~ /&MT=([^&]*)/ ) {
                $query = 1;
            }
        } else {
            if ( my $blog = $app->blog ) {
                my $site_url = site_url( $blog );
                my $search = quotemeta( $request );
                if ( $site_url =~ /^$search/ ) {
                    if ( $param =~ /&query=([^&]*)/ ) {
                        $query = $1;
                    }
                }
            }
        }
    }
    $query = trim( $query );
    return undef unless $query;
    if ( wantarray ) {
        my @keywords = split( /\s+/, $query );
        return @keywords;
    }
    return $query;
}
sub referral_serch_keyword { goto &referral_search_keyword }

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

1;