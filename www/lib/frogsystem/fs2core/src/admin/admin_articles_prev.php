<?php if (!defined('ACP_GO')) die('Unauthorized access!');


// Reload Page
if (!isset($_POST['article_text']) && !isset($_POST['sended'])) {
    // Reload Page Template
    $template = '
        <script type="text/javascript">
            $().ready(function(){
                loaddata();
                document.getElementById(\'form\').submit();
            });

            function loaddata() {
                document.getElementById(\'article_title\').value = opener.document.getElementById(\'article_title\').value;
                document.getElementById(\'article_user\').value = opener.document.getElementById(\'userid\').value;
                document.getElementById(\'article_user_name\').value = opener.document.getElementById(\'username\').value;

                document.getElementById(\'d\').value = opener.document.getElementById(\'d\').value;
                document.getElementById(\'m\').value = opener.document.getElementById(\'m\').value;
                document.getElementById(\'y\').value = opener.document.getElementById(\'y\').value;

                if ( opener.document.getElementById(\'article_html\').checked == true ) {
                    document.getElementById(\'article_html\').value = 1;
                } else {
                    document.getElementById(\'article_html\').value = 0;
                }
                if ( opener.document.getElementById(\'article_fscode\').checked == true ) {
                    document.getElementById(\'article_fscode\').value = 1;
                } else {
                    document.getElementById(\'article_fscode\').value = 0;
                }
                if ( opener.document.getElementById(\'article_para\').checked == true ) {
                    document.getElementById(\'article_para\').value = 1;
                } else {
                    document.getElementById(\'article_para\').value = 0;
                }

                document.getElementById(\'article_text\').value = opener.document.getElementById(\'article_text\').value;
            }
        </script>

        <form action="?go=article_preview" method="post" id="form">
            <input type="hidden" name="sended" id="sended" value="1">
            <input type="hidden" name="article_title" id="article_title" value="">
            <input type="hidden" name="article_user" id="article_user" value="">
            <input type="hidden" name="article_user_name" id="article_user_name" value="">
            <input type="hidden" name="d" id="d" value="">
            <input type="hidden" name="m" id="m" value="">
            <input type="hidden" name="y" id="y" value="">
            <input type="hidden" name="article_html" id="article_html" value="">
            <input type="hidden" name="article_fscode" id="article_fscode" value="">
            <input type="hidden" name="article_para" id="article_para" value="">
            <input type="hidden" name="article_text" id="article_text" value="">
        </form>

        ' . get_content_container('&nbsp;', $FD->text("page", "preview_note")) . '
    ';

    // "Display" Reload Page
    echo $template;
} // Preview Page
else {
    // goto
    $goto = 'articles_preview';
    $FD->setConfig('env', 'get_go', $goto);
    $FD->setConfig('goto', $goto);
    $FD->setConfig('env', 'goto', $goto);


    // Load Data from $_POST
    $article_arr['article_title'] = $_POST['article_title'];

    // Create Article-Date
    if (
        ($_POST['d'] && $_POST['d'] != '' && $_POST['d'] > 0) &&
        ($_POST['m'] && $_POST['m'] != '' && $_POST['m'] > 0) &&
        ($_POST['y'] && $_POST['y'] != '' && $_POST['y'] > 0) &&
        (isset ($_POST['d']) && isset ($_POST['m']) && isset ($_POST['y']))
    ) {
        settype($_POST['d'], 'integer');
        settype($_POST['m'], 'integer');
        settype($_POST['y'], 'integer');
        $article_arr['article_date'] = mktime(0, 0, 0, $_POST['m'], $_POST['d'], $_POST['y']);
    } else {
        $article_arr['article_date'] = 0;
    }
    // Get Date & Create Date Template
    if ($article_arr['article_date'] != 0) {
        $article_arr['date_formated'] = date_loc($FD->config('date'), $article_arr['article_date']);
        // Create Template
        $date_template = new template();
        $date_template->setFile('0_articles.tpl');
        $date_template->load('DATE');
        $date_template->tag('date', $article_arr['date_formated']);
        $article_arr['date_template'] = $date_template->display();
    } else {
        $article_arr['date_formated'] = '';
        $article_arr['date_template'] = '';
    }

    // Format Article-Text
    settype($_POST['article_html'], 'integer');
    settype($_POST['article_fscode'], 'integer');
    settype($_POST['article_para'], 'integer');
    $parseflags = array(
        'fscode' => $_POST['article_fscode'],
        'nofscodeatall' => false,
        'html' => $_POST['article_html'],
        'nohtmlatall' => false,
        'paragraph' => $_POST['article_para'],
    );
    $article_arr['article_text'] = parse_all_fscodes($_POST['article_text'], $parseflags);

    // Format User
    $article_arr['article_user_name'] = killhtml($_POST['article_user_name']);
    $article_arr['article_user'] = $_POST['article_user'];
    settype($article_arr['article_user'], 'integer');

    // Create User Template
    if ($article_arr['article_user_name'] != '' && $article_arr['article_user'] > 0) {
        $user_arr['user_id'] = $article_arr['article_user'];
        $user_arr['user_name'] = $article_arr['article_user_name'];
        $user_arr['user_url'] = '?go=user&id=' . $user_arr['user_id'];

        // Create Template
        $author_template = new template();
        $author_template->setFile('0_articles.tpl');
        $author_template->load('AUTHOR');

        $author_template->tag('user_id', $user_arr['user_id']);
        $author_template->tag('user_name', $user_arr['user_name']);
        $author_template->tag('user_url', $user_arr['user_url']);

        $article_arr['author_template'] = $author_template->display();
    } else {
        $article_arr['author_template'] = '';
        $user_arr['user_id'] = '';
        $user_arr['user_name'] = '';
        $user_arr['user_url'] = '';
    }

    // Create Template
    $article_arr['template'] = new template();
    $article_arr['template']->setFile('0_articles.tpl');
    $article_arr['template']->load('BODY');

    $article_arr['template']->tag('title', $article_arr['article_title']);
    $article_arr['template']->tag('text', $article_arr['article_text']);
    $article_arr['template']->tag('date_template', $article_arr['date_template']);
    $article_arr['template']->tag('date', $article_arr['date_formated']);
    $article_arr['template']->tag('author_template', $article_arr['author_template']);
    $article_arr['template']->tag('user_id', $user_arr['user_id']);
    $article_arr['template']->tag('user_name', $user_arr['user_name']);
    $article_arr['template']->tag('user_url', $user_arr['user_url']);
    $template_preview = $article_arr['template']->display();

    // Preview Page Template
    $FD->setConfig('dyn_title', 1);
    $FD->setConfig('dyn_title_ext', '{..ext..}');
    $FD->setConfig('info', 'page_title', $FD->text('page', 'preview_title') . ': ' . $article_arr['article_title']);


    // Display Preview Page
    $theTemplate = new template();
    $theTemplate->setFile('0_main.tpl');
    $theTemplate->load('MAIN');
    $theTemplate->tag('content', $template_preview);
    $theTemplate->tag('copyright', get_copyright());

    $template_general = (string)$theTemplate;
    $template_general = $template_general;

    // Get Main Template
    global $APP;
    $APP = load_applets();

    echo tpl_functions_init(get_maintemplate($template_general, '../'));
    $JUST_CONTENT = true; //preview has own html head

}

?>
