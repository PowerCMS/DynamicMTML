About DynamicMTML

[Synopsis]

DynamicMTML is the PHP extension framework for Movable Type. By using mod_rewrite to pass the HTTP request to the dynamic site Bootstrapper (.mtview.php),  MTML Tags in static files will be processed by Movable Type's dynamic publishing engine. This framework also provides various libraries and callbacks for the plugin.


[System Requirements]

Movable Type 5.04 or later
dynamic publishing (PHP 5 or later)
mod_rewrite and .htaccsess


[Getting Started]

Upload DynamicMTML.pack to the addons directory ( MT_HOME/addons/ ).

Check Website/Blog's Settings > General > Dynamic Publishing Options > Enable DynamicMTML and click "Save Changes".

Confirm .htaccess and .mtview.php were generated under the blog's publishing path.
Confirm a directory called "powercms_files" was created under the MT root directory ( MT_HOME/powercms_files), and was granted a writable permissions.

If you wish to enable Perl based dynamic publishing, copy two files under addons/DynamicMTML.pack/tools to the MT_HOME/tools directory, and grant an executable permission.


[Settings]

You can specify the following directives in mt-config.cgi.

# Do not display error on Edit Template screen.
DisableCompilerWarnings 1

# Always compile smarty templates.
DynamicForceCompile 1

# Run PHP in static files.
DynamicIncludeStatic 1

# Specify the path of "powercms_files" directory (include cache directory).
PowerCMSFilesDir /path/to/powercms_files

# Specify the PHP file to run the DynamicMTML.
DynamicSiteBootstrapper .mtview.php

# Ignores the value of UserSessionTimeout when you call $app->user().
UserSessionTimeoutNoCheck 1

# DynamicMTML does not magic_quotes_gpc to Off
# (If magic_quotes_gpc is On, DynamicMTML does magic_quotes_gpc to Off).
AllowMagicQuotesGPC 1 # (Default:0)

# PHP code to run earlier than MTML.
DynamicPHPFirst 1 # (Default:0)


[Overview]

When a server receives a HTTP request:

If the static file does not exist, the request is processed by the Movable Type's standard dynamic publishing engine.

If the static file already exists, and its mime_type is text/foo or application/xhtml+xml, MTML Tags within the text file will also be processed by the Movable Type's dynamic publishing engine, and PHP code is executed if "DynamicIncludeStatic" config directive is set to 1.

You can use callbacks from the plug-in during this process.


[Fail-safe for the database connection error]

Callback plugin can handle DB connection error in several ways. Such as:

1. Try to access to another database
2. If cache is enabled, use cache when it exists.
3. If the static file exists, return the contents of the file.

During this fail-over, You can use the following MTML tag.

    <MTNonDynamicMTML>
        This block is displayed.
    </MTNonDynamicMTML>
    <MTDynamicMTML>
        This block is not displayed.
    </MTDynamicMTML>

Once MTDynamicMTML and MTNonDynamicMTML tags were processed, the other MTML tags will be remove. See also callback plugin examples(init_request, mt_init_exception).


[Template Tags]

MTDynamicMTML (Block Tag)

If the template's publishing setting was set to static, this block is not built during MT's static publishing process. Instead, this block is processed dynamically at the HTTP access.

* To build this tag and MTRawMTML tag statically with PHP, please specify the unique value in 'id' modifier. (This Tag is not available in included template because 'id' must be contains at the Smarty template).


MTNonDynamicMTML(Block Tag)

This block is displayed when database connection fails (MT:: get_instance failed).


MTRawMTML(Block Tag)

If the template's publishing setting was set to static, the contents within this block is not built (output raw content).


MTIfLogin(Block Tag)

Conditional block tag. True if the user is logged in. MTAuthorFoo tags are available in this block. 


MTClientAuthorBlock(Block Tag)

