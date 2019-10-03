<?php 

define('UPDATESERVER', 'https://update.schule-intern.de');
define('FERIENURL', 'https://ferien.schule-intern.de/Ferien.txt');

//define('FOLDER','../install');
define('FOLDER_TEMP','./install_temp');

define('FOLDER_ROOT','./system');

@error_reporting(E_ERROR);


$action = $_GET['action'];

switch ($action) {

  /*
    Step 1 - Server - get
  */
  case 'server';

    $branches = file_get_contents(UPDATESERVER . "/api/branches");
    if($branches != false) {
        $branches = true;
    }

    $return = array(
      'currentDir' => getcwd(),
      'currentDirWriteAble' => is_writable('.'),
      'upperDir' => getcwd(). "/../",
      'upperDirWriteable' => is_writable("../."), // Ein Verzeichnis nach oben
      'phpVersion' => phpversion(),
      'phpVersionCompare' => version_compare(phpversion(), '7.2.0', '>'),
      'branches' => $branches
    );
    echo json_encode($return);
    exit;

    break;
  
  /*
    Step 2 - Settings - get
  */
  case 'settings';

    $branches = file_get_contents(UPDATESERVER . "/api/branches");
    if($branches === false) {
      exit;
    }

    $return = array(
      'uri' => (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://{$_SERVER['HTTP_HOST']}/index.php",
      'cronkey' => getRandomString(30),
      'apikey' => getRandomString(30),
      'adminuser' => 'admin',
      'dbport' => '3306',
      'adminpass' => getRandomString(10),
      'notenverwaltung' => 0,
      'branches' => json_decode($branches, true)
    );
    echo json_encode($return);
    exit;

    break;

  /*
    Step 3 - Install - post
  */
  case 'install';

    $return = array('install' => true);

    // Reihenfolge ist wichtig!
    $return['list'] = array(
      'downloadBranch' => downloadBranch(),
      'moveFiles' => moveFiles(),
      'makeConfig' => makeConfig(),
      'initDbTable' => initDbTable(),
      'dbPreSettings' => dbPreSettings(),
      'removeFolder' => removeFolder()
    );

    foreach ($return['list'] as $key => $value) {
      if ($value != true || isset($value['errorMsg'])) {
        $return['error'] = true;
        $return['install'] = false;
      }
    }
    
    echo json_encode($return);
    exit;

    break;

  default:
    exit;
    break;

}

function removeFolder() {
  
  if ( !rrmdir(FOLDER_TEMP) ) {
    return array(
      'errorMsg' => 'Ordner konnte nicht gelöscht werden!'
    );
  }
  return true;

}

function initDbTable() {

  if ( !isset($_POST['dbhost']) ) {
    return array(
      'errorMsg' => 'Fehlende Daten! (Host)'
    );
  }
  if ( !isset($_POST['dbuser']) ) {
    return array(
      'errorMsg' => 'Fehlende Daten! (User)'
    );
  }
  if ( !isset($_POST['dbname']) ) {
    return array(
      'errorMsg' => 'Fehlende Daten! (Name)'
    );
  }

  $mysqli = new mysqli(
    $_POST['dbhost'],
    $_POST['dbuser'],
    $_POST['dbpass'] || '',
    $_POST['dbname']
  );

  if ($mysqli->connect_errno) {
    return array(
      'errorMsg' => 'Es kann keine Verbindung zur Datenbank aufgebaut werden.'
    );
  }

  if ( !file_exists(FOLDER_TEMP."/Datenbank/Installation/database.sql") ) {
    return array(
      'errorMsg' => 'Fehler beim Download und entpacken. database.sql wurde nicht gefunden.'
    );
  }
  $sqlInstallation = file_get_contents(FOLDER_TEMP."/Datenbank/Installation/database.sql");

  if ( !$sqlInstallation ) {
    return array(
      'errorMsg' => 'Datenbank Datei konnte nicht geöffnet werden.'
    );
  }

  if ( !$mysqli->multi_query($sqlInstallation) ) {
    return array(
      'errorMsg' => 'Datenbank Query konnte nicht richtig ausgeführt werden.'
    );
  }

  return true;

}

function dbPreSettings() {

  $mysqli = new mysqli(
    $_POST['dbhost'],
    $_POST['dbuser'],
    $_POST['dbpass'],
    $_POST['dbname']
  );

  if ($mysqli->connect_errno) {
    return array(
      'errorMsg' => 'Es kann keine Verbindung zur Datenbank aufgebaut werden.'
    );
  }

  $sqlInstallation = file_get_contents("./config/presettings.sql");

  $install = $mysqli->multi_query($sqlInstallation);

  return true;
}

function makeConfig() {
  // Make Config file
  $configTemp = file_get_contents("./config/temp.php");

  $configTemp = str_replace('{{schulnummer}}', $_POST['nummer'], $configTemp);
  $configTemp = str_replace('{{dbhost}}', $_POST['dbhost'], $configTemp);
  $configTemp = str_replace('{{dbport}}', $_POST['dbport'], $configTemp);
  $configTemp = str_replace('{{dbname}}', $_POST['dbname'], $configTemp);
  $configTemp = str_replace('{{dbuser}}', $_POST['dbuser'], $configTemp);
  $configTemp = str_replace('{{dbpass}}', $_POST['dbpass'], $configTemp);
  $configTemp = str_replace('{{name}}', $_POST['name'], $configTemp);
  $configTemp = str_replace('{{name1}}', $_POST['name1'], $configTemp);
  $configTemp = str_replace('{{name2}}', $_POST['name2'], $configTemp);
  $configTemp = str_replace('{{uri}}', $_POST['uri'], $configTemp);
  $configTemp = str_replace('{{cronkey}}', $_POST['cronkey'], $configTemp);
  $configTemp = str_replace('{{apikey}}', $_POST['apikey'], $configTemp);
  $configTemp = str_replace('{{elternbenutzer}}', $_POST['elternbenutzer'], $configTemp);
  $configTemp = str_replace('{{stundenplan}}', $_POST['stundenplan'], $configTemp);
  $configTemp = str_replace('{{notenverwaltung}}', $_POST['notenverwaltung'], $configTemp);
  $configTemp = str_replace('{{ferienURL}}', FERIENURL, $configTemp);
  $configTemp = str_replace('{{updateServer}}', UPDATESERVER, $configTemp);

  file_put_contents(FOLDER_ROOT."/data/config/config.php", $configTemp);

  return true;
}


function moveFiles() {

  // Move Files to Folders
  mkdir(FOLDER_ROOT);
  rename(FOLDER_TEMP."/Upload/cli", FOLDER_ROOT."/cli");
  rename(FOLDER_TEMP."/Upload/data", FOLDER_ROOT."/data");
  rename(FOLDER_TEMP."/Upload/framework", FOLDER_ROOT."/framework");
  rename(FOLDER_TEMP."/Upload/www", FOLDER_ROOT."/www");
  //recCopy(FOLDER_TEMP."/Upload/www", FOLDER_ROOT);

  return true;

}

function downloadBranch() {

  $version = file_get_contents(UPDATESERVER . "/api/branch/" . $_POST['branch'] . "/version");

  if ($version === false) {
    
    return array(
      'errorMsg' => 'Es kann leider keine aktuelle Version heruntergeladen werden.'
    );
    //die("Es kann leider keine aktuelle Version heruntergeladen werden: " . UPDATESERVER . "/api/branch/" . $_POST['branches'] . "/version");
  
  } else {
      $version = json_decode($version, true);
      $url = UPDATESERVER . "/api/release/" . $version['id'] . "/download";

      //$_SESSION['SI_INSTALL_INSTALL_VERSION_ID'] = $version['id'];

      mkdir(FOLDER_TEMP);

      file_put_contents(FOLDER_TEMP."/install.zip", fopen($url, 'r'));

      $zip = new ZipArchive;
      if ($zip->open(FOLDER_TEMP.'/install.zip') === TRUE) {
          $zip->extractTo(FOLDER_TEMP.'/');
          $zip->close();
          return true;

      } else {
          return array(
            'errorMsg' => 'Installationsdatei konnte nicht entpackt werden.'
          );
      }
  }
  
}



function getRandomString($length) {
  return str_replace("+","S", str_replace("/", "L", substr(base64_encode(random_bytes(100)), 4, $length)));
}

function rrmdir($dir) { 
  if (is_dir($dir)) { 
    $objects = scandir($dir); 
    foreach ($objects as $object) { 
      if ($object != "." && $object != "..") { 
        if (is_dir($dir."/".$object))
          rrmdir($dir."/".$object);
        else
          unlink($dir."/".$object); 
      } 
    }
    rmdir($dir); 
    return true;
  } 
  return false;
}