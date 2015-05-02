<?php

/**
 * @file     class_MailManager.php
 * @folder   /libs
 * @version  0.3
 * @author   Sweil
 *
 * this class provides methods for mail creation
 */
class MailManager
{

    // Get Html Config
    public static function getHtmlConfig()
    {
        global $FD;

        try {
            $html = $FD->db()->conn()->query('SELECT html FROM ' . $FD->env('DB_PREFIX') . 'email LIMIT 1');
            $html = $html->fetchColumn();
            return (boolean)$html;
        } catch (Exception $e) {
            throw $e;
        }
    }

    // Get Default Mail
    public static function getDefaultSender()
    {
        global $FD;

        try {
            $data = $FD->db()->conn()->query(
                'SELECT use_admin_mail, email FROM ' . $FD->env('DB_PREFIX') . 'email LIMIT 1');
            $data = $data->fetch(PDO::FETCH_ASSOC);
            if ($data['use_admin_mail'] == 1)
                return $FD->cfg('admin_mail');
            else
                return $data['email'];

        } catch (Exception $e) {
            throw $e;
        }
    }

    // parse fscode and/or tpl_function in mail content
    public static function parseContent($content, $FSCODE = true, $TPL_FUNC = true)
    {
        if ($TPL_FUNC) {
            global $FD;
            $content = tpl_functions($content, $FD->cfg('system', 'var_loop'), array('DATE', 'VAR', 'URL', 'SNP'), true);
        }
        if ($FSCODE && self::getHtmlConfig()) {
            $content = parse_all_fscodes($content, array('html' => true));
        }

        return $content;
    }
}

?>
