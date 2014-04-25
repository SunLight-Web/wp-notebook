<?php
/*
Plugin Name: Таблица-Записная книжка
Description: Плагин добавляет таблицу-записную книжку.
Version: 1.0
Author: Sunlight-web
Author URI: http://web-sunlight.com/
License: A "Slug" license name e.g. GPL2
*/

/*  Copyright 2014  Sunlight Web  (email : info@web-sunlight.com)

*/
 wp_enqueue_script('jquery'); 
add_action( 'admin_menu', 'tzk_menu' );

function tzk_menu() {
    add_menu_page( 'Таблица', 'Таблица', 'manage_options', 'main_tzk_page', 'tzk_itself' );
    add_submenu_page( 'main_tzk_page', 'Параметры', 'Параметры', 'manage_options', 'preferences_tzk_page', 'tzk_options' );
    add_submenu_page( 'main_tzk_page', 'Опросы', 'Опросы', 'manage_options', 'polls_tzk_page', 'tzk_poll_page' );
    add_submenu_page( 'main_tzk_page', 'Рассылка email','Рассылка email', 'manage_options', 'Mass-Email','massEmail_func', '');
    add_submenu_page( 'main_tzk_page', 'История рассылок', 'История рассылок', 'manage_options', 'Mass-Email-History', 'massEmail_history' );

}
 

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


