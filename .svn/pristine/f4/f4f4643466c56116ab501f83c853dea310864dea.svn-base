App.Upload = function (opts) {
	var options = {
		fileInput 		: null,				// html file控件
		dragDropElement : null,				// 拖拽敏感区域
		actButton 		: null,				// 提交按钮
		url 			: ''				// ajax地址
		fileFilter 		: [],				// 过滤后的文件数组
		onSelect 		: function() {},	// 文件选择后
		onDelete 		: function() {},	// 文件删除后
		onDragOver 		: function() {},	// 文件拖拽到敏感区域时
		onDragLeave		: function() {},	// 文件离开到敏感区域时
		onProgress		: function() {},	// 文件上传进度
		onSuccess		: function() {},	// 文件上传成功时
		onFailure		: function() {},	// 文件上传失败时,
		onComplete		: function() {}
	}
	
	//选择文件组的过滤方法
	options.filter =  function(files) {		
		return files;	
	}

	// 上传失败默认处理
	options.onFailure = function(file, msg) {
		alert(msg);
	}

	//文件拖放
	var dragHover = function(e) {
		e.stopPropagation();
		e.preventDefault();
		e.type == 'dragover'?
			options.onDragOver.call(e.target):
			options.onDragLeave.call(e.target);
	}

	// 获取选择文件，file控件或拖放
	var getFiles = function(e) {
		// 获取文件列表对象
		var files = e.target.files || e.dataTransfer.files;

		dragHover(e);

		//继续添加文件
		options.fileFilter = options.fileFilter.concat(options.filter(files));
		dealFiles();
	}
	
	// 选中文件的处理与回调
	var dealFiles = function() {
		for (var i = 0, file; file = options.fileFilter[i]; i++) {
			//增加唯一索引值
			file.index = i;
		}
		//执行选择回调
		options.onSelect(options.fileFilter);
	},
	
	// 删除对应的文件
	var deleteFile = function(fileDelete) {
		var arrFile = [];
		for (var i = 0, file; file = options.fileFilter[i]; i++) {
			if (file != fileDelete) {
				arrFile.push(file);
			} else {
				options.onDelete(fileDelete);	
			}
		}
		options.fileFilter = arrFile;
	}
	
	// 文件上传
	var uploadFile: function() {
		var upload = function(file) {
			var xhr = new XMLHttpRequest();
			if (xhr.upload) {
				// 上传中
				xhr.upload.addEventListener("progress", function(e) {
					options.onProgress(file, e.loaded, e.total);
				}, false);
	
				// 文件上传成功或是失败
				xhr.onreadystatechange = function(e) {
					if (xhr.readyState == 4) {
						if (xhr.status == 200) {
							options.onSuccess(file, xhr.responseText);
							options.deleteFile(file);
							if (!options.fileFilter.length) {
								//全部完毕
								options.onComplete();	
							}
						} else {
							options.onFailure(file, xhr.responseText);		
						}
					}
				};
	
				// 开始上传
				xhr.open("POST", options.url, true);
				xhr.setRequestHeader("X_FILENAME", file.name);
				xhr.send(file);

				return true;
			} else {
				// alert('浏览器不支持上传，推荐使用Chrome、Firefox或IE10以上浏览器。');
				options.onFailure(file, '浏览器不支持上传，推荐使用Chrome、Firefox或IE10以上浏览器。');
				return false;
			}	
		}
		for (var i = 0, file; file = options.fileFilter[i]; i++) {
			if(!upload(file)) {
				break;
			}
		}	
	}
	
	var init = function(opts) {
		for(var k in opts) {
			options[k] = opts[k];
		}
		if (options.dragDropElement) {
			options.dragDropElement.addEventListener("dragover", function(e) { dragHover(e); }, false);
			options.dragDropElement.addEventListener("dragleave", function(e) { dragHover(e); }, false);
			options.dragDropElement.addEventListener("drop", function(e) { getFiles(e); }, false);
		}
		
		//文件选择控件选择
		if (options.fileInput) {
			options.fileInput.addEventListener("change", function(e) { getFiles(e); }, false);	
		}
		
		//上传按钮提交
		if (options.actButton) {
			options.actButton.addEventListener("click", function(e) { uploadFile(e); }, false);	
		} 
	}

	init.call(this, opts || {});
}