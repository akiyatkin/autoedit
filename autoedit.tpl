{admin:}
	
	<h1>Администрирование</h1>
	{:form}
		<input type="hidden" name="admin" value="{data.admin?:1?:0}">
		
		<div style="margin-bottom:10px">
			<span class="label label-{data.admin?:danger?:success}">
				Вы {data.admin?:администратор?:обычный пользователь}
			</span>&nbsp;
			<span class="label label-warning" id="cachechecklabel">
				проверка кэша...
			</span>

			<script>
				(function(){
					window.checkcacheres;
					var callback=function(r){
						if(r){
							$('#cachechecklabel').removeClass('label-warning').addClass('label-success').html('кэш используется');
						}else{
							$('#cachechecklabel').removeClass('label-warning').addClass('label-danger').html('кэш не используется');
						}
					}
					if(typeof(window.checkcacheres)=='undefined'){
						
						var request = new XMLHttpRequest();

						request.onreadystatechange=function(){
							if (request.readyState !== 4) return;
							var header=request.getResponseHeader('Infrajs-Cache');
							console.log(request.getAllResponseHeaders());
							window.checkcacheres=(header=='true');
							callback(window.checkcacheres);
						};
						request.open('HEAD', document.location, true);
						request.setRequestHeader("If-Modified-Since", new Date(0).toGMTString());
						request.send(null);
						
					}else{
						callback(window.checkcacheres);
					}
					
				})();
			</script>
		</div>
		<table>
			{data.admin|:admin_form}
		</table>
		<div style="margin-bottom:10px">
		{data.admin?:adminhelp}
		</div>
		<div style="margin-bottom:10px">
		{data.admin?:adminmenu}
		</div>
		{:submit}{data.admin?:Выйти?:Войти}{:/submit}
	{:/form}
	{admin_form:}
		<tr style="display:{data.admin?:none?}">
			<td style="vertical-align:middle;">Логин</td><td><input type="text" name="login" value=""></td></tr>
		<tr style="display:{data.admin?:none?}">
			<td style="vertical-align:middle;">Пароль&nbsp;</td><td><input type="password" name="pass" value=""></td></tr>
	{adminmenu:}
		<div class="row">
			<div class="col-sm-6">
				<div class="list-group">
					{AUTOEDIT.menu::adminmenuitem}
				</div>
			</div>
		</div>
	{adminmenuitem:}
		 <button type="button" class="list-group-item" onclick="AUTOEDIT.menu[{$key}].click()">{name}</button>
	{adminhelp:}
		<table>
			<tr>
				<td style="vertical-align:middle">
						<span style="font-size:18px">Редактор блоков</span>
				</td>
				<td style="padding-left:20px; vertical-align:middle"> 
						<input checked="checked" name="autoblockeditor" type="checkbox">
				</td>
			</tr>
		</table>
		<script>
			infra.require('vendor/vsn4ik/bootstrap-checkbox/dist/js/bootstrap-checkbox.min.js');
			infra.loadCSS('-autoedit/autoedit.css');
			Event.onext('Infrajs.onshow', function(){
				var layer=infrajs.getUnickLayer("{id}");
				var div=$('#'+layer.div);
				var box=div.find('[name="autoblockeditor"]');

				box.checkboxpicker({
					offClass:"btn-default",
					offLabel:'Выкл',
					onLabel:'Вкл'
				}).change(function(){
					var is=box.prop('checked');
					if(is){
						div.find('.autoblockeditorhelp').slideDown();
					}else{
						div.find('.autoblockeditorhelp').slideUp();
					}
					AUTOEDIT.active=is;
					if(is)AUTOEDIT.setHandlers();
				});
				var is=box.prop('checked');
				if(is){
					div.find('.autoblockeditorhelp').slideDown();
				}else{
					div.find('.autoblockeditorhelp').slideUp();
				}
				AUTOEDIT.active=is;
				if(is)AUTOEDIT.setHandlers();
			});
		</script>
		<div style="display:none; margin-top:10px;" class="autoblockeditorhelp">
			Для редактирования данных, закройте окно и наведите мышку на блок, в котором нужно поправить инфорацию. Если это возможно, блок подсветится и после клика по пустому месту в блоке откроется окно для редактирования.
		</div>
{version:}
	<h1>Информация о версии системы</h1>
	Последнее обнволение {~date(:j F Y,data.data.0.time)} г.
	<table class="table table-striped">
	{data.data::verrow}
	</table>
	{verrow:}<tr><td><b><a title="{~date(:j F Y,time)}" href="{homepage}" style="white-space:nowrap">{name}</a></b><br>{version}</td><td>{description}</td></tr>
{addfile:}
	<h1>Загрузить файл</h1>
	{:form}
		Папка <b><span class="aebutton" onclick="ADMIN('editfolder','{data.id}')">{data.id}</span></b><br>
		<input style="margin:5px 0" type="file" name="file"><br>
		<div style="margin:5px 0">
			<input type="checkbox" name="rewrite" style=""> — перезаписать, если такой файл уже есть
		</div>
		<div style="margin-bottom:5px">
		{config.ans.edit?:oldedit}
		{data.take?:oldtake}
		</div>
		{:submit}ОК{:/submit}
	{:/form}
	{oldedit:}
		<span class="aebutton" onclick="ADMIN('editfile','{data.id}{config.ans.name}')">{config.ans.name}</span>
	{oldtake:}
		, <span class="aebutton" onclick="ADMIN('takeinfo','{data.id}{config.ans.name}')" style="color:red">{~date(:_takedate,config.ans.take.date)}</span>
{takeshow:}
	{data:takeshow2}
	{takeshow2:}
		{files.length?:listshow?:nolistshow}
		{listshow:}
			<table class="table table-striped table-hover">
			<thead>
				<tr>
					<td></td><th>Файл</th><th>Дата отметки</th><th>Дата изменения</th><th>IP</th>
				</tr>
			</thead>
			<tbody>
				{files::listtakefiles}
			</tbody>
			</table>
		{listtakefiles:}
			<tr>
			<td><img alt=" " src="{infra.theme(:-autoedit/icons/)}{ext}.png" title="{ext}"></td>
			<td onclick="AUTOEDIT('editfile','{path}')" style="cursor:pointer; text-decoration:underline;">{path}</td>
			<td onclick="AUTOEDIT('takeinfo','{path}')" style="cursor:pointer; text-decoration:underline;">{$date(:_takedate, date)}</td>
			<td>{$date(:_takedate, modified)}</td>
			<td>{ip}</td>
			</tr>
		{nolistshow:}
			Сейчас нет редактируемых кем-то файлов
{editfile:}
	{data:editfile2}
	{editfile2:}
		<h1>Редактирование файла</h1>
		{:form}
		<input type="hidden" name="file" value="{file}">
		<input type="hidden" name="folder" value="{folder}">
		<table class="param">
			<tr><td>Папка:&nbsp;</td>
				<td><span class="btn btn-default btn-xs" onclick="AUTOEDIT('editfolder','{folder}')">{folder}</span></td></tr>
			<tr><td>Файл:&nbsp;</td>
				<td style="font-size:14px">
					<img alt=" " src="{infra.theme(:-autoedit/icons/)}{ext}.png" title="{ext}"> 
					{data.isfile?:editfilea?file}
					<span class="action">
						{data.isfile?:editfileload}
						{data.corable?:corable}
						{data.isfile?:editfiledel}
						<img alt="del" style="cursor:pointer" onclick="AUTOEDIT('renamefile','{folder}{file}')" title="Переименовать" src="{infra.theme(:-autoedit/images/rename.png)}"> 
					</span>
				</td></tr>
				{data.isfile?:editfileinfo}
		</table>
		{data.isfile?:getfile}
		<div style="border-top:dotted 1px gray; margin-top:5px;"></div>
		<small>{data.isfile?:editishelp?:editishelpis}</small>
		<table class="table" style="margin-top:5px; margin-bottom:5px;">
			<tr><td style="vertical-align:middle">{data.isfile?:Заменить новым?:Загрузить}</td>
				<td><input type="file" value="Обновить" name="file"></td></tr>
		</table>
		{:submit}Сохранить{:/submit}
		{:/form}
		{image?:editfileimage}
	{editfileimage:}<img style="margin-top:15px" src="/-imager/?w=300&src={folder}{file}">
	{editishelp:}Файл уже есть, имя загружаемого файла должно быть<br><i>{file}</i><br><input type="checkbox" name="passname">Не проверять имя загружаемого файла.
	{editishelpis:}Файла ещё нет, имя загружаемого файла не принимается во внимание, <br>будет установлено имя {file}
	{editfileinfo:}
			<tr><td>Размер</td>
			<td>{size} Кб</td></tr>
		<tr><td>Последние изменения</td>
			<td>{~date(:_takedate,time)}</td></tr>
	{_takedate:}H:i d.m.Y
	{editfilea:}
		<a style="text-decoration:underline" title="Открыть файл в браузере" target="_blank" href="/{path}">{file}</a>&nbsp;
	{editfileload:}
		<a href="{pathload}" onclick="AUTOEDIT.takefile('{config.id}',true)"><img alt="load" title="Скачать" src="{infra.theme(:-autoedit/images/floppy.png)}"></a>
	{editfiledel:}
		<img alt="del" style="cursor:pointer" onclick="AUTOEDIT('deletefile','{folder}{file}')" title="Удалить" src="{infra.theme(:-autoedit/images/delete.png)}"> 
	{corable:}
		<img alt="edit" style="cursor:pointer" onclick="AUTOEDIT('corfile','{folder}{file}');" title="Редактировать" src="{infra.theme(:-autoedit/images/edit.png)}"> 
		{data.rteable?:rteable}
	{getfile:}
		<div style="margin:5px">
			{data.take?:getfilebad?:getfilegood}
		</div>
	{getfilebad:}
		<span onclick="AUTOEDIT.takefile('{config.id}',false);" class="btn btn-default btn-xs">освободить файл</span>
		<span style="font-weight:bold; color:red;">Файл редактируется <span onclick="AUTOEDIT('takeinfo','{config.id}')" style="cursor:pointer; text-decoration:underline;">{$date(:_takedate,data.take)}</span></span>
	{getfilegood:}
		<span onclick="AUTOEDIT.takefile('{config.id}',true);" class="btn btn-default btn-xs">захватить файл</span>
		<span style="color:darkgreen">Файл можно редактировать</span>
		
	{rteable:}
		<img alt="rte" style="cursor:pointer" onclick="AUTOEDIT('rte','{folder}{file}');" title="Визуальный редактор" src="{infra.theme(:-autoedit/images/rte.png)}">
{takeinfo:}
	{data.take?data:takeyes?data:takeno}
	{takeno:} 
		<span class="btn btn-default btn-xs" onclick="AUTOEDIT('editfile','{data.path}')">{data.path}</span><br>
		Файл свободен для редактирования<br>
		<span class="btn btn-default btn-xs" onclick="popup.hide();AUTOEDIT.takefile('{data.path}',true)">Занять</span>
	{takeyes:}
		<table style="font-size:12px">
			<tr><th>Файл</th><td><img alt=" " src="{infra.theme(:-autoedit/icons/)}{ext}.png" title="{ext}"> <span class="btn btn-default btn-xs" onclick="AUTOEDIT('editfile','{data.path}')">{data.path}</span></td></tr>
			<tr><th>Дата отметки</th><td>{$date(:_takedate,data.take.date)}</td></tr>
			<tr><th>IP:</th><td>{data.take.ip|}</td></tr>
			<tr><th>Браузер:</th><td>{data.take.browser|}</td></tr>
		</table>
		<span class="btn btn-default btn-xs" onclick="popup.hide();AUTOEDIT.takefile('{data.take.path}',false)">Освободить</span> <span onclick="popup.op('<div style=\'width:300px\'><b>Файл редактируется или файл занят</b> &mdash; значит, что файл был кем-то скачен и сейчас в файл вносятся изменения. Если Вы не являетесь этим самым человеком необходимо выяснить кто не убрал отметку о редактировании файла. Иначе Ваши изменения могут быть затёрты.</div>');" class="btn btn-default btn-xs">помощь</span> 
{copyfile:}
	<h1>Создать копию файла?</h1>
	{:form}
		{:cfinfofile}
		<b>Создание копии</b><br>
		{:cffullpath}
		{:cfnewfilename}
		{:submit}Cкопировать{:/submit}
	{:/form}
	{cfnewfilename:}
		<!--Новое имя <input style="width:200px" type="text" name="newname" value="{data.name}"><br>-->
		<div style="margin:5px 0">
		<input style="width:200px" type="text" name="newname" value="{data.name}"> — Имя нового файла <br>
		</div>
	{cffullpath:}
		<div style="margin:5px 0">
			<input type="checkbox" name="full" onclick="popup.reparse();"> — задать полный путь<br>
		</div>
		<div id="fullpath" style="margin-top:5px; display:{autosave.full|:none}">
			<input type="text" style="width:200px;" name="newfolder" value="{data.folder}"> — Папка<br>
		</div>
	{cfinfofile:}
		<input type="hidden" name="oldfolder" value="{data.folder}">
		<input type="hidden" name="oldname" value="{data.name}">
		<table>
		<tr><td>Папка:&nbsp;</td><td><span class="btn btn-default btn-xs" onclick="AUTOEDIT('editfolder','{data.folder}')">{data.folder}</span></td></tr>
		<tr><td>Файл:&nbsp;</td><td><span class="btn btn-default btn-xs" onclick="AUTOEDIT('editfile','{data.id}')">{data.name}</span></td></tr>
		</table>
{deletefile:}
	<h1>Удалить файл?</h1>
	{:form}
		{:cfinfofile}
		{:submit}Удалить{:/submit}
	{:/form}
{renamefile:}
	<h1>Переименовать файл</h1>
	{data.isfile?:renamefilegood?:renamefilebad}
{renamefilebad:}Файл <b>{data.id}</b> не найден
{renamefilegood:}
	
	{:form}
		{:cfinfofile}
		{:cffullpath}
		{:cfnewfilename}
		{:submit}Переименовать{:/submit}
	{:/form}
{editfolder:}
	<h1>Редактирование папки</h1>
	<div><b>{data.id}</b></div>
	<!--<span class="a" onclick="name=prompt('Укажите имя нового файла, после этого Вы сможете его загрузить');if(name)AUTOEDIT('editfile','{data.id}'+name);">cоздать файл</span> -->
	<span class="btn btn-default btn-xs" onclick="AUTOEDIT('addfile','{data.id}')">загрузить файл</span>
	<span class="btn btn-default btn-xs" onclick="AUTOEDIT('corfile','{data.id}Новый файл.tpl')">создать файл</span>
	<span class="btn btn-default btn-xs" onclick="AUTOEDIT('mkdir','{data.id}')">создать папку</span>
	<table class="teditfolder table table-striped table-hover table-clicked" style="margin-top:10px">
		<thead>
			<tr onmouseover="$(this).find('.action').css('visibility','visible')" onmouseout="$(this).find('.action').css('visibility','hidden')">
				<td></td><td>Файл</td><td>Кб</td><td colspan="2">Дата&nbsp;изменения</td>
			</tr>
		</thead>
		<tbody>
			{data.parent:edftop}
			{data.folders::folders}
			{data.list::file}
		</tbody>
	</table>
	{:close}Закрыть{:/close}
	{edftop:}
		<tr>
			<td style="cursor:pointer" onclick="AUTOEDIT('editfolder','{data.parent}')">
				<img src="{infra.theme(:-autoedit/icons/dir.png)}" title="dir">
			</td>
			<td style="cursor:pointer" onclick="AUTOEDIT('editfolder','{data.parent}')">
				..
			</td>
			<td></td>
			<td></td>
			<td></td>
		</tr>
	{folders:}
		<tr style="color:{take?red}" onmouseover="$(this).find('.action').css('visibility','visible')" onmouseout="$(this).find('.action').css('visibility','hidden')">
			<td style="cursor:pointer" onclick="AUTOEDIT('editfolder','{data.id}{name}/')"><img src="{infra.theme(:-autoedit/icons/)}dir.png" title="dir"></td>
			<td style="cursor:pointer" onclick="AUTOEDIT('editfolder','{data.id}{name}/')">
				{name}
			</td>
			<td>&nbsp;</td><td>{~date(:d.m.Y,time)}</td>
			<td>
				<span class="action" style="visibility:hidden">
					<img alt="del" style="cursor:pointer" onclick="AUTOEDIT('rmdir','{data.id}{name}/')" title="Удалить" src="{infra.theme(:-autoedit/images/delete.png)}"> 
					<img alt="name" style="cursor:pointer" onclick="AUTOEDIT('mvdir','{data.id}{name}/')" title="Переименовать" src="{infra.theme(:-autoedit/images/rename.png)}">
					<!--<img alt="copy" style="cursor:pointer" onclick="AUTOEDIT('cpdir','{data.id}{name}/')" title="Создать копию" src="{:-autoedit/images/copy.png}"> -->
				</span>
			</td>
		</tr>
	{file:}
		<tr style="color:{take?red}" onmouseover="$(this).find('.action').css('visibility','visible')" onmouseout="$(this).find('.action').css('visibility','hidden')">
			<td style="cursor:pointer" onclick="AUTOEDIT('editfile','{data.id}{name}{ext?:point}{ext}')"><img alt="" src="{infra.theme(:-autoedit/icons/)}{ext}.png" title="{ext}"></td>
			<td style="cursor:pointer" onclick="AUTOEDIT('editfile','{data.id}{name}{ext?:point}{ext}')">
				{file}{:strtake}
			</td>
			<td>{size}</td><td>{~date(:d.m.Y,time)}</td>
			<td>{mytake?:actions}</td>
		</tr>
	{point:}.
	{strtake:}
			
			{take?:strtakenow}
	{strtakenow:}
		<span class="btn btn-xs {mytake?:btn-warning?:btn-danger}" onclick="AUTOEDIT('takeinfo','{data.id}{name}{ext?:point}{ext}')">{~date(:_takedate,take)}</span>
	{actions:}
			
				<nobr class="action" style="visibility:hidden">
					<a href="{pathload}"><img alt="load" 
					title="Скачать" src="/-autoedit/images/floppy.png"></a>
					<img alt="del" style="cursor:pointer" onclick="AUTOEDIT('deletefile','{data.id}{file}')" title="Удалить" src="{infra.theme(:-autoedit/images/delete.png)}"> 
					<img alt="name" style="cursor:pointer" onclick="AUTOEDIT('renamefile','{data.id}{file}')" title="Переименовать/переместить" src="{infra.theme(:-autoedit/images/rename.png)}">
					<img alt="copy" style="cursor:pointer" onclick="AUTOEDIT('copyfile','{data.id}{file}')" title="Создать копию" src="{infra.theme(:-autoedit/images/copy.png)}"> 
					{corable?:cancorfile}
					{rteable?:filerteable}
				</nobr>
			
	{cancorfile:}
			<img alt="edit" style="cursor:pointer" onclick="AUTOEDIT('corfile','{data.id}{name}{ext?:point}{ext}');" title="Редактировать" src="{infra.theme(:-autoedit/images/edit.png)}"> 
	{filerteable:}
		<img alt="rte" style="cursor:pointer" onclick="AUTOEDIT('rte','{config.id}{name}{ext?:point}{ext}');" title="Визуальный редактор" src="{infra.theme(:-autoedit/images/rte.png)}"> 
{404:}
	Информация по слою не найдена
{allblocks:}
	<h1>Блоки на открытой странице</h1>
	<div id="allblockslist"></div>
	<script>
		Event.one('Infrajs.onshow', function(){
			var list={ };
			infrajs.run(infrajs.getAllLayers(),function(layer){
				if(!layer.showed)return;
				if(!layer.autoedit)return;
				var title=layer.autoedit.title||layer.autoedit.text||layer.autoedit.fast||layer.autoedit.html||layer.tplroot;
				list[layer.id]={
					title:title,
					layer:layer,
					id:layer.id
				};
			});
			var html=infra.template.parse('-autoedit/autoedit.tpl',list,'allblockslist');
			var div=document.getElementById('allblockslist');
			div.innerHTML=html;


			var layer=infrajs.getUnickLayer({id});
			div=$(div);
			for(var i in list){
				(function(){
					var block = list[i];
					div.find('.block'+i).click(function(){
						AUTOEDIT.checkLayer(block.layer);
					});
				})();
			};
		});
	</script>

	{allblockslist:}
		{::allblock}
	{allblock:}
		<span class="btn btn-default btn-xs block{id}">{title|layer}</span> 
{corfile:}
	{:style}
	{:form}
		{:infofile}
		<textarea autosavebreak="1" style="font-family:Tahoma; font-size:12px; color:#444444; width:500px; height:300px" name="content">{data.content}</textarea>
		<br>
		{:submit}Сохранить{:/submit}
	{:/form}
{jsoneditor:}
	{:style}
	{:form}
		{:infofile}
		<textarea autosavebreak="1" style="font-family:Tahoma; font-size:12px; color:#444444; width:500px; height:300px" name="content">{data.content}</textarea>
		<br>
		{:submit}Сохранить{:/submit}
	{:/form}
	<script type="text/javascript">
		Event.one('Infrajs.oncheck', function(){

			var layer=infrajs.getUnickLayer("{id}");
			var ta=$('#'+layer.div).find('textarea').get(0);
			
			var id="{config.id}";
			var d=id.split('|');
			var file=d[0];
			var schema={ };
			if(d[1])schema=schema[d[1]];
			if(!schema){
				schema=infra.loadJSON(d[1]);
			}
			AUTOEDIT.jsonedit(ta,schema);
		});
	</script>
{cpdir:}
	{:style}
	{:form}
		{:infofolder}
		{:fullpath}
		{:newfilename}
		{:submit}Скопировать{:/submit}
	{:/form}

