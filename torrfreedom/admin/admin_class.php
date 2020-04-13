<?php
if (file_exists("../include/bittorrent.inc.php")) require_once("../include/bittorrent.inc.php");
else require_once("include/bittorrent.inc.php");

class sql
{

    const sqls = array(
        "getAllUsers" => "SELECT users.username, users.added, users.last_login, users.last_access,
      (SELECT COUNT(*) FROM torrents WHERE torrents.owner = users.id) AS cntt,
      (SELECT COUNT(*) FROM comments WHERE comments.user = users.id) AS cntc, status
      FROM users",
        "addUser" => "INSERT INTO users (username, password, secret, status, added,admin) VALUES( '%s', '%s', '%s', '%s'" . ", NOW(), '%s')",
        "delUser" => "DELETE FROM users WHERE username='%s'",

        "getTorrentByID" => "SELECT * FROM torrents WHERE id = '%d'",
        "delTorrentByID" => "DELETE FROM torrents WHERE id= '%d'",
        "delTorrentByX" => "DELETE FROM torrents WHERE %s= '%s'",
        "delTorrentsByUserID" => "DELETE FROM torrents WHERE owner= '%s'",
        "getActiveTorrents" => "SELECT %s FROM torrents WHERE torrents.banned='no' AND (torrents.leechers+torrents.seeders)>1 AND torrents.visible='yes'",
        "getTorrentsHits" => "SELECT COUNT(torrents.hits) FROM torrents %s",
        "getTorrentsViews" => "SELECT SUM(torrents.views) FROM torrents %s",
        "getTorrentsCompleted" => "SELECT SUM(torrents.times_completed) FROM torrents %s",
        "getTorrentsByUserID" => "SELECT * FROM torrents WHERE owner= '%s' LIMIT %d OFFSET %d",
        "getTorrentTop" => "SELECT COUNT(torrents.hits) as hits, COUNT(torrents.times_completed) AS downloadtimes, COUNT(torrents.leechers) AS leechers, COUNT(torrents.seeders) AS seeders FROM torrents order by hits LIMIT '%d'",
        "getUserByID" => "SELECT * FROM users WHERE id='%d'",
        "getUserByName" => "SELECT * FROM users WHERE username='%s'",

        "delCommentsWhereIS" => "DELETE FROM comments WHERE %s='%s'",
        "changeValueOfTorrentByID" => "UPDATE torrents SET `%s`='%s' WHERE `%s`='%s'", //Update torrents set what is WHERE a=b
        "getCountOfTB" => "SELECT COUNT(*) FROM %s",
        "getCountOfTBWhere" => "SELECT COUNT(*) FROM %s WHERE %s='%s'",
        "getCountOfTBWhereMoreFunc" => "SELECT COUNT(*) FROM %s WHERE %s > %s",
        "isAdmin" => "SELECT * FROM users WHERE username='%s' AND admin='yes';",
        "getAllTorrents" => "SELECT * FROM torrents LIMIT %d OFFSET %d",
        "getNameOfCategoryByID" => "SELECT * FROM categories WHERE id='%d'"
    );
    function doSQL($sprintf, ...$arguments)
    {
        $string = vsprintf($sprintf, $arguments);
        //printf("Debug info: %s\n\r<br>", $string);
        return $result = mysqli_query($this->con, $string);
    }
    function getSQLCon()
    {
        return $this->con;
    }
    function __construct()
    {
        dbconn(0);
        $this->con = $GLOBALS["___mysqli_ston"];
    }
    function getLastSQLError()
    {
        return mysqli_error($this->con);
    }
    function getCountOfTB($table)
    {
        $r = $this->doSQL(sql::sqls['getCountOfTB'], $table);
        return mysqli_fetch_array($r)[0];
    }
    function getCountOfTBWhere($table, $a, $b)
    {
        $r = $this->doSQL(sql::sqls['getCountOfTBWhere'], $table, $a, $b);
        return mysqli_fetch_array($r)[0];
    }
    function getCountOfTBWhereMoreFunc($table, $a, $b)
    {
        $r = $this->doSQL(sql::sqls['getCountOfTBWhereMoreFunc'], $table, $a, $b);
        return mysqli_fetch_array($r)[0];
    }
}

class comments extends sql
{
    const commentfields = array(
        "id", "user", "torrent", "added", "text", "ori_text"
    );

    function countComments()
    {
        return $this->getCountOfTB("comments");
    }

    function delCommentIsWhere($is, $WHERE = "id")
    {
        $allowed = false;
        foreach (self::commentfields as $allow_fields) {
            if ($WHERE == $allow_fields) {
                $allowed = true;
                break;
            }
        }
        if (!$allowed) return false;
        return $this->doSQL(sql::sqls['delCommentsWhereIS'], $WHERE, $is);
    }
    function delCommentByUserID($id)
    {
        return $this->delCommentIsWhere($id, "user");
    }
}

