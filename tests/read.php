<?php

$ans=array();
$ans['title']='Общая проверка';
infra_test(true);


$res=infra_loadJSON('*autoedit/autoedit.php?type=editfile&id=*.config.json');

if (infra_admin()) {
	if (!$res['result'] || !$res['isfile']) {
		return infra_err($ans, 'Неудалось получить информацию о файле .config.json');
	}
} else {
	if (!$res||$res['result']) {
		return infra_err($ans, 'Неудалось обратиться за файлом .config.json');
	}
}


$res=infra_loadJSON('*autoedit/autoedit.php?type=editfolder&id=*');

if (infra_admin()) {
	if (!$res['result'] || !sizeof($res['list'])) {
		return infra_err($ans, 'Неудалось прочитать папку');
	}
} else {
	if (!$res||$res['result']) {
		return infra_err($ans, 'Неудалось обратиться к папке');
	}
}


return infra_ret($ans, 'Вроде ок, прочитали папку, посмотрели файл');
