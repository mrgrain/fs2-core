<?php
// Set canonical parameters
$FD->setConfig('info', 'canonical', array('newpassword'));

///////////////////////////////////
//// User is already logged in ////
///////////////////////////////////
if (is_loggedin() && isset($_POST['login']) && $_POST['login'] == 1) {
    $template = forward_message($FD->text("frontend", "user_login"), $FD->text("frontend", "user_login_ok"), url($FD->cfg('home_real')));
} elseif (is_loggedin()) {
    $template = sys_message($FD->text("frontend", "user_login"), $FD->text("frontend", "user_login_ok"));
}

//////////////////////////////
//// Request new password ////
//////////////////////////////
elseif (isset($_GET['newpassword']) && (!isset($_POST['login']) || $_POST['login'] != 1)) {

    // Check Mail
    if (isset($_POST['newpassword_mail'])) {

        // check for mail
        $user = $FD->db()->conn()->prepare(
            'SELECT user_id, user_name, user_mail FROM ' . $FD->env('DB_PREFIX') . 'USER
                       WHERE `user_mail` = ? LIMIT 1');
        $user->execute(array($_POST['newpassword_mail']));
        $user = $user->fetch(PDO::FETCH_ASSOC);

        //mail found
        if (!empty($user)) {

            //create Hash
            $hm = new HashMapper();
            $hash = $hm->createForNewPassword($user['user_id']);

            // set message
            $messages = array($FD->text('frontend', 'new_password_request_successful'));

            // send email
            $mail_subject = $FD->text('frontend', 'mail_new_password_request_on') . ' ' . $FD->cfg('virtualhost');
            $content = get_email_template('change_password_ack');
            $content = str_replace('{..user_name..}', $user['user_name'], $content);
            $content = str_replace('{..new_password_url..}', $FD->cfg('virtualhost') . $hash->getURL(), $content);

            // Build Mail and send
            $mail = new Mail(MailManager::getDefaultSender(), $user['user_mail'], $mail_subject, MailManager::parseContent($content), MailManager::getHtmlConfig());
            if ($mail->send()) {
                $messages[] = $FD->text('frontend', 'mail_new_password_request_sended');
            } else {
                $messages[] = $FD->text('frontend', 'mail_new_password_request_not_sended');
            }

            // forward
            $template = sys_message($FD->text('frontend', 'new_password_request'), implode('<br>', $messages));
        } // no user found
        else {
            $FD->text('frontend', 'new_password_user_not_found');
            $template = forward_message($FD->text('frontend', 'new_password_user_not_found'), $FD->text('frontend', 'new_password_user_not_found_text'), url('login', array('newpassword' => '')));
        }


        // Show Form
    } else {
        $template = new template();
        $template->setFile('0_user.tpl');
        $template->load('NEW_PASSWORD');
        $template = $template->display();
    }
}

////////////////////////////
//// Display Login Form ////
////////////////////////////
else {
    // Error Messages
    switch ($FD->cfg('login_state')) {
        case 2: // Wrong Password
            $error_message = $FD->text("frontend", "user_login_error");
            break;
        case 1: // Wrong Username
            $error_message = $FD->text("frontend", "user_login_error");
            break;
    }

    if ($FD->cfg('login_state') == 1 || $FD->cfg('login_state') == 2) {
        $template = forward_message($FD->text("frontend", "user_login_error_title"), $error_message, url('login'));
    } else {
        $template = new template();
        $template->setFile('0_user.tpl');
        $template->load('LOGIN');
        $template = $template->display();
    }
}
?>
