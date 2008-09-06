<?php
///////////////////////
// Load group Config //
///////////////////////

// Create group-Config-Array
$group_config_arr[cat_pic_x] = 200;
$group_config_arr[cat_pic_y] = 50;
$group_config_arr[cat_pic_size] = 1024;


//////////////////////
// Update Database  //
//////////////////////

// Insert group
if (
		isset ( $_POST['sended'] ) && $_POST['sended'] == "add" &&
		isset ( $_POST['group_action'] ) && $_POST['group_action'] == "add" &&
		$_POST['user_group_name'] && $_POST['user_group_name'] != ""
	)
{
	// Security-Functions
	$_POST['user_group_name'] = savesql ( $_POST['user_group_name'] );
	$_POST['user_group_user'] = $_SESSION['user_id'];
	settype ( $_POST['user_group_user'], "integer" );
	$group_date = time ();

	// MySQL-Update-Query
    $insert_query = mysql_query ("
					INSERT INTO ".$global_config_arr['pref']."user_groups (user_group_name, user_group_date, user_group_user)
					VALUES (
						'".$_POST['user_group_name']."',
						'".$group_date."',
						'".$_POST['user_group_user']."'
					)
	", $db );

    $message = "Gruppe wurde erfolgreich hinzugef�gt";
	$id = mysql_insert_id ( $db );
	
	// Image-Operations
    if ( $_FILES['user_group_pic']['name'] != "" ) {
      $upload = upload_img ( $_FILES['user_group_pic'], "images/groups/", "staff_".$id, $group_config_arr['cat_pic_size']*1024, $group_config_arr['cat_pic_x'], $group_config_arr['cat_pic_y'] );
      $message .= "<br>" . upload_img_notice ( $upload );
    }

    // Display Message
    systext ( $message, $admin_phrases[common][info] );

    // Unset Vars
    unset ( $_POST );

	// Set Vars
	$_POST['group_action'] = "edit";
	$_POST['user_group_id'] = mysql_insert_id ( $db );
}

// Update group
elseif (
		isset ( $_POST['user_group_id'] ) &&
		isset ( $_POST['sended'] ) && $_POST['sended'] == "edit" &&
		isset ( $_POST['group_action'] ) && $_POST['group_action'] == "edit" &&

		$_POST['d'] && $_POST['d'] != "" && $_POST['d'] > 0 &&
		$_POST['m'] && $_POST['m'] != "" && $_POST['m'] > 0 &&
		$_POST['y'] && $_POST['y'] != "" && $_POST['y'] > 0 &&

		$_POST['user_group_name'] && $_POST['user_group_name'] != "" &&
		isset ( $_POST['user_group_user'] )
	)
{
	// Security-Functions
	$_POST['user_group_name'] = savesql ( $_POST['user_group_name'] );
	$_POST['user_group_description'] = savesql ( $_POST['user_group_description'] );
	$_POST['user_group_title'] = savesql ( $_POST['user_group_title'] );
	$_POST['user_group_color'] = savesql ( $_POST['user_group_color'] );
	if ( strlen ( trim ( $_POST['user_group_color'] ) ) == 0 ) {
	    $_POST['user_group_color'] = -1;
	}
	settype ( $_POST['user_group_id'], "integer" );
    settype ( $_POST['user_group_user'], "integer" );
    settype ( $_POST['user_group_highlight'], "integer" );
    $date_arr = getsavedate ( $_POST['d'], $_POST['m'], $_POST['y'] );
	$group_date = mktime ( 0, 0, 0, $date_arr['m'], $date_arr['d'], $date_arr['y'] );

	// MySQL-Update-Query
    mysql_query ("
					UPDATE ".$global_config_arr['pref']."user_groups
                 	SET
					 	user_group_name = '".$_POST['user_group_name']."',
                     	user_group_description = '".$_POST['user_group_description']."',
                     	user_group_title = '".$_POST['user_group_title']."',
                     	user_group_color = '".$_POST['user_group_color']."',
                     	user_group_highlight = '".$_POST['user_group_highlight']."',
                     	user_group_date = '".$group_date."',
                     	user_group_user = '".$_POST['user_group_user']."'
                 	WHERE
					 	user_group_id = '".$_POST['user_group_id']."'
	", $db );
    $message = $admin_phrases[common][changes_saved];

	// Image-Operations
    if ( $_POST['group_pic_delete'] == 1 ) {
      if ( image_delete ( "images/groups/", "staff_".$_POST['user_group_id'] ) ) {
        $message .= "<br>" . $admin_phrases[common][image_deleted];
      } else {
		$message .= "<br>" . $admin_phrases[common][image_not_deleted];
      }
    } elseif ( $_FILES['user_group_pic']['name'] != "" ) {
      image_delete ( "images/groups/", "staff_".$_POST['user_group_id'] );
	  $upload = upload_img ( $_FILES['user_group_pic'], "images/groups/", "staff_".$_POST['user_group_id'], $group_config_arr['cat_pic_size']*1024, $group_config_arr['cat_pic_x'], $group_config_arr['cat_pic_y'] );
      $message .= "<br>" . upload_img_notice ( $upload );
    }

    // Display Message
    systext ( $message, $admin_phrases[common][info] );

    // Unset Vars
    unset ( $_POST );
}

// Delete group
elseif (
		isset ( $_POST['user_group_id'] ) && $_POST['user_group_id'] != 0 &&
		isset ( $_POST['sended'] ) && $_POST['sended'] == "delete" &&
		isset ( $_POST['group_action'] ) && $_POST['group_action'] == "delete" &&
		isset ( $_POST['user_group_delete'] )
	)
{
	if ( $_POST['user_group_delete'] == 1 ) {

		// Security-Functions
		settype ( $_POST['user_group_id'], "integer" );

		// Udpate Users
    	mysql_query ("
						UPDATE ".$global_config_arr['pref']."user
						SET user_group = '0'
                 		WHERE user_group = '".$_POST['user_group_id']."'
		", $db );
		
		// Delete Permissions
    	mysql_query ("
						DELETE FROM ".$global_config_arr['pref']."user_permissions
                 		WHERE x_id = '".$_POST['user_group_id']."'
                 		AND perm_for_group = '1'
		", $db );

		// MySQL-Delete-Query
    	mysql_query ("
						DELETE FROM ".$global_config_arr['pref']."user_groups
                 		WHERE user_group_id = '".$_POST['user_group_id']."'
		", $db );
		$message = "Gruppe wurde erfolgreich gel�scht";

		// Delete Category Image
		if ( image_delete ( "images/groups/", "staff_".$_POST['user_group_id'] ) ) {
			$message .= "<br>" . $admin_phrases[common][image_deleted];
		}

	} else {
		$message = "Gruppe wurde nicht gel�scht";
	}

    // Display Message
    systext ( $message, $admin_phrases[common][info] );

    // Unset Vars
    unset ( $_POST );
}



///////////////////////////
// Display Action-Pages  //
///////////////////////////

// No Data to write into DB
if ( isset ( $_POST['user_group_id'] ) && $_POST['group_action'] )
{
	// Edit Category
	if ( $_POST['group_action'] == "edit" )
	{
		// security functions
		settype ( $_POST['user_group_id'], "integer" );

		// Load Data from DB
		$index = mysql_query ( "
								SELECT *
								FROM ".$global_config_arr['pref']."user_groups
								WHERE user_group_id = '".$_POST['user_group_id']."'", $db );
		$group_arr = mysql_fetch_assoc ( $index );

		// Display Error Messages
		if ( isset ( $_POST['sended'] ) ) {
            $group_arr = getfrompost ( $group_arr );
            systext ( $admin_phrases[common][note_notfilled], $admin_phrases[common][error], TRUE );
		}

		// Security-Functions
		$group_arr['user_group_name'] = killhtml ( $group_arr['user_group_name'] );
		$group_arr['user_group_description'] = killhtml ( $group_arr['user_group_description'] );
		$group_arr['user_group_title'] = killhtml ( $group_arr['user_group_title'] );
		$group_arr['user_group_color'] = killhtml ( $group_arr['user_group_color'] );

		//Create Color-Code
		if ( $group_arr['user_group_color'] == -1 ) {
		    $group_arr['user_group_color'] = "";
		}

		// Get User
    	$index = mysql_query ( "SELECT user_name FROM ".$global_config_arr['pref']."user WHERE user_id = '".$group_arr['user_group_user']."'", $db );
    	$group_arr['user_group_user_name'] = killhtml ( mysql_result ( $index, 0, "user_name" ) );

		// Create Date-Arrays
    	if ( !isset ( $_POST['d'] ) ) {
    		$_POST['d'] = date ( "d", $group_arr['user_group_date'] );
    		$_POST['m'] = date ( "m", $group_arr['user_group_date'] );
    		$_POST['y'] = date ( "Y", $group_arr['user_group_date'] );
		}
        $date_arr = getsavedate ( $_POST['d'], $_POST['m'], $_POST['y'] );
    	$nowbutton_array = array( "d", "m", "y" );

		// Display Page
		echo '
					<form action="" method="post" enctype="multipart/form-data">
						<input type="hidden" name="sended" value="edit">
						<input type="hidden" name="group_action" value="'.$_POST['group_action'].'">
						<input type="hidden" name="user_group_id" value="'.$group_arr['user_group_id'].'">
						<input type="hidden" name="go" value="group_admin">
						<table class="configtable" cellpadding="4" cellspacing="0">
						    <tr><td class="line" colspan="2">'."Haupteinstellungen".'</td></tr>
       						<tr>
           						<td class="config">
               						'."Name".':<br>
               						<span class="small">'."Der Name der Kategorie.".'</span>
           						</td>
           						<td>
             						<input class="text" name="user_group_name" size="40" maxlength="50" value="'.$group_arr['user_group_name'].'">
           						</td>
       						</tr>
                            <tr>
                                <td class="config">
                                    '."Erstellungsdatum".':<br>
                                    <span class="small">'."Die Kategorie wurde erstellt am ...".'</span>
                                </td>
                                <td class="config" valign="top">
									<span class="small">
										<input class="text" size="3" maxlength="2" id="d" name="d" value="'.$date_arr['d'].'"> .
                                    	<input class="text" size="3" maxlength="2" id="m" name="m" value="'.$date_arr['m'].'"> .
                                    	<input class="text" size="5" maxlength="4" id="y" name="y" value="'.$date_arr['y'].'">&nbsp;
									</span>
									'.js_nowbutton ( $nowbutton_array, $admin_phrases[common][today] ).'
                                </td>
                            </tr>
                            <tr>
                                <td class="config" valign="top">
                                    '."Ersteller".':<br>
                                    <span class="small">'."Die Kategorie wurde erstellt von ...".'</span>
                                </td>
                                <td class="config" valign="top">
                                    <input class="text" size="30" maxlength="100" readonly id="username" name="user_group_user_name" value="'.$group_arr['user_group_user_name'].'">
                                    <input type="hidden" id="userid" name="user_group_user" value="'.$group_arr['user_group_user'].'">
                                    <input class="button" type="button" onClick=\''.openpopup ( "admin_finduser.php", 400, 400 ).'\' value="'.$admin_phrases[common][change_button].'">
                                </td>
                            </tr>
                            <tr><td class="space"></td></tr>
       						<tr><td class="line" colspan="2">'."Zus�tzliche Einstellungen".'</td></tr>
       						<tr>
           						<td class="config">
             						'."Symbol".': <span class="small">'.$admin_phrases[common][optional].'</span><br><br>
	 	';
		if ( image_exists ( "images/groups/", "staff_".$group_arr['user_group_id'] ) ) {
		    echo '
									<img src="'.image_url ( "images/groups/", "staff_".$group_arr['user_group_id'] ).'" alt="'.$group_arr['user_group_name'].'" border="0">
		    						<table>
										<tr>
											<td>
												<input type="checkbox" name="group_pic_delete" id="gpd" value="1" onClick=\'delalert ("gpd", "'.$admin_phrases[common][js_delete_image].'")\'>
											</td>
											<td>
												<span class="small"><b>'.$admin_phrases[common][delete_image].'</b></span>
											</td>
										</tr>
									</table>
			';
		} else {
		    echo '<span class="small">'.$admin_phrases[common][no_image].'</span><br>';
		}
		echo'                   	<br>
								</td>
								<td class="config">
									<input name="user_group_pic" type="file" size="40" class="text"><br>
		';
		if ( image_exists ( "images/groups/", "staff_".$group_arr['user_group_id'] ) ) {
			echo '<span class="small"><b>'.$admin_phrases[common][replace_img].'</b></span><br>';
		}
		echo'
									<span class="small">
										['.$admin_phrases[common][max].' '.$group_config_arr[cat_pic_x].' '.$admin_phrases[common][resolution_x].' '.$group_config_arr[cat_pic_y].' '.$admin_phrases[common][pixel].'] ['.$admin_phrases[common][max].' '.$group_config_arr[cat_pic_size].' '.$admin_phrases[common][kib].']
									</span>
								</td>
							</tr>
       						<tr>
           						<td class="config">
               						'."Titel".': <span class="small">'.$admin_phrases[common][optional].'</span><br>
               						<span class="small">'."Titel den Mitglieder der Gruppe tragen.".'</span>
           						</td>
           						<td>
             						<input class="text" name="user_group_title" size="40" maxlength="50" value="'.$group_arr['user_group_title'].'">
           						</td>
       						</tr>
       						<tr>
           						<td class="config">
               						'."Einf�rbung".': <span class="small">'.$admin_phrases[common][optional].'</span><br>
               						<span class="small">'."Farbliche Hervorhebung der Namen der Gruppenmitglieder.".'</span>
           						</td>
           						<td class="configbig">
             						<b>#</b> <input class="text" name="user_group_color" size="7" maxlength="6" value="'.$group_arr['user_group_color'].'">
									 <span class="small">'."freilassen um Namen nicht einzuf�rben".'</span><br>
             						<span class="small">'."[Hexadezimal-Farbcode]".'</span>
           						</td>
       						</tr>
       						<tr>
           						<td class="config">
               						'."Hervorhebung".': <span class="small">'.$admin_phrases[common][optional].'</span><br>
               						<span class="small">'."Besondere Hervorhebung der Namen der Gruppenmitglieder.".'</span>
           						</td>
           						<td>
           						    <select name="user_group_highlight" size="1">
                                        <option value="0" '.getselected( $group_arr['user_group_highlight'], 0 ).'>keine Hervorhebung</option>
                                        <option value="1" '.getselected( $group_arr['user_group_highlight'], 1 ).'>fett</option>
                                        <option value="2" '.getselected( $group_arr['user_group_highlight'], 2 ).'>kursiv</option>
                                        <option value="5" '.getselected( $group_arr['user_group_highlight'], 5 ).'>fett & kursiv</option>
							   		</select>
           						</td>
       						</tr>
							<tr>
								<td class="config">
            						'."Beschreibung".': <span class="small">'.$admin_phrases[common][optional].'</span><br>
									<span class="small">'."Ein kurzer Text �ber die Gruppe.".'</span>
								</td>
								<td class="config">
									<textarea class="text" name="user_group_description" rows="5" cols="50" wrap="virtual">'.$group_arr['user_group_description'].'</textarea>
								</td>
							</tr>
							<tr><td class="space"></td></tr>
							<tr>
								<td class="buttontd" colspan="2">
									<button class="button_new" type="submit">
										'.$admin_phrases[common][arrow].' '.$admin_phrases[common][save_long].'
									</button>
								</td>
							</tr>
						</table>
					</form>';
	}

	// Delete group
	elseif ( $_POST['group_action'] == "delete" && $_POST['user_group_id'] != 0 )
	{
		// security functions
		settype ( $_POST['user_group_id'], "integer" );
		
		$index = mysql_query ( "
								SELECT `user_group_id`, `user_group_name`
								FROM ".$global_config_arr['pref']."user_groups
								WHERE user_group_id = '".$_POST['user_group_id']."'
		", $db );
		$group_arr = mysql_fetch_assoc ( $index );

		$group_arr['user_group_name'] = killhtml ( $group_arr['user_group_name'] );
		
		$index_numusers = mysql_query ( "
											SELECT COUNT(`user_id`) AS 'num_users'
											FROM `".$global_config_arr['pref']."user`
											WHERE `user_group` = '".$group_arr['user_group_id']."'
		", $db );
        $group_arr['user_group_num_users'] = mysql_result ( $index_numusers, 0, "num_users" );

		echo '
					<form action="" method="post">
						<input type="hidden" name="sended" value="delete">
						<input type="hidden" name="group_action" value="'.$_POST['group_action'].'">
						<input type="hidden" name="user_group_id" value="'.$group_arr['user_group_id'].'">
						<input type="hidden" name="go" value="group_admin">
						<table class="configtable" cellpadding="4" cellspacing="0">
							<tr><td class="line" colspan="2">'."Gruppe l�schen".'</td></tr>
							<tr>
								<td class="configthin" style="width: 100%;">
									'."Soll diese Gruppe wirklich gel�scht werden:".' <b>'.$group_arr['user_group_name'].'</b>
									(<b>'.$group_arr['user_group_num_users'].'</b> Mitglieder)
								</td>
								<td class="config right top" style="padding: 0px;">
		    						<table width="100%" cellpadding="4" cellspacing="0">
										<tr class="bottom pointer" id="tr_yes"
											onmouseover="'.color_list_entry ( "del_yes", "#EEEEEE", "#64DC6A", "this" ).'"
											onmouseout="'.color_list_entry ( "del_yes", "transparent", "#49C24f", "this" ).'"
											onclick="'.color_click_entry ( "del_yes", "#EEEEEE", "#64DC6A", "this", TRUE ).'"
										>
											<td>
												<input class="pointer" type="radio" name="user_group_delete" id="del_yes" value="1"
                                                    onclick="'.color_click_entry ( "this", "#EEEEEE", "#64DC6A", "tr_yes", TRUE ).'"
												>
											</td>
											<td class="config middle">
												'.$admin_phrases[common][yes].'
											</td>
										</tr>
										<tr class="bottom red pointer" id="tr_no"
											onmouseover="'.color_list_entry ( "del_no", "#EEEEEE", "#DE5B5B", "this" ).'"
											onmouseout="'.color_list_entry ( "del_no", "transparent", "#C24949", "this" ).'"
											onclick="'.color_click_entry ( "del_no", "#EEEEEE", "#DE5B5B", "this", TRUE ).'"
										>
											<td>
												<input class="pointer" type="radio" name="user_group_delete" id="del_no" value="0" checked="checked"
                                                    onclick="'.color_click_entry ( "this", "#EEEEEE", "#DE5B5B", "tr_no", TRUE ).'"
												>
											</td>
											<td class="config middle">
												'.$admin_phrases[common][no].'
											</td>
										</tr>
										'.color_pre_selected ( "del_no", "tr_no" ).'
									</table>
								</td>
							</tr>
							<tr><td class="space"></td></tr>
							<tr>
								<td class="buttontd" colspan="2">
									<button class="button_new" type="submit">
										'.$admin_phrases[common][arrow].' '.$admin_phrases[common][do_button_long].'
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
else
{
    // New group
	// Display Error Messages
	if ( isset ( $_POST['sended'] ) ) {
		$_POST['user_group_name'] = killhtml ( $_POST['user_group_name'] );
		systext ( $admin_phrases[common][note_notfilled], $admin_phrases[common][error], TRUE );
	}

    // Display Add-Form
	echo '
					<form action="" method="post" enctype="multipart/form-data">
						<input type="hidden" name="sended" value="add">
					    <input type="hidden" name="group_action" value="add">
						<input type="hidden" name="go" value="group_admin">
						<table class="configtable" cellpadding="4" cellspacing="0">
						    <tr><td class="line" colspan="2">'."Gruppe hinzuf�gen".'</td></tr>
						    <tr>
								<td class="config">
								    <span class="small">'."Name".':</span>
								</td>
								<td class="config">
								    <span class="small">'."Symbol".': '.$admin_phrases[common][optional].'</span>
								</td>
							</tr>
						    <tr valign="top">
								<td class="config">
									<input class="text" name="user_group_name" size="40" maxlength="100" value="'.$_POST['cat_name'].'">
								</td>
								<td class="config">
									<input name="user_group_pic" type="file" size="30" class="text"><br>
									<span class="small">
										['.$admin_phrases[common][max].' '.$group_config_arr[cat_pic_x].' '.$admin_phrases[common][resolution_x].' '.$group_config_arr[cat_pic_y].' '.$admin_phrases[common][pixel].'] ['.$admin_phrases[common][max].' '.$group_config_arr[cat_pic_size].' '.$admin_phrases[common][kib].']
									</span>
								</td>
							</tr>
							<tr><td class="space"></td></tr>
							<tr>
								<td class="buttontd" colspan="2">
									<button class="button_new" type="submit">
										'.$admin_phrases[common][arrow].' '.$admin_phrases[articles][new_cat_add_button].'
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
						    <tr><td class="line" colspan="4">'."Gruppenverwaltung".'</td></tr>
	';

	// Get groups from DB
	$index = mysql_query ( "
							SELECT `user_group_id`, `user_group_name`, `user_group_user`, `user_group_date`
							FROM `".$global_config_arr['pref']."user_groups`
							WHERE `user_group_id` > 0
							ORDER BY `user_group_name`
	", $db );
	
	// groups found
    if ( mysql_num_rows ( $index ) > 0 ) {
		// display table head
		echo '
							<tr>
							    <td class="config">Gruppenname & Grafik</td>
							    <td class="config">Informationen</td>
							    <td class="config" width="20">Mitglieder</td>
							    <td class="config" width="20"></td>
							</tr>
							<tr><td class="space"></td></tr>
		';
		
		while ( $group_arr = mysql_fetch_assoc ( $index ) )
		{
			$index_username = mysql_query ( "
												SELECT `user_name`
												FROM `".$global_config_arr['pref']."user`
												WHERE `user_id` = '".$group_arr['user_group_user']."'
			", $db );
	        $group_arr['user_group_user_name'] = mysql_result ( $index_username, 0, "user_name" );

			$index_numusers = mysql_query ( "
												SELECT COUNT(`user_id`) AS 'num_users'
												FROM `".$global_config_arr['pref']."user`
												WHERE `user_group` = '".$group_arr['user_group_id']."'
			", $db );
	        $group_arr['user_group_num_users'] = mysql_result ( $index_numusers, 0, "num_users" );

			// Display each Group
			echo '
							<tr class="pointer" id="tr_'.$group_arr['user_group_id'].'"
								onmouseover="'.color_list_entry ( "input_".$group_arr['user_group_id'], "#EEEEEE", "#64DC6A", "this" ).'"
								onmouseout="'.color_list_entry ( "input_".$group_arr['user_group_id'], "transparent", "#49c24f", "this" ).'"
                                onclick="'.color_click_entry ( "input_".$group_arr['user_group_id'], "#EEEEEE", "#64DC6A", "this", TRUE ).'"
							>
			';
			echo '
								<td class="configthin middle">
									<b>'.$group_arr['user_group_name'].'</b>
			';
			if ( image_exists ( "images/groups/", "staff_".$group_arr['user_group_id'] ) ) {
			    echo '<br><img src="'.image_url ( "images/groups/", "staff_".$group_arr['user_group_id'] ).'" alt="'.$group_arr['user_group_name'].'" border="0">';
			}
			echo '
								</td>
								<td class="configthin middle">
									<span class="small">
										'.$admin_phrases[articles][list_cat_created_by].' <b>'.$group_arr['user_group_user_name'].'</b> '.$admin_phrases[articles][list_cat_created_on].' <b>'.date ( $global_config_arr['date'], $group_arr['user_group_date'] ).'</b>
									</span>
								</td>
								<td class="configthin center middle">'.$group_arr['user_group_num_users'].'</td>
								<td class="configthin middle" style="text-align: center; vertical-align: middle;">
                                    <input class="pointer" type="radio" name="user_group_id" id="input_'.$group_arr['user_group_id'].'" value="'.$group_arr['user_group_id'].'"
										onclick="'.color_click_entry ( "this", "#EEEEEE", "#64DC6A", "tr_".$group_arr['user_group_id'], TRUE ).'"
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
										<option value="edit">'.$admin_phrases[common][selection_edit].'</option>
										<option value="delete">'.$admin_phrases[common][selection_del].'</option>
									</select>
								</td>
							</tr>
							<tr><td class="space"></td></tr>
							<tr>
								<td class="buttontd" colspan="4">
									<button class="button_new" type="submit">
										'.$admin_phrases[common][arrow].' '.$admin_phrases[common][do_button_long].'
									</button>
								</td>
							</tr>
		';
    
    } else {
		echo'
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
						<input type="hidden" name="user_group_id" value="0">
						<input type="hidden" name="group_action" value="edit">
						    <tr><td class="space"><br></td></tr>
						    <tr><td class="line" colspan="4">'."Verwaltung der Administrator-Gruppe".'</td></tr>
	';

	// Get groups from DB
	$index = mysql_query ( "
							SELECT `user_group_id`, `user_group_name`, `user_group_user`, `user_group_date`
							FROM `".$global_config_arr['pref']."user_groups`
							WHERE `user_group_id` = 0
							LIMIT 0,1
	", $db );

	// get group data
	$group_arr = mysql_fetch_assoc ( $index );
	
	$index_username = mysql_query ( "
										SELECT `user_name`
										FROM `".$global_config_arr['pref']."user`
										WHERE `user_id` = '".$group_arr['user_group_user']."'
	", $db );
    $group_arr['user_group_user_name'] = mysql_result ( $index_username, 0, "user_name" );

	$index_numusers = mysql_query ( "
										SELECT COUNT(`user_id`) AS 'num_users'
										FROM `".$global_config_arr['pref']."user`
										WHERE `user_group` = '".$group_arr['user_group_id']."'
	", $db );
    $group_arr['user_group_num_users'] = mysql_result ( $index_numusers, 0, "num_users" );

	// Display the Group
	echo '
							<tr>
								<td class="configthin middle">
									<b>'.$group_arr['user_group_name'].'</b>
	';
	if ( image_exists ( "images/groups/", "staff_".$group_arr['user_group_id'] ) ) {
	    echo '<br><img src="'.image_url ( "images/groups/", "staff_".$group_arr['user_group_id'] ).'" alt="'.$group_arr['user_group_name'].'" border="0">';
	}
	echo '
								</td>
								<td class="configthin middle">
									<span class="small">
										'.$admin_phrases[articles][list_cat_created_by].' <b>'.$group_arr['user_group_user_name'].'</b> '.$admin_phrases[articles][list_cat_created_on].' <b>'.date ( $global_config_arr['date'], $group_arr['user_group_date'] ).'</b>
									</span>
								</td>
								<td class="configthin right middle" colspan="2"><b>'.$group_arr['user_group_num_users'].'</b> Mitglieder</td>
							</tr>
							<tr><td class="space"></td></tr>
							<tr>
								<td class="buttontd" colspan="4">
									<button class="button_new" type="submit">
										'.$admin_phrases[common][arrow].' '."Administratorgruppe bearbeiten".'
									</button>
								</td>
							</tr>
						</table>
					</form>
	';
}
?>