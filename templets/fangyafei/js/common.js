/*
 * ue执行脚本
 * author:gaoshiyong<gaoshiyong1272@vip.163.com>
 * jQuery版本必须在1.42以上
 */

var UE = window.UE || {};


/**
 * [log 输出日志,支持原生console和alert输出日志]
 * @return 
 */
UE.LOG = UE.log = function() {
	if (typeof(console) == "object" && typeof(console.log) == "function") console.log.apply(console, arguments);
};


/**
 * [UE.namespace 创建局部命名空间]
 * @param  {[sting]} ns [传入命名空间字符串]
 * @return {[object]}   [返回命名空间对象]
 */
UE.namespace = function(ns) {
    if (!ns || !ns.length) return null;
	var levels = ns.split(".");
    var nsobj = UE;
	for (var i=(levels[0] == "UE") ? 1 : 0; i<levels.length; ++i) {
        nsobj[levels[i]] = nsobj[levels[i]] || {};
        nsobj = nsobj[levels[i]];
    }
	return nsobj;
};


/**
 * 创建工具类对象
 */
UE.namespace('UE.util');


/**
 * [UE.util.ajax description] 异步请求地址
 * @param options.url(String)  请求连接地址必选是完整的链接地址 （必选）
 * @param options.type (String) 请求类型 默认 - post
 * @param options.data(String)  请求参数 （可选）
 * @param options.textType (String)  返回数据类型 默认是json 支持格式为jQuery模式
 * @param options.success (function)  请求成功处理方法 （可选）
 * @param options.error (function)  请求失败处理方法（可选）
 * @return {[type]} [description]
 */
UE.util.ajax = function(options){
	
	if(!options.url) {
		if(config.debug) alert('请输入请求地址！')
		return;
	}

	//设置默认参数
	var definOpt = {
		url : null,
		type : 'post',
		dataType : 'json',
		data : {},
		success : null,
		error : null,
		timeout : 20000,
		cache : false
	} 

	//合并参数
	var opt = $.extend({},definOpt,options); 
	
	//设置请求方式
	opt.type = opt.type == 'get' ? 'GET' : 'POST';
	
	//判断是否为同域
	var host = location.protocol + '//' + location.hostname;
	var crossdomain = opt.url.substr(0, host.length) == host ? false : true;
	if(crossdomain) {
		opt.type = 'GET';
		opt.dataType = "jsonp";
		opt.jsonp = "callback";
	}
	$.ajax(opt);
}

/**
 * 创建配置类对象
 */
UE.namespace('UE.config');
UE.config._setting = {};


/**
 * [UE.config.get description]  获取配置信息，如果没有找到该配置返回null
 * @param  {[string]} key [description] 配置名字
 * @return {[type]}     [description] 	返回配置信息(String)
 */
UE.config.get = function(key) {
	return UE.config._setting[key] ? UE.config._setting[key] : null;
};


/**
 * [UE.config.set description] 写入配置信息
 * @param {[string]} key   [description] 配置名字
 * @param {[string]} value [description] 配置信息
 */
UE.config.set = function(key, value) {
	if ($.isPlainObject(key)) {
		$.extend(UE.config._setting, key);
	} else {
		UE.config._setting[key] = value;
	}
};

//config初始化
UE.config.set('host',location.protocol + '//' + location.hostname + '/');


/**
 * [UE.util.tab description] tab切换类操作
 * @param  {[string]} opt.eType   [description] 事件类型 hover 和 click  默认 click
 * @param  {[object]} opt.tabEleParent  [description] 	切换元素对象 
 * @param  {[object]} opt.contEleParent [description]   被切换元素对象
 * @param  {[string]} opt.css [description]   			切换当前元素样式
 * @param  {[type]} opt.callback [description]   		切换后回调方法
 */
UE.util.tab = function(options){
	var defin = {
		eType : 'click',
		tabEleParent : null,
		contEleParent : null,
		css : 'active',
		callback : null
	}
	var opt = $.extend({} , defin , options ? options : {});
	if(opt.eType != 'click' && opt.eType != 'hover' ) return;
	opt.tabEleParent.children()[opt.eType](function(e){
		if($(this).hasClass(opt.css)) return false;
		var index = $(this).index();
		$(this).addClass(opt.css).siblings().removeClass(opt.css);
		opt.contEleParent.children().eq(index).show().siblings().hide();
		if(typeof opt.callback == 'function' ) opt.callback(opt);
		e.stopPropagation();
		return false;
	});	
};


