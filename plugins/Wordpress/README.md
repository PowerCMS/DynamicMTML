# About MTPlugin Wordpress

## Synopsis

MTML(Movable Type Markup Language) for Wordpress.
This plugin works on DynamicMTML or MT's dynamic publishing.

## Getting Started

1. Install addons/DynamicMTML.pack and plugins/Wordpress.
2. Edit wp-config.cgi(DATABASE SETTINGS).
3. Edit wp-prefix.php(WP_PREFIX).

## Template Tags

*Example:*

    <mt:wp:Entries category="Foo" limit="20">
        <mt:if name="__first__"><ul></mt:if>
        <li class="<mt:if name="__odd__">odd<mt:else>even</mt:if>">
            <a href="<$mt:wp:EntryPermalink$>">
                <$mt:wp:EntryTitle escape="html"$>
                (<$mt:wp:EntryAuthorDisplayName$> / <$mt:wp:EntryDate format="%b %Y"$>)
            </a>
            <mt:wp:EntryCategories glue=",">
                <mt:if name="__first__">Categories:</mt:if>
                <a href="<$mt:wp:CategoryLink$>">
                    <$mt:wp:CategoryLabel$>
                </a>
            </mt:wp:EntryCategories>
            <mt:wp:EntryTags glue=",">
                <mt:if name="__first__">Tags:</mt:if>
                <a href="<$mt:wp:TagLink$>">
                    <$mt:wp:TagName$>
                </a>
            </mt:wp:EntryTags>
        </li>
        <mt:if name="__last__"></ul></mt:if>
    </mt:wp:Entries>

---------------------------------------

**mt:wp:Entries(Block Tag)**

*Alias: mt:wp:get\_posts, mt:wp:posts*

A block tag which iterates over a list of published posts from a blog.

*Attributes:*

    id            : Outputs a single post matching the given post ID.
    category      : Category name
    category_id   : Category id
    tag           : Tag
    status        : Status text or '*'(all), 'publish' is the default.
    type          : Type text or '*'(all), 'post' is the default.
    sort_by       : Defines the data to sort posts.
                    The default value is "ID".
    sort_order    : Accepted values are "ascend" and "descend".
                    Default order is descend.
    lastn         : Display the N posts. N is a positive integer.
    offset        : Used in coordination with limit,
                    starts N posts from the start of the list.
    limit         : Load the N posts. N is a positive integer.
    glue          : A string that is output between post.

*Example:*

    <mt:wp:Entries>
        <$mt:wp:EntryTitle$>
        <$mt:wp:EntryBody$>
    </mt:wp:Entries>

*Example:*

    <mt:wp:Entries category="Foo" limit="20">
        <mt:if name="__first__"><ul></mt:if>
        <li>
            <a href="<$mt:wp:EntryPermalink$>">
                <$mt:wp:EntryTitle escape="html"$>
            </a>
        </li>
        <mt:if name="__last__"></ul></mt:if>
    </mt:wp:Entries>

*You can write like this.*

    <mt:wp:get_posts category="Foo" limit="20">
        <mt:if name="__first__"><ul></mt:if>
        <li>
            <a href="<$mt:wp:the_permalink$>">
                <$mt:wp:the_title escape="html"$>
            </a>
        </li>
        <mt:if name="__last__"></ul></mt:if>
    </mt:wp:Entries>

---------------------------------------

**mt:wp:EntryNext(Block Tag)**

*Alias: mt:wp:next\_post*

A block tag providing a context for the post immediately following the current entry in context (in terms of authored date).

---------------------------------------

**mt:wp:EntryPrevious(Block Tag)**

*Alias: mt:wp:previous\_post*

A block tag providing a context for the post immediately preceding the current entry in context (in terms of authored date).

---------------------------------------

**mt:wp:EntryPrevious(Block Tag)**

*Alias: mt:wp:previous\_post*

A block tag providing a context for the post immediately preceding the current entry in context (in terms of authored date).

---------------------------------------

**mt:wp:Categories(Block Tag)**

*Alias: mt:wp:list\_categories*

A block tag which iterates over a list of category from a blog.

*Attributes:*

    hide_empty    : Setting this optional attribute to true (1)
                    will exclude categories with no posts assigned.
                    The default is false (0).
    toplevel      : Same as mt:wp:SubCategories.
    sort_by       : Defines the data to sort categories.
                    The default value is "ID".
    sort_order    : Accepted values are "ascend" and "descend".
                    Default order is descend.
    lastn         : Display the N categories. N is a positive integer.
    offset        : Used in coordination with limit,
                    starts N categories from the start of the list.
    limit         : Load the N categories. N is a positive integer.
    glue          : A string that is output between post.

