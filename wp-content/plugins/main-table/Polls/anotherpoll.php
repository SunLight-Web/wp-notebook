


<!DOCTYPE html>
<html class="embed safari">
<head>
<link rel="stylesheet" type="text/css" href="../css/styles.css">
<meta charset="utf-8">
<title>
<?php echo $pollname; ?>
</title>
</head>
<body class="poll_body">

<?php 
$pollname = "pollN2"; 
$textOfQuestions = array(
"Вопрос №1:",
"Вопрос №2:",
"Вопрос №3:",
"Вопрос №4:",
"Вопрос №5:",
"Комментарии:",
"id");
?>












<?php 
if($_GET['id'] == 'init'){



$pathToFile = 'polldata/' . $pollname . ".csv";
    $handle = fopen($pathToFile, "w");
    fputcsv($handle, $textOfQuestions);
    fclose($handle);
    echo "CSV файл был успешно создан! Он находится по адресу " . "http://dev.web-sunlight.com/wp-content/plugins/main-table/Polls/polldata/" . $pollname . ".csv";
   	echo "<br>";
    echo "Пожалуйста, добавьте эту ссылку в контрольной панели WordPress в меню таблицы – параметры.";
    die();
}

if($_POST['submit-n1-poll']) {

$answers = array($_POST['question1'], $_POST['question2'], $_POST['question3'], $_POST['question4'], $_POST['question5'], $_POST['question6'], $_POST['id_of_client']);
$pathToFile = 'polldata/' . $pollname . ".csv";
    $handle = fopen($pathToFile, "a");
    fputcsv($handle, $answers);
    fclose($handle);
    echo "Благодарим за ваш ответ!";
    die();
}


?>

<form method="post" action="">

<table class="polls_table">
	<tr>
		<td class="td_border_bottom">
	<p>Компания Prsoperty Selection не стоит на месте. Мы всегда стремимся улучшить Наш сервис. Именно поэтому мы просим Вас уделить несколько минут и оставить свой комментарий о Нашей работе.</p>
	<p>Оцените нашу работу по шакале от 1 до 5, где 1 – это плохо, а 5 – отлично.</p>
	</td>
	</tr>
</tr>
	<th class="th_text">
		<?php echo $textOfQuestions[0]; ?>
	</th>
</tr>
<tr>
	<td class="td_border_bottom">
		1<input type="radio" name="question1" value="1">
		2<input type="radio" name="question1" value="2">
		3<input type="radio" name="question1" value="3">
		4<input type="radio" name="question1" value="4">
		5<input type="radio" name="question1" value="5">
	</td>

	

</tr>
	<th class="th_text">
		<?php echo $textOfQuestions[1]; ?>
	</th>
</tr>
<tr>
	<td class="td_border_bottom">
		1<input type="radio" name="question2" value="1">
		2<input type="radio" name="question2" value="2">
		3<input type="radio" name="question2" value="3">
		4<input type="radio" name="question2" value="4">
		5<input type="radio" name="question2" value="5">
	</td>
</tr>

<tr>
	<th class="th_text">
		<?php echo $textOfQuestions[2]; ?>
	</th>
</tr>
<tr>
	<td class="td_border_bottom">
		1<input type="radio" name="question3" value="1">
		2<input type="radio" name="question3" value="2">
		3<input type="radio" name="question3" value="3">
		4<input type="radio" name="question3" value="4">
		5<input type="radio" name="question3" value="5">
	</td>
</tr>
<tr>
	<th class="th_text">
		<?php echo $textOfQuestions[3]; ?>		
	</th>
</tr>
<tr>
	<td class="td_border_bottom">
		1<input type="radio" name="question4" value="1">
		2<input type="radio" name="question4" value="2">
		3<input type="radio" name="question4" value="3">
		4<input type="radio" name="question4" value="4">
		5<input type="radio" name="question4" value="5">
	</td>
</tr>
<tr>
	<th class="th_text">
		<?php echo $textOfQuestions[4]; ?>
	</th>
</tr>
<tr>
	<td class="td_border_bottom">
		1<input type="radio" name="question5" value="1">
		2<input type="radio" name="question5" value="2">
		3<input type="radio" name="question5" value="3">
		4<input type="radio" name="question5" value="4">
		5<input type="radio" name="question5" value="5">
	</td>
</tr>
<tr>
	<th class="th_textarea">
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
		<input type="submit" name="submit-n1-poll" class="input_button"> 
	</td>
</tr>
</table>
</form>


</body>
</html>