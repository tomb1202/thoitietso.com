$(document).ready(function () {

	var data_id = $('#video').attr('data-id');
	var server_id = $('#video').attr('data-sv');
	if(data_id){
		// server(server_id,data_id);
	}
	$("button.download").click(function(){
		var id = $('button.download').attr('data-id');
		if(id){
			$('#download').html('<p>Loading...</p>');
		}
	});
	
	$(".download-button").click(function(){
		var id = $('.download-button a').attr('data-id');
		if(id){
			$('#download').html('<p>Loading...</p>');
		}
	});
	
	//search
	function xd(str) {
	str = str.replace(/à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ/g, "a");
	str = str.replace(/è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ/g, "e");
	str = str.replace(/ì|í|ị|ỉ|ĩ/g, "i");
	str = str.replace(/ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ/g, "o");
	str = str.replace(/ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ/g, "u");
	str = str.replace(/ỳ|ý|ỵ|ỷ|ỹ/g, "y");
	str = str.replace(/đ/g, "d");
	str = str.replace(/À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ/g, "A");
	str = str.replace(/È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ/g, "E");
	str = str.replace(/Ì|Í|Ị|Ỉ|Ĩ/g, "I");
	str = str.replace(/Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ/g, "O");
	str = str.replace(/Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ/g, "U");
	str = str.replace(/Ỳ|Ý|Ỵ|Ỷ|Ỹ/g, "Y");
	str = str.replace(/Đ/g, "D");
	return str;
	}
	$('.search').submit(function(){
		var kw = xd($('.searchTxt').val());
		kw = $.trim(kw);
		kw = kw.replace(/\ /gi,'-');
		if(kw) {
			window.location.href = '/search/' + kw + '/';
		}
		return false;
	});
	//search
    
    var f = document.location.pathname.match(/^\/search\/([^\/]+-[^\/]+)\//)
    if(f){
        f = f[1]
        var f2 = localStorage['f:'+f]
        if(f2 && f2<Math.round(new Date().getTime()/1000)){            
        }else{
            f2 = 0
        }
        if(!f2){
            
        }
    }

	var _x = document.location.protocol + '//' + document.location.hostname
	$('#primary-nav li a').each(function() {
		var href = this.href.replace(_x, "")
		var href2 = document.location.href.replace(_x, "")
		if ((href != '/' && href2.indexOf(href) === 0) || (href == '/' && (href2.indexOf('/new/') === 0 || href2 == href))) {
			this.style.color = '#dadada';
			this.style.background = 'rgba(234, 67, 53, 0.6)'
		}
	})
}); 

//$(function(){
//	$('#primary-nav li a').each(function(){if((this.href!='/' && document.location.href.indexOf(this.href)===0) || (this.href=='/' && (document.location.href.indexOf('/new/')===0  || document.location.href==this.href) )
//	){this.style.color='#ea4335';this.style.background='#2b2b2b'}})
//})

var reloadedCount = {};
function reloadCurrentserver(){
	var data_id = $('#video').attr('data-id');
	var server_id = $('#video').attr('data-sv');
	if(data_id){
		server(server_id,data_id);
	}
}

function del_cache() {
	var data_id = $('#video').attr('data-id');
	var server_id = $('#video').attr('data-sv');
}

var cookie_notice = !1,
	error_thispage = false;
