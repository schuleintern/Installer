<?php 

$action = $_GET['action'];

switch ($action) {

  /*
    Step 1 - Server - get
  */
  case 'server';

    $return = array(
      'currentDir' => getcwd(),
      'currentDirWriteAble' => is_writable('.'),
      'upperDir' => getcwd(). "/../",
      'upperDirWriteable' => is_writable("../."), // Ein Verzeichnis nach oben
      'phpVersion' => phpversion(),
      'phpVersionCompare' => version_compare(phpversion(), '7.2.0', '>')
    );
    echo json_encode($return);
    exit;

    break;
  
  /*
    Step 2 - Settings - get
  */
  case 'settings';

    $return = array(
      'uri' => (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://{$_SERVER['HTTP_HOST']}/index.php",
      'cronkey' => getRandomString(30),
      'apikey' => getRandomString(30),
      'adminuser' => 'admin',
      'adminpass' => getRandomString(10),
      'version' => 'stable'
    );
    echo json_encode($return);
    exit;

    break;

  /*
    Step 3 - Install - post
  */
  case 'install';


    $data = $_POST;
    //print_r($data);

    echo json_encode( array('install' => true) );
    exit;

    break;


  default:
    exit;
    break;

}

function getRandomString($length) {
  return str_replace("+","S", str_replace("/", "L", substr(base64_encode(random_bytes(100)), 4, $length)));
}