function tzk_itself() {
    if ( !current_user_can( 'manage_options' ) )  {
        wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
    }
    echo '<div class="wrap">';



$checkAndCreateTables = include_once (plugin_dir_path(__FILE__) . 'dbcreate.php');
include_once (plugin_dir_path(__FILE__) . '/update_poll_data.php');

if($checkAndCreateTables){
echo "";
}
else
{
  echo "<div style='color:lightgrey; padding:10px; border: 2px solid #aa6666; background-color:#aa0000;'>Произошла ошибка с базой данных!<br>";
  echo "Файл " . plugin_dir_path(__FILE__) . 'dbcreate.php' . " не найден!";
  echo "</div>";
}

global $wpdb;





if($_POST['mass-email']){
  $query = "SELECT client_email FROM table_of_clients WHERE ";

  if (sizeof($_POST['ckboxs'])>1) {
    foreach ($_POST['ckboxs'] as $val) {
      $query .= "(id = " . $val . ") OR ";
    }
    $query = substr($query, 0, strlen($query) - 4);
  }
  else
  {
    $query .= "id = " . $_POST['ckboxs'][0];
  }

    $arrayOfEmails = $wpdb->get_results($query,"ARRAY_A");
  ?>

  <!-- ПОФИКСИТЬ!! -->
  <form method="post" action="<?php echo site_url('/wp-admin/admin.php?page=Mass-Email'); ?>" name="sendEmail" id="sendEmail">
    <div>
<h3>Вы собираетесь отправить email клиентам.</h3>

    <input type="hidden" name="action" value="sendEmailForm">
    <?php 
    foreach ($arrayOfEmails as $val) {
      echo "<input type='hidden' name='ckboxs[]' value='" . $val['client_email'] . "'>";
    }
    ?>
<table>
<tr>
  <th>Выберите рассылку:</th>
  <th></th>
</tr>

      <tr>
        <td>
            <select name="predefined-email">
                              <option value="new" selected>Новое сообщение...</option>
                <?php 
                     $listOfEmails = $wpdb->get_results("SELECT id,email_subject FROM sent_email_history",'ARRAY_A');
                      foreach ($listOfEmails as $val) {
                        echo "<option value='" . $val['id'] . "'>" . $val['email_subject'] . "</option>";
                      }
                ?>
            </select>
        </td>
        <td><input type="submit" value="Далее" class="button-secondary" id="sendemails"></td>
      </tr>
</table>

  </form>
</div>
  <?
}





if($_POST['addNewRow']){
   $wpdb->insert('table_of_clients',   array('client_date'     => date('Y-m-d'),
                                             'client_date_lm'  => date('Y-m-d')));
}

if($_POST['removeRow'] AND $_POST['ckboxs']){
  foreach ($_POST['ckboxs'] as $val) {
      $wpdb->delete('table_of_clients', array('id' => $val));
  }
}

if($_POST['saveData'] AND $_POST['ckboxs'])
{
 foreach ($_POST['ckboxs'] as $val) {
  if (is_array($_POST[$val . ',newCountry'])) {
     $country = implode(",", $_POST[$val . ',newCountry']);
  }
  else {
     $country = $_POST[$val . ',newCountry'];
  }


  if (is_array($_POST[$val . ',newWhoIsWorking'])) {
     $whoisworking = implode(",", $_POST[$val . ',newWhoIsWorking']);
  }
  else {
     $whoisworking = $_POST[$val . ',newWhoIsWorking'];
  }
       $wpdb->update('table_of_clients', array('client_name'              =>        $_POST[$val . ',newName'],
                                              'client_typeOfDeal'         =>        $_POST[$val . ',newTypeOfDeal'],
                                              'client_contacts'           =>        $_POST[$val . ',newContacts'],
                                              'client_email'              =>        trim($_POST[$val . ',newEmail']),
                                              'client_country'            =>        $country,
                                              'client_source'             =>        $_POST[$val . ',newSource'],
                                              'client_date_lm'            =>        date('Y-m-d'), 
                                              'client_whoisworking'       =>        $whoisworking,
                                              'client_MEMO'               =>        $_POST[$val . ',newMEMO'],
                                              'client_objects'            =>        $_POST[$val . ',newObjects'],
                                              'client_uid'                =>        $_POST[$val . ',newUid'],            
                                              'client_priority'           =>        $_POST[$val . ',newPriority'], 
                                               ),
                                        array('id' => $val));
  }
}

$tableStuffToFill=$wpdb->get_results("SELECT * FROM table_of_clients",'ARRAY_A');


if($_POST['needs-to-filter']) {
  $isFiltered = true;
  $whereStringForQuery = array();
  foreach ($_POST['needs-to-filter'] as $val) {
    switch ($val) {
      case 'client_typeOfDeal':
        $whereStringForQuery['client_typeOfDeal']   =   $_POST['filter_typeOfDeal'];
        break;
      case 'client_name':
        $whereStringForQuery['client_name']         =   $_POST['filter_name'];
        break;
      case 'client_email':
        $whereStringForQuery['client_email']        =   $_POST['filter_email'];
        break;
      case 'client_contacts':
        $whereStringForQuery['client_contacts']     =   $_POST['filter_contacts'];
        break;
      case 'client_country':
        $whereStringForQuery['client_country']      =   $_POST['filter_country'];
        break;
      case 'client_source':
        $whereStringForQuery['client_source']       =   $_POST['filter_source'];
        break;
      case 'client_whoisworking':
        $whereStringForQuery['client_whoisworking'] =   $_POST['filter_whoisworking'];
        break;
      case 'client_MEMO':
        $whereStringForQuery['client_MEMO']         =   $_POST['filter_MEMO'];
        break;
      case 'client_uid':
        $whereStringForQuery['client_uid']          =   $_POST['filter_uid'];
        break;
      case 'client_objects':
        $whereStringForQuery['client_objects']      =   $_POST['filter_objects'];
        break;
      case 'client_priority':
      {
        if (isset($_POST['filter_priority']))  $priority = 1;  else  $priority = 0;   
        $whereStringForQuery['client_priority']     =   $priority;
      }
        break;
      case 'client_date':                                 
        $whereStringForQuery['client_date']         =   "`client_date` BETWEEN  '" . $_POST['filter_date_from'] . "' AND '" . $_POST['filter_date_to'] . "'";
        break;
      case 'client_date_lm':
        $whereStringForQuery['client_date_lm']      =   "`client_date_lm` BETWEEN '" . $_POST['filter_date_lm_from'] . "' AND '" . $_POST['filter_date_lm_to'] . "'";
        break;
      
      default:
                              $whereStringForQuery['error'] = 1;
        break;
              }

            }

  $query="
  SELECT * 
  FROM table_of_clients
  WHERE ";
  if (sizeof($whereStringForQuery)>1){
   foreach ($whereStringForQuery as $key => $val) {
    if (!(($key == 'client_date') OR ($key == 'client_date_lm'))) {
      $addToQuery = '(' . $key . " LIKE " . "'%" . $val . "%'" . ') AND ';
      $query .= $addToQuery;
    }
  }
  if(isset($whereStringForQuery['client_date'])) $query .=  "(" . $whereStringForQuery['client_date'] . ") AND";
  if(isset($whereStringForQuery['client_date_lm'])) $query .=  "(" . $whereStringForQuery['client_date_lm'] . ") AND";
    $query = substr($query, 0, strlen($query) - 4);

  }
  else 
  {
    foreach ($whereStringForQuery as $key => $val) {
    if (($key == 'client_date') OR ($key == 'client_date_lm')) {
      $query .= $val;
      }
    else
      { 
      $addToQuery =  $key . " LIKE " . "'%" . $val . "%'";
      $query .= $addToQuery;
      }
    }
  } 
    $tableStuffToFill=$wpdb->get_results($query, 'ARRAY_A');
}
else
{
  $isFiltered = false;
}
                 if($isFiltered) {
                  echo "<h3>Отфильтрованно по ";
                  $fltrString = "";
                  foreach ($whereStringForQuery as $key => $val) {
                     switch ($key) {
                      case 'client_typeOfDeal':
                        $fltrString .= "типу сделки (" . $val . "), ";
                        break;
                      case 'client_name':
                        $fltrString .= "имени (" . $val . "), ";
                        break;
                      case 'client_email':
                        $fltrString .= "email (" . $val . "), ";
                        break;
                      case 'client_contacts':
                        $fltrString .= "контактным данным (" . $val . "), ";
                        break;
                      case 'client_country':
                        $fltrString .= "стране (" . $val . "), ";
                        break;
                      case 'client_source':
                        $fltrString .= "источнику (" . $val . "), ";
                        break;
                      case 'client_whoisworking':
                        $fltrString .= "работнику (" . $val . "), ";
                        break;
                      case 'client_MEMO':
                        $fltrString .= "МЕМО (" . $val . "), ";
                        break;
                      case 'client_uid':
                        $fltrString .= "номеру (" . $val . "), ";
                        break;
                      case 'client_objects':
                        $fltrString .= "объектам (" . $val . "), ";
                        break;
                      case 'client_priority':
                        $fltrString .= "приоритету (" . $val . "), ";
                        break;
                      case 'client_date':
                        $fltrString .= "дате создания (" . "дата" . "), ";
                        break;
                      case 'client_date_lm':
                        $fltrString .= "дате изменения (" . "дата" . "), ";
                        break;
                      default:

                        break;
                    }
                  }
                    $fltrString = substr($fltrString, 0, strlen($fltrString) - 2);
                    echo $fltrString . "." . "</h3>";
                  }

?>

 <link rel='stylesheet' href='<?php echo  plugin_dir_url(__FILE__) . "css/styles.css"; ?>' type='text/css' media='all' />
 <link rel='stylesheet' href='<?php echo  plugin_dir_url(__FILE__) . "css/tcal.css"; ?>' type='text/css' media='all' />
 <script src='<?php echo  plugin_dir_url(__FILE__) . "js/jquery.js"; ?>'></script>
 <!-- <script src='<?php echo  plugin_dir_url(__FILE__) . "js/jquery.tablesorter.js"; ?>'></script> -->
 <script src='<?php echo  plugin_dir_url(__FILE__) . "js/tcal.js"; ?>'></script>
   <script src='<?php echo  plugin_dir_url(__FILE__) . "js/jquery.stickytableheaders.js"; ?>'></script>
<script>
  $(function() {
  $('.features-table').stickyTableHeaders();
  
  });

  </script>







 <script type="text/javascript">

function openListOfPolls(id) {
document.getElementById('listOfPolls' + id).style.display = "block";

}



// jQuery(document).ready(function() {
  
//     $("#main-table").tablesorter({ 
//         headers: { 
//             0: {  sorter: false  }, 
//             3: {  sorter: false  },
//            10: {  sorter: false  },
//            14: {  sorter: false  },
//            15: {  sorter: false  }
//                  } 
//     }); 
  
// });


</script>
<script>
  $(this).ready(function () {
    $(".tr_dblclick").dblclick(
      function () {
      $(".input_dblclick", this).attr('checked',true)
    });
});

</script>

<!-- модальное  окно мемо-->
<script>
function openPopUp(clid) {
  var inpta = document.getElementById(clid + ',newMEMO');
  var outta = document.getElementById('modalTA');

    var maskHeight = $(document).height();
    var maskWidth = $(document).width();
    var id = '#dialog';
    $('.mask').css({'width':maskWidth,'height':maskHeight});
    $('.mask').fadeIn(400);
    $('.mask').fadeTo("slow",0.8);
    var winH = $(window).height();
    var winW = $(window).width();
    $(id).css('top',  winH/2-$(id).height()/2);
    $(id).css('left', winW/2-$(id).width()/2);
    $(id).fadeIn(400);
    outta.value = inpta.value;
  $('.window .close').click(function (e) {
    e.preventDefault();
    $('.mask, .window').hide();
  });
    $('.window .send').click(function (e) {
      inpta.value = outta.value;
    document.getElementById(clid + "checkbox").checked = true;
    document.getElementById('saveData').value = 'go';
    $('.mask, .window').hide();
    document.getElementById('rowManage').submit();
  });
   $('.window input').click(function (e) {
    e.preventDefault();
    $('.mask, .window').hide();
  });
  $('.mask').click(function () {
    $(this).hide();
    $('.window').hide();
  });
  return 0;
};


</script>
<!-- модальное окно мемо-->


<!-- модальное окно boxes_whoisworking и boxes_country-->
<script>
$(document).ready(function() {
    $('a[name=modal]').click(function(e) {
      e.preventDefault();
      var id = $(this).attr('href');
      var idOfUser = $(this).attr('id');
      window.idOfUser = idOfUser;
      var maskHeight = $(document).height();
      var maskWidth = $(window).width();
      $('.mask').css({'width':maskWidth,'height':maskHeight});
      $('.mask').fadeIn(400);
      $('.mask').fadeTo("slow",0.8);
      var winH = $(window).height();
      var winW = $(window).width();
      $(id).css('top',  winH/2-$(id).height()/2);
      $(id).css('left', winW/2-$(id).width()/2);
      $(id).fadeIn(400);
    });
  $('.window .sendit').click(function (e) {
    document.getElementById(idOfUser + "checkbox").checked = true;
    document.getElementById('saveData').value = 'go';
    $('.mask, .window').hide();
    document.getElementById('rowManage').submit();
  });

  $('.window .close').click(function (e) {
    e.preventDefault();
    $('.mask, .window').hide();
  });
  $('.mask').click(function () {
    $(this).hide();
    $('.window').hide();
  });
});
</script>
<!-- //модальное окно boxes_whoisworking-->

<!-- модальное окно мемо-->
<div id="boxes">
  <div id="dialog" class="window">
  <textarea id="modalTA"></textarea>
  <button type="button" class="send button-secondary">Отправить</button>
  <span><a href="#" class="close"/>Закрыть</a>
  </div>
</div>
<!-- /модальное окно мемо-->



         


<?php

// ВЫВОД ТАБЛИЦЫ
$totalRecordForQuery = sizeof($tableStuffToFill);
$idOfListOfPolls = 0;
$objectLink = $wpdb->get_results("SELECT objects_link FROM config_tzk","ARRAY_A");
$objectLink = $objectLink[0]['objects_link'];
  if ($totalRecordForQuery != 0)
  {
?>
          <div class="mask"></div>
            <div class="main-table-block">
            <div class="table_div">
            <table id="main-table" class="features-table" style="width:100%;">
            <thead>
                    <form name="fitler-form" method="post" action="">
               <tr id="filterClients">
                    <td scope="col" class="grey">
                      <input type="submit" value="Применить" name="filter-the-table" id='go' class="button-secondary">
                    </td>
                    <td scope="col" style="" class="grey">
                      <input type="checkbox" name="needs-to-filter[]" value="client_typeOfDeal">
                      <select name="filter_typeOfDeal" id="filter_typeOfDeal">
                                        <?php 
                                         $dealsWeHave = $wpdb->get_results("SELECT deal FROM new_values WHERE deal != ''",'ARRAY_A');
                                          foreach ($dealsWeHave as $val) {
                                            echo "<option value='" . $val['deal'] . "'>" . $val['deal'] . "</option>";
                                          }
                                          ?>
                      </select>

                    </td>
                    <td scope="col" style="" class="grey">

                      <input type="checkbox" name="needs-to-filter[]" value="client_name">
                      <input type="text" name="filter_name" id="filter_name">

                    </td>
                    <td scope="col" style="" class="grey">

                      <input type="checkbox" name="needs-to-filter[]" id="contactsCheckbox" value="client_contacts">
                      <input type="text" name="filter_contacts" id="filter_contacts">

                    </td>
                    <td scope="col" style="" class="grey">

                      <input type="checkbox" name="needs-to-filter[]" value="client_email">
                      <input type="text" name="filter_email" id="filter_email"></td>

                    <td scope="col" style="" class="grey">

                      <input type="checkbox" name="needs-to-filter[]" value="client_country">
                      <select name="filter_country" id="filter_country">
                                    <?php 
                                   $dealsWeHave = $wpdb->get_results("SELECT country FROM new_values WHERE country != ''",'ARRAY_A');

                                    foreach ($dealsWeHave as $val) {
                                      echo "<option value='" . $val['country'] . "'>" . $val['country'] . "</option>";
                                    }
                                    ?>
                      </select>
                    </td>
                    <td scope="col" style="" class="grey">

                        <input type="checkbox" name="needs-to-filter[]" value="client_source">
                        <select name="filter_source" id="filter_source">
                                      <?php 
                                     $dealsWeHave = $wpdb->get_results("SELECT source FROM new_values WHERE source != ''",'ARRAY_A');

                                      foreach ($dealsWeHave as $val) {
                                        echo "<option value='" . $val['source'] . "'>" . $val['source'] . "</option>";
                                      }
                                      ?>
                        </select>

                    </td>
                    <td scope="col" style="" class="grey">

                      <input type="checkbox" name="needs-to-filter[]" value="client_date">
                      От:<input type="text" name="filter_date_from" class="tcal" style="width:66px;" value="" />
                      До:<input type="text" name="filter_date_to" class="tcal" style="width:66px;" value="" />


                    </td>
                    <td scope="col" style="" class="grey">

                      <input type="checkbox" name="needs-to-filter[]" value="client_date_lm">
                      От:<input type="text" name="filter_date_lm_from" class="tcal" style="width:66px;" value="" />
                      До:<input type="text" name="filter_date_lm_to" class="tcal" style="width:66px;" value="" />


                    </td>
                    <td scope="col" style="" class="grey">
                        <input type="checkbox" name="needs-to-filter[]" value="client_whoisworking">
                        <input type="hidden" name="filter-the-table" value="go"> 
                      <select name="filter_whoisworking" id="filter_whoisworking">
                                    <?php 
                                   $dealsWeHave = $wpdb->get_results("SELECT worker FROM new_values WHERE worker != ''",'ARRAY_A');

                                    foreach ($dealsWeHave as $val) {
                                      echo "<option value='" . $val['worker'] . "'>" . $val['worker'] . "</option>";
                                    }
                                    ?>
                      </select>

                    </td>
                    <td scope="col" style="" class="grey">

                      <input type="checkbox" name="needs-to-filter[]" value="client_MEMO">
                      <input type="text" name="filter_MEMO" id="filter_MEMO">

                    </td>
                    <td scope="col" style="" class="grey">

                      <input type="checkbox" name="needs-to-filter[]" value="client_objects">
                      <input type="text" name="filter_objects" id="filter_objects">

                    </td>
                    <td scope="col" style="" class="grey">

                      <input type="checkbox" name="needs-to-filter[]" value="client_uid">
                      <input type="text" name="filter_uid" id="filter_uid">

                    </td>
                    <td scope="col" style="" class="grey">

                      <input type="checkbox" name="needs-to-filter[]" value="client_priority">
                      <input type="checkbox" value="1" name="filter_priority" id="filter_priority" style="height: 16px;">
                    </td>
                    <td scope="col" style="" class="grey"><!-- <input type="submit" style="float: right;" value="Отфильтровать" name="filter-the-table" class="button-secondary"> --></td>
                    <td scope="col" style="" class="grey"><!-- Рассылка --></td> 
         <!--        </form> -->
                    </form>
                </tr>
              
              <h1>Таблица</h1>
                <tr>
                    <th scope="col" class="grey"></th>
                    <th scope="col" style="" class="grey">Тип сделки</th>
                    <th scope="col" style="" class="grey">ФИО</th>
                    <th scope="col" style="" class="grey">Контакты</th>
                    <th scope="col" style="" class="grey">E-mail</th>
                    <th scope="col" style="" class="grey">Страна</th>
                    <th scope="col" style="" class="grey">Источник</th>
                    <th scope="col" style="" class="grey">Дата</th>
                    <th scope="col" style="" class="grey">Посл. изменение</th>
                    <th scope="col" style="" class="grey">Кто работает</th>
                    <th scope="col" style="" class="grey">МЕМО</th>
                    <th scope="col" style="" class="grey">Объекты</th>
                    <th scope="col" style="" class="grey">Номер клиента</th>
                    <th scope="col" style="" class="grey">Приоритет</th>
                    <th scope="col" style="" class="grey">Пройденные опросы</th>
                    <th scope="col" style="" class="grey">Рассылка</th>    
                </tr>
            </thead>
            <tbody>

             <?php 
              for($i=0; $i<=$totalRecordForQuery-1; $i++){ 
                $idOfCurrentClient = $tableStuffToFill[$i]['id'];
                ?>
 <!-- MODAL WINDOWS -->
                <form method="post" name="rowManage" id="rowManage" action="">
<!-- модальное окно boxes_сountry-->
<div id="boxes_country">
  <div id="dialog_country_<?php echo $idOfCurrentClient; ?>" class="window">
        <select name='<?php echo "$idOfCurrentClient" . ",newCountry[]"; ?>' multiple class="select-main" id='<?php echo "$idOfCurrentClient" . ",newCountry"; ?>'>  
        <?php
          $dealsWeHave  = $wpdb->get_results("SELECT country FROM new_values WHERE country != ''",'ARRAY_A');
          $arrayToCompare = array();
            foreach ($dealsWeHave as $value) {
               array_push($arrayToCompare, $value['country']);
            }
          $arrayOfDealsForClient = explode(",", $tableStuffToFill[$i]['client_country']);
          $diffBetweenDeals = array_diff($arrayToCompare, $arrayOfDealsForClient);
            foreach ($arrayOfDealsForClient as $val) {
                echo "<option value='" . $val . "' selected>" . $val . "</option>";  
             } 

                  foreach ($diffBetweenDeals as $val) {
                     echo "<option value='" . $val . "'>" . $val . "</option>";
            } 
      ?>
    </select>
  <span><a href="#" class="close"/>Закрыть</a></span>
      <button type="button" class="sendit button-secondary">Отправить</button>
  </div>
</div>



<div id="boxes_whoisworking" >
  <div id="dialog_whoisworking_<?php echo $idOfCurrentClient; ?>" class="window">
      <select name='<?php echo "$idOfCurrentClient" . ",newWhoIsWorking[]"; ?>' multiple class="select-main" id='<?php echo "$idOfCurrentClient" . ",newWhoIsWorking"; ?>'>
      <?php
          $dealsWeHave    = $wpdb->get_results("SELECT worker FROM new_values WHERE worker != ''",'ARRAY_A');
          $arrayToCompare = array();
            foreach ($dealsWeHave as $value) {
              array_push($arrayToCompare, $value['worker']);
            }
          $arrayOfDealsForClient = explode(",", $tableStuffToFill[$i]['client_whoisworking']);
          $diffBetweenDeals = array_diff($arrayToCompare, $arrayOfDealsForClient);
              foreach ($arrayOfDealsForClient as $val) {
                echo "<option value='" . $val . "' selected>" . $val . "</option>";  
              } 

              foreach ($diffBetweenDeals as $val) {
                 echo "<option value='" . $val . "'>" . $val . "</option>";                                                                              
              }
      ?>
    </select>
  <span><a href="#" class="close"/>Закрыть</a></span>
    <button type="button" class="sendit button-secondary">Отправить</button>
  </div>
</div>
<!-- //модальное окно boxes_whoisworking-->

              
    <!-- /WINDOWS -->
                <tr class="tr_dblclick">
                    <td >
                      <input class="input_dblclick" id="<?php echo $idOfCurrentClient . 'checkbox';?>" type='checkBox' name='ckboxs[]'  value="<?php echo $idOfCurrentClient; ?>">
                    </td>

                    <td>
                      <span class="hidden"><?php echo $tableStuffToFill[$i]['client_typeOfDeal']; ?></span>
                      <select style="width:90px;" name='<?php echo "$idOfCurrentClient" . ",newTypeOfDeal"; ?>' id='<?php echo "$idOfCurrentClient" . ",newTypeOfDeal"; ?>'>

                                                                            <?php
                                                                              $dealsWeHave  = $wpdb->get_results("SELECT deal FROM new_values WHERE deal != ''",'ARRAY_A');
                                                                              foreach ($dealsWeHave as $val) {
                                                                              if ($tableStuffToFill[$i]['client_typeOfDeal'] == $val['deal']){ 
                                                                                  $shouldBeSelected = " selected"; 
                                                                              }
                                                                              else { $shouldBeSelected = ""; 
                                                                              }
                                                                              echo "<option value='" . $val['deal'] . "'" . $shouldBeSelected . ">" . $val['deal'] . "</option>";
                                                                            } ?>

                      </select>
                    </td> 

                    <td>
                      <span class="hidden"><?php echo $tableStuffToFill[$i]['client_name']; ?></span>
                      <input type="text" id='<?php echo "$idOfCurrentClient" . ",newName"; ?>' name='<?php echo "$idOfCurrentClient" . ",newName"; ?>' value="<?php echo $tableStuffToFill[$i]['client_name']; ?>"     class="main-input">
                    </td>

                    <td>
                      <span class="hidden"><?php echo $tableStuffToFill[$i]['client_contacts']; ?></span>
                      <textarea  id='<?php echo "$idOfCurrentClient" . ",newContacts"; ?>' name='<?php echo "$idOfCurrentClient" . ",newContacts"; ?>' value="<?php echo $tableStuffToFill[$i]['client_contacts']; ?>" class="textarea-main"><?php echo $tableStuffToFill[$i]['client_contacts']; ?></textarea>
                    </td>

                    <td>
                      <span class="hidden"><?php echo $tableStuffToFill[$i]['client_email']; ?></span>
                      <input type="text"  id='<?php echo "$idOfCurrentClient" . ",newEmail"; ?>' name='<?php echo "$idOfCurrentClient" . ",newEmail"; ?>' value="<?php echo $tableStuffToFill[$i]['client_email']; ?>"    class="main-input">
                    </td>

                    <td >
                      <?php foreach (explode(",", $tableStuffToFill[$i]['client_country']) as $val) {
                        echo $val . "<br>";
                      } ?>
                      <a href="#dialog_country_<?php echo $idOfCurrentClient; ?>" id="<?php echo $idOfCurrentClient; ?>" name="modal" class="dialog_a">Редактировать</a>
                  </td>

                   <td>
                      <span  class="hidden"><?php echo $tableStuffToFill[$i]['client_source']; ?></span>
                      <select style="width:90px;" id='<?php echo "$idOfCurrentClient" . ",newSource"; ?>' name='<?php echo "$idOfCurrentClient" . ",newSource"; ?>'>
                      
                                                                            <?php
                                                                              $dealsWeHave  = $wpdb->get_results("SELECT source FROM new_values WHERE source != ''",'ARRAY_A');
                                                                              foreach ($dealsWeHave as $val) {
                                                                              if ($tableStuffToFill[$i]['client_source'] == $val['source']){ 
                                                                                  $shouldBeSelected = " selected"; 
                                                                              }
                                                                              else { $shouldBeSelected = ""; 
                                                                              }
                                                                              echo "<option value='" . $val['source'] . "'" . $shouldBeSelected . ">" . $val['source'] . "</option>";
                                                                             }
                                                                             ?>

                     </select>
                   </td>

                    <td><?php echo $tableStuffToFill[$i]['client_date']; ?>
                      <div style="width:100px;"></div>
                    </td>

                    <td><?php echo $tableStuffToFill[$i]['client_date_lm']; ?>
                      <div style="width:100px;"></div>
                    </td>

                    <td>
                      <?php foreach (explode(",", $tableStuffToFill[$i]['client_whoisworking']) as $val) {
                        echo $val . "<br>";
                      } ?>
                    <a href="#dialog_whoisworking_<?php echo $idOfCurrentClient; ?>" id="<?php echo $idOfCurrentClient; ?>" name="modal" class="dialog_a">Редактировать</a>
                    </td>

                    <td>

                      <span class="hidden"><?php echo $tableStuffToFill[$i]['client_MEMO']; ?></span>
                    <textarea readonly onclick='openPopUp(<?php echo $idOfCurrentClient; ?>)' id='<?php echo "$idOfCurrentClient" . ",newMEMO"; ?>' name='<?php echo "$idOfCurrentClient" . ",newMEMO"; ?>' class="textarea-main"><?php echo stripcslashes($tableStuffToFill[$i]['client_MEMO']); ?></textarea>
                    </td>

                    <td>
                      <?php
                      $objectsThemselves = explode(",", $tableStuffToFill[$i]['client_objects']);
                      foreach ($objectsThemselves as $key => $object) {
                        $object = trim($object);
                        echo '<a href="' . $objectLink . $object . '">' . $object . '</a><br>';
                      }

                      ?>
                      <input type="text" id='<?php echo "$idOfCurrentClient" . ",newObjects"; ?>' value='<?php echo $tableStuffToFill[$i]['client_objects']; ?>' name='<?php echo "$idOfCurrentClient" . ",newObjects"; ?>' class="textarea-main">
                    </td>

                    <td>
                      <span class="hidden"><?php $intUid = (int)$tableStuffToFill[$i]['client_uid'];  echo $intUid; ?></span>
                      <input  id='<?php echo "$idOfCurrentClient" . ",newUid"; ?>' name='<?php echo "$idOfCurrentClient" . ",newUid"; ?>' type="text" value="<?php echo $tableStuffToFill[$i]['client_uid']; ?>" class="main-input" style="width:40px;">
                    </td>

                    <td>
                      <span class="hidden"><?php echo $tableStuffToFill[$i]['client_priority']; ?></span>
                      <input value="1" id='<?php echo "$idOfCurrentClient" . ",newPriority"; ?>' name='<?php echo "$idOfCurrentClient" . ",newPriority"; ?>' type="checkbox" <?php if($tableStuffToFill[$i]['client_priority'] == 1) echo ' checked'; ?>>
                    </td>
                    <td>
                      <div style="width:250px;"></div>
                      <?php

                      if (empty($tableStuffToFill[$i]['client_poll_hstry'])) echo "<i>Нет пройденных опросов.</i>";

           $arrayOfPolls = explode(".", $tableStuffToFill[$i]['client_poll_hstry']);

 
          $query="
          SELECT id,poll_name,poll_category
          FROM poll_data
          WHERE id ";

          if (sizeof($arrayOfPolls)>1){
              $query .= "IN ('";
                 foreach ($arrayOfPolls as $value) {
                    $stringInValue = $value[0];
                    $query .= $stringInValue[0] . "', '";
                  }
              $query = substr($query, 0, strlen($query) - 4);      
              $query .= "')";
          }
          else 
           {
             $query .= "= " . $arrayOfPolls[0][0];
           } 
            $client_poll = $wpdb->get_results($query,'ARRAY_A');
            $categoriesArray = array();


          foreach ($client_poll as $value) {
          array_push($categoriesArray, $value['poll_category']);
          }
          $categoriesArray = array_unique($categoriesArray);
          foreach ($categoriesArray as $category) {
            $j = 0;
            $arrayOfNames = array();
             foreach ($client_poll as $key => $polldataforclient) {
               if($polldataforclient['poll_category'] == $category) {
                $j++;
                  $arrayOfNames[$polldataforclient['id']] = $polldataforclient['poll_name'];
              }
             }
            $idOfListOfPolls++;
            echo  "<a href='#' onclick='openListOfPolls($idOfListOfPolls)'>" . $category . " (" . $j . ")" . "</a><br>";

            echo  "<div style='display:none;' id='listOfPolls" . $idOfListOfPolls . "'>";
            echo "<table style='font-size:60%; width:100%; margin:2px'>";
              foreach ($arrayOfNames as $key => $poll_name) {
                // ПОФИКСИТЬ!!
                echo "<tr><td class='listOfPolls_td' style='font-size: 11px;'>" . $poll_name . "</td><td class='listOfPolls_td'><a target='_blank' href='" . plugin_dir_url(__FILE__) . "result_of_single_client.php?idOfThePoll=" . $key . "&idOfClientToFilter=" . $tableStuffToFill[$i]['id'] . "'>" . "Посмотреть ответы" . "</a></td></tr>";
              }
            echo "</table>";
            echo "</div>";
            echo "<br>";
          }



           ?></td>
                    <td class="td_subscrib_asdf">
                      <div style="width:250px;"></div>
                      <?php
                      $stuffToShow = "";
                      $dbEmail_history = $wpdb->get_results("SELECT * FROM sent_email_history","ARRAY_A");
                      foreach ($dbEmail_history as $key => $value) {
                        $isItThere = stripos($value['email_recivers'], $tableStuffToFill[$i]['client_email']);
                        if ($isItThere !== FALSE)
                        {
                          echo "<a target='_blank' href='" . plugin_dir_url(__FILE__) . "messageview.php?id=" . $value['id'] . "' >" . $value['email_sent_date'] . " " . $value['email_subject'] . "</a><br>";
                        }
                      } ?>
                    </td>    
                </tr>


            <?php } ?>
            </tbody>
            </table>
            </div>
              <div class="link-to-next-page">
                  <input type="hidden" value="" id="saveData" name="saveData">
                  <input type="submit" class="button-secondary" value="Сохранить изменения" id="saveData" name="saveData">
                  <input type="submit" class="button-secondary" value="Добавить клиента" id="addNewRow" name="addNewRow">
                  <input type="submit" class="button-secondary" value="Удалить" id="removeRow" name="removeRow">
                  <input type="submit" class="button-secondary" value="Отправить email" id="mass-email" name="mass-email">
              </div>
            </form>

<?php // /ВЫВОД ТАБЛИЦЫ ?>
<?php // ФИЛЬТРЫ ?>



 <div class="filter-show">
          <br><br>
        <ul>
          <?php if ($isFiltered){ ?>
            <li><a href="admin.php?page=main_tzk_page" id="reload">Убрать фильтрацию</a></li>
          <?php } ?>
        </ul>
         
<?php // /ФИЛЬТРЫ ?>


</div>
</div>
</div>



<?php
  }
  else
  {
    echo '<h1 style="color:#222;">НЕТ ЗАПИСЕЙ ДЛЯ ВЫВОДА!</h1>'; 
      ?>
        <ul>
          <?php if ($isFiltered){ ?>

            <li><a href="admin.php?page=main_tzk_page" id="reload">Убрать фильтрацию</a></li>
          <?php } ?>
        </ul>
      <?php
  }
}

