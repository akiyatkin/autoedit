<?php
namespace infrajs\autoedit;
use infrajs\load\Load;
use infrajs\path\Path;
use infrajs\ans\Ans;
use infrajs\config\Config;
use infrajs\infra\Infra;
use infrajs\each\Each;
use infrajs\access\Access;

if (!is_file('vendor/autoload.php')) {
	chdir('../../../');
	require_once('vendor/autoload.php');
}

Path::req('-autoedit/admin.inc.php');

$type = Path::toutf(@$_REQUEST['type']);
$id = Path::toutf(@$_REQUEST['id']);
$submit = (bool) @$_REQUEST['submit'];

$RTEABLE = array('tpl','html','htm','');
$CORABLE = array('json','tpl','html','htm','txt','js','css','');

$ans = array('id' => $id,'type' => $type,'msg' => '');
if (in_array($type, array('admin'))) {
	if (!$submit) {
		$ans['admin'] = Access::admin();
	} else {
		$ans['admin'] = Access::admin(array(@$_REQUEST['login'], @$_REQUEST['pass']));
		if (!$ans['admin']) {
			if (isset($_REQUEST['login'])) {
				return Ans::err($ans, 'Неправильный пароль!');
			} else {
				return Ans::ret($ans, 'Вы успешно вышли!');
			}
		}
	}
}

if (!Access::admin()) {
	return Ans::err($ans, 'Вам нужно авторизоваться');
}

