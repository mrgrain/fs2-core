<?php if (!defined('ACP_GO')) die('Unauthorized access!');

$JUST_CONTENT = true;


if (!has_perm('stat_view')) die('Unauthorized access!');

if (!isset($_GET['s_year'])) {
    $_GET['s_year'] = date('Y');
}
if (!isset($_GET['s_month'])) {
    $_GET['s_month'] = date('m');
}
settype($_GET['s_year'], 'integer');
settype($_GET['s_month'], 'integer');

$monthnames = explode(',', $FD->text('frontend', 'month_names_array'));
$monthname = $monthnames[$_GET['s_month'] - 1];
$feldbreite = 458 / date_loc('t', mktime(0, 0, 0, $_GET['s_month'], 1, $_GET['s_year']));  // Numbers of days per month

$imagewidth = 500;
$imageheight = 300;

// Colours
$image = imagecreate($imagewidth, $imageheight);
$farbe_body = imagecolorallocate($image, 21, 21, 21);
$farbe_visits = imagecolorallocate($image, 85, 85, 85);
$farbe_hits = imagecolorallocate($image, 119, 119, 119);
$farbe_text = imagecolorallocate($image, 204, 204, 204);
$farbe_rot = imagecolorallocate($image, 48, 48, 48);
$farbe_rand = imagecolorallocate($image, 0, 0, 0);
$farbe_text2 = imagecolorallocate($image, 25, 25, 25);
$farbe_text3 = imagecolorallocate($image, 204, 204, 204);

// Area
imagestring($image, 3, 140, 11, $FD->text('page', 'monthly_statistics') . " ($monthname $_GET[s_year])", $farbe_text);
imagefilledrectangle($image, 20, 35, 45, 40, $farbe_visits);
imagestring($image, 2, 57, 30, 'Visits', $farbe_text);
imagefilledrectangle($image, 455, 35, 480, 40, $farbe_hits);
imagestring($image, 2, 418, 30, 'Hits', $farbe_text);
imagefilledrectangle($image, 20, 45, 480, 285, $farbe_rot);