{mkdir:}
	{:style}
	{:form}
		{:infofolder}
		{:fullpath}
		{:newfilename}
		{:submit}Создать{:/submit}
	{:/form}

{rmdir:}
	{:style}
	{:form}
		{:infofolder}
		{:submit}Удалить{:/submit}
	{:/form}

{mvdir:}
	{:style}
	{:form}
		{:infofolder}
		{:fullpath}
		{:newfilename}
		{:submit}Применить{:/submit}
	{:/form}
{form:}
	<form action="/-autoedit/autoedit.php?submit=1" method="post">
	<input type="hidden" name="type" value="{config.type}">
	<input type="hidden" name="id" value="{config.id}">
{/form:}
	{:close}Отмена{:/close}
	</form>

	
		{config.ans.msg?config.ans:ansmsg}
{ansmsg:}
	<div style="margin-top:10px" class="alert alert-{result?:success?:danger}" role="alert">
		{msg}
	</div>
{submit:}<input class="btn btn-danger" type="submit" value="{/submit:}">
{close:}
	<input class="btn btn-default" type="button" value="{/close:}" onclick="popup.hide()">
{style:}
	<style>
		#{div} {
			font-size:12px;
		}
		/*.aebutton {
			cursor:pointer;
			text-decoration:none;
			border-bottom:dashed 1px gray;
		}*/
		#{div} .imgsize {
			display:none;
			padding:10px; 
			margin:10px; 
		}
		#{div} .imgsel img {
			margin:4px;
			cursor:pointer;

		}
		#{div} .imgsel img.select {
			border:solid 2px red;
			margin:2px;
		} 
		#{div} .help {
			border:dotted gray 1px; 
			padding:10px; 
			margin:10px; 
			display:none;
		}
		#{div} .imgblock {
			display:none;
		}
		#{div} .imgsize .show {
			margin:10px 0;
		}
		#{div} .imgsize .show img {
			border:solid 1px gray;
		}
		#{div} .imgsize {
			border:dotted gray 1px; 
			display:none;
			padding:10px; 
			margin:10px; 
		}
		#{div} h1 {
			margin-top:0;
			text-align:center;
		}
		#{div} form {
			margin:0;
			padding:0 0 5px 0;
		}
	</style>

