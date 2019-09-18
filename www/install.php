<?php

// SchuleIntern Installer
// License GPLv2
// (c) Christian Spitschka

define('UPDATESERVER', 'https://update.schule-intern.de');
define('FERIENURL', 'https://ferien.schule-intern.de/Ferien.txt');


@error_reporting(E_ERROR);

// Prüfe, ob bereits installiert.
$installLock = @file_get_contents("../data/install.lock");

if($installLock !== false) {
    die("Installation bereits vorhanden. Keine weitere Installation möglich. (data/install.lock) vorhanden.");
}

if(!session_start()) die("Keine PHP Sessions möglich.");

// Schritt auswerten
$step = intval($_REQUEST['step']);

$currentStep = $_SESSION['SI_INSTALL_STEP'];

checkStepAccess($step);

switch($step) {
    case 1:
    default:
        step1();
        break;

    case 2:
        step2();
        break;


    case 3: step3(); break;
    case 4: step4(); break;
    case 5: step5(); break;
    case 6: step6(); break;
    case 7: step7(); break;


}


function step1() {
    showHeader(1);

    ?>

    Herzlich Willkommen bei der Installation zu SchuleIntern. Bitte legen Sie sich folgende Daten bereit:

    <ul>

        <li>MySQL / MariaDB Datenbank mit passenden Zugangsdaten</li>
        <li>Mailaccount mit passenden Zugangsdaten</li>

    </ul>

    <?php

    showButtonNextStep(2);

    showFooter();


}

function step2() {
    showHeader(2);

    $failed = false;

    $upperDirWriteable = is_writable("../.");        // Ein Verzeichnis nach oben
    $currentDirWriteAble = is_writable('.');

    if(!$upperDirWriteable) $failed = true;
    if(!$currentDirWriteAble) $failed = true;


    $phpVersion = phpversion();

    if (version_compare(phpversion(), '7.2.0', '<')) {
        $failed = true;
        $phpVersionStatus = "<i class=\"fa fa-ban rot\"></i> Nicht OK. (Min. 7.2)";
    }
    else $phpVersionStatus = "<i class=\"fa fa-check gruen\"></i> OK";


    // Basis Verzeichnis korrekt?

    $requestURI = $_SERVER['REQUEST_URI'];

    if(strpos($requestURI, "?") > 0) {
        $requestURI = explode("?", $requestURI);
    }
    else $requestURI = [$requestURI];

    $requestURICorrect = ($requestURI[0] == '/install.php');




    ?>

    <table style="width: 100%; border: 1px solid; border-collapse: collapse;">
        <tr>
            <th style="text-align: left;width: 50%;">Objekt</th>
            <th style="text-align: left">Status</th>
            <th style="text-align: left">Bemerkung</th>
        </tr>
        <tr>
            <td>Schreibrechte im Übergeordnetem Verzeichnis<br/>
                <code><?php echo getcwd() . "/../" ?></code></td>
            <td><?php if($upperDirWriteable): ?><i class="fa fa-check gruen"></i> OK<?php else: ?><i class="fa fa-ban rot"></i> Nicht OK<?php endif; ?></td>
            <td>SchuleIntern speichert alle Daten außerhalb des von außen erreichbaren Verzeichnisses.</td>
        </tr>
        <tr>
            <td>Schreibrechte im aktuellen Verzeichnis<br/>
                <code><?php echo getcwd() . "" ?></code></td>
            <td><?php if($currentDirWriteAble): ?><i class="fa fa-check gruen"></i> OK<?php else: ?>><i class="fa fa-ban rot">Nicht OK<?php endif; ?></td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td>PHP Version<br/>
                <code>Min. 7.2 - Installiert: <?php echo phpversion() . "" ?></code></td>
            <td><?php echo($phpVersionStatus); ?></td>
            <td>&nbsp;</td>
        </tr>

        <tr>
            <td>Liegt die install.php im Rootverzeichnis des Servers?<br/>
                <code><?php echo $requestURI[0] . "" ?></code></td>
            <td><?php if($requestURICorrect): ?><i class="fa fa-check gruen"></i> OK<?php else: ?><i class="fa fa-ban rot"></i> Nicht OK<?php endif; ?></td>
            <td>Beachten Sie, dass die Software direkt im Root des WebServers liegen muss. Beispiele:
                <ul>
                    <li><i class="fa fa-check gruen"></i>: http://portal.schule.de/install.php</li>
                    <li><i class="fa fa-ban rot"></i> http://www.schule.de/portal/install.php</li>
                </ul>
            Diese Einstellung können Sie bei Ihrem Webhoster vornehmen.</td>
        </tr>


    </table>

    <br /><br />

    <?php

    if(!$failed) showButtonNextStep(3);

    showFooter();
}

