<?php if (!defined('ACP_GO')) die('Unauthorized access!');

echo noscript_nohidden();

///////////////////
//// Add Video ////
///////////////////

if (
    isset($_POST['video_title']) && $_POST['video_title'] != '' &&
    (
        ($_POST['video_type'] == 1 && isset($_POST['video_url']) && $_POST['video_url'] != '') ||
        ($_POST['video_type'] == 2 && isset($_POST['video_youtube']) && $_POST['video_youtube'] != '') ||
        ($_POST['video_type'] == 3 && isset($_POST['video_myvideo']) && $_POST['video_myvideo'] != '') ||
        ($_POST['video_type'] == -1 && isset($_POST['video_other']) && $_POST['video_other'] != '')
    )
) {
    settype($_POST['video_type'], 'integer');
    settype($_POST['dl_id'], 'integer');

    settype($_POST['video_h'], 'integer');
    settype($_POST['video_m'], 'integer');
    settype($_POST['video_s'], 'integer');

    $_POST['video_lenght'] = $_POST['video_h'] * 3600 + $_POST['video_m'] * 60 + $_POST['video_s'];

    switch ($_POST['video_type']) {
        case 3:
            $_POST['video_x'] = trim($_POST['video_myvideo']);
            break;
        case 2:
            $_POST['video_x'] = trim($_POST['video_youtube']);
            break;
        case -1:
            $_POST['video_x'] = trim($_POST['video_other']);
            break;
        default:
            $_POST['video_x'] = trim($_POST['video_url']);
            break;
    }

    $stmt = $FD->db()->conn()->prepare(
        'INSERT INTO
                         ' . $FD->env('DB_PREFIX') . "player
                         ( video_type, video_x, video_title, video_lenght, video_desc, dl_id )
                     VALUES (
                             '" . $_POST['video_type'] . "',
                             ?,
                             ?,
                             '" . $_POST['video_lenght'] . "',
                             ?,
                             '" . $_POST['dl_id'] . "'
                            )");
    $stmt->execute(array($_POST['video_x'], $_POST['video_title'], $_POST['video_desc']));
    systext('Video erfolgreich eingetragen', $FD->text('admin', 'info'));

    // Unset Vars
    unset ($_POST);
}

//////////////////////
///// Video Form /////
//////////////////////

if (TRUE) {
    // Display Error Messages
    if (isset ($_POST['sended'])) {
        systext($FD->text('admin', 'note_notfilled'), $FD->text('admin', 'error'), TRUE);
    }

    $_POST['video_title'] = isset($_POST['video_title']) ? killhtml($_POST['video_title']) : '';
    $_POST['video_desc'] = isset($_POST['video_desc']) ? killhtml($_POST['video_desc']) : '';
    settype($_POST['video_type'], 'integer');
    settype($_POST['dl_id'], 'integer');

    $_POST['video_url'] = isset($_POST['video_url']) ? killhtml($_POST['video_url']) : '';
    $_POST['video_youtube'] = isset($_POST['video_youtube']) ? killhtml($_POST['video_youtube']) : '';
    $_POST['video_myvideo'] = isset($_POST['video_myvideo']) ? killhtml($_POST['video_myvideo']) : '';
    $_POST['video_other'] = isset($_POST['video_other']) ? killhtml($_POST['video_other']) : '';

    if (!isset($_POST['video_h'])) $_POST['video_h'] = '';
    if ($_POST['video_h'] != '') {
        $_POST['video_h'] = add_zero($_POST['video_h']);
    }
    if (!isset($_POST['video_m'])) $_POST['video_m'] = '';
    if ($_POST['video_m'] != '') {
        $_POST['video_m'] = add_zero($_POST['video_m']);
    }
    if (!isset($_POST['video_s'])) $_POST['video_s'] = '';
    if ($_POST['video_s'] != '') {
        $_POST['video_s'] = add_zero($_POST['video_s']);
    }

    $display_arr['tr_1'] = 'hidden';
    $display_arr['tr_2'] = 'hidden';
    $display_arr['tr_3'] = 'hidden';
    $display_arr['tr_-1'] = 'hidden';

    switch ($_POST['video_type']) {
        case 3:
            $display_arr['tr_3'] = 'default';
            break;
        case 2:
            $display_arr['tr_2'] = 'default';
            break;
        case -1:
            $display_arr['tr_-1'] = 'default';
            break;
        default:
            $display_arr['tr_1'] = 'default';
            break;
    }

    echo '
                    <form action="" method="post">
                                                <input type="hidden" value="player_add" name="go">
                        <input type="hidden" name="sended" value="1">
                                                <table class="configtable" cellpadding="4" cellspacing="0">
                                                        <tr><td class="line" colspan="2">Video hinzuf&uuml;gen</td></tr>
                            <tr>
                                <td class="config">
                                    Titel:<br>
                                    <span class="small">Der Titel des Videos</span>
                                </td>
                                <td class="config">
                                    <input class="text" size="45" maxlength="100" name="video_title" value="' . $_POST['video_title'] . '">
                                </td>
                            </tr>
                            <tr>
                                <td class="config">
                                    Quelle:<br>
                                    <span class="small">Quelle aus der das Video stammt.</span>
                                </td>
                                <td class="config" valign="top">
                                    <select name="video_type" size="1"
                                         onChange="show_one(\'tr_1|tr_2|tr_3|tr_-1\', \'1|2|3|-1\', this)"
                                                                        >
                                            <option value="1" ' . getselected($_POST['video_type'], 1) . '>eigenes Video</option>
                                            <option value="2" ' . getselected($_POST['video_type'], 2) . '>YouTube</option>
                                            <option value="3" ' . getselected($_POST['video_type'], 3) . '>MyVideo</option>
                                            <option value="-1" ' . getselected($_POST['video_type'], -1) . '>andere Quelle</option>
                                                                        </select>
                                </td>
                            </tr>
                            <tr class="' . $display_arr['tr_1'] . '" id="tr_1">
                                <td class="config">
                                    URL:<br>
                                    <span class="small">URL zur Video-Datei (FLV-Format).</span>
                                </td>
                                <td class="config" valign="top">
                                    <input class="text" size="45" maxlength="255" name="video_url" value="' . $_POST['video_url'] . '">
                                </td>
                            </tr>
                            <tr class="' . $display_arr['tr_2'] . '" id="tr_2">
                                <td class="config">
                                    YouTube-ID:<br>
                                    <span class="small">http://youtube.com/watch?v=<b>YouTube-ID</b></span>
                                </td>
                                <td class="config" valign="top">
                                    <input class="text" size="25" maxlength="20" name="video_youtube" value="' . $_POST['video_youtube'] . '">
                                </td>
                            </tr>
                            <tr class="' . $display_arr['tr_3'] . '" id="tr_3">
                                <td class="config">
                                    MyVideo-ID:<br>
                                    <span class="small">http://myvideo.de/watch/<b>MyVideo-ID</b>/</span>
                                </td>
                                <td class="config" valign="top">
                                    <input class="text" size="25" maxlength="20" name="video_myvideo" value="' . $_POST['video_myvideo'] . '">
                                </td>
                            </tr>
                            <tr class="' . $display_arr['tr_-1'] . '" id="tr_-1">
                                <td class="config">
                                    HTML-Code:<br>
                                    <span class="small">HTML-Code um das Video einzubinden.<br><br>
                                    <span class="small">Damit das Video in unterschiedlichen Gr&ouml;&szlig;en dargstellt werden kann, bitte alle Breitenangaben durch <b>{width}</b> und alle H&ouml;henangaben durch <b>{height}</b> ersetzen.<br><br>
                                    Angaben innerhalb von &raquo;style&laquo; m&uuml;ssen durch <b>{width_css}</b> bzw. <b>{height_css}</b> ersetzt werden.</span>
                                </td>
                                <td class="config" valign="top">
                                    <textarea class="text" name="video_other" rows="10" cols="50" wrap="virtual">' . $_POST['video_other'] . '</textarea>
                                </td>
                            </tr>
                            <tr>
                                <td class="config">
                                    L&auml;nge: <span class="small">' . $FD->text('admin', 'optional') . '</span><br>
                                    <span class="small">Die Laufzeit des Videos.</span>
                                </td>
                                <td class="config" valign="top">
                                    <input class="text center" size="2" maxlength="2" name="video_h" value="' . $_POST['video_h'] . '"> :
                                    <input class="text center" size="2" maxlength="2" name="video_m" value="' . $_POST['video_m'] . '"> :
                                    <input class="text center" size="2" maxlength="2" name="video_s" value="' . $_POST['video_s'] . '">&nbsp;&nbsp;&nbsp;Stunden : Minuten : Sekunden
                                </td>
                            </tr>
                            <tr>
                                <td class="config">
                                    Beschreibung: <span class="small">' . $FD->text('admin', 'optional') . '</span><br>
                                    <span class="small">Text, der das Video beschreibt.</span>
                                </td>
                                <td class="config" valign="top">
                                    <textarea class="text" name="video_desc" rows="5" cols="50" wrap="virtual">' . $_POST['video_desc'] . '</textarea>
                                </td>
                            </tr>
                            <tr>
                                <td class="config">
                                    Download:<br>
                                    <span class="small">Verkn&uuml;pft das Video mit einem Download.</span>
                                </td>
                                <td class="config">
                                    <select name="dl_id">
                                        <option value="0" ' . getselected(0, $_POST['dl_id']) . '>keine Verkn&uuml;pfung</option>
        ';
    // List DLs
    $index = $FD->db()->conn()->query('
                        SELECT D.dl_id, D.dl_name, C.cat_name
                        FROM ' . $FD->env('DB_PREFIX') . 'dl D, ' . $FD->env('DB_PREFIX') . 'dl_cat AS C
                        WHERE D.cat_id = C.cat_id
                        ORDER BY D.dl_name ASC');
    while ($dl_arr = $index->fetch(PDO::FETCH_ASSOC)) {
        settype($dl_arr['dl_id'], 'integer');
        echo '<option value="' . $dl_arr['dl_id'] . '" ' . getselected($dl_arr['dl_id'], $_POST['dl_id']) . '>' . $dl_arr['dl_name'] . ' (' . $dl_arr['cat_name'] . ')</option>';
    }
    echo '
                                    </select><br>
                                    <span class="small"><b>Hinweis:</b> Funktion noch nicht implementiert!</span>
                                </td>
                            </tr>
                                                        <tr><td class="space">' . getselected($dl_arr['dl_id'], $_POST['dl_id']) . '</td></tr>
                            <tr>
                                <td class="buttontd" colspan="2">
                                    <button class="button_new" type="submit">
                                        ' . $FD->text('admin', 'button_arrow') . ' Video hinzuf&uuml;gen
                                    </button>
                                </td>
                            </tr>
                        </table>
                    </form>
    ';
}
?>