/**
 * [UE.util.getElementInfo description] 获取元素对象高，宽，坐标值,没有找对元素节点对象返回null
 * @param  {[object]} element [description] 元素节点
 * @return {[object]}         [description] 
 */
UE.util.getElementInfo = function(element){
	var json = {};
	if(element === undefined) element = $(window); 
	if($(element).length == 0) return null;
	if(element.get(0) == window){
		json.h = $(element).height();
		json.w = $(element).width();
		json.st = $(element).scrollTop();
		json.sl = $(element).scrollLeft();
	}else{
		json.h = $(element).innerHeight();
		json.w = $(element).innerWidth();
	}
	if(element.get(0) == window) return json;
	json.t = Math.floor($(element).offset().top);
	json.l = Math.floor($(element).offset().left);
	return json;
};



/**
 * [description] 绑定用户头像相关事件
 * @param  {[type]} ){})( [description]
 * @return {[type]}         [description]
 */
;(function(){
	var ueAvatarImage = $('.ue-avatarImage');
	if(ueAvatarImage.length == 0) return;
	ueAvatarImage.each(function(index, element) {
		if($(this).find('.ue-avatarImage-img a').length > 0) $(this).css({'cursor':'pointer'});
	});
	ueAvatarImage.live('click',function(){
		var url = $(this).find('.ue-avatarImage-img a').attr('href');
		if(!url) return false;
		window.location.href = url;
	});	
})();


/**
 * [description] 绑定input 和 textarea 焦点和失去焦点事件
 * @return {[type]} [description]
 */
(function(){
	var foucsCss = 'blur';
	$('input[data-type=blur],textarea[data-type=blur],select[data-type=blur]').focus(function(e) {
		$(this).addClass(foucsCss);
	}).blur(function(e) {
		$(this).removeClass(foucsCss);
	});;
})();

/**
 * [description]  消费记录设置table列基偶显示样式
 * @param  {[type]} ){})( [description]
 * @return {[type]}         [description]
 */
(function(){
	var table = $('.ue-my-costTable table');
	if(table.length == 0) return;
	$(table).find('tr').each(function(index,element){
		if((index + 1)%2 == 0) $(this).addClass('bg');
	});
})();

/**
 * [description]  消费记录设置table列基偶显示样式
 * @param  {[type]} ){})( [description]
 * @return {[type]}         [description]
 */
(function(){
	var table = $('.ue-my-tools table');
	if(table.length == 0) return;
	$(table).find('tr').each(function(index,element){
		if((index + 1)%2 != 0) $(this).addClass('bg');
	});
})();


/**
 * [ description] 下拉菜单组件
 * @return {[type]} [description]
 */
