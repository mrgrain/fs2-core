<?php
// Load Config Arrays
$FD->loadConfig('preview_images');
$random_config_arr = $FD->configObject('preview_images')->getConfigArray();

$FD->loadConfig('screens');
$screen_config_arr = $FD->configObject('screens')->getConfigArray();
$config_arr = array_merge((array)$random_config_arr, (array)$screen_config_arr);

// Check System
if ($config_arr['active'] == 1) {

    // Select Preview Image System
    if ($config_arr['type_priority'] == 1) {
        $data = get_timed_pic();
    } else {
        $data = get_random_pic();
    }

    // Select other System if use both Systems
    if ($data == FALSE && $config_arr['use_priority_only'] != 1) {
        if ($config_arr['type_priority'] == 1) {
            $data = get_random_pic();
        } else {
            $data = get_timed_pic();
        }
    }

    if ($data != FALSE) {
        $link_args = array('id' => $data['id']);
        if ($data['type'] == 1) {
            $link_args['single'] = 1;
        }
        $link = url('viewer', $link_args);

        if ($config_arr['show_type'] == 1) {
            $half_x = floor($config_arr['show_size_x'] / 2);
            $half_y = floor($config_arr['show_size_y'] / 2);
            $link = "javascript:popUp('" . urlencode($link) . "','popupviewer','" . $config_arr['show_size_x'] . "','" . $config_arr['show_size_y'] . "');";
        }

        // Get Template
        $template = new template();
        $template->setFile('0_previewimg.tpl');
        $template->load('BODY');

        $template->tag('previewimg', get_image_output('/gallery', $data['id'] . '_s', $data['caption']));
        $template->tag('previewimg_url', image_url('/gallery', $data['id'] . '_s'));
        $template->tag('image_url', image_url('/gallery', $data['id']));
        $template->tag('viewer_url', $link);
        $template->tag('caption', $data['caption']);
        $template->tag('cat_title', $data['cat_title']);

        $template = $template->display();
    } else {
        // Get Template
        $template = new template();
        $template->setFile('0_previewimg.tpl');
        $template->load('NOIMAGE_BODY');
        $template = $template->display();
    }
} else {
    $template = '';
}
?>
