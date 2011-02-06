<?php 
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */
 
/**
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     resource
 * Purpose:  fetches template from a global variable
 * Version:  1.0 [Sep 28, 2002 boots since Sep 28, 2002 boots]
 * -------------------------------------------------------------
 */ 
function smarty_resource_var_source($tpl_name, &$tpl_source, &$ctx) 
{ 
    if (isset($tpl_name)) { 
        global $$tpl_name; 
        $tpl_source = $$tpl_name; 
        return true; 
    } 
    return false;
} 
 
function smarty_resource_var_timestamp($tpl_name, $tpl_timestamp, &$ctx) 
{ 
    if (isset($tpl_name)) { 
        $tpl_timestamp = microtime(); 
        return true; 
    } 
    return false; 
} 
 
function smarty_resource_var_secure($tpl_name, &$ctx) 
{ 
    // assume all templates are secure 
    return true; 
} 
 
function smarty_resource_var_trusted($tpl_name, &$ctx) 
{ 
    // not used for templates 
} 
 
?>