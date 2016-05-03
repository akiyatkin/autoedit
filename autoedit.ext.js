
infrajs.autoeditInit=function(){
	infrajs.externalAdd('autoedittpl',function(now,ext,layer,external,i){
		if(layer[i.replace(/tpl$/,'')])return;
		if(layer[i])return;
		if(!now)now=ext;
		return now;
	});
	
	$(document).bind('keydown',function(event){
		if (event.keyCode == 113){
			//infra.loader.show();
			infra.require('-autoedit/autoedit.js');
			AUTOEDIT('admin');
		}
	});
}
infrajs.autoeditLink=function(){//infrajs onshow
	$('.showAdmin[showAdmin!=true]').attr('nohref','1').attr('showAdmin','true').click(function(){
		infra.loader.show();
		infra.require('-autoedit/autoedit.js');
		AUTOEDIT('admin');
		return false;
	});
}
infrajs.autoedit_SaveOpenedWin=function(){
	if(!window.sessionStorage)return;
	if(!window.AUTOEDIT)return;	
	for(var i in window.AUTOEDIT.popups){
		var layer=window.AUTOEDIT.popups[i];
		if(!layer.showed)continue;
		infrajs.popup_memorize('infra.require("-autoedit/autoedit.js");AUTOEDIT("'+layer.config.type+'","'+layer.config.id+'");');
	}
}


Event.one('Infrajs.oninit',function(){
	//autoedit
	if (infra.admin(true)) infra.theme.prefix = '-nostore=true';
	else  infra.theme.prefix = '';

	infrajs.autoeditInit();	
});

Event.handler('Infrajs.onshow',function(){
	//autoedit
	infrajs.autoeditLink();
});
Event.handler('Infrajs.onshow',function(){
	//autoedit
	if(!window.AUTOEDIT)return;
	if(!AUTOEDIT.active)return;
	if(!infra.admin())return;
	AUTOEDIT.setHandlers();
});
Event.handler('Infrajs.onshow',function(){
	//autoedit
	infrajs.autoedit_SaveOpenedWin();
});
