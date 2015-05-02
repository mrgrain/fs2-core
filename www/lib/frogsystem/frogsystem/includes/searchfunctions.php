<?php
///////////////////////////////////
//// Maybe Update Search Index ////
///////////////////////////////////
function search_index()
{
    global $FD;

    if ($FD->cfg('cronjobs', 'search_index_update') == 2) {
        update_search_index('news');
        update_search_index('articles');
        update_search_index('dl');
    }
}


function get_default_operators()
{
    global $FD;
    $FD->loadConfig('search');

    $and = explode(',', $FD->cfg('search', 'search_and'));
    $or = explode(',', $FD->cfg('search', 'search_or'));
    $xor = explode(',', $FD->cfg('search', 'search_xor'));
    $not = explode(',', $FD->cfg('search', 'search_not'));
    $wc = explode(',', $FD->cfg('search', 'search_wildcard'));

    $ops = array(
        'and' => trim(array_shift($and)),
        'or' => trim(array_shift($or)),
        'xor' => trim(array_shift($xor)),
        'not' => trim(array_shift($not)),
        'wildcard' => trim(array_shift($wc))
    );

    return $ops;
}


function new_search_index($FOR)
{
    delete_search_index($FOR);
    update_search_index($FOR);
}

function delete_search_index($FOR)
{
    global $FD;

    $FD->db()->conn()->exec('
                    DELETE FROM `' . $FD->env('DB_PREFIX') . "search_index`
                    WHERE `search_index_type` = '" . $FOR . "'");
    $FD->db()->conn()->exec('
                    DELETE FROM `' . $FD->env('DB_PREFIX') . "search_time`
                    WHERE `search_time_type` = '" . $FOR . "'");
}

function delete_search_index_for_one($ID, $TYPE)
{
    global $FD;

    $FD->db()->conn()->exec('
                    DELETE FROM `' . $FD->env('DB_PREFIX') . "search_index`
                    WHERE `search_index_type` = '" . $TYPE . "'
                    AND `search_index_document_id` = '" . $ID . "'");
    $FD->db()->conn()->exec('
                    DELETE FROM `' . $FD->env('DB_PREFIX') . "search_time`
                    WHERE `search_time_type` = '" . $TYPE . "'
                    AND `search_time_document_id` = '" . $ID . "'");
}


function delete_word_list()
{
    global $FD;

    $FD->db()->conn()->exec('
                    TRUNCATE TABLE `' . $FD->env('DB_PREFIX') . 'search_words`');
}

function update_search_index($FOR)
{
    global $FD;

    $data = get_make_search_index($FOR);
    while ($data_arr = $data->fetch(PDO::FETCH_ASSOC)) {
        // Compress Text and filter Stopwords
        $data_arr['search_data'] = delete_stopwords(compress_search_data($data_arr['search_data']));

        // Remove Old Indexes & Update Timestamp
        if ($data_arr['search_time_id'] != null) {
            $FD->db()->conn()->exec('
                            DELETE FROM `' . $FD->env('DB_PREFIX') . "search_index`
                            WHERE `search_index_type` = '" . $data_arr['search_time_type'] . "'
                            AND `search_index_document_id` = " . $data_arr['search_time_document_id']);
            $FD->db()->conn()->exec('
                            UPDATE `' . $FD->env('DB_PREFIX') . "search_time`
                            SET `search_time_date` = '" . time() . "'
                            WHERE `search_time_id` = '" . $data_arr['search_time_id'] . "'");
        } else {
            $FD->db()->conn()->exec('
                            INSERT INTO
                                `' . $FD->env('DB_PREFIX') . "search_time`
                                (`search_time_type`, `search_time_document_id`, `search_time_date`)
                            VALUES (
                                '" . $data_arr['search_time_type'] . "',
                                '" . $data_arr['search_time_document_id'] . "',
                                '" . time() . "'
                            )");
        }

        // Pass through word list
        $word_arr = explode(' ', $data_arr['search_data']);
        $index_arr = array();
        foreach ($word_arr as $word) {
            if (strlen($word) > 32) {
                $word = substr($word, 0, 32);
            }
            $word_id = get_search_word_id($word);

            if (isset ($index_arr[$word_id])) {
                $index_arr[$word_id]['search_index_count'] = $index_arr[$word_id]['search_index_count'] + 1;
            } else {
                $index_arr[$word_id]['search_index_word_id'] = $word_id;
                $index_arr[$word_id]['search_index_type'] = $data_arr['search_time_type'];
                $index_arr[$word_id]['search_index_document_id'] = $data_arr['search_time_document_id'];
                $index_arr[$word_id]['search_index_count'] = 1;
            }
        }
        sort($index_arr);

        $insert_values = array();
        foreach ($index_arr as $word_data) {
            $insert_values[] = '(' . $word_data['search_index_word_id'] . ", '" . $word_data['search_index_type'] . "', " . $word_data['search_index_document_id'] . ', ' . $word_data['search_index_count'] . ' )';
        }

        // Insert Indexes
        $FD->db()->conn()->exec('
                        INSERT INTO
                            `' . $FD->env('DB_PREFIX') . 'search_index`
                            (`search_index_word_id`, `search_index_type`, `search_index_document_id`, `search_index_count`)
                        VALUES
                            ' . implode(',', $insert_values) . '');
    }
}


function get_make_search_index($FOR)
{
    global $FD;

    switch ($FOR) {
        case 'dl':
            // DL
            return $FD->db()->conn()->query("
                SELECT
                    `dl_id` AS 'search_time_document_id',
                    `search_time_id`,
                    'dl' AS 'search_time_type',
                    CONCAT(`dl_name`, ' ', `dl_text`) AS 'search_data'
                FROM `" . $FD->env('DB_PREFIX') . 'dl`
                LEFT JOIN `' . $FD->env('DB_PREFIX') . "search_time`
                    ON `search_time_document_id` = `dl_id`
                    AND FIND_IN_SET('dl', `search_time_type`)
                WHERE 1
                    AND ( `search_time_id` IS NULL OR `dl_search_update` > `search_time_date` )
                ORDER BY `dl_search_update`");
            break;
        case 'articles':
            // Articles
            return $FD->db()->conn()->query("
                SELECT
                    `article_id` AS 'search_time_document_id',
                    `search_time_id`,
                    'articles' AS 'search_time_type',
                    CONCAT(`article_title`, ' ', `article_text`) AS 'search_data'
                FROM `" . $FD->env('DB_PREFIX') . 'articles`
                LEFT JOIN `' . $FD->env('DB_PREFIX') . "search_time`
                    ON `search_time_document_id` = `article_id`
                    AND FIND_IN_SET('articles', `search_time_type`)
                WHERE 1
                    AND ( `search_time_id` IS NULL OR `article_search_update` > `search_time_date` )
                ORDER BY `article_search_update`");
            break;
        case 'news':
            // News
            return $FD->db()->conn()->query("
                SELECT
                    `news_id` AS 'search_time_document_id',
                    `search_time_id`,
                    'news' AS 'search_time_type',
                    CONCAT(`news_title`, ' ', `news_text`) AS 'search_data'
                FROM `" . $FD->env('DB_PREFIX') . 'news`
                LEFT JOIN `' . $FD->env('DB_PREFIX') . "search_time`
                    ON `search_time_document_id` = `news_id`
                    AND FIND_IN_SET('news', `search_time_type`)
                WHERE 1
                    AND ( `search_time_id` IS NULL OR `news_search_update` > `search_time_date` )
                ORDER BY `news_search_update`");
            break;
    }
}

function get_search_word_id($WORD)
{
    global $FD;

    $stmt = $FD->db()->conn()->prepare('
                SELECT `search_word_id` FROM `' . $FD->env('DB_PREFIX') . 'search_words`
                WHERE `search_word` = ?');
    $stmt->execute(array($WORD));
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($result !== false) {
        $id = $result['search_word_id'];
        $stmt->closeCursor();
        return $id;
    } else {
        $stmt = $FD->db()->conn()->prepare('
                    INSERT INTO `' . $FD->env('DB_PREFIX') . "search_words` (`search_word`)
                    VALUES (?)");
        $stmt->execute(array($WORD));
        return $FD->db()->conn()->lastInsertId();
    }
}

function compress_search_data($TEXT)
{
    $locSearch[] = "=�=i";
    $locSearch[] = "=�|�=i";
    $locSearch[] = "=�|�=i";
    $locSearch[] = "=�|�=i";
    $locSearch[] = "=�|�|�|�|�|�=i";
    $locSearch[] = "=�|�|�|�|�|�=i";
    $locSearch[] = "=�|�|�|�|�|�=i";
    $locSearch[] = "=�|�|�|�|�|�|�=i";
    $locSearch[] = "=�|�|�|�|�|�|�=i";
    $locSearch[] = "=�=i";
    $locSearch[] = "=�=i";
    $locSearch[] = "=([0-9/.,+-]*\s)=";
    $locSearch[] = "=([^A-Za-z])=";
    $locSearch[] = "=\s+=";

    $locReplace[] = 'ss';
    $locReplace[] = 'ae';
    $locReplace[] = 'oe';
    $locReplace[] = 'ue';
    $locReplace[] = 'a';
    $locReplace[] = 'o';
    $locReplace[] = 'u';
    $locReplace[] = 'e';
    $locReplace[] = 'i';
    $locReplace[] = 'n';
    $locReplace[] = 'c';
    $locReplace[] = ' ';
    $locReplace[] = ' ';
    $locReplace[] = ' ';

    $TEXT = trim(strtolower(killfs($TEXT)));
    $TEXT = preg_replace($locSearch, $locReplace, $TEXT);
    return $TEXT;
}

function delete_stopwords($TEXT)
{
    global $FD;
    $FD->loadConfig('search');

    $locSearch = array();
    $locReplace = array();

    $locSearch[] = "=(\s[A-Za-z]{1," . ($FD->cfg('search', ('search_min_word_length')) - 1) . "})\s=";
    $locReplace[] = ' ';

    // Use Stopwords?
    if ($FD->config('search', 'search_use_stopwords') == 1) {
        $locSearch[] = '= ' . implode(' | ', get_stopwords()) . " =i";
        $locReplace[] = ' ';
    }

    $locSearch[] = "= +=";
    $locReplace[] = ' ';

    $TEXT = ' ' . str_replace(' ', '  ', $TEXT) . ' ';
    $TEXT = trim(preg_replace($locSearch, $locReplace, $TEXT));
    return $TEXT;
}


function get_stopwords()
{
    $stopfilespath = FS2MEDIA . '/stopwords/';
    $stopfiles = scandir_ext($stopfilespath, 'txt');
    $ACCESS = new fileaccess();

    $return_arr = array();
    foreach ($stopfiles as $file) {
        $return_arr = array_merge($return_arr, $ACCESS->getFileArray($stopfilespath . $file));
    }
    return array_map('trim', $return_arr);
}

// fucntion to compare found-data-arrays
function compare_found_data($v1, $v2)
{
    return compare_update_rank($v1, $v2, create_function('$r1, $r2', 'return $r1;'));;
}

;

// fucntion to compare found-data-arrays and update rank
function compare_update_rank(&$v1, $v2, $func)
{
    if ($v1['id'] > $v2['id'])
        return -1;
    if ($v1['id'] == $v2['id']) {
        $v1['rank'] = $func($v1['rank'], $v2['rank']);
        return 0;
    }
    return 1;
}

;
?>
