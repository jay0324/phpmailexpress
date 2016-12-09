/*
code: jquery form validator
coder: JH
date: 2016/05/13

usage:
$(yourForm).JFormValidator();

param:
$(yourForm).JFormValidator({
	checkType: 'input, textarea, select', //check field type
	msg: {
		required: '* This field is require ',
		text: '* Text only ',
		number: '* Number only ',
		email: '* Invalid email address ',
		choice_require: '* You must select one ',
		captcha: '* Invalid validate code '
	}
});

for input, textarea, select field validation setting:
<input type="text" data-validate="validation method" />

for checkbox, radio validation settion:
<input name="any_name" value="your checkbox or radio group name" type="hidden" data-validate="choice_require">

validation method:
require,text,number,email 

*/
(function($) {
    $.fn.JFormValidator = function(options) {
        var defaults = {
        	checkType: 'input, textarea, select, radio, checkbox',
        	msg: {}
        };
        options = $.extend(defaults, options);
        var checkType = options.checkType;
        var msg = options.msg;

        $(this).each(function(){
        	var myform = $(this);

        	$(checkType,myform).each(function(){
				//在所有檢查欄位後建立訊息區塊
				if ($(this).type!='checkbox' && $(this).type!='radio') {
					$(this).wrap('<span class="validate-wrap">');
					$(this).after('<span class="validate-msg">');
				}
			})
			
			$(checkType).each(function(){
				//建立檢察欄位的script
				$(this).on('change',function(){
					fnCheckField($(this),msg,myform);
				})
			})

			$("input[type='submit'], submit",myform).on('click',function(){
				var validateResult = [];
				$(checkType,myform).each(function(){
					validateResult.push(fnCheckField($(this),msg,myform));
				});

				if ($.inArray(false,validateResult) == -1) {
					return true;
				}else{
					return false;
				}

			})

			$(".validate-msg").on('click',function(){
				$(this).fadeOut('200');
			})

        })

        //reflash captcha image
        $(".captcha").on('click',function(){
			$(this).attr('src',$(this).attr('src')+'?');
		})

        //check field
		function fnCheckField(obj,msg,myform){
			var val = $(obj).val();
			var type = $(obj).attr('type');
			var name = $(obj).attr('name');
			var validateStr = (type=='checkbox' || type=='radio') ? $("input[value='"+name+"']",myform).attr('data-validate') : $(obj).attr('data-validate');
			var validate = (validateStr != undefined) ? (validateStr.indexOf(',') != -1) ? validateStr.split(',') : [validateStr] : '';
			var is_validate = [];
			var returnMsg = "";

					for (var i= 0; i < validate.length; i++) {
						switch(validate[i]){
							case 'required':
								if (val == ''){
									is_validate.push(false);
									returnMsg += createNotifyMsg(validate[i],msg);
									returnMsg += '<br>'; //break to new line
								}
							break;
							case 'text':
								if (val.match(/[\d]/gi) != null){
									is_validate.push(false);
									returnMsg += createNotifyMsg(validate[i],msg);
									returnMsg += '<br>'; //break to new line
								}
							break;
							case 'number':
								if (val.match(/[^\d]/gi) != null){
									is_validate.push(false);
									returnMsg += createNotifyMsg(validate[i],msg);
									returnMsg += '<br>'; //break to new line
								}
							break;
							case 'email':
								if (val.match(/\S+@\S+\.\S+/gi) == null){
									is_validate.push(false);
									returnMsg += createNotifyMsg(validate[i],msg);
									returnMsg += '<br>'; //break to new line
								}
							break;
							case 'choice_require':
								if (type=='checkbox' || type=='radio') {
									if ($("input[name='"+name+"']:checked",myform).length == 0){
										is_validate.push(false);
										returnMsg += createNotifyMsg(validate[i],msg);
										returnMsg += '<br>'; //break to new line
									}
								}else{
									if ($("input[name='"+val+"']:checked",myform).length == 0){
										is_validate.push(false);
										returnMsg += createNotifyMsg(validate[i],msg);
										returnMsg += '<br>'; //break to new line
									}
								}
							break;
							case 'captcha':
								if (val == ''){
									is_validate.push(false);
									returnMsg += createNotifyMsg(validate[i],msg);
									returnMsg += '<br>'; //break to new line
								}else{
									$.ajax({
										url: $(".captcha", myform).attr('src'),
										method: 'post',
										data: {v: 'fcaptcha',g: val},
										success: function (r) {
											console.log(r);
										    if (r != '1') {
												is_validate.push(false);
												returnMsg += createNotifyMsg(validate[i],msg);
												returnMsg += '<br>'; //break to new line
											}
										 }, 
										 async: false
									})
								}
							break;
						}
					}


					if ($.inArray(false,is_validate) == 0) {
						if (type=='checkbox' || type=='radio') {
							$("input[value='"+name+"']",myform).next('.validate-msg').addClass('validate-error').html('<span>'+returnMsg+'</span>').fadeIn(200);
						}else{
							$(obj).removeClass('validate-ok').addClass('validate-error');
							$(obj).next('.validate-msg').addClass('validate-error').html('<span>'+returnMsg+'</span>').fadeIn(200);
						}

						return false;
					}else{
						if (type=='checkbox' || type=='radio') {
							$("input[value='"+name+"']",myform).next('.validate-msg').removeClass('validate-error').text('').fadeIn(200);
						}else{
							$(obj).removeClass('validate-error').addClass('validate-ok');
							$(obj).next('.validate-msg').removeClass('validate-error').text('').fadeOut(200);
						}

						return true;
					}

		}

		function createNotifyMsg(type,msg) {
			//default msg
			var default_msg = {
	        		required: '* This field is require ',
					text: '* Text only ',
					number: '* Number only ',
					email: '* Invalid email address ',
					choice_require: '* You must select one ',
					captcha: '* Invalid validate code '
	        	};

	        var returnMsg = '';

	        //check custom msg first
			for (var k in msg) {
				if (k == type) {
					returnMsg = msg[k];
				}
			}

			//if no return msg then check the default msg
			if (returnMsg == '') {
				for (var k in default_msg) {
					if (k == type) {
						returnMsg = default_msg[k];
					}
				}
			}

			return returnMsg;
		}
        

    }
})(jQuery);