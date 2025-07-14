<?php
require_once('../../../config/config.php');
require_once($basedir . 'Class_DBConnect.php');
require_once($basedir . 'Class_Staff.php');

Staff::checkLoginAndPermission();

?>


<!DOCTYPE html>
<html lang="ja" dir="ltr">

<head>
  <?php require($basedir . '/manage/head.php'); ?>
</head>

<body>

  <div id="superwrap">
    <iframe src="form.php" name="form_frame" frameborder="0" class="frame mini"></iframe>
    <iframe src="" name="search_frame" frameborder="0" class="frame full full-width"></iframe>
  </div>

</body>

</html>