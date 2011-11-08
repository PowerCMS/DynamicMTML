package HTML::StripTags;

=head1 NAME

HTML::StripTags - Strip HTML or XML tags from a string with Perl like PHP's strip_tags() does

=head1 SYNOPSIS

 use HTML::StripTags qw(strip_tags);

 $string       = '<html>Hallo <u>beautiful</u> <a href="http://worldonline.com">world</a>!</html>';
 $allowed_tags = '<u><b>';

 print strip_tags( $string, $allowed_tags );

=head1 DESCRIPTION

HTML::StripTags provides the function strip_tags() that can strip all HTML or XML tags from a string except the given allowed tags.
This is a Perl port of the PHP function strip_tags() based on PHP 5.3.3.

=head1 SECURITY NOTE

Please note: As per L<http://htmlpurifier.org/comparison#striptags> PHP's strip_tags() is a very basic and unsafe method, so it's strongly recommended not to use it for cleaning up user input!
As HTML::StripTags uses the same state machine, the same applies to this module.

=head1 METHODS

=cut

#------------------------- Pragmas ---------------------------------------------
use strict;
use warnings;

#------------------------- Libs ------------------------------------------------
use Carp;
use Exporter;
use Switch 'fallthrough';

our $VERSION = '1.01';
our @ISA = qw(Exporter);
our @EXPORT_OK = qw(strip_tags);

=head2 strip_tags()

A simple little state-machine to strip out html and php tags

State 0 is the output state, state 1 means we are inside a
normal html tag, state 2 means we are inside a php tag, state 3
means we are inside a "<!--", case 4 means we are inside the
following JavaScript/CSS/etc. tag.

When an allow string is passed in we keep track of the string
in state 1 and when the tag is closed check it against the
allow string to see if we should allow it.

 print strip_tags( $string, "<u><b><?php<?" );

 print strip_tags( $string, ('<u>', '<b>', '<?php', '<?') );

=cut

sub strip_tags {
    my ( $string, $allow ) = @_;

    if (scalar(@_) == 0) {
        croak sprintf "strip_tags() expects at least 1 parameter, %d given", scalar(@_);
        return;
    }
    if (scalar(@_) > 2) {
        croak sprintf "strip_tags() expects at most 2 parameters, %d given", scalar(@_);
        return;
    }
    if (ref($string) ne 'STRING' && ref($string) ne '') {
        croak sprintf "strip_tags() expects parameter 1 to be string, %s given", ref($string);
        return;
    }
    if (ref($allow) eq 'ARRAY') {
        $allow = join "", @$allow;
        croak "Array to string conversion";
    }

    my $i     = 0;                  # while counter
    my $len   = defined $string ? length($string) : 0;    # while end condition
    my @buf   = defined $string ? split(//, $string) : (); # input buffer
    my @tbuf  = ();                 # tag buffer
    my @rbuf  = ();                 # return buffer
    my $c     = $buf[0];            # current character of buf
    my $p     = 0;                  # current position of c in buf
    my $lc    = "\0";               # holds the last significant character read
    my $br    = 0;                  # bracket counter
    my $depth = 0;                  # tag depth
    my $in_q  = 0;                  # in quote
    my $state = 0;                  # State 0 is the output state, state 1 means we are inside a
                                    # normal html tag, state 2 means we are inside a php tag, state 3
                                    # means we are inside a "<!--", case 4 means we are inside the
                                    # following JavaScript/CSS/etc. tag.

    if ($allow) {
        $allow = lc($allow);
    }

    while ($i < $len) {
        switch ($c) {
            case "\0" {
                last;
            }
            case '<' {
                if ($in_q) {
                    last;
                }
                if ($buf[$p + 1] =~ /\s/ ) {
                    next;
                }
                if ($state == 0) {
                    $lc = '<';
                    $state = 1;
                    if ($allow) {
                        push @tbuf, '<';
                    }
                } elsif ($state == 1) {
                    $depth++;
                }
                last;
            }
            case '(' {
                if ($state == 2) {
                    if ($lc ne '"' && $lc ne "'") {
                        $lc = '(';
                        $br++;
                    }
                } elsif ($allow && $state == 1) {
                    push @rbuf, $c;
                } elsif ($state == 0) {
                    push @rbuf, $c;
                }
                last;
            }
            case ')' {
                if ($state == 2) {
                    if ($lc ne '"' && $lc ne "'") {
                        $lc = ')';
                        $br--;
                    }
                } elsif ($allow && $state == 1) {
                    push @rbuf, $c;
                } elsif ($state == 0) {
                    push @rbuf, $c;
                }
                last;
            }
            case '>' {
                if ($depth) {
                    $depth--;
                    last;
                }

                if ($in_q) {
                    last;
                }

                switch ($state) {
                    case 1 { # HTML/XML
                        $lc = '>';
                        $in_q = $state = 0;
                        if ($allow) {
                            push @tbuf, '>';
                            if ($allow =~ (join '', ((join '', map(lc, @tbuf)) =~ m!(<)/?(\S+)(?:.*)(>)! ))) {
                                push @rbuf, @tbuf;
                            }
                            @tbuf = ();
                        }
                        last;
                    }
                    case 2 { # PHP
                        if (!$br && $lc ne '"' && $buf[$p-1] eq '?') {
                            $in_q = $state = 0;
                            @tbuf = ();
                        }
                        last;
                    }
                    case 3 { # <!--
                        $in_q = $state = 0;
                        @tbuf = ();
                        last;
                    }
                    case 4 { # Inside <!-- comment -->
                        if ($p >= 2 && $buf[$p-1] eq '-' && $buf[$p-2] eq '-') {
                            $in_q = $state = 0;
                            @tbuf = ();
                        }
                        last;
                    }
                    case qr/\d/ {
                        push @rbuf, $c;
                        last;
                    }
                }
                last;
            }
            case m/\'|\"/ {
                if ($state == 4) {
                    # Inside <!-- comment -->
                    last;
                } elsif (($state == 2) && $buf[$p-1] ne '\\') {
                    # Inside PHP
                    if ($lc eq $c) {
                        $lc = "\0";
                    } elsif ($lc ne '\\') {
                        $lc = $c;
                    }
                } elsif ($state == 0) {
                    # Outside a tag
                    push @rbuf, $c;
                } elsif ($allow && $state == 1) {
                    # Inside a tag
                    push @tbuf, $c;
                }
                if ($state && $p != 0 && ($state == 1 || $buf[$p-1] ne '\\') && (!$in_q || $buf[$p] eq $in_q)) {
                    if ($in_q) {
                        $in_q = 0;
                    } else {
                        $in_q = $buf[$p];
                    }
                }
                last;
            }
            case '!' {
                # JavaScript & Other HTML scripting languages
                if ($state == 1 && $buf[$p-1] eq '<') {
                    $state = 3;
                    $lc = $c;
                } else {
                    if ($state == 0) {
                        push @rbuf, $c;
                    } elsif ($allow && $state == 1) {
                        push @tbuf, $c;
                    }
                }
                last;
            }
            case '-' {
                if ($state == 3 && $p >= 2 && $buf[$p-1] eq '-' && $buf[$p-2] eq '!') {
                    # <!-- opening finished, now inside <!-- comment -->
                    $state = 4;
                } else {
                    next;
                }
                last;
            }
            case '?' {
                if ($state == 1 && $buf[$p-1] eq '<') {
                    # opened <? PHP tag
                    $br=0;
                    $state=2;
                    last;
                }
            }
            case m/E|e/ {
                # !DOCTYPE exception
                if ($state==3 && $p > 6
                             && lc($buf[$p-1]) eq 'p'
                             && lc($buf[$p-2]) eq 'y'
                             && lc($buf[$p-3]) eq 't'
                             && lc($buf[$p-4]) eq 'c'
                             && lc($buf[$p-5]) eq 'o'
                             && lc($buf[$p-6]) eq 'd') {
                    # we're not in a <!-- (state=3) but in a HTML tag
                    $state = 1;
                    last;
                }
                # fall-through
            }
            case m/L|l/ {
                if ($state == 2 && $p > 2 && lc(join "", ($buf[$p-2], $buf[$p-1])) eq "xm") {
                    # we're not in a <? (state=2, PHP) but in a <?xml tag (state=1, HTML)
                    $state = 1;
                    last;
                }
                # fall-through
            }
            case m/.*/ {
                if ($state == 0) {
                    push @rbuf, $c;
                } elsif ($allow && $state == 1) {
                    push @tbuf, $c;
                }
                last;
            }
        }
        $p++;
        $c = $buf[$p];
        $i++;
    }

    return join "", @rbuf;
}