function step3() {
    $schulname = $_SESSION['SI_INSTALL_SCHULNAME'];

    $schulnummer = $_SESSION['SI_INSTALL_SCHULNUMMER'];

    $urlToIndex = $_SESSION['SI_INSTALL_INDEXPHP'];

    if($urlToIndex == "") {
        $urlToIndex = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://{$_SERVER['HTTP_HOST']}/index.php";
    }

    $cronkey = $_SESSION['SI_INSTALL_CRONKEY'];

    if($cronkey == "") $cronkey = getRandomString(30);

    $apiKey = $_SESSION['SI_INSTALL_APIKEY'];

    if($apiKey == "") $apiKey = getRandomString(30);

    $name1 = $_SESSION['SI_INSTALL_NAME1'];
    $name2 = $_SESSION['SI_INSTALL_NAME2'];

    $stundenplanProgram = $_SESSION['SI_INSTALL_STUNDENPLAN'];

    $hasNotenverwaltung = $_SESSION['SI_INSTALL_NOTENVERWALTUNG'];


    $elternbenutzermodus = $_SESSION['SI_INSTALL_ELTERNMODUS'];

    // Branches abrufen

    $branches = file_get_contents(UPDATESERVER . "/api/branches");
    if($branches === false) {
        die("Leider besteht keine Verbindung zum Update server: ". UPDATESERVER);
    }

    $branches = json_decode($branches, true);

    $branchesSelect = "";
    for($i = 0; $i < sizeof($branches); $i++) {
        $branchesSelect .= "<option value=\"" . $branches[$i]['Name'] . "\"" . (($_SESSION['SI_INSTALL_BRANCH'] == $branches[$i]['Name']) ? ("selected") : ("")) . ">"  . $branches[$i]['Name'] . " - " . $branches[$i]['Desc'] . "</option>";
    }



    showHeader(3);

    ?>

        <form action="install.php?step=4" method="post">
            <input type="hidden" name="save" value="1">

        <table border="1" style="width: 100%; border: 1px solid;">
        <tr>
            <th style="text-align: left;width: 30%;">Einstellung</th>
            <th style="text-align: left">Wert</th>
            <th style="text-align: left; width: 15%">Bemerkungen</th>
        </tr>
        <tr>
            <td>Schulname</td>
            <td><input type="text" name="schulname" value="<?php echo($schulname); ?>" placeholder="z.B. Staatliches Digitalgymnasium" class="form-control" required></td>
            <td></td>
        </tr>

            <tr>
                <td>Schulnummer<br /><i>Vierstellig mit führender Null</i></td>
                <td><input type="text" name="schulnummer" value="<?php echo($schulnummer); ?>" placeholder="0123" maxlength="4" minlength="4" class="form-control" required></td>
                <td></td>
            </tr>

            <tr>
                <td>URI zur Index.php</td>
                <td><input type="text" name="urltoindex" value="<?php echo($urlToIndex); ?>" placeholder="https://beispiel.de/index.php" class="form-control" required></td>
                <td>Beachten Sie bitte folgende Hinweise:
                    <ul>
                        <li>Wenn Sie SSL verwenden (Empfohlen!), dann geben Sie hier bitte die URL mit https beginnend ein!</li>
                        <li>Stellen Sie bitte am Server die automatische Umleitung auf SSL aus! Dies übernimmt die Software für Sie.</li>
                    </ul>
                </td>
            </tr>


            <tr>
                <td>Schlüssel für Cron Jobs<br /><i>Mindestens 20 Stellen, max 30 Stellen</i></td>
                <td><input type="text" name="cronkey" value="<?php echo($cronkey); ?>" placeholder="0123" maxlength="30" minlength="20" class="form-control" required></td>
                <td></td>
            </tr>

            <tr>
                <td>Api Key<br /><i>Mindestens 20 Stellen, max 30 Stellen. Derzeit noch nicht verwendet. Sollte aber schon gesetzt sein.</i></td>
                <td><input type="text" name="apikey" value="<?php echo($apiKey); ?>" placeholder="0123" maxlength="30" minlength="20" class="form-control" required></td>
                <td></td>
            </tr>


            <tr>
                <td>Stundenplan Software</td>
                <td><select name="stundenplan" class="form-control">
                        <option value="UNTIS" <?php if($stundenplanProgram == 'UNTIS'): ?>selected<?php endif; ?>>UNTIS</option>
                        <option value="SPM++" <?php if($stundenplanProgram == 'SPM++'): ?>selected<?php endif; ?>>SPM++ / VPM++</option>
                        <option value="TIME2007" <?php if($stundenplanProgram == 'TIME2007'): ?>selected<?php endif; ?>>TIME2007</option>
                        <option value="WILLI" <?php if($stundenplanProgram == 'WILLI'): ?>selected<?php endif; ?>>WILLI</option>
                    </select></td>
                <td></td>
            </tr>

            <tr>
                <td>Notenverwaltung aktivieren?</td>
                <td><select name="notenverwaltung" class="form-control">
                        <option value="0" <?php if($hasNotenverwaltung == '0'): ?>selected<?php endif; ?>>Nein</option>
                        <option value="1" <?php if($hasNotenverwaltung == '1'): ?>selected<?php endif; ?>>Ja</option>

                    </select></td>
                <td><b>Bitte beachten:</b> Die Notenverwaltung ist bisher nur für Gymnasien und die Klassenstunden 5 bis 9 einsetzbar.</b></td>
            </tr>

            <tr>
                <td>Modus für Elternbenutzer</td>
                <td><select name="elternbenutzer" class="form-control">
                        <option value="ASV_CODE" <?php if($elternbenutzermodus == 'ASV_CODE'): ?>selected<?php endif; ?>>Registrierungscodes</option>
                        <option value="ASV_MAIL" <?php if($elternbenutzermodus == 'ASV_MAIL'): ?>selected<?php endif; ?>>E-Mailadresse aus ASV Import verwenden</option>
                    </select></td>
                <td></td>
            </tr>

            <tr>
                <td>Name der Seite<br /><i>Zweiteilig. z.B. RSU intern</i></td>
                <td><input type="text" name="name1" value="<?php echo($name1); ?>" placeholder="RSU" maxlength="10" minlength="2" class="form-control" required> <input type="text" name="name2" value="<?php echo($name2); ?>" placeholder="intern" maxlength="10" minlength="2" class="form-control" required></td>
                <td></td>
            </tr>

            <tr>

                <td>Zu installierende Verson wählen

                    <?php if($_SESSION['SI_INSTALL_DBWRITTEN']) : ?> <strong>Keine Änderung mehr möglich, da die Datenbank bereits eingerichtet wurde. </strong><?php endif; ?>


                </td>
                <td><select name="branch" class="form-control"

                            <?php if($_SESSION['SI_INSTALL_DBWRITTEN']) : ?> disabled <?php endif; ?>

                    ><?php echo $branchesSelect ?></select></td>
                <td>Ausgewählte Version wird vom Updateserver heruntergeladen.</td>
            </tr>


        </table><br />

    <button type="submit" class="form-control"><i class="fa fa-save"></i> Speichern und zu Schritt 4</button>
        </form>

    <!-- <?php showButtonNextStep(4); ?> -->

    <?php


    showFooter();

}

