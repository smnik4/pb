/*mini ajax*/
function execute_ajax(obj){
    $.each(obj,function(key,value){
        execute_ajax_record(value);
    });
}

function execute_ajax_record(obj){
    if(obj.action){
        switch(obj.action){
            case "alert":
                alert(obj.value);
                break;
            case "message":
                if(obj.element){
                    show_message(obj.element,obj.value);
                }
                break;
            case "replace":
                if(obj.element){
                    $(obj.element).html(obj.value);
                }
                break;
            case "append":
                if(obj.element){
                    $(obj.element).append(obj.value);
                }
                break;
            case "remove":
                if(obj.element){
                    $(obj.element).remove();
                }
                break;
            case "value":
                if(obj.element){
                    $(obj.element).val(obj.value);
                }
                break;
            case "attribute":
                if(obj.element && obj.value.length == 2){
                    $(obj.element).attr(obj.value[0],obj.value[1]);
                }
                break;
            case 'removeAttr':
                if(obj.element){
                    $(obj.element).removeAttr(obj.value);
                }
                break;
            case "prop":
                if(obj.element){
                    $(obj.element).prop(obj.value[0],obj.value[1]);
                }
                break;
            case "redirect":
                document.location.href = obj.value;
                break;
            case 'reload':
                location.reload();
                break;
            case "window":
                if(obj.value){
                    open_win(obj.value);
                }
                break;
            case "swindow":
                if(obj.element){
                    show_swindow(obj.element,obj.value);
                }
                break;
            case 'close_awindow':
                $("#parent_window").hide();
                $("#parent_window").html("");
                $("body").css("overflow","auto");
                break;
            case "awindow":
                $(obj.element).show();
                $(obj.element).html('<div class="window">'
                        +'<img class="close_window img_window" title="Закрыть" src="/assets/img/close.png">'
                        +'<div class="window_title">'+obj.window+'</div>'
                        +'<div class="window_content">'+obj.value+'</div>');
                var height = parseInt(window.innerHeight);
                var w = $(obj.element).find(".window");
                var wh = parseInt(w.css("height"));
                var wt = parseInt(w.css("top"));
                $("body").css("overflow","hidden");
                if(wh > height){
                    w.css("height","calc(100% - 18px)");
                }
                wh = parseInt(w.css("height"));
                var wn = wh / 2 * -1;
                if(wt > 0){
                    w.css("margin-top",wn);
                }
                break;
            case "debug":
                if(obj.value){
                    $("#debug").html("<pre>"+obj.value+"</pre>");
                }
                break;
            case "set_var":
                switch(obj.element){
                    case "last_update":
                        last_update = parseInt(obj.value);
                        break;
                    default:
                        show_message("#message",'<div class="error">UNKNOWN VAR: '+obj.element+'</div>');
                }
                break;
            case 'class':
                $(obj.element).addClass(obj.value);
                break;
            default:
                show_message("#message",'<div class="error">UNKNOWN COMMAND: '+obj.action+'</div>');
        }
    }
}

function open_win(script) {  
    var height = screen.height/3*2;
    var width = screen.width/3*2;
    var top = screen.height/6;
    var left = screen.width/6;
    var popupWin = window.open(script,'Tickets',"top="+top+",left="+left+",width="+width+",height="+height+",scrollbars=yes"); 
    if(popupWin !== null){
        popupWin.focus();
    }else{
        show_message("#message",'<div class="error">Резрешите в браузере всплывающие окна</div>');
    }
}

function show_message(obj,text){
    var id = $(text).attr("id");
    var pub = true;
    if(id != "undefined"){
        if(parseInt($(obj).children("#"+id).length) > 0){
            pub = false;
        }
    }
    if(pub){
        $(obj).append(text);
        var val = $(obj).children("div").last("div");
        var time = parseInt(text.length / 15 * 1000);
        if(time < 5000){
            time = 5000;
        }
        if(!val.is(".noclose")){
            window.setTimeout(function() {val.remove();}, time);
        }
    }
}

function show_swindow(obj,url){
    if($(obj).children("iframe").is("iframe")){
        cinsole.log($(obj).children("iframe").attr(src));
    }
    text = '<div><div class="close"></div><iframe src="'+url+'" width="100%" height="100%" align="left"></iframe></div>';
    $(obj).css("height","100%");
    $(obj).html(text);
}

