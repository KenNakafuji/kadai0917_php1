<?php
// todo_txt_read.php

// データまとめ用の空文字変数
$str = '';

// ファイルを開く（読み取り専用）
$file = fopen('data/data.csv', 'r');

// ファイルをロック
flock($file, LOCK_EX);

// fgets()で1行ずつ取得→$lineに格納
if ($file) {
  while ($line = fgets($file)) {
    // 取得したデータを`$str`に追加する
    $str .= "<tr><td>{$line}</td></tr>";
  }
}

// ロックを解除する
flock($file, LOCK_UN);
// ファイルを閉じる
fclose($file);

// `$str`に全てのデータ（タグに入った状態）がまとまるので，HTML内の任意の場所に表示する．



// 投票選択肢
$alcohols = ['和🍣', '洋🍔', '中🥟'];

// 送信有無を判定する関数
$is_posted = fn (array $post): bool => count($post) > 0;

// バリデーションする関数
$is_validated = fn (array $post, array $dataArray): bool =>
isset($post['type'])
  && in_array($post['type'], $dataArray, true)
  && $post['post_date'] !== "";

// ファイル書き込みする関数
function write_data_to_file(string $file_name, array $data): bool
{
  $file = fopen($file_name, 'a');
  flock($file, LOCK_EX);
  fwrite($file, "{$data['post_date']} {$data['type']}\n");
  flock($file, LOCK_UN);
  return fclose($file);
}

// ファイルあれば中身取得して配列に入れる関数
$get_raw_data = fn (string $file_path): array =>
file_exists($file_path)
  ? file(__DIR__ . '/' . $file_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)
  : [];

// 生データをかっこいい配列にする関数
$generate_fantastic_array = fn (array $raw_data): array =>
array_map(
  fn ($x) =>
  [
    'post_date' => explode(' ', $x)[0],
    'type' => str_replace("\n", '', explode(' ', $x)[1]),
  ],
  $raw_data
);

// 配列中のtypeで集計する関数
$get_type_count = fn (string $type, array $array): int => count(array_filter($array, fn ($x) => $x['type'] === $type));
$get_type_percent = fn (string $type, array $array): float => (count(array_filter($array, fn ($x) => $x['type'] === $type)) * 100 / (count($array) !== 0 ? count($array) : 1));

// 集計した配列を作成する関数
$get_result = fn (array $type_array, array $data_array): array => array_map(
  fn ($x) => [
    'type' => $x,
    'count' => $get_type_count($x, $data_array),
    'percent' => $get_type_percent($x, $data_array)
  ],
  $type_array
);

// データ送信時にデータ追加
if ($is_posted($_POST) && $is_validated($_POST, $alcohols)) {
  write_data_to_file('data/data.csv', $_POST);
  header('Location:todo_txt_input.php');
  exit();
}

// データ読み込み時にデータ取得して集計
$result_data = $get_result($alcohols, $generate_fantastic_array($get_raw_data('data/data.csv')));


?>

<!-- HTML -->

<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>textファイル書き込み型todoリスト（入力画面）</title>
</head>

<body>
  <form action="todo_txt_create.php" method="POST">
    <fieldset>
      <legend>最近食べた美味しい料理アンケート</legend>
      <a href="todo_txt_read.php" method="POST">一覧画面</a>
      <div>
        名前: <input type="name" name="name" placeholder="山田太郎">
      </div>
      <div>
        性別: <label><input type="radio" name="gender">男性</label>
        <label><input type="radio" name="gender">女性</label>

      </div>
      <div>
        年齢: <input type="number" name="age" placeholder="1">歳
      </div>

      料理カテゴリー
      <div id="select"></div>

      <div>
        <label for="美味しさ">
          おいしさ：まずい<input type="range" name="delicious" min="1" max="5">激うま！

          <div>
            どこで何を食べたか: <input type="text" name="text" placeholder="中央区の定食屋でカツカレー">
          </div>

          <div>
            <button>submit</button>
          </div>
    </fieldset>

    <fieldset>
      <form action="todo_txt_create.php" method="POST">
        <legend>みんなの美味しかった料理リスト</legend>
        <table>
          <thead>
            <tr>
              <th>アンケート掲示板</th>
            </tr>
          </thead>
          </label>
          </div>
          <div>
            <tbody><?= $str ?></tbody>
        </table>
    </fieldset>

  </form>









  <body>
    <form action="" method="post">
      <fieldset>
        <legend>投票の分析</legend>

        <button>submit</button>
        </div>
      </fieldset>
    </form>

    <fieldset>
      <legend>結果（%）</legend>
      <div id="result"></div>
    </fieldset>

    <script>
      const selectTags = <?= json_encode($alcohols) ?>.map(x => `<div><label for="${x}">${x}: <input type="radio" name="type" id="${x}" value="${x}"></label></div>`).join('');
      const tagArray = <?= json_encode($result_data) ?>.map(x => `<p>${[...new Array(x.percent|0)].fill(x.type).join('')} ${ !isNaN(x.percent) ? (Math.round(x.percent * 100) / 100) : 0 } (${ !isNaN(x.count) ? (Math.round(x.count * 100) / 100) : 0 })</p>`);
      document.getElementById('select').innerHTML = selectTags;
      document.getElementById('result').innerHTML = tagArray.join('');
    </script>
  </body>






</body>

</html>