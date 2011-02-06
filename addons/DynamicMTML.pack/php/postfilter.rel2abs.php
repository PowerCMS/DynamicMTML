<?php 
/* 
* Smarty plugin 
* ------------------------------------------------------------- 
* File:     postfilter.rel2abs.php 
* Type:     postfilter 
* Name:     rel2abs 
* Purpose:  Converts URI:s from relative to absolute 
* ------------------------------------------------------------- 
*/ 

/* 
Smarty postfilter rel2abs 1.0 - released July 28, 2004 

The postfilter rel2abs detects relative URI:s in a template-file 
and uses make_abs (see below) to make them absolute and then replaces them. 
The absolute URI of the template has to be put into the variable 
$GLOBALS['tpl_absuri'](please leave out http://domain) 
(i.e. /templates/something/something.tpl 
and not http://domain/templates/something/something.tpl) 
If anybody figures out a way to not having to input $GLOBALS['tpl_absuri'] 
please let me know. Other comments, suggestions and modifications are welcome. 

It was written by Simon Rönnqvist, the most recent version can 
be found at http://ownmedia.net/products/ 


make_abs takes a relative URI (i.e. ../../css/stylesheet.css) 
asd and the absolute URI (i.e. /templates/something/something.tpl) 
of the document in which the relative URI was found 
and outputs an absolute one (i.e. /css/stylesheet.css) 

make_abs was written by Andreas Friedrich http://www.x-author.de/ 
and found at http://www.webmasterworld.com/forum88/334.htm 
*/ 

function make_abs($rel_uri, $base, $REMOVE_LEADING_DOTS = true) { 
preg_match("'^([^:]+://[^/]+)/'", $base, $m); 
$base_start = $m[1]; 
if (preg_match("'^/'", $rel_uri)) { 
return $base_start . $rel_uri; 
} 
$base = preg_replace("{[^/]+$}", '', $base); 
$base .= $rel_uri; 
$base = preg_replace("{^[^:]+://[^/]+}", '', $base); 
$base_array = explode('/', $base); 
if (count($base_array) and!strlen($base_array[0])) 
array_shift($base_array); 
$i = 1; 
while ($i < count($base_array)) { 
if ($base_array[$i - 1] == ".") { 
array_splice($base_array, $i - 1, 1); 
if ($i > 1) $i--; 
} elseif ($base_array[$i] == ".." and $base_array[$i - 1]!= "..") { 
array_splice($base_array, $i - 1, 2); 
if ($i > 1) { 
$i--; 
if ($i == count($base_array)) array_push($base_array, ""); 
} 
} else { 
$i++; 
} 
} 
if (count($base_array) and $base_array[-1] == ".") 
$base_array[-1] = ""; 
/* How do we treat the case where there are still some leading ../ 
segments left? According to RFC2396 we are free to handle that 
any way we want. The default is to remove them. 
# 
"If the resulting buffer string still begins with one or more 
complete path segments of "..", then the reference is considered 
to be in error. Implementations may handle this error by 
retaining these components in the resolved path (i.e., treating 
them as part of the final URI), by removing them from the 
resolved path (i.e., discarding relative levels above the root), 
or by avoiding traversal of the reference." 
# 
http://www.faqs.org/rfcs/rfc2396.html 5.2.6.g 
*/ 
if ($REMOVE_LEADING_DOTS) { 
while (count($base_array) and preg_match("/^\.\.?$/", $base_array[0])) { 
array_shift($base_array); 
} 
} 
return($base_start . '/' . implode("/", $base_array)); 
} 


function smarty_postfilter_rel2abs($compiled, &$smarty) { 
//Extracts strings containing href or src="something" 
//that "something" can't begin with / or contain a : 
//because that would indicate that its already a relative URI 
while (eregi("(href|src|action)=\"(([^/])[[:alnum:]/+=%&_.~?-]*)\"", $compiled, $regs)) { 
$input_uri = $regs[2]; 
//Inputs the extracted string into the function make_abs 
$output_uri = make_abs($input_uri, $GLOBALS['tpl_absuri']); 
//Replaces the relative URI with the absolute one 
$compiled = ereg_replace("((href|src|action)=\")$input_uri(\")", "\\1$output_uri\\3", $compiled); 
//Repeats over again until no relative URI:s are detected 
} 

if (!isset($GLOBALS['tpl_absuri'])) { 
$compiled = "ERROR: Since the variable \$GLOBALS['tpl_absuri'] defining the template's absolute URI is not set the Smarty plug-in postfilter.rel2abs.php treats it like it'd be in the site root.\n 
Please define \$GLOBALS['tpl_absuri'] and then make sure this template is recompiled." . $compiled; 
} 
return($compiled); 
}

?>