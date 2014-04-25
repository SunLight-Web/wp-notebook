<?php 
$pollname = "pollN1"; 
$textOfQuestions = array(
"Оцените общее впечатление от оказанных Вам услуг:",
"Оцените качество первоначальной информации об объектах, описания и фотографий объектов на сайтах, порталах и журналах. Ее доступность и понятность:",
"Оцените организацию просмотров:",
"Оцените впечатление от просмотра/ов:",
"Порекомендуете ли Вы Property Selection своим друзьям и знакомым:",
"Комментарии:",
"id");
?>


<!DOCTYPE html>
<html class="embed safari">
<head>
<link rel="stylesheet" type="text/css" href="../css/styles.css">
<meta charset="utf-8">
<title>
<?php echo $pollname; ?>
</title>
</head>
<body>



<?php 

if($_GET['id'] == 'test'){
	echo "<i style='color:red;'>(Опрос запущен в тестовом режиме, отправленные данные сохраняться не будут.)</i>";
}

if($_GET['id'] == 'init'){

$pathToFile = 'polldata/' . $pollname . ".csv";
    $handle = fopen($pathToFile, "w");
    fputcsv($handle, $textOfQuestions);
    fclose($handle);
    echo "CSV файл был успешно создан! Он находится по адресу " . site_url('/wp-content/plugins/main-table/polldata/') . $pollname . ".csv";
   	echo "<br>";
    echo "Пожалуйста, добавьте эту ссылку в контрольной панели WordPress в меню таблицы – параметры.";
    die();
}



if($_POST['submit-n1-poll'] AND ($_GET['id'] != 'test')) {
$question6withoutCommas = str_replace(',', '&#44;', $_POST['question6']);
$answers = array($_POST['question1'], $_POST['question2'], $_POST['question3'], $_POST['question4'], $_POST['question5'], $question6withoutCommas, $_POST['id_of_client']);
$pathToFile = 'polldata/' . $pollname . ".csv";
    $handle = fopen($pathToFile, "a");
    fputcsv($handle, $answers);
    fclose($handle);
    echo "Спасибо за ответы!";
    die();
}

?>



	<p>Компания Prsoperty Selection не стоит на месте. Мы всегда стремимся улучшить Наш сервис. Именно поэтому мы просим Вас уделить несколько минут и оставить свой комментарий о Нашей работе.</p>
	<p>Оцените нашу работу по шакале от 1 до 5, где 1 – это плохо, а 5 – отлично.</p>
<form method="post" action="">

<table>
<tr>
	<th>
		<?php echo $textOfQuestions[0]; ?>
	</th>
</tr>
<tr>
	<td>
		<input type="radio" name="question1" value="1">
		<input type="radio" name="question1" value="2">
		<input type="radio" name="question1" value="3">
		<input type="radio" name="question1" value="4">
		<input type="radio" name="question1" value="5">
	</td>
</tr>
<tr>
	<th>
		<?php echo $textOfQuestions[1]; ?>
	</th>
</tr>
<tr>
	<td>
		<input type="radio" name="question2" value="1">
		<input type="radio" name="question2" value="2">
		<input type="radio" name="question2" value="3">
		<input type="radio" name="question2" value="4">
		<input type="radio" name="question2" value="5">
	</td>
</tr>

<tr>
	<th>
		<?php echo $textOfQuestions[2]; ?>
	</th>
</tr>
<tr>
	<td>
		<input type="radio" name="question3" value="1">
		<input type="radio" name="question3" value="2">
		<input type="radio" name="question3" value="3">
		<input type="radio" name="question3" value="4">
		<input type="radio" name="question3" value="5">
	</td>
</tr>
<tr>
	<th>
		<?php echo $textOfQuestions[3]; ?>		
	</th>
</tr>
<tr>
	<td>
		<input type="radio" name="question4" value="1">
		<input type="radio" name="question4" value="2">
		<input type="radio" name="question4" value="3">
		<input type="radio" name="question4" value="4">
		<input type="radio" name="question4" value="5">
	</td>
</tr>
<tr>
	<th>
		<?php echo $textOfQuestions[4]; ?>
	</th>
</tr>
<tr>
	<td>
		<input type="radio" name="question5" value="1">
		<input type="radio" name="question5" value="2">
		<input type="radio" name="question5" value="3">
		<input type="radio" name="question5" value="4">
		<input type="radio" name="question5" value="5">
	</td>
</tr>
<tr>
	<th>
		<?php echo $textOfQuestions[5]; ?>
	</th>
</tr>
<tr>
	<td>
		<textarea name="question6"></textarea>
	</td>
</tr>
<input type="hidden" name="id_of_client" value='<?php echo $_GET['id']; ?>'>
<tr>
	<td>
		<input type="submit" name="submit-n1-poll"> 
	</td>
</tr>
</table>
</form>

</body>
</html>