function tzk_options() {


    if ( !current_user_can( 'manage_options' ) )  {
        wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
    }
    

global $wpdb;

if ($_POST['addNewCsv']){   

  $wpdb->update('poll_data', array('csv_link' => $_POST['csv-link']), array('id' => $_POST['poll_id'])); 

}


  if ($_POST['addClientSubmission']){

    $wpdb->insert('table_of_clients',   array('client_typeOfDeal'     => $_POST['client_typeOfDeal'],
                                              'client_name'           => $_POST['client_name'],
                                              'client_contacts'       => $_POST['client_contacts'],
                                              'client_email'          => $_POST['client_email'],
                                              'client_country'        => $_POST['client_country'],
                                              'client_source'         => $_POST['client_source'],
                                              'client_date'           => date('Y-m-d'),
                                              'client_date_lm'        => date('Y-m-d'),
                                              'client_whoisworking'   => $_POST['client_whoisworking'],
                                              'client_MEMO'           => $_POST['client_MEMO'],
                                              'client_objects'        => $_POST['client_objects'],
                                              'client_uid'            => $_POST['client_uid'],
                                              'client_priority'       => $_POST['client_priority']
                                               ));
  }



  if ($_POST['addDeal'])      {   $wpdb->insert('new_values',   array( 'deal'     => $_POST['deal']                 ));  }
  if ($_POST['addCountry'])   {   $wpdb->insert('new_values',   array( 'country'  => $_POST['country']              ));  }
  if ($_POST['addSource'])    {   $wpdb->insert('new_values',   array( 'source'   => $_POST['source']               ));  }
  if ($_POST['addWorker'])    {   $wpdb->insert('new_values',   array( 'worker'   => $_POST['worker']               ));  }
  if ($_POST['removeDeal'])   {   $wpdb->delete('new_values',   array( 'deal'     => $_POST['client_deal']          ));  }
  if ($_POST['removeCountry']){   $wpdb->delete('new_values',   array( 'country'  => $_POST['client_country']       ));  }
  if ($_POST['removeSource']) {   $wpdb->delete('new_values',   array( 'source'   => $_POST['client_source']        ));  }
  if ($_POST['removeWorker']) {   $wpdb->delete('new_values',   array( 'worker'   => $_POST['client_worker']        ));  }

  if ($_POST['newPollData']) {  $wpdb->insert('poll_data',    array( 'poll_link'    => $_POST['form-link'],
                                                                    'poll_name'     => $_POST['poll_name'],
                                                                    'poll_category' => $_POST['poll_category']    ));  }



  if ($_POST['changeObjectLink']) { $wpdb->update('config_tzk',   array( 'objects_link'  => $_POST['linkOfObjects']), array('id' => '1') );  }

           $url = plugin_dir_url(__FILE__);
           $urlCss=$url."css/styles.css";
    ?>
<link rel='stylesheet' href='<?php echo $urlCss; ?>' type='text/css' media='all' />
<link rel='stylesheet' href='<?php echo  plugin_dir_url(__FILE__) . "css/jquery.asmselect.css"; ?>' type='text/css' media='all' />
<script src="<?php echo  plugin_dir_url(__FILE__) . "js/jquery.js"; ?>"></script>
<script src="<?php echo  plugin_dir_url(__FILE__) . "js/jquery.asmselect.js"; ?>"></script>



<script type="text/javascript">




 jQuery(document).ready(function() {

   jQuery("#addClient").validate({
                    errorClass: "error_jquery",
                    rules: {
                                  client_name: { 
                                        required: true
                                  },
                                  client_uid: { 
                                        required: true
                                  },  
                                  client_email: { 
                                        required: true ,email:true
                                  }
                       }, 
      
                            errorPlacement: function(error, element) {
                            error.appendTo( element.next().next());
                      }
                      
                 });
                      

  });


 
</script>


<div class="add-new-client">
  <h2>Новый клиент</h2>
  <div class="line"></div>
  <?php 
       $url  = plugin_dir_url(__FILE__);
       $urlJS= $url."js/jqueryValidate.js";
  ?>
 <script src="<?php echo $urlJS; ?>"></script>
<form method="post" action="" id="addClient" name="addClient">
  <table>
    <tr>
      <td>Тип сделки:</td>
      <td>
        <select name="client_typeOfDeal" id="client_typeOfDeal">
          <?php 
           $dealsWeHave = $wpdb->get_results("SELECT deal FROM new_values WHERE deal != ''",'ARRAY_A');

            foreach ($dealsWeHave as $val) {
              echo "<option value='" . $val['deal'] . "'>" . $val['deal'] . "</option>";
            }
            ?>
        </select>


      </td>
    </tr>
    <tr>
      <td>ФИО:</td>
      <td><input type="text" name="client_name" id="client_name" placeholder="Иванов Иван Иванович"></td>
    </tr>
    <tr>
      <td>E-mail:</td>
      <td><input type="text" name="client_email" id="client_email" placeholder="example@gmail.com"></td>
    </tr>
    <tr>
      <td>Контакты:</td>
      <td>
        <textarea name="client_contacts" id="client_contacts" placeholder="Москва, +7 999 222 22 22"></textarea>
      </td>
    </tr>
    <tr>
      <td>Страна:</td>
    <td>
      <select name="client_country" id="client_country" multiple>
      <?php 
     $dealsWeHave = $wpdb->get_results("SELECT country FROM new_values WHERE country != ''",'ARRAY_A');

      foreach ($dealsWeHave as $val) {
        echo "<option value='" . $val['country'] . "'>" . $val['country'] . "</option>";
      }
      ?>
      </select>
  </td>
    </tr>
    <tr>
      <td>Источник:</td>
      <td>
      <select name="client_source" id="client_source">
      <?php 
     $dealsWeHave = $wpdb->get_results("SELECT source FROM new_values WHERE source != ''",'ARRAY_A');

      foreach ($dealsWeHave as $val) {
        echo "<option value='" . $val['source'] . "'>" . $val['source'] . "</option>";
      }
      ?>
      </td>
    </tr>
    <tr>
      <td>Кто работает:</td>
      <td>    
      <select name="client_whoisworking" id="client_whoisworking" multiple>
      <?php 
     $dealsWeHave = $wpdb->get_results("SELECT worker FROM new_values WHERE worker != ''",'ARRAY_A');

      foreach ($dealsWeHave as $val) {
        echo "<option value='" . $val['worker'] . "'>" . $val['worker'] . "</option>";
      }
      ?>
      </select>
    </td>
    </tr>
    <tr>
      <td>MEMO:</td>
      <td><textarea  name="client_MEMO" id="client_MEMO"></textarea></td>
    </tr>
    <tr>
      <td>Номер:</td>
      <td><input type="text" name="client_uid" id="client_uid"></td>
    </tr>
    <tr>
      <td>Объекты:</td>
      <td><input type="text" name="client_objects" id="client_objects"></td>
    </tr>
    <tr>
      <td>Приоритет:</td>
      <td><input type="checkbox" value="1" name="client_priority" id="client_priority" style="height: 16px;"></td>
    </tr>
    <tr>
      <td></td>
      <td><input type="submit" style="float: right;" value="Добавить" name="addClientSubmission" id="addClientSubmission" class="button-secondary"></td>
    </tr>
  </table>
</form>
</div>





<form method="post" action="" id="addClient" name="addClient">
<div class="add-new-info">
    <h2>Добавить новые значения</h2>
  <div class="line"></div>
  <table>
    <tr>
      <td>Тип сделки:</td>
      <td><input type="text" placeholder="Аренда" name="deal" id="deal">
          <input type="submit" class="button-secondary" value="Добавить" name="addDeal" id="addDeal">
      </td>
    </tr>
    <tr>
          <td><i style="color:grey;">Удалить</i></td>
<td> <select name="client_deal" id="client_deal">
      <?php 
     $dealsWeHave = $wpdb->get_results("SELECT deal FROM new_values WHERE deal != ''",'ARRAY_A');

      foreach ($dealsWeHave as $val) {
        echo "<option value='" . $val['deal'] . "'>" . $val['deal'] . "</option>";
      }
      ?>
      </select>
   <input type="submit" class="button-secondary del-btn" value="Удалить" name="removeDeal" id="removeDeal">
</td>
    </tr>
    <tr>
      <td>Страна:</td>
      <td><input type="text" placeholder="Россия" name="country" id="country">
          <input type="submit" class="button-secondary" value="Добавить" name="addCountry" id="addCountry">
      </td>
    </tr>
      <tr>
          <td><i style="color:grey;">Удалить</i></td>
<td> <select name="client_country" id="client_country">
      <?php 
     $dealsWeHave = $wpdb->get_results("SELECT country FROM new_values WHERE country != ''",'ARRAY_A');

      foreach ($dealsWeHave as $val) {
        echo "<option value='" . $val['country'] . "'>" . $val['country'] . "</option>";
      }
      ?>
      </select>
   <input type="submit" class="button-secondary del-btn" value="Удалить" name="removeCountry" id="removeCountry">
</td>
    </tr>
    <tr>
      <td>Источник:</td>
      <td><input type="text" placeholder="CITY24" name="source" id="source">
          <input type="submit" class="button-secondary" value="Добавить" name="addSource" id="addSource">
      </td>
    </tr>
        <tr>
          <td><i style="color:grey;">Удалить</i></td>
<td> <select name="client_source" id="client_source">
      <?php 
     $dealsWeHave = $wpdb->get_results("SELECT source FROM new_values WHERE source != ''",'ARRAY_A');

      foreach ($dealsWeHave as $val) {
        echo "<option value='" . $val['source'] . "'>" . $val['source'] . "</option>";
      }
      ?>
      </select>
   <input type="submit" class="button-secondary del-btn" value="Удалить" name="removeSource" id="removeSource">
</td>
    </tr>
    <tr>
      <td>Работник:</td>
      <td><input type="text" placeholder="Ольга" name="worker" id="worker">
          <input type="submit" class="button-secondary" value="Добавить" name="addWorker" id="addWorker">
      </td>
    </tr>
        <tr>
          <td><i style="color:grey;">Удалить</i></td>
<td> <select name="client_worker" id="client_worker">
      <?php 
     $dealsWeHave = $wpdb->get_results("SELECT worker FROM new_values WHERE worker != ''",'ARRAY_A');

      foreach ($dealsWeHave as $val) {
        echo "<option value='" . $val['worker'] . "'>" . $val['worker'] . "</option>";
      }
      ?>
      </select>
   <input type="submit" class="button-secondary del-btn" value="Удалить" name="removeWorker" id="removeWorker">
</td>
    </tr>
  </table>
</div>


<div class="add-new-info">
  <h2>Новый опрос</h2>
<?php
global $wpdb;
$polls = $wpdb->get_results("SELECT * FROM poll_data","ARRAY_A");
$categoriesArray = array();
$namesArray = array();
foreach ($polls as $value) {
  array_push($categoriesArray, $value['poll_category']);
  array_push($namesArray, $value['poll_name']);
}
$categoriesArray = array_unique($categoriesArray);

 ?>

  <div class="line"></div>
<form method="post" action="" name="updatePollData">
<table style="width:450px;">
    <tr>
      <td scope="col" id="name" class="manage-column column-name">Название опроса:</td>
      <td><input type="text" placeholder="" name="poll_name" id="poll_name"></td>
    </tr>
    <tr>
      <td scope="col" id="name" class="manage-column column-name">Категория:</td>
      <td scope="col" id="name" class="manage-column column-name">
        <select id="category-picker" multiple onclick="CopyValues(this, 'theCat');">
        <option value="">Новая...</option>
    <?php  
for ($i=0; $i < sizeof($categoriesArray); $i++) { 
  echo "<option value='" . $categoriesArray[$i] . "'>" . $categoriesArray[$i] . "</option>";
}
    ?>

        </select>
        <input type="text" placeholder="Новая категория" name="poll_category" id="theCat">
      </td>
    </tr>
    <tr>
      <td>Ссылка на форму:
        <i style="text-size:50%; color:grey;">(Должна оканчиваться на id=)</i>
      </td>
      <td><input type="text" placeholder="" name="form-link" id="form-link"></td>
    </tr>
    <tr>
      <td></td>
    <td><input type="submit" value="Добавить" style="float:right;" class="button-secondary" id="newPollData" name="newPollData">
    </td>
    </tr>
  </form>
</table>
<h2>Добавить CSV</h2>
<div class="line"></div>
  <form method="post" action="">

<table>
<tr>
  <td>Опрос:</td>
  <td>
<select name="poll_id">
    <?php  

for ($i=0; $i < sizeof($namesArray); $i++) { 
  echo "<option value='" . $polls[$i]['id'] . "'>" . $polls[$i]['poll_name'] . "</option>";
}
    ?>
        </select>
  </td>
</tr>
<tr>
  <td>Ссылка на CSV</td>
  <td><input type="text" name="csv-link"></td>
</tr>
<tr>
  <td></td>
  <td></td>
  <td><input type="submit" style="float:right;" class="button-secondary" value="Добавить" name="addNewCsv"></td>
</tr>

  </form>
  </table>
</div>


<form method="post" action="" id="changeConfig" name="changeConfig">
<div class="add-new-info">
    <h2>Конфигурация</h2>
  <div class="line"></div>
  <table>
    <tr>
      <td>Ссылка для объектов:</td>
      <td>
        <?php
        $objectLink = $wpdb->get_results("SELECT objects_link FROM config_tzk","ARRAY_A");
        $objectLink = $objectLink[0]['objects_link'];
        ?>
           <input type="text"  id="linkOfObjects" name="linkOfObjects" value="<?php echo $objectLink; ?>">
           <input type="submit" class="button-secondary" value="Изменить" name="changeObjectLink" id="changeObjectLink">
      </td>
    </tr>
  </div>
</form>
<script type="text/javascript">

$('#category-picker').change(function(){
  if($(this).val() == ''){ 
    document.getElementById("theCat").readonly=false;
  }
  else
  {
    document.getElementById("theCat").readonly=true;
  }
});

function CopyValues(oDDL, sTargetId) {
    var arrValues = new Array();
    for (var i = 0; i < oDDL.options.length; i++) {
        var curOption = oDDL.options[i];
        if (curOption.selected)
            arrValues.push(curOption.value);
    }
    document.getElementById(sTargetId).value = arrValues.join("\n");
}

</script>

<?php

}

