<?php

$host = $HOST;
$dbname = $DBNAME;
$username = $USERNAME;
$password = $PASSWORD;

$db = new PDO("mysql:host=$host;dbname=$dbname;user=$username;password=$password");

try {
  $db->exec($dbBuilder);

  $songFile = file(__DIR__ . "/songs_list.txt");
  $songs = [];

  $radioFile = file(__DIR__ . "/radio_list.txt");
  foreach ($radioFile as $radioName) {
    $radioName = trim($radioName);
    $prepared = $db->prepare("INSERT INTO radio_stations (name) VALUES (?)");
    $prepared->execute([$radioName]);
  }

  $i = 1;
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
    $songs[] = $obj;
  }
  var_dump($songs);
  foreach ($songs as $song) {
    $prepared = $db->prepare("INSERT INTO songs (radio_id, name, author_name) VALUES (?,?,?)");
    $prepared->execute([$song->radioId, $song->name, $song->authorName]);
  }


  echo "Success";
} catch (PDOException $ex) {
  echo "Failed";
  echo $ex;
}
