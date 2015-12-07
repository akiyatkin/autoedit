<?php

function autoedit_theme($isrc)
{
	$src = infra_admin_cache('autoedit_theme', function ($isrc) {
		$src = Path::theme($isrc);
		if ($src) return $src;
		$fdata = Load::srcInfo($isrc);
		$folder = Path::theme($fdata['folder']);
		if (!Path::theme($folder)) {
			return false;
		}
		array_map(function ($file) use (&$result, $fdata) {

			if ($file{0} == '.') {
				return;
			}
			$file=Path::toutf($file);
			$fd = infra_nameinfo($file);
			
			if ($fdata['id'] && $fdata['id'] != $fd['id']) {
				return;
			}
			if ($fdata['name'] && $fdata['name'] != $fd['name']) {
				return;
			}
			
			if ($fdata['ext'] && $fdata['ext'] != $fd['ext']) {
				return;
			} elseif ($result) {
				//Расширение не указано и уже есть результат
				//Исключение.. расширение tpl самое авторитетное
				if ($fd['ext'] != 'tpl') {
					return;
				}
			}
			$result = $file;
		}, scandir(Path::theme($folder)));

		if (!$result) {
			return false;
		}

		return Path::theme($folder.$result);
	}, array($isrc), isset($_GET['re']));
	return $src;
}
function autoedit_createPath($p, $path = '')
{
	//путь до файла или дирректории со * или без, возвращается тот же путь без звёздочки
	//Если путь приходит от пользователя нужно проверять и префикс infra/data добавляется автоматически чтобы ограничить места создания
	//if(preg_match("/\/\./",$ifolder))return err($ans,'Path should not contain points at the beginning of filename /.');
	//if(!preg_match("/^\*/",$ifolder))return err($ans,'First symbol should be the asterisk *.');

	if (is_string($p)) {
		$dirs = infra_dirs();
		$p = preg_replace("/^\*/", $dirs['data'], $p);
		$p = explode('/', $p);
		$f = array_pop($p);//достали файл или пустой элемент у дирректории
		$f = Path::tofs($f);
	} else {
		$f = '';
	}
	$dir = array_shift($p);//Создаём первую папку в адресе
	$dir = Path::tofs($dir);
	if ($dir) {
		if (!is_dir($path.$dir)) {
			$r = mkdir($path.$dir);
		} else {
			$r = true;
		}
		if ($r) {
			return autoedit_createPath($p, $path.$dir.'/').$f;
		} else {
			throw Exception('Ошибка при работе с файловой системой');
		}
	}

	return $path.$dir.'/'.$f;
}
function autoedit_ext($file)
{
	if (!$file) {
		return '';
	}
	$ext = preg_match('/\.(\w{0,4})$/', $file, $match);
	$ext = $match[1];

	return $ext;
}
function autoedit_folder($file)
{
	$s = explode('/', $file);
	$name = array_pop($s);
	$folder = implode('/', $s);
	if ($folder != '*') {
		$folder .= '/';
	}

	return $folder;
}
function autoedit_takepath($file = false)
{
	$dirs=infra_dirs();
	$takepath = $dirs['cache'].'admin_takefiles/';
	if ($file === false) {
		return $takepath;
	}
	$file=autoedit_theme($file);
	$path = $takepath.preg_replace('/[\\/\\\\\*]/', '_', Path::tofs($file)).'.js';

	return $path;
}
function autoedit_ismytake($file)
{
	$takepath = autoedit_takepath($file);
	$take = Load::loadJSON($takepath);
	if (!$take) {
		return true;
	}
	if ($take['ip'] != $_SERVER['REMOTE_ADDR'] || $take['browser'] != $_SERVER['HTTP_USER_AGENT']) {
		return false;
	}

	return true;
}
if (!function_exists('err')) {
	function err($ans, $msg)
	{
		$ans['msg'] = $msg;
		echo infra_json_encode($ans);
	}
}
function autoedit_backup($file)
{
	
}
function cpdir($src, $dst)
{
	$dir = opendir($src);
	mkdir($dst);
	while (false !== ($file = readdir($dir))) {
		if (($file != '.') && ($file != '..')) {
			if (is_dir($src.'/'.$file)) {
				cpdir($src.'/'.$file, $dst.'/'.$file);
			} else {
				copy($src.'/'.$file, $dst.'/'.$file);
			}
		}
	}
	closedir($dir);

	return true;
}
