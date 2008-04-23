<?php

/////////////////////////
//// News hinzuf�gen ////
/////////////////////////

if (
		isset ( $_POST['addnews'] ) &&
		$_POST['title'] && $_POST['title'] != "" &&
		$_POST['text'] && $_POST['text'] != "" &&
		
		$_POST['d'] && $_POST['d'] != "" && $_POST['d'] > 0 &&
		$_POST['m'] && $_POST['m'] != "" && $_POST['m'] > 0 &&
		$_POST['y'] && $_POST['y'] != "" && $_POST['y'] > 0 &&
		$_POST['h'] && $_POST['h'] != "" && $_POST['h'] >= 0 &&
		$_POST['i'] && $_POST['i'] != "" && $_POST['i'] >= 0 &&
		
		isset ( $_POST['catid'] ) &&
		isset ( $_POST['posterid'] )
	)
{
	$_POST['text'] = savesql ( $_POST['text'] );
    $_POST['title'] = savesql ( $_POST['title'] );
    
    settype ( $_POST['catid'], "integer" );
    settype ( $_POST['posterid'], "integer" );

    $date_arr = getsavedate ( $_POST['d'], $_POST['m'], $_POST['Y'], $_POST['H'], $_POST['i'] );
	$newsdate = mktime ( $date_arr['h'], $date_arr['i'], 0, $date_arr['m'], $date_arr['d'], $date_arr['y'] );


	// MySQL-Insert-Query
    mysql_query ("
					INSERT INTO ".$global_config_arr['pref']."news (cat_id, user_id, news_date, news_title, news_text)
					VALUES (
						'".$_POST['catid']."',
						'".$_POST['posterid']."',
						'".$newsdate."',
						'".$_POST['title']."',
						'".$_POST['text']."'
					)
	", $db );

    // Links in die DB eintragen
    $newsid = mysql_insert_id ();
    foreach ( $_POST['linkname'] as $key => $value )
    {
        if ( $_POST['linkname'][$key] != "" && $_POST['linkurl'][$key] != "" )
        {
            $_POST['linkname'][$key] = savesql ( $_POST['linkname'][$key] );
            $_POST['linkurl'][$key] = savesql ( $_POST['linkurl'][$key] );
			switch ( $_POST['linktarget'][$key] )
    		{
        		case 1: settype ( $$_POST['linktarget'][$key], "integer" ); break;
        		default: $_POST['linktarget'][$key] = 0; break;
    		}

            mysql_query("INSERT INTO ".$global_config_arr['pref']."news_links (news_id, link_name, link_url, link_target)
                         VALUES ('".$newsid."',
                                 '".$_POST['linkname'][$key]."',
                                 '".$_POST['linkurl'][$key]."',
                                 '".$_POST['linktarget'][$key]."')", $db);
		}
    }

    mysql_query ( "UPDATE ".$global_config_arr['pref']."counter SET news = news + 1", $db );
    systext( $admin_phrases[news][news_added], $admin_phrases[common][info]);
}

/////////////////////////
///// News Formular /////
/////////////////////////

else
{
    if ( isset ( $_POST['sended'] ) &&  isset ( $_POST['addnews'] ) )
    {
        systext($admin_phrases[common][note_notfilled], $admin_phrases[common][error], TRUE);
    }

    // News Konfiguration lesen
    $index = mysql_query ( "SELECT html_code, fs_code FROM ".$global_config_arr['pref']."news_config", $db );
    $config_arr = mysql_fetch_assoc ( $index );
    $config_arr[html_code] = ($config_arr[html_code] == 2 OR $config_arr[html_code] == 4) ? $admin_phrases[common][on] : $admin_phrases[common][off];
    $config_arr[fs_code] = ($config_arr[fs_code] == 2 OR $config_arr[fs_code] == 4) ? $admin_phrases[common][on] : $admin_phrases[common][off];
    $config_arr[para_handling] = ($config_arr[para_handling] == 2 OR $config_arr[para_handling] == 4) ? $admin_phrases[common][on] : $admin_phrases[common][off];
    
	// User ID ermittlen
	if ( !isset ( $_POST['posterid'] ) )
    {
        $_POST['posterid'] = $_SESSION['user_id'];
    }

	// Security-Functions
	$_POST['text'] = killhtml ( $_POST['text'] );
    $_POST['title'] = killhtml ( $_POST['title'] );
	settype ( $_POST['catid'], "integer" );
    settype ( $_POST['posterid'], "integer" );
	
    // Get User
    $index = mysql_query ( "SELECT user_name, user_id FROM ".$global_config_arr['pref']."user WHERE user_id = '".$_POST['posterid']."'", $db );
    $_POST['poster'] = killhtml ( mysql_result ( $index, 0, "user_name" ) );

	// Create Date-Arrays
    if ( !isset ( $_POST['d'] ) )
    {
    	$_POST['d'] = date ( "d" );
    	$_POST['m'] = date ( "m" );
    	$_POST['y'] = date ( "Y" );
    	$_POST['h'] = date ( "H" );
    	$_POST['i'] = date ( "i" );
	}
	$date_arr = getsavedate ( $_POST['d'], $_POST['m'], $_POST['Y'], $_POST['H'], $_POST['i'] );
	$nowbutton_array = array( "d", "m", "y", "h", "i" );

    // Display Page
    echo'
					<form action="" method="post">
						<input type="hidden" value="newsadd" name="go">
                        <input type="hidden" name="sended" value="1">
                        <input type="hidden" value="'.session_id().'" name="PHPSESSID">
                        <table class="configtable" cellpadding="4" cellspacing="0">
							<tr><td class="line" colspan="2">'.$admin_phrases[news][news_information_title].'</td></tr>
                            <tr>
                                <td class="config">
                                    '.$admin_phrases[news][news_cat].':<br>
                                    <span class="small">'.$admin_phrases[news][news_cat_desc].'</span>
                                </td>
                                <td class="config">
                                    <select name="catid">
	';
    									// Kategorien auflisten
    									$index = mysql_query ( "SELECT * FROM ".$global_config_arr['pref']."news_cat", $db );
    									while ( $cat_arr = mysql_fetch_assoc ( $index ) )
    									{
											echo '<option value="'.$cat_arr['cat_id'].'" '.getselected($cat_arr['cat_id'], $_POST['catid']).'>'.$cat_arr['cat_name'].'</option>';
    									}
	echo'
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td class="config">
                                    '.$admin_phrases[news][news_date].':<br>
                                    <span class="small">'.$admin_phrases[news][news_date_desc].'</span>
                                </td>
                                <td class="config" valign="top">
									<span class="small">
										<input class="text" size="3" maxlength="2" id="d" name="d" value="'.$date_arr['d'].'"> .
                                    	<input class="text" size="3" maxlength="2" id="m" name="m" value="'.$date_arr['m'].'"> .
                                    	<input class="text" size="5" maxlength="4" id="y" name="y" value="'.$date_arr['y'].'"> '.$admin_phrases[common][at].'
                                    	<input class="text" size="3" maxlength="2" id="h" name="h" value="'.$date_arr['h'].'"> :
                                    	<input class="text" size="3" maxlength="2" id="i" name="i" value="'.$date_arr['i'].'"> '.$admin_phrases[common][time_appendix].'&nbsp;
									</span>
									'.js_nowbutton ( $nowbutton_array, $admin_phrases[common][now_button] ).'
                                </td>
                            </tr>
                            <tr>
                                <td class="config" valign="top">
                                    '.$admin_phrases[news][news_poster].':<br>
                                    <span class="small">'.$admin_phrases[news][news_poster_desc].'</span>
                                </td>
                                <td class="config" valign="top">
                                    <input class="text" size="30" maxlength="100" readonly="readonly" id="username" name="poster" value="'.$_POST['poster'].'">
                                    <input type="hidden" id="userid" name="posterid" value="'.$_POST['posterid'].'">
                                    <input class="button" type="button" onClick=\''.openpopup ( "admin_finduser.php", 400, 400 ).'\' value="'.$admin_phrases[common][change_button].'">
                                </td>
                            </tr>
                            <tr><td class="space"></td></tr>
							<tr><td class="line" colspan="2">'.$admin_phrases[news][news_new_title].'</td></tr>
                            <tr>
                                <td class="config" colspan="2">
                                    '.$admin_phrases[news][news_title].':
                                </td>
                            </tr>
                            <tr>
                                <td class="config" colspan="2">
                                    <input class="text" size="75" maxlength="255" name="title" value="'.$_POST['title'].'">
                                </td>
                            </tr>
                            <tr>
                                <td class="config" colspan="2">
                                    '.$admin_phrases[news][news_text].':<br>
									<span class="small">'.
									$admin_phrases[common][html].' '.$config_arr[html_code].'. '.
									$admin_phrases[common][fscode].' '.$config_arr[fs_code].'. '.
									$admin_phrases[common][para].' '.$config_arr[para_handling].'.</span>
                                </td>
                            </tr>
                            <tr>
                                <td class="config" colspan="2">
                                    '.create_editor ( "text", $_POST['text'], "100%", "250px", "", FALSE).'
                                </td>
                            </tr>
                            <tr>
                                <td class="config" colspan="2">
                                    <table cellpadding="0" cellspacing="0" width="100%">
    ';

	//Zu l�schende Links l�schen
	if ( isset ( $_POST['sended'] ) &&  isset ( $_POST['dolinkbutton'] ) && $_POST['do_links'] == "del" && count ( $_POST['dolink'] ) > 0 )
	{
		foreach ( $_POST['dolink'] as $key => $value )
    	{
			if ( $value == 1 )
			{
				$_POST['linkname'][$key] = "";
    	    	$_POST['linkurl'][$key] = "";
    	    	$_POST['linktarget'][$key] = "";
			}
    	}
	}
	
	//Links nach oben verschieben
	if ( isset ( $_POST['sended'] ) &&  isset ( $_POST['dolinkbutton'] ) && $_POST['do_links'] == "up" && count ( $_POST['dolink'] ) > 0 )
	{
		foreach ( $_POST['dolink'] as $key => $value )
    	{
			if ( $value == 1 && $key != 0 )
			{
				$up_name = $_POST['linkname'][$key];
    	    	$up_url = $_POST['linkurl'][$key];
    	    	$up_target = $_POST['linktarget'][$key];
    	    	$_POST['linkname'][$key] = $_POST['linkname'][$key-1];
    	    	$_POST['linkurl'][$key] = $_POST['linkurl'][$key-1];
    	    	$_POST['linktarget'][$key] = $_POST['linktarget'][$key-1];
    	    	$_POST['linkname'][$key-1] = $up_name;
    	    	$_POST['linkurl'][$key-1] = $up_url;
    	    	$_POST['linktarget'][$key-1] = $up_target;
			}
    	}
	}
	
	//Links nach unten verschieben
	if ( isset ( $_POST['sended'] ) &&  isset ( $_POST['dolinkbutton'] ) && $_POST['do_links'] == "down" && count ( $_POST['dolink'] ) > 0 )
	{
		foreach ( $_POST['dolink'] as $key => $value )
    	{
			if ( $value == 1 && $key != count ( $_POST['linkname'] ) - 1 )
			{
				$down_name = $_POST['linkname'][$key];
    	    	$down_url = $_POST['linkurl'][$key];
    	    	$down_target = $_POST['linktarget'][$key];
    	    	$_POST['linkname'][$key] = $_POST['linkname'][$key+1];
    	    	$_POST['linkurl'][$key] = $_POST['linkurl'][$key+1];
    	    	$_POST['linktarget'][$key] = $_POST['linktarget'][$key+1];
    	    	$_POST['linkname'][$key+1] = $down_name;
    	    	$_POST['linkurl'][$key+1] = $down_url;
    	    	$_POST['linktarget'][$key+1] = $down_target;
			}
    	}
	}
	
	//Zu bearbeitende Links l�schen & Daten sichern
	unset ( $edit_name );
	unset ( $edit_url );
	unset ( $edit_target );
	
	if ( isset ( $_POST['sended'] ) &&  isset ( $_POST['dolinkbutton'] ) && $_POST['do_links'] == "edit" && count ( $_POST['dolink'] ) > 0 )
	{
		foreach ( $_POST['dolink'] as $key => $value )
    	{
			if ( $value == 1 )
			{
				$edit_name = $_POST['linkname'][$key];
    	    	$edit_url = $_POST['linkurl'][$key];
    	    	$edit_target = $_POST['linktarget'][$key];
				$_POST['linkname'][$key] = "";
    	    	$_POST['linkurl'][$key] = "";
    	    	$_POST['linktarget'][$key] = "";
			}
    	}
	}

	// Erstellte Linkfelder ausgeben
	if ( !isset ($_POST['linkname']) )
 	{
        $_POST['linkname'][0] = "";
	}
	$linkid = 0;
	
    foreach ( $_POST['linkname'] as $key => $value )
    {
        if ( $_POST['linkname'][$key] != "" && $_POST['linkurl'][$key] != "" )
        {
			$counter = $linkid + 1;

			$link_name = killhtml ( $_POST['linkname'][$key] );

			$link_maxlenght = 60;
            $_POST['linkurl'][$key] = killhtml ( $_POST['linkurl'][$key] );
			$link_fullurl = $_POST['linkurl'][$key];
			if ( strlen ( $_POST['linkurl'][$key] ) > $link_maxlenght )
        	{
            	$_POST['linkurl'][$key] = substr ( $link_fullurl, 0, $link_maxlenght ) . "...";
        	}

			switch ( $_POST['linktarget'][$key] )
    		{
        		case 1: $link_target = $admin_phrases[news][news_link_blank]; break;
        		default:
					$_POST['linktarget'][$key] = 0;
					$link_target = $admin_phrases[news][news_link_self];
					break;
    		}

            echo'
        								<tr style="cursor:pointer;"
	onmouseover=\'
		colorOver (document.getElementById("input_'.$linkid.'"), "#EEEEEE", "#64DC6A", this);\'
	onmouseout=\'
		colorOut (document.getElementById("input_'.$linkid.'"), "transparent", "#49c24f", this);\'
	onClick=\'
		createClick (document.getElementById("input_'.$linkid.'"));
		resetUnclicked ("transparent", last, lastBox, this);
		colorClick (document.getElementById("input_'.$linkid.'"), "#EEEEEE", "#64DC6A", this);\'
                            			>
											<td class="config" style="padding-left: 7px; padding-right: 7px; padding-bottom: 2px; padding-top: 2px;">
												#'.$counter.'
											</td>
											<td class="config" width="100%" style="padding-right: 5px; padding-bottom: 2px; padding-top: 2px;">
                                     			'.$link_name.' <span class="small">('.$link_target.')</span><br>
                                    			<a href="'.$link_fullurl.'" target="_blank" title="'.$link_fullurl.'">'.$_POST['linkurl'][$key].'</a>
                                    			<input type="hidden" name="linkname['.$linkid.']" value="'.$link_name.'">
                                    			<input type="hidden" name="linkurl['.$linkid.']" value="'.$link_fullurl.'">
                                    			<input type="hidden" name="linktarget['.$linkid.']" value="'.$_POST['linktarget'][$key].'">
											</td>

                                			<td align="center">
                                                <input type="radio" name="dolink['.$linkid.']" id="input_'.$linkid.'" value="1" style="cursor:pointer;" onClick=\'createClick(this);\'>
											</td>
										</tr>
            ';
			$linkid++;
        }
	}

	if ( $linkid > 0 )
	{
		echo'
										<tr valign="top">
											<td style="padding-right: 5px; padding-top: 11px;" align="right" colspan="2">
											    <select name="do_links" size="1">
                                                    <option value="0">'.$admin_phrases[news][news_link_no].'</option>
                                                    <option value="del">'.$admin_phrases[news][news_link_delete].'</option>
                                                    <option value="up">'.$admin_phrases[news][news_link_up].'</option>
                                                    <option value="down">'.$admin_phrases[news][news_link_down].'</option>
													<option value="edit">'.$admin_phrases[news][news_link_edit].'</option>
												</select>
											</td>
											<td style="padding-top: 11px;" align="center">
                                                <input class="button" type="submit" name="dolinkbutton" value="'.$admin_phrases[common][do_button].'">
											</td>
										</tr>
		';
	}

	if ( $edit_url == "" ) {
    	$edit_url = "http://";
	}
    
	echo'
									</table>
                                </td>
                            </tr>
                            <tr><td class="space"></td></tr>
							<tr>
                                <td class="config" colspan="2">
                                    '.$admin_phrases[news][news_link_add].':
                                </td>
                            </tr>
                            <tr>
                                <td class="config" colspan="2">
                                    <table cellpadding="0" cellspacing="0" width="100%">
										<tr>
											<td class="config" style="padding-right: 5px;">
                                                '.$admin_phrases[news][news_link_title].':
											</td>
											<td class="config" style="padding-bottom: 4px;" width="100%">
                                                <input class="text" style="width: 100%;" maxlength="100" name="linkname['.$linkid.']" value="'.$edit_name.'">
											</td>
											<td class="config"style="padding-left: 5px;">
                                                '.$admin_phrases[news][news_link_open].':
											</td>
										</tr>
										<tr>
											<td class="config">
                                                '.$admin_phrases[news][news_link_url].':
											</td>
											<td class="config" style="padding-bottom: 4px;">
                                                <input class="text" style="width: 100%;" maxlength="255" name="linkurl['.$linkid.']" value="'.$edit_url.'">
											</td>
											<td style="padding-left: 5px;" valign="top">
												<select name="linktarget['.$linkid.']" size="1">
                                                    <option value="0" '.getselected(0, $edit_target).'>'.$admin_phrases[news][news_link_self].'</option>
                                                    <option value="1" '.getselected(1, $edit_target).'>'.$admin_phrases[news][news_link_blank].'</option>
												</select>
											</td>
											<td align="right" valign="top" style="padding-left: 10px;">
                                                <input class="button" type="submit" name="addlink" value="'.$admin_phrases[common][add_button].'">
											</td>
										</tr>
									</table>
								</td>
                            </tr>
 	';
            
	echo'
							<tr><td class="space"></td></tr>
                            <tr>
                                <td class="buttontd" colspan="2">
                                    <button class="button_new" type="submit" name="addnews">
                                        '.$admin_phrases[common][arrow].' '.$admin_phrases[news][news_add_button].'
                                    </button>
                                </td>
                            </tr>
                        </table>
                    </form>
    ';
}
?>