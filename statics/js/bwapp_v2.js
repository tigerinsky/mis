//document.domain='lanjinger.com';

/**
 * 把对象转换为ajax数据请求
 * @param str object
 * @return str format_ajax
 */

function object_to_data(form_object){
    var post_data='';
    $.each(form_object, function(index, item) {
        post_data+=(index==0)?'':',';
        post_data+=item.name+':"'+item.value+'"';
    });
    return post_data;
}

/**
 * 跳转到指定的url
 * @param str 
 * @example: redirect('http://www.domain.com/');
 */
function redirect(httpurl){
    if(httpurl!=''){
        location.href=httpurl;
    };
}


/**
 * 获取通讯录列表
 * @return json
 * 
 */
function get_phone_data_by(param_str){
    var data_str='';
    // 此处新增正在加载效果
    // <section class="loding">检索结果为空</section>
    $.getJSON('http://app.lanjinger.com/1/phonebook/search?times='+new Date().getTime()+param_str+'&callback=?', function(result){
      if (result.errno == 0) {
        $.each(result.data.list,function(i,item){
            data_str+='<a href="/phone/interview/'+item.id+'"><dl class="phone_item">';
            data_str+='<dt><span class="user_face"><img src="http://123.56.89.16/statics/img/user_face.jpg"></span></dt>';
            data_str+='<dd><span class="uname">'+item.name+'</span> <span class="u_icon icon_approve"></span> <span class="utype">'+item.work+'</span>';
            data_str+='<p>'+item.company+'</p></dd></dl></a>';
        });
        if(result.data.total_number>0){
            $(".phone_home_footer .more_page").attr('param_str',param_str);
            $(".phone_home_footer .more_page").attr('next_page',result.data.next_page);
            $(".phone_home_footer .more_page").attr('prev_page',result.data.prev_page);
        }else{
            $(".phone_home_footer .more_page").hide();
        }
      }else{
        data_str='<section class="nodata">检索结果为空</section>';
      }
      $("#phone_item_box").html(data_str);
    });
 
}

/**
 * 采访反馈列表
 * @return json
 * 
 */
function get_feedbook_data_by(param_str){
    var data_str='';
    var phone_id=$("#feedbook_item_box").attr('data');
    // 此处新增正在加载效果
    // <section class="loding">检索结果为空</section>
    $.getJSON('http://app.lanjinger.com/1/feedback/get_by_phonebook_id?id='+phone_id+'&times='+new Date().getTime()+param_str+'&callback=?', function(result){
      if (result.errno == 0) {
        $.each(result.data.list,function(i,item){
            data_str+='<dl class="feedback_item">';
            data_str+='<dt><img src="http://123.56.89.16/statics/img/user_face.jpg"></dt><dd>';
            data_str+='<span class="uname">'+item.name+'</span> <span class="u_icon icon_approve"></span> <span class="uranks">'+item.work+'</span> <span class="utime">10秒前</span>';
            data_str+='<p>'+item.comment+'</p></dd></dl>';
        });
      }else{
        data_str='<section class="nodata">暂无反馈信息</section>';
      }
      $("#feedbook_item_box").html(data_str);
    });
}

/**
 * show_msg
 * @param str 提示消息
 * @param type 消息类型,如隐藏\手动关闭\跳转 array(1=>hidden,2=>close,3=>confirm);
 * @param delay 若选择隐藏，则设定延迟时间,以毫秒为单位
 * @param httpurl 前往的url地址
 * @return str format_ajax
 * @example: show_msg('注册成功',1,2000,'http://www.domain.com/');
 */

function show_msg(msg,type,delays,httpurl){
    msg=(msg=='')?'unknown error':msg;
    type=(type!=0)?type:0;
    delays=(delays>0)?delays:2000;
    if(type>0){
        var tips_box='';
        switch (type){
            case 3:
                tips_box='<section id="tips_confirm"><h1>'+msg+'</h1><span data_url="'+httpurl+'" class="confirm">OK</span></section>';
                break;
            case 2:
                tips_box='<section id="tips_close"><h1>'+msg+'</h1><i data_url="'+httpurl+'" class="close"></i></section>';
                break;
            case 1:
                tips_box='<section id="tips_hidden"><h1>'+msg+'</h1></section>';
                break;
            default:
                
        }
        $('body').append(tips_box);
        if(type==1){
            setTimeout("$(\"#tips_hidden\").hide().remove()",delays);
            if(httpurl!=''){
                setTimeout("redirect('"+httpurl+"');",1000);
            }
        }
    }
}


