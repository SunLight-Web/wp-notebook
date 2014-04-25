<?php

require_once( $_SERVER['DOCUMENT_ROOT'] . '/wp-config.php' );
require_once( $_SERVER['DOCUMENT_ROOT'] . '/wp-includes/wp-db.php' );
if (!$wpdb) {
    $wpdb = new wpdb( DB_USER, DB_PASSWORD, DB_NAME, DB_HOST);
} else {
    global $wpdb;
}
$idOfMessage = $_GET['id'];
if (!isset($idOfMessage)) $idOfMessage = $_POST['sendID'];
$message = $wpdb->get_results("SELECT email_text, email_subject, email_recivers FROM sent_email_history WHERE id = $idOfMessage",'ARRAY_A');
$subject =  $message[0]['email_subject'];
$email_recivers = $message[0]['email_recivers'];
$message =  $message[0]['email_text'];

?>
<!DOCTYPE HTML>
<html lang="ru/en">
<head>
	<link rel='stylesheet' href='<?php echo  plugin_dir_url(__FILE__) . "css/styles.css"; ?>' type='text/css' media='all' />
	<meta http-equiv="content-type" content="text/html; charset=utf-8">
<title><?php echo "Отправленное email по теме " . $subject; ?></title>
</head>
<body class="messageview">
	<div class="address_messageview">Сообщение было отправленно: </br><span><?php echo $email_recivers; ?></span>.</div>
	<h3>Текст сообщения:</h3>
	<div class="message_messageview"><?php echo $message; ?></div>
</body>
</html>