---------------------------------------

**mt:wp:Tags(Block Tag)**

A block tag which iterates over a list of 'tag' from a blog.

*Attributes:*

    hide_empty    : Setting this optional attribute to true (1)
                    will exclude tags with no posts assigned.
                    The default is false (0).
    sort_by       : Defines the data to sort categories.
                    The default value is "ID".
    sort_order    : Accepted values are "ascend" and "descend".
                    Default order is descend.
    lastn         : Display the N tags. N is a positive integer.
    offset        : Used in coordination with limit,
                    starts N tags from the start of the list.
    limit         : Load the N tags. N is a positive integer.
    glue          : A string that is output between post.

---------------------------------------

**mt:wp:EntryCategories(Block Tag)**

*Alias: mt:wp:get\_the\_category*

A container tag that lists all of the categories to which the post is assigned.
This tagset creates a category context within which any category tags may be used.

---------------------------------------

**mt:wp:EntryTags(Block Tag)**

*Alias: mt:wp:get\_the\_tags*

A container tag that lists all of the tags to which the post is assigned.
This tagset creates a tag context within which any 'tag' tags may be used.

---------------------------------------

**mt:wp:IfCommentsActive**

*Alias: mt:wp:IfCommentsAccepted, *Alias: mt:wp:EntryIfAllowComments*

Conditional tag that displays its contents if comments are enabled for the post in context.

---------------------------------------

**mt:wp:IfPingsAccepted**

*Alias: mt:wp:EntryIfAllowPings*

Conditional tag that displays its contents if trackback are enabled for the post in context.

---------------------------------------

**mt:wp:ArchiveList**

A container tag representing a list of 'Monthly' archive pages.

*Attributes:*

    sort_order    : Accepted values are "ascend" and "descend".
                    Default order is descend.
    lastn         : Display the N tags. N is a positive integer.
    glue          : A string that is output between post.

*Example:*

    <mt:wp:ArchiveList archive_type="Monthly">
    <mt:if name="__first__"><ul></mt:if>
        <li>
            <a href="<mt:wp:ArchiveLink archive_type="Monthly">">
                <mt:wp:archivetitle format="%b %Y">(<mt:wp:ArchiveCount>)
            </a>
        </li>
    <mt:if name="__last__"></ul></mt:if>
    </mt:wp:ArchiveList>

---------------------------------------

**mt:wp:SubCategories(Block Tag)**

A specialized version of the mt:wp:Categories block tag that respects the hierarchical structure of categories.

*Attributes:*

    hide_empty    : Setting this optional attribute to true (1)
                    will exclude categories with no posts assigned.
                    The default is false (0).
    sort_by       : Defines the data to sort categories.
                    The default value is "ID".
    sort_order    : Accepted values are "ascend" and "descend".
                    Default order is descend.
    lastn         : Display the N categories. N is a positive integer.
    offset        : Used in coordination with limit,
                    starts N categories from the start of the list.
    limit         : Load the N categories. N is a positive integer.
    glue          : A string that is output between post.

*Example:*

    <mt:wp:SubCategories hide_empty="1">
    <mt:wp:SubCatisFirst><ul></mt:wp:SubCatisFirst>
        <li>
            <a href="<mt:wp:CategoryLink>">
            <mt:wp:CategoryLabel> (<mt:wp:CategoryCount>)
            </a>
        <mt:wp:SubCatsRecurse></li>
    <mt:wp:SubCatisLast></ul></mt:wp:SubCatisLast>
    </mt:wp:SubCategories>

---------------------------------------

**mt:wp:SubCatIsFirst(Block Tag)**

The contents of this container tag will be displayed when the first category listed by a mt:wp:SubCategories loop tag is reached.

---------------------------------------

**mt:wp:SubCatIsLast(Block Tag)**

The contents of this container tag will be displayed when the last category listed by a mt:wp:SubCategories loop tag is reached.

---------------------------------------

**mt:wp:SubCatsRecurse(Function Tag)**

Recursively call the mt:wp:SubCategories container with the subcategories of the category in context.

---------------------------------------

**mt:wp:BlogName(Function Tag)**

Outputs the name of the blog.

---------------------------------------

**mt:wp:BlogInfo(Function Tag)**

Outputs the information of the blog.

*Example:*

    <$mt:wp:BlogInfo name="blogdescription"$>
    
    =>Just another WordPress site

---------------------------------------

**mt:wp:BlogURL(Function Tag)**

*Alias: mt:wp:site\_url*

Outputs the Site URL field of the blog.

---------------------------------------

**mt:wp:EntriesCount(Function Tag)**

*Alias: mt:wp:count\_posts*

