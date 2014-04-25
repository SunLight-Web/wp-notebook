  <?php

function is_url_exist($url){
    $ch = curl_init($url);    
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if($code == 200){
       $status = true;
    }else{
      $status = false;
    }
    curl_close($ch);
   return $status;
}



?>
<html lang="ru/en">
<head>
  <meta http-equiv="content-type" content="text/html; charset=utf-8">

<title><?php echo $subject; ?></title>
</head>
<body style="background-color:#fafafa;">

<?php


$query = "SELECT csv_link, poll_name FROM poll_data WHERE id = '" . $_GET['idOfThePoll'] . "'";
$csvFile = $wpdb->get_results($query,"ARRAY_A");
echo "<h2>" . $csvFile[0]['poll_name'] . ": результаты." . "</h2>";
echo "<table class='widefat fixed' cellspacing='0' style='width:70% !important'>\n\n";
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
                            echo "<td>" . htmlspecialchars($cell) . "</td>";
                      }
                      else
                      {
                            echo "<td>" . 'Имя отвечающего:' . "</td>";
                      }
                  }
                  else
                  {
                    $nameOfClient = $wpdb->get_results("SELECT client_name FROM table_of_clients WHERE id = $cell","ARRAY_A");
                    echo "<td>" . $nameOfClient[0]['client_name'] . "</td>";
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
</body>
</html>