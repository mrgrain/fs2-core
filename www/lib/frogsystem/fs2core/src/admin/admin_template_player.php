<?php if (!defined('ACP_GO')) die('Unauthorized access!');

$TEMPLATE_GO = 'tpl_player';
$TEMPLATE_FILE = '0_player.tpl';
$TEMPLATE_EDIT = array();

$TEMPLATE_EDIT[] = array(
    'name' => 'PLAYER',
    'title' => $FD->text("template", "player_player_title"),
    'description' => $FD->text("template", "player_player_desc"),
    'rows' => 15,
    'cols' => 66,
    'help' => array(
        array('tag' => 'player', 'text' => $FD->text("template", "player_player_player")),
    )
);

// Init Template-Page
echo templatepage_init($TEMPLATE_EDIT, $TEMPLATE_GO, $TEMPLATE_FILE);
?>