{seo:}
	{infrajs.seo.get():seotpl}
{seotpl:}
	Страница: <b><a href="/{data.id}">{data.id|:Главная}</a></b>
	{:form}

	<h2>Заголовок - Title</h2>
	<center>
		<textarea name="def[title]" style="display:none">{title|}</textarea>
		<textarea name="seo[title]" style="font-family:Tahoma; font-size:12px; color:{data.seo.title?:green?:#444444}; width:500px; height:34px">{data.seo.title|(title|)}</textarea>
	</center>

	<h2>Описание - Description</h2>
	<center>
		<textarea name="def[description]" style="display:none">{description|}</textarea>
		<textarea name="seo[description]" style="font-family:Tahoma; font-size:12px; color:{data.seo.description?:green?:#444444}; width:500px; height:96px">{data.seo.description|(description|)}</textarea>
	</center>

	<h2>Ключевые слова - Keywords</h2>
	<center>
		<textarea name="def[keywords]" style="display:none">{keywords|}</textarea>
		<textarea name="seo[keywords]" style="font-family:Tahoma; font-size:12px; color:{data.seo.keywords?:green?:#444444}; width:500px; height:96px">{data.seo.keywords|(keywords|)}</textarea>
	</center>

	{:submit}Сохранить{:/submit}
	{:/form}
{infofile:}
	<input type="hidden" name="oldfolder" value="{data.oldfolder}">
	<input type="hidden" name="oldname" value="{data.oldname}">
	<table style="margin-bottom:5px">
	<tr><td>Каталог:&nbsp;</td><td><span class="btn btn-default btn-xs" onclick="AUTOEDIT('editfolder','{data.oldfolder}')">{data.oldfolder}</span></td></tr>
	{data.oldname:infofile_1}
	</table>
{infofile_1:}<tr><td>Файл:</td><td><b>{.}</b></td></tr>
{infofolder:}
	<input type="hidden" name="oldfolder" value="{data.oldfolder}">
	<input type="hidden" name="oldname" value="{data.oldname}">
	<table>
	<tr><td>Каталог:&nbsp;</td><td><span class="btn btn-default btn-xs" onclick="AUTOEDIT('editfolder','{data.oldfolder}')">{data.oldfolder}</span></td></tr>
	{data.oldname:infofoldername}
	</table>
	{infofoldername:}<tr><td></td><td><b><span class="btn btn-default btn-xs" onclick="AUTOEDIT('editfolder','{data.oldfolder}{data.oldname}/')">{data.oldname}</span></b></td></tr>
{fullpath:}
	<div style="margin:5px 0">
		<input type="checkbox" name="full" onclick="popup.reparse();"> — задать новый полный путь<br>
	</div>
	{:fullpath2}
{fullpath2:}
	<div id="fullpath" style="margin-top:5px; display:{autosave.full|:none}">
		<input type="text" style="width:200px;" name="newfolder" value="{data.oldfolder}"> — Папка<br>
	</div>
{newfilename:}
	<!--Новое имя <input style="width:200px" type="text" name="newname" value="{data.oldname}"><br>-->
	<div style="margin:5px 0">
		<input style="width:200px" type="text" name="newname" value="{data.oldname|}"> — Имя новой папки<br>
	</div>

{rteimg:}
	<img title="{}" alt="{}" onclick="$('.imgsize').slideDown('fast'); $(this).parent().find('.select').removeClass('select'); $(this).addClass('select')" orig="{}" src="{infra.theme(-imager/imager.php)}?src={..folder}{}&w=100&h=100&crop=1">