1;

__END__

=head1 TODO

=over 4

=item Pass in state variable to allow a function like fgetss() to maintain state across calls to the function.

=item Implement fgetss().

=back

=head1 BUGS

Please report any bugs or feature requests to C<< <hinnerk at cpan.org> >>, or through
the GitHub web interface at L<http://github.com/hinnerk-a/perl-strip_tags/issues>.  I will be notified, and then you'll
automatically be notified of progress on your bug as I make changes.

=head1 SUPPORT

You can find documentation for this module with the perldoc command.

    perldoc HTML::StripTags

You can also look for information at:

=over 4

=item * HTML::StripTags Homepage

L<http://www.hinnerk-altenburg.de/perl-strip_tags/>

=item * Code Repository on GitHub

L<http://github.com/hinnerk-a/perl-strip_tags>

=item * GitHub Issue Tracker

L<http://github.com/hinnerk-a/perl-strip_tags/issues>

=item * AnnoCPAN: Annotated CPAN documentation

L<http://annocpan.org/dist/HTML-StripTags>

=item * CPAN Ratings

L<http://cpanratings.perl.org/d/HTML-StripTags>

=item * Search CPAN

L<http://search.cpan.org/dist/HTML-StripTags>

=back

=head1 AUTHOR

Hinnerk Altenburg, C<< <hinnerk at cpan.org> >> L<http://www.hinnerk-altenburg.de/>

=head1 COPYRIGHT & LICENSE

Copyright (C) 2011 by Hinnerk Altenburg C<< <hinnerk at cpan.org> >> L<http://www.hinnerk-altenburg.de/>.

This file is part of HTML::StripTags.

HTML::StripTags is free software; you can redistribute it and/or modify it under
the same terms as the Perl 5 programming language system itself.

Terms of the Perl programming language system itself

a) the GNU General Public License "L<perlgpl>" as published by the Free
   Software Foundation; either version 1, or (at your option) any
   later version, or

b) the "Artistic License" "L<perlartistic>"" which comes with this Kit.

=head1 SEE ALSO

L<HTML::Strip>, L<perlfaq9/"How do I remove HTML from a string?">, L<HTML::Parser>

=cut
