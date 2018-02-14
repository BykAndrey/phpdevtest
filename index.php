<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/* include dependings */
require_once('./config.php');
require_once('./mysql.class.php');

/* time of working script*/
set_time_limit(0);

$db = new mysql(DB_HOST, '', DB_USER, DB_PASSWORD, DB_NAME); // connecting to database


/**/
if(isset($_REQUEST['ajax'])){
   // header('Content-Type: text/html; charset=utf-8');
    header('Content-Type: application/json; charset=utf-8');
    if( $_REQUEST['ajax']=="create"){
       

        SmsSender::createNew($_REQUEST['TEXT']);
        echo true;

        exit;
    }
    if( $_REQUEST['ajax']=="getList"){
        $page=1;
        if(isset($_REQUEST['page'])){
            $page=intval($_REQUEST['page']);
        }
        $page--;
        $size=5;
        $max_page=ceil(SQLCountRow("sms_list_tz")/$size);
        echo json_encode(['list'=>SQLSelect("sms_list_tz",$size*$page,$size),'max_page'=>$max_page]);
        exit;
    }
    if( $_REQUEST['ajax']=="dropMessage"){
        
      
        echo SQLDropRow("sms_list_tz",intval($_REQUEST['id']));
        
        exit;
    }
}




/** */

/* defind encoding of page*/
header('Content-Type: text/html; charset=utf-8');
/* class of sender sms*/
class SmsSender
{
    /** table toward which we  will be connecting */
    const TABLE = 'sms_list_tz';


    /** creating new message */
    static function createNew($text)
    {
        /** cleaning message */
        $insert = array('TEXT' => self::clearText($text));

        /** insert message to database  */
        SQLInsert(self::TABLE, $insert);
    }

    /**  */
    static function clearText($text)
    {
        /** splitting  message on parts */
        $d = explode('©', $text);
        $text = $d[0];
        
        /* remove line break from message and replace on gaps*/
        $text = str_replace(array("\r\n"), "", $text);
        /* remove gaps in start and in end of message */
        $text = trim($text);

        return $text;
    }

    static function notify()
    {
        /* select from DB random line which was not send*/
        $sql = 'SELECT * FROM `' . self::TABLE . '` WHERE IS_SENDED = 0 ORDER BY RAND()';
        $exists = SQLSelectOne($sql);
        /* if not exists then send message */
        if (!$exists) {
            self::sendEmail('SMS NOT FOUND');
            return false;
        }

        /**
         * if exists then send text send text and email
         */
        self::sendSms($exists['TEXT']);
        self::sendEmail($exists['TEXT']);
            /*
            defind IS_SENDED as true
            */
        $exists['IS_SENDED'] = 1;

        /* update line in DB */
        SQLUpdate(self::TABLE, $exists);

        return true;
    }

    static function sendEmail($text)
    {
        echo 'Email sended: ' . $text . '<br>';
        return true;
    }

    public static function sendSms($text)
    {
        echo 'Sms sended: ' . $text . '<br>';
        return true;
    }

    public static function translitIt($str)
    {
        $tr = array(
            "А" => "A", "Б" => "B", "В" => "V", "Г" => "G",
            "Д" => "D", "Е" => "E", "Ж" => "J", "З" => "Z", "И" => "I",
            "Й" => "Y", "К" => "K", "Л" => "L", "М" => "M", "Н" => "N",
            "О" => "O", "П" => "P", "Р" => "R", "С" => "S", "Т" => "T",
            "У" => "U", "Ф" => "F", "Х" => "H", "Ц" => "TS", "Ч" => "CH",
            "Ш" => "SH", "Щ" => "SCH", "Ъ" => "", "Ы" => "YI", "Ь" => "",
            "Э" => "E", "Ю" => "YU", "Я" => "YA", "а" => "a", "б" => "b",
            "в" => "v", "г" => "g", "д" => "d", "е" => "e", "ж" => "j",
            "з" => "z", "и" => "i", "й" => "y", "к" => "k", "л" => "l",
            "м" => "m", "н" => "n", "о" => "o", "п" => "p", "р" => "r",
            "с" => "s", "т" => "t", "у" => "u", "ф" => "f", "х" => "h",
            "ц" => "ts", "ч" => "ch", "ш" => "sh", "щ" => "sch", "ъ" => "y",
            "ы" => "yi", "ь" => "", "э" => "e", "ю" => "yu", "я" => "ya"
        );
        return strtr($str, $tr);
    }
}

?>

<html>
<head>
    <title>Тестовое задание</title>
    <script
			  src="http://code.jquery.com/jquery-3.3.1.min.js"
			  integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8="
			  crossorigin="anonymous"></script>
              <script src="js/script.js"></script>
</head>
<body>
<?php

/** changed by me. without "isset" show error Notice: Undefined index: addsms
 * and form for send message doesn't show on the page
 */
echo '<form method="post" action="/" id="form">
    
<textarea name="TEXT" rows="5" cols="40">
</textarea><br>
<input type="hidden" name="save" value="1">
<input type="submit" name="addsms" value="Отправить">
</form>

<div id="list">
</div>
<div><b onclick="prevPage()"> -prev- </b> <i id="currentPage">1</i> <b onclick="nextPage()"> -next-</b></div>

';

if (isset($_REQUEST['addsms'])) {
    if (($_REQUEST['save'])) {
        /** create new message. without sending */
        SmsSender::createNew($_REQUEST['TEXT']);
    }

    
} else {
    /* file with last sended message*/
    $file = './last.txt';
    /*if exists*/
    if (is_file($file)) {
        /*get data from file*/
        $date = file_get_contents($file);
        /** converting string to date type*/
        $date = strtotime($date);

        if (time() - $date < 3600 * 24 * DAYS_COUNT) exit;
        if (date('G') < 9 || date('G') > 12) exit; // send sms from 9 to 12
        if (in_array(date('N'), array(6, 7))) exit; // send sms from monday to friday
        /** delete message */
        unlink($file);
    }
    /** send or not send. random */
    $need = rand(0, 1);
    if ($need) {
        if (SmsSender::notify()) {
            /* save last sended message*/
            file_put_contents($file, date('Y-m-d H:i:s'));
        }
    }
}
?>






</body>
</html>
