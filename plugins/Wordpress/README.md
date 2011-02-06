# About MTPlugin Wordpress

## Getting Started

1. Install DynamicMTML.pack.
2. Put Wordpress to MT\_DIR/plugins
3. Edit MT\_DIR/plugins/Wordpress/wp-config.cgi(Edit DATABASE SETTINGS).
4. Edit MT\_DIR/plugins/Wordpress/wp_prefix.php(Edit WP\_PREFIX).

**It works with DynamicMTML or MT's dynamic publishing or Alfie.**

*Example:*

    <mt:DynamicMTML>
        <mt:wp:Entries limit="20" category="Foo">
            <mt:if name="__first__"><ul></mt:if>
                <li class="<mt:if name="__odd__">odd<mt:else>even</mt:if>">
                    <a href="<$mt:wp:EntryPermalink>">
                        <$mt:wp:EntryTitle escape="html"$>
                    </a>
                    <mt:wp:EntryCategories glue=",">
                        <mt:if name="__first__">Category:</mt:if>
                        <a href="<mt:wp:CategoryLink>">
                            <mt:wp:CategoryLabel>
                        </a>
                    </mt:wp:EntryCategories>
                    <mt:wp:EntryTags glue=",">
                        <mt:if name="__first__">Tags:</mt:if>
                        <a href="<mt:wp:TagLink>">
                            <mt:wp:TagName>
                        </a>
                    </mt:wp:EntryTags>
                        Author:<$mt:wp:EntryAuthorDisplayName$>
                        Date:<$mt:wp:EntryDate format="%B %e, %Y %I:%M %p"$>
                </li>
            <mt:if name="__last__"></ul></mt:if>
        </mt:wp:Entries>
    </mt:DynamicMTML>

## Template Tags

---------------------------------------

**mt:wp:Entries(Block Tag)**

*Alias: mt:wp:get\_posts, mt:wp:posts*

A block tag which iterates over a list of published posts from a blog.

*Attributes*

    id           : Outputs a single post matching the given post ID.
    category     : Category name
    category_id  : Category id
    tag          : Tag
    type         : post_type or '*'(all). 'post' is the default.
    status       : status or '*'(all). 'publish' is the default.
    sort_by      : Defines the data to sort posts.
                   The default value is "ID".
    sort_order   : Accepted values are "ascend" and "descend".
                   Default order is descend.
    offset       : Used in coordination with limit,
                   starts N posts from the start of the list.
    limit        : Load N posts of the blog. N is a positive integer.
    lastn        : Display the last N posts of the blog.
                   N is a positive integer.
    glue         : If specified, this string will be placed in between
                   each "row" of data produced by the loop.

*Example:*

    <mt:wp:Entries>
        <$mt:wp:EntryTitle$>
        <$mt:wp:EntryBody$>
    </mt:wp:Entries>

*Example:*

    <mt:wp:Entries limit="20" category="Foo">
        <mt:if name="__first__"><ul></mt:if>
            <li class="<mt:if name="__odd__">odd<mt:else>even</mt:if>">
                <a href="<$mt:wp:EntryPermalink>">
                    <$mt:wp:EntryTitle escape="html"$>
                            (<$mt:wp:EntryDate format="%B %e, %Y %I:%M %p"$>)
                </a>
            </li>
        <mt:if name="__last__"></ul></mt:if>
    </mt:wp:Entries>

*You can write like this.*

    <mt:wp:get_posts limit="20" category="Foo">
        <mt:if name="__first__"><ul></mt:if>
            <li class="<mt:if name="__odd__">odd<mt:else>even</mt:if>">
                <a href="<$mt:wp:the_permalink>">
                    <$mt:wp:the_title escape="html"$>
                            (<$mt:wp:the_date format="%B %e, %Y %I:%M %p"$>)
                </a>
            </li>
        <mt:if name="__last__"></ul></mt:if>
    </mt:wp:get_posts>

---------------------------------------

**mt:wp:Categories(Block Tag)**

*Alias: mt:wp:list\_categories*

Produces a list of categories defined for the current blog.

