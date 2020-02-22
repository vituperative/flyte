<?php
//session_start(); // some fix... and dont works for us...
require_once "../include/bittorrent.inc.php";

class Installer
{

    const defPathToConfig = "../include/secrets.inc.php";
    function checkConfPathWritable()
    {
        return is_writable(self::defPathToConfig);
    }
    const install_elements =
    array(
        "sql" => array("mysql_host", "mysql_user", "mysql_pass", "mysql_db"),
        "tracker_info" => array("tracker_url_name", "tracker_url_key", "torrent_dir", "tracker_title", "tracker_path",
		"contact_url","contact"),
        "admin" => array("admin_username", "admin_password")
    );
	//TODO: construct that... in method
    const need4conf = array(
        "mysql_host", "mysql_user", "mysql_pass", "mysql_db",
        "tracker_url_name", "tracker_url_key", "torrent_dir", "tracker_title", "tracker_path",
		"contact_url","contact",
        "tracker_url_name", "tracker_url_key"
    );
    const placeholders =
    array(
        "mysql_host" =>       "Host of your mysql database (usually localhost)",
        "mysql_pass" =>       "Password to access the database",
        "mysql_user" =>       "Username to access the database",
        "mysql_db" =>         "The name of the database you will use",
        "tracker_title" =>    "Name of your tracker",
        "tracker_url_name" => "Complete human url to tracker location. DO NOT trail with a",
        "tracker_url_key" =>  "b32 url e.g. http://nfrjvknwcw47itotkzmk6mdlxmxfxsxhbhlr5ozhlsuavcogv4hq.b32.i2p ",
        "torrent_dir" =>      "Directory of torrent files, will be like to 777 mode as example",
        "admin_username" =>   "Admin username",
        "admin_password" =>   "Password of admin",
	"contact_url" => "URL to contact with admin maybe mailto:admintorrents@mail.i2p",
	"tracker_path" => "/ or /Torrents or /TF/torrfredom",
	"contact" => "Contacts"
    );
    //need4conf from
    function conn2DB_arr($elemets)
    {
        if (!function_exists('mysqli_connect')) return "Not installed mysqli";
        $mysql_host = $elemets['mysql_host'];
        $mysql_user = $elemets['mysql_user'];
        $mysql_pass = $elemets['mysql_pass'];
        $mysql_db = $elemets['mysql_db'];
        $this->link = new mysqli("$mysql_host", "$mysql_user", "$mysql_pass", "$mysql_db");
        if ($this->link->connect_errno) {
            //var_dump($elemets);
            //print('err con');
            //$this->link->query("SHOW TABLES");
            return $this->link->connect_error;
        }
        return True;
    } //conn2DB_arr
    function conn2DB($host, $user, $pass, $db)
    {
        $elements = array(
            "mysql_host" => $host,
            "mysql_user" => $user,
            "mysql_pass" => $pass,
            "mysql_db" => $db
        );
        return $this->conn2DB_arr($elements);
    }
    function checkPost($what, $where)
    { // some shitcode
        if (!isset(self::install_elements[$what])) die("element $n not found");
        foreach (self::install_elements[$what] as $key) {
            if (!isset($where[$key])) return False;
        }
        return True;
    }
    function getPost($what, $where)
    { //too
        $return = array();
        if (!isset(self::install_elements[$what])) die("element $n not found");
        foreach (self::install_elements[$what] as $key) {
            $return[$key] = $where[$key];
        }
        return $return;
    }