(function(){

//初始化值
var defined = {
	actCss : 'actClassname',
	hoverCss : 'hoverClass',
	btnEl : null,
	menuEl : null,
	width : null,
	dir : 'left',
	time : 100
},_lastObjcet = null;
var count = 0;
var temp = 'positionHaddle_';
var reSizeObj = {};

UE.namespace('UE.menu');

//构造器
var menu = function(options){
	this.opt = $.extend({}, defined, options);
	this.opt.over = true;
	this.opt.timer = null;
	count++;
}

//扩展方法
menu.prototype = {
	
	//点击事件
	click : function(){
		var that = this;
		if(this.opt.btnEl.length == 0 || this.opt.menuEl.length == 0) return;

		//处理下拉菜单位置
		this.position();

		//绑定事件
		this.opt.btnEl.bind('click', function(e) {
			var _this = $(this);
			if(that.opt.menuEl.is(':hidden')) {
				_this.removeClass(that.opt.hoverCss).addClass(that.opt.actCss);
				that.opt.menuEl.show();
				$('body').bind('click',function(e){
					that.opt.menuEl.hide();
					_this.removeClass(that.opt.actCss).addClass(that.opt.hoverCss);
					$(this).unbind('click');
					e.stopPropagation();
				});
				that.opt.menuEl.bind('click',function(e){
					e.stopPropagation();	
				});
			}else {
				that.opt.menuEl.hide();
				_this.removeClass(that.opt.hoverCss).addClass(that.opt.actCss);	
			}
			e.preventDefault();	
			e.stopPropagation();
		});

		//压入事件
		reSizeObj[temp + count] = function(){
			that.position();	
		}

		//初始化窗口事件
		that.reSize();
	},

	//计算位置
	position : function(){
		var info = UE.util.getElementInfo(this.opt.btnEl);
		if(!this.opt.width) this.opt.width = $(this.opt.menuEl).outerWidth(true);
		var top = info.h + info.t;	
		if(this.opt.dir == 'left') {
			var left = info.l;
		}else if(this.opt.dir == 'right'){
			var left = info.l - ($(this.opt.menuEl).outerWidth(true) -  info.w);	
		}else if(this.opt.dir == 'center') {
			var num = $(this.opt.menuEl).outerWidth(true)/2 - info.w/2;
			var left = info.l - num;
		}
		this.opt.menuEl.css({left:left,top:top,width:this.opt.width});	
	},

	//鼠标滑过下拉菜单
	hover : function(){
		var that = this;
		if(this.opt.btnEl.length == 0 || this.opt.menuEl.length == 0) return;
		
		//处理下拉菜单位置
		this.position();

		//绑定触发按钮事件
		this.opt.btnEl.mouseover(function(e) {
			if(that.opt.over) {
				that.opt.menuEl.show();
				that.opt.btnEl.addClass(that.opt.hoverCss).removeClass(that.opt.actCss);
				that.opt.over = false;	
			}
		}).mouseout(function(e) {
			that.opt.over = true;
			setTimeout(function(){
				that.hideTimer()
			},50);
		}).click(function(){
			return false;
		});

		//绑定下来菜单事件
		this.opt.menuEl.mouseover(function(e) {
			that.opt.over = false;
		}).mouseout(function(e) {
			that.opt.over = true;
			setTimeout(function(){
				that.hideTimer();
			},50);
		});

		//压入事件
		reSizeObj[temp + count] = function(){
			that.position();	
		}

		//初始化窗口事件
		that.reSize();
	},

	//隐藏处理
	hideTimer : function(){ 
		if(this.opt.over){
			this.opt.menuEl.hide();
			this.opt.btnEl.removeClass(this.opt.hoverCss).addClass(this.opt.actCss);
		} 
	},

	//窗体发生改变时处理事件
	reSize : function(){
		var that = this;
		var timeer = null;
		$(window).unbind('resize').bind('resize',function(e){
			if(timeer) clearTimeout(timeer);
			timeer = setTimeout(function(){
				for( var key in reSizeObj){
					if(typeof reSizeObj[key] == 'function') {
						reSizeObj[key]();
					}
				}
			},that.opt.time);
		});
	}	
}

var createObj = function(options){
	if(_lastObjcet) _lastObjcet = null;
	_lastObjcet = new menu(options);
	return _lastObjcet;
}

//对外方法鼠标滑过事件
UE.menu.hover = function(options){
	options = options == undefined ? {} : options;
	createObj(options).hover(); 
}

//对外方法鼠标滑过事件
UE.menu.click = function(options){
	options = options == undefined ? {} : options;
	createObj(options).click(); 
}

})();

/*
UE.menu.hover({
	btnEl : $('.ue-logined-info'),
	menuEl : $('.ue-logined-nav'),
	width : 108,
	hoverCss : 'my_down acitve',
	actCss : 'my_up acitve'
});


UE.menu.hover({
	btnEl : $('.ue-logined-news'),	//下拉菜单触发按钮jQuery对象
	menuEl : $('.ue-logined-msg'),	//下拉菜单外框jQuery对象
	dir : 'right',	//控制下拉菜单对齐方式 默认为左侧对齐 （可选）
	width : null,   //控制下拉菜单宽度（可选）
	hoverCss : 'active',  //鼠标滑过样式（可选）
	actCss : 'actClassname' //鼠标离开之后样式或者初始化样式（可选）
});
*/


/**
 * 弹窗组件效果操作方法
 * @param
 * @return
 */
