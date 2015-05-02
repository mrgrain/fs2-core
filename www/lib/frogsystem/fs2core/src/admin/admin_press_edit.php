<?php if (!defined('ACP_GO')) die('Unauthorized access!');

/////////////////////////
/// Edit Press report ///
/////////////////////////
if ((isset($_POST['title']) AND $_POST['title'] != '')
    && (isset($_POST['url']) AND $_POST['url'] != '' AND $_POST['url'] != 'http://')
    && (isset($_POST['day']) AND $_POST['day'] != '')
    && (isset($_POST['month']) AND $_POST['month'] != '')
    && (isset($_POST['year']) AND $_POST['year'] != '')
    && (isset($_POST['text']) AND $_POST['text'] != '')
    && $_POST['press_action'] == 'edit'
    && $_POST['sended'] == 'edit'
    && isset($_POST['press_id'][0])
) {
    $_POST['press_id'] = $_POST['press_id'][0];
    settype($_POST['press_id'], 'integer');

    settype($_POST['day'], 'integer');
    settype($_POST['month'], 'integer');
    settype($_POST['year'], 'integer');
    $datum = mktime(0, 0, 0, $_POST['month'], $_POST['day'], $_POST['year']);

    settype($_POST['game'], 'integer');
    settype($_POST['cat'], 'integer');
    settype($_POST['lang'], 'integer');

    $stmt = $FD->db()->conn()->prepare(
        'UPDATE ' . $FD->env('DB_PREFIX') . "press
               SET press_title = ?,
                   press_url = ?,
                   press_date = '$datum',
                   press_intro = ?,
                   press_text = ?,
                   press_note = ?,
                   press_lang = '$_POST[lang]',
                   press_game = '$_POST[game]',
                   press_cat = '$_POST[cat]'
               WHERE press_id = '$_POST[press_id]'");
    $stmt->execute(array($_POST['title'],
        $_POST['url'],
        $_POST['intro'],
        $_POST['text'],
        $_POST['note']));
    systext('&Auml;nderungen wurden erfolgreich gespeichert!');

    unset($_POST['press_action']);
    unset($_POST['sended']);
    unset($_POST['press_id']);
}


////////////////////////////
/// Delete Press report ////
////////////////////////////
elseif (isset($_POST['press_action']) && $_POST['press_action'] == 'delete'
    && isset($_POST['sended']) && $_POST['sended'] == 'delete'
    && isset($_POST['press_id'])
) {
    $_POST['press_id'] = $_POST['press_id'][0];
    settype($_POST['press_id'], 'integer');

    if ($_POST['delete_press'])   // Delete Press report
    {
        $FD->db()->conn()->exec('DELETE FROM ' . $FD->env('DB_PREFIX') . "press WHERE press_id = '$_POST[press_id]'");
        systext('Der Pressebericht wurde gel&ouml;scht.');
    } else {
        systext('Der Pressebericht wurde nicht gel&ouml;scht.');
    }

    unset($_POST['delete_press']);
    unset($_POST['press_action']);
    unset($_POST['sended']);
    unset($_POST['press_id']);
}


/////////////////////////
/// Show Press report ///
/////////////////////////
elseif (isset($_POST['press_action']) && $_POST['press_action'] == 'edit'
    && isset($_POST['press_id'])
) {
    $_POST['press_id'] = $_POST['press_id'][0];
    settype($_POST['press_id'], 'integer');

    // Load Press Report
    $index = $FD->db()->conn()->query('SELECT * FROM ' . $FD->env('DB_PREFIX') . "press WHERE press_id = '$_POST[press_id]'");
    $press_arr = $index->fetch(PDO::FETCH_ASSOC);

    $press_arr['press_title'] = killhtml($press_arr['press_title']);
    $press_arr['press_url'] = killhtml($press_arr['press_url']);
    $press_arr['press_intro'] = killhtml($press_arr['press_intro']);
    $press_arr['press_text'] = killhtml($press_arr['press_text']);
    $press_arr['press_note'] = killhtml($press_arr['press_note']);
    settype($_POST['press_game'], 'integer');
    settype($_POST['press_cat'], 'integer');
    settype($_POST['press_lang'], 'integer');

    //If required, add http://
    if ($press_arr['press_url'] == '') {
        $press_arr['press_url'] = 'http://';
    }
    // Create Date
    if ($press_arr['press_date'] != 0) {
        $date['tag'] = date('d', $press_arr['press_date']);
        $date['monat'] = date('m', $press_arr['press_date']);
        $date['jahr'] = date('Y', $press_arr['press_date']);
    } else {
        unset($date);
    }
    //Time Array for "Today" Button
    $heute['time'] = time();
    $heute['tag'] = date('d', $heute['time']);
    $heute['monat'] = date('m', $heute['time']);
    $heute['jahr'] = date('Y', $heute['time']);


    //Error Message
    if (isset($_POST['sended']) && $_POST['sended'] == 'edit') {
        echo get_systext($FD->text('admin', 'changes_not_saved') . '<br>' . $FD->text('admin', 'form_not_filled'), $FD->text('admin', 'error'), 'red', $FD->text('admin', 'icon_save_error'));


        $press_arr['press_title'] = killhtml($_POST['title']);
        $press_arr['press_url'] = killhtml($_POST['url']);
        $press_arr['press_intro'] = killhtml($_POST['intro']);
        $press_arr['press_text'] = killhtml($_POST['text']);
        $press_arr['press_note'] = killhtml($_POST['note']);

        $date['tag'] = $_POST['day'];
        $date['monat'] = $_POST['month'];
        $date['jahr'] = $_POST['year'];
        settype($date['tag'], 'integer');
        settype($date['monat'], 'integer');
        settype($date['jahr'], 'integer');

        $press_arr['press_game'] = $_POST['game'];
        $press_arr['press_cat'] = $_POST['cat'];
        $press_arr['press_lang'] = $_POST['lang'];
        settype($_POST['press_game'], 'integer');
        settype($_POST['press_cat'], 'integer');
        settype($_POST['press_lang'], 'integer');
    }


    echo '
                    <form action="" method="post">
                        <input type="hidden" value="press_edit" name="go">
                        <input type="hidden" value="edit" name="press_action">
                        <input type="hidden" value="edit" name="sended">
                        <input type="hidden" value="' . $press_arr['press_id'] . '" name="press_id[0]">
                        <table class="content" cellpadding="3" cellspacing="0">
                            <tr><td colspan="2"><h3>Pressebericht bearbeiten</h3><hr></td></tr>
                            <tr>
                                <td class="config" valign="top">
                                    Titel:<br>
                                    <font class="small">Der Name der Website.</font>
                                </td>
                                <td class="config" valign="top">
                                    <input class="text" name="title" size="51" maxlength="150" value="' . $press_arr['press_title'] . '">
                                </td>
                            </tr>
                            <tr>
                                <td class="config" valign="top">
                                    URL:<br>
                                    <font class="small">Link zum Artikel.</font>
                                </td>
                                <td class="config" valign="top">
                                    <input class="text" name="url" size="51" maxlength="255"  value="' . $press_arr['press_url'] . '">
                                </td>
                            </tr>
                            <tr>
                                <td class="config" valign="top">
                                    Datum:<br>
                                    <font class="small">Datum der Ver&ouml;ffentlichung.</font>
                                </td>
                                <td class="config" valign="top">
                                    <input class="text" size="1" name="day" id="day" maxlength="2" value="' . $date['tag'] . '"> .
                                    <input class="text" size="1" name="month" id="month"  maxlength="2" value="' . $date['monat'] . '"> .
                                    <input class="text" size="3" name="year" id="year"  maxlength="4" value="' . $date['jahr'] . '">&nbsp;
                                    <input  type="button" value="Heute"
                                     onClick=\'document.getElementById("day").value="' . $heute['tag'] . '";
                                               document.getElementById("month").value="' . $heute['monat'] . '";
                                               document.getElementById("year").value="' . $heute['jahr'] . '";\'>&nbsp;
                                    <input  type="button" value="Zur&uuml;cksetzen"
                                     onClick=\'document.getElementById("day").value="' . $date['tag'] . '";
                                               document.getElementById("month").value="' . $date['monat'] . '";
                                               document.getElementById("year").value="' . $date['jahr'] . '";\'>
                                </td>
                            </tr>
                            <tr>
                                <td class="config" valign="top">
                                    Einleitung: <font class="small">' . $FD->text("admin", "optional") . '</font><br />
                                    <font class="small">Eine kurze Einleitung zum Pressebericht.</font>
                                </td>
                                <td class="config" valign="top">
                                    ' . create_editor('intro', $press_arr['press_intro'], 408, 75, '', false) . '
                                </td>
                            </tr>
                            <tr>
                                <td class="config" valign="top">
                                    Text:<br>
                                    <font class="small">Ein kleiner Auszug aus dem vorgestellten Pressebericht.</font>
                                </td>
                                <td class="config" valign="top">
                                    ' . create_editor('text', $press_arr['press_text'], 330, 150) . '
                                </td>
                            </tr>
                            <tr>
                                <td class="config" valign="top">
                                    Anmerkungen: <font class="small">' . $FD->text("admin", "optional") . '</font><br />
                                    <font class="small">Anmerkungen zum Pressebericht.<br />
                                    (z.B. die Wertung eines Tests)</font>
                                </td>
                                <td class="config" valign="top">
                                    ' . create_editor('note', $press_arr['press_note'], 408, 75, '', false) . '
                                </td>
                            </tr>
                            <tr>
                                <td class="config" valign="top">
                                    Spiel:<br>
                                    <font class="small">Spiel, auf das sich der Artikel bezieht.</font>
                                </td>
                                <td class="config" valign="top">
                                    <select name="game" size="1" class="text">';

    $index = $FD->db()->conn()->query('SELECT * FROM ' . $FD->env('DB_PREFIX') . "press_admin
                                        WHERE type = '1' ORDER BY title");
    while ($game_arr = $index->fetch(PDO::FETCH_ASSOC)) {
        echo '
                                        <option value="' . $game_arr['id'] . '"' .
            ($press_arr['press_game'] == $game_arr['id'] ? ' selected="selected"' : '') .
            '>' . $game_arr['title'] . '</option>
        ';
    }
    echo '
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td class="config" valign="top">
                                    Kategorie:<br>
                                    <font class="small">Die Kategorie, der der Artikel angeh&ouml;rt.</font>
                                </td>
                                <td class="config" valign="top">
                                    <select name="cat" size="1" class="text">';

    $index = $FD->db()->conn()->query('SELECT * FROM ' . $FD->env('DB_PREFIX') . "press_admin
                                        WHERE type = '2' ORDER BY title");
    while ($cat_arr = $index->fetch(PDO::FETCH_ASSOC)) {
        echo '
                                        <option value="' . $cat_arr['id'] . '"' .
            ($press_arr['press_cat'] == $cat_arr['id'] ? ' selected="selected"' : '') .
            '>' . $cat_arr['title'] . '</option>
        ';
    }
    echo '
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td class="config" valign="top">
                                    Sprache:<br>
                                    <font class="small">Sprache, in der der Artikel verfasst wurde.</font>
                                </td>
                                <td class="config" valign="top">
                                    <select name="lang" size="1" class="text">';

    $index = $FD->db()->conn()->query('SELECT * FROM ' . $FD->env('DB_PREFIX') . "press_admin
                                        WHERE type = '3' ORDER BY title");
    while ($lang_arr = $index->fetch(PDO::FETCH_ASSOC)) {
        echo '
                                        <option value="' . $lang_arr['id'] . '"' .
            ($press_arr['press_lang'] == $lang_arr['id'] ? ' selected="selected"' : '') .
            '>' . $lang_arr['title'] . '</option>
        ';
    }
    echo '
                                    </select>
                                </td>
                            </tr>
                            <tr><td>&nbsp;</td></tr>
                            <tr>
                                <td class="buttontd" colspan="2">
                                    <button class="button_new" type="submit">
                                        ' . $FD->text("admin", "button_arrow") . ' ' . $FD->text("admin", "save_changes_button") . '
                                    </button>
                                </td>
                            </tr>
                        </table>
                    </form>
    ';
}


/////////////////////////////
/// Delete Press report /////
/////////////////////////////
elseif (isset($_POST['press_action'])
    && $_POST['press_action'] == 'delete'
    && isset($_POST['press_id'])
) {
    $_POST['press_id'] = $_POST['press_id'][0];
    settype($_POST['press_id'], 'integer');

    $index = $FD->db()->conn()->query('SELECT * FROM ' . $FD->env('DB_PREFIX') . "press WHERE press_id = $_POST[press_id]");
    $press_arr = $index->fetch(PDO::FETCH_ASSOC);

    $press_arr['press_title'] = killhtml($press_arr['press_title']);
    $press_arr['press_url'] = killhtml($press_arr['press_url']);;
    $press_arr['press_text'] = killhtml($press_arr['press_text']);
    $press_arr['press_date'] = date('d.m.Y', $press_arr['press_date']);
    settype($press_arr['press_game'], 'integer');
    settype($press_arr['press_cat'], 'integer');
    settype($press_arr['press_lang'], 'integer');

    $index = $FD->db()->conn()->query('SELECT title FROM ' . $FD->env('DB_PREFIX') . "press_admin WHERE id = $press_arr[press_game] AND type = 1");
    $press_arr['press_game'] = $index->fetchColumn();
    $index = $FD->db()->conn()->query('SELECT title FROM ' . $FD->env('DB_PREFIX') . "press_admin WHERE id = $press_arr[press_cat] AND type = 2");
    $press_arr['press_cat'] = $index->fetchColumn();
    $index = $FD->db()->conn()->query('SELECT title FROM ' . $FD->env('DB_PREFIX') . "press_admin WHERE id = $press_arr[press_lang] AND type = 3");
    $press_arr['press_lang'] = $index->fetchColumn();

    echo '
                    <form action="" method="post">
                        <input type="hidden" value="press_edit" name="go">
                        <input type="hidden" value="delete" name="press_action">
                        <input type="hidden" value="delete" name="sended">
                        <input type="hidden" value="' . $press_arr['press_id'] . '" name="press_id">
                        <table class="content" cellpadding="3" cellspacing="0">
                            <tr><td colspan="2"><h3>Pressebericht l&ouml;schen</h3><hr></td></tr>
                            <tr align="left" valign="top">
                                <td class="config" colspan="2">
                                    Pressebericht l&ouml;schen: ' . $press_arr['press_title'] . '
                                    <span class="small">(' . $press_arr['press_game'] . ', ' . $press_arr['press_cat'] . ', ' . $press_arr['press_lang'] . ')</span>
                                </td>
                            </tr>
                            <tr><td>&nbsp;</td></tr>
                            <tr valign="top">
                                <td width="50%" class="config">
                                    Soll der unten stehende Pressebericht wirklich gel&ouml;scht werden?
                                </td>
                                <td width="50%" align="right">
                                    <select name="delete_press" size="1">
                                        <option value="0">Pressebericht nicht l&ouml;schen</option>
                                        <option value="1">Pressebericht l&ouml;schen</option>
                                    </select>
                                    <input type="submit" value="' . $FD->text("admin", "do_action_button_long") . '">
                                </td>
                            </tr>
                            <tr><td>&nbsp;</td></tr>
                            <tr align="left" valign="top">
                                <td class="config" colspan="2">
                                    ' . $press_arr['press_title'] . '
                                    <span class="small">(' . $press_arr['press_game'] . ', ' . $press_arr['press_cat'] . ', ' . $press_arr['press_lang'] . ')</span><br />
                                    <span class="small">am ' . $press_arr['press_date'] . ' auf <a href="' . $press_arr['press_url'] . '" target="_blank" class="small">' . cut_in_string($press_arr['press_url'], 50, "...") . '</a></span>
                                    <br /><br />
                                    <i>&bdquo;' . $press_arr['press_text'] . '&ldquo;</i>

                                </td>
                            </tr>
                        </table>
                    </form>';
}


//////////////////////////
/// List Press reports ///
//////////////////////////
$index = $FD->db()->conn()->query('SELECT COUNT(press_id) FROM ' . $FD->env('DB_PREFIX') . 'press');
$num_rows = $index->fetchColumn();

if (!isset($_POST['press_id']) && $num_rows > 0) {

    $filterwhere = '';
    if (isset($_POST['gameid']) AND $_POST['gameid'] != 0) {
        if ($filterwhere != '') {
            $filterwhere .= ' AND';
        }
        settype($_POST['gameid'], 'integer');
        $filterwhere .= ' press_game = ' . $_POST['gameid'];
    }
    if (isset($_POST['catid']) AND $_POST['catid'] != 0) {
        if ($filterwhere != '') {
            $filterwhere .= ' AND';
        }
        settype($_POST['catid'], 'integer');
        $filterwhere .= ' press_cat = ' . $_POST['catid'];
    }
    if (isset($_POST['langid']) AND $_POST['langid'] != 0) {
        if ($filterwhere != '') {
            $filterwhere .= ' AND';
        }
        settype($_POST['langid'], 'integer');
        $filterwhere .= ' press_lang = ' . $_POST['langid'];
    }
    if ($filterwhere != '') {
        $filterwhere = 'WHERE' . $filterwhere;
    }

    if (!isset($_POST['gameid']))
        $_POST['gameid'] = 0;
    if (!isset($_POST['order_by'])) {
        $_POST['order_by'] = 'press_date';
    }
    if (!isset($_POST['order_type'])) {
        $_POST['order_type'] = 'desc';
    }
    if (!isset($_POST['catid']))
        $_POST['catid'] = 0;
    if (!isset($_POST['langid']))
        $_POST['langid'] = 0;

    echo '
                    <form action="" method="post">
                        <input type="hidden" value="press_edit" name="go">
                        <table class="content" cellpadding="3" cellspacing="0">
                            <tr><td colspan=4"><h3>Auswahl filtern</h3><hr></td></tr>
                            <tr>
                                <td class="config">
                                    Spiele:
                                </td>
                                <td class="config">
                                    <select name="gameid" size="1">
                                        <option value="0"' .
        ($_POST['gameid'] == 0 ? ' selected="selected"' : '') .
        '>alle anzeigen</option>';

    $index = $FD->db()->conn()->query('SELECT * FROM ' . $FD->env('DB_PREFIX') . 'press_admin WHERE type = 1 ORDER BY title');
    while ($game_arr = $index->fetch(PDO::FETCH_ASSOC)) {
        echo '
                                        <option value="' . $game_arr['id'] . '"' .
            ($_POST['gameid'] == $game_arr['id'] ? ' selected="selected"' : '') .
            '>' . $game_arr['title'] . '</option>';
    }
    echo '
                                    </select>
                                </td>
                                <td class="config" style="text-align:center;" rowspan="3">
                                    Sortieren nach:
                                    <select name="order_by" size="1">
                                        <option value="press_date"' .
        ($_POST['order_by'] == 'press_date' ? ' selected="selected"' : '') .
        '>Datum</option>
                                        <option value="press_title"' .
        ($_POST['order_by'] == 'press_title' ? ' selected="selected"' : '') .
        '>Titel</option>
                                    </select>&nbsp;
                                    <select name="order_type" size="1">
                                        <option value="asc"' .
        ($_POST['order_type'] == 'asc' ? ' selected="selected"' : '') .
        '>aufsteigend</option>
                                        <option value="desc"' .
        ($_POST['order_type'] == 'desc' ? ' selected="selected"' : '') .
        '>absteigend</option>
                                    </select>
                                    <br /><br />
                                    <input  type="submit" value="Filter anwenden">
                                </td>
                            </tr>
                            <tr>
                                <td class="config">
                                    Kategorien:
                                </td>
                                <td class="config">
                                    <select name="catid" size="1">
                                        <option value="0"' .
        ($_POST['catid'] == 0 ? ' selected="selected"' : '') .
        '>alle anzeigen</option>';

    $index = $FD->db()->conn()->query('SELECT * FROM ' . $FD->env('DB_PREFIX') . 'press_admin WHERE type = 2 ORDER BY title');
    while ($cat_arr = $index->fetch(PDO::FETCH_ASSOC)) {
        echo '
                                        <option value="' . $cat_arr['id'] . '"' .
            ($_POST['catid'] == $cat_arr['id'] ? ' selected="selected"' : '') .
            '>' . $cat_arr['title'] . '</option>';
    }
    echo '
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td class="config">
                                    Sprachen:
                                </td>
                                <td class="config">
                                    <select name="langid" size="1">
                                        <option value="0"' .
        ($_POST['langid'] == 0 ? ' selected="selected"' : '') .
        '>alle anzeigen</option>';

    $index = $FD->db()->conn()->query('SELECT * FROM ' . $FD->env('DB_PREFIX') . 'press_admin WHERE type = 3 ORDER BY title');
    while ($lang_arr = $index->fetch(PDO::FETCH_ASSOC)) {
        echo '
                                        <option value="' . $lang_arr['id'] . '"' .
            ($_POST['langid'] == $lang_arr['id'] ? ' selected="selected"' : '') .
            '>' . $lang_arr['title'] . '</option>';
    }
    echo '
                                    </select>
                                </td>
                            </tr>
                            <tr><td>&nbsp;</td></tr>
                        </table>
                    </form>
    ';


//////////////////////////////
//// Select Press reports ////
//////////////////////////////
    $index = $FD->db()->conn()->query('SELECT COUNT(*)
                          FROM ' . $FD->env('DB_PREFIX') . "press
                          $filterwhere
                          ORDER BY $_POST[order_by] $_POST[order_type]");
    if ($index->fetchColumn() > 0) {
        echo '
                    <form action="" method="post">
                        <input type="hidden" value="press_edit" name="go">
                        <table class="content select_list" cellpadding="3" cellspacing="0">
                            <tr><td colspan="4"><h3>Presseberichte ausw&auml;hlen</h3><hr></td></tr>
                            <tr>
                                <td class="config" width="40%">
                                    Titel
                                </td>
                                <td class="config" width="30%">
                                </td>
                                <td class="config" width="10%">
                                    Datum
                                </td>
                                <td class="config" style="text-align:right;" width="20%">
                                    Auswahl
                                </td>
                            </tr>
        ';

        $index = $FD->db()->conn()->query('SELECT press_id, press_title, press_date, press_game, press_cat, press_lang
                          FROM ' . $FD->env('DB_PREFIX') . "press
                          $filterwhere
                          ORDER BY $_POST[order_by] $_POST[order_type]");
        while ($press_arr = $index->fetch(PDO::FETCH_ASSOC)) {
            $index2 = $FD->db()->conn()->query('SELECT title FROM ' . $FD->env('DB_PREFIX') . "press_admin WHERE id = $press_arr[press_game] AND type = 1");
            $press_arr['press_game'] = $index2->fetchColumn();
            $index2 = $FD->db()->conn()->query('SELECT title FROM ' . $FD->env('DB_PREFIX') . "press_admin WHERE id = $press_arr[press_cat] AND type = 2");
            $press_arr['press_cat'] = $index2->fetchColumn();
            $index2 = $FD->db()->conn()->query('SELECT title FROM ' . $FD->env('DB_PREFIX') . "press_admin WHERE id = $press_arr[press_lang] AND type = 3");
            $press_arr['press_lang'] = $index2->fetchColumn();

            $press_arr['press_date'] = date('d.m.Y', $press_arr['press_date']);
            echo '
                            <tr class="thin select_entry">
                                <td class="configthin">
                                    ' . $press_arr['press_title'] . '
                                </td>
                                <td class="">
                                    <span class="small">(' . $press_arr['press_game'] . ', ' . $press_arr['press_cat'] . ', ' . $press_arr['press_lang'] . ')</span>
                                </td>
                                <td class="">
                                    ' . $press_arr['press_date'] . '
                                </td>
                                <td class="top right">
                                    <input class="select_box" type="checkbox" name="press_id[]" value="' . $press_arr['press_id'] . '">
                                </td>
                            </tr>
            ';
        }
        echo '
                            <tr><td>&nbsp;</td></tr>
                            <tr>
                                <td class="right" colspan="4">
                                   <select class="select_type" name="press_action" size="1">
                                     <option class="select_one" value="edit">' . $FD->text('admin', 'selection_edit') . '</option>
                                     <option class="select_red select_one" value="delete">' . $FD->text('admin', 'selection_delete') . '</option>
                                   </select>
                                </td>
                            </tr>
                            <tr>
                                <td class="buttontd" colspan="4">
                                    <button class="button_new" type="submit">
                                        ' . $FD->text("admin", "button_arrow") . ' ' . $FD->text('admin', 'do_action_button_long') . '
                                    </button>
                                </td>
                            </tr>
                        </table>
                    </form>
        ';
    } else {
        echo $FD->text('page', 'note_noreleases');
    }
} elseif ($num_rows <= 0) {
    echo $FD->text('page', 'note_noreleases');
}
?>
