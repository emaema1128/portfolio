<?php
require_once('../../../config/config.php');
require_once($basedir . 'Class_DBConnect.php');
require_once($basedir . 'Class_Staff.php');

Staff::checkLoginAndPermission();

$dbh = DBConnect::connect();

// error_log(print_r($_POST,true));

if (empty($_POST['from_date']) || empty($_POST['to_date'])) {
  echo '[ERROR]:  日付に空の項目があります。';
  exit;
}
$from_datetime = $_POST['from_date'];
$to_datetime = $_POST['to_date'];

if (empty($_POST['chara_ids'])) {
  echo "[ERROR]:  サポーターを選択して下さい。";
  exit;
}
// } elseif(!empty($_POST['chara_ids'])) {
if (!is_array($_POST['chara_ids'])) {
  $chara_ids_str = implode(',', array_filter(preg_split('/\r\n|[\r\n]/', $_POST['chara_ids']), 'is_numeric'));
  $chara_ids_arr = array_unique(explode(',', $chara_ids_str));
} else {
  $chara_ids_str = implode(',', $_POST['chara_ids']);
  $chara_ids_arr = explode(',', $chara_ids_str);
}
if (count($chara_ids_arr) > 30) {
  echo "[ERROR]:  検索上限は30人です。";
  exit;
}
// }


// } elseif (count($_POST['chara_ids']) > 30) {
//   $chara_ids_str = implode(',', array_filter(preg_split('/\r\n|[\r\n]/', $_POST['chara_ids_text']), 'is_numeric'));
//   echo "[ERROR]:  検索上限は30人です。";
//   exit;
// }

// $chara_ids_str = implode(',', $_POST['chara_ids']);
$chara_ids_where = " AND system_id IN ($chara_ids_str)";

$sql = "SELECT system_id, username
        FROM user
        WHERE support_flag = 1
        $chara_ids_where
        ORDER BY system_id";
$stmt = $dbh->prepare($sql);
$stmt->execute();
$results = $stmt->fetchall(PDO::FETCH_KEY_PAIR);

$select_parts = "";
$left_join_parts = "";
foreach ($results as $chara_id => $chara_name) {
  $select_parts .=  " ,yd{$chara_id}.yes_deposit_count AS yes_deposit{$chara_id}
                      ,nd{$chara_id}.no_deposit_count AS no_deposit{$chara_id}
                    ";

  $left_join_parts .= " LEFT JOIN (
                          SELECT
                            DATE(m.created_at) AS created_at
                            ,COUNT(DISTINCT m.from_id) AS yes_deposit_count
                          FROM mail m
                          WHERE
                            EXISTS (
                              SELECT 1
                              FROM user u
                              WHERE u.system_id = m.from_id
                                AND u.first_deposit_date IS NOT NULL
                            )
                          AND to_id = {$chara_id}
                          GROUP BY DATE(m.created_at)
                        ) yd{$chara_id} ON yd{$chara_id}.created_at = DATE(m.created_at)
                        LEFT JOIN (
                          SELECT
                            DATE(m.created_at) AS created_at
                            ,COUNT(DISTINCT m.from_id) AS no_deposit_count
                          FROM mail m
                          WHERE
                            EXISTS (
                              SELECT 1
                              FROM user u
                              WHERE u.system_id = m.from_id
                                AND u.first_deposit_date IS NULL
                            )
                          AND to_id = {$chara_id}
                          GROUP BY DATE(m.created_at)
                        ) nd{$chara_id} ON nd{$chara_id}.created_at = DATE(m.created_at)
                      ";
}

$sql = "SELECT
          m.created_at
          $select_parts
        FROM (
          SELECT DATE(created_at) AS created_at
          FROM mail
          GROUP BY DATE(created_at)
        ) m
        $left_join_parts
        WHERE m.created_at >= :from_date
        AND m.created_at <= :to_date
        ORDER BY DATE(m.created_at)";
$stmt = $dbh->prepare($sql);
$stmt->bindValue(":from_date", $from_datetime, PDO::PARAM_STR);
$stmt->bindValue(":to_date", $to_datetime, PDO::PARAM_STR);
$stmt->execute();
$fetchs = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="ja" dir="ltr">

<head>
  <?php require($basedir . '/manage/head.php') ?>
</head>
<body>
  <div class="wrap scroll_x_y data_table_stripe">
    <h1>日別送信者数集計</h1>
    <?php if (!empty($fetchs)) : ?>
      <table>
        <thead>
          <tr>
            <th rowspan="3">日付</th>
            <?php foreach ($results as $chara_name): ?>
              <th class="border-right-bold" colspan="2"><?= $chara_name ?></th>
            <?php endforeach; ?>
          </tr>
          <tr>
            <?php foreach ($results as $chara_name): ?>
              <th class="border-right-bold" colspan="2">入金</th>
            <?php endforeach; ?>
          </tr>
          <tr>
            <?php foreach ($results as $chara_name): ?>
              <th>あり</th>
              <th class="border-right-bold">なし</th>
            <?php endforeach; ?>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($fetchs as $fetch): ?>
            <tr>
              <td><?= date('m/d', strtotime($fetch['created_at'])).'('.['日','月','火','水','木','金','土'][date('w', strtotime($fetch['created_at']))].')' ?></td>
              <?php foreach ($results as $id => $chara_name): ?>
                <td><?= $fetch["yes_deposit{$id}"] ? "<a href='extract.php?chara_id=yes_deposit{$id}&created_at={$fetch['created_at']}'> {$fetch["yes_deposit{$id}"]} </a>" : 0 ?></td>
                <td class="border-right-bold"><?= $fetch["no_deposit{$id}"] ? "<a href='extract.php?chara_id=no_deposit{$id}&created_at={$fetch['created_at']}'> {$fetch["no_deposit{$id}"]} </a>" : 0 ?></td>
              <?php endforeach; ?>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php else : ?>
      <p>検索結果：  該当なし</p>
    <?php endif ?>
  </div>
</body>

</html>