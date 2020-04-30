<?php
include_once("../methods_.php");
include_once mm::require_class("peers");
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
        //if withTorrents... DELETE FROM TORRENTS where ... username=...
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

    function getCountActiveTorrents()
    {
        $ret = $this->doSQL(sql::sqls['getActiveTorrents'], "COUNT(*)");
        return mysqli_fetch_array($ret);
    }

    function getCountRunningTorrents()
    {
        $ret = $this->doSQL(sql::sqls['getCountRunningTorrents'], "COUNT(*)");
        return mysqli_fetch_array($ret)[0];
    }

    function isAdmin($nick)
    {
        $r = $this->doSQL(sql::sqls['isAdmin'], $nick);
        $r = mysqli_fetch_assoc($r);
        if (strlen($r['username'])) return True;
        return False;
    }
}
