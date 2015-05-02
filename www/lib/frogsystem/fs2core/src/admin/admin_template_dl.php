<?php if (!defined('ACP_GO')) die('Unauthorized access!');

$TEMPLATE_GO = 'tpl_dl';
$TEMPLATE_FILE = '0_downloads.tpl';
$TEMPLATE_EDIT = null;

$TEMPLATE_EDIT[] = array(
    'name' => 'APPLET_LINE',
    'title' => $FD->text("template", "dl_applet_line_title"),
    'description' => $FD->text("template", "dl_applet_line_desc"),
    'rows' => 10,
    'cols' => 66,
    'help' => array(
        array('tag' => 'title', 'text' => $FD->text("template", "dl_applet_line_dl_title")),
        array('tag' => 'date', 'text' => $FD->text("template", "dl_applet_line_date")),
        array('tag' => 'url', 'text' => $FD->text("template", "dl_applet_line_url")),
        array('tag' => 'download_id', 'text' => $FD->text("template", "dl_applet_line_dl_id")),
    )
);

$tmp['name'] = 'SEARCH';
$tmp['title'] = $FD->text("template", "dl_search_field_title");
$tmp['description'] = $FD->text("template", "dl_search_field_description");
$tmp['rows'] = '15';
$tmp['cols'] = '66';
$tmp['help'][0]['tag'] = 'input_cat';
$tmp['help'][0]['text'] = $FD->text("template", "dl_search_field_help_1");
$tmp['help'][1]['tag'] = 'keyword';
$tmp['help'][1]['text'] = $FD->text("template", "dl_search_field_help_2");
$tmp['help'][2]['tag'] = 'all_url';
$tmp['help'][2]['text'] = $FD->text("template", "dl_search_field_help_3");
$TEMPLATE_EDIT[] = $tmp; //$tmp is now saved in the template-creation-array
unset($tmp); //unsets $tmp for safety-issues

$TEMPLATE_EDIT[] = array(
    'name' => 'NAVIGATION_LINE',
    'title' => $FD->text("template", "dl_navigation_line_title"),
    'description' => $FD->text("template", "dl_navigation_line_desc"),
    'rows' => 15,
    'cols' => 66,
    'help' => array(
        array('tag' => 'icon_url', 'text' => $FD->text("template", "dl_navigation_line_icon_url")),
        array('tag' => 'cat_url', 'text' => $FD->text("template", "dl_navigation_line_cat_url")),
        array('tag' => 'cat_name', 'text' => $FD->text("template", "dl_navigation_line_cat_name")),
    )
);

$TEMPLATE_EDIT[] = array(
    'name' => 'NAVIGATION_BODY',
    'title' => $FD->text("template", "dl_navigation_body_title"),
    'description' => $FD->text("template", "dl_navigation_body_desc"),
    'rows' => 10,
    'cols' => 66,
    'help' => array(
        array('tag' => 'lines', 'text' => $FD->text("template", "dl_navigation_body_lines")),
    )
);

$TEMPLATE_EDIT[] = array(
    'name' => 'PREVIEW_LINE',
    'title' => $FD->text("template", "dl_preview_line_title"),
    'description' => $FD->text("template", "dl_preview_line_desc"),
    'rows' => 15,
    'cols' => 66,
    'help' => array(
        array('tag' => 'title', 'text' => $FD->text("template", "dl_preview_line_entry_title")),
        array('tag' => 'url', 'text' => $FD->text("template", "dl_preview_line_url")),
        array('tag' => 'cat_name', 'text' => $FD->text("template", "dl_preview_line_cat_name")),
        array('tag' => 'date', 'text' => $FD->text("template", "dl_preview_line_date")),
        array('tag' => 'text', 'text' => $FD->text("template", "dl_preview_line_text")),
    )
);


$TEMPLATE_EDIT[] = array(
    'name' => 'NO_PREVIEW_LINE',
    'title' => $FD->text("template", "dl_no_preview_line_title"),
    'description' => $FD->text("template", "dl_no_preview_line_desc"),
    'rows' => 10,
    'cols' => 66,
    'help' => array()
);

$TEMPLATE_EDIT[] = array(
    'name' => 'PREVIEW_LIST',
    'title' => $FD->text("template", "dl_preview_list_title"),
    'description' => $FD->text("template", "dl_preview_list_desc"),
    'rows' => 15,
    'cols' => 66,
    'help' => array(
        array('tag' => 'entries', 'text' => $FD->text("template", "dl_preview_list_entries")),
        array('tag' => 'page_title', 'text' => $FD->text("template", "dl_page_title")),
    )
);


$TEMPLATE_EDIT[] = array(
    'name' => 'BODY',
    'title' => $FD->text("template", "dl_body_title"),
    'description' => $FD->text("template", "dl_body_desc"),
    'rows' => 20,
    'cols' => 66,
    'help' => array(
        array('tag' => 'navigation', 'text' => $FD->text("template", "dl_body_navigation")),
        array('tag' => 'search', 'text' => $FD->text("template", "dl_body_search")),
        array('tag' => 'entries', 'text' => $FD->text("template", "dl_body_entries")),
        array('tag' => 'page_title', 'text' => $FD->text("template", "dl_page_title")),
    )
);


