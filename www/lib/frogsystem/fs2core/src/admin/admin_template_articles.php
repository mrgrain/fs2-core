<?php if (!defined('ACP_GO')) die('Unauthorized access!');

$TEMPLATE_GO = 'tpl_articles';
$TEMPLATE_FILE = '0_articles.tpl';
$TEMPLATE_EDIT = array();

$TEMPLATE_EDIT[] = array(
    'name' => 'AUTHOR',
    'title' => $FD->text("template", "articles_author_title"),
    'description' => $FD->text("template", "articles_author_desc"),
    'rows' => 10,
    'cols' => 66,
    'help' => array(
        array('tag' => 'user_id', 'text' => $FD->text("template", "articles_user_id")),
        array('tag' => 'user_name', 'text' => $FD->text("template", "articles_user_name")),
        array('tag' => 'user_url', 'text' => $FD->text("template", "articles_user_url")),
    )
);

$TEMPLATE_EDIT[] = array(
    'name' => 'DATE',
    'title' => $FD->text("template", "articles_date_title"),
    'description' => $FD->text("template", "articles_date_desc"),
    'rows' => 10,
    'cols' => 66,
    'help' => array(
        array('tag' => 'date', 'text' => $FD->text("template", "articles_date")),
    )
);

$TEMPLATE_EDIT[] = array(
    'name' => 'BODY',
    'title' => $FD->text("template", "articles_body_title"),
    'description' => $FD->text("template", "articles_body_desc"),
    'rows' => 25,
    'cols' => 66,
    'help' => array(
        array('tag' => 'title', 'text' => $FD->text("template", "articles_body_article_title")),
        array('tag' => 'text', 'text' => $FD->text("template", "articles_body_text")),
        array('tag' => 'date_template', 'text' => $FD->text("template", "articles_date_template")),
        array('tag' => 'date', 'text' => $FD->text("template", "articles_date")),
        array('tag' => 'author_template', 'text' => $FD->text("template", "articles_body_author")),
        array('tag' => 'user_id', 'text' => $FD->text("template", "articles_user_id")),
        array('tag' => 'user_name', 'text' => $FD->text("template", "articles_user_name")),
        array('tag' => 'user_url', 'text' => $FD->text("template", "articles_user_url")),
    )
);

// Init Template-Page
echo templatepage_init($TEMPLATE_EDIT, $TEMPLATE_GO, $TEMPLATE_FILE);
?>
