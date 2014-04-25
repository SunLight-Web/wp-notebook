  <?php


?>


<?php

global $wpdb;

$polls = $wpdb->get_results("SELECT * FROM poll_data","ARRAY_A");

foreach ($polls as $value) {
    if(is_url_exist($value['csv_link'])){

      $file = fopen($value['csv_link'],"r") or die("Возникла проблема при попытке загрузить системный файл, вероятно – проблемы с хостингом. Попробуйте перезагрузить страницу.");
      $theArray = array();
      $i = 0;
      while(! feof($file))
        {
        $theArray[$i] = (fgetcsv($file));
        $i++;
        }

      fclose($file);
      $key = array_search('id', $theArray[0]);
        for ($i=1; $i <= (count($theArray) - 1); $i++) { 
            if ($theArray[$i] != NULL) 
            {

          $idOfClient = $theArray[$i][$key];
          unset($theArray[$i][$key]);

            

                $query = "SELECT client_poll_hstry FROM table_of_clients WHERE id =" . $idOfClient;
                $answersByClient = $wpdb->get_results($query, "ARRAY_A");
                $updateData = $value['id'] . "."; 
                $pos = strpos($answersByClient[0]['client_poll_hstry'], $updateData);
                if ($pos === false){
                $updateData = $answersByClient[0]['client_poll_hstry'] . $value['id'] . "."; 
                $wpdb->update('table_of_clients', array('client_poll_hstry' => $updateData), array('id' => $idOfClient ));
                }
            }
        }
  }
  else
  echo 'ОПРОС "' . $value['poll_name'] . '" НЕ ИНИЦИАЛИЗИРОВАН! <br> Для инициализации пройдите по ссылке: <a href="http://' . $value['poll_link'] . 'init">' . $value['poll_link'] . 'init' . '</a>';
}
?>

 