*Example:*

    <mt:wp:Categories>
        <$mt:wp:CategoryLabel escape="html"$>
        <$mt:wp:CategoryDescription escape="html"$>
    </mt:wp:Categories>

*Attributes*

    hide_empty   : Toggles the display of categories with no posts.
    toplevel     : Same as mt:wp:SubCategories.
    sort_by      : Defines the data to sort posts.
                   The default value is "ID".
    sort_order   : Accepted values are "ascend" and "descend".
                   Default order is descend.
    offset       : Used in coordination with limit,
                   starts N posts from the start of the list.
    limit        : Load N posts of the blog. N is a positive integer.
    lastn        : Display the last N posts of the blog.
                   N is a positive integer.
    glue         : If specified, this string will be placed in between
                   each "row" of data produced by the loop.

---------------------------------------

**mt:wp:SubCategories(Block Tag)**

A specialized version of the mt:wp:Categories block tag that respects the hierarchical structure of categories.

    hide_empty   : Toggles the display of categories with no posts.
    sort_by      : Defines the data to sort posts.
                   The default value is "ID".
    sort_order   : Accepted values are "ascend" and "descend".
                   Default order is descend.
    offset       : Used in coordination with limit,
                   starts N posts from the start of the list.
    limit        : Load N posts of the blog. N is a positive integer.
    lastn        : Display the last N posts of the blog.
                   N is a positive integer.
    glue         : If specified, this string will be placed in between
                   each "row" of data produced by the loop.

*Example:(See also mt:wp:SubCatsRecurse, mt:wp:SubCatisFirst and mt:wp:SubCatisLast.)*

    <mt:wp:SubCategories hide_empty="1">
    <mt:wp:SubCatisFirst><ul></mt:wp:SubCatisFirst>
        <li>
            <a href="<mt:wp:CategoryLink>">
                <mt:wp:CategoryLabel escape="html">
                            (<$mt:wp:CategoryCount$>)
            </a>
        <mt:wp:SubCatsRecurse></li>
    <mt:wp:SubCatisLast></ul></mt:wp:SubCatisLast>
    </mt:wp:SubCategories>

---------------------------------------

**mt:wp:SubcatIsFirst(Block Tag)**

The contents of this container tag will be displayed when the first category listed by a mt:SubCategories loop tag is reached.
Use mt:wp:SubCatIsLast for conditioning content at the end of the loop.

---------------------------------------

**mt:wp:SubcatIsLast(Block Tag)**

The contents of this container tag will be displayed when the last category listed by a mt:SubCategories loop tag is reached.
Use mt:wp:SubCatIsFirst for conditioning content at the beginning of the loop.

---------------------------------------

**mt:wp:EntryCategories(Block Tag)**

*Alias: mt:wp:get\_the\_category*

A container tag that lists all of the categories (primary and secondary) to which the post is assigned.
This tagset creates a category context within which any category tags may be used.

---------------------------------------

**mt:wp:Tags(Block Tag)**

Produces a list of tags defined for the current blog.

*Example:*

    <mt:wp:Tags hide_empty="1">
        <mt:if name="__first__"><ul></mt:if>
            <li class="<mt:if name="__odd__">odd<mt:else>even</mt:if>">
                <a href="<mt:wp:TagArchiveLink>">
                    <$mt:wp:TagName escape="html"$>
                                (<$mt:wp:TagCount escape="html"$>)
                </a>
            </li>
        <mt:if name="__last__"></ul></mt:if>
    </mt:wp:Tags>

*Attributes*

    hide_empty   : Toggles the display of tags with no posts.
    sort_by      : Defines the data to sort posts.
                   The default value is "ID".
    sort_order   : Accepted values are "ascend" and "descend".
                   Default order is descend.
    offset       : Used in coordination with limit,
                   starts N posts from the start of the list.
    limit        : Load N posts of the blog. N is a positive integer.
    lastn        : Display the last N posts of the blog.
                   N is a positive integer.
    glue         : If specified, this string will be placed in between
                   each "row" of data produced by the loop.

---------------------------------------

**mt:wp:EntryTags(Block Tag)**

