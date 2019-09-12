App.Box = function() {
	var box = new Object();

	var createBox = function() {
		var div = $('#app-box');
		if(div.length == 0) {
			div = $([
				'<div id="app-box">',
					'<div id="app-box-content">',
						'<a id="app-box-close"><i class="fa fa-times"></i></a>',
					'</div>',
				'</div>'
			].join(''));
			div.appendTo(document.body);
			div.on('click', '#app-box-close', function() {
				close('close');
			}).on('click', 'button[app-state]', function() {
				var state = $(this).attr('app-state');
				close(state);
			});
		}
		return div;
	}

	box.open = function(src) {
		var div = createBox();
		box.state = true;
		$.ajax({
			url: src,
			dataType: 'html',
			success: function(response) {
				var context = $('<div id="app-box-body"></div>');
				context.html(response);
				context.appendTo('#app-box-content');
				div.show(0, function() {
					var content = context.children().eq(0);
					if(!content.attr('app-init')) {
						content.trigger('init');
						content.attr('app-init', 'on');
					}
					content.find('input[autofocus]').focus();
					content.trigger('active');
				});
			}
		});
	}

	var close = function(state) {
		var content = $('#app-box-body').children().eq(0);
		(state != undefined || state !== true) && content.trigger('close', [box, state]);
		if(box.state != false) {
			content.remove();
			$('#app-box-body').remove();
			createBox().hide();
		}
		box.state = true;
	}

	box.close = function(state) {
		if(state !== false) {
			close(state);
		}
	}

	return box;
}();

App.urlReplace = function(url, key, val) {
	var tmp   = url.split('?');
	var query = '?' + (tmp[1] || '');
	var reg   = new RegExp('([\?&])' + key + '=[^&]*');

	query = query.replace(reg, '');
	query = query + (query? '&': '?');
	if(key) {
		query += encodeURIComponent(key) + '=' + encodeURIComponent(val || '');
	}
	return url = tmp[0] + query;
}

App.upload = function(selector, url, complete) {
	var thumbs = $(selector);
	var length = thumbs.length, count = 0, failure = 0;
	var done = function() {
		count++;
		if(count == length) {
			if(failure) {
				App.alert('共上传 ' + length + ' 张图片，' + failure + ' 张上传失败。请重试。');
			} else {
				complete && complete();
			}
		}
	}
	var progress = function(file, loaded, total) {
	}
	if(length > 0) {
		thumbs.each(function() {
			var item = $(this);
			var file = item.data('file');
			if(file) {
				var xhr = new XMLHttpRequest();
				if (xhr.upload) {
					// 上传中
					xhr.upload.addEventListener("progress", function(e) {
						progress(file, e.loaded, e.total);
					}, false);
		
					// 文件上传成功或是失败
					xhr.onreadystatechange = function(e) {
						if (xhr.readyState == 4) {
							if (xhr.status == 200) {
								item.removeData('file');
								done();
							} else {
								failure++;
								done();
								// options.onFailure(file, xhr.responseText);		
							}
						}
					};
		
					// 开始上传
					xhr.open("POST", url, true);
					xhr.setRequestHeader("X_File_Name", file.name);
					xhr.send(file);
				}
			} else {
				done();
			}
		});
	} else {
		done();
	}
}

App.PushBox = new function() {
	this.open = function(id, mod, text) {
		var params = [
			'id=' + encodeURIComponent(id),
			'mod=' + encodeURIComponent(mod),
			'text=' + encodeURIComponent(text)
		];
		App.Box.open('push.html?' + params.join('&'));
	}
}

App.Notific = new function() {
	var _alert = function(type, msg, callback) {
		$().toastmessage('showToast', {
			text             : msg,
			sticky           : false,
			inEffectDuration : 100,   // in effect duration in miliseconds
			stayTime         : 5000,
			position         : 'top-center',
			type             : type,
			close            : callback
		});
	}

	this.popover = function(type, msg, callback) {
		_alert(type, msg, callback);
	}

	this.info = function(msg, callback) {
		_alert('info', msg, callback);
	}

	this.success = function(msg, callback) {
		_alert('success', msg, callback);
	}

	this.warning = function(msg, callback) {
		_alert('warning', msg, callback);
	}

	this.error = function(msg, callback) {
		_alert('error', msg, callback);
	}
}