function step4() {
    if($_REQUEST['save'] == 1) {
        $_SESSION['SI_INSTALL_SCHULNAME'] = $_REQUEST['schulname'];

        $_SESSION['SI_INSTALL_SCHULNUMMER'] = $_REQUEST['schulnummer'];

        $_SESSION['SI_INSTALL_INDEXPHP'] = $_REQUEST['urltoindex'];

        $_SESSION['SI_INSTALL_CRONKEY'] = $_REQUEST['cronkey'];
        $_SESSION['SI_INSTALL_APIKEY'] = $_REQUEST['apikey'];

        $_SESSION['SI_INSTALL_NAME1'] = $_REQUEST['name1'];

        $_SESSION['SI_INSTALL_NAME2'] = $_REQUEST['name2'];

        $_SESSION['SI_INSTALL_STUNDENPLAN'] = $_REQUEST['stundenplan'];

        $_SESSION['SI_INSTALL_NOTENVERWALTUNG'] = $_REQUEST['notenverwaltung'];

        $_SESSION['SI_INSTALL_ELTERNMODUS'] = $_REQUEST['elternbenutzer'];

        if(!$_SESSION['SI_INSTALL_DBWRITTEN']) {
            $_SESSION['SI_INSTALL_BRANCH'] = $_REQUEST['branch'];

            // Version aus dem Branch abrufen

            $version = file_get_contents(UPDATESERVER . "/api/branch/" . $_REQUEST['branch'] . "/version");

            if ($version === false) {
                die("Es kann leider keine aktuelle Version heruntergeladen werden: " . UPDATESERVER . "/api/branch/" . $_REQUEST['branch'] . "/version");
            } else {
                $version = json_decode($version, true);
                $url = UPDATESERVER . "/api/release/" . $version['id'] . "/download";

                $_SESSION['SI_INSTALL_INSTALL_VERSION_ID'] = $version['id'];

                mkdir("../install");

                file_put_contents("../install/install.zip", fopen($url, 'r'));

                $zip = new ZipArchive;
                if ($zip->open('../install/install.zip') === TRUE) {
                    $zip->extractTo('../install/');
                    $zip->close();
                } else {
                    die('Installationsdatei konnte nicht entpackt werden.');
                }
            }
        }

        if($_SESSION['SI_INSTALL_STEP'] < 4) $_SESSION['SI_INSTALL_STEP'] = 4;



        header("Location: install.php?step=4");
        exit(0);

    }

    showHeader(4);

    $dbWritten = $_SESSION['SI_INSTALL_DBWRITTEN'];
    
    if($dbWritten) {
        echo("Die Datenbank wurde bereits eingerichtet.");

        showButtonNextStep(5);
    }
    else {
        ?>


        <form action="install.php?step=5" method="post">
            <input type="hidden" name="save" value="1">

            <p>Bitte beachten Sie, dass die Datenbank bereits angelegt sein muss.</p>

            <table border="1" style="width: 100%; border: 1px solid;">
                <tr>
                    <th style="text-align: left;width: 30%;">Einstellung</th>
                    <th style="text-align: left">Wert</th>
                    <th style="text-align: left; width: 15%">Bemerkungen</th>
                </tr>
                <tr>
                    <td>Datenbank - Host</td>
                    <td><input type="text" name="dbhost" value="" placeholder="z.B. localhost"
                        class="form-control" required>
                    </td>
                    <td></td>
                </tr>

                <tr>
                    <td>Datenbank - Port</td>
                    <td><input type="text" name="dbport" value="3306" placeholder="z.B. 3306"
                        class="form-control" required>
                    </td>
                    <td></td>
                </tr>

                <tr>
                    <td>Datenbank - Benutzername</td>
                    <td><input type="text" name="dbuser" value="" placeholder="z.B. root"
                        class="form-control" required>
                    </td>
                    <td></td>
                </tr>

                <tr>
                    <td>Datenbank - Passwort</td>
                    <td><input type="text" name="dbpass" value="" placeholder="z.B. secret"
                        class="form-control">
                    </td>
                    <td></td>
                </tr>

                <tr>
                    <td>Datenbank - Datenbankname</td>
                    <td><input type="text" name="dbname" value="" placeholder="z.B. schuleinterndatenbank"
                               class="form-control" required>
                    </td>
                    <td></td>
                </tr>

            </table><br />

            <button type="submit" class="form-control"><i class="fa fa-save"></i> Speichern, Datenbank befüllen und zu Schritt 5</button>
        </form>

        <!-- <?php showButtonNextStep(4); ?> -->

        <?php
    }

    showFooter();
}

