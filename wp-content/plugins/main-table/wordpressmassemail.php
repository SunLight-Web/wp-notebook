<?php 
 
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
   

    $emailTo= preg_replace('/\s\s+/', ' ', $_POST['emailTo']);
    $toSendEmail=explode(",",$emailTo);
    $flag=false;
    foreach($toSendEmail as $key=>$val){
        $val=trim($val);
        
        $subject=$_POST['email_subject'];
        $from_name=$_POST['email_From_name'];
        $from_email=$_POST['email_From'];
        $emailBody=$_POST['txtArea'];
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
  var_dump($clientDb);
  wp_die("Произошла ошибка.");
}

        $emailBody=stripslashes($emailBody);
// и тут:
        $emailBody=str_replace('[client_name]',$usernamerep,$emailBody); 
        $emailBody=str_replace('[client_email]', $useremailrep, $emailBody);
        $emailBody=str_replace('[client_id]', $idForLink, $emailBody);
        
        
        
        $mailheaders .= "MIME-Version: 1.0\n";
        $mailheaders .= "X-Priority: 1\n";
        $mailheaders .= "Content-Type: text/html; charset=\"UTF-8\"\n";
        $mailheaders .= "Content-Transfer-Encoding: 7bit\n\n";
        $mailheaders .= "From: $from_name <$from_email>" . "\r\n";
        //$mailheaders .= "Bcc: $emailTo" . "\r\n";
        $message='<html><head></head><body>'.$emailBody.'</body></html>';
      echo $val;
        $Rreturns=wp_mail($val, $subject, $message, $mailheaders);
        if($Rreturns)
           $flag=true;        
  }  

      if($_POST['iCameFromHistory'])
  {
            $idOfMessage = $_POST['sendID'];
            $theMessage=$wpdb->get_results("SELECT * FROM sent_email_history WHERE id = $idOfMessage",'ARRAY_A');
            $emailTo = explode('<br>', $_POST['stringOfNewRecivers']);
            $flag=false;
            $subject = $theMessage[0]['email_subject'];
            $message = $theMessage[0]['email_text'];
            $mailheaders = $theMessage[0]['email_mailheaders'];
        foreach($emailTo as $key=>$val){
            $val=trim($val);
            $Rreturns=wp_mail($val, $subject, $message, $mailheaders);
            if($Rreturns)
            $flag=true; 
        }
  }           

// иницилизация таблицы истории в бд

     $adminUrl=get_admin_url();
     if($flag){

        if($wpdb->get_var("show tables like sent_email_history") != 'sent_email_history') 
  {
    $sql = "CREATE TABLE sent_email_history (
    id mediumint(9) NOT NULL AUTO_INCREMENT,
    email_subject text COLLATE utf8_general_ci NOT NULL,
    email_text text COLLATE utf8_general_ci NOT NULL,
    email_recivers text COLLATE utf8_general_ci NOT NULL,
    email_mailheaders text COLLATE utf8_general_ci NOT NULL,
    email_sent_date date NOT NULL,
    UNIQUE KEY id (id)
    );";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
  }

// отправка записи о рассылке в бд в таблицу истории
  if (!$_POST['iCameFromHistory'])
  {
    $wpdb->insert('sent_email_history', array('email_subject' => $subject,
                                              'email_text' => $message,
                                              'email_recivers' => $emailTo,
                                              'email_mailheaders' => $mailheaders,
                                              'email_sent_date' => date('Y-m-d') 
                                               ));
  }
  else
  {
    $email_reciversUpdate=$wpdb->get_results("SELECT email_recivers FROM sent_email_history WHERE id = $idOfMessage",'ARRAY_A');

foreach ($emailTo as $key => $val) {
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
       $urlCss=$url."styles.css";
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
    wp_editor( '', 'content-id', array( 'textarea_name' => 'txtArea', 'media_buttons' => true ) );
    
  ?>

       <div style="clear: both;"></div><div></div>                       
       </div>
        <input type="hidden" name="editor_val" id="editor_val" />  
        <div style="clear: both;"></div><div></div> 
        <br>
        <div style="font-style: italic; font-size:70%;">
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
       
       <input type='submit'  value='Отправить' name='sendEmailsend' class='button-primary' id='sendEmailsend' >  
      </td>
   </tr>
   
</table>

<script type="text/javascript">


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
         $urlCss=$url."styles.css";
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

              <!-- Deleted shit here -->
                <br>
                <?php   var_dump($clientDb); ?>
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
                    <td class='name column-name' style='padding-top:15px;padding-left:10px;'><input onclick="return validateSendEmailAndDeleteEmail(this)" type='submit' value='Далее' name='sendEmail' class='button-primary' id='sendEmail' ></td>
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
        else if(idobj.name=='deleteSubscriber') 
         {
            alert('Please select atleast one email to delete.')  
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
                              <form method="post" action="<?php echo plugin_dir_url(__FILE__) . 'messageview.php'?>" id="mailManager" name="mailManager">
                                  <input type='hidden' value=<?php echo '"' . $listOfMessages[$i]['id'] . '"'; ?> name="sendID" id="sendID">
                                  <input type='submit'  value='Посмотреть' name='sendParticularEmail' class='button-primary' id='sendParticularEmail' style="float:right; margin:4px;"><br> 
                              </form>

                                           <?php if($showSendButton){ ?>
                              <form method="post" action="admin.php?page=Mass-Email" id="mailManager" name="mailManager" onsubmit="return confirm('Вы уверены, что хотите разослать эту рассылку всем, кому она не была отправлена?');">
                                  <input type='hidden' value=<?php echo '"' . $listOfMessages[$i]['id'] . '"'; ?> name="sendID" id="sendID">
                                  <input type='hidden' value="<?php echo $emailsThatWerentUsedString; ?>" name="stringOfNewRecivers" id="stringOfNewRecivers">
                                  <input type='submit' value='Отправить' name='iCameFromHistory' class='button-primary' id='iCameFromHistory' style='float:right; margin:4px;'><br>
                              </form>                     

                                           <?php  } ?>

                              <form method="post" action="" id="deleteRow" name="deleteRow" onsubmit="return confirm('Вы уверены, что хотите удалить эту рассылку из истории?');">
                                  <input type='hidden' value=<?php echo '"' . $listOfMessages[$i]['email_subject'] . '"'; ?> name="subj" id="subj">                                  
                                  <input type='hidden' value=<?php echo '"' . $listOfMessages[$i]['id'] . '"'; ?> name="number" id="number">
                                  <input type='submit' value='Удалить' name='deleteMailFromHistory' class='button-primary' id='deleteMailFromHistory' style="float:right; margin:4px;"><br>
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



  ?>