    function initHTML($elements)
    {
        $need = func_get_args();
        $return = "";
        foreach ($need as $n) {
            if (!isset(self::install_elements[$n])) die("element $n not found");
            foreach (self::install_elements[$n] as $element) {
                //print $element;
                $type = "text";
                if (strstr($element, "pass")) {
                    $type = "password";
                    $return .= sprintf(
                        "\n<label><span>%s</span> <input autocomplete=\"new-password\" type='%s' name='%s' placeholder='%s' required></label><hr>\n",
                        str_replace("_", " ", $element),
                        $type,
                        $element,
                        self::placeholders[$element]
                    );
                } else
                    //$_SERVER['SERVER_NAME'];
                    if ($element == "tracker_url_name")
                        $return .= sprintf(
                            "\n<label><span>%s</span> <input type='%s' name='%s' placeholder='%s' value='%s' required></label><hr>\n",
                            str_replace("_", " ", $element),
                            $type,
                            $element,
                            self::placeholders[$element],
                            $_SERVER['SERVER_NAME']
                        );
                    else // tododoto fix that shitstylecoding to array with cood placeholder
                        $return .= sprintf(
                            "\n<label><span>%s</span> <input type='%s' name='%s' placeholder='%s' required></label><hr>\n",
                            str_replace("_", " ", $element),
                            $type,
                            $element,
                            self::placeholders[$element]
                        );
            }
        } //foreach
        return $return;
    } //initTables()
    function installconf($elements)
    {
	$need4conf = array();
	foreach(self::install_elements as $namearr=>$arr){
		if($namearr != "admin") // in not array of ignores;
		foreach($arr as $name=>$v)
			$need4conf[$name]=$v;
	}
	//var_dump($need4conf); // todo check it; if good delete self::need4conf and change there to $need4conf;

        $config_raw = "<?php \r\n";
        foreach (self::need4conf as $need) {
            if (!isset($elements[$need])) {
                var_dump($elements);
                die("need " . $need . " for installing, <hr><a href='index.php'>START INSTALLATION</a> ");
            }
            $config_raw .= '$' . $need . '="' . $elements[$need] . '";' . "\r\n";
        }
        $configFile = fopen(self::defPathToConfig, "w") or die("Can't open " . $defPathToConfig . " to write config file");
        fwrite($configFile, $config_raw) or die("cant write to config, check permission on: " . $defPathToConfig);
        fclose($configFile);
        return True;
    }
    function initAllTables()
    {
        return $this->initHTML("sql", "tracker_info");
    }
    function installDB($file = "db.sql")
    {
        if (!isset($this->link)) return False;
        $sql = file($file);
        $templine = '';
        $errs = 0;
        foreach ($sql as $query) {
            if (substr($query, 0, 2) == '--' || $query == '') {
                continue;
            }

            $templine .= $query;
            //echo "do"; //https://stackoverflow.com/questions/19751354/how-to-import-sql-file-in-mysql-database-using-php thx author; because source db.sql sdont works
            if (substr(trim($query), -1, 1) == ';') {
                // Perform the query
                if (!$this->link->query($templine)) {
                    print('Error performing query \'<strong>' . $templine . '\': ' . $link->error . '<br><br>');
                    $errs += 1;
                }
                // Reset temp variable to empty
                //echo ("I did ".$templine);
                $templine = '';
            }
        }
        if ($errs == 0) return True;
    } //installDB()
    function ConnToDBByConfig()
    {
        require_once "../include/secrets.inc.php";
        global $mysql_host, $mysql_user, $mysql_pass, $mysql_db;

        if (!$this->conn2DB($mysql_host, $mysql_user, $mysql_pass, $mysql_db)) print("cant conn to db");
    }
    function addAdmin($username, $password)
    {
        if (strlen($password) > 64)
            die("Sorry, password is too long (max is 63 chars)");
        if (!preg_match('/^[a-z][\w.-]*$/is', $username) || strlen($username) > 40)
            die("Invalid username. Must not be more than 40 characters long and no weird characters");
        if (!isset($this->link)) {
		$this->ConnToDBByConfig();
	}
        print("Connected");
        $secret = mksecret();
        $hashpass = hash("sha256", $secret . $password . $secret); //JES NEED TO CHANGE sha3 to sha3-224 maybe 224.....
        //die("INSERT INTO users (username, password, secret, status, added,admin) VALUES( '$username', '$hashpass', '$secret', 'confirmed'" . ", NOW(), 'yes')");
        $ret = mysqli_query($this->link,
            "INSERT INTO users (username, password, secret, status, added,admin) VALUES( '$username', '$hashpass', '$secret', 'confirmed'" . ", NOW(), 'yes')"
        );
	//die($this->link->error);
        if ( strlen( mysqli_error($this->link) ) > 5 ) return (  mysqli_error($this->link) );
        return True;
    }
}
