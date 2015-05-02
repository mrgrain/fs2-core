<?php if (!defined('ACP_GO')) die('Unauthorized access!');

/////////////////////
//// Load Config ////
/////////////////////
$FD->loadConfig('groups');
$config_arr = $FD->configObject('groups')->getConfigArray();

//////////////////////
// Update Database  //
//////////////////////

// Insert group
if (
    isset ($_POST['sended']) && $_POST['sended'] == 'add' &&
    isset ($_POST['group_action']) && $_POST['group_action'] == 'add' &&
    isset ($_POST['user_group_name']) && $_POST['user_group_name'] != ''
) {
    // Security-Functions
    $_POST['user_group_user'] = $_SESSION['user_id'];
    settype($_POST['user_group_user'], 'integer');
    $group_date = time();

    // SQL-Update-Query
    $stmt = $FD->db()->conn()->prepare('
                    INSERT INTO ' . $FD->env('DB_PREFIX') . "user_groups (user_group_name, user_group_date, user_group_user)
                    VALUES (
                        ?,
                        '" . $group_date . "',
                        '" . $_POST['user_group_user'] . "'
                    )");
    $stmt->execute(array($_POST['user_group_name']));

    $message = 'Gruppe wurde erfolgreich hinzugef&uuml;gt';
    $id = $FD->db()->conn()->lastInsertId();

    // Image-Operations
    if ($_FILES['user_group_pic']['name'] != '') {
        $upload = upload_img($_FILES['user_group_pic'], '/group-images', 'staff_' . $id, $config_arr['group_pic_size'] * 1024, $config_arr['group_pic_x'], $config_arr['group_pic_y']);
        $message .= '<br>' . upload_img_notice($upload);
    }

    // Display Message
    systext($message, $FD->text('admin', 'info'));

    // Unset Vars
    unset ($_POST);

    // Set Vars
    $_POST['group_action'] = 'edit';
    $_POST['user_group_id'] = $FD->db()->conn()->lastInsertId();
} // Update group
elseif (
    isset ($_POST['user_group_id']) &&
    isset ($_POST['sended']) && $_POST['sended'] == 'edit' &&
    isset ($_POST['group_action']) && $_POST['group_action'] == 'edit' &&

    isset($_POST['d']) && $_POST['d'] != '' && $_POST['d'] > 0 &&
    isset($_POST['m']) && $_POST['m'] != '' && $_POST['m'] > 0 &&
    isset($_POST['y']) && $_POST['y'] != '' && $_POST['y'] > 0 &&

    isset($_POST['user_group_name']) && $_POST['user_group_name'] != '' &&
    isset ($_POST['user_group_user'])
) {
    // Security-Functions
    if (strlen(trim($_POST['user_group_color'])) == 0) {
        $_POST['user_group_color'] = -1;
    }
    settype($_POST['user_group_id'], 'integer');
    settype($_POST['user_group_user'], 'integer');
    settype($_POST['user_group_highlight'], 'integer');
    $date_arr = getsavedate($_POST['d'], $_POST['m'], $_POST['y']);
    $group_date = mktime(0, 0, 0, $date_arr['m'], $date_arr['d'], $date_arr['y']);

    // SQL-Update-Query
    $stmt = $FD->db()->conn()->prepare('
                UPDATE ' . $FD->env('DB_PREFIX') . "user_groups
                 SET
                     user_group_name = ?,
                     user_group_description = ?,
                     user_group_title = ?,
                     user_group_color = ?,
                     user_group_highlight = '" . $_POST['user_group_highlight'] . "',
                     user_group_date = '" . $group_date . "',
                     user_group_user = '" . $_POST['user_group_user'] . "'
                 WHERE
                     user_group_id = '" . $_POST['user_group_id'] . "'");
    $stmt->execute(array($_POST['user_group_name'],
        $_POST['user_group_description'],
        $_POST['user_group_title'],
        $_POST['user_group_color']));
    $message = $FD->text('admin', 'changes_saved');

    // Image-Operations
    if (isset($_POST['group_pic_delete']) && ($_POST['group_pic_delete'] == 1)) {
        if (image_delete('/group-images', 'staff_' . $_POST['user_group_id'])) {
            $message .= '<br>' . $FD->text('admin', 'image_deleted');
        } else {
            $message .= '<br>' . $FD->text('admin', 'image_not_deleted');
        }
    } elseif ($_FILES['user_group_pic']['name'] != '') {
        image_delete('/group-images', 'staff_' . $_POST['user_group_id']);
        $upload = upload_img($_FILES['user_group_pic'], '/group-images', 'staff_' . $_POST['user_group_id'], $config_arr['group_pic_size'] * 1024, $config_arr['group_pic_x'], $config_arr['group_pic_y']);
        $message .= '<br>' . upload_img_notice($upload);
    }

    // Display Message
    systext($message, $FD->text('admin', 'info'));

    // Unset Vars
    unset ($_POST);
} // Delete group
elseif (
    isset ($_POST['user_group_id']) && $_POST['user_group_id'] > 1 &&
    isset ($_POST['sended']) && $_POST['sended'] == 'delete' &&
    isset ($_POST['group_action']) && $_POST['group_action'] == 'delete' &&
    isset ($_POST['user_group_delete'])
) {
    if ($_POST['user_group_delete'] == 1) {

        // Security-Functions
        settype($_POST['user_group_id'], 'integer');

        // Udpate Users
        $FD->db()->conn()->exec('
                UPDATE ' . $FD->env('DB_PREFIX') . "user
                SET user_group = '0'
                WHERE user_group = '" . $_POST['user_group_id'] . "'");

        // Delete Permissions
        $FD->db()->conn()->exec("
                DELETE FROM " . $FD->env('DB_PREFIX') . "user_permissions
                WHERE x_id = '" . $_POST['user_group_id'] . "'
                    AND perm_for_group = '1'");

        // SQL-Delete-Query
        $FD->db()->conn()->exec('
                DELETE FROM ' . $FD->env('DB_PREFIX') . "user_groups
                WHERE user_group_id = '" . $_POST['user_group_id'] . "'
                AND user_group_id > 1");
        $message = 'Gruppe wurde erfolgreich gel&ouml;scht';

        // Delete Category Image
        if (image_delete('/group-images', 'staff_' . $_POST['user_group_id'])) {
            $message .= '<br>' . $FD->text('admin', 'image_deleted');
        }

    } else {
        $message = 'Gruppe wurde nicht gel&ouml;scht';
    }

    // Display Message
    systext($message, $FD->text('admin', 'info'));

    // Unset Vars
    unset ($_POST);
}


///////////////////////////
// Display Action-Pages  //
///////////////////////////

// No Data to write into DB
if (isset ($_POST['user_group_id']) && isset($_POST['group_action'])) {
    // Edit Category
    if ($_POST['group_action'] == 'edit') {
        // security functions
        settype($_POST['user_group_id'], 'integer');

        // Load Data from DB
        $index = $FD->db()->conn()->query('
                        SELECT *
                        FROM ' . $FD->env('DB_PREFIX') . "user_groups
                        WHERE user_group_id = '" . $_POST['user_group_id'] . "'");
        $group_arr = $index->fetch(PDO::FETCH_ASSOC);

        // Display Error Messages
        if (isset ($_POST['sended'])) {
            $group_arr = getfrompost($group_arr);
            systext($FD->text('admin', 'note_notfilled'), $FD->text('admin', 'error'), TRUE);
        }

        // Security-Functions
        $group_arr['user_group_name'] = killhtml($group_arr['user_group_name']);
        $group_arr['user_group_description'] = killhtml($group_arr['user_group_description']);
        $group_arr['user_group_title'] = killhtml($group_arr['user_group_title']);
        $group_arr['user_group_color'] = killhtml($group_arr['user_group_color']);
        settype($group_arr['user_group_highlight'], 'integer');

        //Create Color-Code
        if ($group_arr['user_group_color'] == -1) {
            $group_arr['user_group_color'] = '';
        }

        // Get User
        $index = $FD->db()->conn()->query('SELECT user_name FROM ' . $FD->env('DB_PREFIX') . "USER WHERE user_id = '" . $group_arr['user_group_user'] . "'");
        $group_arr['user_group_user_name'] = killhtml($index->fetchColumn());

        // Create Date-Arrays
        if (!isset ($_POST['d'])) {
            $_POST['d'] = date('d', $group_arr['user_group_date']);
            $_POST['m'] = date('m', $group_arr['user_group_date']);
            $_POST['y'] = date('Y', $group_arr['user_group_date']);
        }
        $date_arr = getsavedate($_POST['d'], $_POST['m'], $_POST['y']);
        $nowbutton_array = array('d', 'm', 'y');

        // Display Page
        echo '
                    <form action="" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="sended" value="edit">
                        <input type="hidden" name="group_action" value="' . $_POST['group_action'] . '">
                        <input type="hidden" name="user_group_id" value="' . $group_arr['user_group_id'] . '">
                        <input type="hidden" name="go" value="group_admin">
                        <table class="configtable" cellpadding="4" cellspacing="0">
                            <tr><td class="line" colspan="2">' . "Haupteinstellungen" . '</td></tr>
                               <tr>
                                   <td class="config">
                                       ' . "Name" . ':<br>
                                       <span class="small">' . "Der Name der Kategorie." . '</span>
                                   </td>
                                   <td>
                                     <input class="text" name="user_group_name" size="40" maxlength="50" value="' . $group_arr['user_group_name'] . '">
                                   </td>
                               </tr>
                            <tr>
                                <td class="config">
                                    ' . "Erstellungsdatum" . ':<br>
                                    <span class="small">' . "Die Kategorie wurde erstellt am ..." . '</span>
                                </td>
                                <td class="config" valign="top">
                                    <span class="small">
                                        <input class="text" size="3" maxlength="2" id="d" name="d" value="' . $date_arr['d'] . '"> .
                                        <input class="text" size="3" maxlength="2" id="m" name="m" value="' . $date_arr['m'] . '"> .
                                        <input class="text" size="5" maxlength="4" id="y" name="y" value="' . $date_arr['y'] . '">&nbsp;
                                    </span>
                                    ' . js_nowbutton($nowbutton_array, $FD->text('admin', 'today')) . '
                                </td>
                            </tr>
                            <tr>
                                <td class="config" valign="top">
                                    ' . "Ersteller" . ':<br>
                                    <span class="small">' . "Die Kategorie wurde erstellt von ..." . '</span>
                                </td>
                                <td class="config" valign="top">
                                    <input class="text" size="30" maxlength="100" readonly id="username" name="user_group_user_name" value="' . $group_arr['user_group_user_name'] . '">
                                    <input type="hidden" id="userid" name="user_group_user" value="' . $group_arr['user_group_user'] . '">
                                    <input class="button" type="button" onClick=\'' . openpopup('admin_finduser.php', 400, 400) . '\' value="' . $FD->text('admin', 'change') . '">
                                </td>
                            </tr>
                            <tr><td class="space"></td></tr>
                               <tr><td class="line" colspan="2">' . "Zus&auml;tzliche Einstellungen" . '</td></tr>
                               <tr>
                                   <td class="config">
                                     ' . "Symbol" . ': <span class="small">' . $FD->text('admin', 'optional') . '</span><br><br>
         ';
        if (image_exists('/group-images', 'staff_' . $group_arr['user_group_id'])) {
            echo '
                                    <img src="' . image_url('/group-images', 'staff_' . $group_arr['user_group_id']) . '" alt="' . $group_arr['user_group_name'] . '" border="0">
                                    <table>
                                        <tr>
                                            <td>
                                                <input type="checkbox" name="group_pic_delete" id="gpd" value="1" onClick=\'delalert ("gpd", "' . $FD->text('admin', 'js_delete_image') . '")\'>
                                            </td>
                                            <td>
                                                <span class="small"><b>' . $FD->text('admin', 'delete_image') . '</b></span>
                                            </td>
                                        </tr>
                                    </table>
            ';
        } else {
            echo '<span class="small">' . $FD->text('admin', 'no_image') . '</span><br>';
        }
        echo '                       <br>
                                </td>
                                <td class="config">
                                    <input name="user_group_pic" type="file" size="40" class="text"><br>
        ';
        if (image_exists('/group-images', 'staff_' . $group_arr['user_group_id'])) {
            echo '<span class="small"><b>' . $FD->text("admin", "replace_img") . '</b></span><br>';
        }
        echo '
                                    <span class="small">
                                        [' . $FD->text("admin", "max") . ' ' . $config_arr['group_pic_x'] . ' ' . $FD->text("admin", "resolution_x") . ' ' . $config_arr['group_pic_y'] . ' ' . $FD->text("admin", "pixel") . '] [' . $FD->text("admin", "max") . ' ' . $config_arr['group_pic_size'] . ' ' . $FD->text("admin", "kib") . ']
                                    </span>
                                </td>
                            </tr>
                               <tr>
                                   <td class="config">
                                       ' . "Titel" . ': <span class="small">' . $FD->text("admin", "optional") . '</span><br>
                                       <span class="small">' . "Titel den die Mitglieder der Gruppe tragen." . '</span>
                                   </td>
                                   <td>
                                     <input class="text" name="user_group_title" size="40" maxlength="50" value="' . $group_arr['user_group_title'] . '">
                                   </td>
                               </tr>
                               <tr>
                                   <td class="config">
                                       ' . 'Einf&auml;rbung' . ': <span class="small">' . $FD->text("admin", "optional") . '</span><br>
                                       <span class="small">' . "Farbliche Hervorhebung des Gruppentitels." . '</span>
                                   </td>
                                   <td class="configbig">
                                     <b>#</b> <input class="text colorpicker" name="user_group_color" size="7" maxlength="6" value="' . $group_arr['user_group_color'] . '">
                                     <span class="small">' . "freilassen um Titel nicht einzuf&auml;rben" . '</span><br>
                                     <span class="small">' . "[Hexadezimal-Farbcode]" . '</span>
                                   </td>
                               </tr>
                               <tr>
                                   <td class="config">
                                       ' . "Hervorhebung" . ': <span class="small">' . $FD->text("admin", "optional") . '</span><br>
                                       <span class="small">' . 'Besondere Hervorhebung des Gruppentitels.' . '</span>
                                   </td>
                                   <td>
                                       <select name="user_group_highlight" size="1">
                                        <option value="0" ' . getselected($group_arr['user_group_highlight'], 0) . '>keine Hervorhebung</option>
                                        <option value="1" ' . getselected($group_arr['user_group_highlight'], 1) . '>fett</option>
                                        <option value="2" ' . getselected($group_arr['user_group_highlight'], 2) . '>kursiv</option>
                                        <option value="5" ' . getselected($group_arr['user_group_highlight'], 5) . '>fett & kursiv</option>
                                       </select>
                                   </td>
                               </tr>
                            <tr>
                                <td class="config">
                                    ' . "Beschreibung" . ': <span class="small">' . $FD->text("admin", "optional") . '</span><br>
                                    <span class="small">' . "Ein kurzer Text &uuml;ber die Gruppe." . '</span>
                                </td>
                                <td class="config">
                                    <textarea class="text" name="user_group_description" rows="5" cols="50" wrap="virtual">' . $group_arr['user_group_description'] . '</textarea>
                                </td>
                            </tr>
                            <tr><td class="space"></td></tr>
                            <tr>
                                <td class="buttontd" colspan="2">
                                    <button class="button_new" type="submit">
                                        ' . $FD->text("admin", "button_arrow") . ' ' . $FD->text("admin", "save_changes_button") . '
                                    </button>
                                </td>
                            </tr>
                        </table>
                    </form>';
    } // Delete group
    elseif ($_POST['group_action'] == 'delete' && $_POST['user_group_id'] > 1) {
        // security functions
        settype($_POST['user_group_id'], 'integer');

        $index = $FD->db()->conn()->query('
                        SELECT `user_group_id`, `user_group_name`
                        FROM ' . $FD->env('DB_PREFIX') . "user_groups
                        WHERE user_group_id = '" . $_POST['user_group_id'] . "'");
        $group_arr = $index->fetch(PDO::FETCH_ASSOC);

        $group_arr['user_group_name'] = killhtml($group_arr['user_group_name']);

        $index_numusers = $FD->db()->conn()->query("
                                SELECT COUNT(`user_id`) AS 'num_users'
                                FROM `" . $FD->env('DB_PREFIX') . "user`
                                WHERE `user_group` = '" . $group_arr['user_group_id'] . "'");
        $group_arr['user_group_num_users'] = $index_numusers->fetchColumn();

        echo '
                    <form action="" method="post">
                        <input type="hidden" name="sended" value="delete">
                        <input type="hidden" name="group_action" value="' . $_POST['group_action'] . '">
                        <input type="hidden" name="user_group_id" value="' . $group_arr['user_group_id'] . '">
                        <input type="hidden" name="go" value="group_admin">
                        <table class="configtable" cellpadding="4" cellspacing="0">
                            <tr><td class="line" colspan="2">' . "Gruppe l&ouml;schen" . '</td></tr>
                            <tr>
                                <td class="configthin" style="width: 100%;">
                                    ' . "Soll diese Gruppe wirklich gel&ouml;scht werden:" . ' <b>' . $group_arr['user_group_name'] . '</b>
                                    (<b>' . $group_arr['user_group_num_users'] . '</b> Mitglieder)
                                </td>
                                <td class="config right top" style="padding: 0px;">
                                    ' . get_yesno_table('user_group_delete') . '
                                </td>
                            </tr>
                            <tr><td class="space"></td></tr>
                            <tr>
                                <td class="buttontd" colspan="2">
                                    <button class="button_new" type="submit">
                                        ' . $FD->text('admin', 'button_arrow') . ' ' . $FD->text('admin', 'do_action_button') . '
                                    </button>
                                </td>
                            </tr>
                        </table>
                    </form>
        ';
    }
}



//////////////////////////
// Display Default-Page //
//////////////////////////

// Display New group & group Listing
else {
    // New group
    // Display Error Messages
    if (isset ($_POST['sended'])) {
        $_POST['user_group_name'] = killhtml($_POST['user_group_name']);
        systext($FD->text('admin', 'note_notfilled'), $FD->text('admin', 'error'), TRUE);
    }

    // Display Add-Form
    if (!isset($_POST['cat_name']))
        $_POST['cat_name'] = '';
    echo '
                    <form action="" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="sended" value="add">
                        <input type="hidden" name="group_action" value="add">
                        <input type="hidden" name="go" value="group_admin">
                        <table class="configtable" cellpadding="4" cellspacing="0">
                            <tr><td class="line" colspan="2">' . "Gruppe hinzuf&uuml;gen" . '</td></tr>
                            <tr>
                                <td class="config">
                                    <span class="small">' . "Name" . ':</span>
                                </td>
                                <td class="config">
                                    <span class="small">' . "Symbol" . ': ' . $FD->text('admin', 'optional') . '</span>
                                </td>
                            </tr>
                            <tr valign="top">
                                <td class="config">
                                    <input class="text" name="user_group_name" size="40" maxlength="100" value="' . $_POST['cat_name'] . '">
                                </td>
                                <td class="config">
                                    <input name="user_group_pic" type="file" size="30" class="text"><br>
                                    <span class="small">
                                        [' . $FD->text('admin', 'max') . ' ' . $config_arr['group_pic_x'] . ' ' . $FD->text('admin', 'resolution_x') . ' ' . $config_arr['group_pic_y'] . ' ' . $FD->text('admin', 'pixel') . '] [' . $FD->text('admin', 'max') . ' ' . $config_arr['group_pic_size'] . ' ' . $FD->text('admin', 'kib') . ']
                                    </span>
                                </td>
                            </tr>
                            <tr><td class="space"></td></tr>
                            <tr>
                                <td class="buttontd" colspan="2">
                                    <button class="button_new" type="submit">
                                        ' . $FD->text('admin', 'button_arrow') . ' ' . $FD->text('page', 'new_cat_add_button') . '
                                    </button>
                                </td>
                            </tr>
                            <tr><td class="space"></td></tr>
                        </table>
                    </form>
    ';


    // group Listing
    echo '
                    <form action="" method="post">
                        <input type="hidden" name="go" value="group_admin">
                        <table class="configtable" cellpadding="4" cellspacing="0">
                            <tr><td class="line" colspan="4">' . "Gruppenverwaltung" . '</td></tr>
    ';

    // Get groups from DB
    $index = $FD->db()->conn()->query('
                    SELECT COUNT(*)
                    FROM `' . $FD->env('DB_PREFIX') . 'user_groups`
                    WHERE `user_group_id` > 1');

    // groups found
    if ($index->fetchColumn() > 0) {
        // display table head
        echo '
                            <tr>
                                <td class="config">Gruppenname &amp; Grafik</td>
                                <td class="config">Informationen</td>
                                <td class="config" width="20">Mitglieder</td>
                                <td class="config" width="20"></td>
                            </tr>
                            <tr><td class="space"></td></tr>
        ';
        $index = $FD->db()->conn()->query('
                        SELECT `user_group_id`, `user_group_name`, `user_group_user`, `user_group_date`
                        FROM `' . $FD->env('DB_PREFIX') . 'user_groups`
                        WHERE `user_group_id` > 1
                        ORDER BY `user_group_name`');
        while ($group_arr = $index->fetch(PDO::FETCH_ASSOC)) {
            $index_username = $FD->db()->conn()->query('
                                    SELECT `user_name`
                                    FROM `' . $FD->env('DB_PREFIX') . "user`
                                    WHERE `user_id` = '" . $group_arr['user_group_user'] . "'");
            $group_arr['user_group_user_name'] = $index_username->fetchColumn();

            $index_numusers = $FD->db()->conn()->query("
                                    SELECT COUNT(`user_id`) AS 'num_users'
                                    FROM `" . $FD->env('DB_PREFIX') . "user`
                                    WHERE `user_group` = '" . $group_arr['user_group_id'] . "'");
            $group_arr['user_group_num_users'] = $index_numusers->fetchColumn();

            // Display each Group
            echo '
                            <tr class="pointer" id="tr_' . $group_arr['user_group_id'] . '"
                                onmouseover="' . color_list_entry('input_' . $group_arr['user_group_id'], '#EEEEEE', '#64DC6A', 'this') . '"
                                onmouseout="' . color_list_entry('input_' . $group_arr['user_group_id'], 'transparent', '#49c24f', 'this') . '"
                                onclick="' . color_click_entry('input_' . $group_arr['user_group_id'], '#EEEEEE', '#64DC6A', 'this', TRUE) . '"
                            >
            ';
            echo '
                                <td class="configthin middle">
                                    <b>' . $group_arr['user_group_name'] . '</b>
            ';
            if (image_exists('/group-images', 'staff_' . $group_arr['user_group_id'])) {
                echo '<br><img src="' . image_url('/group-images', 'staff_' . $group_arr['user_group_id']) . '" alt="' . $group_arr['user_group_name'] . '" border="0">';
            }
            echo '
                                </td>
                                <td class="configthin middle">
                                    <span class="small">
                                        ' . $FD->text('page', 'list_cat_created_by') . ' <b>' . $group_arr['user_group_user_name'] . '</b> ' . $FD->text('page', 'list_cat_created_on') . ' <b>' . date($FD->config('date'), $group_arr['user_group_date']) . '</b>
                                    </span>
                                </td>
                                <td class="configthin center middle">' . $group_arr['user_group_num_users'] . '</td>
                                <td class="configthin middle" style="text-align: center; vertical-align: middle;">
                                    <input class="pointer" type="radio" name="user_group_id" id="input_' . $group_arr['user_group_id'] . '" value="' . $group_arr['user_group_id'] . '"
                                        onclick="' . color_click_entry('this', '#EEEEEE', '#64DC6A', 'tr_' . $group_arr['user_group_id'], TRUE) . '"
                                    >
                                </td>
                            </tr>
            ';
        }

        // End of Form & Table incl. Submit-Button
        echo '
                            <tr><td class="space"></td></tr>
                            <tr>
                                <td style="text-align:right;" colspan="4">
                                    <select name="group_action" size="1">
                                        <option value="edit">' . $FD->text('admin', 'selection_edit') . '</option>
                                        <option value="delete">' . $FD->text('admin', 'selection_delete') . '</option>
                                    </select>
                                </td>
                            </tr>
                            <tr><td class="space"></td></tr>
                            <tr>
                                <td class="buttontd" colspan="4">
                                    <button class="button_new" type="submit">
                                        ' . $FD->text('admin', 'button_arrow') . ' ' . $FD->text('admin', 'do_action_button') . '
                                    </button>
                                </td>
                            </tr>
        ';

    } else {
        echo '
                            <tr><td class="space"></td></tr>
                            <tr>
                                <td class="config center" colspan="4">Keine Gruppen gefunden!</td>
                            </tr>
                            <tr><td class="space"></td></tr>
        ';
    }

    echo '
                    </form>
    ';

    // admin-group
    echo '
                    <form action="" method="post">
                        <input type="hidden" name="go" value="group_admin">
                        <input type="hidden" name="user_group_id" value="1">
                        <input type="hidden" name="group_action" value="edit">
                            <tr><td class="space"><br></td></tr>
                            <tr><td class="line" colspan="4">' . "Verwaltung der Administrator-Gruppe" . '</td></tr>
    ';

    // Get admin group from DB
    $index = $FD->db()->conn()->query('
                    SELECT `user_group_id`, `user_group_name`, `user_group_user`, `user_group_date`
                    FROM `' . $FD->env('DB_PREFIX') . 'user_groups`
                    WHERE `user_group_id` = 1
                    LIMIT 0,1');

    // get group data
    $group_arr = $index->fetch(PDO::FETCH_ASSOC);

    $index_username = $FD->db()->conn()->query('
                            SELECT `user_name`
                            FROM `' . $FD->env('DB_PREFIX') . "user`
                            WHERE `user_id` = '" . $group_arr['user_group_user'] . "'");
    $group_arr['user_group_user_name'] = $index_username->fetchColumn();

    $index_numusers = $FD->db()->conn()->query("
                            SELECT COUNT(`user_id`) AS 'num_users'
                            FROM `" . $FD->env('DB_PREFIX') . "user`
                            WHERE `user_is_admin` = '1'");
    $group_arr['user_group_num_users'] = $index_numusers->fetchColumn();

    // Display the Group
    echo '
                            <tr>
                                <td class="configthin middle">
                                    <b>' . $group_arr['user_group_name'] . '</b>
    ';
    if (image_exists('/group-images', 'staff_' . $group_arr['user_group_id'])) {
        echo '<br><img src="' . image_url('/group-images', 'staff_' . $group_arr['user_group_id']) . '" alt="' . $group_arr['user_group_name'] . '" border="0">';
    }
    echo '
                                </td>
                                <td class="configthin middle">
                                    <span class="small">
                                        ' . $FD->text('page', 'list_cat_created_by') . ' <b>' . $group_arr['user_group_user_name'] . '</b> ' . $FD->text('page', 'list_cat_created_on') . ' <b>' . date($FD->config('date'), $group_arr['user_group_date']) . '</b>
                                    </span>
                                </td>
                                <td class="configthin right middle" colspan="2"><b>' . $group_arr['user_group_num_users'] . '</b> Mitglieder</td>
                            </tr>
                            <tr><td class="space"></td></tr>
                            <tr>
                                <td class="buttontd" colspan="4">
                                    <button class="button_new" type="submit">
                                        ' . $FD->text('admin', 'button_arrow') . ' ' . "Administratorgruppe bearbeiten" . '
                                    </button>
                                </td>
                            </tr>
                        </table>
                    </form>
    ';
}
?>