;(function($){
	
var _zindex = 5000; //5000;
var timeHider = null;
var _count = 0;
var _over = true;
var _load = '<div class="ue-load"><p></p><div style="padding:10px 0 0">拼命的为您加载中，请稍后..</div></div>';
var _lastDialog = null;
var _defaults = {
    id : false,				
    title : '窗口模式',
    showClose : true,
    time : 0,
    mask : true,
    width : 350,
    element : null,
    btn : null,
    btnAlign : 'right',
    align : 'center',
    drag : true,
    fixTop : 0,
    checkLogin : false,
    remove : true,
    dir : 'down',
    css : 'dialogCss',
    icoAlign : 'center',
    eType : 'show',
    active : 0,
    actCss : 'active',
    hideTitle : false,
	tpl : 'card_html',
    shortcut : true,
    type : 'post',
	arrow : null,
	loadType : null
};

	
var fDialog = function(content,options){
	this.options = $.extend({},_defaults,options);
	this.content = content;
	this.count = _count;
	_count++;
    _zindex ++ ;
}

fDialog.prototype = {
	
	//自动关闭按钮处理方法
	autoClose : function(element){
		if(this.options.time !== 0) {
			timeHider = setTimeout(function(){
				element.find('.ue-close a').click();
			},this.options.time);
		}	
	},
	
	//当窗口弹出事件为隐藏的时候处理方法
	tower : function(element){
		var index = $(element).attr('dialog_count');
		$(element).attr('dialog_flag',true);
		var selector = 	'#' + (this.options.id ? this.options.id : 'dialog_' + index);
		if(this.content){ $(selector).find('.dialog_content').html(this.content);}
		this.setContent($(selector),this.options.width);
		this.autoClose($(selector));
		this.maskDiv(index);
	},
	
	//弹出窗口初始化事件处理
	one : function(element){
		var that = this; 
		
		//给窗口设置关联数字
		$(element).attr('dialog_count',that.count);
		$(element).attr('dialog_flag',true);
		
		//给窗口设置样式和选择器
		var html = $(that[that.options.tpl]()).css('width',that.options.width).attr('dialog_count',that.count).addClass('dialog_tips');
		html.attr('id',that.options.id ? that.options.id : 'dialog_' + (that.count));
		if(that.content && this.options.loadType){
			html.find('.dialog_content')[this.options.loadType](that.content);
		} else {
			html.find('.dialog_content').html(that.content);
		}
		
		//是否显示窗口标题
		if(that.options.hideTitle) html.find('.ue-title').hide();
		else html.find('.ue-title p').html('<span></span><em>' + that.options.title + '</em>');
		
		//给关闭窗口设置关联数字 和 是否显示关闭按钮
		html.find('.ue-title .close a').attr('dialog_count',that.count);
		if(!that.options.showClose) html.find('.ue-title .close a').hide();
		if(!that.options.arrow) html.find('.arrow').remove(); 
		
		//对显示按钮相关操作
		if(that.options.btn) {
			that.btn(html,that.options.btn,that.count);
			html.find('.ue-btn').addClass('btn_' + that.options.btnAlign);
		}else html.find('.ue-btn').remove();
		html.find('.ue-btn').css('width',that.options.width - 44);
		$('body').append(html);
		return html;
	},
	
	//回去选择器对象
	getElement : function(){
		if(!this.options.element || this.options.element.get(0) == window){
			$('body').append('<div id="definElemenetDiv_'+ (this.count) +'" style="display:none"></div>');
			var el = $('#definElemenetDiv_' + (this.count));
			return el;
		}else return this.options.element;
	}, 
	
	//绑定手动关闭事件
	close : function(element,dialog,fn){
		$(element).removeAttr('dialog_flag');
		var index = element.attr('dialog_count');
		//if(this.options.drag) $('body').removeAttr('onselectstart','return false').removeAttr('style');
		if(typeof timeHider != 'undefined') {
			clearTimeout(timeHider);
			timeHider = null;
		}
		
		//绑定窗口关闭回调事件
		if(typeof fn == 'function'){
			fn();
		}else{
			if(typeof this.options.closeCallback == 'function') this.options.closeCallback(dialog,this.options);
		}
		
		//是否销毁窗口和遮罩层
		if(this.options.remove) {
			element.removeAttr('dialog_count');
			dialog.remove();
			if($('#mask_opacity_' + index).length > 0) $('#mask_opacity_' + index ).remove();
		}else {
			dialog.fadeOut(500);
			if($('#mask_opacity_' + index).length > 0) $('#mask_opacity_' + index ).fadeOut(500);
		}
		if($('#definElemenetDiv_' + (this.count)).length > 0) $('#definElemenetDiv_' + (this.count)).remove();
		
		
	},
	
	//生成遮罩层
	maskDiv : function(index){
		if(!this.options.mask) return;
		var html = $('<div id="mask_opacity_'+ index +'"></div>');
		var h = Math.max($(window).outerHeight(true),$('body').outerHeight(true));
		if($('#mask_opacity_' + index).length > 0 ){
			$('#mask_opacity_' + index).show();
		}else{
			$('body').append(html);
			html.css({position : 'absolute' , top : 0 , left : 0 , width : '100%' , height : h, opacity:0.2,'background-color':'#000','z-index':_zindex -1});
		}
	},
	
	//提示类弹出
	tips : function(){
		var element = this.getElement(),that = this;
		
		//只能执行一次
		if($(element).attr('dialog_flag') == 'true') return;
		
		//第二次进入处理方法
		if($(element).attr('dialog_count')) {
			this.tower(element);
			return;
		}
		
		//初始化窗口页面
		var ohtml =  this.one(element);
		
		//居中显示处理方法
		if(that.options.align == 'center'){
			ohtml.hide();
			that.setContent(ohtml,that.options.width);
			that.autoClose(ohtml);
			that.maskDiv(that.count);
		}
		
		//手动关闭处理方法
		ohtml.find('.ue-close a').unbind('click').click(function(){
			that.close(element,ohtml);
			return false
		});
		
		//弹出之后，如果传入回调方法时，绑定回调事件
		if(typeof that.options.callback == 'function'){
			that.options.callback(ohtml,that.options);
		}
	},
	
	//后台弹出框操作处理方法
	box : function(){
		var element = this.getElement(),that = this;
		if(this.options.checkLogin && !SMK.util.getLoginStatus()){
			SMK.util.checkLogin()
			return; //是否开启登录检测
		}
		//检测是否传入入请求地址
		if(!that.options.url){
			if(gVConfig && gVConfig.debug) alert('请您传入请求地址！');
			return;
		}  
		
		//第二次进入处理方法
		if($(element).attr('dialog_count')) {
			this.tower(element);
			return;
		}
		
		//初始化窗口页面
		var ohtml =  this.one(element);
		
		//设置预加载处理方法
		ohtml.find('.dialog_content').html(_load);
		this.maskDiv(that.count);
		$('body').append(ohtml);
		this.setContent(ohtml,this.options.width,true);
		
		//手动关闭处理方法
		var callret = null;
		ohtml.find('.ue-close a').unbind('click').click(function(){
			that.close(element,ohtml,function(){
				if(typeof that.options.closeCallback == 'function'){
					that.options.closeCallback(ohtml,that.options,callret);
				}	
			});
			return false
		});
		
		//与后台交互处理方法
		UE.util.ajax({
			type : this.options.type,
			url : this.options.url,
			data : this.options.data,
			success : function(ret){
				setTimeout(function(){
					callret = ret;
					if(ret && (ret.code == 1)){ 
						ohtml.find('.dialog_content').html(ret.data.html);
						that.setContent(ohtml,that.options.width,true);
						if(typeof(that.options.callback) == 'function') that.options.callback(ohtml,that.options,ret);
					}	
				},500);	
			},
			error : function(ret){
				alert('与后台交互出错了\n' + ret.responseText);	
			}
		});
	},
	
	//自定义设置按钮
	btn : function(dom,btn,dialog_count){
		var me = this;
		for(val in btn){
			(function(_btn){
				if(typeof _btn.text != 'undefined' && typeof _btn.style != 'undefined'){
					var oBtn = $('<a href="javascript:void(0);"><span></span></a>');
					if(_btn.style) oBtn.addClass( _btn.style);
					if(_btn.target) oBtn.attr('target',_btn.target);
					if(_btn.text)  oBtn.children().html(_btn.text);
					if(_btn.url) oBtn.attr('href',_btn.url);
					if(_btn.handle && typeof(_btn.handle) == 'function' ){
						oBtn.click(function(){
							_btn.handle(dom,$(this),me.options);
							over = true;
							if($('#definElemenetDiv').length > 0) $('#definElemenetDiv').remove();
							if(!_btn.url) return false;
						});
					}else if(val == 'ok' || val == 'cancel'){
						oBtn.click(function(){
							dom.find('.ue-close a').click();
							if($('#definElemenetDiv').length > 0) $('#definElemenetDiv').remove();
							return false;
						});
					}
					$(dom).find('.ue-btn').append(oBtn);
				}
			})(btn[val]);
		}
		$(dom).find('.ue-btn').css('width',$(dom).width() - 40);	
	},
	
	
	//设置元素块居中样式
	setContent : function(ele,width,animateType){
		var that = this;
		if(this.options.shortcut){
			$(document).unbind('keydown').keydown(function(e){
				if(e.which == ' 27'){
					ele.find('.ue-close a').click();
				};			
			});
		}

		//计算相关值
		$(ele).css({'height':'auto'});
		var winObj = that.getElementInfo($(window));
		var eleObj = that.getElementInfo($(ele),true);
		var left = Math.floor((winObj.w - eleObj.w)/2);
		var top = Math.floor((winObj.h - eleObj.h)/2) + winObj.st;

		//是否使用动画
		if(animateType){
			var border = !parseInt($(ele).css('border-left-width')) ? 0 : parseInt($(ele).css('border-left-width'));
			var padding = !parseInt($(ele).css('padding-left')) ? 0 : parseInt($(ele).css('padding-left'));
			top = eleObj.h > winObj.h ? that.options.fixTop + winObj.st : top;
			ele.css({top:Math.floor(winObj.h/2 - eleObj.h/2) + winObj.st,left:Math.floor(winObj.w/2 - eleObj.w/2),'z-index':_zindex,height:eleObj.h - border*2 - padding*2 ,width:eleObj.w}).show();
		}else{
			top = eleObj.h > winObj.h ? that.options.fixTop + winObj.st : top;
			ele.css({top:top,left:left,'z-index':_zindex}).fadeIn(500);
		}

		//绑定拖拽事件
		if(this.options.drag){
			var defineX = 0,defineY = 0;
			$(ele).find('.ue-title p').css('cursor','move');
			$(ele).find('.ue-title p').mousedown(function(e){
				var _that = $(this);
				$('body').css({'cursor':'move'});
				var eleObj =  that.getElementInfo($(ele));
				var pObj = that.getElementInfo(_that,true);
				defineX = e.pageX - eleObj.l + 8;
				defineY = e.pageY - eleObj.t + 8;
				
				//不满足条件跳出
				if(defineX >= pObj.w || defineY >= pObj.h) return;
				
				//绑定移动事件
				$(document).mousemove(function(e){
					var win = that.getElementInfo($(window));
					var dialog = that.getElementInfo(ele);
					var x = e.pageX - defineX
					var y = e.pageY - defineY;

					//计算位置
					if(x <= 0) x = 0;
					if(x >= win.w - dialog.w) x = win.w - dialog.w;
					if(y <= win.st + that.options.fixTop) y = win.st;
					if(y >= (win.st + win.h) -  dialog.h) y = (win.st + win.h) - dialog.h;

					//绑定页面释放事件
					$(document).mouseup(function(){
						$(document).unbind('mousemove');
						$('body').css({'cursor':'auto'});
					});

					$(ele).css({"left" : x,"top" : y});
				});
				return false;
			}).mouseup(function(){
				$(document).unbind('mousemove');
				$('body').css({'cursor':'auto'});
			})
		}
	},
	
	//仿jQuery弹窗框结构
	card_html : function(){
		var html = '<div class="ue-dialog-box">';
			html +='	<div class="ue-layerBox">';
			html +='		<div class="ue-title"><p></p><div class="ue-close"><a dialog_count="0" href="javascript:void(0)"></a></div></div>';
			html +='		<div class="arrow arrow1"></div>';
			html +='		<div class="dialog_content"></div>';
			html +='		<div class="ue-btn"></div>';
			html +='	</div>';
			html +='</div>';
		return $(html); 
	},
	
	//获取对象高宽和屏幕坐标
	getElementInfo : function(element,type){
		var type = type ? type : false;
		var json = {};
		if(element === undefined) element = $(window); 
		if($(element).length == 0) return null;
		if(element.get(0) == window){
			json.h = $(element).height();
			json.w = $(element).width();
			json.st = $(element).scrollTop();
			json.sl = $(element).scrollLeft();
		}else{
			if(type) {
				json.h = $(element).outerHeight();
				json.w = $(element).outerWidth();
			}else{
				json.h = $(element).innerHeight();
				json.w = $(element).innerWidth();
			}
			
		}
		if(element.get(0) == window) return json;
		json.t = Math.floor($(element).offset().top);
		json.l = Math.floor($(element).offset().left);
		return json;
	}
};


//对象实例化
UE.namespace('UE.dialog');
UE.dialog.show = function(content, options) {
	if(_lastDialog) {
        _lastDialog = null;
    }
	_lastDialog = new fDialog(content, options);
	return _lastDialog;
};

//提示类弹出处理方法
UE.dialog.tips = function(content,options){
	var options = options || {};
	UE.dialog.show(content,options);
	_lastDialog.tips();
};

//后台弹出框操作处理方法
UE.dialog.box = function(options){
	var options = options || {};
	UE.dialog.show('',options);
	_lastDialog.box();
};
	
	
})(jQuery);

