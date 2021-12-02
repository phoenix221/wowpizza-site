<?

$filename = 'b_user.sql'; // Имя файла дампа
$mysql_host = 'localhost'; // Адрес сервера MySQL
$mysql_username = 'cl301470_af'; // Имя пользователя MySQL
$mysql_password = 'WGVkvR61'; // Пароль MySQL
$mysql_database = 'cl301470_af'; // Имя БД
mysql_connect($mysql_host, $mysql_username, $mysql_password) or die('Error connecting to MySQL server: ' . mysql_error());
mysql_select_db($mysql_database) or die('Error selecting MySQL database: ' . mysql_error());
$templine = '';
$lines = file($filename);
foreach ($lines as $line){
    if (substr($line, 0, 2) == '--' || $line == '')
        continue;
    $templine .= $line;
    if (substr(trim($line), -1, 1) == ';'){
        mysql_query($templine) or print('Error performing query \'<strong>' . $templine . '\': ' . mysql_error() . '');
        $templine = '';
    }
}
echo "Таблицы успешно импортированы";
