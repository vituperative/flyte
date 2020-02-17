<?php
$timeCook = 3600 * 3;
require_once "install_class.php";
require_once "../include/bittorrent.inc.php";
require_once "../include/page_header.inc.php";
print("<link rel=stylesheet href=installer.css type=text/css>");
$installer = new Installer();

if ($installer->checkPost("admin", $_POST)) {
    print("Adding Administrator account");
    $values = $installer->getPost("admin", $_POST);

    if ($installer->addAdmin($values['admin_username'], $values['admin_password'])) {
        print("header");
        header("Location: addedAdmin.php");
    }
    header("Location: addedAdmin.php?alreadyadded=1");
}

const stepNames = array(
    1 => "Check database connection",
    2 => "Setup Tracker configuration",
    3 => "Install database",
    4 => "Install Tracker configuration"
);

if (!isset($_COOKIE['step'])) {
    print("deleted? - " . $_COOKIE['step']);
    //setStep(1);
    setcookie('step', 1, time() + $timeCook);
    header("Refresh: 0");
}

switch ($_COOKIE['step']) {
    case 1:
        if ($installer->checkPost("sql", $_POST)) {
            $err = $installer->conn2DB_arr($_POST);
            if ($err !== True) {
                printf(
                    "<div class='error'>Cant connect to DB! Err: %s</div>",
                    $err
                );
            } else {
                setcookie('inst_sql', serialize($installer->getPost("sql", $_POST)), time() + $timeCook);
                setcookie('step', 2, time() + $timeCook);
                header("Refresh: 0");
            }
        }
        break;
    case 2:
        if ($installer->checkPost("tracker_info", $_POST) && isset($_COOKIE['inst_sql'])) {
            //TODO check if that information is good.
            setcookie('inst_info', serialize($installer->getPost("tracker_info", $_POST)), time() + $timeCook);
            setcookie('step', 3, time() + $timeCook);
            header("Refresh: 0");
        }
        break;
    case 3:

    case 4:
        if (!isset($_COOKIE['inst_sql']) || !isset($_COOKIE['inst_info']))
            print("broken cookies... try delete all cookies for this website");
        if (!$installer->checkConfPathWritable()) {
            printf(
                "<center><div class='step'>Dont writable file %s</div></center>",
                Installer::defPathToConfig
            ); // to own method maybe? how to use va list like C in php?
        } else {
            if (isset($_POST['continue_conf'])) {
                $all = array(
                    "sql" => unserialize($_COOKIE['inst_sql']),
                    "inst_info" => unserialize($_COOKIE['inst_info'])
                );
                $elements = array();
                foreach ($all as $arr)
                    foreach ($arr as $key => $value)
                        $elements[$key] = $value;
                if (!$installer->installconf($elements)) //todo elements get
                    printf(
                        "<center><div class='step'>Can't write conf file %s</div></center>",
                        Installer::defPathToConfig
                    );
                else {
                    setcookie('step', 4, time() + $timeCook);
                    header("Refresh: 0");
                }
            }
            if (isset($_POST['continue_sql'])) {
                $installer->conn2DB_arr(unserialize($_COOKIE['inst_sql']));
                if (!$installer->installDB())
                    printf("<center><div class='step'>Can't install DB </div></center>");
                else {
                    setcookie('step', 5, time() + $timeCook);
                    header("Refresh: 0");
                }
            }
            break;
        }
} //switch end
?>

    <table id=wrapper>
        <tr>
            <td>
                <div id=installer>
                    <form action='index.php' method="POST">
                        <?php
                        if (strpos($_COOKIE['step'], "5") !== false) {
                            printf("<center><div class='step'>Installation Complete!</div> </center>", $_COOKIE['step'], stepNames[$_COOKIE['step']]);
                        } else {
                            printf("<center><div class='step'>Step: %d  - %s </div> </center>", $_COOKIE['step'], stepNames[$_COOKIE['step']]);
                        }
                        switch ($_COOKIE['step']) {
                            case 1:
                                print($installer->initHTML("sql"));
                                break;
                            case 2:
                                if (!isset($_COOKIE['inst_sql'])) {
                                    print("<div class='error'>You haven't supplied correct MySQL database information; go back and try again!</div>");
                                }
                                print($installer->initHTML("tracker_info"));
                                break;
                            case 3:
                                print("Continue?");
                                print("<input type=hidden name='continue_sql' value='1'/>");
                                //$_POST['continue_conf']
                                break;
                            case 4:
                                print("Continue?");
                                print("<input type=hidden name='continue_conf' value='1'/>");
                                //$_POST['continue_conf']
                                break;
                            case 5:
                                print("<p>You should move or delete the <code>install</code> folder after you have verified your tracker is working.</p>");
                                print("<p>To change various tracker settings, edit <code>include/secrets.ini.php</code></p>");
                                break;
                            default:
                                print("are u crazy?");
                                break;
                        }
                        //print($installer->initAllTables());
                        print("<div id=dostuff>");
                        if ($_COOKIE['step'] == 1)
                            print('<input type=submit value="Start Installation"/>');
                        elseif ($_COOKIE['step'] < 5)
                            print('<input type=submit value="Next"/>');
                        else
                            print('<a href=addAdmin.php class=button>Create Administrator Account</a>&nbsp;<a href=../ class=button>View ' . $tracker_title . '</a>');
                        print("</div>");
                        ?>

                </form>
                </div>
            </td>
        </tr>
    </table>
<?php stdfoot(); ?>