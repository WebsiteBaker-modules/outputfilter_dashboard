<?php

/*
add_filter.php
*/

/**
 *
 * @category        tool
 * @package         Outputfilter Dashboard
 * @version         1.4.1
 * @authors         Thomas "thorn" Hornik <thorn@nettest.thekk.de>, Christian M. Stefan (Stefek) <stefek@designthings.de>, Martin Hecht (mrbaseman) <mrbaseman@gmx.de>
 * @copyright       2009,2010 Thomas "thorn" Hornik, 2010 Christian M. Stefan (Stefek), 2016 Martin Hecht (mrbaseman)
 * @link            https://github.com/WebsiteBaker-modules/outpufilter_dashboard
 * @link            http://forum.websitebaker.org/index.php/topic,28926.0.html
 * @link            http://forum.wbce.org/viewtopic.php?pid=3121
 * @link            http://addons.wbce.org/pages/addons.php?do=item&item=53
 * @license         GNU General Public License, Version 3
 * @platform        WebsiteBaker 2.8.x
 * @requirements    PHP 5.4 and higher
 * 
 * This file is part of OutputFilter-Dashboard, a module for Website Baker CMS.
 * 
 * OutputFilter-Dashboard is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * OutputFilter-Dashboard is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with OutputFilter-Dashboard. If not, see <http://www.gnu.org/licenses/>.
 * 
 **/

// prevent this file from being accessed directly
if(!defined('WB_PATH')) die(header('Location: ../index.php'));

// obtain module directory
$mod_dir = basename(dirname(__FILE__));
require(WB_PATH.'/modules/'.$mod_dir.'/info.php');

// include module.functions.php 
include_once(WB_PATH . '/framework/module.functions.php');

// include the module language file depending on the backend language of the current user
if (!include(get_module_language_file($mod_dir))) return;

// load outputfilter-functions
require_once(dirname(__FILE__).'/functions.php');

// This file will be included from tool.php

// check if user is allowed to use admin-tools (to prevent this file to be called by an unauthorized user e.g. from a code-section)
if(!$admin->get_permission('admintools')) die(header('Location: ../../index.php'));


// get unique name for filter-function
$function_name = uniqid('opff_');

// set filter-data (empty)
$id = 0;
$types = opf_get_types();
$type = key($types);
$name = $LANG['MOD_OPF']['TXT_INSERT_NAME'];
$active = 1;
$userfunc = 1;
$file = '';
$modules = array();
$pages_parent = array('all');
$pages = array('all');
$desc = $LANG['MOD_OPF']['TXT_INSERT_DESCRIPTION'];
$func = htmlspecialchars("<?php\nfunction $function_name(&\$content, \$page_id, \$section_id, \$module, \$wb) {\n  // add filter here\n  \n  \n  return(TRUE);\n}\n?>");
$funcname = $function_name;
$allowedit = 1;
$allowedittarget = 1;

$filter_type_options='';

foreach($types as $value=>$text){
        $filter_type_options .= "<option value=\"$value\" ";
        if($type==$value) $filter_type_options .= 'selected="selected"';
        $filter_type_options .= ">".opf_quotes($text)."</option>";
}


// fill target checkbox-trees
$mlist = $plist1 = $plist2 = '';
$mlist = opf_make_modules_checktree($modules, $type='tree', TRUE);
$plist1  = opf_make_pages_parent_checktree($pages_parent, $pages, $type='tree');
//$plist2  = opf_make_pages_checktree($pages, $type='tree');

// do we have to display additional_fields? - No
$list_growfield = "";
$list_editarea = "";
$extra_fields = array();


// init template
$tpl = new Template(WB_PATH.'/modules/outputfilter_dashboard');
$tpl->set_file('page', 'templates/add_edit.htt');
$tpl->set_block('page', 'main_block', 'main');