function step5() {
    if($_REQUEST['save'] == 1) {
        $_SESSION['SI_INSTALL_DBHOST'] = $_REQUEST['dbhost'];

        $_SESSION['SI_INSTALL_DBPORT'] = $_REQUEST['dbport'];

        $_SESSION['SI_INSTALL_DBUSER'] = $_REQUEST['dbuser'];

        $_SESSION['SI_INSTALL_DBPASS'] = $_REQUEST['dbpass'];

        $_SESSION['SI_INSTALL_DBDATABASE'] = $_REQUEST['dbname'];


        $mysqli = new mysqli(
            $_SESSION['SI_INSTALL_DBHOST'],
            $_SESSION['SI_INSTALL_DBUSER'],
            $_SESSION['SI_INSTALL_DBPASS'],
            $_SESSION['SI_INSTALL_DBDATABASE']
        );

        if ($mysqli->connect_errno) {
            echo "Es kann keie Verbindung zur Datenbank aufgebaut werden.: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
            echo("<br /><ahref=\"install.php?step=4\">Zurück zu Schritt 4</a>");
            exit(1);
        }

        $sqlInstallation = file_get_contents("../install/Datenbank/Installation/database.sql");


        $install = $mysqli->multi_query($sqlInstallation);

        if($install) $_SESSION['SI_INSTALL_DBWRITTEN'] = true;
    }

    showHeader(5);


    ?>

    Richten Sie bitte zwei Cron Jobs ein:


    <table border="1" style="width: 100%; border: 1px solid;">
    <tr>
        <th style="text-align: left;width: 80%;">URL</th>
        <th style="text-align: left">Intervall</th>
    </tr>
        <tr>
            <td><input type="text" value="<?php echo(str_replace("index.php", "cron.php", $_SESSION['SI_INSTALL_INDEXPHP']))?>?cronkey=<?php echo($_SESSION['SI_INSTALL_CRONKEY']); ?>" class="form-control"></td>
            <td>Alle 15 Minuten</td>
        </tr>
        <tr>
            <td><input type="text" value="<?php echo(str_replace("index.php", "cron.php", $_SESSION['SI_INSTALL_INDEXPHP']))?>?cronkey=<?php echo($_SESSION['SI_INSTALL_CRONKEY']); ?>&cronName=MailSender" class="form-control"></td>
            <td>Alle 3 Minuten</td>
        </tr>
    </table>


    <?php

    showButtonNextStep(6);

    showFooter();
}

