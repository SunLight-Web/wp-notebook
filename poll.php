
<!DOCTYPE HTML>
<html lang="ru/en">
<head>
	<meta http-equiv="content-type" content="text/html charset=utf-8">
<title><?php echo $subject; ?></title>

</head>
<body>
<style type="text/css">
body {
	width: 100%;
	height: 100%;
	padding: 0;
	margin: 0;
	background: #E8EEF7;
}
	.container_poll {
		height: 100%;
		width: 960px;
		margin: 0 auto;
		background: #E8EEF7;
	}
	.div_iframe {
		left: 50%;
	}
	.poll_iframe_title {
		
	}
	.poll_iframe_title h1 {
		font-family: Arial,sans-serif;
		text-align: center;
		padding: 0 165px;
	}
	.poll_iframe_title p {
		font-family: Arial,sans-serif;
		text-align: center;
		padding: 0 165px;
	}
	iframe {
		left: -380px;
	}
</style>

<?php


define( 'SHORTINIT', true );
require_once('wp-load.php' );

global $wpdb;



	if ((isset($_GET['id']) AND $_GET['id']!="") AND (isset($_GET['poll']) AND $_GET['poll']!="")){
		$idOfUser = $_GET['id'];

		$query = "SELECT client_name FROM table_of_clients WHERE id = $idOfUser";
		$userArray = $wpdb->get_results($query,'ARRAY_A');


		$query = "SELECT * FROM poll_data WHERE id = " . $_GET['poll'];
		$pollArray = $wpdb->get_results($query,'ARRAY_A');


		$poll_link = $pollArray[0]['poll_link'] . $idOfUser;


?>
		<div class="container_poll">
			<div class="div_iframe">
				<div class="poll_iframe_title">
					<h1>Опрос</h1>
					<p>Уважаемый <?php echo $userArray[0]['client_name']; ?>! Пожалуйста, заполните данную форму:</p>
				</div>
				<center>
					<table>
						<tr>
							<td>
								<iframe src='<?php echo $poll_link; ?>' width="760" height="1000" frameborder="0" marginheight="0" marginwidth="0">Загрузка...</iframe>
							</td>
						</tr>
					<table>
				</center>			
			</div>
		</div>
<?	}
else 
{
	echo "<div><h1 style='color:red'>ПРОИЗОШЛА ОШИБКА, ПОЖАЛУЙСТА ПОВТОРИТЕ ПОПЫТКУ</h1></div>";
}

?>

</body>
</html>