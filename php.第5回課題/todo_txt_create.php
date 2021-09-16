<?php

// まずは`var_dump($_POST);`で値を確認すること！！

// データの受け取り
$name = $_POST["name"];
$gender = $_POST["gender"];
$age = $_POST["age"];
$select = $_POST["select"];
$range = $_POST["delicious"];
$text = $_POST["text"];

// データ1件を1行にまとめる（最後に改行を入れる）
$write_data = "{$name} {$gender} {$age} {$select} {$range} {$text}\n";

// ファイルを開く．引数が`a`である部分に注目！
$file = fopen('data/data.csv', 'a');

// ファイルをロックする
flock($file, LOCK_EX);

// 指定したファイルに指定したデータを書き込む
fwrite($file, $write_data);

// ファイルのロックを解除する
flock($file, LOCK_UN);

// ファイルを閉じる
fclose($file);

// データ入力画面に移動する
header("Location:todo_txt_input.php");

?>