<?php
namespace infrajs\autoedit;
use infrajs\load\Load;
use infrajs\access\Access;
use infrajs\ans\Ans;

if (!is_file('vendor/autoload.php')) {
	chdir('../../../');
	require_once('vendor/autoload.php');
}

$ans=array();
$ans['title']='Общая проверка';
Access::test(true);


$res=Load::loadJSON('*autoedit/autoedit.php?type=editfile&id=*.infra.json');

if (Access::admin()) {
	if (!$res['result'] || !$res['isfile']) {
		return Ans::err($ans, 'Неудалось получить информацию о файле .infra.json');
	}
} else {
	if (!$res||$res['result']) {
		return Ans::err($ans, 'Неудалось обратиться за файлом .infra.json');
	}
}


$res=Load::loadJSON('*autoedit/autoedit.php?type=editfolder&id=~');

if (Access::admin()) {
	if (!$res['result'] || !sizeof($res['list'])) {
		return Ans::err($ans, 'Неудалось прочитать папку');
	}
} else {
	if (!$res||$res['result']) {
		return Ans::err($ans, 'Неудалось обратиться к папке');
	}
}


return Ans::ret($ans, 'Вроде ок, прочитали папку, посмотрели файл');
