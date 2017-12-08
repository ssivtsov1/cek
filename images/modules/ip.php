<link rel="stylesheet" type="text/css" href="./images/modules/mystyles.css">
<script src="./images/modules/script.js" type="text/javascript"></script>

<?php

echo "<form method='post' action=".$_SERVER['PHP_SELF'].">";
mb_internal_encoding("UTF-8");
$servername = "localhost";
$username = "prozoro";
$password = "58QtmQ6nXqMcTjtT";
$dbname = "db";
ini_set('display_errors',1);
ini_set('error_reporting',2047);
Header("Content-Type: text/html; charset=UTF-8");
$sort='%';
$sort1='%';
$sort2='%';
$sort3='%';
$sort4='0'; //Очікувана вартість > (грн.)
$sort5='%';
$sort6='%';
$sort7='%';
$sort9='10000000'; //Очікувана вартість < (грн.)
$sort8='`Номер закупівлі` DESC';
if (!empty($_POST['search_id'])) $sort = $_POST['search_id'];
if (!empty($_POST['select_typ'])) $sort1 = $_POST['select_typ'];
if (!empty($_POST['search_DK'])) $sort2 = $_POST['search_DK'];
if (!empty($_POST['search_NAZVA'])) $sort3 = $_POST['search_NAZVA'];
if (!empty($_POST['search_VARTIST'])) $sort4 = $_POST['search_VARTIST'];
if (!empty($_POST['search_VARTIST'])) $sort9 = $_POST['search_VARTIST_MAX'];
if (!empty($_POST['search_KONTAKT'])) $sort5 = $_POST['search_KONTAKT'];
if (!empty($_POST['search_DATASTART'])) $sort6 = $_POST['search_DATASTART'];
if (!empty($_POST['select_num'])) $sort8 = $_POST['select_num'];

echo "<h1 align='center'><hr />";
echo "<font size='4pt' onclick='showMessage();'>Вкажіть опції пошуку та натисніть кнопку: </font><br />";
echo "<table align='center' border='1'>
                <tr><th>Номер<br />закупівлі</th>
                <th>Тип<br />закупівлі</th>
                <th>ДК-021:2015</th>
                <th>Назва<br />закупівлі</th>
                <th>Очікувана<br />вартість<br />більше<br />(грн.)</th>
                <th>Очікувана<br />вартість<br />меньше<br />(грн.)</th>
                <th>Контактна<br />особа</th>
                <th>Орієнтовний<br />початок<br />проведення<br />закупівлі</th></tr>";
echo "<tr><td align='center'><input type='text' name='search_id' value='' size=30 /></td>";
#echo "<td><input type='text' name='search' value='".$textval1."' size=30 /></td>";      
echo "<td align='center'><select id=selectid name=select_typ>
    <option value=%>Всі</option> 
    <option value=товари>Товари</option>    
    <option value=роботи>Роботи</option>
    <option value=послуги>Послуги</option></td></select>";
echo "<td align='center'><input type='text' name='search_DK' value='' size=30 /></td>";
echo "<td align='center'><input type='text' name='search_NAZVA' value='' size=30 /></td>";
echo "<td align='center'><input type='text' name='search_VARTIST' value='".$sort4."' size=30 /></td>";
echo "<td align='center'><input type='text' name='search_VARTIST_MAX' value='".$sort9."' size=30 /></td>";
echo "<td align='center'><input type='text' name='search_KONTAKT' value='' size=30 /></td>";
echo "<td align='center'><input type='text' name='search_DATASTART' value='' size=30 /></td></tr>";
echo "</table>";

echo "<select id=select_num name=select_num>
    <option value='`Номер закупівлі`DESC'>Сортувати за спаданням номеру закупівлі</option> 
    <option value='`Номер закупівлі`'>Сортувати за зростанням номеру закупівлі</option>
    <option value='`Очікувана вартість (грн.)`'>Сортувати за зростанням ціни закупівлі</option> 
    <option value='`Очікувана вартість (грн.)` DESC'>Сортувати за спаданням ціни закупівлі</option>
    </select>&nbsp&nbsp";

echo "<input type=submit class='btn-style' name=send value='Виконати пошук' onClick='location.href=".$_SERVER['PHP_SELF']."'>";
echo "<hr /></h1>";