class torrents extends comments
{
    const torrentfields = array(
        //is much...
    );
    //		"changeValueOfTorrentByID"=>"UPDATE torrents SET %s='%s' WHERE '%s'='%s'" //Update torrents set what is WHERE a=b
    function changeValueOfTorrentByID($what, $value, $WHERE_a, $WHERE_b)
    {
        return $this->doSQL(sql::sqls['changeValueOfTorrentByID'], $what, $value, $WHERE_a, $WHERE_b);
    }
    function setBanTorrentByID($is_banned, $id)
    {
        if ($is_banned === True) $is_banned = "yes";
        elseif ($is_banned === False) $is_banned = "no";

        $this->changeValueOfTorrentByID("banned",  "$is_banned", "id", "$id");
    }
    function setVissbleTorrentByID($is_vissible, $id)
    {
        if ($is_vissible === True) $is_vissible = "yes";
        elseif ($is_vissible === False) $is_vissible = "no";

        $this->changeValueOfTorrentByID("visible",  "$is_vissible", "id", "$id");
    }
    function getTorrentByID($id)
    {
        //$res = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT * FROM torrents WHERE id = $id");
        $ret = $this->doSQL(sql::sqls['getTorrentByID'], $id);
        return mysqli_fetch_array($ret);
    }
    function getActiveTorrents()
    {
        $ret = $this->doSQL(sql::sqls['getActiveTorrents'], "*");
        return mysqli_fetch_array($ret);
    }
    function getCountActiveTorrents()
    {
        $ret = $this->doSQL(sql::sqls['getActiveTorrents'], "COUNT(*)");
        return mysqli_fetch_array($ret)[0];
    }
    function getTorrentsHits()
    {
        $ret = $this->doSQL(sql::sqls['getTorrentsHits'], "");
        return mysqli_fetch_array($ret);
    }
    function getTorrentsViews()
    {
        $ret = $this->doSQL(sql::sqls['getTorrentsViews'], "");
        return mysqli_fetch_array($ret)[0];
    }
    function getTorrentTop($limit = 10)
    {
        $ret = $this->doSQL(sql::sqls['getTorrentTop'], $limit);
        return mysqli_fetch_array($ret);
    }
    function getTorrentsCompleted()
    {
        $ret = $this->doSQL(sql::sqls['getTorrentsCompleted'], "");
        return mysqli_fetch_array($ret)[0];
    }
    //"delTorrentByX"=>"DELETE FROM torrents WHERE %s= '%s'"
    function delTorrentByX($x, $value)
    {
        $ret1 = $this->doSQL(sql::sqls['delTorrentByX'], $x, $value);
    }

    function delTorrentsByUserID($id, $withComments = True)
    {
        $ret0 = True;
        $ret1 = $this->doSQL(sql::sqls['delTorrentsByUserID'], $id);
        if ($withComments) $ret0 = $this->delCommentIsWhere("user", $id);
        if (!($ret1 && $ret0)) return $this->getLastSQLError();
        return True;
    }

    function delTorrentByID($id, $withComments = True)
    { //maybeto delTorrentByX? or no...
        $ret0 = True;

        $ret1 = $this->doSQL(sql::sqls['delTorrentByID'], $id);
        if ($withComments) $ret0 = $this->delCommentIsWhere("torrent", $id);

        if (!($ret1 && $ret0)) return $this->getLastSQLError();
        return True;
    }
    function countTorrents()
    {
        return $this->getCountOfTB("torrents");
    }
    function getAllTorrents($offset = 0, $limit = 60)
    {
        return $this->doSQL(sql::sqls['getAllTorrents'], $limit, $offset);
    }
    function getTorrentsByUserID($id, $offset = 0, $limit = 60)
    {
        return $this->doSQL(sql::sqls['getTorrentsByUserID'], $id, $limit, $offset);
    }
    function getTorrentsByUserNick($nick, $offset = 0, $limit = 60)
    {

        $user = $this->getUserByName($nick);
        //var_dump($user);
        return $this->getTorrentsByUserID($user['id'], $offset, $limit);
    }
    function countOfTorrentsByUserID($id)
    {
        return $this->getCountOfTBWhere("torrents", "owner", $id);
    }
    function countOfTorrentsByUserNick($id)
    {
        $user = $this->getUserByName($id);
        return $this->countOfTorrentsByUserID($user['id']);
    }
}

class categories extends torrents
{
    function countCategories()
    {
        return $this->getCountOfTB("categories");
    }
    function getNameOfCategoryByID($id)
    {
        //getNameOfCategoryByID
        $category = $this->doSQL(sql::sqls['getNameOfCategoryByID'], $id);
        //$category=mysqli_fetch_array($category)['name'];
        return mysqli_fetch_array($category)['name'];
    }
}