function save_form(otherform,reedit){
    if(otherform){
        var form1 = document.getElementById(otherform);
    }else{
        var form1 = document.getElementById("edit_form");
    }
    var data = new FormData(form1),
        xhr = new XMLHttpRequest();
    var err_attach = get_files_size();;
    if(ajax_path && err_attach == false){
        load_start();
        if(reedit){
            data.set("reedit",1);
        }else{
            data.set("reedit",0);
        }
        $(".error_field").attr("class","check_field");
        xhr.open('POST', ajax_path);
        xhr.responseType = 'json';
        xhr.onload = function (e) {
            load_end();
            //var res = e.currentTarget.responseText;
            if(parseInt(e.currentTarget.status) == 200){
                //res = $.parseJSON(res);
                var res = xhr.response;
                if(res !== null){
                    execute_ajax(res);
                }else{
                    show_message("#message",'<div class="error">На странице произошла ошибка. Запрос не обработан.</div>');
                }
            }else{
                show_message("#message",'<div class="error">На странице произошла ошибка. Запрос не обработан.</div>');
				console.log(ajax_path);
                console.log(data);
                console.log(xhr);
            }
        }
        xhr.onerror = function (e) {
            load_end();
            show_message("#message",'<div class="error">На странице произошла ошибка. Запрос не обработан.</div>');
			console.log(ajax_path);
            console.log(data);
            console.log(xhr);
        }
        xhr.send(data);
        return false;
    }else{
        if(err_attach == false){
            alert("AJAX not configure path");
        }else{
            show_message("#message",'<div class="error">Размер прикрепленных файлов больше допустимого.</div>');
        }
    }
}

function get_files_size(){
    var max_size = parseInt($("input[name=MAX_FILE_SIZE]").val()),
        sum_size = 0;
    $("input[type=file]").each(function(i,e){
        if(this.files.length > 0){
            $.each(this.files,function(fi,fe){
                sum_size = sum_size + fe.size;
            });
        }
    });
    if(sum_size >= max_size){
        return true;
    }else{
        return false;
    }
}


function switch_block(hide_selector,show_selector){
    $(hide_selector).hide();
    $(show_selector).show();
}

function hide_block(hide_selector){
    $(hide_selector).hide();
}

function confirm_set(obj,param,subparam,execute,el_id,check,text){
    if(!el_id){
        el_id = 0;
    }
    if(!text){
        text = '';
    }
    if(text.length == 0){
        text = $(obj).attr("title");
        var text_length = parseInt(text.length);
    }
    if(text_length == 0){
        text = 'Выполнить?';
    }else{
        text = text + "?";
    }
    var tvalue = false;
    var value = $(obj).val();
    if($(obj).attr("tvalue") && check !== false){
        tvalue = $(obj).attr("tvalue");
        if(value == tvalue){
            show_message("#message",'<div class="error">Ошибка: Ничего не поменялось</div>');
            return false;
        }
    }
    if(confirm(text)){
        set_var(obj,param,subparam,execute,el_id);
    }
}

function set_var(obj,param,subparam,execute,el_id){
    var value = null;
    if(!el_id){
        el_id = 0;
    }
    if($(obj).is("div.button")){
        var many_val = false;
        if(param == "ticket" && subparam == "complete"){
            many_val = true;
            if($(obj).is("div.button.nonactive")){
                value = 1;
            }
            if($(obj).is("div.button.active")){
                value = 2;
            }
            if($(obj).is("div.button.selected")){
                value = 0;
            }
        }
        if(param == "hosts" && subparam == "internet"){
            many_val = true;
            if($(obj).is("div.button.ignore_val")){
                value = 1;
            }
            if($(obj).is("div.button.active")){
                value = 2;
            }
            if($(obj).is("div.button.only_omgpu")){
                value = 0;
            }
            if($(obj).is("div.button.only_lan")){
                value = 3;
            }
        }
        if(!many_val){
            if($(obj).is("div.button.active")){
                value = 0;
            }else{
                value = 1;
            }
        }
    }else{
        if($(obj).attr("value_set")){
            value = $(obj).attr("value_set");
        }else{
            if($(obj).is('input[type=checkbox]')){
                value = $(obj).is('input:checked');
            }else{
                value = $(obj).val();
            }
        }
        
    }
    if(ajax_path){
        load_start();
        $.post(ajax_path,{"action":"set_value","param":param,"subparam":subparam,"execute":execute,"value":value,'id':el_id},function(data){
            load_end();
            execute_ajax(data);
        },"json");
    }else{
        alert("AJAX not configure path");
    }
}

function set_var_enter(event,obj,param,subparam,action){
    if(parseInt(event.keyCode) == 13){
        set_var(obj,param,subparam,action)
    }
}

function confirm_exec(obj,command,arguments){
    if(arguments !== arguments){
        arguments = false;
    }
    var text = $(obj).attr("title");
    var text_length = parseInt(text.length);
    if(text_length == 0){
        text = 'Выполнить?';
    }else{
        text = text + "?";
    }
    if(confirm(text)){
        exec_remote(command,false,arguments);
    }
}

