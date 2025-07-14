<?php
require_once('../../../config/config.php');
require_once($basedir . 'Class_DBConnect.php');
require_once($basedir . 'Class_QuickLogin.php');
require_once($basedir . 'Class_Staff.php');

Staff::checkLoginAndPermission();

$dbh = DBConnect::connect();

$enable_chara_list = Chara::getCharaEnableList();
$enable_chara_list = empty($enable_chara_list) ? [] : $enable_chara_list;

?>

<!DOCTYPE html>
<html lang="ja" dir="ltr">

<head>
  <?php require($basedir . '/manage/head.php') ?>
</head>

<body>
  <div class="wrap">
    <h1>日別送信者数集計</h1>
    <form action="search.php" method="post" target="search_frame">
      <table>
        <tr>
          <th colspan="2">日時</th>
        </tr>
        <tr>
          <td colspan="2">
            <div>
              <label>
                <input type="date" name="from_date" value="<?= date('Y-m-01') ?>">
              </label>～
            </div>
            <div>
              <label>
                <input type="date" name="to_date" value="<?= date('Y-m-d') ?>">
              </label>
            </div>
          </td>
        </tr>
        <tr>
          <th>絞り込み(選択上限 30人)</th>
          <th class="id_search"><button class="id_search_button" type="button">ID</button></th>
          <th class="chara_search hide"><button class="chara_search_button" type="button">サポーター</button></th>
        </tr>
        <tr>
          <td class="id_search" colspan="2">
            <select class="id_search" name="chara_ids[]" style="height: 345px; width: 255px; resize: vertical;" multiple>
              <?php foreach ($enable_chara_list as $chara_data) : ?>
                <option value="<?= $chara_data['system_id'] ?>"><?= $chara_data['username'] ?></option>
              <?php endforeach ?>
            </select>
          </td>
        </tr>
        <tr>
          <td class="chara_search hide" colspan="2">
            <textarea class="chara_search resize_vertical" name="chara_ids" rows="23" cols="37" disabled></textarea>
          </td>
        </tr>
        <tr class="button">
          <td colspan="2"><button type="submit">検索</button></td>
        </tr>
      </table>
    </form>
  </div>

  <script type="text/javascript">
    $('.id_search_button').on('click', function() {
      $('.id_search').addClass('hide').prop('disabled', true);
      $('.chara_search').removeClass('hide').prop('disabled', false);
    });

    $('.chara_search_button').on('click', function() {
      $('.chara_search').addClass('hide').prop('disabled', true);
      $('.id_search').removeClass('hide').prop('disabled', false);
    });
  </script>
</body>

</html>