if (in_array($type, array('mvdir', 'mkdir', 'cpdir', 'rmdir'))) {
	if ($id{0} != '~') {
		return Ans::err($ans, 'Путь должен начинаться с ~');
	}

	

	if ($type === 'mkdir' && !Path::theme($id, 'snd')) {
		return Ans::err($ans, 'Нет папки в которой нужно создать');
	}
	if ($type === 'mvdir' && !Path::theme($id, 'snd')) {
		return Ans::err($ans, 'Нет папки которую нужно перенести');
	}
	if ($type === 'cpdir' && !Path::theme($id, 'snd')) {
		return Ans::err($ans, 'Нет папки которую нужно скопировать');
	}
	if ($type === 'rmdir' && !Path::theme($id, 'snd')) {
		return Ans::err($ans, 'Нет папки которую нужно удалить');
	}

	
	if (!$submit) {
		if (in_array($type, array('mvdir', 'cpdir', 'rmdir'))) {
			//Нужно определить имя родительской папки, которое по умолчанию показывается
			$path = Path::theme($id, 'snd');
			$path = explode('/', $id);
			array_pop($path);//Так как папка заканчивается на / последний элемент в массиве буедт всегда пустым
			$name = array_pop($path);
			$name = preg_replace("/^\-/", '', $name);
			$name = preg_replace("/^\~/", '', $name);
			$name = preg_replace("/^\!/", '', $name);
			$parent = implode('/', $path);
			if (!$parent) {
				$parent = '~';
			} else {
				$parent .= '/';
			}

			$ans['oldname'] = $name;//Имя по умолчанию
			$ans['oldfolder'] = $parent;//Папка в которой можно увидеть обрабатываемую папку
		} elseif (in_array($type, array('mkdir'))) {
			//id это уже родительская папка
			$ans['oldfolder'] = $id;
			$ans['oldname'] = '';
		}
	} else {
		if (in_array($type, array('mvdir', 'cpdir', 'rmdir'))) {
			//Есть дирректория источник
			$oldfolder = $_REQUEST['oldfolder'];
			$oldname = $_REQUEST['oldname'];
			$oldpath = $oldfolder.$oldname.'/';
			if ($id !== $oldpath) {
				return Ans::err($ans, 'Ошибка в переданных параметрах');
			}
			if (!Path::theme($oldpath, 'snd')) {
				return Ans::err($ans, 'Не найден оригинальный путь');
			}
		}
		if (in_array($type, array('mvdir', 'cpdir', 'mkdir'))) {
			//Есть дирректория назначения
			$newfolder = trim($_REQUEST['newfolder']);
			$newname = trim($_REQUEST['newname']);
			$newpath = $newfolder.$newname.'/';
			if (!$newname) {
				return Ans::err($ans, 'Нужно указать имя');
			}
			if (preg_match('/\//', $newname)) {
				return Ans::err($ans, 'Имя папки не может содержать слэш');
			}
			if (!Path::theme($newfolder)) {
				return Ans::err($ans, 'Не найдено новое место где нужно расположить папку');
			}
			if (Path::theme($newpath)) {
				return Ans::err($ans, 'Такая папка уже существует или имя занято');
			}
		}
		if ($type === 'mvdir') {
			if (@rename(Path::theme($oldfolder).Path::tofs($oldname).'/', Path::theme($newfolder).Path::tofs($newname).'/')) {
				$ans['close'] = 1;

				return Ans::ret($ans, 'Директория переименована.');
			} else {
				return Ans::err($ans, 'Не удалось переименовать директорию.');
			}
		} elseif ($type === 'mkdir') {
			if (@mkdir(Path::theme($newfolder).Path::tofs($newname).'/')) {
				$ans['close'] = 1;//Сигнал окну закрыться
				return Ans::ret($ans, 'Директория создана');
			} else {
				return Ans::err($ans, 'Создать директорию не получилось.');
			}
		} elseif ($type === 'cpdir') {
			if (@copy(Path::theme($oldfolder).Path::tofs($oldname), Path::theme($newfolder).Path::tofs($newname).'/')) {
				$ans['close'] = 1;

				return Ans::ret($ans, 'Директория скопирована');
			} else {
				return Ans::err($ans, 'Скопировать директорию не получилось.');
			}
		} elseif ($type === 'rmdir') {
			if (@rmdir(Path::theme($oldfolder).Path::tofs($oldname))) {
				$ans['close'] = 1;

				return Ans::ret($ans, 'Директория удалена.');
			} else {
				return Ans::ret($ans, 'Ошибка. Папка не удалена. Вероятно она не пустая.');
			}
		}
	}
} elseif (in_array($type, array('copyfile', 'deletefile', 'renamefile'))) {
	if (!$submit) {
		$ans['name'] = preg_replace("/(.*\/)*/", '', $id);
		if ($ans['name'][0] == '-') {
			$ans['name'] = preg_replace('/^\-/', '', $ans['name']);
			$ans['folder'] = '-';
		} else if ($ans['name'][0] == '~') {
			$ans['name'] = preg_replace('/^\~/', '', $ans['name']);
			$ans['folder'] = '~';
		} else if ($ans['name'][0] == '!') {
			$ans['name'] = preg_replace('/^\!/', '', $ans['name']);
			$ans['folder'] = '!';
		} else {
			$ans['folder'] = str_replace($ans['name'], '', $id);
		}
		$ans['full'] = Path::theme($ans['folder']);
		$ans['full'] = Path::toutf($ans['full']);
		$file = Path::theme($id);
		$ans['isfile'] = (bool) $file;

		$takepath = autoedit_takepath($file);
		if ($file) {
			$ans['take'] = Load::loadJSON($takepath);
		} else {
			$ans['take'] = false;
		}
	} else {
		if ($type == 'deletefile') {
			$ans['close'] = 1;//закрывать окно по окончанию
			$ans['autosave'] = 0;//Не очищать autosave

			$file = Path::theme($id);
			if (!$file) {
				return Ans::err($ans, 'Файл не найден '.Path::toutf($id), 0);
			}
			$takepath = autoedit_takepath($file);
			$take = Load::loadJSON($takepath);
			if ($take) {
				$ans['editfile'] = $id;
				$ans['takeinfo'] = $take;

				return Ans::err($ans, 'Файл занят '.Path::toutf($id));
			}
			$msg = autoedit_backup($file);
			if (is_string($msg)) {
				return Ans::err($ans, $msg);
			}
			$r = @unlink($file);
			if (!$r) {
				return Ans::err($ans, 'Неудалось удалить файл. Возможно нет прав, если это скрытый файл в windows.');
			}
		} elseif ($type == 'renamefile' || $type == 'copyfile') {
			$oldfolder = Path::theme($_REQUEST['oldfolder']);
			if (!$oldfolder) {
				return Ans::err($ans, 'Не найдена оригинальная папка '.Path::toutf($_REQUEST['oldfolder']));
			}
			$oldname = Path::tofs($_REQUEST['oldname']);
			$oldfile = Path::theme($oldfolder.$oldname);
			if (!is_file($oldfile)) {
				return Ans::err($ans, 'Не найден оригинальный файл'.Path::toutf(@$_REQUEST['oldold']));
			}
			$takepath = autoedit_takepath($oldfile);

			$take = Load::loadJSON($takepath);
			if ($take) {
				$ans['editfile'] = $_REQUEST['oldfolder'].$_REQUEST['oldname'];
				$ans['takeinfo'] = $take;

				return Ans::err($ans, 'Файл занят');
			}
		}
		if ($type == 'renamefile' || $type == 'copyfile') {
			$newname = trim(Path::tofs($_REQUEST['newname']));
			if (!$newname) {
				return Ans::err($ans, 'Не указано имя нового файла '.Path::toutf($oldfile));
			}
			$isfull = (bool) @$_REQUEST['full'];
			if ($isfull) {
				$ans['newfile'] = $_REQUEST['newfolder'].$_REQUEST['newname'];
				$newfolder = Path::theme($_REQUEST['newfolder']);
				if (!$newfolder) {
					return Ans::err($ans, 'Не найдена папка '.Path::toutf($newfolder));
				}
			} else {
				$ans['newfile'] = $_REQUEST['oldfolder'].$_REQUEST['newname'];
				$newfolder = $oldfolder;
			}
			if (($newfolder == $oldfolder && $newname == $oldname)) {
				return Ans::err($ans, 'Нужно указать новое имя файла '.Path::toutf($oldfile));
			}

			$newfile = $newfolder.$newname;
			$r = Path::theme($newfolder.$newname);
			if ($r) {
				$ans['editfile'] = $ans['newfile'];

				return Ans::err($ans, 'Указанный файл '.Path::toutf($newfolder.$newname).' уже существует.');
			}
		}
		$ans['close'] = 1;//закрывать окно по окончанию
		if ($type == 'renamefile') {
			$r = rename($oldfile, $newfile);
			if (!$r) {
				return Ans::err($ans, 'Неудалось переименовать файл');
			}
		}
		if ($type == 'copyfile') {
			$r = copy($oldfile, $newfile);
			if (!$r) {
				return Ans::err($ans, 'Неудалось скопировать файл');
			}
		}
	}
} elseif ($type == 'version') {
	$data = Load::loadJSON('composer.lock');
	$ar=array();
	foreach($data['packages'] as $k=>$v){
		$homepage=$v['homepage'];
		if(!$homepage&&!empty($v['source'])) $homepage = $v['source']['url'];
		$d=array(
			"name"=>$v['name'],
			"version"=>$v['version'],
			"time"=>strtotime($v['time']),
			"description"=>$v['description'],
			"homepage"=>$homepage
		);
		$ar[]=$d;
	}
	usort($ar,function ($a, $b) {
		$a=$a['time'];
		$b=$b['time'];
	    if ($a == $b) {
	        return 0;
	    }
	    return ($a < $b) ? 1 : -1;
	});
	$ans['data'] = $ar;
	return Ans::ret($ans);
	
} elseif ($type == 'addfile') {
	if (!$submit) {
		$name = $_REQUEST['name'];
		if ($name) {
			$file = Path::theme($id.$name);
			$takepath = autoedit_takepath($file);
			$take = Load::loadJSON($takepath);
			$ans['path'] = $id.$name;
			$ans['take'] = $take;
		}
	} else {
		$ifolder = Path::toutf($id);

		$folder = autoedit_createPath($ifolder);
		if (!$folder) {
			return err($ans, 'Failed to create the directory');
		}

		$rewrite = @$_REQUEST['rewrite'];
		$ofile = $_FILES['file'];
		if (!$ofile) {
			return err($ans, 'Нe указан файл для загрузки');
		}

		ini_set('upload_max_filesize', '16M');//Не применяется
		if ($ofile['error']) {
			if ($ofile['error'] == 4) {
				return err($ans, 'Вы не указали файл для загрузки');
			}
			if ($ofile['error'] === 1) {
				return err($ans, 'Слишком большой размер файла. Файд должен быть не больше '.ini_get('upload_max_filesize'));
			}

			return err($ans, 'Ошибка при загрузкe файла '.$ofile['error']);
		}

		$name = Path::toutf($ofile['name']);
		$ans['name'] = $name;
		$file = $folder.Path::tofs($name);

		if (!$rewrite && is_file($file)) {
			$ans['edit'] = $id.Path::toutf($name);

			return err($ans, 'Указанный файл уже есть');
		}

		$takepath = autoedit_takepath($file);
		$take = Load::loadJSON($takepath);
		if ($take && is_file($file)) {
			$ans['edit'] = $id.Path::toutf($name);
			$ans['take'] = $take;

			return err($ans, 'Ошибка! Файл существует и сейчас редактируется!');
		}
		if (!is_file($ofile['tmp_name'])) {
			return err($ans, 'Не найден загруженный файл '.Path::toutf($ofile['name']));
		}
		if (!move_uploaded_file($ofile['tmp_name'], $file)) {
			return err($ans, 'Не удалось загрузить файл '.Path::toutf($id.$name));
		}
		$ans['close'] = 1;
		$ans['autosave'] = 1;
	}
} elseif ($type == 'editfile') {
	if ($submit) {
		$ofile = $_FILES['file'];
		$ifolder = Path::toutf($_REQUEST['folder']);

		$folder = autoedit_createPath($ifolder);

		$ans['close'] = 0;
		if ($folder) {
			$oldname = Path::tofs($_REQUEST['file']);
			$file = $ifolder.Path::toutf($oldname);
			$oldfile = Path::theme($file);//Цифры не ищутся когда путь прямой без *
			if (!$oldfile) {
				$ans['mmmm'] = 'Не найден старый файл '.$file;
				//return err($ans,'Не найден файл '.Path::toutf($file));
				//Значит старого файла и не было... ну такое тоже возможно... просто создаём новый
			}
			if ($oldfile && $ofile && !$ofile['error']) {
				//Делаем backup
				$msg = autoedit_backup($oldfile);
				if (is_string($msg)) {
					return Ans::err($ans, $msg);
				}
			}
			if ($ofile) {
				//Новый файл
				if ($ofile['error']) {
					if ($ofile['error'] === 4) {
						return err($ans, 'Не указан файл для загрузки на сервер');
					} else {
						return err($ans, 'Ошибка при загрузке файла. Код: '.$ofile['error']);
					}
				} else {
					if ($oldfile) {
						$newname = Path::tofs($ofile['name']);
						$newfile = Path::theme($folder.$newname);

						$newr = Load::nameInfo($newname);
						$oldr = Load::nameInfo($oldname);
						$oldr['name'] = preg_replace("/\s\(\d\)$/", '', $oldr['name']);
						$newr['name'] = preg_replace("/\s\(\d\)$/", '', $newr['name']);
						$newr['name'] = preg_replace("/^\./", '', $newr['name']);
						$oldr['name'] = preg_replace("/^\./", '', $oldr['name']);
						//$ans['dddd'] = $oldr;

						if (!@$_REQUEST['passname'] && ($newr['name'] != $oldr['name'] || $newr['ext'] != $oldr['ext'])) {
							return Ans::err($ans, 'Имя загружаемого файла и расширение должны совпадать с текущим файлом, кроме (1) и точки в начале имени. Текущий файл '.$newr['name'].'.'.$newr['ext']);
						}
						$file = $oldfile;
						$r = unlink($file);
						if (!$r) {
							return err($ans, 'Не удалось удалить старый файл '.Path::toutf($file));
						}
					} else {
						$extload = preg_match('/\.\w{0,4}$/', $ofile['name'], $match);
						$extload = $match[0];
						$ext = preg_match('/\.\w{0,4}$/', $file, $match);
						$ext = $match[0];
						if (!$ext) {
							$file .= $extload;
						}
						$file = Path::resolve($file);//preg_replace('/^\*/', 'infra/data/', $file);
					}
					if (!is_file($ofile['tmp_name'])) {
						return err($ans, 'Не найден загруженный файл '.Path::toutf($ofile['name']));
					}
					$file = Path::tofs($file);
					$r = move_uploaded_file($ofile['tmp_name'], $file);
					if (!$r) {
						return err($ans, 'Не удалось загрузить файл '.Path::toutf($file));
					}
					//autoedit_setLastFolderUpdate($file);
					return Ans::ret($ans, 'Файл загружен <span title="'.Path::toutf($file).'">'.Path::toutf($ofile['name']).'</span>');
				}
			}
		} else {
			return Ans::err($ans, 'Не найдена папка');
		}
	} else {
		$file = autoedit_theme($id);//Можно указывать путь без расришения (Для админки которая не знает что будет за файл
		$ans['path'] = $id;
		if (!$file) {
			$ans['take'] = false;
			$ans['isfile'] = false;
			$ans['msg'] = 'Файл ещё не существует, <br>рекомендуется для загрузки нового файла<br>скачать и поправить файл из другова<br>анологичного места. Если это возможно.';
			$filed = Load::nameInfo($id);
			$ans['ext'] = $filed['ext'];
		} else {
			$ans['isfile'] = true;
			$takepath = autoedit_takepath($file);
			$take = Load::loadJSON($takepath);
			if ($take) {
				$ans['take'] = $take['date'];
			} else {
				$ans['take'] = false;
			}

			$ans['size'] = ceil(filesize($file) / 1000);
			$ans['time'] = filemtime($file);
			preg_match("/\.([a-zA-Z0-9]+)$/", $file, $match);
			$ans['ext'] = strtolower($match[1]);
		}
		$ans['corable'] = in_array(strtolower($ans['ext']), $CORABLE);
		$ans['rteable'] = (bool) Path::theme('infra/lib/wymeditor/');
		if ($ans['rteable']) {
			$ans['rteable'] = in_array(strtolower($ans['ext']), $RTEABLE);
		}
		$conf = Config::get('imager');
		$imgext = $conf['images'];
		Each::forr($imgext, function &($e) use (&$ans) {
			if ($e == $ans['ext']) {
				$ans['image'] = true;
			}//Значит это картинка
			$r = null;

			return $r;
		});

		if ($file) {
			//Если файл есть
			$p = explode('/', $file);//Имя с расширением
			$ans['file'] = array_pop($p);
		} else {
			//Если файла нет.. определяем имя из id
			$p = explode('/', $id);//Имя с расширением
			$ans['file'] = array_pop($p);
		}
		$ans['file'] = preg_replace("/^\~/", '', Path::toutf($ans['file']));

		$p = explode('/', $ans['id']);
		array_pop($p);
		$ans['folder'] = implode('/', $p);
		if ($ans['folder'] == '/' || !$ans['folder']) {
			$ans['folder'] = '~';
		} else {
			$ans['folder'] .= '/';
		}

		
		//$s=Path::tofs($s);
		//$p=_infra_src($s);
		//echo '<pre>';
		//print_r($p);
		

		$ans['pathload'] = '/-autoedit/download.php?'.Path::toutf($id);
		$ans['path'] = $ans['path'];
	}
} elseif ($type == 'takeinfo') {
	$file = autoedit_theme($id);
	$takepath = autoedit_takepath($file);
	$take = Load::loadJSON($takepath);

	$ans['path'] = $id;
	$ans['take'] = $take;
	$fd=Load::nameInfo($file);
	$ans['ext'] = $fd['ext'];
} elseif ($type === 'editfolder') {
	if (!$submit) {
		$folder = $id;
		$parent = Path::resolve($folder);
		
		$p = explode('/', $parent);
		array_pop($p);//'/'
		array_pop($p);//'name/'
		$parent = implode('/', $p).'/';// *Разделы/

		
		$parentN = Path::pretty($parent);//preg_replace('/^'.str_replace('/', '\/', $dirs['data']).'/', '~', $parent);
		if ($parentN!=$parent) {
			$ans['parent'] = $parentN;
		}
		
		$folder = autoedit_theme($id);

		if ($folder && !is_dir($folder)) {
			return Ans::err($ans, 'Нет такой папки');
		}
		;
		$list = array();
		$folders = array();
		array_map(function ($file) use (&$list, &$folders, $folder) {
			if ($file=='.' || $file=='..') {
				return;
			}
			$src=$folder.$file;
			$file=Path::toutf($file);
			
			$fd = Load::nameInfo($file);
			$fd['time'] = filemtime($src);
			
			if (is_file($src)) {
				$fd['size']=round(filesize($src)/1000,2);
				$list[]=$fd;
			} else {
				$folders[]=$fd;
			}
		}, scandir($folder));

		usort($folders, function ($a, $b) {
			$aa = (int) $a['num'];
			$bb = (int) $b['num'];
			if ($aa || $bb) {
				if ($aa == $bb) return 0;
				if(!$aa) return 1;
				return ($aa > $bb) ? 1 : -1;
			}
			$aa = $a['name'];
			$bb = $b['name'];
			return strcasecmp($aa, $bb);
		});
		//$folders = array_reverse($folders);
		//$list = array_reverse($list);
		
		$folder = Path::toutf($folder);
		$folder = Path::pretty($folder);
		$ans['list'] = $list;
		$ans['folders'] = $folders;
		if ($ans['list']) {
			foreach ($ans['list'] as &$v) {
				$e = $v['ext'] ? '.'.$v['ext'] : '';
				$file = $folder.$v['file'];
				$takepath = autoedit_takepath($file);
				$d = Load::loadJSON($takepath);
				$v['corable'] = in_array(strtolower($v['ext']), $CORABLE);

				$v['pathload'] = '/-autoedit/download.php?'.Path::toutf($file);
				
				$v['mytake'] = autoedit_ismytake($file);
				if ($d) {
					$v['take'] = $d['date'];
				}
			}
		}
	}
} elseif ($type === 'jsoneditor') {
	$file = explode('|', $id);
	$file = Path::tofs($file[0]);
	$origfile = $file;
	$isfile = Path::theme($file);
	if ($isfile) {
		$file = $isfile;
	} else {
		$file = Path::resolve($file);
	}

	if (!$submit) {
		$path = explode('/', $file);
		$name = array_pop($path);//Так как папка заканчивается на / последний элемент в массиве буедт именем файла
		$parent = implode('/', $path).'/';
		
		$parent = Path::pretty($parent); //preg_replace("/^infra\/data\//", '~', $parent);

		$ans['oldfolder'] = Path::toutf($parent);//Папка в которой можно увидеть обрабатываемую папку
		$ans['oldname'] = Path::toutf($name);

		if ($isfile) {
			$ans['content'] = Load::loadTEXT($file);
		} else {
			$ans['content'] = '';
		}

		return Ans::ret($ans);
	} else {
		if (!$isfile) {
			if (!autoedit_ext($file)) {
				$file .= '.json';
			}
			

			$file = autoedit_createPath($origfile);

			$ans['msg'] .= 'Файл был создан<br>';
			if (!$file) {
				return Ans::err($ans, 'Не удалось создать путь до файла '.Path::toutf($file));
			}
		}

		$r = file_put_contents($file, $_REQUEST['content']);
		if (!$r) {
			return Ans::err($ans, 'Неудалось сохранить файл');
		}
		$ans['msg'] .= 'Cохранено';

		return Ans::ret($ans);
	}
} elseif ($type === 'seo') {
	$dir = Path::resolve('~seo/');
	$src = Path::tofs($id);
	$src = str_replace('/', '-', $src);
	$src = str_replace('..', '-', $src);
	if (strlen($src) > 100) {
		$src = md5($src);
	}
	$src = $dir.'seo-'.$src.'.json';
	if (!$submit) {
		$seo = Load::loadJSON($src);
		$ans['seo'] = $seo;
	} else {
		$dir = autoedit_createPath($dir);

		$seo = $_POST['seo'];
		$def = $_POST['def'];
		$keys = array();
		Each::foro($seo, function ($val, $key) use (&$seo, &$def, &$keys) {
			if ($seo[$key] == $def[$key]) {
				return;
			}
			$keys[$key] = $val;
		});

		if (sizeof($keys) == 0) {
			$r = unlink($src);
		} else {
			$keys['page'] = $id;
			$keys['time'] = time();
			$r = file_put_contents($src, Load::json_encode($keys));
		}
		if ($r) {
			return Ans::ret($ans, 'SEO-данные сохранены');
		}

		return Ans::err($ans, 'Ошибка. SEO-данные не сохранены');
	}
} elseif ($type === 'corfile') {
	if (!$submit) {

		$folder = Path::resolve($id);

		$path = explode('/', $folder);
		$name = array_pop($path);//Так как папка заканчивается на / последний элемент в массиве буедт всегда пустым
		$parent = implode('/', $path).'/';
		
		$parent = Path::pretty($parent);

		$ans['oldfolder'] = $parent;//Папка в которой можно увидеть обрабатываемую папку
		$ans['oldname'] = $name;

		$ans['content'] = Load::loadTEXT($id);

		return Ans::ret($ans);
	} else {
		$file = $id;
		//$isdir=Path::theme($file,'sdn');
		//if($isdir) return infra_echo($ans,'Существует папка с именем как у файла '.$id);

		$isfile = Path::theme($file);
		if (!$isfile) {
			if (!autoedit_ext($file)) {
				$file .= '.tpl';
			}
			$ans['msg'] .= 'Файл был создан<br>';
	
			$file = autoedit_createPath($file);
	

			if (!$file) {
				return Ans::err($ans, 'Не удалось создать путь до файла'.$id);
			}
		} else {
			$file = $isfile;
		}

		$r = file_put_contents($file, $_REQUEST['content']);
		//autoedit_setLastFolderUpdate($file);
		if (!$r) {
			return Ans::err($ans, 'Неудалось сохранить файл');
		}
		//$ans['noclose']=1;
		//$ans['autosave']=0;
		$ans['msg'] .= 'Cохранено';

		return Ans::ret($ans);
	}
} elseif ($type == 'takeshow') {
	$takepath = autoedit_takepath();
	$list=array();
	array_map(function ($file) use (&$list, $takepath) {
		if ($file=='.' || $file=='..') {
			return;
		}
		$list[]=Path::toutf($file);
	}, scandir($takepath));

	$files = array();
	if ($list) {
		foreach ($list as $file) {
			$d = Load::loadJSON($takepath.$file);
			$d['path'] = Path::resolve($d['path']);

			$d['modified'] = filemtime(Path::theme($d['path']));
			preg_match("/\.([a-zA-Z]+)$/", $d['path'], $match);
			$d['ext'] = strtolower($match[1]);
			$files[] = $d;
		}
	}
	$ans['files'] = $files;
} elseif ($type == 'takefile') {
	if ($submit) {
		$take = (bool) $_GET['take'];
		$ans['take'] = $take;
		$file = autoedit_theme($id);
		$file = Path::toutf($file);
		if (!$file) {
			$ans['noaction'] = true;//Собственно всё осталось как было
		} else {
			$takepath = autoedit_takepath($file);
			if (!$take && is_file($takepath)) {
				$r = @unlink($takepath);
				if (!$r) {
					return Ans::err($ans, 'Неудалось отпустить файл');
				}
			} elseif ($take && !is_file($takepath)) {
				//Повторно захватывать не будем
				$save = array('path' => $id,'date' => time(),'ip' => $_SERVER['REMOTE_ADDR'],'browser' => $_SERVER['HTTP_USER_AGENT']);
				$r = file_put_contents($takepath, Load::json_encode($save));
				if (!$r) {
					return Ans::err('Неудалось захватить файл');
				}
			} else {
				$ans['noaction'] = true;//Собственно всё осталось как было
				
			}
		}
	}
}

return Ans::ret($ans);