*Alias: mt:wp:get\_the\_tags*

A container tag that lists all of the tags to which the post is assigned.
This tagset creates a tag context within which any tag's tags may be used.

---------------------------------------

**mt:wp:EntryNext(Block Tag)**

*Alias: mt:wp:next\_post*

A block tag providing a context for the post immediately following the current post in context (in terms of date).

---------------------------------------

**mt:wp:EntryPrevious(Block Tag)**

*Alias: mt:wp:previous\_post*

A block tag providing a context for the post immediately preceding the current post in context (in terms of date).

---------------------------------------

**mt:wp:EntryIfAllowComments(Block Tag)**

*Alias: mt:wp:IfCommentsActive*

Conditional tag that is positive when the post in context is configured to allow commenting.

---------------------------------------

**mt:wp:EntryIfAllowPings(Block Tag)**

*Alias: mt:wp:IfPingsAccepted*

Conditional tag that is positive when pings are allowed for and the post.

---------------------------------------

**mt:wp:ArchiveList(Block Tag)**

A container tag representing a list of 'Monthly' or 'Yearly' archive pages.

*Attributes*

    archive_type : 'Monthly' or 'Yearly'
    sort_order   : Accepted values are "ascend" and "descend".
                   Default order is descend.
    lastn        : Display the last N posts of the blog.
                   N is a positive integer.
    glue         : If specified, this string will be placed in between
                   each "row" of data produced by the loop.

*Example:*

    <mt:wp:ArchiveList archive_type="Monthly">
    <mt:if name="__first__"><ul></mt:if>
        <li>
            <a href="<mt:wp:ArchiveLink archive_type="Monthly">">
                <mt:wp:ArchiveTitle format="%b %Y">
                                (<mt:wp:ArchiveCount>)
            </a>
        </li>
    <mt:if name="__last__"></ul></mt:if>
    </mt:wp:ArchiveList>

---------------------------------------

**mt:wp:BlogInfo(Function Tag)**

Returns information about your blog(siteurl, blogname, blogdescription, admin\_email and others).

*Example:*

    <mt:wp:BlogInfo name="blogdescription" filters="__default__">

---------------------------------------

**mt:wp:BlogName(Function Tag)**

Outputs the name of the blog.

---------------------------------------

**mt:wp:BlogURL(Function Tag)**

*Alias: mt:wp:site\_url*

Outputs the Site URL field of the blog.

---------------------------------------

**mt:wp:EntryID(Function Tag)**

*Alias: mt:wp:the\_ID*

Ouptuts the numeric ID for the current post in context.

---------------------------------------

**mt:wp:EntryTitle(Function Tag)**

*Alias: mt:wp:the\_title*

Outputs the title of the current post in context.

---------------------------------------

**mt:wp:EntryBody(Function Tag)**

*Alias: mt:wp:the\_content*

Outputs the "main" text of the current post in context.

---------------------------------------

**mt:wp:EntryExcerpt(Function Tag)**

*Alias: mt:wp:the\_excerpt*

Ouputs the value of the excerpt field of the current post in context.

---------------------------------------

**mt:wp:EntryDate(Function Tag)**

*Alias: mt:wp:the\_date*