function step6() {
    // Daten kopieren

    if($_REQUEST['copyDataDir'] > 0) {
        rename("../install/Upload/data", "../data");
        showCheckGreen();
        exit(0);
    }

    if($_REQUEST['copyProgramData'] > 0) {
        rename("../install/Upload/framework", "../framework");
        showCheckGreen();
        exit(0);

    }

    if($_REQUEST['copyWWW'] > 0) {
        recCopy("../install/Upload/www", ".");
        showCheckGreen();
        exit(0);

    }

    if($_REQUEST['makeConfigFile'] > 0) {

        $configFile = '<?php

class GlobalSettings {
    public $debugMode = false;
    public $schulnummer = "' . $_SESSION['SI_INSTALL_SCHULNUMMER'] . '";
    public $dbSettigns = array(
        "host" => "' . $_SESSION['SI_INSTALL_DBHOST'] . '",
        "port" => ' . $_SESSION['SI_INSTALL_DBPORT'] . ',
        "user" => "' . $_SESSION['SI_INSTALL_DBUSER'] . '",
        "password" => "' . $_SESSION['SI_INSTALL_DBPASS'] . '",
        "database" => "' . $_SESSION['SI_INSTALL_DBDATABASE'] . '"
    );

    public $urlToIndexPHP = "' . $_SESSION['SI_INSTALL_INDEXPHP'] . '";

    public $cronkey = "' . $_SESSION['SI_INSTALL_CRONKEY'] . '";

    public $apiKey = "' . $_SESSION['SI_INSTALL_APIKEY'] . '";
	
    public $siteNameHTMLDisplay = "<b>' . $_SESSION['SI_INSTALL_NAME1'] . '</b>' . $_SESSION['SI_INSTALL_NAME2'] . '";

    public $siteNameHTMLDisplayShort = "<b>' . $_SESSION['SI_INSTALL_NAME1'] . '</b>";

    public $siteNamePlain = "' . $_SESSION['SI_INSTALL_NAME1'] . '' . $_SESSION['SI_INSTALL_NAME2'] . '";


    public $schoolName = "' . $_SESSION['SI_INSTALL_SCHULNAME'] . '";


    public $schuelerUserMode = "ASV";

    public $lehrerUserMode = "ASV";

    public $elternUserMode = "' . $_SESSION['SI_INSTALL_ELTERNMODUS'] . '";


    public $stundenplanSoftware = "' . $_SESSION['SI_INSTALL_STUNDENPLAN'] . '";

    public $hasNotenverwaltung = ' . (($_SESSION['SI_INSTALL_NOTENVERWALTUNG'] > 0) ? 'true' : 'false') . ';


    public $office365AppCredentials = [
        "client_id" => "",
        "scope" => "https://graph.microsoft.com/.default",
        "client_secret" => "",
        "grant_type" => "client_credentials"
    ];

    public $ferienURL = "' . FERIENURL . '";
    
    public $updateServer = "' . UPDATESERVER . '";

    
}';


        file_put_contents("../data/config/config.php", $configFile);

        showCheckGreen();
        exit(0);
    }

    
    showHeader(6);

    ?>

    <p>Installiere Software</p>

    <table border="1" style="width: 100%; border: 1px solid;">
    <tr>
        <th style="text-align: left;width: 80%;">Programteil</th>
        <th style="text-align: left">Status</th>
    </tr>
    <tr>
        <td>
            Datenverzeichnis anlegen und befüllen
        </td>
        <td>
            <img src="install.php?step=6&copyDataDir=1" width="30">
        </td>
    </tr>
    <tr>
        <td>
            Programverzeichnis anlegen und befüllen
        </td>
        <td>
            <img src="install.php?step=6&copyProgramData=1" width="30">
        </td>
    </tr>

    <tr>
        <td>
            Webserver Basisverzeichnis befüllen (CSS, JS, Bilder)
        </td>
        <td>
            <img src="install.php?step=6&copyWWW=1" width="30">
        </td>
    </tr>

        <tr>
            <td>
                Konfigurationsdatei schreiben (data/config/config.php)
            </td>
            <td>
                <img src="install.php?step=6&makeConfigFile=1" width="30">
            </td>
        </tr>
    </table>
    <br />
    <?php

    showButtonNextStep(7);

    showFooter();


}

