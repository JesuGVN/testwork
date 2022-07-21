<?php

////////  Старый Код  //////////

namespace vBulletin\Search;

class Search {
    public static function doSearch(): void {

        if ($_REQUEST['searchid']){            // Условия можно укоротить 
            $_REQUEST['do'] = 'showresults';          
        }elseif(!empty($_REQUEST['q'])){  
            $_REQUEST['do'] = 'process';
            $_REQUEST['query'] = &$_REQUEST['q']; 
        }

        $db = new \PDO('mysql:dbname=vbforum;host=127.0.0.1', 'forum', '123456'); // Зачем так подключать ? Можно же сделать конфигурацию + Лишний знак в названии класса

        if ($_REQUEST['do'] == 'process') {
            $sth = $db->prepare('SELECT * FROM vb_post WHERE  text like ?'); 
            $sth->execute(array($_REQUEST['query']));
            $result = $sth->fetchAll(); // Можно укоротить, 

            self::render_search_results($result); 

            $file = fopen('/var/www/search_log.txt', 'a+'); /// Тут можно укоротить, так как с переменной file 
            fwrite($file, $_REQUEST['query'] . "\n");       /// произволится всего 1 действие
        } elseif ($_REQUEST['do'] == 'showresults'){
            $sth = $db->prepare('SELECT * FROM vb_searchresult WHERE searchid = ?');
            $sth->execute(array($_REQUEST['searchid']));
            $result = $sth->fetchAll();

            self::render_search_results($result);
        }
        else {
            echo "<h2>Search in forum</h2><form><input name='q'></form>";
        }
    }

    public static function render_search_results($result){
        global $render;

        foreach($result as $row){
            if ($row['forumid'] != 5){
                $render->render_searh_result($row);
            }
        }
    }
}

//////////////////////////////////////////




//////////  Новый Код //////////

namespace vBulletin\Search;

$db_name = 'vbforum';
$db_host = '127.0.0.1';
$db_user = 'forum';
$db_pass = '123456';

class Search {
    public static function doSearch() {

        $searchID     =  $_REQUEST['searchid'];
        $searchQuery  =  $_REQUEST['q'];

        $action       =  !empty($searchID) ? 'showresults' : (!empty($searchQuery) ? 'process' : false);
        $query        =  $action == 'process' ? $searchQuery : false;

        $db           =  new \PDO('mysql:dbname=$db_name;host=$db_host', $db_user, $db_pass);

        if ($action == 'process') {
            $sth = $db->prepare('SELECT * FROM vb_post WHERE text like ?');
            $sth->execute(array($searchQuery));
            $result = $sth->fetchAll();

            fwrite(fopen('/var/www/search_log.txt', 'a+'), $searchQuery . "\n");
            return self::render_search_results($result);

        } else if ($action == 'showresults'){
            $sth = $db->prepare('SELECT * FROM vb_searchresult WHERE searchid = ?');
            $sth->execute(array($searchID));
            $result = $sth->fetchAll();

            return self::render_search_results($result);
        }
        
        return echo "<h2>Search in forum</h2><form><input name='q'></form>"; 
    }

    public static function render_search_results($result){
        global $render;

        foreach($result as $row){
            if ($row['forumid'] != 5){
                $render->render_searh_result($row);
            }
        }
    }
}