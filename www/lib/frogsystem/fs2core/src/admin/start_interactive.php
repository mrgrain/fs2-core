<?php if (!defined('ACP_GO')) die('Unauthorized access!');

$date = date('U');
$index = $FD->db()->conn()->query('SELECT * FROM ' . $FD->env('DB_PREFIX') . "poll WHERE poll_end > $date AND poll_start < $date ORDER BY poll_start DESC LIMIT 0,1 ");
if ($poll_arr = $index->fetch(PDO::FETCH_ASSOC)) {

    $poll_arr['poll_start'] = date('d.m.Y', $poll_arr['poll_start']);
    $poll_arr['poll_end'] = date('d.m.Y', $poll_arr['poll_end']);

    if ($poll_arr['poll_type'] == 1) {
        $poll_arr['poll_type'] = $FD->text("page", "multiple_selection");
    } else {
        $poll_arr['poll_type'] = $FD->text("page", "single_selection");
    }

    $index = $FD->db()->conn()->query("
                    SELECT SUM(`answer_count`) AS 'num_votes'
                    FROM " . $FD->env('DB_PREFIX') . "poll_answers
                    WHERE `poll_id` = '" . $poll_arr['poll_id'] . "'");
    $row = $index->fetch(PDO::FETCH_ASSOC);
    $all_votes = $row['num_votes'];

    $index = $FD->db()->conn()->query('SELECT * FROM ' . $FD->env('DB_PREFIX') . "poll_answers WHERE poll_id = '" . $poll_arr['poll_id'] . "' ORDER BY answer_id ASC");
    $antworten = '';
    while ($answer_arr = $index->fetch(PDO::FETCH_ASSOC)) {
        if ($all_votes != 0) {
            $answer_arr['percentage'] = round($answer_arr['answer_count'] / $all_votes * 100, 1);
            $answer_arr['bar_width'] = $answer_arr['percentage'] . '%';
        } else {
            $answer_arr['percentage'] = 0;
            $answer_arr['bar_width'] = '1px';
        }
        $template = '
                            <tr>
                                <td class="configthin">{answer}</td>
                                <td class="configthin middle"><b>{percentage}</b><br><b>{votes}</b> ' . sp_string($answer_arr['answer_count'], $FD->text("page", "vote"), $FD->text("page", "votes")) . '</td>
                                <td class="config middle" style="width: 250px;">
                                    <div style="width: 250px;"><div style="width:{bar_width}; height:4px; font-size:1px; background-color:#00FF00;"></div>
                                </td>
                            </tr>
        ';
        $template = str_replace('{answer}', $answer_arr['answer'], $template);
        $template = str_replace('{votes}', $answer_arr['answer_count'], $template);
        $template = str_replace('{percentage}', $answer_arr['percentage'] . '%', $template);
        $template = str_replace('{bar_width}', $answer_arr['bar_width'], $template);

        $antworten .= $template;
    }
    unset($answer_arr);

    $template = '
                            <tr>
                                <td class="config" colspan="3">{question}</td>
                            </tr>
                            {answers}
                            <tr><td class="space"></td></tr>
                            <tr>
                                <td class="configthin">' . $FD->text("page", "number_of_participants") . ':</td>
                                <td class="configthin" colspan="2"><b>{participants}</b></td>
                            </tr>
                            <tr>
                                <td class="configthin">' . $FD->text("page", "number_of_votes") . ':</td>
                                <td class="configthin" colspan="2"><b>{all_votes}</b></td>
                            </tr>
                            <tr>
                                <td class="configthin">' . $FD->text("page", "type_of_poll") . ':</td>
                                <td class="configthin" colspan="2"><b>{typ}</b></td>
                            </tr>
                            <tr>
                                <td class="configthin">' . $FD->text("page", "period_of_poll") . ':</td>
                                <td class="configthin" colspan="2"><b>{start_datum}</b> bis <b>{end_datum}</b></td>
                            </tr>
    ';
    $template = str_replace('{question}', $poll_arr['poll_quest'], $template);
    $template = str_replace('{answers}', $antworten, $template);
    $template = str_replace('{all_votes}', $all_votes, $template);
    $template = str_replace('{typ}', $poll_arr['poll_type'], $template);
    $template = str_replace('{participants}', $poll_arr['poll_participants'], $template);
    $template = str_replace('{start_datum}', $poll_arr['poll_start'], $template);
    $template = str_replace('{end_datum}', $poll_arr['poll_end'], $template);

    unset($poll_arr);

    echo '
                            <table class="configtable" cellpadding="4" cellspacing="0">
                                <tr><td class="line" colspan="3">' . $FD->text("page", "latest_poll") . '</td></tr>
                                ' . $template . '
                                <tr><td class="space"></td></tr>
                                <tr><td class="space"></td></tr>
                        </table>
    ';

    unset ($template);

}

$index = $FD->db()->conn()->query("
                SELECT COUNT(DISTINCT(P.`poll_id`)) AS 'num_polls', SUM(DISTINCT(P.`poll_participants`)) AS 'num_participants', SUM(A.`answer_count`) AS 'num_votes'
                FROM " . $FD->env('DB_PREFIX') . 'poll P, ' . $FD->env('DB_PREFIX') . 'poll_answers A
                WHERE P.`poll_id` = A.`poll_id`');
$row = $index->fetch(PDO::FETCH_ASSOC);
$num_polls = $row['num_polls'];
$num_participants = $row['num_participants'];
settype($num_participants, 'integer');
$num_votes = $row['num_votes'];
settype($num_votes, 'integer');

if ($num_polls > 0) {
    $index = $FD->db()->conn()->query('
                    SELECT `poll_quest`, `poll_participants`
                    FROM ' . $FD->env('DB_PREFIX') . 'poll
                    ORDER BY `poll_participants` DESC
                    LIMIT 0,1');
    $row = $index->fetch(PDO::FETCH_ASSOC);
    $biggest_poll_part = $row['poll_quest'];
    $most_participants = $row['poll_participants'];

    $index = $FD->db()->conn()->query("
                    SELECT P.`poll_quest`, SUM(A.`answer_count`) AS 'num_votes'
                    FROM " . $FD->env('DB_PREFIX') . 'poll P, ' . $FD->env('DB_PREFIX') . 'poll_answers A
                    WHERE P.`poll_id` = A.`poll_id`
                    GROUP BY P.`poll_quest`
                    ORDER BY `num_votes` DESC
                    LIMIT 0,1');
    $row = $index->fetch(PDO::FETCH_ASSOC);
    $biggest_poll_vote = $row['poll_quest'];
    $most_votes = $row['num_votes'];
}


echo '
                        <table class="configtable" cellpadding="4" cellspacing="0">
                            <tr><td class="line" colspan="2">' . $FD->text("admin", "informations_and_statistics") . '</td></tr>
                            <tr>
                                <td class="configthin" width="200">' . $FD->text("page", "number_of_polls") . ':</td>
                                <td class="configthin"><b>' . $num_polls . '</b></td>
                            </tr>
';

echo '
                            <tr>
                                <td class="configthin">' . $FD->text("page", "overall_participants") . ':</td>
                                <td class="configthin"><b>' . $num_participants . '</b></td>
                            </tr>
';

if ($num_polls > 0) {
    echo '
                            <tr>
                                <td class="configthin">' . $FD->text("page", "most_participants") . ':</td>
                                <td class="configthin"><b>' . $biggest_poll_part . '</b> mit <b>' . $most_participants . '</b> ' . $FD->text("page", "participant_s") . '</td>
                            </tr>
    ';
}

echo '
                            <tr>
                                <td class="configthin">' . $FD->text("page", "overall_votes") . ':</td>
                                <td class="configthin"><b>' . $num_votes . '</b></td>
                            </tr>
';

if ($num_polls > 0) {
    echo '
                            <tr>
                                <td class="configthin">' . $FD->text("page", "most_votes") . ':</td>
                                <td class="configthin"><b>' . $biggest_poll_vote . '</b> ' . $FD->text("admin", "with") . ' <b>' . $most_votes . '</b> ' . $FD->text("page", "vote_s") . '</td>
                            </tr>
    ';
}

echo '
                        </table>
';
?>