class peers extends categories
{
    function countPeers()
    {
        return $this->getCountOfTB("peers");
    }
    function countOfSeeders()
    {
        return $this->getCountOfTBWhere("peers", "seeder", "yes");
    }
    function countOfLeech()
    {
        return $this->getCountOfTBWhere("peers", "seeder", "no");
    }
}

class users extends peers
{
    function addUser($username, $password, $admin = 'no', $confirmed = 1)
    {
        $confirmed = $confirmed ? "confirmed" : "pending";
        if (strlen($password) > 64)
            return ("Sorry, password is too long (max is 63 chars)");
        if (!preg_match('/^[a-z][\w.-]*$/is', $username) || strlen($username) > 40)
            return ("Invalid username. Must not be more than 40 characters long and no weird characters");
        //if (!isset($this->link)) $this->ConnToDBByConfig();
        //print("Connected");
        $secret = mksecret();
        $hashpass = hash("sha256", $secret . $password . $secret); //JES NEED TO CHANGE sha3 to sha3-224 maybe 224.....

        $ret = $this->doSQL(sql::sqls['addUser'], $username, $hashpass, $secret, $confirmed, $admin);
        if ($ret !== True) return ($this->getLastSQLError() . "(maybe user exist already?)");
        //print("return true");
        return True;
    }
    function getUserByName($username)
    {
        //print($username);
        $res = $this->doSQL(sql::sqls['getUserByName'], $username);
        $user = mysqli_fetch_array($res);
        return $user;
    }

    function getUserByID($id)
    {
        $res = $this->doSQL(sql::sqls['getUserByID'], $id);
        $user = mysqli_fetch_array($res);
        return $user;
    }
    function blackListToUsername($username, $lengthrand = 32)
    {
        //die("do black list");
        $rand = random_bytes($lengthrand);
        $this->addUser($username, $rand, 'no', false);
    }
    function delUserByUsername($username, $withTorrents = True, $withComments = True)
    {
        $ret0 = True;
        //if withTorrents... DELETE FROM TORRENTS WHERE ... username=...
        //also with comments
        $user = $this->getUserByName($username);
        //var_dump($user);
        //exit(0);
        if ($withTorrents) {
            //print("DEL TORRENTS>". $withComments);
            //exit(0);
            $ret0 = $this->delTorrentsByUserID($user['id'], $withComments);
            //print("deleted?");
            //exit(0);
        } elseif ($withComments && !$withTorrents) {
            $ret0 = $this->delCommentByUserID($user['id']);
        }
        $ret1 = True;
        $ret1 = $this->doSQL(sql::sqls['delUser'], $username);
        if (!($ret1 && $ret0)) return $this->getLastSQLError();
        return True;
    }
    function getAllUsers()
    {
        return $this->doSQL(sql::sqls['getAllUsers']);
    }
    function countUsers()
    {
        return $this->getCountOfTB("users");
    }
    function countAccess()
    {
        return $this->countDayAccess() . "/" . $this->countWeekAccess();
    }
    function countDayAccess()
    {
        return $this->getCountOfTBWhereMoreFunc("users", "last_access", "DATE_SUB(NOW(), INTERVAL 1 DAY)");
    }
    function countWeekAccess()
    {
        return $this->getCountOfTBWhereMoreFunc("users", "last_access", "DATE_SUB(NOW(), INTERVAL 1 WEEK)");
    }
    function isAdmin($nick)
    {
        $r = $this->doSQL(sql::sqls['isAdmin'], $nick);
        $r = mysqli_fetch_assoc($r);
        if (strlen($r['username'])) return True;
        return False;
    }
}

class admin extends users
{
    const DEBUG = FALSE;
    function getServInfo(
        $indicesServer = array(
            'SERVER_NAME',
            'SERVER_ADDR',
            'SERVER_PORT',
            'SERVER_SIGNATURE',
            'SERVER_SOFTWARE',
            'SERVER_PROTOCOL',
        )
    ) {
        $returns = array();
        foreach ($indicesServer as $info) {
            //print("INFO:".$_SERVER[$info]);
            if (isset($_SERVER[$info])) $returns[$info] = $_SERVER[$info];
        }
        return $returns;
    }

    function __construct($moveIfNotAdmin = True, $page = '../index.php')
    {
        sql::__construct();
        if ($moveIfNotAdmin) {
            $is_admin = $this->checkAdmin();
            if (!$is_admin) {

                if (!self::DEBUG) {
                    header("Location: ../index.php");
                    exit(0);
                } else {
                }
            }
        }
        stdhead("Admin page");
        //include_once "../include/page_header.inc.php";
    }

    function checkAdmin()
    {
        global $CURUSER;
        $this->isAdmin = (isset($CURUSER) && $CURUSER["admin"] == "yes");
        return $this->isAdmin;
    }
};