Outputs the 'authored' date of the current post in context.
[See the Date tag for supported attributes.](http://www.movabletype.org/documentation/appendices/tags/date.html)

---------------------------------------

**mt:wp:EntryModifiedDate(Function Tag)**

*Alias: mt:wp:the\_modified*

Outputs the modification date of the current post in context.
[See the Date tag for supported attributes.](http://www.movabletype.org/documentation/appendices/tags/date.html)

---------------------------------------

**mt:wp:EntryAuthorDisplayName(Function Tag)**

Outputs the display name of the author for the current post in context.

---------------------------------------

**mt:wp:EntryAuthorID(Function Tag)**

*Alias: mt:wp:the\_author*

Outputs the numeric ID of the author for the current post in context.

---------------------------------------

**mt:wp:EntryPermalink(Function Tag)**

*Alias: mt:wp:the\_permalink*

An absolute URL pointing to the archive page containing this post(Wordpress's default, http://your.domain.com/wordpress/?p=1).

---------------------------------------

**mt:wp:EntryGUID(Function Tag)**

*Alias: mt:wp:the\_guid*

Ouputs the value of the guid field of the current post in context.

---------------------------------------

**mt:wp:EntryCommentCount(Function Tag)**

*Alias: mt:wp:the\_comment\_count*

Outputs the number of comments for the current post.

---------------------------------------

**mt:wp:EntryMeta(Function Tag)**

*Alias: mt:wp:the\_meta*

Returns the custom field(attribute 'key' specified) value of the current post in context.

---------------------------------------

**mt:wp:EntriesCount(Function Tag)**

*Alias: mt:wp:count\_posts*

Returns the count of entries.

*Attributes*

    category     : Category name
    category_id  : Category id
    tag          : Tag
    type         : post_type or '*'(all). 'post' is the default.
    status       : status or '*'(all). 'publish' is the default.

---------------------------------------

**mt:wp:CategoryLabel(Function Tag)**

*Alias: mt:wp:cat\_name*

The name of the category in context.

---------------------------------------

**mt:wp:CategoryID(Function Tag)**

*Alias: mt:wp:cat\_ID*

The numeric ID of the category in context.

---------------------------------------

**mt:wp:CategorySlug(Function Tag)**

Ouputs the value of the slug field of the current category in context.

---------------------------------------

**mt:wp:CategoryDescription(Function Tag)**

Ouputs the description of the current category in context.

---------------------------------------

**mt:wp:CategoryCount(Function Tag)**

Returns the count of entries of the current category in context.

---------------------------------------

**mt:wp:CategoryArchiveLink(Function Tag)**

*Alias: mt:wp:CategoryLink, mt:wp:get_category_link*

An absolute URL pointing to the archive page of the current category in context(Wordpress's default, http://your.domain.com/wordpress/?cat=1).

---------------------------------------

**mt:wp:SubCatsRecurse(Function Tag)**

Recursively call the SubCategories container with the subcategories of the category in context.

*Attributes*

    max_depth    : The following code prints out a recursive list of
                   categories/subcategories, linking those with posts
                   assigned to their category archive pages.

---------------------------------------

**mt:wp:TagName(Function Tag)**

The name of the tag in context.

---------------------------------------

**mt:wp:TagID(Function Tag)**

The numeric ID of the tag in context.

---------------------------------------

**mt:wp:TagSlug(Function Tag)**

Ouputs the value of the slug field of the current tag in context.

---------------------------------------

**mt:wp:TagDescription(Function Tag)**

Ouputs the description of the current tag in context.

---------------------------------------

**mt:wp:TagCount(Function Tag)**

Returns the count of entries of the current tag in context.

---------------------------------------

**mt:wp:TagArchiveLink(Function Tag)**

*Alias: mt:wp:TagLink, mt:wp:get\_tag\_link*

An absolute URL pointing to the archive page of the current tag in context(Wordpress's default, http://your.domain.com/wordpress/?tag=foo).

---------------------------------------

**mt:wp:ArchiveTitle(Function Tag)**

In mt:wp:ArchiveList block tag, Returns the descriptive title of the current archive.
The value returned from this tag will vary based on the archive type:

    Monthly : The range of dates in the week in "Month YYYY" form.
    Yearly  : The range of dates in the week in "YYYY" form.(Unsupported)

[See the Date tag for supported attributes.](http://www.movabletype.org/documentation/appendices/tags/date.html)

---------------------------------------

**mt:wp:ArchiveCount(Function Tag)**

In mt:wp:ArchiveList block tag, Returns the count of entries of the current archive in context.

---------------------------------------

**mt:wp:ArchiveLink(Function Tag)**

In mt:wp:ArchiveList block tag, An absolute URL pointing to the archive page('Monthly') of the current archive in context(Wordpress's default, http://your.domain.com/wordpress/?m=yyyymm).

*Attributes*

    archive_type : 'Monthly' or 'Yearly'(Unsupported)

---------------------------------------
