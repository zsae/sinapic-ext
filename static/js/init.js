(function(D){
	//sinapic-ext class
	function spe(){
		var that = this;
		this.config = {
			file_id : '#spe-file',
			upload_btn_id : '#spe-upload-btn',
			fm_id : '#spe-fm',
			files_container_id : '#files-container',
			progress_bar_id : '#progress-bar',
			lang : {
				M00001 : 'Loading, please wait...',
				M00002 : 'Select or Drag image into here',
				M00003 : 'Success, your image has been uploaded.',
				M00004 : '<a href="http://ww3.sinaimg.cn/large/686ee05djw1eihtkzlg6mj216y16ydll.jpg" target="_blank" title="Donate by Alipay"><strong>{0} files</strong> have been uploaded!</a>',
				M00005 : '<strong>Uploading {0}/{1}</strong> , please wait...',
				E00001 : 'Sorry, server error, please try again later.'
			},
			cookie_last_size : 'spe-size',
			process_url : 'action.php',
			token : '',
			/**
			 * sizes
			 */
			sizes : {
				thumb150 	: 'max 150x150, crop',
				mw600 		: 'max-width:600',
				large 		: 'original size',
				square 		: 'max-width:80 or max-height:80',
				thumbnail 	: 'max-width:120 or max-height:120',
				bmiddle 	: 'max-width:440'
				
			},
			
		}
		/** 
		 * set intervaltime for each file upload
		 */
		this.interval = 3000;
		var is_authorized = false;
		var cache = {
			file_i : 0,
			$bg : false,
			$upload_btn : false,
			$file : false,
			files : false
		}
		this.check_authorize = function(){
			upload_tip('loading',that.config.lang.M00001);
			$.ajax({
				url : that.config.process_url,
				type : 'post',
				data : {
					action : 'check-auth'
				},
				dataType : 'json'
			}).done(function(data){
				if(data && data.status === 'success'){
					upload_tip('success',data.msg);
					is_authorized = true;
					cache.$fm = $(that.config.fm_id).show();
					that.init();
				}else{
					upload_tip('danger',data.msg);
				}
			}).fail(function(){
				upload_tip('danger',that.config.lang.E00001);
			})
		}
		this.init = function(){
			file.init();
		}
		var file = {
			init : function(){
				var _this = this;

				cache.$file = $(that.config.file_id);
				cache.$upload_btn = $(that.config.upload_btn_id);
				cache.$files_container = $(that.config.files_container_id);
				cache.$progress_bar = $(that.config.progress_bar_id);
				
				//cache.$file[0].addEventListener('drop',function(e){
				//	_this.select(e);
				//}, false);
				cache.$file.on({
					change 		: this.change_handle,
					dragenter 	: this.drop_enter,
					dragover 	: this.drop_over,
					dragleave 	: this.drop_leave,
					drop 		: this.drop_handle
				});
			},
			change_handle : function(e){
				file.select_handle(e);
			},
			drop_handle : function(e){
				file.select_handle(e);
				e.stopPropagation();
				e.preventDefault(); 
			},
			select_handle : function(e){
				cache.files = e.target.files.length ? e.target.files : e.originalEvent.dataTransfer.files;

				cache.file_count = cache.files.length;
				cache.file = cache.files[0];
				//start upload
				file.upload(cache.files[0]);
				// cache.$fm.hide();
			},
			drop_enter : function(e){
				
			},
			drop_over : function(e){
				
			},
			drop_leave : function(e){
				
			},
			upload : function(f){
				var _this = this,
					reader = new FileReader();
				cache.start_time = new Date();


				upload_tip('loading',format(that.config.lang.M00005,cache.file_i + 1,cache.file_count));

				reader.onload = function (evt) {
					_this.submission.init(evt.target.result.split(',')[1]);
				}
				reader.readAsDataURL(f);		
			},
			submission : {
				init : function(base64){
					var _this = this;
					
					var fd = new FormData(),
					xhr = new XMLHttpRequest();
					fd.append('file',cache.files[cache.file_i]);
					xhr.open('post',that.config.process_url + '?action=upload');
					xhr.onload = _this.done;
					
					xhr.onreadystatechange = function(){
						if (xhr && xhr.readyState === 4) {
							status = xhr.status;
							if (status >= 200 && status < 300 || status === 304) {
							
							}else{
								_this.fail();
							}
						}
						cache.is_uploading = false;
						xhr = null;
					}

					xhr.upload.onprogress = function(e){
						if (e.lengthComputable) {
							var percent = e.loaded / e.total * 100;		
							cache.$progress_bar.animate({
								width : percent + '%'
							},500);
							
						}
					}
					xhr.send(fd);
				},
				done : function(data){
					var _this = this,
					data = this.responseText,
						url;
					try{
						data = $.parseJSON(this.responseText);
					}catch(error){
						data = false;
					}
					if(data && data.status === 'success'){
						//upload_tip('success',that.config.lang.M00003);
						var url = data.img_url,
							args = {
								'img_url' : url,
								'size' : ''
							},
							$tpl = $(tpl(args)).hide(),
							$img_url;
							

						cache.$files_container.show().prepend($tpl);
						$tpl.fadeIn('slow');
						
						$img_url = $('#img-url-' + get_id(url)).on('click',function(){
							$(this).select();
						}).select();

						/**
						 * bind thumb_change click
						 */
						thumb_change(args.img_url);
							
						cache.file_i++;

						/** 
						 * check all thing has finished, if finished
						 */
						if(cache.file_count ==  cache.file_i){
							var tx = format(that.config.lang.M00004,cache.file_count);
							upload_tip('success',tx);
							/** 
							 * reset
							 */
							cache.file_i = 0;
							cache.$file.val('');
							// cache.$fm.show();
						/** 
						 * upload next pic
						 */
						}else{
							/** 
							 * check interval time
							 */
							var end_time = new Date(),
								interval_time = end_time - cache.start_time,
								timeout = that.config.interval - interval_time,
								timeout = timeout < 0 ? 0 :timeout;
							/** 
							 * if curr time > interval time, upload next pic right now 
							 */
							setTimeout(function(){
								file.upload(cache.files[cache.file_i]);
							},timeout);
						}
					}else if(data && data.status === 'error'){
						upload_tip('danger',data.msg);
					}else{
						upload_tip('danger',that.config.lang.E00001);
					}
				},
				fail : function(){
					upload_tip('danger',that.config.lang.E00001);
				},
				always : function(){
					
				}
			}
		}
		/**
		 * get_cookie
		 * 
		 * @params string
		 * @return string
		 * @version 1.0.0
		 * @author KM@INN STUDIO
		 */
		var get_cookie = function(c_name){
			var i,x,y,ar=document.cookie.split(';');
			for(i=0;i<ar.length;i++){
				x=ar[i].substr(0,ar[i].indexOf('='));
				y=ar[i].substr(ar[i].indexOf('=')+1);
				x=x.replace(/^\s+|\s+$/g,'');
				if(x==c_name) return unescape(y);
			}
		}
		/**
		 * set_cookie
		 * 
		 * @params string cookie key name
		 * @params string cookie value
		 * @params int the expires days
		 * @return n/a
		 * @version 1.0.0
		 * @author KM@INN STUDIO
		 */
		var set_cookie = function(c_name,value,exdays){
			var exdate = new Date();
			exdate.setDate(exdate.getDate() + exdays);
			var c_value=escape(value) + ((exdays==null) ? '' : '; expires=' + exdate.toUTCString());
			document.cookie = c_name + '=' + c_value;
		}
		
		var tpl = function(args){
			if(!args) return false;
			var id = get_id(args.img_url),
				img_url = args.img_url,
				size_str = '<select id="img-size-' + id + '" class="form-control">',
				i = 0,
				selected,
				content,
				cookie = get_cookie(that.config.cookie_last_size),
				last_img_size = cookie ? cookie : 'thumb150';
			for(var key in that.config.sizes){
				i++;
				/**
				 * check the cookie
				 */
				
				if(!cookie){
					selected = i === 1 ? ' selected ' : '';
				}else{
					selected = cookie === key ? ' selected ' : '';
				}
				/**
				 * size
				 */
				size_str += 
					'<option title="' + that.config.sizes[key] + '" value="' + key + '" ' + selected + '>' + key + ' - ' + that.config.sizes[key] + '</option>';
			}
			size_str += '</select>';
			return '' +
'<form class="tpl" id="tpl-' + id + '" action="javascript:void(0);">'+
	'<a class="img-link" id="img-link-' + id + '" href="' + get_img_url_by_size(last_img_size,img_url) + '" target="_blank">' +
		'<img src="' + get_img_url_by_size('square',img_url) + '" alt="" id="img-preview-' + id + '" class="img-preview" alt="preview">' +
	'</a>' +
	'<div class="controls">' + 
		'<input id="img-url-' + id + '" type="url" class="img-url form-control" value="' + get_img_url_by_size(last_img_size,img_url) + '" readonly />' +
			size_str +
	'</div>'+
'</form>';
		}
		var upload_tip = function(t,c){
			if(!cache.$upload_tip) cache.$upload_tip = $('#upload-tip');
			cache.$upload_tip.html(status_tip(t,c)).show();
		}
		var format = function(){
			var ary = [];
			for(var i=1;i<arguments.length;i++){
				ary.push(arguments[i]);
			}
			return arguments[0].replace(/\{(\d+)\}/g,function(m,i){
				return ary[i];
			});
		}
		/**
		 * get_img_url_by_size
		 * 
		 * @params string size The img size,etc:
		 * 						square 		(mw/mh:80)
		 * 						thumbnail 	(mw/mh:120)
		 * 						thumb150 	(150x150,crop)
		 * 						mw600 		(mw:600)
		 * 						bmiddle  	(mw:440)
		 * 						large 		(original)
		 * @return string The img url
		 * @version 1.0.0
		 * @author KM@INN STUDIO
		 */
		var get_img_url_by_size = function(size,img_url){
			if(!size) size = 'square';
			var file_obj = img_url.split('/'),
				len = file_obj.length,
				basename = file_obj[len - 1],
				old_size = file_obj[len - 2],
				hostname = img_url.substr(0,img_url.indexOf(old_size));
				hostname = hostname.replace('http://','https://');
				url = hostname + size + '/' + basename;
			return url;
		}
		/**
		 * get_id
		 * 
		 * @params string Image url
		 * @return string The ID
		 * @version 1.0.0
		 * @author KM@INN STUDIO
		 */
		var get_id = function(img_url){
			var id = img_url.split('/');
			return id[id.length - 1].split('.')[0];
		}
		/**
		 * thumb_change
		 * 
		 * @params string img_url
		 * @return n/a
		 * @version 1.0.0
		 * @author KM@INN STUDIO
		 */
		var thumb_change = function(img_url){
			var id = get_id(img_url);
			
			for(var key in  that.config.sizes){
				/**
				 * start bind
				 */
				$('#img-size-' + id).on('change',function(){
					var $this = $(this),
						img_size_url = get_img_url_by_size($this.val(),img_url);
					$('#img-url-' + id).val(img_size_url).select();
					$('#img-link-' + id).attr('href',img_size_url);
					/**
					 * set cookie for next default changed
					 */
					set_cookie(that.config.cookie_last_size,$this.val(),365);
				});
			}
		}
	}

	function status_tip(t,c){
		return '<div class="alert alert-' + t + '" role="alert">' + c + '</div>';
	}
	function hide_no_js(){
		var $no_js = $('.hide-no-js'),
			$on_js = $('.hide-on-js');
		$on_js[0] && $on_js.hide();
		$no_js[0] && $no_js.show();
		
	}
	
	function init(){
		$(document).ready(function(){
			hide_no_js();
			var o_spe = new spe();
			o_spe.check_authorize();
		})
	}
	init();
})(document);