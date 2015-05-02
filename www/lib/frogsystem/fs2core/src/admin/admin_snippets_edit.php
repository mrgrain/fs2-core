<?php if (!defined('ACP_GO')) die('Unauthorized access!');

/////////////////////////////
//// DB: Update Snippets ////
/////////////////////////////

if (
    isset ($_POST['sended']) && $_POST['sended'] == 'edit'
    && isset ($_POST['snippet_action']) && $_POST['snippet_action'] == 'edit'
    && isset ($_POST['snippet_id'])
) {
    // Security-Functions
    settype($_POST['snippet_id'], 'integer');
    settype($_POST['snippet_active'], 'integer');

    // SQL-Queries
    $stmt = $FD->db()->conn()->prepare('
                UPDATE `' . $FD->env('DB_PREFIX') . "snippets`
                SET
                    `snippet_text` = ?,
                    `snippet_active` = '" . $_POST['snippet_active'] . "'
                WHERE `snippet_id` = '" . $_POST['snippet_id'] . "'");
    $stmt->execute(array($_POST['snippet_text']));

    // Display Message
    systext($FD->text("admin", "changes_saved"),
        $FD->text("admin", "info"), FALSE, $FD->text("admin", "icon_save_ok"));

    // Unset Vars
    unset ($_POST);
}

/////////////////////////////
//// DB: Delete Snippets ////
/////////////////////////////
elseif (
    $_SESSION['snippets_delete']
    && isset ($_POST['sended']) && $_POST['sended'] == 'delete'
    && isset ($_POST['snippet_action']) && $_POST['snippet_action'] == 'delete'
    && isset ($_POST['snippet_id'])
    && isset ($_POST['snippet_delete'])
) {
    if ($_POST['snippet_delete'] == 1) {

        // Security-Functions
        $_POST['snippet_id'] = array_map('intval', explode(',', $_POST['snippet_id']));

        // SQL-Delete-Query
        $FD->db()->conn()->exec('
            DELETE
            FROM `' . $FD->env('DB_PREFIX') . 'snippets`
            WHERE `snippet_id` IN (' . implode(',', $_POST['snippet_id']) . ')');

        systext($FD->text("admin", "snippets_deleted"),
            $FD->text("admin", "info"), FALSE, $FD->text("admin", "icon_trash_ok"));

    } else {
        systext($FD->text("admin", "snippets_not_deleted"),
            $FD->text("admin", "info"), FALSE, $FD->text("admin", "icon_trash_error"));
    }

    // Unset Vars
    unset ($_POST);
}

///////////////////////
//// Display Forms ////
///////////////////////
if (isset ($_POST['snippet_id']) && is_array($_POST['snippet_id']) && $_POST['snippet_action']) {
    // Security Function
    $_POST['snippet_id'] = array_map('intval', $_POST['snippet_id']);

    ///////////////////////////
    //// Edit Snippet Form ////
    ///////////////////////////
    if ($_POST['snippet_action'] == 'edit' && count($_POST['snippet_id']) == 1) {
        $_POST['snippet_id'] = $_POST['snippet_id'][0];

        // Display Error Messages
        if (isset($_POST['sended']) && ($_POST['sended'] == 'edit')) {

            // Shouldn't happen

            // Get Data from DB
        } else {
            $index = $FD->db()->conn()->query('
                        SELECT *
                        FROM `' . $FD->env('DB_PREFIX') . "snippets`
                        WHERE `snippet_id` = '" . $_POST['snippet_id'] . "'
                        LIMIT 0,1");
            $data_arr = $index->fetch(PDO::FETCH_ASSOC);
            putintopost($data_arr);
        }

        // Security Functions
        $_POST['snippet_tag'] = killhtml($_POST['snippet_tag']);
        $_POST['snippet_text'] = killhtml($_POST['snippet_text']);

        settype($_POST['snippet_id'], 'integer');
        settype($_POST['snippet_active'], 'integer');

        // Display Form
        echo '
                    <form action="" method="post">
                        <input type="hidden" name="go" value="snippets_edit">
                        <input type="hidden" name="snippet_action" value="edit">
                        <input type="hidden" name="sended" value="edit">
                        <input type="hidden" name="snippet_id" value="' . $_POST['snippet_id'] . '">
                        <table class="configtable" cellpadding="4" cellspacing="0">
                            <tr><td class="line" colspan="2">' . $FD->text("admin", "snippet_edit_title") . '</td></tr>
                            <tr>
                                <td class="config" width="50%">
                                    ' . $FD->text("admin", "snippet_tag_title") . ':<br>
                                    <span class="small">' . $FD->text("admin", "snippet_tag_desc") . '</span>
                                </td>
                                <td class="config" width="50%">
                                    ' . $_POST['snippet_tag'] . '
                                </td>
                            </tr>
                            <tr>
                                <td class="config">
                                    ' . $FD->text("admin", "snippet_active_title") . ':<br>
                                    <span class="small">' . $FD->text("admin", "snippet_active_desc") . '</span>
                                </td>
                                <td class="config">
                                    <input class="pointer" type="checkbox" name="snippet_active" value="1" ' . getchecked(1, $_POST['snippet_active']) . '>
                                </td>
                            </tr>
                            <tr>
                                <td class="config">
                                    ' . $FD->text("admin", "snippet_text_title") . ':<br>
                                    <span class="small">' . $FD->text("admin", "snippet_text_desc") . '</span>
                                </td>
                            </tr>
                            <tr>
                                <td class="config" colspan="2">
                                    <textarea style="width:100%;" name="snippet_text" rows="20" wrap="virtual">' . $_POST['snippet_text'] . '</textarea>
                                </td>
                            </tr>
                            <tr><td class="space"></td></tr>
                            <tr>
                                <td colspan="2" class="buttontd">
                                    <button class="button_new" type="submit">
                                        ' . $FD->text("admin", "button_arrow") . ' ' . $FD->text("admin", "save_changes_button") . '
                                    </button>
                                </td>
                            </tr>
                        </table>
                    </form>
        ';
    }

    ///////////////////////////////////////////////////////////////
    //// Show too much selected Error & Go back to Select Form ////
    ///////////////////////////////////////////////////////////////
    elseif ($_POST['snippet_action'] == 'edit' && count($_POST['snippet_id']) > 1) {
        // Display Error
        systext($FD->text("admin", "select_only_one_to_edit"),
            $FD->text("admin", "error"), TRUE, $FD->text("admin", "icon_error"));
        unset ($_POST['snippet_id']);
    }

    /////////////////////////////
    //// Delete Snippet Form ////
    /////////////////////////////
    elseif ($_SESSION['snippets_delete'] && $_POST['snippet_action'] == 'delete' && count($_POST['snippet_id']) >= 1) {
        // Display Head of Table
        echo '
                    <form action="" method="post">
                        <input type="hidden" name="go" value="snippets_edit">
                        <input type="hidden" name="snippet_action" value="delete">
                        <input type="hidden" name="sended" value="delete">
                        <input type="hidden" name="snippet_id" value="' . implode(',', $_POST['snippet_id']) . '">
                        <table class="configtable" cellpadding="4" cellspacing="0">
                            <tr><td class="line" colspan="2">' . $FD->text("admin", "snippets_delete_title") . '</td></tr>
                            <tr>
                                <td class="configthin">
                                    ' . $FD->text("admin", "snippets_delete_question") . '
                                    <br><br>
        ';

        // get snippets from db
        $index = $FD->db()->conn()->query('
                        SELECT COUNT(*)
                        FROM `' . $FD->env('DB_PREFIX') . 'snippets`
                        WHERE `snippet_id` IN (' . implode(',', $_POST['snippet_id']) . ')');
        // snippets found
        if ($index->fetchColumn() > 0) {

            // display snippets
            $index = $FD->db()->conn()->query('
                        SELECT *
                        FROM `' . $FD->env('DB_PREFIX') . 'snippets`
                        WHERE `snippet_id` IN (' . implode(',', $_POST['snippet_id']) . ')
                        ORDER BY `snippet_tag`');
            while ($data_arr = $index->fetch(PDO::FETCH_ASSOC)) {

                // get other data
                $data_arr['active_text'] = ($data_arr['snippet_active'] == 1) ? $FD->text("admin", "snippet_active") : $FD->text("admin", "snippet_not_active");

                echo '
                                    <b>' . killhtml($data_arr['snippet_tag']) . '</b> (' . $data_arr['active_text'] . ')<br>
                ';
            }
        }

        // Display End of Table
        echo '
                                </td>
                                <td class="config right top" style="padding: 0px;">
                                    ' . get_yesno_table('snippet_delete') . '
                                </td>
                            </tr>
                            <tr><td class="space"></td></tr>
                            <tr>
                                <td class="buttontd" colspan="2">
                                    <button class="button_new" type="submit">
                                        ' . $FD->text("admin", "button_arrow") . ' ' . $FD->text("admin", "do_action_button_long") . '
                                    </button>
                                </td>
                            </tr>
                        </table>
                    </form>
        ';
    }
}
/////////////////////////////
//// Select Snippet Form ////
/////////////////////////////
if (!isset ($_POST['snippet_id'])) {

    // start display
    echo '
                    <form action="" method="post">
                        <input type="hidden" name="go" value="snippets_edit">
                        <table class="configtable select_list" cellpadding="4" cellspacing="0">
                            <tr><td class="line" colspan="3">' . $FD->text("admin", "snippet_select_title") . '</td></tr>
    ';

    // get snippets from db
    $index = $FD->db()->conn()->query('
                SELECT COUNT(*)
                FROM `' . $FD->env('DB_PREFIX') . 'snippets`');

    // snippets found
    if ($index->fetchColumn() > 0) {

        // display table head
        echo '
                            <tr>
                                <td class="config">' . $FD->text("admin", "snippet_tag_title") . '</td>
                                <td class="config" width="20">&nbsp;&nbsp;' . $FD->text("admin", "active") . '&nbsp;&nbsp;</td>
                                <td class="config" width="20"></td>
                            </tr>
        ';

        // display Snippets
        $index = $FD->db()->conn()->query('
                SELECT *
                FROM `' . $FD->env('DB_PREFIX') . 'snippets`
                ORDER BY `snippet_tag`');
        while ($data_arr = $index->fetch(PDO::FETCH_ASSOC)) {

            // get other data
            $data_arr['active_text'] = ($data_arr['snippet_active'] == 1) ? $FD->text("admin", "yes") : $FD->text("admin", "no");

            echo '

                            <tr class="select_entry">
                                <td class="configthin middle">' . killhtml($data_arr['snippet_tag']) . '</td>
                                <td class="configthin middle center">' . $data_arr['active_text'] . '</td>
                                <td class="config top center">
                                    <input class="pointer select_box" type="checkbox" name="snippet_id[]" value="' . $data_arr['snippet_id'] . '">
                                </td>
                            </tr>
            ';
        }

        if (!isset($_POST['snippet_action']))
            $_POST['snippet_action'] = '';
        // display footer with button
        echo '
                            <tr><td class="space"></td></tr>
                            <tr>
                                <td class="right" colspan="4">
                                    <select class="select_type" name="snippet_action" size="1">
                                        <option class="select_one" value="edit" ' . getselected('edit', $_POST['snippet_action']) . '>' . $FD->text("admin", "selection_edit") . '</option>
        ';
        echo ($_SESSION['snippets_delete']) ? '<option class="select_red" value="delete" ' . getselected('delete', $_POST['snippet_action']) . '>' . $FD->text("admin", "selection_delete") . '</option>' : '';
        echo '
                                    </select>
                                </td>
                            </tr>
                            <tr><td class="space"></td></tr>
                            <tr>
                                <td class="buttontd" colspan="4">
                                    <button class="button_new" type="submit">
                                        ' . $FD->text("admin", "button_arrow") . ' ' . $FD->text("admin", "do_action_button_long") . '
                                    </button>
                                </td>
                            </tr>
        ';

        // no Snippets found
    } else {

        echo '
                            <tr><td class="space"></td></tr>
                            <tr>
                                <td class="config center" colspan="4">' . $FD->text("admin", "snippets_not_found") . '</td>
                            </tr>
                            <tr><td class="space"></td></tr>
        ';
    }
    echo '
                        </table>
                </form>
    ';
}
?>
