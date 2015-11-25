<?php



infra_require('*autoedit/admin.inc.php');

$type = infra_toutf(@$_REQUEST['type']);
$id = infra_toutf(@$_REQUEST['id']);
$submit = (bool) @$_REQUEST['submit'];

$RTEABLE = array('tpl','html','htm','');
$CORABLE = array('json','tpl','html','htm','txt','js','css','');

$ans = array('id' => $id,'type' => $type,'msg' => '');
if (in_array($type, array('admin'))) {
	if (!$submit) {
		$ans['admin'] = infra_admin();
	} else {
		$ans['admin'] = infra_admin(array(@$_REQUEST['login'], @$_REQUEST['pass']));
		if (!$ans['admin']) {
			if (isset($_REQUEST['login'])) {
				return infra_err($ans, 'Неправильный пароль!');
			} else {
				return infra_ret($ans, 'Вы успешно вышли!');
			}
		}
	}
}

if (!infra_admin()) {
	return infra_err($ans, 'Вам нужно авторизоваться');
}

if (in_array($type, array('mvdir', 'mkdir', 'cpdir', 'rmdir'))) {
	if ($id{0} != '*') {
		return infra_err($ans, 'Путь должен быть тематический, начинаться с *.');
	}

	

	if ($type === 'mkdir' && !infra_theme($id, 'snd')) {
		return infra_err($ans, 'Нет папки в которой нужно создать');
	}
	if ($type === 'mvdir' && !infra_theme($id, 'snd')) {
		return infra_err($ans, 'Нет папки которую нужно перенести');
	}
	if ($type === 'cpdir' && !infra_theme($id, 'snd')) {
		return infra_err($ans, 'Нет папки которую нужно скопировать');
	}
	if ($type === 'rmdir' && !infra_theme($id, 'snd')) {
		return infra_err($ans, 'Нет папки которую нужно удалить');
	}

	
	if (!$submit) {
		if (in_array($type, array('mvdir', 'cpdir', 'rmdir'))) {
			//Нужно определить имя родительской папки, которое по умолчанию показывается
			$path = infra_theme($id, 'snd');
			$path = explode('/', $id);
			array_pop($path);//Так как папка заканчивается на / последний элемент в массиве буедт всегда пустым
			$name = array_pop($path);
			$name = preg_replace("/^\*/", '', $name);
			$name = preg_replace("/^\~/", '', $name);
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
				return infra_err($ans, 'Ошибка в переданных параметрах');
			}
			if (!infra_theme($oldpath, 'snd')) {
				return infra_err($ans, 'Не найден оригинальный путь');
			}
		}
		if (in_array($type, array('mvdir', 'cpdir', 'mkdir'))) {
			//Есть дирректория назначения
			$newfolder = trim($_REQUEST['newfolder']);
			$newname = trim($_REQUEST['newname']);
			$newpath = $newfolder.$newname.'/';
			if (!$newname) {
				return infra_err($ans, 'Нужно указать имя');
			}
			if (preg_match('/\//', $newname)) {
				return infra_err($ans, 'Имя папки не может содержать слэш');
			}
			if (!infra_theme($newfolder)) {
				return infra_err($ans, 'Не найдено новое место где нужно расположить папку');
			}
			if (infra_theme($newpath)) {
				return infra_err($ans, 'Такая папка уже существует или имя занято');
			}
		}
		if ($type === 'mvdir') {
			if (@rename(infra_theme($oldfolder).infra_tofs($oldname).'/', infra_theme($newfolder).infra_tofs($newname).'/')) {
				$ans['close'] = 1;

				return infra_ret($ans, 'Директория переименована.');
			} else {
				return infra_err($ans, 'Не удалось переименовать директорию.');
			}
		} elseif ($type === 'mkdir') {
			if (@mkdir(infra_theme($newfolder).infra_tofs($newname).'/')) {
				$ans['close'] = 1;//Сигнал окну закрыться
				return infra_ret($ans, 'Директория создана');
			} else {
				return infra_err($ans, 'Создать директорию не получилось.');
			}
		} elseif ($type === 'cpdir') {
			if (@copy(infra_theme($oldfolder).infra_tofs($oldname), infra_theme($newfolder).infra_tofs($newname).'/')) {
				$ans['close'] = 1;

				return infra_ret($ans, 'Директория скопирована');
			} else {
				return infra_err($ans, 'Скопировать директорию не получилось.');
			}
		} elseif ($type === 'rmdir') {
			if (@rmdir(infra_theme($oldfolder).infra_tofs($oldname))) {
				$ans['close'] = 1;

				return infra_ret($ans, 'Директория удалена.');
			} else {
				return infra_ret($ans, 'Ошибка. Папка не удалена. Вероятно она не пустая.');
			}
		}
	}
} elseif (in_array($type, array('copyfile', 'deletefile', 'renamefile'))) {
	if (!$submit) {
		$ans['name'] = preg_replace("/(.*\/)*/", '', $id);
		if ($ans['name'][0] == '*') {
			$ans['name'] = preg_replace('/^\*/', '', $ans['name']);
			$ans['folder'] = '*';
		} else if ($ans['name'][0] == '~') {
			$ans['name'] = preg_replace('/^\~/', '', $ans['name']);
			$ans['folder'] = '~';
		} else {
			$ans['folder'] = str_replace($ans['name'], '', $id);
		}
		$ans['full'] = infra_theme($ans['folder']);
		$ans['full'] = infra_toutf($ans['full']);
		$file = infra_theme($id);
		$ans['isfile'] = (bool) $file;

		$takepath = autoedit_takepath($file);
		if ($file) {
			$ans['take'] = infra_loadJSON($takepath);
		} else {
			$ans['take'] = false;
		}
	} else {
		if ($type == 'deletefile') {
			$ans['close'] = 1;//закрывать окно по окончанию
			$ans['autosave'] = 0;//Не очищать autosave

			$file = infra_theme($id);
			if (!$file) {
				return infra_err($ans, 'Файл не найден '.infra_toutf($id), 0);
			}
			$takepath = autoedit_takepath($file);
			$take = infra_loadJSON($takepath);
			if ($take) {
				$ans['editfile'] = $id;
				$ans['takeinfo'] = $take;

				return infra_err($ans, 'Файл занят '.infra_toutf($id));
			}
			$msg = autoedit_backup($file);
			if (is_string($msg)) {
				return infra_err($ans, $msg);
			}
			$r = @unlink($file);
			if (!$r) {
				return infra_err($ans, 'Неудалось удалить файл. Возможно нет прав, если это скрытый файл в windows.');
			}
		} elseif ($type == 'renamefile' || $type == 'copyfile') {
			$oldfolder = infra_theme($_REQUEST['oldfolder']);
			if (!$oldfolder) {
				return infra_err($ans, 'Не найдена оригинальная папка '.infra_toutf($_REQUEST['oldfolder']));
			}
			$oldname = infra_tofs($_REQUEST['oldname']);
			$oldfile = infra_theme($oldfolder.$oldname);
			if (!is_file($oldfile)) {
				return infra_err($ans, 'Не найден оригинальный файл'.infra_toutf(@$_REQUEST['oldold']));
			}
			$takepath = autoedit_takepath($oldfile);

			$take = infra_loadJSON($takepath);
			if ($take) {
				$ans['editfile'] = $_REQUEST['oldfolder'].$_REQUEST['oldname'];
				$ans['takeinfo'] = $take;

				return infra_err($ans, 'Файл занят');
			}
		}
		if ($type == 'renamefile' || $type == 'copyfile') {
			$newname = trim(infra_tofs($_REQUEST['newname']));
			if (!$newname) {
				return infra_err($ans, 'Не указано имя нового файла '.infra_toutf($oldfile));
			}
			$isfull = (bool) @$_REQUEST['full'];
			if ($isfull) {
				$ans['newfile'] = $_REQUEST['newfolder'].$_REQUEST['newname'];
				$newfolder = infra_theme($_REQUEST['newfolder']);
				if (!$newfolder) {
					return infra_err($ans, 'Не найдена папка '.infra_toutf($newfolder));
				}
			} else {
				$ans['newfile'] = $_REQUEST['oldfolder'].$_REQUEST['newname'];
				$newfolder = $oldfolder;
			}
			if (($newfolder == $oldfolder && $newname == $oldname)) {
				return infra_err($ans, 'Нужно указать новое имя файла '.infra_toutf($oldfile));
			}

			$newfile = $newfolder.$newname;
			$r = infra_theme($newfolder.$newname);
			if ($r) {
				$ans['editfile'] = $ans['newfile'];

				return infra_err($ans, 'Указанный файл '.infra_toutf($newfolder.$newname).' уже существует.');
			}
		}
		$ans['close'] = 1;//закрывать окно по окончанию
		if ($type == 'renamefile') {
			$r = rename($oldfile, $newfile);
			if (!$r) {
				return infra_err($ans, 'Неудалось переименовать файл');
			}
		}
		if ($type == 'copyfile') {
			$r = copy($oldfile, $newfile);
			if (!$r) {
				return infra_err($ans, 'Неудалось скопировать файл');
			}
		}
	}
} elseif ($type == 'version') {
	$tpl = infra_loadTEXT('*controller/version.tpl');
	if (!$tpl) {
		$tpl = 'Информация не указана.';
	}
	$ans['info'] = $tpl;
} elseif ($type == 'addfile') {
	if (!$submit) {
		$name = $_REQUEST['name'];
		if ($name) {
			$file = infra_theme($id.$name);
			$takepath = autoedit_takepath($file);
			$take = infra_loadJSON($takepath);
			$ans['path'] = $id.$name;
			$ans['take'] = $take;
		}
	} else {
		$ifolder = infra_toutf($id);

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

		$name = infra_toutf($ofile['name']);
		$ans['name'] = $name;
		$file = $folder.infra_tofs($name);

		if (!$rewrite && is_file($file)) {
			$ans['edit'] = $id.infra_toutf($name);

			return err($ans, 'Указанный файл уже есть');
		}

		$takepath = autoedit_takepath($file);
		$take = infra_loadJSON($takepath);
		if ($take && is_file($file)) {
			$ans['edit'] = $id.infra_toutf($name);
			$ans['take'] = $take;

			return err($ans, 'Ошибка! Файл существует и сейчас редактируется!');
		}
		if (!is_file($ofile['tmp_name'])) {
			return err($ans, 'Не найден загруженный файл '.infra_toutf($ofile['name']));
		}
		if (!move_uploaded_file($ofile['tmp_name'], $file)) {
			return err($ans, 'Не удалось загрузить файл '.infra_toutf($id.$name));
		}
		$ans['close'] = 1;
		$ans['autosave'] = 1;
	}
} elseif ($type == 'editfile') {
	if ($submit) {
		$ofile = $_FILES['file'];
		$ifolder = infra_toutf($_REQUEST['folder']);

		$folder = autoedit_createPath($ifolder);

		$ans['close'] = 0;
		if ($folder) {
			$oldname = infra_tofs($_REQUEST['file']);
			$file = $ifolder.infra_toutf($oldname);
			$oldfile = infra_theme($file);//Цифры не ищутся когда путь прямой без *
			if (!$oldfile) {
				$ans['mmmm'] = 'Не найден старый файл '.$file;
				//return err($ans,'Не найден файл '.infra_toutf($file));
				//Значит старого файла и не было... ну такое тоже возможно... просто создаём новый
			}
			if ($oldfile && $ofile && !$ofile['error']) {
				//Делаем backup
				$msg = autoedit_backup($oldfile);
				if (is_string($msg)) {
					return infra_err($ans, $msg);
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
						$newname = infra_tofs($ofile['name']);
						$newfile = infra_theme($folder.$newname);

						$newr = infra_nameinfo($newname);
						$oldr = infra_nameinfo($oldname);
						$oldr['name'] = preg_replace("/\s\(\d\)$/", '', $oldr['name']);
						$newr['name'] = preg_replace("/\s\(\d\)$/", '', $newr['name']);
						$newr['name'] = preg_replace("/^\./", '', $newr['name']);
						$oldr['name'] = preg_replace("/^\./", '', $oldr['name']);
						$ans['dddd'] = $oldr;

						if (!@$_REQUEST['passname'] && ($newr['name'] != $oldr['name'] || $newr['ext'] != $oldr['ext'])) {
							return infra_err($ans, 'Имя загружаемого файла и расширение должны совпадать с текущим файлом, кроме (1) и точки в начале имени. Текущий файл '.$newr['name'].'.'.$newr['ext']);
						}
						$file = $oldfile;
						$r = unlink($file);
						if (!$r) {
							return err($ans, 'Не удалось удалить старый файл '.infra_toutf($file));
						}
					} else {
						$extload = preg_match('/\.\w{0,4}$/', $ofile['name'], $match);
						$extload = $match[0];
						$ext = preg_match('/\.\w{0,4}$/', $file, $match);
						$ext = $match[0];
						if (!$ext) {
							$file .= $extload;
						}
						$file = preg_replace('/^\*/', 'infra/data/', $file);
					}
					if (!is_file($ofile['tmp_name'])) {
						return err($ans, 'Не найден загруженный файл '.infra_toutf($ofile['name']));
					}
					$file = infra_tofs($file);
					$r = move_uploaded_file($ofile['tmp_name'], $file);
					if (!$r) {
						return err($ans, 'Не удалось загрузить файл '.infra_toutf($file));
					}
					//autoedit_setLastFolderUpdate($file);
					return infra_ret($ans, 'Файл загружен <span title="'.infra_toutf($file).'">'.infra_toutf($ofile['name']).'</span>');
				}
			}
		} else {
			return infra_err($ans, 'Не найдена папка');
		}
	} else {
		$file = autoedit_theme($id);//Можно указывать путь без расришения (Для админки которая не знает что будет за файл
		$ans['path'] = $id;
		if (!$file) {
			$ans['take'] = false;
			$ans['isfile'] = false;
			$ans['msg'] = 'Файл ещё не существует, <br>рекомендуется для загрузки нового файла<br>скачать и поправить файл из другова<br>анологичного места. Если это возможно.';
			$filed = infra_nameinfo($id);
			$ans['ext'] = $filed['ext'];
		} else {
			$ans['isfile'] = true;
			$takepath = autoedit_takepath($file);
			$take = infra_loadJSON($takepath);
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
		$ans['rteable'] = (bool) infra_theme('infra/lib/wymeditor/');
		if ($ans['rteable']) {
			$ans['rteable'] = in_array(strtolower($ans['ext']), $RTEABLE);
		}
		$conf = infra_config();
		$imgext = $conf['imager']['images'];
		infra_forr($imgext, function &($e) use (&$ans) {
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
		$ans['file'] = preg_replace("/^\~/", '', infra_toutf($ans['file']));

		$p = explode('/', $ans['id']);
		array_pop($p);
		$ans['folder'] = implode('/', $p);
		if ($ans['folder'] == '/' || !$ans['folder']) {
			$ans['folder'] = '~';
		} else {
			$ans['folder'] .= '/';
		}

		
		//$s=infra_tofs($s);
		//$p=_infra_src($s);
		//echo '<pre>';
		//print_r($p);
		

		$ans['pathload'] = '?*autoedit/download.php?'.infra_toutf($id);
		$ans['path'] = infra_toutf(infra_theme($ans['path']));
	}
} elseif ($type == 'takeinfo') {
	$file = autoedit_theme($id);
	$takepath = autoedit_takepath($file);
	$take = infra_loadJSON($takepath);

	$ans['path'] = $id;
	$ans['take'] = $take;
	$fd=infra_nameinfo($file);
	$ans['ext'] = $fd['ext'];
} elseif ($type === 'editfolder') {
	if (!$submit) {
		$folder = $id;
		$dirs = infra_dirs();
		$parent = preg_replace("/^~/", $dirs['data'], $folder);
		
		$p = explode('/', $parent);
		array_pop($p);//'/'
		array_pop($p);//'name/'
		$parent = implode('/', $p).'/';// *Разделы/

		
		$parentN = preg_replace('/^'.str_replace('/', '\/', $dirs['data']).'/', '~', $parent);
		if ($parentN!=$parent) {
			$ans['parent'] = $parentN;
		}
		
		$folder = autoedit_theme($id);

		if ($folder && !is_dir($folder)) {
			return infra_err($ans, 'Нет такой папки');
		}
		;
		$list=array();
		$folders=array();
		array_map(function ($file) use (&$list, &$folders, $folder) {
			if ($file=='.' || $file=='..') {
				return;
			}
			$src=$folder.$file;
			$file=infra_toutf($file);
			
			$fd=infra_nameinfo($file);
			$fd['time']=filemtime($src);
			
			if (is_file($src)) {
				$fd['size']=round(filesize($src)/1000,2);
				$list[]=$fd;
			} else {
				$folders[]=$fd;
			}
		}, scandir($folder));
		$folders=array_reverse($folders);
		$list=array_reverse($list);
		
		$folder = infra_toutf($folder);
		$folder = preg_replace('/^'.str_replace('/', '\/', $dirs['data']).'/', '~', $folder);
		$ans['list'] = $list;
		$ans['folders'] = $folders;
		if ($ans['list']) {
			foreach ($ans['list'] as &$v) {
				$e = $v['ext'] ? '.'.$v['ext'] : '';
				$file = $folder.$v['file'];
				$takepath = autoedit_takepath($file);
				$d = infra_loadJSON($takepath);
				$v['corable'] = in_array(strtolower($v['ext']), $CORABLE);

				$v['pathload'] = '?*autoedit/download.php?'.infra_toutf($file);
				
				$v['mytake'] = autoedit_ismytake($file);
				if ($d) {
					$v['take'] = $d['date'];
				}
			}
		}
	}
} elseif ($type === 'jsoneditor') {
	$file = explode('|', $id);
	$file = infra_tofs($file[0]);
	$origfile = $file;
	$isfile = infra_theme($file);
	$dirs=infra_dirs();
	if ($isfile) {
		$file = $isfile;
	} else {
		$file = preg_replace("/^~/", $dirs['data'], $file);
		$file = preg_replace("/^\*/", $dirs['data'], $file);
	}

	if (!$submit) {
		$path = explode('/', $file);
		$name = array_pop($path);//Так как папка заканчивается на / последний элемент в массиве буедт именем файла
		$parent = implode('/', $path).'/';
		
		$parent = preg_replace("/^infra\/data\//", '~', $parent);

		$ans['oldfolder'] = infra_toutf($parent);//Папка в которой можно увидеть обрабатываемую папку
		$ans['oldname'] = infra_toutf($name);

		if ($isfile) {
			$ans['content'] = infra_loadTEXT($file);
		} else {
			$ans['content'] = '';
		}

		return infra_ret($ans);
	} else {
		if (!$isfile) {
			if (!autoedit_ext($file)) {
				$file .= '.json';
			}
			//$file=infra_theme($file,'sfnm');//Создали путь до файла

			//$f=preg_replace('/^\*/','*/',$file);
			/*$p=explode('/',$f);
			if($p[0]=='*')$p[0]='infra/data';
			$f=array_pop($p);//достали файл*/

			$file = autoedit_createPath($origfile);

			$ans['msg'] .= 'Файл был создан<br>';
			if (!$file) {
				return infra_err($ans, 'Не удалось создать путь до файла '.infra_toutf($file));
			}
		}

		$r = file_put_contents($file, $_REQUEST['content']);
		if (!$r) {
			return infra_err($ans, 'Неудалось сохранить файл');
		}
		$ans['msg'] .= 'Cохранено';

		return infra_ret($ans);
	}
} elseif ($type === 'seo') {
	$dirs = infra_dirs();
	$dir = $dirs['data'].'seo/';//stencil//
	$src = infra_tofs($id);
	$src = str_replace('/', '-', $src);
	$src = str_replace('..', '-', $src);
	if (strlen($src) > 100) {
		$src = md5($src);
	}
	$src = $dir.'seo-'.$src.'.json';
	if (!$submit) {
		$seo = infra_loadJSON($src);
		$ans['seo'] = $seo;
	} else {
		$dir = autoedit_createPath($dir);

		$seo = $_POST['seo'];
		$def = $_POST['def'];
		$keys = array();
		infra_foro($seo, function ($val, $key) use (&$seo, &$def, &$keys) {
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
			$r = file_put_contents($src, infra_json_encode($keys));
		}
		if ($r) {
			return infra_ret($ans, 'SEO-данные сохранены');
		}

		return infra_err($ans, 'Ошибка. SEO-данные не сохранены');
	}
} elseif ($type === 'corfile') {
	if (!$submit) {
		$dirs=infra_dirs();
		$folder = preg_replace("/^\*/", $dirs['data'], $id);
		$folder = preg_replace("/^~/", $dirs['data'], $folder);
		$path = explode('/', $folder);
		$name = array_pop($path);//Так как папка заканчивается на / последний элемент в массиве буедт всегда пустым
		$parent = implode('/', $path).'/';
		$parent = preg_replace("/^infra\/data\//", '~', $parent);

		$ans['oldfolder'] = $parent;//Папка в которой можно увидеть обрабатываемую папку
		$ans['oldname'] = $name;

		$ans['content'] = infra_loadTEXT($id);

		return infra_ret($ans);
	} else {
		$file = $id;
		//$isdir=infra_theme($file,'sdn');
		//if($isdir) return infra_echo($ans,'Существует папка с именем как у файла '.$id);

		$isfile = infra_theme($file);
		if (!$isfile) {
			if (!autoedit_ext($file)) {
				$file .= '.tpl';
			}
			$ans['msg'] .= 'Файл был создан<br>';
			//$f=preg_replace('/^\*/','*/',infra_tofs($file));
			/*$p=explode('/',$f);
			if($p[0]=='*')$p[0]='infra/data';
			$f=array_pop($p);//достали файл*/
			$file = autoedit_createPath($file);
			//$file=infra_tofs($dir.$f);

			//$file=infra_theme($file,'sfnm');//Создали путь до файла

			if (!$file) {
				return infra_err($ans, 'Не удалось создать путь до файла'.$id);
			}
		} else {
			$file = $isfile;
		}

		$r = file_put_contents($file, $_REQUEST['content']);
		//autoedit_setLastFolderUpdate($file);
		if (!$r) {
			return infra_err($ans, 'Неудалось сохранить файл');
		}
		//$ans['noclose']=1;
		//$ans['autosave']=0;
		$ans['msg'] .= 'Cохранено';

		return infra_ret($ans);
	}
} elseif ($type == 'takeshow') {
	$takepath = autoedit_takepath();
	$list=array();
	array_map(function ($file) use (&$list, $takepath) {
		if ($file=='.' || $file=='..') {
			return;
		}
		$list[]=infra_toutf($file);
	}, scandir($takepath));

	$files = array();
	if ($list) {
		foreach ($list as $file) {
			$d = infra_loadJSON($takepath.$file);
			$dirs = infra_dirs();
			$d['path'] = str_replace($dirs['data'], '~', $d['path']);

			$d['modified'] = filemtime(infra_theme($d['path']));
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
		$file = infra_toutf($file);
		if (!$file) {
			$ans['noaction'] = true;//Собственно всё осталось как было
		} else {
			$takepath = autoedit_takepath($file);
			if (!$take && is_file($takepath)) {
				$r = @unlink($takepath);
				if (!$r) {
					return infra_err($ans, 'Неудалось отпустить файл');
				}
			} elseif ($take && !is_file($takepath)) {
				//Повторно захватывать не будем
				$save = array('path' => $id,'date' => time(),'ip' => $_SERVER['REMOTE_ADDR'],'browser' => $_SERVER['HTTP_USER_AGENT']);
				$r = file_put_contents($takepath, infra_json_encode($save));
				if (!$r) {
					return infra_err('Неудалось захватить файл');
				}
			} else {
				$ans['noaction'] = true;//Собственно всё осталось как было
				
			}
		}
	}
}

return infra_ret($ans);