//验证码倒计时函数
function countdown_mcode(second){
    var mcode=$("#re_mcode");
    if(second>0){
        mcode.html(second+'秒后重新获取');
        second--;
        setTimeout("countdown_mcode("+second+")",1000);
    }else{
        mcode.html('重新获取验证码');
        mcode.removeAttr('disabled');
    }
}

$(function(){

    //加快click时间响应速度
    FastClick.attach(document.body);

    //提示框
    $('#tips_confirm .confirm,#tips_close .close').live('click',function(event) {
        var httpurl=$(this).attr('data_url');
        $(this).parent().hide().remove();
        if(httpurl!=''){
            setTimeout("redirect('"+httpurl+"');",1000);
        }
    });

    /** 通讯录首页 */
    
    //默认加载通讯录
    if($('#phone_item_box').attr('page-name')=='phone_list_def'){
       get_phone_data_by('&perct=2'); 
    }

    //加载评论
    if($('#feedbook_item_box').attr('page-name')=='comment_list_def'){
       get_feedbook_data_by(''); 
    }
    
    //搜索提示框
    $("#keyword").bind('focus',function() {
        var q=$("#keyword").val();
        if(q=='找人或找公司'){
            q=$("#keyword").val('');
        }
    });

    $("#keyword").bind('blur',function(event) {
        var q=$("#keyword").val();
        if(q==''){
            q=$("#keyword").val('找人或找公司');
        }
    });

    //通讯录搜索
    $('#keyword').keypress(function(event){
        var hotkey=event.keyCode;
        var param_str='';
        var q=$("#keyword").val();
        if(hotkey==13 && q!=''){
            param_str=(q=='')?'':'&q='+encodeURIComponent(q);
            get_phone_data_by(param_str);
        }
    });

    $(".expert_all").on('click',function(event){
        var param_str='&identity=2';
        get_phone_data_by(param_str);
    });

    //行业筛选
    $('#more_industry_box').on("click",function(event){
		var more_industy_btn=$(this).parent().find(".more_industry");
        if(more_industy_btn.hasClass('on')){
            more_industy_btn.removeClass('on');
            $('#industry_list').hide();
        }else{
            more_industy_btn.addClass('on');
            $('#industry_list').show();
        }
    });

    $('#industry_list li').on("click",function(event){
        $(this).siblings().removeClass('on');
        $(this).addClass('on');
        //当对应有绑定值的情况下进行ajax请求
        var industry_id=$(this).attr("data");
        var param_str='';
        param_str=(industry_id=='')?'':'&industry_id='+encodeURIComponent(industry_id);
        get_phone_data_by(param_str);
        $('#more_industry_box .more_industry').removeClass('on');
        $("#industry_list").hide();
    });

    //通讯录分页(当筛选和搜索操作后，都有搜索功能)
    $(".phone_home_footer .more_page").on("click",function(event){
        var page_id=$(this).attr("next_page");
        var param_str=$(this).attr("param_str");
        if(param_str!=''){
            if(param_str.indexOf('page=')==-1){
                param_str=param_str+'&page='+page_id;
            }else{
                param_str=param_str.replace(/page=(\d+)/ig,'page='+page_id);
            }
        }else{
            param_str='&page='+page_id;
        }
        get_phone_data_by(param_str);
    });
    
	//sug提示框功能
	
	
	//蓝鲸老用户登录
    $("#elogin_btn").click(function() {
        var form_data=$('#form_user_elogin').serialize();
        var goto_url=$('#form_user_elogin').attr('action');
        $.post('http://app.lanjinger.com/elogin?times='+new Date().getTime(),form_data,function(result,textStaus){
            if(result.errno==0){
                show_msg('登录成功',1,1000,goto_url);
            }else{
                show_msg('用户名或密码错误',2,0,'');
            }
        },"json");
    });
	
	//蓝鲸用户登录
    $("#user_login_btn").click(function() {
        var form_data=$('#form_user_login').serialize();
        var goto_url=$('#form_user_login').attr('action');
        $.post('http://app.lanjinger.com/plus/user/login?times='+new Date().getTime(),form_data,function(result,textStaus){
            if(result.errno==0){
                //goto_url='';
                //$('#form_user_login').attr('action',$result['access_token']);
                //goto_url='ljapp://set_info?userid='+result['id']+'&session='+result['access_token'];
                goto_url='ljapp://login_suc?userid='+result['id']+'&session='+result['access_token'];
                show_msg('登录成功',1,1000,goto_url);
            }else{
                show_msg('用户名或密码错误',2,0,'');
            }
        },"json");
    });
	
	//注册时验证用户账号，并给指定手机发送验证码
    $("#reg_mobile_btn").click(function() {
        var form_data=$('#form_reg_mobile').serialize();
        var goto_url=$('#form_reg_mobile').attr('action');
        $.get('http://app.lanjinger.com/plus/user/valid_username?times='+new Date().getTime(),form_data,function(result,textStaus){
            switch(result.errno){
                case 0://用户未注册
                    $.get('http://app.lanjinger.com/plus/sms/sendsms?times='+new Date().getTime(),form_data,function(result_msg,textStaus){
                        if(result_msg.errno==0){
                            $("#codeid").val(result_msg.identifier);
                            $("#form_reg_mobile").submit();
                        }else{
                            show_msg('验证码获取异常',2,0,'');
                        }
                    },"json");
                    break;
                case 20301://手机号格式错误
                    show_msg('手机号格式错误',2,0,'');
                    break;
                default://用户已注册
                    show_msg('手机号已注册',2,0,'');
            }
        },"json");
    });
	
	//找回密码验证账号是否正确
    $("#forget_mobile_btn").click(function() {
        var form_data=$('#form_user_forget').serialize();
        var goto_url=$('#form_user_forget').attr('action');
        $.get('http://app.lanjinger.com/plus/user/valid_username?times='+new Date().getTime(),form_data,function(result,textStaus){
            switch(result.errno){
                case 20103://用户存在
                    $.get('http://app.lanjinger.com/plus/sms/sendsms?times='+new Date().getTime(),form_data,function(result_msg,textStaus){
                        if(result_msg.errno==0){
                            $("#codeid").val(result_msg.identifier);
                            $("#form_user_forget").submit();
                        }else{
                            show_msg('验证码获取异常',2,0,'');
                        }
                    },"json");
                    break;
                case 20301://手机号格式错误
                    show_msg('手机号格式错误',2,0,'');
                    break;
                case 0:
                    show_msg('该账号不存在',2,0,'');
                    break;
                default:
                    show_msg('操作异常',2,0,'');
            }
        },"json");
    });
	
	//找回密码设置密码
    $("#forget_setpwd_btn").click(function() {
        var form_data=$('#form_forget_setpwd').serialize();
        var goto_url=$('#form_forget_setpwd').attr('action');
        $.post('http://app.lanjinger.com/plus/user/forgetpwd?times='+new Date().getTime(),form_data,function(result,textStaus){
            if(result.errno==0){
                show_msg('密码找回成功',2,0,'');
                $("#form_forget_setpwd").submit();
            }else{
                show_msg('操作异常',2,0,'');
            }
        },"json");
    });
	
    //重新获取短信验证
    $("#re_mcode").click(function() {
        if($(this).attr("disabled")!=1){
            var form_data=$('#form_verify_sms').serialize();
            var goto_url=$('#form_verify_sms').attr('action');
            $.get('http://app.lanjinger.com/plus/sms/sendsms?times='+new Date().getTime(),form_data,function(result,textStaus){
                if(result.errno==0){
                    $("#codeid").val(result.identifier);
                }else{
                    show_msg('验证码获取异常',2,0,'');
                }
            },"json");
        }
    });

    //判断短信验证结果
    $("#verify_sms_btn").click(function() {
        var form_data=$('#form_verify_sms').serialize();
        var goto_url=$('#form_verify_sms').attr('action');
        $.get('http://app.lanjinger.com/plus/sms/verify_sms?times='+new Date().getTime(),form_data,function(result,textStaus){
            if(result.errno==0){
                $("#form_verify_sms").submit();
            }else{
                show_msg('验证码错误或过期',2,0,'');
            }
        },"json");
    });
	
	//注册认领用户
    $("#user_regpwd_btn").click(function() {
        var form_data=$('#form_user_regpwd').serialize();
        var goto_url=$('#form_user_regpwd').attr('action');
        $.post('http://app.lanjinger.com/plus/user/build?times='+new Date().getTime(),form_data,function(result,textStaus){
            switch(result.errno){
                case 0:
                    show_msg('注册成功',2,0,'');
                    $("#form_user_regpwd").submit();
                    break;
                case 10011://参数错误
                    show_msg('参数错误',2,0,'');
                    break;
                case 20302:
                    show_msg('验证码异常',2,0,'');
                    break;
                case 20303:
                    show_msg('验证码异常',2,0,'');
                    break;
                case 20304:
                    show_msg('验证码异常',2,0,'');
                    break;
                case 20107://已经被认领
                    show_msg('已经被认领',2,0,'');
                    break;
                case 20103:
                    show_msg('用户已存在',2,0,'');
                    break;
                default://操作异常
                    show_msg('操作异常',2,0,'');
            }
        },"json");
    });
	
	//补充用户附属资料
    $("#info_reinforce_btn").click(function() {
        var form_data=$('#form_info_reinforce').serialize();
        var goto_url=$('#form_info_reinforce').attr('action');
        $.post('http://app.lanjinger.com/plus/user/userinfo_push?times='+new Date().getTime(),form_data,function(result,textStaus){
            if(result.errno==0){
                show_msg('用户信息修改成功',2,0,'');
                $("#form_info_reinforce").submit();
            }else{
                show_msg('操作异常,请重新提交',2,0,'');
            }
        },"json");
    });
	
	//用户关注行业设定
    $("#user_industry_item li").click(function(event) {
		
		var btn_follow=$(this);
        var have_follow=$(this).attr('follow');
        var industry_id=$(this).attr('data-id');
        var api_url='';
        if(have_follow==0){
            api_url='http://app.lanjinger.com/1/industry/add_follow';
        }else{
            api_url='http://app.lanjinger.com/1/industry/cancel_follow';
        }
		
		//执行取消或关注用户操作
        $.post(api_url, {ids:industry_id}, function(result){
            if(result.errno == 0) {
                if(have_follow==0){
                   btn_follow.attr('follow',1);
                   btn_follow.addClass('on');
                }else{
                   btn_follow.attr('follow',0);
                   btn_follow.removeClass('on');
                }
            }else{
                show_msg('操作异常，请重新尝试',2,0,'');
            }
        }, 'json');
       	
    });
	
	//用户关注推荐关注会员
    $("#user_follow_box .follow_item").click(function(event) {
        var btn_follow=$(this);
        var have_follow=$(this).attr('follow');
        var user_id=$(this).attr('data-id');
        var api_url='';
        if(have_follow==0){
            api_url='http://app.lanjinger.com/agent/relation/follow';
        }else{
            api_url='http://app.lanjinger.com/agent/relation/unfollow';
        }
        //执行取消或关注用户操作
        $.get(api_url, {follower_uid:user_id}, function(result){
            if(result.errno == 0) {
                if(have_follow==0){
                   btn_follow.attr('follow',1);
                   btn_follow.find(".follow_btn").addClass('on');
                }else{
                   btn_follow.attr('follow',0);
                   btn_follow.find(".follow_btn").removeClass('on');
                }
            }else{
				show_msg('操作异常，请重新尝试',2,0,'');
            }
        }, 'json');
       
    });

    //关注推荐所有用户
    $("#user_follow_all").click(function(event) {
        var user_ids='';
        var user_list=$("#user_follow_box dl");
        $.each(user_list, function(index,val) {
            data_id=$(val).attr('data');
            user_ids+=(index==0)?data_id:','+data_id;
        });
        $.post("http://app.lanjinger.com/1/follow/add_follow", {ids:user_ids}, function(data){
            if(data.errno == 0) {
                $("#user_follow_box dl b").remove();
                $.each(user_list, function(index,val) {
                    $(val).find('dd').append('<b>+</b>');
                });
            }
        }, 'json')
    });
	
});
