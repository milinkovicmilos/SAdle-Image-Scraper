<?php

$env = file(".env");
foreach ($env as $row) {
  $row = trim($row);
  list($name, $value) = explode('=', $row);
  define($name, $value);
}

$db = new PDO("mysql:host=" . HOST . ";dbname=" . DBNAME . ";user=" . USERNAME . ";password=" . PASSWORD);

try {
  $songFile = file(__DIR__ . "/songs_list.txt");
  $songs = [];

  $radioFile = file(__DIR__ . "/radio_list.txt");
  foreach ($radioFile as $radioName) {
    $radioName = trim($radioName);
    $prepared = $db->prepare("INSERT INTO radio_stations (name) VALUES (?)");
    $prepared->execute([$radioName]);
  }

  $i = 1;
  $j = 0;
  $urlFile = file(__DIR__ . "/url_list.txt");
  foreach ($songFile as $songString) {
    $songString = trim($songString);
    if ($songString == "") {
      $i++;
      continue;
    }

    $arr = explode('+', $songString);
    $obj = new stdClass();
    $obj->authorName = trim($arr[0]);
    $obj->name = trim($arr[1]);
    $obj->radioId = $i;
    $obj->video_id = $urlFile[$j];
    $songs[] = $obj;
    $j++;
  }
  var_dump($songs);
  foreach ($songs as $song) {
    $prepared = $db->prepare("INSERT INTO songs (radio_id, name, author_name, video_id) VALUES (?,?,?,?)");
    $prepared->execute([$song->radioId, $song->name, $song->authorName, $song->video_id]);
  }


  echo "Success";
} catch (PDOException $ex) {
  echo "Failed";
  echo $ex;
}