function step7() {
    if($_REQUEST['save']) {

        $mysqli = new mysqli(
            $_SESSION['SI_INSTALL_DBHOST'],
            $_SESSION['SI_INSTALL_DBUSER'],
            $_SESSION['SI_INSTALL_DBPASS'],
            $_SESSION['SI_INSTALL_DBDATABASE']
        );

        if ($mysqli->connect_errno) {
            echo "Es kann keie Verbindung zur Datenbank aufgebaut werden.: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;

            exit(1);
        }

        $password = crypt($_POST['password'],
            '$2a' .
            '$10' .
            '$' . substr(sha1(mt_rand()),0,22));

        $mysqli->query("INSERT INTO users (userName, userCachedPasswordHash, userCachedPasswordHashTime, userNetwork) values('portaladmin','" . $password . "',UNIX_TIMESTAMP(),'SCHULEINTERN')");
        $mysqli->query("INSERT INTO users_groups (userID, groupName) values('" . $mysqli->insert_id . "','Webportal_Administrator')");

        $mysqli->query("INSERT INTO settings (settingName, settingValue) values('current-release-id','" . $_SESSION['SI_INSTALL_INSTALL_VERSION_ID'] . "')");

        file_put_contents("../data/install.lock", time());


        header("Location: index.php");


        exit(0);
    }

    showHeader(7);

    ?>
    <p>Administratorzugang anlegen</p>



    <form action="install.php?step=7&save=1" method="post">


    <table border="1" style="width: 100%; border: 1px solid;">
    <tr>
        <th style="text-align: left;width:10%;">Programteil</th>
        <th style="text-align: left">Status</th>
    </tr>
    <tr>
        <td>Benutzername</td>
        <td><pre>portaladmin</pre></td>
    </tr>
    <tr>
        <td>Passwort</td>
        <td><input type="text" name="password" class="form-control" value="<?php echo(getRandomString(10)) ?>"></td>
    </tr>
    </table><br />

    <button type="submit" class="form-control"><i class="fa fa-user-plus"></i> Administrator Account anlegen und Setup abschließen</button>

    </form>

    <?php

    showFooter();

}

function showCheckGreen() {

    header("Content-type: image/svg+xml");

    echo('<?xml version="1.0" encoding="UTF-8"?>');

    ?>
    <svg xmlns="http://www.w3.org/2000/svg" width="600" height="600">
        <path d="m7.7,404.6c0,0 115.2,129.7 138.2,182.68l99,0c41.5-126.7 202.7-429.1 340.92-535.1c28.6-36.8-43.3-52-101.35-27.62-87.5,36.7-252.5,317.2-283.3,384.64-43.7,11.5-89.8-73.7-89.84-73.7z" fill="#181"/>
    </svg>
    <?php
}

function recCopy($source,$destination) {
    $dir = opendir($source);
    @mkdir($destination);
    while(( $file = readdir($dir)) ) {
        if (( $file != '.' ) && ( $file != '..' ) && $file != '.gitignore') {
            if ( is_dir($source . '/' . $file) ) {
                recCopy($source .'/'. $file, $destination .'/'. $file);
            }
            else {
                copy($source .'/'. $file,$destination .'/'. $file);
            }
        }
    }
    closedir($dir);
}

function showButtonNextStep($step) {

    $currentStep = $_SESSION['SI_INSTALL_STEP'];

    if($currentStep < $step) {
        $_SESSION['SI_INSTALL_STEP'] = $step-1;
    }


    echo("<a href=\"install.php?step=$step\" style=\"-moz-appearance: button;    -ms-appearance: button;    -o-appearance: button;    -webkit-appearance: button;    appearance: button;    text-decoration: none;color: #000;padding: 0.5em 0.5em; width: 100%\">&gt; Weiter zu Schritt $step</a>");
}


