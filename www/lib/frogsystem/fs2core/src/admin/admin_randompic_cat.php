<?php

////////////////////////
//// Save Selection ////
////////////////////////

if (isset($_POST['sended'])) {
    while (list($key, $val) = each($_POST['randompic_cat'])) {
        $key = intval($key); //better be safe than sorry
        $val = intval($val);
        $FD->db()->conn()->exec('UPDATE ' . $FD->env('DB_PREFIX') . "screen_cat
                     SET randompic = '$val'
                     WHERE cat_id = '$key'");
    }
    systext('Einstellungen wurden gespeichert!');
}

///////////////////
/// Input form ////
///////////////////
echo '
                    <form action="" method="post">
                        <input type="hidden" value="randompic_cat" name="go">
                        <input type="hidden" value="1" name="sended">
                        <table class="content" cellpadding="0" cellspacing="0">
                            <tr><td colspan="5"><h3>Kategorien ausw&auml;hlen</h3><hr></td></tr>
                            <tr>
                                <td class="config" width="30%">
                                    Name
                                </td>
                                <td class="config" width="10%">
                                    Pics
                                </td>
                                <td class="config" width="20%">
                                    Sichtbarkeit
                                </td>
                                <td class="config" width="20%">
                                    erstellt am
                                </td>
                                <td class="config" width="20%">
                                    Zufallsbild-Kategorie
                                </td>
                            </tr>
    ';
$index = $FD->db()->conn()->query('SELECT * FROM ' . $FD->env('DB_PREFIX') . 'screen_cat WHERE cat_type != 2 ORDER BY cat_name ASC');
while ($cat_arr = $index->fetch(PDO::FETCH_ASSOC)) {
    $cat_arr['cat_date'] = date('d.m.Y', $cat_arr['cat_date']);
    $screen_index = $FD->db()->conn()->query('SELECT COUNT(cat_id) FROM ' . $FD->env('DB_PREFIX') . "screen where cat_id = $cat_arr[cat_id]");
    $screen_rows = $screen_index->fetchColumn();
    echo '
                            <input type="hidden" name="randompic_cat[' . $cat_arr['cat_id'] . ']" value="0">
                            <tr>
                                <td class="thin">
                                    ' . $cat_arr['cat_name'] . '
                                </td>
                                <td class="thin">
                                    ' . $screen_rows . '
                                </td>
                                <td class="thin">';
    switch ($cat_arr['cat_visibility']) {
        case 0:
            echo 'Nicht aufrufbar';
            break;
        case 1:
            echo 'Sichtbar';
            break;
        case 2:
            echo 'Nicht in Auswahl';
            break;
    }
    echo '
                                </td>
                                <td class="thin">
                                    ' . $cat_arr['cat_date'] . '
                                </td>
                                <td class="thin">
                                    <input type="checkbox" name="randompic_cat[' . $cat_arr['cat_id'] . ']" value="1"';
    if ($cat_arr['randompic'] == 1)
        echo ' checked=checked';
    echo '
                                    >
                                </td>
                            </tr>

        ';
}
echo '                   <tr>
                                <td colspan="5" align="center">
                                    <input class="button" type="submit" value="Auswahl speichern">
                                </td>
                            </tr>
                        </table>
                    </form>';
?>