$tmp['name'] = 'ENTRY_FILE_LINE';
$tmp['title'] = $FD->text("template", "dl_file_title");
$tmp['description'] = $FD->text("template", "dl_file_description");
$tmp['rows'] = '20';
$tmp['cols'] = '66';
$tmp['help'][0]['tag'] = 'name';
$tmp['help'][0]['text'] = $FD->text("template", "dl_file_help_1");
$tmp['help'][1]['tag'] = 'url';
$tmp['help'][1]['text'] = $FD->text("template", "dl_file_help_2");
$tmp['help'][2]['tag'] = 'size';
$tmp['help'][2]['text'] = $FD->text("template", "dl_file_help_3");
$tmp['help'][3]['tag'] = 'traffic';
$tmp['help'][3]['text'] = $FD->text("template", "dl_file_help_4");
$tmp['help'][4]['tag'] = 'hits';
$tmp['help'][4]['text'] = $FD->text("template", "dl_file_help_5");
$tmp['help'][5]['tag'] = 'mirror_ext';
$tmp['help'][5]['text'] = $FD->text("template", "dl_file_help_6");
$tmp['help'][6]['tag'] = 'mirror_col';
$tmp['help'][6]['text'] = $FD->text("template", "dl_file_help_7");
$TEMPLATE_EDIT[] = $tmp; //$tmp is now saved in the template-creation-array
unset($tmp); //unsets $tmp for safety-issues

$tmp['name'] = 'ENTRY_FILE_IS_MIRROR';
$tmp['title'] = $FD->text("template", "dl_file_is_mirror_title");
$tmp['description'] = $FD->text("template", "dl_file_is_mirror_description");
$tmp['rows'] = '10';
$tmp['cols'] = '66';
$TEMPLATE_EDIT[] = $tmp; //$tmp is now saved in the template-creation-array
unset($tmp); //unsets $tmp for safety-issues

$tmp['name'] = 'ENTRY_STATISTICS';
$tmp['title'] = $FD->text("template", "dl_stats_title");
$tmp['description'] = $FD->text("template", "dl_stats_description");
$tmp['rows'] = '15';
$tmp['cols'] = '66';
$tmp['help'][0]['tag'] = 'number';
$tmp['help'][0]['text'] = $FD->text("template", "dl_stats_help_1");
$tmp['help'][1]['tag'] = 'size';
$tmp['help'][1]['text'] = $FD->text("template", "dl_stats_help_2");
$tmp['help'][2]['tag'] = 'traffic';
$tmp['help'][2]['text'] = $FD->text("template", "dl_stats_help_3");
$tmp['help'][3]['tag'] = 'hits';
$tmp['help'][3]['text'] = $FD->text("template", "dl_stats_help_4");
$TEMPLATE_EDIT[] = $tmp; //$tmp is now saved in the template-creation-array
unset($tmp); //unsets $tmp for safety-issues


$TEMPLATE_EDIT[] = array(
    'name' => 'ENTRY_BODY',
    'title' => $FD->text("template", "dl_entry_body_title"),
    'description' => $FD->text("template", "dl_entry_body_desc"),
    'rows' => 40,
    'cols' => 66,
    'help' => array(
        array('tag' => 'title', 'text' => $FD->text("template", "dl_entry_body_entry_title")),
        array('tag' => 'img_url', 'text' => $FD->text("template", "dl_entry_body_img_url")),
        array('tag' => 'thumb_url', 'text' => $FD->text("template", "dl_entry_body_thumb_url")),
        array('tag' => 'viewer_link', 'text' => $FD->text("template", "dl_entry_body_viewer_link")),
        array('tag' => 'navigation', 'text' => $FD->text("template", "dl_entry_body_navigation")),
        array('tag' => 'search', 'text' => $FD->text("template", "dl_entry_body_search")),
        array('tag' => 'uploader', 'text' => $FD->text("template", "dl_entry_body_uploader")),
        array('tag' => 'uploader_url', 'text' => $FD->text("template", "dl_entry_body_uploader_url")),
        array('tag' => 'author', 'text' => $FD->text("template", "dl_entry_body_author")),
        array('tag' => 'author_url', 'text' => $FD->text("template", "dl_entry_body_author_url")),
        array('tag' => 'author_link', 'text' => $FD->text("template", "dl_entry_body_author_link")),
        array('tag' => 'date', 'text' => $FD->text("template", "dl_entry_body_date")),
        array('tag' => 'cat_name', 'text' => $FD->text("template", "dl_entry_body_cat_name")),
        array('tag' => 'text', 'text' => $FD->text("template", "dl_entry_body_text")),
        array('tag' => 'files', 'text' => $FD->text("template", "dl_entry_body_files")),
        array('tag' => 'statistics', 'text' => $FD->text("template", "dl_entry_body_statistics")),
        array('tag' => 'messages', 'text' => $FD->text("template", "dl_entry_body_messages")),
        //TODO: localize texts
        //~ array ( 'tag' => 'comments_url', 'text' => 'URL zur Kommentaransicht des Downloads' ),
        array('tag' => 'comments_number', 'text' => 'Anzahl der zum Download abgegebenen Kommentare')
    )
);


//////////////////////////
//// Intialise Editor ////
//////////////////////////

echo templatepage_init($TEMPLATE_EDIT, $TEMPLATE_GO, $TEMPLATE_FILE);
?>