function checkSessionStep($step) {
    $currentStep = $_SESSION['SI_INSTALL_STEP'];

    $currentStep = intval($currentStep);
    if($currentStep == 0 || $currentStep == "") {
        $_SESSION['SI_INSTALL_STEP'] = 1;
        return true;
    }

    else return $currentStep >= ($step-1);
}

function checkStepAccess($step) {
    if(!checkSessionStep($step)) {
        showHeader($step);

        echo("Kein Zugriff auf Schritt $step. Bitte vorher Schritt " . ($step-1) . " ausführen.");
        
        showFooter();

        exit();
    }
}

function showHeader($step) {

    ?>

    <!doctype HTML>
    <html lang="de">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />

        <link href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">

        <style>*,
            ::after,
            ::before {
                box-sizing: border-box
            }

            body {
                max-width: 65em;
                margin: 0 auto;
                padding: 0 1em;
                font: normal 1em Arial, sans-serif;
                color: #8b0000;
                background-color: #fff
            }

            dl,
            ol,
            p,
            ul {
                color: #333
            }

            header {
                margin: 0;
                padding: 1em;
                background-color: #e7e8ee
                size: 20pt;
                vertical-align:middle;
                text-align: center;
            }

            header a {
                padding: .5em .5em .5em 3em;
                height: 3em;
                text-decoration: none;
                border: 1px solid transparent
            }

            header p {
                font-variant: small-caps;
                font-size: 2em
            }

            header span {
                font-weight: 700
            }

            .akzentfarbe1 {
                color: orange
            }

            .akzentfarbe2 {
                color: #8b0000
            }

            h1,
            h2 {
                color: currentColor
            }

            h3 {
                color: #999
            }

            a {
                color: currentColor;
                font-weight: 700
            }

            a:focus,
            a:hover {
                color: #888
            }

            a:focus,
            a:hover {
                background-color: gold
            }

            a.more {
                float: right
            }

            a.more:focus,
            a.more:hover {
                color: #000
            }

            a.more:focus::before,
            a.more:hover::before {
                color: #fff
            }

            h2.img {
                padding-left: 2.5em
            }

            a img,
            img {
                border: 0 none
            }

            article>h2 {
                clear: both
            }

            article>h2+p>img {
                width: 33%;
                float: left;
                margin: .15em 1.5em 1.5em 0
            }

            article>h2:nth-of-type(odd)+p>img {
                float: right;
                margin: .15em 0 1.5em 1.5em
            }

            dl {
                display: grid;
                grid-template-columns: 1fr 2fr;
                grid-gap: 1em 2em;
                margin-bottom: 3em
            }

            dl>* {
                margin: 0;
                padding: 0
            }

            dt {
                font-weight: 700
            }

            dt::after {
                content: ":"
            }

            .news {
                background: no-repeat top .5em right .5em #eee;
                padding: 0 1em 1em
            }

            .news h3 {
                color: #666
            }

            .news ul,
            aside ul {
                margin: 2em 0;
                padding: 0;
                color: orange;
                list-style-position: inside
            }

            .news li,
            aside li {
                font-weight: 700;
                padding: .5em 0
            }

            .news li span {
                color: #474747
            }

            aside ul {
                color: #333
            }

            nav ul {
                box-shadow: 0 .6em .3em 0 rgba(0, 0, 0, .75);
                text-align: center;
                margin: 0;
                padding: 0;
                list-style-type: none
            }

            nav a {
                background-color: #8b0000;
                color: #fff;
                text-decoration: none;
                display: inline-block;
                width: 95%;
                margin: .5em 0;
                padding: .5em 1em;
                border-radius: .5em
            }

            nav a[aria-current=page] {
                color: orange;
                font-weight: 700
            }

            nav a:focus,
            nav a:hover {
                background-color: gold;
                color: currentColor
            }

            nav a:focus::after,
            nav a:hover::after {
                color: transparent
            }

            footer {
                margin: 2em 0;
                display: flex;
                color: #989898
            }

            footer p,
            footer ul {
                flex: 1 1 100%
            }

            footer ul {
                padding-left: 0;
                margin-left: 0
            }

            footer li {
                list-style-type: none;
                display: inline-block;
                border-left: .2em solid #989898
            }

            footer li:first-child {
                border-left-color: transparent
            }

            footer a {
                color: #999
            }

            footer a:focus,
            footer a:hover {
                color: #333
            }

            footer li a {
                padding: 0 .5em 0 .7em
            }

            footer p {
                margin-top: 0;
                padding-top: 0;
                text-align: right
            }

            main {
                padding: 1em 0;
                margin: 2em 0
            }

            nav ul {
                display: flex;
                flex-direction: column
            }

            @media screen and (min-width:25em) {
                header {
                    height: 10em
                }
            }

            @media screen and (min-width:45em) {
                header {
                    background: #e7e8ee url(../img/header.png) no-repeat right bottom;
                    background-size: contain
                }
                nav ul {
                    flex-direction: row;
                    background-color: #8b0000
                }
                nav li {
                    margin: 0;
                    flex: 1 1 0%
                }
                main {
                    display: flex;
                    flex-flow: row wrap
                }
                main>* {
                    flex: 1 100%
                }
                section {
                    flex: 1 48%;
                    margin: 1%
                }
            }

            @media screen and (min-width:58em) {
                aside,
                section {
                    flex: 1 31%;
                    margin: 1%
                }
                article {
                    flex: 0 0 100%;
                    margin: 1%
                }
                article blockquote,
                article li {
                    max-width: 40em
                }
                #about {
                    flex: 1 30%;
                    margin: 1%;
                    background-color: #eee;
                    border: 1px solid #8b0000;
                    padding: 1em;
                    height: 22em
                }
                #impressum {
                    flex: 1 60%;
                    margin: 1%
                }
                aside p {
                    margin-bottom: 3em
                }
                aside p:last-child {
                    margin-bottom: 1.2em
                }
            }

            table {
                border-collapse: collapse;
            }

            table td {
                border: 1px solid;
                padding: 5px;
            }

            table th {
                border: 1px solid;
                padding: 5px;

            }

            .rot {
                color: #8b0000;
            }

            .gruen {
                color: limegreen;
            }

            .form-control {
                display:block;width:100%;padding:.375rem .75rem;font-size:1rem;line-height:1.5;color:#495057;background-color:#fff;background-clip:padding-box;border:1px solid #ced4da;border-radius:.25rem;transition:border-color .15s ease-in-out,box-shadow .15s ease-in-out;
            }

            .installHeader {
                font-size: 25pt;
            }
        </style>
        <title>Installation SchuleIntern</title>
    </head>

    <body>

    <header>
        <img width="80" src="data:image/png;base64,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"><br />
        <span class="installHeader"><i class="fa fa-download"></i> INSTALLATION</span>
        
    </header>

    <nav>
        <ul>
            <li><a <?php if($step == 1): ?> aria-current="page" <?php endif; ?> href="install.php?step=1">1<br></br>Start</a></li>
            <li><a  <?php if($step == 2): ?> aria-current="page" <?php endif; ?> href="install.php?step=2">2<br>Systemprüfung</a></li>
            <li><a  <?php if($step == 3): ?> aria-current="page" <?php endif; ?> href="install.php?step=3">3<br>Allgemeine Daten</a></li>
            <li><a  <?php if($step == 4): ?> aria-current="page" <?php endif; ?> href="install.php?step=4">4<br>Datenbank</a></li>
            <li><a  <?php if($step == 5): ?> aria-current="page" <?php endif; ?> href="install.php?step=5">5<br>Cron</a></li>
            <li><a  <?php if($step == 6): ?> aria-current="page" <?php endif; ?> href="install.php?step=6">6<br>Installation</a></li>
            <li><a  <?php if($step == 7): ?> aria-current="page" <?php endif; ?> href="install.php?step=7">7<br>Admin Account</a></li>

        </ul>
    </nav>

    <main>
        <article>
            <h1>Installation SchuleIntern</h1>

            <h2>Schritt <?php echo($step); ?></h2>
<?php

}

function showFooter() {
    ?>
        </article>


    </main>

    <footer>
        <ul>
            <li><a href="https://www.schule-intern.de" target="_blank">SchuleIntern Homepage</a></li>
            <li><a href="http://doku.schule-intern.de/display/ADMINHANDBUCH" target="_blank">Adminhandbuch</a></li>
            <li><a href="https://www.schule-intern.de/forum/" target="_blank">Support Forum</a></li>
            <li><a href="https://www.github.com/schuleintern" target="_blank">Github</a></li>

        </ul>
    </footer>

    </body>
</html><?php
}

function getRandomString($length) {
    return str_replace("+","S", str_replace("/", "L", substr(base64_encode(random_bytes(100)), 4, $length)));
}

