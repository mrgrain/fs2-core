<?php
//////////////////////
//// Do Index Ops ////
//////////////////////
if ( isset ( $_POST['do'] ) && ( in_array ( $_POST['do'], array ( "update", "new", "delete", "delete_with_word" ) ) ) ) {

    // Include searchfunctions.php
    require ( FS2_ROOT_PATH . "includes/searchfunctions.php" );

    // Update
    if ( $_POST['do'] == "update" ) {
        update_search_index ( "news" );
        update_search_index ( "articles" );
        update_search_index ( "dl" );
        // Display Message
        systext ( $TEXT["admin"]->get("search_index_updated"),
            $TEXT["admin"]->get("info"), FALSE, $TEXT["admin"]->get("icon_save_ok") );

    // New
    } elseif ( $_POST['do'] == "new" ) {
        new_search_index ( "news" );
        new_search_index ( "articles" );
        new_search_index ( "dl" );
        // Display Message
        systext ( $TEXT["admin"]->get("search_index_renewed"),
            $TEXT["admin"]->get("info"), FALSE, $TEXT["admin"]->get("icon_save_add") );

    // Delete
    } elseif ( $_POST['do'] == "delete" ) {
        delete_search_index ( "news" );
        delete_search_index ( "articles" );
        delete_search_index ( "dl" );
        // Display Message
        systext ( $TEXT["admin"]->get("search_index_deleted"),
            $TEXT["admin"]->get("info"), FALSE, $TEXT["admin"]->get("icon_trash_ok") );
    
    // Delete with word List
    } elseif ( $_POST['do'] == "delete_with_word" ) {
        delete_search_index ( "news" );
        delete_search_index ( "articles" );
        delete_search_index ( "dl" );
        delete_word_list();
        // Display Message
        systext ( $TEXT["admin"]->get("search_index_deleted_with_word_list"),
            $TEXT["admin"]->get("info"), FALSE, $TEXT["admin"]->get("icon_trash_ok") );
    }
}

/////////////////////
//// Config Form ////
/////////////////////

if ( TRUE )
{
    // Get some Data
    $words = mysql_query ( "SELECT COUNT(`search_word_id`) AS 'words' FROM `".$global_config_arr['pref']."search_words`", $db );
    $docs = mysql_query ( "SELECT COUNT(`search_time_id`) AS 'docs' FROM `".$global_config_arr['pref']."search_time`", $db );

    // Display Form
    echo '
                        <table class="configtable" cellpadding="4" cellspacing="0">
                            <tr><td class="line" colspan="2">'.$TEXT['admin']->get("search_index_title").'</td></tr>
                            <tr>
                                <td class="config right_space">
                                    '.$TEXT['admin']->get("search_index_update_title").':<br>
                                    <span class="small">'.$TEXT['admin']->get("search_index_update_desc").'</span>
                                </td>
                                <td class="config center">
                                    <form action="" method="post">
                                        <input type="hidden" name="go" value="search_index">
                                        <input type="hidden" name="do" value="update">
                                        <input class="button" type="submit" value="'.$TEXT['admin']->get("search_index_update_button").'">
                                    </form>
                                </td>
                            </tr>
                            <tr>
                                <td class="config right_space">
                                    '.$TEXT['admin']->get("search_index_new_title").':<br>
                                    <span class="small">'.$TEXT['admin']->get("search_index_new_desc").'</span>
                                </td>
                                <td class="config center">
                                    <form action="" method="post">
                                        <input type="hidden" name="go" value="search_index">
                                        <input type="hidden" name="do" value="new">
                                        <input class="button" type="submit" value="'.$TEXT['admin']->get("search_index_new_button").'"><br>
                                        <span class="small">'.$TEXT['admin']->get("search_index_new_note").'</span>
                                    </form>
                                </td>
                            </tr>
                            <tr>
                                <td class="config right_space">
                                    '.$TEXT['admin']->get("search_index_delete_title").':<br>
                                    <span class="small">'.$TEXT['admin']->get("search_index_delete_desc").'</span>
                                </td>
                                <td class="config center">
                                    <form action="" method="post">
                                        <input type="hidden" name="go" value="search_index">
                                        <input type="hidden" name="do" value="delete">
                                        <input class="button" type="submit" value="'.$TEXT['admin']->get("search_index_delete_button").'"><br>
                                        <span class="small">'.$TEXT['admin']->get("search_index_delete_note").'</span>
                                    </form>
                                </td>
                            </tr>
                            <tr>
                                <td class="config right_space">
                                    '.$TEXT['admin']->get("search_index_delete_with_word_title").':<br>
                                    <span class="small">'.$TEXT['admin']->get("search_index_delete_with_word_desc").'</span>
                                </td>
                                <td class="config center">
                                    <form action="" method="post">
                                        <input type="hidden" name="go" value="search_index">
                                        <input type="hidden" name="do" value="delete_with_word">
                                        <input class="button" type="submit" value="'.$TEXT['admin']->get("search_index_delete_with_word_button").'"><br>
                                        <span class="small">'.$TEXT['admin']->get("search_index_delete_with_word_note").'</span>
                                    </form>
                                </td>
                            </tr>
                            <tr><td class="space"></td></tr>
                            <tr><td class="line" colspan="2">'.$TEXT['admin']->get("search_index_info_title").'</td></tr>
                            <tr>
                                <td class="config right_space">
                                    '.$TEXT['admin']->get("search_index_info_words").':
                                </td>
                                <td class="configthin left">
                                    '.mysql_result ( $words, 0, "words" ).'
                                </td>
                            </tr>
                            <tr>
                                <td class="config right_space">
                                    '.$TEXT['admin']->get("search_index_info_docs").':
                                </td>
                                <td class="configthin left">
                                    '.mysql_result ( $docs, 0, "docs" ).'
                                </td>
                            </tr>
                        </table>
    ';
}
?>