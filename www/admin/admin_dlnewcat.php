<?php

///////////////////////////////////////
//// Kategorie in die DB eintragen ////
///////////////////////////////////////

if (isset($_POST[catname]))
{
    $_POST[catname] = savesql($_POST[catname]);

    $index = mysql_query("SELECT cat_name FROM fs_dl_cat WHERE cat_name = '$_POST[catname]'", $db);
    $rows = mysql_num_rows($index);

    if ($rows == 0)
    {
        settype($_POST[subcatof], 'integer');
        mysql_query("INSERT INTO fs_dl_cat (subcat_id, cat_name)
                     VALUES ('".$_POST[subcatof]."',
                             '".$_POST[catname]."');", $db);
        systext("Kategorie wurde hinzugef�gt");
    }
    else
    {
        systext("Kategorie existiert bereits");
    }
}

///////////////////////////////////////
///////// Kategorie Formular //////////
///////////////////////////////////////

else
{
    echo'
                    <form action="'.$PHP_SELF.'" method="post">
                        <input type="hidden" value="dlnewcat" name="go">
                        <input type="hidden" value="'.session_id().'" name="PHPSESSID">
                        <table border="0" cellpadding="4" cellspacing="0" width="600">
                            <tr>
                                <td class="config" valign="top">
                                    Name:<br>
                                    <font class="small">Name der neuen Kategorie</font>
                                </td>
                                <td class="config" valign="top">
                                    <input class="text" name="catname" size="33" maxlength="100">
                                </td>
                            </tr>
                            <tr>
                                <td class="config" valign="top">
                                    Subkategorie von:<br>
                                    <font class="small">Macht diese Kategorie zu einer Unterkategorie einer anderen</font>
                                </td>
                                <td class="config" valign="top">
                                    <select name="subcatof">
                                        <option value="0">Keine Subkategorie</option>
                                        <option value="0">--------------------------------------</option>
    ';

    $valid_ids = array();
    get_dl_categories (&$valid_ids, -1);

    foreach ($valid_ids as $cat)
    {
        echo'
                                        <option value="'.$cat[cat_id].'">'.str_repeat("&nbsp;&nbsp;&nbsp;&nbsp;", $cat[ebene]).$cat[cat_name].'</option>
        ';
    }
    echo'
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td align="center" colspan="2">
                                    <br><input class="button" type="submit" value="Hinzuf�gen">
                                </td>
                            </tr>
                        </table>
                    </form>
    ';
}
?>