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
  for ($i = 0; $i < count($radioFile); $i++) {
    $radioName = trim($radioFile[$i]);
    $prepared = $db->prepare("INSERT INTO radio_stations (id, name) VALUES (?,?)");
    $prepared->execute([$i + 1, $radioName]);
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
    $obj->video_id = trim($urlFile[$j]);
    $songs[] = $obj;
    $j++;
  }
  foreach ($songs as $song) {
    $prepared = $db->prepare("INSERT INTO songs (radio_id, name, author_name, video_id) VALUES (?,?,?,?)");
    $prepared->execute([$song->radioId, $song->name, $song->authorName, $song->video_id]);
  }

  // --------------------------------------------------------------------------
  // Missions
  $missionsFilePath = __DIR__ . "/missions.json";
  $missions = json_decode(file_get_contents($missionsFilePath));

  $givers = array();
  $origins = array();

  // Find unique values
  foreach ($missions as $mission) {
    if (!in_array($mission->giver, $givers)) {
      $givers[] = $mission->giver;
    }
    if (!in_array($mission->origin, $origins)) {
      $origins[] = $mission->origin;
    }
  }

  for ($i = 0; $i < count($givers); $i++) {
    $prepared = $db->prepare("INSERT INTO mission_givers (id, name) VALUES (?,?)");
    $prepared->execute([$i + 1, $givers[$i]]);
  }

  for ($i = 0; $i < count($origins); $i++) {
    $prepared = $db->prepare("INSERT INTO mission_origins (id, name) VALUES (?,?)");
    $prepared->execute([$i + 1, $origins[$i]]);
  }

  for ($i = 0; $i < count($missions); $i++) {
    $giverId = array_search($missions[$i]->giver, $givers) + 1;
    $originId = array_search($missions[$i]->origin, $origins) + 1;

    $prepared = $db->prepare("INSERT INTO missions (id, title, origin_id, giver_id, description, objective, reward) VALUES (?,?,?,?,?,?,?)");
    $prepared->execute([
      $i + 1,
      $missions[$i]->title,
      $originId,
      $giverId,
      $missions[$i]->description,
      $missions[$i]->objective,
      $missions[$i]->reward,
    ]);
  }

  echo "Success";
} catch (PDOException $ex) {
  echo "Failed";
  echo $ex;
}