// fill template vars
$tpl->set_var(
array_merge($LANG['MOD_OPF'],
        array(
        // only inline-filters and filters with 'allowedit' are editable
        'tpl_filter_readonly' => ($userfunc||$allowedit)?'':'readonly="readonly"',
        'tpl_filter_disabled' => ($userfunc||$allowedit)?'':'disabled="disabled"',
        // filter active?
        'tpl_filter_active' => ($active)?'checked="checked"':'',
        // filter-types: array $types[$value]=>$text to fill dropdown-list
        //'tpl_filter_types' => $types,
        'tpl_filter_type' => $type,
        // checkbox-trees: contains the whole HTML-output. Just use echo
        'tpl_module_tree' => $mlist,
        'tpl_pages_list1' => $plist1,
        //'tpl_pages_list2' => $plist2,
        // additional fields
        //'tpl_extra_fields' => $extra_fields,
        'tpl_save_url' => opf_quotes(ADMIN_URL."/admintools/tool.php?tool=".basename(dirname(__FILE__)).'&amp;'.$admin->getFTAN(false)),
        'tpl_id' => opf_quotes($id),
        'tpl_filter_name' => opf_quotes($name),
        'tpl_filter_funcname' => opf_quotes($funcname),
        'tpl_filter_file' => opf_quotes($file),
        'tpl_filter_description' => opf_quotes($desc),
        'tpl_filter_helppath_onclick' => '', // opf_quotes(''),
        'TPL_HELP_BLOCK' => '',
        'tpl_funcname' => $funcname,
        'tpl_func' => $func,
        'tpl_cancel_onclick' => opf_quotes('javascript: window.location = \''.ADMIN_URL.'/admintools/tool.php?tool='.basename(dirname(__FILE__)).'\';'),
        'tpl_allowedit' => (($func <> "")?("var opf_editarea = ".($allowedit?'"editable"':'""').";"):""),
        'tpl_list_editarea' => "",
        'tpl_list_growfield' => $list_growfield,
        'tpl_filter_type_options' => $filter_type_options,
        'WB_URL' => WB_URL,
        'MOD_URL' => WB_URL.'/modules/'.$module_directory,
        'IMAGE_URL' => WB_URL.'/modules/'.$module_directory.'/templates/images'

)));


        // if file is not empty parse the file_area_block and store the result in TPL_FILE_AREA_BLOCK
        if(!empty($file)){
                $tpl->set_block('page', 'file_area_block', 'file_area');
                $tpl->parse('TPL_FILE_AREA_BLOCK', 'file_area_block', false);
        } else { 
                $tpl->set_var('TPL_FILE_AREA_BLOCK', "");
        }

        // if func is not empty parse the func_area_block and store the result in TPL_FUNC_AREA_BLOCK
        if(!empty($func)){
                $tpl->set_block('page', 'func_area_block', 'func_area');
                $tpl->parse('TPL_FUNC_AREA_BLOCK', 'func_area_block', false);
        } else { 
                $tpl->set_var('TPL_FUNC_AREA_BLOCK', "");
        }

        // if extra_fileds is not empty parse the extra_fields_block and store the result in TPL_EXTRA_fields_AREA_BLOCK
        if(!empty($extra_fields)){
            $TPL_EXTRA_FIELDS_BLOCK="";
            foreach($tpl_extra_fields as $field){
                    $template=$field['type'];
                if($field['type']=='editarea')$template='textarea';                
                $tpl_field_text=opf_quotes($field['text']);
                $tpl->set_var('tpl_field_text', $tpl_field_text);
                $tpl_field_name=opf_quotes($field['name']);
                $tpl->set_var('tpl_field_name', $tpl_field_name);
                $tpl_field_value=$field['value'];
                $tpl_field_id='';
                if($template=='textarea'){
                    $tpl_field_id=opf_quotes($field['id']);
                } else {
                    $tpl_field_value=opf_quotes($tpl_field_value);
                }
                $tpl->set_var('tpl_field_value', $tpl_field_value);
                $tpl->set_var('tpl_field_id', $tpl_field_id);
                $tpl_field_style=$field['style'];
                $tpl->set_var('tpl_field_style', $tpl_field_style);
                $tpl_field_style=$field['checked'];
                $tpl->set_var('tpl_field_checked', $tpl_field_checked);
                $tpl_field_style=$field['options'];
                $tpl->set_var('tpl_field_options', $tpl_field_options);

                if($field['type']=='array'){
                    foreach($field['values'] as $key=>$value){
                        $tpl->set_var('tpl_key', $key);
                        $tpl->set_var('tpl_value', $value);
                        $keyid=uniqid(); 
                        $list_growfield[]=$keyid; 
                        $tpl->set_var('tpl_keyid', $keyid);
                        $valid=uniqid(); 
                        $list_growfield[]=$valid; 
                        $tpl->set_var('tpl_valid', $valid);
                        // first parse the block specific to this field type
                        $tpl->set_block('page', 'array_row_block', 'extra_field');
                        $tpl->parse('TPL_FIELD_BLOCK', 'array_row_block', false);
                        // now insert this again into a single line
                        $tpl->set_block('page', 'single_field_block', 'extra_fields');
                        $tpl->parse('TPL_SINGLE_FIELD_BLOCK', 'single_field_block', false);
                        $TPL_EXTRA_FIELDS_BLOCK .= $tpl->get_var('TPL_SINGLE_FIELD_BLOCK');
                    }
                } else {  
                   // in short, pretty much the same, but just do it only once
                   $tpl->set_block('page', $template.'_block', 'extra_field');
                   $tpl->parse('TPL_FIELD_BLOCK', $template.'_block', false);
                   $tpl->set_block('page', 'single_field_block', 'extra_fields');
                   $tpl->parse('TPL_SINGLE_FIELD_BLOCK', 'single_field_block', false);
                   $TPL_EXTRA_FIELDS_BLOCK .= $tpl->get_var('TPL_SINGLE_FIELD_BLOCK');
                }
            }
            $tpl->set_var('TPL_EXTRA_FIELDS_BLOCK', $TPL_EXTRA_FIELDS_BLOCK);
        } else { 
                $tpl->set_var('TPL_EXTRA_FIELDS_BLOCK', "");
        }

        // if list_editarea is present update tpl_list_editarea
        if($list_editarea <> ""){
                $tpl_list_editarea = "var opf_editarea_list = new Array();";
                $i = 0; 
                foreach($tpl_list_editarea as $id) {
                        $tpl_list_editarea .= 'opf_editarea_list['.$i++.'] = '."'$id';";
                }
                $tpl->set_var('tpl_list_editarea', $tpl_list_editarea);
        }

        // if list_growfield is present update tpl_list_growfield
        if($list_growfield <> ""){
                $tpl_list_growfield = "var opf_growfield_list = new Array();";
                $i = 0; 
                foreach($tpl_list_growfield as $id) {
                        $tpl_list_growfield .= 'opf_growfield_list['.$i++.'] = '."'$id';";
                }
                $tpl->set_var('tpl_list_growfield', $tpl_list_growfield);
        }


// show page
$tpl->set_unknowns('keep');
$tpl->parse('main', 'main_block', false);
print opf_filter_Comments($tpl->parse('output', 'main', false));

