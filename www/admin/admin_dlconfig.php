<?php

/////////////////////////////////////
//// Konfiguration aktualisieren ////
/////////////////////////////////////

if ($_POST[screenx] && $_POST[screeny] && $_POST[thumbx] && $_POST[thumby] && $_POST[quickinsert])
{
    settype($_POST[screenx], 'integer');
    settype($_POST[screeny], 'integer');
    settype($_POST[thumbx], 'integer');
    settype($_POST[thumby], 'integer');
    settype($_POST[dl_rights], 'integer');
    
    $update = "UPDATE fs_dl_config
               SET screen_x = '$_POST[screenx]',
                   screen_y = '$_POST[screeny]',
                   thumb_x = '$_POST[thumbx]',
                   thumb_y = '$_POST[thumby]',
                   quickinsert = '$_POST[quickinsert]',
                   dl_rights = '$_POST[dl_rights]'";
    mysql_query($update, $db);
    systext("Die Konfiguration wurde aktualisiert");
}

/////////////////////////////////////
////// Konfiguration Formular ///////
/////////////////////////////////////

else
{
    $index = mysql_query("SELECT * FROM fs_dl_config", $db);
    $config_arr = mysql_fetch_assoc($index);
    echo'
                    <form action="'.$PHP_SELF.'" method="post">
                        <input type="hidden" value="dlconfig" name="go">
                        <input type="hidden" value="'.session_id().'" name="PHPSESSID">
                        <table border="0" cellpadding="4" cellspacing="0" width="600">
                            <tr>
                                <td class="config" valign="top" width="70%">
                                    Max. Bildgr��e:<br>
                                    <font class="small">Stellt die max. Upload Gr��e der Vorschau-Bilder ein</font>
                                </td>
                                <td class="config" valign="top" width="30%">
                                    <input class="text" size="5" name="screenx" value="'.$config_arr[screen_x].'" maxlength="4">
                                    x
                                    <input class="text" size="5" name="screeny" value="'.$config_arr[screen_y].'" maxlength="4">
                                </td>
                            </tr>
                            <tr>
                                <td class="config" valign="top" width="50%">
                                    Thumbnail Gr��e:<br>
                                    <font class="small">Gibt die Gr��e der Thumbnails an</font>
                                </td>
                                <td class="config" valign="top" width="50%">
                                    <input class="text" size="5" name="thumbx" value="'.$config_arr[thumb_x].'" maxlength="3">
                                    x
                                    <input class="text" size="5" name="thumby" value="'.$config_arr[thumb_y].'" maxlength="3">
                                </td>
                            </tr>
                            <tr>
                                <td class="config" valign="top" width="50%">
                                    Quick-Insert Pfad:<br>
                                    <font class="small">Der Datei-Pfad der mit dem Quick-Insert Button eingef�gt wird.</font>
                                </td>
                                <td class="config" valign="top" width="50%">
                                    <input class="text" size="40" name="quickinsert" value="'.stripslashes(killhtml($config_arr[quickinsert])).'" maxlength="255">
                                </td>
                            </tr>
                            <tr>
                                <td class="config" valign="top" width="50%">
                                    Downloads erlauben f�r:<br>
                                    <font class="small">Wer darf die Downloads verwenden?</font>
                                </td>
                                <td class="config" valign="top" width="50%">
                                    <select name="dl_rights">
                                        <option value="2"';
                                        if ($config_arr[dl_rights] == 2)
                                            echo ' selected="selected"';
                                        echo'>alle User</option>
                                        <option value="1"';
                                        if ($config_arr[dl_rights] == 1)
                                            echo ' selected="selected"';
                                        echo'>registrierte User</option>
                                        <option value="0"';
                                        if ($config_arr[dl_rights] == 0)
                                            echo ' selected="selected"';
                                        echo'>niemanden</option>
                                    </select>
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
?>