function exec_remote(command,update_time,arguments){
    if(!update_time){
        update_time = false;
    }
    if(!arguments){
        arguments = false;
    }
    if(ajax_path){
        var lu = 0
        if(update_time && window.last_update){
            lu = last_update;
        }
        $.post(ajax_path,{"action":"execute","execute":command,"last_update":lu,"arguments":arguments},function(data){
            execute_ajax(data);
        },"json")
        .fail(function() {
			//alert( "error" );
            console.log(this);
			console.log(ajax_path);
            console.log(command);
            console.log(arguments);
        });
    }else{
        alert("AJAX not configure path");
    }
}

function load_start(){
    $("body").append('<div class="load_data_active"></div>');
}
function load_end(){
    $("body .load_data_active").remove();
}

$(document).ready(function(){
    //кнопка вверх
    $('.up').click(function(){
        if($('.up').attr("no_top") != "true"){
            $('html, body').animate({scrollTop: 0},500);
            return false;
        }
    });
    /*if($('.up').is("a")){
        $('.up').draggable({
            stop:function(){
                $(this).attr("no_top","true");
                setTimeout(function(){
                    $('.up').removeAttr("no_top");
                },2000);
            }
        });
    }*/
    var messages = $("#message").children("div");
    $.each(messages,function(){
        if(!$(this).is(".noclose")){
            var width = window.outerWidth;
            if(width <= 700){
                return false;
            }
            var time = parseInt($(this).html().length / 15 * 1000),
                obj = $(this);
            if(time < 5000){
                time = 5000;
            }
            window.setTimeout(function() {
                //obj.css({"visibility":"hidden","opacity":1});
                obj.remove();
            }, time);
        }
        
    });
	
    $(".select_table_line").click(function(){
        $(".select_table_line.select").attr("class","select_table_line");
        $(this).attr("class","select_table_line select");
    });
    
    $("#initial_window").on("click",".close",function(data){
        //close initial_window content
        $("#initial_window").children("div").remove();
        $("#initial_window").css("height","auto");
    });
    $("#message").on("click","div",function(data){
        //close message
        if(!$(this).is(".noclose")){
            $(this).remove();
        }
    });
    
    $("form").on("click",".autocomplete li", function(event){
        //устанавливаем выбранное значение в autocomplete
        var text = '';
        if($(this).attr("value")){
            text = $(this).attr("value");
        }else{
            text = $(this).html();
        }
        $(this).parents("td").children("input[type=text]").val(text);
        //проверяем тип поля и отправляем сведения
        if($(this).parents("td").is(".ticket_edit_inventory")){
            set_var($(this).parents("td").children("input[type=text]"),'ticket_edit_var','inventory',false);
        }
        //console.log($(this).parents("td").children("input[type=text]").val());
        $(this).parents("ul").remove();
    });
    $("form").on("mouseenter",".autocomplete li",function(e){
        var ob = $(this).parents(".autocomplete");
        //console.log(ob);
        if(ob.is(".over") == false){
            $(this).parents(".autocomplete").attr("class","autocomplete over");
        }
    });
    $("form").on("mouseout",".autocomplete li",function(e){
        var ob = $(this).parents(".autocomplete");
        if(ob.is(".over")){
            $(this).parents(".autocomplete").attr("class","autocomplete");
        }
    });
    $("form").on("focusout","input[type=text]",function(e){
        //очищаем autocomplete после потери фокуса
        if($(this).parent("td").children(".autocomplete").is(".over") == false){
            $(this).parent("td").children(".autocomplete").html("");
        }
    });
    $("#parent_window").on("click",".close_window",function(){
        $("#parent_window").hide();
        $("#parent_window").html("");
        $("body").css("overflow","auto");
    });
	
	/*$(".close_window").click(function(){
		$(".parent_window").attr("hidden","hidden");
    });
	var area = $("select[name='area']").val();
	if(area){
		if(area == 3 || area == 112){
			$(".remont_block").show();
		}
		if(area == 1){
			$(".input_tick_block").show();
		}
	}
	$("#lic_product").on("change", function(event){
		get_lic_version();
	});
	$(".slides").on("click", function(event){
		var cc = $(this).parent(".info_block").children("content");
		if(cc.css("display") == "none"){
			cc.show();
		}else{
			cc.hide();
		}
		return false;
	});*/
});

window.onscroll = function() {
    var top = document.body.scrollTop;
    if(top > 200){
        $(".up").fadeIn();
    }else{
        $(".up").fadeOut();
    }
}