// Hits curve
$index = $FD->db()->conn()->query(
    'SELECT s_hits
              FROM ' . $FD->env('DB_PREFIX') . "counter_stat
              WHERE s_year  = $_GET[s_year] and
                    s_month = $_GET[s_month]
              ORDER BY s_hits DESC
              LIMIT 1");
$dbmaxhits = $index->fetchColumn();

$hitsarray = array(0);
$arraycount = 2;
$startwert = 21 + $feldbreite / 2;
$anz_tage = date('t', mktime(0, 0, 0, $_GET['s_month'], 1, $_GET['s_year']));
for ($d = 1; $d < $anz_tage + 1; $d++) {
    $index = $FD->db()->conn()->query(
        'SELECT s_hits
                  FROM ' . $FD->env('DB_PREFIX') . "counter_stat
                  WHERE s_year  = $_GET[s_year] and
                        s_month = $_GET[s_month] and
                        s_day   = $d");
    $row = $index->fetch(PDO::FETCH_ASSOC);
    if ($row !== false) {
        $dbhits = $row['s_hits'];
        // X-coordinate
        $hitsarray[$arraycount] = $startwert;
        $startwert = $startwert + $feldbreite;
        $arraycount = $arraycount + 1;
        // Y-coordinate
        $hitsarray[$arraycount] = 285 - ($dbhits / $dbmaxhits * 220);
        $arraycount = $arraycount + 1;
    } else {
        // X-coordinate
        $hitsarray[$arraycount] = $startwert;
        $startwert = $startwert + $feldbreite;
        $arraycount = $arraycount + 1;
        // Y-coordinate
        $hitsarray[$arraycount] = 285;
        $arraycount = $arraycount + 1;
    }
}

// X-coordinate
$hitsarray[$arraycount] = 479;
$arraycount = $arraycount + 1;
// Y-coordinate
$hitsarray[$arraycount] = $hitsarray[$arraycount - 2];
$arraycount = $arraycount + 1;
// X-coordinate
$hitsarray[$arraycount] = 480;
$arraycount = $arraycount + 1;
// Y-coordinate
$hitsarray[$arraycount] = 285;
$arraycount = $arraycount + 1;
// X-coordinate
$hitsarray[$arraycount] = 20;
$arraycount = $arraycount + 1;
// Y-coordinate
$hitsarray[$arraycount] = 285;
$arraycount = $arraycount + 1;

$hitsarray[0] = 21;
$hitsarray[1] = $hitsarray[3];
imagefilledpolygon($image, $hitsarray, round($arraycount / 2), $farbe_hits);


// Visits curve
$index = $FD->db()->conn()->query(
    'SELECT s_visits
              FROM ' . $FD->env('DB_PREFIX') . "counter_stat
              WHERE s_year  = $_GET[s_year] and
                    s_month = $_GET[s_month]
              ORDER BY s_visits DESC
              LIMIT 1");
$dbmaxvisits = $index->fetchColumn();
if ($dbmaxvisits > 0) {
    $visitsarray = array(0);
    $arraycount = 2;
    $startwert = 21 + $feldbreite / 2;
    for ($d = 1; $d < $anz_tage + 1; $d++) {
        $index = $FD->db()->conn()->query(
            'SELECT s_visits
                    FROM ' . $FD->env('DB_PREFIX') . "counter_stat
                    WHERE s_year  = $_GET[s_year] AND
                          s_month = $_GET[s_month] AND
                          s_day   = $d");
        $row = $index->fetch(PDO::FETCH_ASSOC);
        if ($row !== false) {
            $dbvisits = $row['s_visits'];
            // X-coordinate
            $visitsarray[$arraycount] = $startwert;
            $startwert = $startwert + $feldbreite;
            $arraycount = $arraycount + 1;
            // Y-coordinate
            $visitsarray[$arraycount] = 285 - ($dbvisits / $dbmaxvisits * 160);
            $arraycount = $arraycount + 1;
        } else {
            // X-coordinate
            $visitsarray[$arraycount] = $startwert;
            $startwert = $startwert + $feldbreite;
            $arraycount = $arraycount + 1;
            // Y-coordinate
            $visitsarray[$arraycount] = 285;
            $arraycount = $arraycount + 1;
        }
    }

    // X-coordinate
    $visitsarray[$arraycount] = 479;
    $arraycount = $arraycount + 1;
    // Y-coordinate
    $visitsarray[$arraycount] = $visitsarray[$arraycount - 2];
    $arraycount = $arraycount + 1;
    // X-coordinate
    $visitsarray[$arraycount] = 480;
    $arraycount = $arraycount + 1;
    // Y-coordinate
    $visitsarray[$arraycount] = 285;
    $arraycount = $arraycount + 1;
    // X-coordinate
    $visitsarray[$arraycount] = 20;
    $arraycount = $arraycount + 1;
    // Y-coordinate
    $visitsarray[$arraycount] = 285;
    $arraycount = $arraycount + 1;

    $visitsarray[0] = 21;
    $visitsarray[1] = $visitsarray[3];
    imagefilledpolygon($image, $visitsarray, round($arraycount / 2), $farbe_visits);
} else {
    $dbmaxvisits = 1;
}

// Days
$startwert = 24;
for ($d = 1; $d < $anz_tage + 1; $d++) {
    $dayname = date('w', mktime(0, 0, 0, $_GET['s_month'], $d, $_GET['s_year']));
    $daynumber = date('d', mktime(0, 0, 0, $_GET['s_month'], $d, $_GET['s_year']));
    imagestringup($image, 1, $startwert, 280, $daynumber . ' ' . $dayname, $farbe_text2);
    $startwert = $startwert + $feldbreite;
}

// Grid
$startwert = 21;
for ($d = 1; $d < $anz_tage; $d++) {
    $startwert = $startwert + $feldbreite;
    imageline($image, $startwert, 45, $startwert, 285, $farbe_rot);
}

// Scale
imagerectangle($image, 20, 45, 480, 285, $farbe_rand);

switch (TRUE) {
    case ($dbmaxvisits < 10):
        $nvis = $dbmaxvisits;
        $ovis = $dbmaxvisits;
        $add = 1;
        $tvis = '';
        break;
    case ($dbmaxvisits < 100):
        $nvis = ceil($dbmaxvisits / 10);
        $add = 10;
        $ovis = $nvis * 10;
        $tvis = '0';
        break;
    case ($dbmaxvisits < 1000):
        $nvis = ceil($dbmaxvisits / 100);
        $add = 100;
        $ovis = $nvis * 100;
        $tvis = '00';
        break;
    case ($dbmaxvisits < 10000):
        $nvis = ceil($dbmaxvisits / 1000);
        $add = 1000;
        $ovis = $nvis * 1000;
        $tvis = 'k';
        break;
    case ($dbmaxvisits > 10000):
        $nvis = ceil($dbmaxvisits / 10000);
        $add = 10000;
        $ovis = $nvis * 10000;
        $tvis = '0k';
        break;
}

for ($i = 0; $i <= $ovis; $i = $i + $add) {
    $maxx = 285 - ($i / $dbmaxvisits * 160);
    imageline($image, 15, $maxx, 20, $maxx, $farbe_rand);
    imagestring($image, 1, 2, $maxx - 5, $i / $add . $tvis, $farbe_text3);
}

switch (TRUE) {
    case ($dbmaxhits < 10):
        $nvis = $dbmaxhits;
        $ovis = $dbmaxhits;
        $add = 1;
        $tvis = '';
        break;
    case ($dbmaxhits < 100):
        $nvis = round($dbmaxhits / 10);
        $add = 10;
        $ovis = $nvis * 10;
        $tvis = '0';
        break;
    case ($dbmaxhits < 1000):
        $nvis = round($dbmaxhits / 100);
        $add = 100;
        $ovis = $nvis * 100;
        $tvis = '00';
        break;
    case ($dbmaxhits < 10000):
        $nvis = round($dbmaxhits / 1000);
        $add = 1000;
        $ovis = $nvis * 1000;
        $tvis = 'k';
        break;
    case  ($dbmaxhits > 10000):
        $nvis = round($dbmaxhits / 10000);
        $add = 10000;
        $ovis = $nvis * 10000;
        $tvis = '0k';
        break;
}
for ($i = 0; $i <= $ovis; $i = $i + $add) {
    $maxx = 285 - ($i / $dbmaxhits * 220);
    imageline($image, 480, $maxx, 485, $maxx, $farbe_rand);
    imagestring($image, 1, 483, $maxx - 5, $i / $add . $tvis, $farbe_text3);
}

if ($_SESSION['stat_view'] == 1) {
    header('Content-Type: image/png');
    imagepng($image);
}

unset($FD);

?>