$sql_POT = "select `Номер закупівлі`,`Тип закупівлі`,`ДК-021:2015`,`Назва закупівлі`,
`Очікувана вартість (грн.)`,`Контактна особа`,`Пов’язані документи`,`Орієнтовний початок проведення закупівлі`,
`Посилання на prozorro`
from db.godovaya where (`Номер закупівлі` like '".$sort."')AND(`Тип закупівлі` like '%".$sort1."%')"
. "AND(`ДК-021:2015` like '%".$sort2."%')AND(`Назва закупівлі` like '%".$sort3."%')"
. "AND(`Очікувана вартість (грн.)` > '".$sort4."')AND(`Очікувана вартість (грн.)` < '".$sort9."')AND(`Контактна особа` like '%".$sort5."%')"
. "AND(`Орієнтовний початок проведення закупівлі` like '%".$sort6."%') ORDER BY ".$sort8." limit 50";

$sql_IP = "select `Номер закупівлі`,`Тип закупівлі`,`ДК-021:2015`,`Назва закупівлі`,
`Очікувана вартість (грн.)`,`Контактна особа`,`Пов’язані документи`,`Орієнтовний початок проведення закупівлі`,
`Посилання на prozorro`
from db.invest where (`Номер закупівлі` like '".$sort."')AND(`Тип закупівлі` like '%".$sort1."%')"
. "AND(`ДК-021:2015` like '%".$sort2."%')AND(`Назва закупівлі` like '%".$sort3."%')"
. "AND(`Очікувана вартість (грн.)` > '".$sort4."')AND(`Очікувана вартість (грн.)` < '".$sort9."')AND(`Контактна особа` like '%".$sort5."%')"
. "AND(`Орієнтовний початок проведення закупівлі` like '%".$sort6."%') ORDER BY ".$sort8." limit 50";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$result = $conn->query($sql_IP);
echo "<table align='center' border='1'>
                <tr><th>Номер<br />закупівлі</th>
                <th>Тип<br />закупівлі</th>
                <th>ДК-021:2015</th>
                <th>Назва<br />закупівлі</th>
                <th>Очікувана<br />вартість<br />(грн.)</th>
                <th>Контактна<br />особа</th>
                <th>Пов’язані<br />документи</th>
                <th>Орієнтовний<br />початок<br />проведення<br />закупівлі</th>
                <th>Посилання<br />на сайт<br />prozorro</th></tr>";
 
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
 if ( $row["Посилання на prozorro"] > '') {
        
 echo "<tr><td align=center>".$row["Номер закупівлі"]."</td><td align='center'>".$row["Тип закупівлі"].
      "</td><td align='center'>".$row["ДК-021:2015"]."</td><td align='center'>".$row["Назва закупівлі"].
      "</td><td align='center'>".$row["Очікувана вартість (грн.)"]."</td><td align='center'>".$row["Контактна особа"].
      "</td><td align='center'><a href=".$row["Пов’язані документи"].">Завантажити</td>".
      "<td align='center'>".$row["Орієнтовний початок проведення закупівлі"]."</td>".
     "<td align='center'><a href=".$row["Посилання на prozorro"].">Посилання</td></tr>";
 }
 else {
     echo "<tr><td align=center>".$row["Номер закупівлі"]."</td><td align='center'>".$row["Тип закупівлі"].
      "</td><td align='center'>".$row["ДК-021:2015"]."</td><td align='center'>".$row["Назва закупівлі"].
      "</td><td align='center'>".$row["Очікувана вартість (грн.)"]."</td><td align='center'>".$row["Контактна особа"].
      "</td><td align='center'><a href=".$row["Пов’язані документи"].">Завантажити</td>".
      "<td align='center'>".$row["Орієнтовний початок проведення закупівлі"]."</td>".
     "<td align='center'><a href=".$row["Посилання на prozorro"]."></td></tr>";
 }
 
    }   
echo "</tr></table>";
}
else {
    #echo $sql_IP;
    echo "</tr></table>";
    echo "<p align='center'><font size='4pt'>Не знайдено інформації по вашому запиту</p>";
}
$conn->close();
?>