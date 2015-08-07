<?php

$ans=array();
$ans['title']='Общая проверка';



$res=infra_loadJSON('*autoedit/autoedit.php?type=editfile&id=*.config.json');
if (!$res['result'] || !$res['isfile']) {
	return infra_err($ans, 'Неудалось получить информацию о файле .config.json');
}



$res=infra_loadJSON('*autoedit/autoedit.php?type=editfolder&id=*');

if (!$res['result'] || !sizeof($res['list'])) {
	return infra_err($ans, 'Неудалось прочитать папку');
}



return infra_ret($ans, 'Вроде ок, прочитали папку, посмотрели файл');
