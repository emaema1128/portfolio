<?php
require_once('../../../config/config.php');
require_once($basedir . 'Class_DBConnect.php');
require_once($basedir . 'Class_Staff.php');

Staff::checkLoginAndPermission();

$dbh = DBConnect::connect();

if ((empty($_GET['chara_id'])) || (empty($_GET['created_at'])) || ($_GET['deposit'] !== 'yes' && $_GET['deposit'] !== 'no')) {
  echo '[ERROR] 値が不正です。';
  exit;
}

$chara_id = $_GET['chara_id'];
$created_at = $_GET['created_at'];
$deposit = $_GET['deposit'];

if ($deposit === "yes") {
  $exists_parts = " AND u.first_deposit_date IS NOT NULL";
} else {
  $exists_parts = " AND u.first_deposit_date IS NULL";
}

$sql = "SELECT DISTINCT from_id
        FROM mail m
        WHERE DATE(m.created_at) = '{$created_at}'
        AND to_id = $chara_id
        AND EXISTS (
              SELECT 1
              FROM user u
              WHERE u.system_id = m.from_id
              $exists_parts
            )";
$stmt = $dbh->prepare($sql);
$stmt->execute();
$fetchs = $stmt->fetchAll(PDO::FETCH_COLUMN);
$system_ids = implode(PHP_EOL, $fetchs);

?>

<!DOCTYPE html>
<html lang="ja" dir="ltr">

<head>
  <?php require($basedir . '/manage/head.php') ?>
</head>

<body>
  <div class="wrap">
    <h1>ID抽出結果</h1>
    <p><?= count($fetchs) ?>件 抽出</p>
    <textarea rows="35" cols="40"><?= $system_ids ?></textarea>
  </div>
  <a href="javascript:void(0);" onclick="window.history.back();">戻る</a>
</body>

</html>