

<?php

require_once( $_SERVER['DOCUMENT_ROOT'] . '/wp-config.php' );
require_once( $_SERVER['DOCUMENT_ROOT'] . '/wp-includes/wp-db.php' );
if (!$wpdb) {
    $wpdb = new wpdb( DB_USER, DB_PASSWORD, DB_NAME, DB_HOST);
} else {
    global $wpdb;
}

$query = "SELECT csv_link, poll_name FROM poll_data WHERE id = '" . $_GET['idOfThePoll'] . "'";
$csvFile = $wpdb->get_results($query,"ARRAY_A");
?>

<html lang="ru/en">
<head>
  <meta http-equiv="content-type" content="text/html; charset=utf-8">
   <link rel='stylesheet' href='<?php echo  plugin_dir_url(__FILE__) . "css/styles.css"; ?>' type='text/css' media='all' />

<title ><?php echo $csvFile[0]['poll_name']; ?></title>
</head>

<body style="background-color:#fafafa;">
  <?php
echo "<h2>" . $csvFile[0]['poll_name'] . ": результаты." . "</h2>";
echo "<table class='widefat fixed features-table' style='min-width: 90%;'>\n\n";
 if (is_url_exist($csvFile[0]['csv_link'])) {
        $f = fopen($csvFile[0]['csv_link'], "r");
        $i = 0;
        $j = 1;

        while (($line = fgetcsv($f)) !== false) {
          if ($i == 1) $thereWeGo = ($j - 1);
          if (($thereWeGo != 0) AND isset($_GET['idOfClientToFilter']) AND ($line[$thereWeGo] != $_GET['idOfClientToFilter'])) {
            echo "";
          }
          else {
            $j=0;
                echo "<tr>";
                foreach ($line as $cell) {
                  if(($i == 0) OR ($j != $thereWeGo)) {
                      if($cell != 'id'){
                            echo "<th>" . htmlspecialchars($cell) . "</th>";
                      }
                      else
                      {
                            echo "<th>" . 'Имя отвечающего:' . "</th>";
                      }
                  }
                  else
                  {
                    $nameOfClient = $wpdb->get_results("SELECT client_name FROM table_of_clients WHERE id = $cell","ARRAY_A");
                    echo "<th>" . $nameOfClient[0]['client_name'] . "</th>";
                  }
                $j++;
                }
                echo "</tr>\n";
            $i++;
          }

      }
        fclose($f);
        echo "\n</table>";
  }
  else
  {
    echo "Опрос не инициализирован, csv файла не существует.";
  }



?>
<style>
  table {
    font-weight: normal;
  }
 
   th {
    padding: 7px;
    font-weight: normal;
  }
   tr:first-child {
    background: #EAEAEA;
    font-weight: bold;
  }
  tr:first-child th {
    font-weight: bold;
  }
  h2 {
    text-align: center;
    color: #695E5E;
  }
</style>
</body>
</html>