When the user was logged in, MTAuthorFoo tag are available in this block ( To use this tag, Blog's setting [Enable dynamic cache] and [Enable DynamicMTML Cache] must be off).


MTLoginAuthorCTX (Block Tag)

Alias for MTClientAuthorBlock.


MTIfUserHasPermission (Block Tag)

Output if the user was logged in, and user has permission arguments[permission](ex.permission="comment").


MTIfUserAgent(Block Tag)

Conditional block tag.
True when Arguments[wants] are 'keitai' and User-Agent is Japanese mobile phone's browser.
True when Arguments[wants] are 'smartphone' and User-Agent is SmartPhone(ex:iPhone).
True when User-Agent contains a arguments[like].
See also $app->get_agent().

Example:

    <MTIfUserAgent wants="keitai">
        Output this area when User-Agent is DoCoMo or AU or SoftBank(Japanese mobile phone)
    </MTIfUserAgent>
    <MTIfUserAgent wants="SmartPhone">
        Output this area when User-Agent is Android or BlackBerry or iPhone(iPod touch) or iPad or Palm or Opera Mini
    </MTIfUserAgent>
    <MTIfUserAgent like="Safari">
        Output this area when User-Agent contains a 'Safari'
    </MTIfUserAgent>


MTEntryCategoryBlock(Block Tag)

Set the context of the primary category of the entry.


MTSearchEntries(Block Tag)

This template tag returns a list of entries those mt_arguments[target] contains a arguments[query].
If arguments[target] is empty, the default is set to entry_title||entry_text||entry_text_more||entry_excerpt.
Specify the arguments[operator](default:LIKE) to set a search criteria.
For example, operator="=" target="title" query="Movable Type" hits entries those title equal to 'Movable Type'.

    Attributes
    query         : Search string.
    blog_id       : Blog's ID
    include_blogs : A comma delimited list of blog ids specifying which blogs to include entries from, or the word "all" "children" "siblings" to include entries from all blogs in the installation.
    exclude_blogs : A comma delimited list of blog ids specifying which blogs to exclude entries from when including entries from all blogs in the installation.
    target        : Search target column name.
    operator      : Search criteria(Expression of SQL. default:LIKE).
    class         : Class name(entry or page).
    category      : Category label
    category_id   : Category id
    tag           : Tag
    status        : Number or '*'(all)
    sort_by       : Defines the data to sort entries. The default value is "authored_on".
    sort_order    : Accepted values are "ascend" and "descend". Default order is descend.
    lastn         : Display the N entries. N is a positive integer.
    offset        : Used in coordination with lastn, starts N entries from the start of the list.
    unique        : Filters out entries that have been previously published on the same page using another <MTSearchEntries> tag. Values 1 or 0 (default).
    not_entry_id  : Except single entry which entry_id equal to attribute value[ID].


MTQueryLoop(Block Tag)

Loop query parameter arguments[key]. In this loop, set var[key] to query parameter.

Example:
    <MTQueryLoop key="foo" glue=","><mt:var name="foo" escape="html"></MTQueryLoop>

    If the HTTP request is request_url?foo[]=bar&foo[]=buz , output [bar,buz].


MTQueryVars(Block Tag)

Loop query parameter. In this loop, set var[key] to key, set var[value] to query parameter.

Example:

    <MTQueryVars glue=",">
        <mt:var name="key" escape="html"> =&gt; <mt:var name="value" escape="html">
    </MTQueryVars>

    If Request is request_url?foo=bar&bar=buz , output [foo=>bar,bar=>buz].


MTSetQueryVars(Block Tag)

Set to query parameter to var[key].

Example:
    <MTSetQueryVars><mt:var name="foo" escape="html">,<mt:var name="bar" escape="html"></MTSetQueryVars>

If the HTTP request is request_url?foo=1&bar=2 , output [1,2].


MTSplitVars(Block Tag)

Loop string arguments[text] separated by arguments[delimiter]. In this loop, set variable[value(or arguments[name])] to separated text.


MTReferralKeywords(Block Tag)

If referral website is Google, Bing, MSN, Yahoo! or Goo (or search this site *parameter is 'query' or 'search'), output keywords. In this loop, set variable[keyword] to keyword.


MTCommentOut(Block Tag)

Output '<!--' & contents & '-->'. If you specify "invisible" attribute, it does not output anything. Same as MTIgnore.


MTCommentStrip(Block Tag)

Strip '<!--' and '-->' when publish. By using MTCommentOut and MTCommentStrip tags, you can edit the template with Dreamweaver with a dummy HTML. You can strip the dummy HTML on the server with these tags.


MTML (Function Tag)

Static publishing templates only. Outputs the MT Tag attributes[tag] add parameters attributes[params].

Example:

    <MTML tag="MTIfUserHasPermission" params='permission="comment"'>
        You can post comment on <MTBlogName escape="html">.
    <MTML tag="MTElse">
        You can't post comment on <MTBlogName escape="html">.
    <MTML tag="/MTIfUserHasPermission">

    This template outputs following code in the static file and processed by this dynamic MTML engine.

    <MTIfUserHasPermission permission="comment">
        You can post comment on [Blog Name].
    <MTElse>
        You can't post comment on [Blog Name].
    </MTIfUserHasPermission>


MTMTML(Function Tag)

Alias for MTML.


MTRawMTMLTag(Function Tag)

Alias for MTML.


MTQuery(Function Tag)

Output query parameter arguments[key].


MTReferralKeyword(Function Tag)

If referral website is Google, Bing, MSN, Yahoo! or Goo(or search this site *parameter is 'query' or 'search'), output keyword(phrase).


MTUserAgent(Function Tag)

Output the User-Agent string to determine. With raw="1" attribute, it outputs HTTP_USER_AGENT.
See also $app->get_agent().


MTCurrentArchiveURL(Function Tag)

Output current page URL(without the query string).


MTCurrentArchiveFile(Function Tag)

Output server file path of the current page.


MTEntryStatusInt(Function Tag)

Output entry's status as a number.


MTAuthorLanguage(Function Tag)

Output author's preferred_language.


MTTrans(Function Tag)

Output translated phrase based on the current user's language preference.

* Array $Lexicon in PluginName/php/l10n/l10n_en.php (or other languages), can be extended as a translate table.


highlightingsearchword (Attribute)

If the referral website is Google, bing, MSN, Yahoo! or goo(or search this site *parameter is 'query' or 'search'), strong tag with attribute class argument.

Example:
    <MTEntryBody highlightingsearchword="match-words">
    Replace Keyword to <strong class="match-words">Keyword</strong>


make_seo_basename (Attribute)

Extracting from a character string specifying the URL from the first letter only available to URL-encoded string and returns the basename. URL is not available to the character '_' will be replaced.

Example:

    <$MTEntryTitle make_seo_basename="50"$>
   [Welcome to Movable Type] => [Welcome_to_Movable_Type]


trimwhitespace (Attribute)

Trim white spaces and blank lines.


intval (Attribute)

Convert string to int.


[Template Tag examples]

# Search entry body (/foo.html?q=keyword)

    <mt:dynamicmtml>
    <mt:query key="q" escape="html" setvar="query">
    <mt:if name="query">
        <mt:query key="limit" intval="1" setvar="limit">
        <mt:unless name="limit">
            <mt:setvar name="limit" value="20">
        </mt:unless>
        <mt:searchentries target="text" query="$query" lastn="$limit" count="1">
        <mt:if name="__first__">
        <div class="search-entry widget">
            <h3 class="widget-header">Serarch result for '<$mt:var name="query"$>'(<mt:var name="__entries_count__"> entries match).</h3>
                <ul>
        </mt:if>
                    <li><a href="<$mt:entrypermalink$>"><$mt:entrytitle escape="html"$></a></li>
        <mt:if name="__last__">
                </ul>
                <mt:if name="__entries_count__" gt="$limit">
                    <p><a href="<mt:CurrentArchiveUrl>?q=<$mt:var name="query" escape="url"$>&amp;limit=<mt:var name="__entries_count__">">More</a></p>
                </mt:if>
        </mt:if>
        </mt:searchentries>
    </mt:if>
    </mt:dynamicmtml>


# List 10 entries that contain the search word in the entry body when the referer contains  search engines, and highlight the search words.

    <mt:dynamicmtml>
    <mt:archivetype setvar="archive_type">
    <mt:if name="archive_type" eq="Individual">
    <$mt:entryid setvar="me"$>
    </mt:if>
    <mt:if name="archive_type" eq="Page">
    <$mt:pageid setvar="me"$>
    </mt:if>
    <$mt:setvar name="entries_max" value="10"$>
    <$mt:setvar name="entries_counter" value="0"$>
    <mt:referralkeywords trimwhitespace="1">
    <mt:if name="entries_counter" lt="$entries_max">
        <mt:searchentries target="text" query="$keyword" unique="1" lastn="$entries_max" not_entry_id="$me" class="*" highlightingsearchword="1">
            <mt:unless name="entries_counter">
    <div class="related-entry widget">
        <h3 class="widget-header">Your serarch keyword '<$mt:referralkeyword escape="html"$>'?</h3>
            <ul>
            </mt:unless>
            <mt:if name="entries_counter" lt="$entries_max">
                <li><a href="<$mt:entrypermalink$>"><$mt:entrytitle escape="html"$></a></li>
            </mt:if>
            <$mt:setvar name="entries_counter" value="1" op="+"$>
        </mt:searchentries>
    </mt:if>
    </mt:referralkeywords>
            <mt:if name="entries_counter"></ul>
    </div>
    </mt:if>
    </mt:dynamicmtml>


# For PC, return the static archive. For the mobile and smartphone access, includes templates by dynamic MTML.

    <mtml tag="mt:IfUserAgent" params='wants="keitai"'>
        <mt:dynamicmtml>
            <$mt:include module="Template for Japanese Keitai"$>
        </mt:dynamicmtml>
    <mtml tag="mt:else">
    <mtml tag="mt:IfUserAgent" params='wants="SmartPhone"'>
        <mt:dynamicmtml>
            <$mt:include module="Template for SmartPhone"$>
        </mt:dynamicmtml>
    <mtml tag="mt:else">
        <$mt:include module="Template for PC"$>
    <mtml tag="/mt:else">
    <mtml tag="/mt:IfUserAgent">
    <mtml tag="/mt:else">
    <mtml tag="/mt:IfUserAgent">


#  Show Edit link for Authorized user.

    <mt:dynamicmtml>
    <mt:IfUserHasPermission permission="edit_all_posts">
        <$mt:setvar name="can_post" value="1"$>
    <mt:Else>
        <mt:IfUserHasPermission permission="publish_post">
            <$mt:entryAuthorId setvar="entry_author_id"$>
            <$mt:AuthorId setvar="client_author_id"$>
            <mt:if name="entry_author_id" eq="client_author_id">
               <$mt:setvar name="can_post" value="1"$> 
            </mt:if>
        </mt:IfUserHasPermission>
    </mt:Else>
    </mt:IfUserHasPermission>
    <mt:if name="can_post">
        <p>
            <a href="<$mt:CGIPath$><$mt:AdminScript$>?__mode=view&amp;_type=entry&amp;id=<$mt:entryid$>&amp;blog_id=<$mt:blogid$>">Edit</a>
        </p>
    </mt:if>
    </mt:dynamicmtml>


[Class DynamicMTML]

    $mt_dir = '/path/to/mt/';
    require_once ( $mt_dir . 'php/mt.php' );
    require_once ( $mt_dir . 'addons/DynamicMTML.pack/php/dynamicmtml.php' );

    $blog_id = 1;
    $mt_config = $mt_dir . 'mt-config.cgi';
    $app = new DynamicMTML();
    $app->configure( $mt_config );
    try {
        $mt = MT::get_instance( $blog_id, $mt_config );
    } catch ( MTInitException $e ) {
        $app->run_callbacks( 'mt_init_exception', $mt, $ctx, $args, $e );
    }
    if ( isset( $mt ) ) {
        $ctx =& $mt->context();
        $app->set_context( $mt, $ctx );
    }
    ...


[Get class in plugin]
In DynamicMTML environment, 

    global $app
    // or
    $app = $ctx->stash('bootstrapper')


[MT::App compatible methods]

$app->login()

Checks the user's credentials, first by looking for a login cookie, then by looking for the username and password parameters. In both cases, the username and password are verified for validity, and set the user's login cookie and redirect to URL which return_url parameter.


$app->logout()

Clear user's login cookie. If the sessid parameter is set if, remove sessid and the redirect to the URL.


$app->send_http_header($content_type,[$ts,$length])

Sends the HTTP header to the client. If $content_type is specified, the Content-Type header is set to $content_type. <text/html> is used as the default.
If $ts(Unix Unix epoch format timestamp) is specified, set the Last-Modified header and the ETag header. If $length is specified, set the Content-Length header.


$app->user_cookie()

Returns the string of the cookie name used for the user login cookie.


$app->user()

Returns the object of the logged in user. Typically a MT::Author object.


$app->blog([$blog_id])

Returns the active application's MT::Blog object.


$app->current_magic()

Returns the active user's "magic token" which is used to validate posted data with the validate_magic method.


$app->make_magic_token()

Creates a new "magic token" string which is a random set of characters.


$app->validate_magic()

Checks for a 'magic_token' HTTP parameter and validates it for the current author.
If it is invalid, an error message is assigned to the application and a false result is returned. If it is valid, it returns 1.

Example:

    if (! $app->validate_magic() ) {
        echo $app->translate( 'Error:Invalid request.' );
        exit();
    }


$app->mode()

Returns the '__mode' parameter. 'default' is used as the default.


$app->session()

Returns the active user's session(MT::Session) object.


$app->static_path()

Returns the StaticWebPath(Example:/path/to/mt-static/).


$app->cookie_val($name)

Returns the value of a given cookie.


$app->delete_param($param)

Clears the value of a given CGI parameter.


$app->is_secure()

Returns 1 result based on whether the application request is happening over a secure (HTTPS) connection.


$app->param($name)

Interface for getting and setting CGI query parameters.


$app->query_string()

Returns the CGI query string of the active request(If REQUEST_METHOD is GET).


$app->request_method()

Returns the method of the active HTTP request, typically either "GET" or "POST".


$app->redirect($url)

Redirects the client to the URL $url. If $url is not an absolute URL, it is prepended with the value of $app->base().

By default, the redirection is accomplished by means of a Location header and a 302 Redirect response.


$app->base()

The protocol and domain of the application. For example, with the full URI
http://www.foo.com/mt/mt.php, this method will return http://www.foo.com.


$app->path()

The path component of the URL of the application directory. For example, with the full URL http://www.foo.com/mt/mt.html, this method will return /mt/.


$app->log($msg[,$args])

Adds the message $msg to the activity log. The log entry will be tagged with the IP address of the client running the application (that is, of the browser that made the HTTP request), using $app->remote_ip().
$args['level'],$args['class'],$args['metadata'] can be specified.


$app->remote_ip()

The IP address of the client.


$app->config($configname)

This method is used to get configuration settings($configname).


$app->component($plugin_name)

Get Class $plugin_name defined in config.php or config.yaml. If there is a config.php, you get the class there is defined in config.php. If there isn't a config.php, get class MTPlugin.


$app->translate($str[,$params])

Translate $str to the current user's language.


$app->build_page($path[,$params]);

Return result of build template file $path(array $params set to 'var').

Example:

    $app->build_page( '/path/to/template.tmpl', array( 'foo' => 'bar' ) );



[MT::WeblogPublisher compatible methods]

Templates to build using dynamic publishing engine and output static file. In this case, $param['build_type'] build_type template can be specified in an array.
When the build_type is 1 or 2, output static file. When the build_type is 3, updates mt_fileinfo record(if the file exists, renamed file with a .static file extension). When the build_type is 4, use publishing queue.


$app->rebuild($args);

Rebuilds your entire blog, indexes and archives; or some subset of your blog,
as specified in the arguments.

    Blog:MT::Blog object corresponding to the blog that you would like to
rebuild. Either this or BlogID is required.
    BlogID:The ID of the blog that you would like to rebuild. Either this or Blog is required.
    ArchiveType:The archive type that you would like to rebuild(Text or Comma separated text). This argument is optional; if not provided, all archive types will be rebuilt.
    NoIndexes:By default it will rebuild the index templates after rebuilding all of the archives; if you do not want to rebuild the index templates, set the value for this argument to a true value. This argument is optional.
    NoStatic:When this value is true, it acts as a hint to the rebuilding routine that static output files need not be rebuilt; the "rebuild" operation
is just to update the bookkeeping that supports dynamic rebuilds(exclude build_type 1 and 2 ).
    Limit:Limit the number of entries to be rebuilt to the last N entries in the blog (if the archive type 'Indivisual' or 'Page').
    Offset:When used with Limit, specifies the entry at which to start rebuilding your individual(page) entry archives (if the archive type 'Individual' or 'Page').

Example:

$app->rebuild( array( 'Blog' => $blog,
                      'ArchiveType' => 'Individual',
                      'NoIndexes' => 1,
                      'Limit' => 10 ) );


$app->rebuild_indexes([$param])

Rebuilds all of the index templates in your blog.

Example:

    $app->rebuild_indexes( array( 'blog' => $blog, 'build_type' => array( 1, 3, 4 ) );


$app->rebuild_archives([$param])

Current blog or $param['blog']'s template that set to $param ['recipe'] archives to rebuild. If $param['updated'], rebuild archives relating to entry update by the active requests.

    $archives = array( 'Index', 'Category', 'Monthly', 'Yearly', 'Weekly', 'Daily', 'Author' );
    $app->rebuild_archives( array( 'blog' => $blog, 'recipe' => $archives, 'updated' => 1 ) );

$param['limit'], $param['offset'] you can specify a range by specifying a target rebuild. By archive, object for which the count will change.

    Index : The number of templates.
    Entry : The number of entries.
    Page  : The number of pages.
    Category : The number of categories.
    Date based archive : The number of archives.
    Author and the date : The number of authors.
    Category and the date : The number of categories.

* PluginName/php/publishers/ArchiveTypeName.php can be rebuilt by installing a custom archive type.


$app->rebuild_entry([$param])

Rebuilds a particular entry in your blog (and its dependencies, if specified).
Saving an entry can have effects on other entries; so after saving, it is often necessary to rebuild other entries, to reflect the changes onto all of the affected archive pages, indexes, etc.

Example:

    $app->rebuild_entry( array( 'entry' => $entry, 'BuildDependencies' => 1 ) );


$app->rebuild_category([$param])

Rebuilds a particular category in your blog.

Example:

    $app->rebuild_category( array( 'category' => $category, 'build_type' => array( 1, 3, 4 ) ) );


$app->rebuild_from_fileinfo($fileinfo);

Rebuild archive by MT::FileInfo object.By build_type, output static file or use publishing queue.


[MT::FileMgr compatible methods]

$app->put($src,$dest[,$type ])

Puts the contents of the file $src in the path $dest. $src can be URL or the path to a local file, $dest must be a path to a file.
$type is optional and defines whether the put is for an uploaded file or for an output HTML file; this tells the what mode to write the files in, what umask settings to use, etc. The two values for $type are 'upload' and 'output', 'output' is the default.
Returns the number of bytes "put" (can be 0).
On error, returns FALSE.


$app->put_data($data,$dest[,$type ])

Puts the block of data $data in the path $dest. $dest should be a path to a file.
$type is optional and defines whether the put is for an uploaded file or for an output HTML file; this tells the what mode to write the files in, what umask settings to use, etc. The two values for $type are 'upload' and 'output', 'output' is the default.
Returns the number of bytes "put" (can be 0).
On error, returns FALSE.


$app->get_data($src)

Gets a block of data from the path $src, returns the block of data.
$src should be a path to a file or URL.
On error, returns FALSE.


$app->mkpath($path[,$perms])

Creates the path $path recursively, in other words, if any of the directories in the path do not exist, they are created. Returns true on success.
On error, returns FALSE.
Put $perms, set permission of the directory to $perms(or $app->config('DirUmask')'s value).


$app->content_is_updated($file,$content)

Returns 1 if the contents of $file differs from the value in $content(Or file does not exists).


$app->delete($file)

Delete the $file. If successful delete, or if $file does not exist or is a symbolic link, return TRUE.


[The other Methods of Class DynamicMTML]

$app->init_mt($mt,$ctx,$blog_id)

Given BlogID to initialize the MT.
Call when $mt->blog_id() is not specified, or initialize specify a different BlogID.


$app->can_do($ctx,$permission)

Return 1 if the logged in user has been given $permission for $ctx->stash('blog').
The difference between Perl API, $permission value is passed to the permission (create_post, edit_templates etc.).


$app->add_lexicon($lang,$array())

Add translation table to $Lexicon_$lang.


$app->plugin_get_config_value($component,$key[,$blog_id])

Return plugin's config value.


$app->escape($string,[$urldecode]);

(If $urldecode is specified, at first, urldecode and) Return result of $mt->db()->escape($string).


$app->get_agent($wants,$like)

Return 'Android' or 'BlackBerry' or 'iPhone'(iPhone or iPod touch) or 'iPad' or 'Palm' or 'Opera Mini'(If $wants is 'Smartphone', return 1) or 'DoCoMo' or 'AU' or 'SoftBank'(If $wants is'Keitai', return 1) or 'PC'.
If $like is specified and if User-Agent contains a $like, return 1.


$app->delete_params($array)

Clears the value of a given CGI parameters.


$app->include_exclude_blogs($ctx,$args)

SQL expression constructing $ctx,$args(array of values specified in the template tag modifiers) automatically generates and returns.

Example:

        $include_exclude_blogs = $app->include_exclude_blogs( $ctx, $args );
        require_once 'class.mt_entry.php';
        $where = " entry_blog_id {$include_exclude_blogs} "
               . " AND entry_class = 'entry' "
               . " AND entry_status = 2 ";
        $extra = array(
            'limit' => 10,
            'offset' => 0,
        ); 
        $_entry = new Entry;
        $entries = $_entry->Find( $where, false, false, $extra );

You can write like this.
        $include_exclude_blogs = $app->include_exclude_blogs( $ctx, $args );
        $terms = array( 'blog_id' => $include_exclude_blogs,
                        'status' => 2 );
        $extra = array(
            'limit' => 10,
            'offset' => 0,
        ); 
        $entries = $app->load( 'Entry', $terms, $extra );


$app->do_conditional($ts)

HTTP header information and compared to $ts, if the content has not been updated to use client caching allows 304 Not Modified header returned.


$app->moved_permanently($url)

301 Moved Permanently header put a redirect to $url.


$app->file_not_found($msg)

Set header status 404 and output error.php or error.html or echo string $msg(default '404 File Not Found.').


$app->access_forbidden($msg)

Set header status 403 and output error.php or error.html or echo string $msg(default '403 Access Forbidden.').


$app->service_unavailable($msg)

Set header status 503 and output error.php or error.html or echo string $msg(default '503 Service Unavailable.').


$app->non_dynamic_mtml($content);

Remove <MTDynamicMTML>*</MTDynamicMTML> block of $content, and expand <MTNonDynamicMTML>*</MTNonDynamicMTML> block of $content, Remove other MT Tag of $content, and return $content.
See also [Continue processing when database connection failed].


$app->is_valid_author($ctx,$author,$password[,$permission])

If $password is a valid password for the $author(string or MT::Author object), return 1.


$app->get_mime_type($extension)

Return mime_type of $extension. If $extension is 'html' and User-Agent contains 'DoCoMo2.0', return 'application/xhtml+xml'. If mime_type does not match, return 'text/plain'.


$app->stash($key[,$val])

Given a key $key, returns the cached value of the key in the cache held by $app. Given a value $value and a key $key, sets the value of the key $key in the cache.
$value can be a simple scalar, a reference, an object, etc.


$app->cache($key[,$val])

Alias for $app->stash().


$app->save_entry($entry[,$params])

Save the entry object. The default value of blog has not been given a few settings, current date if no date specified in the relevant column, and each set of current user of the author unless otherwise specified.
$params['categories'](number(id), object, or array of them),$params['tags'](string(name), object, or array of them) stored as if the related object is specified after saving the entry, and the related mt_fileinfo mt_trackback table is created or updated.


$app->delete_entry($entry)

To delete an entry, and delete the child objects and related objects.


$app->make_atom_id($entry)

Generates a string for mt_entry_atom_id.


$app->can_edit_entry($entry)

Returns 1 when current user have permission to edit the entry.


$app->set_entry_categories($entry,$categories)

Set the $category to the $entry.$categories in numbers, objects, or array of them.


$app->set_tags($object,$tags)

Tagging the object. $tags is string(name), object, or array of them.


$app->fetch_tags($object[,$args]) {

Object tag(MT::Tag object)returns an array.
$args is include_private, sort_by, sort_order can be specified.


$app->get_tag_obj($str[,$args])

Return MT::Tag object that tag name is equal to $str (If none match, generate tag and returns an object).
$args['no_generate']is specified, the created object is not saved.

$app->model($class)

Creates a new object. $entry = $app->model('Entry'), Equivalent to the following that.

    require_once 'class.mt_entry.php';
    $entry = new Entry;


$app->load($class,$terms[,$args])

Load object $class from the datastore using $terms and $args.
If $terms is a number(id), or $args['limit']=1, return the scalar, otherwise, it returns an array.
$terms the value of the array can be specified('OR' Search).

Example:

    $terms = array( 'blog_id' => $blog_id, 'id' => $id );
    $args  = array( 'limit' => 1 );
    $entry = $app->load( 'Entry', $terms, $args );

    You can write like this.
    $entry = $app->load( 'Entry', $id );

Example: (Load 10 Entry by category_id.)

    $terms = array( 'blog_id' => $blog_id,
                    'status'  => 2 );
    $extra = array( 'sort' => 'authored_on',
                    'direction' => 'descend',
                    'limit' => 10 );
    $join = array( 'mt_placement', 'entry_id',
                    array( 'category_id' => $category_id ) );
    $extra[ 'join' ] = $join;
    $entries = $app->load( 'Entry', $terms, $extra );

Example: (Load entry authored_on is today.)

    $current_ts = $app->current_ts( $blog );
    $ymd = substr( $current_ts, 0, 4 ) . '-' .
           substr( $current_ts, 4, 2 ) . '-' .
           substr( $current_ts, 6, 2 ) ;
    // 2011-01-01 or
    // $current_ts = $app->db()->ts2db( $current_ts );
    // $ymd = preg_replace( '/^(([0-9][^0-9]*){8}).*$/', '$1', $current_ts );
    $terms = array( 'blog_id' => $blog->id,
                    'class'   => array( 'entry', 'page' ),
                    'authored_on' => array( 'like' => $ymd . '%' ),
                    'status'  => 2 );
    $entries = $this->load( 'Entry', $terms );

Example: (Load entry using range_incl parameter.)

    $terms = array( 'blog_id' => $blog_id,
                    'authored_on' => array( '20110101000000',
                                            '20110131235959' ),
                    'status'  => 2 );
    $extra = array( 'sort' => 'authored_on',
                    'direction' => 'descend',
                    'range_incl' => array( 'authored_on' => 1 ),
                    // or 'start_val' => '20110131000000',
                    );
    $entries = $this->load( 'Entry', $terms, $extra );


$app->get_by_key($class,$terms)

Load object $class from the datastore using $terms, If no matching object is found, a new object will be constructed(The return value is always a scalar).


$app->exists($class,$terms)

Load object $class from the datastore using $terms, If the object exists, return 1.


$app->column_values($obj)

Returns an array of objects column names and column values.(It's the same as $obj->GetArray()).


$app->column_names($obj)

Returns an array of objects column names(It's the same as $obj->GetAttributeNames()).


$app->touch_blog([$blog])

Update the blog's  mt_blog_children_modified_on to current timestamp.


$app->current_ts([$blog])

Returns the current timestamp(YmdHis format).


$app->build_tmpl($ctx,$text[,$params])

Given $ctx and $text, returns the results from the build $text.
$params['archive_type'], $params ['blog'], $params ['basename'], $params ['fileinfo'] can be specified ($param if you omit, build as index template of the current blog.)


$app->run_tasks([$task_id])

Run the tasks specified in config.yaml or config.php.

Example: Clean up temporary mt-preview files.

<?php
class CleanTemporaryFiles extends MTPlugin {
    var $app;
    var $registry = array(
        'name' => 'CleanTemporaryFiles',
        'id'   => 'CleanTemporaryFiles',
        'key'  => 'cleantemporaryfiles',
        'tasks' => array(
            'CleanTemporaryFiles' => array( 'label' => 'Remove Temporary Files',
                                            'code'  => 'clean_temporary_files',
                                            'frequency' => 3600, ),
        ),
    );
    function clean_temporary_files ( &$app ) {
        $do;
        $ts = time() - 3600;
        $extra = array( 'sort' => 'start',
                        'direction' => 'descend',
                        'start_val' => $ts );
        $files = $this->app->load( 'Session', array( 'kind' => 'TF' ), $extra );
        foreach ( $files as $file ) {
            if ( file_exists( $file->name ) ) {
                unlink( $file->name );
            }
            if (! file_exists( $file->name ) ) {
                $file->Delete();
                $do = 1;
            }
        }
        return $do;
    }
}
?>


$app->run_workers([$workers_id])

Run the task_workers specified in config.yaml or config.php.
Put /mt/plugins/PluginName/php/task_workers/workers_id.php, Run function task_workers_workers_id($app, $jobs).
At this time, $jobs is the array of MT::TheSchwartz::Job object.

Example:Call Rebuild Queue.

<?php
class RebuildQueue extends MTPlugin {
    var $app;
    var $registry = array(
        'name' => 'RebuildQueue',
        'id'   => 'RebuildQueue',
        'key'  => 'rebuildqueue',
        'task_workers' => array(
            'mt_rebuild' => array( 'label' => 'Publishes content.',
                                   'code'  => 'workers_mt_rebuild',
                                   'class' => 'MT::Worker::Publish', ),
        ),
    );
    function workers_mt_rebuild ( &$app, $jobs ) {
        $do;
        $start = time();
        $files = 0;
        foreach ( $jobs as $job ) {
            $uniqkey = intval( $job->uniqkey );
            if ( $uniqkey ) {
                $fileinfo = $app->load( 'FileInfo', $uniqkey );
                if ( $fileinfo ) {
                    if ( $file_path = $fileinfo->file_path ) {
                        if ( $output = $app->rebuild_from_fileinfo( $fileinfo, 1 ) ) {
                            if ( $output != NULL ) {
                                if ( $app->content_is_updated( $file_path, $output ) ) {
                                    $app->put_data( $output, $file_path );
                                    $args = $app->get_args();
                                    $app->run_callbacks( 'rebuild_file', $app->mt(), $app->ctx(),
                                                                                     $args, $output );
                                    $do = 1;
                                    $files ++;
                                }
                            }
                        }
                    }
                }
            }
            $job->Delete();
        }
        if ( $do ) {
            $end = time();
            $time = $end - $start;
            $app->log( $app->translate( '-- set complete ([quant,_1,file,files] in [_2] seconds)',
                                        array( $files, $time ) ) );
        }
        return $do;
    }
}
?>


$app->run_callbacks($callbackname,$mt,$ctx,$args,$content)

Invokes a particular callback, running any associated callback handlers.
The first parameter is the name of the callback to execute.
Include /mt/plugins/PluginName/php/callbacks/pluginname_callbackname.php and do function pluginname_callbackname($callbackname,$mt,$ctx,$args,$content), or method of Class MTPlugin(in config.php).
See also class MTPlugin or config.php section.
Plugin execution order by name ascending.
After the build, $content is build result. Get &$content and $content = 'Foo', can change content.

$args is that. ($args['foo'] is equal to $app->stash('foo'))

    blog_id(Blog's ID)
    conditional(Use conditional get)
    use_cache(Use file cache)
    cache_dir(Path of cache directory)
    file(Request file path)
    base($app->base())
    path($app->path())
    script($app->script())
    request(Current URL that removed the query string)
    param($app->query_string())
    is_secure($app->is_secure())
    url(Current URL)
    contenttype(mime_type)
    extension(File extension)
    build_type('dynamic_mtml(DynamicMTML)','static_text(Text not contains MTML)','binary_data(Binary file)','mt_dynamic(MT's dynamic publishing)')



[Class MTPlugin]

Class MTPlugin is an extension of the Class DynamicMTML.
Can be managed in one file. Template tag, callbacks, plugin settings and more. 
It also provides an interface that allows easy access to the plugin configuration.

    $plugin = $app->component( 'Foo' );
    $foo = $plugin->get_config_value( 'foo', 'blog:1' );
    $foo++;
    $plugin->set_config_value( 'foo', $foo, 'blog:1' );

In plugins/MyFirstPlugin/php/config.php, that(See also  section of config.php.).

<?php
class MyFirstPlugin extends MTPlugin {
    var $app;
    var $registry = array(
        'name' => 'MyFirstPlugin',
        'key'  => 'myfirstplugin',
        'config_settings' => array(
            'PluginConfigSetting' => array( 'default' => 0 ),
        ),
        'settings' => array(
            'example_setting' => array( 'default' => 1 ),
        ),
        'tags' => array(
            'block'    => array( 'example_block'     => 'hdlr_block' ), // ,...
            'function' => array( 'example_function1' => 'hdlr_function' ), // ,...
            'modifier' => array( 'example_modifier1' => 'filter_modifier' ), // ,...
        ),
        'callbacks' => array(
            'build_page' => 'filter_build_page',
            'post_init'  => 'post_init_routine',
        ),
    );
    // Tags or Callbacks...
?>

[MT::Plugin(MT::PluginData) compatible methods]

$plugin->get_config_obj($scope)

Retrieves the MT::PluginData object associated with this plugin and the scope identified (which defaults to 'system' if unspecified).


$plugin->get_config_hash($scope)

Retrieves the configuration data associated with this plugin and returns it a a Perl hash reference. If the scope parameter is not given, the 'system' scope is assumed.


$plugin->reset_config($scope)

This method drops the configuration data associated with this plugin given the scope identified and reverts to th MT defaults.


$plugin->config_vars()

Returns an array of configuration setting names.


$plugin->get_config_value($key,$scope)

Get PluginData $key for plugin.


$plugin->set_config_value($key[,$value,$scope])

They make use of the PluginData table in the database to
store a set of key-value pairs for each plugin.



[dynamicmtml.util.php]

    require_once('dynamicmtml.util.php');
    $res = some_function($param1,$param2);

get_agent($wants,$like)

Alias for $app->get_agent($wants,$like).


get_param($param)

Alias for $app->param($param).


convert2thumbnail($text[,$type],$embed[,$link,$dimension])

Images contained in the text in the format specified $type (optional if the auto(auto-detection)) to convert image is converted to $embed pixel values specified by rewrite the path in the text.
$dimension in the 'width' or 'height' (default 'width'), $link if you have given a number, $link the image link to the image pixels.


path2url($input_uri,$site_url[,$url])

HTML link in the source string in the URL $site_url (blog's site_url) starting from the full http URL to generate returns. Page as a starting point if you know the URL, $url of the page in the URL as a starting point.


referral_site()

return HTTP_REFERER's protocol and the FQDN (example: http://www.google.co.jp/).

referral_search_keyword($ctx[,$array])

If referral website is Google, Bing, MSN, Yahoo! or Goo(or search this site *parameter is 'query' or 'search'), extracts from the search words HTTP_REFERER.
$array is specified, substitute the array of search terms.


make_seo_basename($title,$length)

Extracting from a character string specifying the URL from the first letter only available to URL-encoded string and returns the basename. URL is not available to the character '_' will be replaced.


__get_next_year($timestamp)

Given $timestamp(YmdHis), calculates the timestamp to the start of the next year.


__get_previous_year($timestamp)

Given $timestamp(YmdHis), calculates the timestamp to the start of the previous year.


__get_next_month($timestamp)

Given $timestamp(YmdHis), calculates the timestamp to the start of the next month.


__get_previous_month($timestamp)

Given $timestamp(YmdHis), calculates the timestamp to the start of the previous month.


__get_next_week($timestamp)

Given $timestamp(YmdHis), calculates the timestamp to the start of the next week.


__get_previous_week($timestamp)

Given $timestamp(YmdHis), calculates the timestamp to the start of the previous week.


__get_next_day($timestamp)

Given $timestamp(YmdHis), calculates the timestamp to the start of the next day.


__get_previous_day($timestamp)

Given $timestamp(YmdHis), calculates the timestamp to the start of the previous day.


__date2ts($str)

2011-01-01 to YmdHis(20110101000000);


__umask2permission($umask)

Given umask, returns permissions(three-digit).


__is_hash($array)

Returns 1 an array is hash.


__cat_file($dir,$paths)

Concatenate one or more directory names and a filename to form a complete path ending with a filename.


__cat_dir($dir,$paths)

Alias for __cat_file.


[Configuration of PHP plugin]

Your Plugin directory is /PluginName, put files and directories that.

- /PluginName/php/config.php
    Plugin configuration file.
- /PluginName/php/callbacks/
    Installing the callback plugins.
- /PluginName/php/l10n/
    Installing the language files
- /PluginName/php/publishers/
    Installing the programs for static rebuild by archive types.



[Description of config.php]

Define Plugin Class extends MTPlugin Class(Class name is the name of the directory).
var $registry, settings to specify an array. And do callbacks, add template tags. Method that corresponds to each value in the array.


Example:

<?php
class DynamicMTML_pack extends MTPlugin {
    var $app;
    var $registry = array(
        'name' => 'DynamicMTML',
        'id'   => 'DynamicMTML',
        'key'  => 'dynamicmtml',
        'author_name' => 'Alfasado Inc.',
        'author_link' => 'http://alfasado.net/',
        'version' => '1.5',
        'description' => 'DynamicMTML is PHP extension for Movable Type.',
        'config_settings' => array( // for mt-config.cgi
            'DynamicForceCompile' => array( 'default' => 0 ),
            'DisableCompilerWarnings' => array( 'default' => 0 ),
            'UserSessionTimeoutNoCheck' => array( 'default' => 0 ),
            'DynamicSiteBootstrapper' => array( 'default' => '.mtview.php' ),
        ),
        'settings' => array( // PluginSettings
            'example_setting' => array( 'default' => 1 ),
        ),
        'tags' => array( // Template Tags
            'block'    => array( 'example_block'     => 'hdlr_block' ),
            'function' => array( 'example_function' => 'hdlr_function' ),
            'modifier' => array( 'example_modifier' => 'filter_modifier' ),
        ),
        'tasks' => array( // Tasks
            'FuturePost' => array( 'label' => 'Publish Scheduled Entries',
                                   'code'  => 'publish_scheduled_entries',
                                   'frequency' => 60, ),
            'CleanTemporaryFiles' => array( 'label' => 'Remove Temporary Files',
                                            'code'  => 'clean_temporary_files',
                                            'frequency' => 3600, ),
        ),
        'task_workers' => array( // Workers
            'mt_rebuild' => array( 'label' => 'Publishes content.',
                                   'code'  => 'workers_mt_rebuild',
                                   'class' => 'MT::Worker::Publish', ),
        ),
        'callbacks' => array( // Callbacks
            'build_page' => 'filter_build_page',
            'post_init'  => 'post_init_routine',
        ),
    );

    function post_init_routine ( $mt, &$ctx ) {
        // ... Callback
    }

    function filter_build_page ( $mt, &$ctx, &$args, &$content ) {
        // ... Callback($content is build result.)
        return 1;
    }

    function hdlr_block ( $args, $content, &$ctx, &$repeat ) {
        // ... Block Tag
        return $content;
    }

    function hdlr_if_block ( $args, $content, &$ctx, &$repeat ) {
        // ... Block Tag(Conditional Tag)
        return $ctx->_hdlr_if( $args, $content, $ctx, $repeat, TRUE ); // OR FALSE
    }

    function hdlr_function ( $args, &$ctx ) {
        // ... Function Tag
        return $this->app->translate( 'Welcome to Movable Type' );
    }

    function filter_modifier ( $text, $arg ) {
        // ... Modifier
        return $text;
    }

    function clean_temporary_files ( &$app ) {
        // ... Task(Example:Clean up temporary mt-preview files.)
        return 1;
    }

    function publish_scheduled_entries ( &$app ) {
        // ... Task(Example:Publish future posts.)
        return 1;
    }

    function workers_mt_rebuild ( &$app, $jobs ) {
        // ... Worker(Example:Rebuild Queue.)
        return 1;
    }
}
?>

config_settings value specified for the absence of mt-config.cgi described, plugin_settings value specified for the defaults will not save the settings plug-in.
In the example above,

    $app->config( 'DynamicForceCompile' ), return 0 without describing the mt-config.cgi.
    $app->plugin_get_config_value( 'DynamicMTML','example_setting' ), return 1 it does not save the plugin setting.



[Callbacks(Default Callbacks)]

init_request()

Called at the beginning of .mtview.php.


pre_run($mt,$ctx,$args)

Before MT::get_instance called. $mt and $ctx is NULL. $args is predefined.


post_init($mt,$ctx,$args)

After MT::get_instance called(MT::get_instance is successful).


mt_init_exception($mt,$ctx,$args,$error)

MT::get_instance is failed. $mt and $ctx is NULL.
$error is throw by MTInitException.
For example, return error message or retry MT::get_instance using other mt-config.cgi.


pre_resolve_url($mt,$ctx,$args)

On DynamicMTML(build MTML in file), called before resolve_url.
$args['build_type'] is 'dynamic_mtml'.


post_resolve_url($mt,$ctx,$args)

On DynamicMTML(build MTML in file), called after resolve_url.
$args['build_type'] is 'dynamic_mtml' and $args['fileinfo'] is MT::FileInfo object.


pre_build_page($mt,$ctx,$args)

On DynamicMTML(build MTML in file), Called before file_get_contents(if build_type is 'binary_data', before return contents).
On DynamicMTML(build MTML in file), $app->stash('text') is pre build template(you can change the template).


build_page($mt,$ctx,$args,$content)

Called when $args['build_type'] is 'dynamic_mtml(DynamicMTML)' or 'static_text(Text not contains MTML)' or 'mt_dynamic(MT's dynamic publishing)'.
$content is build result(you can change the content).
Return content after this Callback(except $args['build_type'] is 'binary_data'). 
In this case, the argument $mt is class MT extends the Smarty, $content is content that it contains built, existing Smarty plugin (outputfilter or modifier) can be used as is.

Example:Trim whitespace.

<?php
function pluginname_build_page ( $mt, &$ctx, &$args, &$content ) {
    require_once( 'outputfilter.trimwhitespace.php' );
    $content = smarty_outputfilter_trimwhitespace( $content, $mt );
?>


post_return($mt,$ctx,$args,$content)

Called after return content. If $args['build_type'] is 'binary_data', $content is NULL.


pre_save_cache($mt,$ctx,$args,$content)
Called at cache is enable and before save cache.
$app->stash('cache') is path to cache file, $content is cache data.


take_down($mt,$ctx,$args,$content)

Called at the end of .mtview.php.


take_down_error()
Called at the end of .mtview.php when MT::get_instance failed.


[Callbacks(Rebuild Callbacks)]
Whether called upon to generate static $app->stash ('build_type') can be determined by. During the reconstruction of compatible methods WeblogPublisher, build_type the 'rebuild_static' or 'publish_queue' (for reconstruction of the queue) is.

build_file_filter($mt,$ctx,$args)

This filter is called when Movable Type wants to rebuild a file, but before doing so. This gives plugins the chance to determine whether a file should actually be rebuild in particular situations.


build_page($mt,$ctx,$args,$content)

BuildFile callbacks are invoked just after a file has been built.


build_file($mt,$ctx,$args,$content)

BuildPage callbacks are invoked just after a page has been built, but before the content has been written to the file system.



[Callback Plugin Examples]

# init_request(Required login using Basic Auth.)
# /plugins/PluginName/php/callbacks/pluginname_init_request.php

<?php
function pluginname_init_request () {
    if ( isset( $_SERVER[ 'PHP_AUTH_USER' ] ) && ( $_SERVER[ 'PHP_AUTH_USER' ] 
            === 'username' && $_SERVER[ 'PHP_AUTH_PW' ]
            === 'password' ) ) {
    } else {
        header( 'WWW-Authenticate: Basic realm=""' );
        header( 'HTTP/1.0 401 Unauthorized' );
        exit();
    }
}
?>

# init_request(Use altanative mt-config.cgi.)
# /plugins/PluginName/php/callbacks/pluginname_init_request.php

<?php
function pluginname_init_request () {
    global $mt_config;
    global $mt_dir;
    $new_config = $mt_dir . DIRECTORY_SEPARATOR . 'mt-alt-config.cgi';
    if ( file_exists ( $new_config ) ) {
        $mt_config = $new_config;
    }
}
?>

# init_request(Enable the dynamic building by Perl.)
# /plugins/PluginName/php/callbacks/pluginname_init_request.php

<?php
function pluginname_init_request () {
    global $mt_dir;
    global $app;
    $perlbuilder = $mt_dir . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . 'rebuild-from-fi';
    if ( file_exists( $perlbuilder ) ) {
        $app->stash( 'perlbuild', 1 );
    }
    $perlbuilder = $mt_dir . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . 'build-template-file';
    if ( file_exists( $perlbuilder ) ) {
        $app->stash( 'perlbuild', 1 );
    }
?>

# mt_init_exception(Show error message on DebugMode.)
# /plugins/PluginName/php/callbacks/pluginname_mt_init_exception.php

<?php
function pluginname_mt_init_exception ( &$mt, &$ctx, &$args, $error ) {
    global $app;
    if ( $app->config( 'DebugMode' ) ) {
        echo htmlspecialchars( $error );
        exit();
    }
}
?>

# mt_init_exception(Retry get_instance using mt-alt-config.cgi.)
# /plugins/PluginName/php/callbacks/pluginname_mt_init_exception.php

<?php
function pluginname_mt_init_exception ( &$mt, &$ctx, &$args, $error ) {
    global $app;
    global $mt_dir;
    $config = $mt_dir . DIRECTORY_SEPARATOR . 'mt-alt-config.cgi';
    if ( file_exists ( $config ) ) {
        global $mt_config;
        global $blog_id;
        $mt_config = $config;
        try {
            $mt = MT::get_instance( $blog_id, $mt_config );
        } catch ( MTInitException $e ) {
            $mt = NULL;
        }
        if ( isset ( $mt ) ) {
            $app->configure( $mt_config );
        }
    }
?>

# post_init(Login(check username and password, set $app->user and login) and logout.)
# /plugins/PluginName/php/pluginname_post_init.php

<?php
function pluginname_post_init ( $mt, &$ctx, &$args ) {
    if ( $app->mode() == 'login' ) {
       $app->login();
    } elsif ( $app->mode() == 'logout' ) {
       $app->logout();
    }
?>

# post_init(Required login. Basic Auth using MT.)
# /plugins/PluginName/php/callbacks/pluginname_post_init.php

<?php
function pluginname_post_init ( $mt, &$ctx, &$args ) {
    $app = $ctx->stash( 'bootstrapper' );
    if ( isset( $_SERVER[ 'PHP_AUTH_USER' ] ) &&
       ( $app->is_valid_author( $ctx, $_SERVER[ 'PHP_AUTH_USER' ],
                             $_SERVER[ 'PHP_AUTH_PW' ] ) ) ) {
    } else {
        header( 'WWW-Authenticate: Basic realm=""' );
        header( 'HTTP/1.0 401 Unauthorized' );
        exit();
    }
}
?>


# post_init(Required login. Using MT Commenter Auth.)
# /plugins/PluginName/php/callbacks/pluginname_post_init.php

<?php
function pluginname_post_init ( $mt, &$ctx, &$args ) {
    $app = $ctx->stash( 'bootstrapper' );
    if (! $timeout = $mt->config( 'UserSessionTimeout' ) ) {
        $timeout = 14400;
    }
    $client_author = $app->get_author( $ctx, $timeout, 'comment' );
    if (! $client_author ) {
        $url = $args[ 'url' ];
        $return_url  = $mt->config( 'CGIPath' );
        $return_url .= $mt->config( 'CommentScript' );
        $return_url .= '?__mode=login&blog_id=' . $ctx->stash( 'blog_id' );
        $return_url .= '&return_url=' . rawurlencode( $url );
        $app->redirect( $return_url );
        exit();
    }
?>

# post_init(http://example.com/entry_1/JapaneseTitle/ => http://example.com/entry_1/index.html)
# See also => pluginname_pre_build_page.php
# Setting  : Individual Archive Mapping => entry_<$mt:EntryID$>/%i
#            Category Archive Mapping   => category_<$mt:CategoryID$>/%i
#            <$mt:EntryPermalink$>      => <$mt:EntryPermalink$><$mt:EntryTitle make_seo_basename="50"$>/
#            <$mt:CategoryArchiveLink$> => <$mt:CategoryArchiveLink$><$mt:CategoryLabel make_seo_basename="50"$>/
# /plugins/PluginName/php/callbacks/pluginname_post_init.php

<?php
function pluginname_post_init ( $mt, &$ctx, &$args ) {
    $app = $ctx->stash( 'bootstrapper' );
    $file = $app->stash( 'file' );
    $url  = $app->stash( 'url' );
    $request = $app->stash( 'request' );
    if (! file_exists ( $file ) ) {
        $file = $app->path2index( $file, 'index.html' );
        if ( file_exists ( $file ) ) {
            $request = $app->path2index( $request );
            $url     = $app->path2index( $url );
            $app->stash( 'file', $file );
            $app->stash( 'request', $request );
            $app->stash( 'url', $url );
            $app->stash( 'contenttype', 'text/html' );
            $app->stash( 'extension', 'html' );
            $cache = $app->cache_filename( $ctx->stash( 'blog_id' ), $file, $app->query_string );
            $app->stash( 'cache', $cache );
        }
    }
?>

# pre_build_page(http://example.com/entry_1/ => 301 Moved Permanently header and redirect http://example.com/entry_1/EntryTitle_or_CategoryLabel/ (<$mt:entrypermalink make_seo_basename="50"$>))
# See also => pluginname_post_init.php
# /plugins/PluginName/php/callbacks/pluginname_pre_build_page.php

<?php
function pluginname_pre_build_page ( $mt, &$ctx, &$args ) {
    $app = $ctx->stash( 'bootstrapper' );
    $request = $app->stash( 'request' );
    if ( preg_match( '!/$!', $request ) ) {
        $file = $app->stash( 'file' );
        $blog_id = $app->blog_id;
        if ( file_exists( $file )  && preg_match( '!/index\.html$!', $file ) ) {
            $fileinfo = $app->stash( 'fileinfo' );
            require_once( 'MTUtil.php' );
            if (! isset( $fileinfo ) ) {
                $fileinfo = $mt->db()->resolve_url( $mt->db()->escape( urldecode( $request ) ),
                                                    $blog_id, array( 1, 2, 4 ) );
            }
            if ( isset( $fileinfo ) ) {
                $app->stash( 'fileinfo', $fileinfo );
                $entry_id = $fileinfo->entry_id;
                $category_id = $fileinfo->category_id;
                if ( $entry_id || $category_id ) {
                    $obj = NULL;
                    if ( $entry_id ) {
                        if ( $fileinfo->archive_type == 'Page' ) {
                            $obj = $mt->db()->fetch_page( $entry_id );
                        } else {
                            $obj = $mt->db()->fetch_entry( $entry_id );
                        }
                    } elseif ( $category_id ) {
                        $obj = $mt->db()->fetch_category( $category_id );
                    }
                    if ( isset( $obj ) ) {
                        $title = NULL;
                        if ( $entry_id ) {
                            $title = $obj->title;
                        } elseif ( $category_id ) {
                            $title = $obj->label;
                        }
                        $title = strip_tags( $title );
                        if ( $title ) {
                            require_once ( 'dynamicmtml.util.php' );
                            $title = make_seo_basename( $title, 50 );
                            $url = $request . $title . '/';
                            $app->moved_permanently( $url );
                            exit();
                        }
                    }
                }
            }
        }
    }
}
?>

# build_page(Convert to thumbnail for keitai browser.)
# /plugins/PluginName/php/callbacks/pluginname_build_page.php

<?php
function pluginname_build_page ( $mt, &$ctx, &$args, &$content ) {
    $app = $ctx->stash( 'bootstrapper' );
    require_once ( 'dynamicmtml.util.php' );
    if ( $app->get_agent( 'keitai' ) ) {
        $type = 'auto';
        $scope = 'width';
        // $agent = $app->get_agent();
        // if ( $agent == 'DoCoMo' ) {
        //     $type = 'gif';
        // } else {
        //     $type = 'png';
        // }
        $content = convert2thumbnail( $content, $type, 100, 480, $scope );
    }
}
?>

# build_page(Convert to Shift_JIS for keitai browser.)
# /plugins/PluginName/php/callbacks/pluginname_build_page.php

<?php
function pluginname_build_page ( $mt, &$ctx, &$args, &$content ) {
    if ( $app->get_agent( 'keitai' ) ) {
        $charset = strtolower( $ctx->mt->config( 'PublishCharset' ) );
        $charset = preg_replace( '/\-/', '_', $charset );
        if ( $charset != 'shift_jis' ) {
            $pattern = '/<\?xml\s*version\s*=\s*"1.0"\s*encoding\s*=\s*"UTF-8"\s*\?>/s';
            $replace = '<?xml version="1.0" encoding="Shift_JIS"?>';
            $content = preg_replace( $pattern, $replace, $content );
            $pattern = '/<meta\s*http\-equiv\s*=\s*"Content\-Type"\s*content\s*=\s*"text\/html;\s*charset=UTF\-8"\s*\/>/s';
            $replace = '<meta http-equiv="Content-Type" content="text/html; charset=Shift_JIS" />';
            $content = preg_replace( $pattern, $replace, $content );
            $content = mb_convert_encoding( $content, 'SJIS-WIN', 'UTF-8' );
        }
    }
?>

# build_page(Displays highlighting site visitor's search word.)
# /plugins/PluginName/php/callbacks/pluginname_build_page.php

<?php
function pluginname_build_page ( $mt, &$ctx, &$args, &$content ) {
    if ( preg_match( '/(^.*?<body.*?>)(.*?$)/si', $content, $html ) ) {
        $head = $html[1];
        $text = $html[2];
        require_once ( 'dynamicmtml.util.php' );
        $tag_start  = '<strong class="search-word">';
        $tag_end    = '</strong>';
        $qtag_start = preg_quote( $tag_start, '/' );
        $qtag_end   = preg_quote( $tag_end, '/' );
        $keywords   = array();
        $phrase = referral_search_keyword( $ctx, $keywords );
        foreach ( $keywords as $keyword ) {
            $keyword = htmlspecialchars( $keyword );
            $keyword = trim( $keyword );
            $keyword = preg_quote( $keyword, '/' );
            $pattern1 = "/(<[^>]*>[^<]*?)($keyword)/i";
            $replace1 = '$1' . $tag_start. '$2' . $tag_end;
            $pattern2 = "/($qtag_start)$qtag_start($keyword)$qtag_end($qtag_end)/i";
            $replace2 = '$2$3$4';
            $i = 0;
            while (! $end ) {
                $original = $text;
                $text = preg_replace( $pattern1, $replace1, $text );
                //Nest tag
                $text = preg_replace( $pattern2, $replace2, $text );
                if ( $text == $original ) {
                    $end = 1;
                }
                $i++;
                //Infinite loop
                if ( $i > 20 ) $end = 1;
            }
            unset( $end );
        }
        $content = $head . $text;
    }
?>

# build_page(Phone number replace to the link.)
# /plugins/PluginName/php/callbacks/pluginname_build_page.php

<?php
function pluginname_build_page ( $mt, &$ctx, &$args, &$content ) {
    if ( $app->get_agent( 'keitai' ) || $app->get_agent( 'smartphone' ) ) {
        if ( preg_match( '/(^.*?<body.*?>)(.*?$)/si', $content, $html ) ) {
            $head = $html[1];
            $text = $html[2];
            require_once ( 'dynamicmtml.util.php' );
            $tag_1 = '<a href ="tel:';
            $tag_2 = '">';
            $tag_3 = '</a>';
            $pattern1 = '/(<[^>]*>[^<]*?)(0\d{1,4}-\d{1,4}-\d{3,4})/';
            $replace1 = '$1' . $tag_1 . '$2' . $tag_2 . '$2' . $tag_3;
            $pattern2 = '/(<a.*?>\/*)<a.*?>(0\d{1,4}-\d{1,4}-\d{3,4})<\/a>([^<]*?<\/a>)/';
            $replace2 = '$2$3$4';
            $i = 0;
            while (! $end ) {
                $original = $text;
                $text = preg_replace( $pattern1, $replace1, $text );
                //Nest tag
                $text = preg_replace( $pattern2, $replace2, $text );
                if ( $text == $original ) {
                    $end = 1;
                }
                $i++;
                //Infinite loop
                if ( $i > 20 ) $end = 1;
            }
            unset( $end );
            $content = $head . $text;
        }
    }
?>

# build_page(Trim whitespace.)
# /plugins/PluginName/php/callbacks/pluginname_build_page.php

<?php
function pluginname_build_page ( $mt, &$ctx, &$args, &$content ) {
    require_once( 'outputfilter.trimwhitespace.php' );
    $content = smarty_outputfilter_trimwhitespace( $content, $mt );
?>

# post_return(Record access log.)
# /plugins/PluginName/php/callbacks/pluginname_post_return.php

<?php
function pluginname_post_return ( $mt, &$ctx, &$args, &$content ) {
    $app = $ctx->stash( 'bootstrapper' );
    $url = $app->stash( 'url' );
    if ( $url ) {
        $app->log( $url );
    }
}
?>

# post_return(Record Search Engine's keyword to access log.)
# /plugins/PluginName/php/callbacks/pluginname_post_return.php

<?php
function pluginname_post_return ( $mt, &$ctx, &$args, &$content ) {
    $keyword = referral_search_keyword( $ctx );
    if ( $keyword ) {
        $keyword = trim( $keyword );
        $url = $app->stash( 'url' );
        $referral_site = referral_site();
        $app->log( "url : $url\nreferral_site : $referral_site\nkeyword : $keyword" );
    }
}
?>


[About language file]

If Japanese(ja), in /plugins/PluginName/php/l10n/l10n_ja.php

<?php
$Lexicon = array(
    'Hi, [_1]' => 'こんにちは、[_1]さん',
    'Username' => 'ユーザー名',
    'Password' => 'パスワード',
    'Sign in'  => 'サインイン',
    'Sign out' => 'サインアウト',
);
?>

This table is loaded at initialization, MTTrans tag or $app->translate() returns the result of the language translation of the user.


[Rebuild archves]

Put /plugins/PluginName/php/publishers/, by installing a program for rebuilding the archive below $app->rebuild_archives you can rebuild the archive file contains a static publishing.
See also $app->rebuild_archives.

Example:Rebuild all categories of blog.

/plugins/PluginName/php/publishers/AllCategories.php

<?php
    $categories = $this->load( 'Category', array( 'blog_id' => $blog->id );
    foreach ( $categories as $category ) {
        if (! $this->rebuild_category( array( 'category' => $category,
                                              'build_type' => $build_type ) ) ) {
            return $this->ctx()->error( 
                $this->translate( 'Publish error at archivetype [_1].',
                    $this->translate( 'Category' ) ) );
        }
        $do = 1;
    }
?>

Put this file, you can rebuild archives by calling all categories as follows.

$app->rebuild_archives( array( 'blog' => $blog,
                               'recipe' => ( 'AllCategories' )
                               'build_type' => array( 1, 2, 3, 4 ) ) );