Returns the count of posts.

*Attributes:*

    category      : Category name
    category_id   : Category id
    tag           : Tag
    status        : Status text or '*'(all), 'publish' is the default.
    type          : Type text or '*'(all), 'post' is the default.

---------------------------------------

**mt:wp:EntryTitle(Function Tag)**

*Alias: mt:wp:the\_title*

Outputs the title of the current post in context.

---------------------------------------

**mt:wp:EntryID(Function Tag)**

*Alias: mt:wp:the\_ID*

Ouptuts the numeric ID for the current post in context.

---------------------------------------

**mt:wp:EntryBody(Function Tag)**

*Alias: mt:wp:the\_content, mt:wp:EntryContent*

Outputs the "main" text of the current post in context.

---------------------------------------

**mt:wp:EntryExcerpt(Function Tag)**

*Alias: mt:wp:the\_excerpt*

Ouputs the value of the excerpt field of the current post in context.

---------------------------------------

**mt:wp:EntryGUID(Function Tag)**

*Alias: mt:wp:the\_guid*

Ouputs the value of the guid field of the current post in context.

---------------------------------------

**mt:wp:EntryAuthorDisplayName(Function Tag)**

Outputs the display name of the author for the current post in context.

---------------------------------------

**mt:wp:EntryAuthor(Function Tag)**

*Alias: mt:wp:the\_author*

Outputs the display the numeric ID of the author for the current post in context.

---------------------------------------

**mt:wp:EntryCommentCount(Function Tag)**

*Alias: mt:wp:the\_comment\_count*

Outputs the number of published comments for the current post in context.

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

**mt:wp:EntryPermalink(Function Tag)**

*Alias: mt:wp:the\_permalink*

An absolute URL pointing to the archive page containing this post.
Wordpress's default format.

*Example:*

    <$mt:wp:EntryPermalink$>
    
    => http://www.example.com/p=1

---------------------------------------

**mt:wp:EntryMeta(Function Tag)**

*Alias: mt:wp:post\_meta*

Given 'key' attribute, Output the value of the custom field.

---------------------------------------

**mt:wp:CategoryLabel(Function Tag)**

*Alias: mt:wp:cat\_name*

The name of the category in context.

---------------------------------------

**mt:wp:CategoryLink(Function Tag)**

*Alias: mt:wp:get\_category\_link, mt:wp:CategoryArchiveLink*

A link to the archive page of the current category.
Wordpress's default format.

*Example:*

    <$mt:wp:CategoryLink$>
    
    => http://www.example.com/cat=1

---------------------------------------

**mt:wp:CategoryID(Function Tag)**

*Alias: mt:wp:cat\_ID*

The numeric ID of the category in context.

---------------------------------------

**mt:wp:CategorySlug(Function Tag)**

The 'slug' of the category in context.

---------------------------------------

**mt:wp:CategoryDescription(Function Tag)**

The 'description' of the category in context.

---------------------------------------

**mt:wp:CategoryCount(Function Tag)**

The number of published posts for the category in context.

---------------------------------------

**mt:wp:CategoryName(Function Tag)**

*Alias: mt:wp:cat\_name*

The name of the category in context.

---------------------------------------

**mt:wp:TagLink(Function Tag)**

*Alias: mt:wp:get\_tag\_link, mt:wp:TagArchiveLink*

A link to the archive page of the current tag.
Wordpress's default format.

*Example:*

    <$mt:wp:TagLink$>
    
    => http://www.example.com/tag=foo

---------------------------------------

**mt:wp:TagID(Function Tag)**

The numeric ID of the tag in context.

---------------------------------------

**mt:wp:TagSlug(Function Tag)**

The 'slug' of the tag in context.

---------------------------------------

**mt:wp:TagDescription(Function Tag)**

The 'description' of the tag in context.

---------------------------------------

**mt:wp:TagCount(Function Tag)**

The number of published posts for the tag in context.

---------------------------------------

**mt:wp:ArchiveTitle(Function Tag)**

In mt:wp:ArchiveList, A descriptive title of the current 'Monthly' archive.
The range of dates in the week in "Month YYYY" form.
[See the Date tag for supported attributes.](http://www.movabletype.org/documentation/appendices/tags/date.html)

---------------------------------------

**mt:wp:ArchiveLink(Function Tag)**

*Alias: mt:wp:get_month_link*

In mt:wp:ArchiveList, A link to the archive page of the current month.
Wordpress's default format.

*Attributes:*

    archive_type  : "Monthly"

*Example:*

    <$mt:wp:ArchiveLink archive_type="Monthly"$>
    
    => http://www.example.com/?month=yyyymm

---------------------------------------
