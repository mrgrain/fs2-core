<?php if (!defined('ACP_GO')) die('Unauthorized access!');


////////////////////////
//// Article Update ////
////////////////////////

if (isset($_POST['title']) && isset($_POST['url']) && isset($_POST['preis']) && $_POST['sended'] == 'edit'
    && !empty($_POST['title']) && !empty($_POST['url']) && !empty($_POST['preis'])
) {
    settype($_POST['artikelid'], 'integer');
    if (isset($_POST['delartikel'])) {
        $FD->db()->conn()->exec('DELETE FROM ' . $FD->env('DB_PREFIX') . "shop WHERE artikel_id = $_POST[artikelid]");
        image_delete('/shop', $_POST['artikelid']);
        image_delete('/shop', $_POST['artikelid']);
        systext('Artikel wurde gel&ouml;scht');
    } else {
        $_POST['hot'] = isset($_POST['hot']) ? 1 : 0;

        $messages = array();

        if (!empty($_FILES['artikelimg']['name'])) {
            $upload = upload_img($_FILES['artikelimg'], '/shop', $_POST['artikelid'], 2 * 1024 * 1024, 400, 600);
            $messages[] = upload_img_notice($upload);
            $thumb = create_thumb_from(image_url('/shop', $_POST['artikelid'], FALSE, TRUE), 100, 100);
            $messages[] = create_thumb_notice($thumb);
        }
        $stmt = $FD->db()->conn()->prepare(
            'UPDATE ' . $FD->env('DB_PREFIX') . "shop
                   SET artikel_name  = ?,
                       artikel_url   = ?,
                       artikel_text  = ?,
                       artikel_preis = ?,
                       artikel_hot   = '$_POST[hot]'
                   WHERE artikel_id = '$_POST[artikelid]'");
        $stmt->execute(array($_POST['title'],
            $_POST['url'],
            $_POST['text'],
            $_POST['preis']));
        $messages[] = $FD->text('admin', 'changes_saved');

        echo get_systext(implode('<br>', $messages), $FD->text('admin', 'info'), 'green', $FD->text('admin', 'icon_save_ok'));
    }

    unset($_POST);
}

///////////////////////////
////// Edit Article ///////
///////////////////////////

if (isset($_POST['artikelid'])) {
    $_POST['artikelid'] = $_POST['artikelid'][0];
    if (isset($_POST['sended'])) {
        echo get_systext($FD->text('admin', 'changes_not_saved') . '<br>' . $FD->text('admin', 'form_not_filled'), $FD->text('admin', 'error'), 'red', $FD->text('admin', 'icon_save_error'));
    }

    settype($_POST['artikelid'], 'integer');
    $index = $FD->db()->conn()->query('SELECT * FROM ' . $FD->env('DB_PREFIX') . "shop WHERE artikel_id = $_POST[artikelid]");
    $artikel_arr = $index->fetch(PDO::FETCH_ASSOC);
    $dbartikelhot = ($artikel_arr['artikel_hot'] == 1) ? 'checked' : '';

    echo '
                    <form action="" enctype="multipart/form-data" method="post">
                        <input type="hidden" value="shop_edit" name="go">
                        <input type="hidden" value="edit" name="sended">
                        <input type="hidden" value="' . $artikel_arr['artikel_id'] . '" name="artikelid">
                        <table class="content" cellpadding="3" cellspacing="0">
                            <tr><td colspan="2"><h3>Produkt bearbeiten</h3><hr></td></tr>
                            <tr>
                                <td class="config" valign="top">
                                    Bild:<br>
                                    <font class="small">Aktuelles Artikelbild</font>
                                </td>
                                <td class="config" valign="top">
                                    ' . get_image_output('/shop', $artikel_arr['artikel_id'] . '_s', "", '<span class="small">[' . $FD->text('admin', 'error') . ': ' . $FD->text('admin', 'image_not_found') . ']</span>', false) . '
                                </td>
                            </tr>
                            <tr>
                                <td class="config" valign="top">
                                    Neues Bild:<br>
                                    <font class="small">Nur ausf&uuml;llen, wenn das alte ersetzt werden soll.</font>
                                </td>
                                <td class="config" valign="top">
                                    <input type="file" class="text" name="artikelimg" size="33"><br />
                                    <font class="small">[max. 400 x 600 Pixel] [max. 2 MB]</font>
                                </td>
                            </tr>
                            <tr>
                                <td class="config" valign="top">
                                    Artikelname:<br>
                                    <font class="small">Name des Artikel. Kommt auch in den Hotlink</font>
                                </td>
                                <td class="config" valign="top">
                                    <input class="text" name="title" size="51" value="' . killhtml($artikel_arr['artikel_name']) . '" maxlength="100">
                                </td>
                            </tr>
                            <tr>
                                <td class="config" valign="top">
                                    URL:<br>
                                    <font class="small">Link zum Produkt</font>
                                </td>
                                <td class="config" valign="top">
                                    <input class="text" name="url" size="51" value="' . killhtml($artikel_arr['artikel_url']) . '" maxlength="255">
                                </td>
                            </tr>
                            <tr>
                                <td class="config" valign="top">
                                    Artikelbeschreibung:<br>
                                    <font class="small">Kurze Artikelbeschreibung (optional)</font>
                                </td>
                                <td class="config" valign="top">
                                    ' . create_editor('text', killhtml($artikel_arr['artikel_text']), 330, 130) . '
                                </td>
                            </tr>
                            <tr>
                                <td class="config" valign="top">
                                    Preis:<br>
                                    <font class="small">Preis</font>
                                </td>
                                <td class="config" valign="top">
                                    <input class="text" name="preis" size="10" value="' . killhtml($artikel_arr['artikel_preis']) . '" maxlength="10">
                                </td>
                            </tr>
                            <tr>
                                <td class="config" valign="top">
                                    Hotlink:<br>
                                    <font class="small">Hotlinks erscheinen rechts im Men&uuml;</font>
                                </td>
                                <td class="config" valign="top">
                                    <input type="checkbox" name="hot" value="1" ' . $dbartikelhot . '>
                                </td>
                            </tr>
                            <tr>
                                <td class="config">
                                    Artikel l&ouml;schen:
                                </td>
                                <td class="config">
                                    <input onClick=\'delalert ("delartikel", "Soll der Shop-Artikel wirklich gel�scht werden?")\' type="checkbox" name="delartikel" id="delartikel" value="1">
                                </td>
                            </tr>
                            <tr>
                                <td align="center" colspan="2">
                                    <input class="button" type="submit" value="Absenden">
                                </td>
                            </tr>
                        </table>
                    </form>
        ';
}

/////////////////////////////
////// Select Article ///////
/////////////////////////////

else {
    echo '
                    <form action="" method="post">
                        <input type="hidden" value="shop_edit" name="go">
                        <table class="content select_list" cellpadding="3" cellspacing="0" >
                            <tr><td colspan="4"><h3>Produkt ausw&auml;hlen</h3><hr></td></tr>
                            <tr>
                                <td class="config" width="20%">
                                    Bild
                                </td>
                                <td class="config" width="40%">
                                    Artikelname
                                </td>
                                <td class="config" width="20%">
                                    Preis
                                </td>
                                <td class="config" width="20%">
                                    bearbeiten
                                </td>
                            </tr>
    ';
    $index = $FD->db()->conn()->query('SELECT artikel_id, artikel_name, artikel_preis
                          FROM ' . $FD->env('DB_PREFIX') . 'shop
                          ORDER BY artikel_name DESC');
    while ($artikel_arr = $index->fetch(PDO::FETCH_ASSOC)) {
        echo '
                            <tr class="select_entry thin">
                                <td class="config">
                                    <img src="' . image_url('/shop', $artikel_arr['artikel_id'] . '_s') . '" alt="' . killhtml($artikel_arr['artikel_name']) . '">
                                </td>
                                <td class="configthin">
                                    ' . $artikel_arr['artikel_name'] . '
                                </td>
                                <td class="configthin">
                                    ' . $artikel_arr['artikel_preis'] . '
                                </td>
                                <td class="config">
                                    <input class="select_box" type="checkbox" name="artikelid[]" value="' . $artikel_arr['artikel_id'] . '">
                                </td>
                            </tr>
        ';
    }
    echo '
                            <tr style="display:none">
                                <td colspan="4">
                                    <select class="select_type" name="shop_action" size="1">
                                        <option class="select_one" value="edit">' . $FD->text('admin', 'selection_edit') . '</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="4" align="center">
                                    <input class="button" type="submit" value="editieren">
                                </td>
                            </tr>
                        </table>
                    </form>
    ';
}
?>
