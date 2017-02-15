
/* 加入收藏 */
function addFavoritepage(){
	var sURL=window.location.href;
	var sTitle=document.title;
	try{
		window.external.addFavorite(sURL,sTitle);
	}catch(e){
		try{
			window.sidebar.addPanel(sTitle,sURL,"");
		}catch(e){
			alert("加入收藏失败，请使用Ctrl+D进行添加");
		}
	}
}

/* 字数限制 */
function textarealength(obj,maxlength){
	var v = $(obj).val();
	var l = v.length;
	if( l > maxlength){
		v = v.substring(0,maxlength);
	}
	$(obj).parent().find(".textarea-length").text(v.length);
}

//显示下级导航
$(function(){
	$(".top_nav li").hover(
		function(){
			$(this).addClass('hover');
			$(this).find('ul.sub_nav').slideDown('fast').show();
		},
		function(){
			$(this).removeClass('hover');
			// $(this).find('ul.sub_nav').fadeOut('fast');
			$(this).find('ul.sub_nav').slideUp('fast').hide();
		}
	);
});
/* $(function(){
	$(".top_nav li").hover(
		function(){
			$(this).addClass('hover');
			$(this).find('ul.sub_nav').show();
		},
		function(){
			$(this).removeClass('hover');
			$(this).find('ul.sub_nav').hide();
		}
	);
}); */
/* 导航菜单点击事件 end */

/* hover */
jQuery.mouseHover = function(obj) {
	$(obj).hover(
		function(){
			$(this).addClass("hover");
		},
		function(){
			$(this).removeClass("hover");
		}
	);
}

/* 招聘页面折叠显示 */
jQuery.divFold = function(obj,obj_c,speed,obj_type,Event){
	if(obj_type == 2){
		$(obj+":first").find("b").html("-");
		$(obj_c+":first").show();
	}
	$(obj).bind(Event,function(){
		if($(this).next().is(":visible")){
			if(obj_type == 2){
				return false;
			}
			else{
				$(this).next().slideUp(speed).end().removeClass("selected");
				$(this).find("b").html("+");
			}
		}
		else{
			if(obj_type == 3){
				$(this).next().slideDown(speed).end().addClass("selected");
				$(this).find("b").html("-");
			}else{
				$(obj_c).slideUp(speed);
				$(obj).removeClass("selected");
				$(obj).find("b").html("+");
				$(this).next().slideDown(speed).end().addClass("selected");
				$(this).find("b").html("-");
			}
		}
	});
}

/* 搜索关键词高亮 */
function SearchHighlight(idVal,keyword){
	var pucl = document.getElementById(idVal);
	if("" == keyword) return;
	var temp = pucl.innerHTML;
	var htmlReg = new RegExp("\<.*?\>","i");
	var arrA = new Array();
	//替换HTML标签
	for(var i=0;true;i++){
		var m = htmlReg.exec(temp);
		if(m){
			arrA[i] = m;
		}else{
			break;
		}
		temp = temp.replace(m,"{[("+i+")]}");
	}
	words = unescape(keyword.replace(/\+/g,' ')).split(/\s+/);
	//替换关键字
	for (w=0;w<words.length;w++){
		var r = new RegExp("("+words[w].replace(/[(){}.+*?^$|\\\[\]]/g, "\\$&")+")","ig");
		temp = temp.replace(r,"<b style='color:Red;'>$1</b>");
	}
	//恢复HTML标签
	for(var i=0;i<arrA.length;i++){
		temp = temp.replace("{[("+i+")]}",arrA[i]);
	}
	pucl.innerHTML = temp;
}

/**
* 分页页码
* 搜索关键词
* 分类ID
* 分类名称
* 每页数量
* 总数量
*/
function load_more_wxno(p_num,wxno_srch,cat_id,cat_name,sel_type,page_size,total_count){
	var wxno_srch = (wxno_srch=='') ? '`' : wxno_srch;
	$.ajax({
		type: 'POST',
		url: '/wxno',
		async: false,
		data: {
			"p":p_num,
			"wxno_srch":wxno_srch,
			"cat_id":cat_id,
			"cat_name":cat_name,
			"sel_type":sel_type,
			"page_size":page_size,
		},
		dataType: "text",
		success:function(js_msg){
			$('#wxno_container').append(js_msg);
			SearchHighlight('list_container',''+wxno_srch+'');
			if (total_count > $('.wxno_list_div').length){
				var new_html = '<a class="loading_more" id="loading_more_wxno" onclick="javascript:load_more_wxno(\''+(parseInt(p_num)+1)+'\',\''+wxno_srch+'\',\''+cat_id+'\',\''+cat_name+'\',\''+sel_type+'\',\''+page_size+'\',\''+total_count+'\')">加 载 更 多</a>';
				$('#wxno_load').html(new_html);
			}else{
				$('#wxno_load').html('<a class="loading_more no_more">没 有 更 多 了</a>');
			}
		},
		error:function(msg){
			alert('error');
		}
	});
}

/**
* 分页页码
* 公众号no
* 每页数量
* 总数量
*/
function load_more_wxart(p_num,wx_no,page_size,total_count){
	$.ajax({
		type: 'POST',
		url: '/wxartp',
		async: false,
		data: {
			"p":p_num,
			"wx_no":wx_no,
			"page_size":page_size,
		},
		dataType: "text",
		success:function(js_msg){
			$('#wxart_container').append(js_msg);
			if (total_count > $('.art_list_div').length){
				var new_html = '<a class="loading_more" id="loading_more_wxno" onclick="javascript:load_more_wxart(\''+(parseInt(p_num)+1)+'\',\''+wx_no+'\',\''+page_size+'\',\''+total_count+'\')">加 载 更 多</a>';
				$('#art_load').html(new_html);
			}else{
				$('#art_load').html('<a class="loading_more no_more">没 有 更 多 了</a>');
			}
		},
		error:function(msg){
			alert('error');
		}
	});
}