/**
 * [description] 播放页面相关事件初始化
 * @return {[type]} [description]
 */
(function(){

var lastObj = null;
var _defaults = {
	element: null,
	time : 100,
	width : 640,
	height : 480,
	setVideoBox : null
};

var play = function(options){
	this.opt = $.extend({}, _defaults, options);
	this.resizeObject = {};
	this.video = this.opt.element.find('.ue-player-video');
	this.footer = this.opt.element.find('.ue-player-foot');
	this.head = this.opt.element.find('.ue-player-top');
	this.rightBox = this.opt.element.find('.ue-player-r');
	this.leftBox = this.opt.element.find('.ue-player-l');
	this.videoMargin = parseInt(this.video.css('margin-top'));
	this.videoMarginLeft = parseInt(this.video.css('margin-left'));
	this.rightListBox = this.opt.element.find('.ue-play-other .ue-block-wrap');
	this.teacher = this.opt.element.find('.ue-play-teacher');
}

play.prototype = {
	
	/**
	 * [int description] 初始化页面加载时候处理方法
	 * @return {[type]} [description]
	 */
	int : function(){
		var that = this;

		//对象不存在时候不处理
		if($(this.opt.element).length == 0) return;

		//显示区域初始化页面高宽
		that.resizeObject['intVideoCode'] = function(){
			var bodys = $('body').height();
			var win = that.eleInfo($(window));
			var footer = that.eleInfo(that.footer);
			var head = that.eleInfo(that.head);
			var height = win.h - (head.h + footer.h + that.videoMargin*2);
			var width = win.w - (that.rightListBox.width() + that.videoMarginLeft*2);
			
			//高度自适应
			if(that.opt.height && height <= that.opt.height){
				height 	= that.opt.height;
				that.leftBox.css({height:height + footer.h + head.h + that.videoMargin*2});
				that.rightBox.css({height:height + footer.h + head.h + that.videoMargin*2});
			}else{
				that.rightBox.css({height:'100%'});
				that.leftBox.css({height:'100%'});
			}

			//宽度自适应
			if(that.opt.width && width <= that.opt.width){
				var bodys = that.opt.width + that.videoMarginLeft*2 + that.rightListBox.width();
				that.opt.element.css({width : bodys})
				that.video.css({width:that.opt.width});
			}else{
				that.video.css({width:'auto'});
				that.opt.element.css({width : '100%'});
			}


			//开启视频显示
			that.video.css({height:height}).show();
			var obj = that.eleInfo(that.video);
			UE.log(that.video);
			if(typeof that.opt.setVideoBox == "function"){
				that.opt.setVideoBox(obj.w,obj.h);	
			}

		}

		//右侧区域初始化页面高宽
		that.resizeObject['intRightCode'] = function(){
			var bodys = that.rightBox.height();
			var height = bodys - that.teacher.outerHeight(true) - 42;
			UE.log(1,bodys,that.teacher.outerHeight(true));
			that.rightListBox.css({height:height});
		}

		//初始化屏幕改变事件
		that.resizeObject['intVideoCode']();
		that.resizeObject['intRightCode']();
		that.reSize();
	},

	eleInfo : UE.util.getElementInfo,

	
	/**
	 * [reSize description] 初始化屏幕大小发生变化事件 
	 * @return {[type]} [description]
	 */
	reSize : function(){
		var that = this;
		var timeer = null;
		$(window).unbind('resize').bind('resize',function(e){
			if(timeer) clearTimeout(timeer);
			timeer = setTimeout(function(){
				for( var key in that.resizeObject){
					if(typeof that.resizeObject[key] == 'function') {
						that.resizeObject[key]();
					}
				}
			},that.opt.time);
		});
	}
}

//对象实例化
UE.namespace('UE.play');
var createObj = function(options){
	if(lastObj) lastObj = null;
	lastObj = new play(options == undefined ? {} : options);
	return lastObj;
}

//对外接口
UE.play.int = function(options){
	createObj(options).int();
}


})();