// ОПРОСНИК 


function tzk_poll_page() {
global $wpdb;
echo "<h1>Опросы</h1><br>";
if (isset($_POST['deletePoll'])) {
     $wpdb->delete('poll_data', array('id' => $_POST['idOfThePoll']));
}
if (isset($_POST['renamePoll'])) {
     
}


if(isset($_POST['watchPollResults'])) {
$query = "SELECT csv_link, poll_name FROM poll_data WHERE id = '" . $_POST['idOfThePoll'] . "'";
$csvFile = $wpdb->get_results($query,"ARRAY_A");
echo "<h2>" . $csvFile[0]['poll_name'] . ": результаты." . "</h2>";
echo "<table class='widefat fixed' cellspacing='0' style='width:90% !important'>\n\n";
 if (is_url_exist($csvFile[0]['csv_link'])) {
        $f = fopen($csvFile[0]['csv_link'], "r");
        $i = 0;
        $j = 1;

        while (($line = fgetcsv($f)) !== false) {
          if ($i == 1) $thereWeGo = ($j - 1);
          if (($thereWeGo != 0) AND isset($_POST['idOfClientToFilter']) AND ($line[$thereWeGo] != $_POST['idOfClientToFilter'])) {
              break;
          }
          else
          {
            $j=0;
                echo "<tr>";
                foreach ($line as $cell) {
                  if(($i == 0) OR ($j != $thereWeGo)) {
                      if($cell != 'id'){
                            echo "<td style='border-left: 1px solid #ddd; border-bottom: 1px solid #ddd;'>" . htmlspecialchars($cell) . "</td>";
                      }
                      else
                      {
                            echo "<td style='border-left: 1px solid #ddd; border-bottom: 1px solid #ddd;'>" . 'Имя отвечающего:' . "</td>";
                      }
                  }
                  else
                  {
                    $nameOfClient = $wpdb->get_results("SELECT client_name FROM table_of_clients WHERE id = $cell","ARRAY_A");
                    echo "<td style='border-left: 1px solid #ddd; border-bottom: 1px solid #ddd;'>" . $nameOfClient[0]['client_name'] . "</td>";
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
}



$polls = $wpdb->get_results("SELECT * FROM poll_data","ARRAY_A");
if ($polls != NULL) {
    $categoriesArray = array();
    $namesArray = array();
    foreach ($polls as $value) {
      array_push($categoriesArray, $value['poll_category']);
      array_push($namesArray, $value['poll_name']);
    }
    $categoriesArray = array_unique($categoriesArray);

?>
<br>
<table class="widefat fixed" cellspacing="0" style="width:40% !important">
<thead>
  <tr>
    <th scope="col" id="name" class="manage-column column-name" style="">Категория</th>
    <th scope="col" id="name" class="manage-column column-name" style="">Опросы</th>
    <th scope="col" id="name" class="manage-column column-name" style="">Действия</th>
  </tr>
</thead>
<tfoot>
  <tr>
    <td scope="col" id="name" class="manage-column column-name" style=""></td>
    <td scope="col" id="name" class="manage-column column-name" style=""></td>
    <td scope="col" id="name" class="manage-column column-name" style=""></td>
  </tr>
</tfoot>
<tbody>
<?php 
for ($i=0; $i < sizeof($polls); $i++) {  ?>
<tr>
  <td><?php echo $polls[$i]['poll_category']; ?></td>
  <td><?php echo $polls[$i]['poll_name']; ?></td>
  <td>  
        <form method="post" action="" id="watchPollResults" name="watchPollResults">                                  
            <input type='hidden' value="<?php echo $polls[$i]['id']; ?>" name="idOfThePoll" id="number">
            <input type='submit' value='Результаты' name='watchPollResults' class='button-secondary' id='watchPollResults' style="float:right; margin:4px;"><br>
        </form>
        <br>
       <form method="post" action="" id="deletePoll" name="deletePoll" onsubmit="return confirm('Вы уверены, что хотите удалить этот опросник?');">                                  
            <input type='hidden' value="<?php echo $polls[$i]['id']; ?>" name="idOfThePoll" id="number">
            <input type='submit' value='Удалить' name='deletePoll' class='button-secondary' id='deletePoll' style="float:right; margin:4px;"><br>
        </form>

      </td>
</tr>

<?php
}
    ?>


    <?php
  echo "</tr>";


?> 
</tbody>

</table>
</form>
<?php




}
else
{
  echo "Нет опросов!";
}



}


 function massEmail_func(){
  global $wpdb;

 $selfpage=$_SERVER['PHP_SELF']; 
   
   
 $action=$_REQUEST['action']; 
if (isset($_POST['iCameFromHistory']))
{
  $action = "sendEmailSend";
}
  // RIP кнопки "купить" и "донат";
 
 switch($action){
  
  case 'sendEmailSend':
   


    $flag=false;


      if($_POST['iCameFromHistory']) {
            $idOfMessage = $_POST['sendID'];
            $theMessage=$wpdb->get_results("SELECT * FROM sent_email_history WHERE id = $idOfMessage",'ARRAY_A');
            $toSendEmail = explode('<br>', $_POST['stringOfNewRecivers']);
            $toSendEmail = array_filter($toSendEmail, 'strlen');
            $flag=false;
            $subject = $theMessage[0]['email_subject'];
            $emailBody = $theMessage[0]['email_text'];
            $mailheaders = $theMessage[0]['email_mailheaders'];
      }   
      else {
        $emailTo= preg_replace('/\s\s+/', ' ', $_POST['emailTo']);
        $toSendEmail=explode(",",$emailTo);
        $emailBodyToStore = $_POST['txtArea'];
        $subject=$_POST['email_subject'];
        $from_name=$_POST['email_From_name'];
        $from_email=$_POST['email_From'];
        $emailBody=$_POST['txtArea'];
        $mailheaders .= "MIME-Version: 1.0\n";
        $mailheaders .= "X-Priority: 1\n";
        $mailheaders .= "Content-Type: text/html; charset=\"UTF-8\"\n";
        $mailheaders .= "Content-Transfer-Encoding: 7bit\n\n";
        $mailheaders .= "From: $from_name <$from_email>" . "\r\n";
        //$mailheaders .= "Bcc: $emailTo" . "\r\n";
      }

    foreach($toSendEmail as $key=>$val){
        $val=trim($val);

        $query = "SELECT * FROM table_of_clients WHERE client_email = " . "'" . $val . "'";
        $clientDb = $wpdb->get_results($query,"ARRAY_A");
        $usernamerep="";
        $useremailrep="";
        $idForLink = "";


        if($clientDb) {
          $usernamerep      = $clientDb[0]['client_name'];          
          $useremailrep     = $clientDb[0]['client_email'];         
          $idForLink        = $clientDb[0]['id'];
        }
        else
        {
          wp_die("Произошла неизвестная ошибка.");
        }

        $emailBody=stripslashes($emailBody);
        $emailBodyToStore = stripcslashes($emailBodyToStore);
        $emailBody=str_replace('[client_name]',$usernamerep,$emailBody); 
        $emailBody=str_replace('[client_email]', $useremailrep, $emailBody);
        $emailBody=str_replace('[client_id]', $idForLink, $emailBody);
        
        
        

        $message='<html><head></head><body>'.$emailBody.'</body></html>';
        $Rreturns=wp_mail($val, $subject, $message, $mailheaders);
        if($Rreturns)
           $flag=true;        
  }         


     $adminUrl=get_admin_url();
     if($flag){


// отправка записи о рассылке в бд в таблицу истории
  if (!$_POST['iCameFromHistory'])
  {
    $wpdb->insert('sent_email_history', array('email_subject' => $subject,
                                              'email_text' => $emailBodyToStore,
                                              'email_recivers' => $emailTo,
                                              'email_mailheaders' => $mailheaders,
                                              'email_sent_date' => date('Y-m-d') 
                                               ));
  }
  else
  {
    $email_reciversUpdate=$wpdb->get_results("SELECT email_recivers FROM sent_email_history WHERE id = $idOfMessage",'ARRAY_A');
foreach ($toSendEmail as $key => $val) {
  $val = trim($val);
  $add = ", " . $val;
  $email_reciversUpdate[0]['email_recivers'] .= $add;
  $idOfMessage = stripcslashes($_POST['sendID']);
}
    $resultOfUpdate = $email_reciversUpdate[0]['email_recivers'];
    $wpdb->update('sent_email_history', array('email_recivers' => $resultOfUpdate), array('id' => $idOfMessage ));
  }

     


        update_option( 'mass_email_succ', 'Email успешно отправлены.' );
        $entrant=$_POST['entrant'];
        $setPerPage=$_POST['setPerPage'];
        
        echo "<script>location.href='".$adminUrl."admin.php?page=Mass-Email&entrant=$entrant&setPerPage=$setPerPage"."';</script>"; 
     
     }
    else{
        
           $entrant=$_POST['entrant'];
           $setPerPage=$_POST['setPerPage'];
       
           update_option( 'mass_email_err', 'Произошла ошибка при попытке отправить email.' );
           echo "<script>location.href='".$adminUrl."admin.php?page=Mass-Email&entrant=$entrant&setPerPage=$setPerPage"."';</script>";
    } 
   break;
       
  case 'sendEmailForm':
   
   $lastaccessto=$_SERVER['QUERY_STRING'];
   parse_str($lastaccessto);
   
   $subscribersSelectedEmails=$_POST['ckboxs'];
   $convertToString=implode(",\n",$subscribersSelectedEmails); 
 ?>    
<br>

<h1>Отправить email клиентам</h1>
<br>  
<?php  $url = plugin_dir_url(__FILE__);
       $urlJS=$url."js/jqueryValidate.js";
       $urlCss=$url."css/styles.css";
 ?>
 <div style="width: 100%;">  
 <div style="float:left;width:70%;" >
 <script src="<?php echo $urlJS; ?>"></script>
 
 <link rel='stylesheet' href='<?php echo $urlCss; ?>' type='text/css' media='all' />

<form name="frmSendEmailsToUserSend" id='frmSendEmailsToUserSend' method="post" action=""> 
<input type="hidden" value="sendEmailSend" name="action"> 
<input type="hidden" value="<?php echo $entrant; ?>" name="entrant"> 
<input type="hidden" value="<?php echo $setPerPage; ?>" name="setPerPage"> 
<table class="form-table" style="width:100%" >
<tbody>
  <tr valign="top" id="subject">
     <th scope="row" style="width:30%;text-align: right;">Тема *</th>
     <td>    
        <input type="text" id="email_subject" name="email_subject"  class="valid" size="70">
        <div style="clear: both;"></div><div></div>
      </td>
   </tr>
   <tr valign="top" id="subject">
     <th scope="row" style="width:30%;text-align: right">Email от имени *</th>
     <td>    
        <input type="text" id="email_From_name" name="email_From_name"  class="valid" size="70">
         <br/>(например, admin)  
        <div style="clear: both;"></div><div></div>
       
      </td>
   </tr>
   <tr valign="top" id="subject">
     <th scope="row" style="width:30%;text-align: right">Email отправителя *</th>
     <td>    
        <input type="text" id="email_From" name="email_From"  class="valid" size="70">
        <br/>(например, admin@yoursite.com) 
        <div style="clear: both;"></div><div></div>
  
      </td>
   </tr>
   <tr valign="top" id="subject">
     <th scope="row" style="width:30%;text-align: right">Email получателей *</th>
     <td>    
        <textarea id='emailTo'  readonly="readonly"  name="emailTo" cols="58" rows="4"><?php echo $convertToString;?></textarea>
        <div style="clear: both;"></div><div></div>
      </td>
   </tr>
   <tr valign="top" id="subject">
     <th scope="row" style="width:30%;text-align: right">Email *</th>
     <td>    
       <div class="wrap">

  <?php

  if(isset($_POST['predefined-email']) AND ($_POST['predefined-email'] != 'new')){
  $query = "SELECT email_text FROM sent_email_history WHERE id = " . $_POST['predefined-email'];
  $email = $wpdb->get_results($query,'ARRAY_A');
  $predefinedContent = $email[0]['email_text'];
  }
  else
  {
  $predefinedContent = "";
  } 

    wp_editor( $predefinedContent, 'textareaid', array( 'textarea_name' => 'txtArea', 'media_buttons' => true ) );
    
  ?>


       <div style="clear: both;"></div><div></div>                       
       </div>
        <input type="hidden" name="editor_val" id="editor_val" />  
        <div style="clear: both;"></div><div></div> 
        <br>

        <div style="font-style: italic; font-size:70%; float:right;">
          <table>
<tr>
  <th>
      Сгенерировать ссылку на опрос:
  </th>
  <td>
      <select multiple="multiple" ondblclick="CopyValues(this, 'linkgen');">
          <?php 

               $listOfPolls = $wpdb->get_results("SELECT id,poll_name FROM poll_data",'ARRAY_A');

                foreach ($listOfPolls as $val) {
                  echo "<option value='" . site_url('/poll.php?id=[client_id]&poll=') . $val['id'] . "'>" . $val['poll_name'] . "</option>";
                }
          ?>
      </select>
  </td>
<td><textarea id="linkgen" readonly placeholder="Дважды кликните по названию опроса слева" style="width:200px; resizable:none; height:90px;"></textarea></td>
</tr>
          </table>
 




                <b>Для вставки данных пользователя используйте:</b>
               <ul>
                    <li>[client_name] – Полное имя;</li>
                    <li>[client_email] – Email;</li>
               </ul>
        </div>
      </td>
   </tr>
   <tr valign="top" id="subject">
     <th scope="row" style="width:30%"></th>
     <td> 
       
       <input type='submit'  value='Отправить' name='sendEmailsend' class='button-secondary' id='sendEmailsend' >  
      </td>
   </tr>
   
</table>

<script type="text/javascript">

 function CopyValues(oDDL, sTargetId) {
    var arrValues = new Array();
    for (var i = 0; i < oDDL.options.length; i++) {
        var curOption = oDDL.options[i];
        if (curOption.selected)
            arrValues.push(curOption.value);
    }
    document.getElementById(sTargetId).value = arrValues.join("\n");
}

   var textBox = document.getElementById("linkgen");
    textBox.onfocus = function() {
        textBox.select();

        // Work around Chrome's little problem
        textBox.onmouseup = function() {
            // Prevent further mouseup intervention
            textBox.onmouseup = null;
            return false;
        };
    };



 jQuery(document).ready(function() {

 jQuery.validator.addMethod("chkCont", function(value, element) {
                      
        
         var editorcontent = CKEDITOR.instances['txtArea'].getData().replace(/<[^>]*>/gi, '');
        if (editorcontent.length){
          return true;
        }
        else{
           return false;
        }
     
                                    
   },
        "Please enter email content"
);

   jQuery("#frmSendEmailsToUserSend").validate({
                    errorClass: "error_admin_massemail",
                    rules: {
                                 email_subject: { 
                                        required: true
                                  },
                                  email_From_name: { 
                                        required: true
                                  },  
                                  email_From: { 
                                        required: true ,email:true
                                  }, 
                                  emailTo:{
                                      
                                     required: true 
                                  },
                                 txtArea:{
                                    required: true 
                                 }  
                            
                       }, 
      
                            errorPlacement: function(error, element) {
                            error.appendTo( element.next().next());
                      }
                      
                 });
                      

  });
 
 </script> 
 </div>
         
 <?php 
  break;
  default: 
         $url=plugin_dir_url(__FILE__);
         $urlCss=$url."css/styles.css";
  ?>
  <div style="width: 100%;">  
        <div style="float:left;width:69%;" >
                                                                                
  <link rel='stylesheet' href='<?php echo $urlCss; ?>' type='text/css' media='all' />   
  
  <?php       
    global $wpdb;
    
    $wpcurrentdir=dirname(__FILE__);
    $wpcurrentdir=str_replace("\\","/",$wpcurrentdir);
    require_once "$wpcurrentdir/Pager/Pager.php";
    
        
    $emails=$wpdb->get_results("SELECT * FROM table_of_clients",'ARRAY_A');
    $totalRecordForQuery=sizeof($emails);
    $selfPage=$_SERVER['PHP_SELF'].'?page=Mass-Email'; 
   
   $params = array(
    'itemData' => $emails,
    'perPage' => 30,
    'delta' => 8,             // for 'Jumping'-style a lower number is better
    'append' => true,
    //'separator' => ' | ',
    'clearIfVoid' => false,
    'urlVar' => 'entrant',
    'useSessions' => true,
    'closeSession' => true,
    'mode'  => 'Sliding',    //try switching modes
    //'mode'  => 'Jumping',

  );


    
    
    $pager = & Pager::factory($params);
    $emails = $pager->getPageData();
    
    $selfpage=$_SERVER['PHP_SELF'];
        
    if($totalRecordForQuery>0){
        
             
             
?>              
  <?php
                $SuccMsg=get_option('mass_email_succ');
                update_option( 'mass_email_succ', '' );
               
                $errMsg=get_option('mass_email_err');
                update_option( 'mass_email_err', '' );
                ?> 
                   
                <?php if($SuccMsg!=""){ echo "<div id='succMsg'>"; echo $SuccMsg; echo "</div>";$SuccMsg="";}?>
                 <?php if($errMsg!=""){ echo "<div id='errMsg' >"; _e($errMsg); echo "</div>";$errMsg="";}?>

                <br>
                <h1>Отправить email клиентам</h1>
                <br>
                  
               <form method="post" action="" id="sendemail" name="sendemail">
                <input type="hidden" value="sendEmailForm" name="action" id="action">
                
              <table class="widefat fixed" cellspacing="0" style="width:97% !important" >
                <thead>
                <tr>
                        <th scope="col" id="name" class="manage-column column-name" style=""><input onclick="chkAll(this)" type="checkbox" name="chkallHeader" id='chkallHeader'>&nbsp;<?php _e('Email');?></th>
                        <th scope="col" id="name" class="manage-column column-name" style=""><?php _e('Имя клиента');?></th>
                         
                </tr>
                </thead>

                <tfoot>
                <tr>
                        <th scope="col" id="name" class="manage-column column-name" style=""><input onclick="chkAll(this)" type="checkbox" name="chkallfooter" id='chkallfooter'>&nbsp;<?php _e('Отметить всех');?></th>
                        <th scope="col" id="name" class="manage-column column-name" style=""></th> 
                        
                </tr>
                </tfoot>

                <tbody id="the-list" class="list:cat">
               <?php                             
                    for($i=0;$i<=$totalRecordForQuery-1;$i++)
                     {
                        
                        if($emails[$i]!=""){ 
                       
                           $userId=$emails[$i]['ID'];
                           $user_info = get_userdata($userId); 
                           echo"<tr class='iedit alternate'>
                            <td  class='name column-name' style='border:1px solid #DBDBDB;padding-left:13px;'><input type='checkBox' name='ckboxs[]'  value='".$emails[$i]['client_email']."'>&nbsp;".$emails[$i]['client_email']."</td>";
                            echo "<td  class='name column-name' style='border:1px solid #DBDBDB;'> ".$emails[$i]['client_name']."</td>";
                         


                            echo "</tr>";
                        }   
                           
                     }
                       
                   ?>  
                 </tbody>       
                </table>
                          <?php
            
                $links = $pager->getLinks();
                $options = array(
                    'autoSubmit' => true,
                   
                );
                $selectBox = $pager->getPerPageSelectBox(10,100,10,false,$options);
                ?>    
                <table>
                  <tr>
                    <td>
                      <?php echo $links['all'];  ?>
                    </td>
                    <td>
                      <b>&nbsp;&nbsp;Per Page : </b>
                      <?php echo $selectBox; ?> &nbsp;
                      
                    </td>
                  </tr>
                </table>
                <table> 
                 
                    <tr>
                    <td class='name column-name' style='padding-top:15px;padding-left:10px;'><input onclick="return validateSendEmailAndDeleteEmail(this)" type='submit' value='Далее' name='sendEmail' class='button-secondary' id='sendEmail' ></td>
                    </tr>
               
                </table>
                </form>  
      
                  
     <?php
                   
      }
     else
      {
             echo '<center><div style="padding-bottom:50pxpadding-top:50px;"><h3>No Email Subscribtion Found</h3></div></center>';
             
      } 
    ?>
  </div>
 
    <?php
     break;
     
  } 
 



?>
 <script type="text/javascript" >





  function chkAll(id){
  
  if(id.name=='chkallfooter'){
  
    var chlOrnot=id.checked;
    document.getElementById('chkallHeader').checked= chlOrnot;
   
  }
 else if(id.name=='chkallHeader'){ 
  
      var chlOrnot=id.checked;
     document.getElementById('chkallfooter').checked= chlOrnot;
  
   }
 
     if(id.checked){
     
          var objs=document.getElementsByName("ckboxs[]");
           
           for(var i=0; i < objs.length; i++)
          {
             objs[i].checked=true;
           
            }

     
     } 
    else {

          var objs=document.getElementsByName("ckboxs[]");
           
           for(var i=0; i < objs.length; i++)
          {
             objs[i].checked=false;
           
            }  
      } 
  } 
  




  function validateSendEmailAndDeleteEmail(idobj){
  
       var objs=document.getElementsByName("ckboxs[]");
       var ischkBoxChecked=false;
       for(var i=0; i < objs.length; i++){
         if(objs[i].checked==true){
         
             ischkBoxChecked=true;
             break;
           }
       
        }  
      
      if(ischkBoxChecked==false)
      {
         if(idobj.name=='sendEmail'){
         alert('Пожалуйста выберите хотя бы одного получателя.')  ;
         return false;
        
         }
      }
     else
       return true; 
        
  } 
  
  </script>

<?php  

}








  // История:

  function massEmail_history(){
  global $wpdb;


if ($_POST['deleteMailFromHistory'])
{
  $wpdb->delete( 'sent_email_history', array( 'id' => $_POST['number'] ) );
}
if ($_POST['sendParticularEmail'])
{

}



    ?>    
            <div class="wrap">
                <br>
                <h1>История рассылок</h1>
                <br>

    <?php
            $query="SELECT * FROM sent_email_history";
            $listOfMessages=$wpdb->get_results($query,'ARRAY_A');
            $totalRecordForQuery=sizeof($listOfMessages);
             if($totalRecordForQuery>0){    
    ?>



 <link rel='stylesheet' href='<?php echo  plugin_dir_url(__FILE__) . "css/styles.css"; ?>' type='text/css' media='all' />

  <table class="widefat fixed" cellspacing="0" style="width:97% !important" >
                <thead>
                <tr>    
                        <th scope="col" id="name" class="manage-column column-name" style="width:10px;"><b>№</b></th>
                        <th scope="col" id="name" class="manage-column column-name" style=""><b>Тема рассылки</b></th>
                        <th scope="col" id="name" class="manage-column column-name" style=""><b>Дата отправления</b></th>
                        <th scope="col" id="name" class="manage-column column-name" style=""><b>Получатели</b></th>
                        <th scope="col" id="name" class="manage-column column-name" style=""><b>Не отправлено</b></th>
                        <th scope="col" id="name" class="manage-column column-name" style="width:100px;"><b>Действия</b></th>
                </tr>
                </thead>

                <tfoot>
                <tr>
                        <th scope="col" id="name" class="manage-column column-name" style="width:3%"></th>
                        <th scope="col" id="name" class="manage-column column-name" style=""></th>
                        <th scope="col" id="name" class="manage-column column-name" style=""></th>
                        <th scope="col" id="name" class="manage-column column-name" style=""></th>
                        <th scope="col" id="name" class="manage-column column-name" style=""></th>
                        <th scope="col" id="name" class="manage-column column-name" style=""></th>
                        
                </tr>
                </tfoot>

                <tbody id="the-list" class="list:cat">
               <?php                             
                    for($i=0;$i<=$totalRecordForQuery-1;$i++)
                     {
                        
                        if($listOfMessages[$i]!=""){
                       
                            echo"<tr class='iedit alternate'>";
                            echo "<td  class='name column-name' style='border:1px solid #DBDBDB; width:3%;'>&nbsp;". ($i+1) ."</td>";
                            echo "<td  class='name column-name' style='border:1px solid #DBDBDB; padding-left:13px;'>&nbsp;".$listOfMessages[$i]['email_subject']."</td>";
                            echo "<td  class='name column-name' style='border:1px solid #DBDBDB;'> ".$listOfMessages[$i]['email_sent_date']."</td>";
                        $hiArray = explode(", ",$listOfMessages[$i]['email_recivers']);   
                        $emailReciversString="";
                          foreach ($hiArray as $val) { 
                                    $emailReciversString .= $val . '<br>';                  
                                  }          
                            echo "<td  class='name column-name' style='border:1px solid #DBDBDB;'> ".$emailReciversString."</td>";
                              global $wpdb;
                        $arrayOfAllEmails = $wpdb->get_results("SELECT client_email from table_of_clients",'ARRAY_N');
// Вордпресс получает данные из бд в виде трехмерного массива, в первом столбце котором – ключ, а во втором – ещё один массив с ключом и заветным email.
// Поэтому идём на хитрости:
                          $counter = 0;
                          $wpArray = array();
                            foreach ($arrayOfAllEmails as $val) {
                              $wpArray[$counter] = $val[0];
                              $counter += 1;
                            }

                            $emailsThatWerentUsed = array_values(array_diff($wpArray, $hiArray));

                            $emailsThatWerentUsedString = "";
                            if ($emailsThatWerentUsed != NULL) {
                                  foreach ($emailsThatWerentUsed as $val) { 
                                    $emailsThatWerentUsedString .= $val . '<br>';
                                    $showSendButton = true;                     
                                  }
                            }
                            else
                            {
                                    $emailsThatWerentUsedString = "<i>Рассылка была отправлена всем.</i>";
                                    $showSendButton = false;
                            }

                            echo "<td  class='name column-name' style='border:1px solid #DBDBDB;'>" . $emailsThatWerentUsedString; 
                            ?>
                            </td>
                            <td  class='name column-name' style='border:1px solid #DBDBDB;'>
                              <form method="post" action="<?php echo plugin_dir_url(__FILE__) . 'messageview.php'?>" target="_blank" id="mailManager" name="mailManager">
                                  <input type='hidden' value=<?php echo '"' . $listOfMessages[$i]['id'] . '"'; ?> name="sendID" id="sendID">
                                  <input type='submit'  value='Посмотреть' name='sendParticularEmail' class='button-secondary' id='sendParticularEmail' style="float:right; margin:4px;"><br> 
                              </form>

                                           <?php if($showSendButton){ ?>
                              <form method="post" action="admin.php?page=Mass-Email" id="mailManager" name="mailManager" onsubmit="return confirm('Вы уверены, что хотите разослать эту рассылку всем, кому она не была отправлена?');">
                                  <input type='hidden' value=<?php echo '"' . $listOfMessages[$i]['id'] . '"'; ?> name="sendID" id="sendID">
                                  <input type='hidden' value="<?php echo $emailsThatWerentUsedString; ?>" name="stringOfNewRecivers" id="stringOfNewRecivers">
                                  <input type='submit' value='Разослать' name='iCameFromHistory' class='button-secondary' id='iCameFromHistory' style='float:right; margin:4px;'><br>
                              </form>                     

                                           <?php  } ?>

                              <form method="post" action="" id="deleteRow" name="deleteRow" onsubmit="return confirm('Вы уверены, что хотите удалить эту рассылку из истории?');">
                                  <input type='hidden' value=<?php echo '"' . $listOfMessages[$i]['email_subject'] . '"'; ?> name="subj" id="subj">                                  
                                  <input type='hidden' value=<?php echo '"' . $listOfMessages[$i]['id'] . '"'; ?> name="number" id="number">
                                  <input type='submit' value='Удалить' name='deleteMailFromHistory' class='button-secondary' id='deleteMailFromHistory' style="float:right; margin:4px;"><br>
                              </form>
                             </td>
                           </tr>
                             <?php
                        }   
                           
                     }
                       
                   ?>  
                 </tbody>       
                </table>
           </div>


         <?php 

           }
               else
           {
               echo '<center><div style="padding-bottom:50pxpadding-top:50px;"><h1 style="color:red;">Нет истории!</h1></div></center>';                              
           } 

          ?>           


 <?php   
  }

// :яиротсИ