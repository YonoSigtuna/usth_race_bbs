<html>
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
	<meta http-equiv="content-type" content="text/html; charset=utf-8">
	<title>发帖</title>
	<!-- css -->
	<link rel="stylesheet" href="./css/header.css">
	<link rel="stylesheet" href="./css/style.css">
	<link rel="stylesheet" href="./css/forum.css">
	<link rel="stylesheet" href="./css/send_topic.css">
	
</head>
<body>
	<!-- 引入顶部导航栏 -->
	<?php require_once dirname(__FILE__).'/header.php'; ?>
	<?php
		// 未登录处理
		if (!$uid) {
			exit("<script>alert('当前页面需要登录才能查看内容')</script>");
		}
	?>





	<?php
		// 新发帖
		if ($_GET['mod'] == 'add') {
			$fid = $_GET['fid'];
			$cid = md5("{$_GET['fid']}||$uid");
		}


		// 修改贴需要的数据请求
		if ($_GET['mod'] == 'replace') {
			// 找到tid对应的fid
			$tid = $_GET['tid'];
			$cid = $tid;
			$fid = mysqli_query($link, "SELECT fid FROM topics_index WHERE tid=$tid;");
			$fid = $fid->fetch_assoc()['fid'];
			
			// 根据fid和tid最后一位分表查询找到帖子作者的uid
			$tid_last_char = substr($tid, -1);
			$data = mysqli_query($link, "SELECT * FROM `topics_${fid}_${tid_last_char}` WHERE tid=$tid;");
			$data = $data->fetch_assoc();

			// 对tags进行格式化
			$data['tags'] = format_tags_to_str($tid);

			// 如果作者uid不等于前端uid，无权限修改
			if ($data['uid'] != $uid) {
				exit("<script>alert('当前tid对应帖子的作者不是你，无更改权限')</script>");
			}
		}
	?>



	

	<!-- TITLE分割 -->
	<div class="title_split_line" style="width: 35%;">
		<span>
			<?php
				if ($_GET['mod'] == 'add') {
					echo '发帖';
				} else {
					echo '修改帖子';
				}
			?>
		</span>
	</div>


	<div class="forum">
		<header>
			<input type="text" class="topic_title" id="topic_title" placeholder="帖子标题(必填)" value="<?php echo $data['title']; ?>">
			<input type="text" class="topic_title" id="target_fid" placeholder="发帖至目标版块fid(必填)" value="<?php echo $fid; ?>">
			<input type="text" class="topic_title" id="tags" placeholder="标签，用|号隔开(可不填)" value="<?php echo $data['tags']; ?>">
			<input type="text" class="topic_title" id="cover" placeholder="封面图片aid，用|号隔开(可不填)" value="<?php echo $data['preview']; ?>">
			<button class="button2" onclick="create_preview()">生成预览封面</button>
			<button class="func_button" onclick="const load_protect = prompt('你确定要加载帖子数据吗？会覆盖掉当前帖子数据，如确定请输入：yes'); load_topic_data(load_protect)">加载</button>
			<button class="func_button" onclick="const save_protect = prompt('你确定要保存帖子数据吗？会覆盖掉之前保存的帖子数据，如确定请输入：yes'); save_topic_data(save_protect)">保存</button>
			<button class="func_button" onclick="send_topic()">发帖</button>
		</header>
		<main class="board send_topic_data">
			<!-- 上传图片按钮 -->
			<div class="function">
				<div onclick="open_upload_imgs_cmd()"><img src="./data/imgs/img.png" loading="lazy" alt="图片加载失败"><br><span>图片上传</span></div>
				<div onclick="open_upload_videos_cmd()"><img src="./data/imgs/video.png" loading="lazy" alt="图片加载失败"><br><span>视频上传</span></div>
				<div onclick="alert('站长目前不知道这个功能能干嘛，暂不开发咯QWQ')"><img src="./data/imgs/file.png" loading="lazy" alt="图片加载失败"><br><span>附件上传</span></div>
				<div onclick="insert_text('<br>')"><img src="./data/imgs/switch_line.png" loading="lazy" alt="图片加载失败"><br><span>换行</span></div>
				<div onclick="insert_text('[_d ]')"><img src="./data/imgs/img_defense.png" loading="lazy" title="格式：[_d {_i1}{_i2}{_i3}]" alt="图片加载失败"><br><span>毛玻璃</span></div>
				<div onclick="insert_text('{?pre\n\n?}')"><img src="./data/imgs/pre.png" loading="lazy" title="格式：{?pre *?}" alt="图片加载失败"><br><span>Pre</span></div>
				<div onclick="open_table_guide()"><img src="./data/imgs/table.png" loading="lazy" alt="图片加载失败"><br><span>表格</span></div>
				<div onclick="insert_text('{_goto}')"><img src="./data/imgs/goto.png" loading="lazy" title="格式：{_goto1}" alt="图片加载失败"><br><span>站内跳转</span></div>
			</div>

			<!-- 发帖文本域 -->
			<textarea id="topic_content" placeholder="帖子内容"><?php echo $data['content']; ?></textarea>

			<!-- 特别说明部分 -->
			<div>
				<br>特别说明：<br>
				1、仅仅上传图片是无法在帖子内显示，请自行点击图片将图片代码添加进帖子内容里。
			</div>
		</main>
	</div>

	







<!-- MD5 -->
<script src="https://cdn.bootcdn.net/ajax/libs/blueimp-md5/2.18.0/js/md5.js"></script>

<!-- 加载需要的js -->
<?php require_once dirname(__FILE__)."/js/send_topic.php"; ?>



<script>
	// 
	// 
	// 打开表格教程
	// 
	// 
	const open_table_guide = () => {
		cmd.open()
		cmd.title("建表需要一定的HTML知识，本站暂时无法提供更简便的建表格式")
		cmd.width("80%")
		cmd.content(`
			<div style="display:float; float:left; width: 60%">
				<pre>
&lt;table border="1" cellpadding="12px" cellspacing="0px" width="40%">
   &lt;thead>
      &lt;tr>
         &lt;th>Key&lt;/th> &lt;th>Value&lt;/th> &lt;th>Value2&lt;/th>
      &lt;/tr>
   &lt;/thead>

   &lt;tbody>
      &lt;tr>
         &lt;td>测试&lt;/td> &lt;td>测试2&lt;/td> &lt;td>测试3&lt;/td>
      &lt;/tr>
      &lt;tr>
         &lt;td>测试4&lt;/td> &lt;td>测试5&lt;/td> &lt;td>测试6&lt;/td>
      &lt;/tr>

      // 合并教程
      &lt;tr>
         &lt;td>1&lt;/td>	&lt;td colspan="2">2&lt;/td>
      &lt;/tr>
      &lt;tr>
         &lt;td rowspan="2">3&lt;/td> &lt;td>4&lt;/td> &lt;td>5&lt;/td>
      &lt;/tr>
      &lt;tr>
         &lt;td>6&lt;/td> &lt;td>7&lt;/td>
      &lt;/tr>
   &lt;/tbody>
&lt;/table>
				</pre>
			</div>

			<div style="display:float; float:left; width: 40%">
				cellpadding 单元格与文字边框的距离，默认0px<br>
				cellspacing 单元格与单元格的距离，默认2px<br>
				<br>
				rowspan 跨行合并<br>
				colspan 跨列合并<br>
				<br>
				rowspan="2" 合并2个单元格<br>
				<br>

				
				<table border="1" cellspacing="0px" width="100%">
					<thead>
						<tr>
							<th>Key</th> <th>Value</th> <th>Value2</th>
						</tr>
					</thead>

					<tbody>
						<tr>
							<td>测试</td>	<td>测试2</td>	<td>测试3</td>
						</tr>
						<tr>
							<td>测试4</td>	<td>测试5</td>	<td>测试6</td>
						</tr>
						<tr>
							<td>1</td>	<td colspan="2">2</td>
						</tr>
						<tr>
							<td rowspan="2">3</td>	<td>4</td>	<td>5</td>
						</tr>
						<tr>
							<td>6</td>	<td>7</td>
						</tr>
					</tbody>
				</table>
			</div>
		`)
	}



	// 
	// 
	// 生成预览封面
	// 
	// 
	const create_preview = () => {
		// 获取fid
		var fid = document.querySelector('#target_fid').value
		var aids = document.querySelector('#cover').value

		// 构建xhr请求给服务器
		var data = new FormData()
		data.append("cmd", "create_preview")
		data.append("fid", fid)
		data.append("folder", "<?php echo $cid; ?>")
		data.append("aids", aids)

		// 对参数进行判断
		if (!fid) {
			alert("fid未填写")
			return
		}
		if (!aids) {
			alert("封面图片aid未填写")
			return
		}

		// 发送请求给服务器
		var xhr = new XMLHttpRequest()
		xhr.open("POST", './data/upload.php', true)
		xhr.send(data)

		// xhr处理
		xhr.onreadystatechange = () => {
			if(xhr.readyState == 4 && xhr.status == 200){
				
				// 返回响应
				var return_data = xhr.responseText
				console.log(return_data);
				switch (return_data) {
					case 'succ':
						alert("封面缩略图生成完成")
						break;

					case '图片不存在':
						alert("fid或aid不正确，无法找到正确的图片文件")
						break;
					
					// case '目标图片已存在':
					// 	alert("缩略图已存在，请删除后再重新生成")
					// 	break;

					default:
						alert("未知错误，请联系站长")
						break;
				}
			}
		}
	}
	







	// 
	// 
	// 发帖文本域内插入内容
	// 
	// 
	function insert_text(text) {
		var textarea = document.getElementById("topic_content")
		
		// 获取光标位置
		var cursorPos = textarea.selectionStart

		// 要插入的内容
		var textToInsert = text
		var currentValue = textarea.value
		var newValue = currentValue.slice(0, cursorPos) + textToInsert + currentValue.slice(cursorPos);
		textarea.value = newValue;

		// 恢复光标位置
		textarea.selectionStart = cursorPos + textToInsert.length;
		textarea.selectionEnd = cursorPos + textToInsert.length;
		textarea.focus();
	}




	// 
	// 
	// 删除图片
	// 
	// 
	const delete_img = (aid) => {
		// 构建xhr请求
		var data = new FormData()
		data.append("cmd", "remove_topic_img")
		data.append("cid", "<?php echo $cid; ?>")
		data.append("aid", aid)

		// 发送xhr
		var xhr = new XMLHttpRequest()
		xhr.open("POST", './data/upload.php', true)
		xhr.send(data)

		// 前端删除对应的图片div
		document.querySelector(`#_${aid}`).innerHTML = ''
	}



	// 
	// 
	// 打开隐藏input的上传窗口
	// 
	// 
	const open_imgs_upload_windows = () => {
		imgs_upload.click()
	}




	// 
	// 
	// 加载已上传的图片
	// 
	// 
	const load_topic_imgs = (cid) => {
		// 构建xhr请求
		var data = new FormData()
		data.append("cmd", "load_topic_imgs")
		data.append("cid", cid)

		// 发送xhr
		var xhr = new XMLHttpRequest()
		xhr.open("POST", './server.php', true)
		xhr.send(data)

		// xhr处理
		xhr.onreadystatechange = () => {
			if(xhr.readyState == 4 && xhr.status == 200){
				try {
					
					if (xhr.responseText) {
						// 对返回数据进行JSON解析
						var return_data = JSON.parse(xhr.responseText)

						// 判断数组长度非0
						if (return_data.length != 0) {
							var imgs = return_data

							// 循环每一张图片
							for (n = 0; n < imgs.length; n++) {
								// aid去除.jpg
								var aid = imgs[n].replace('.jpg', '')

								// 前端显示图片到cmd
								var html = `
									<div class="card" id="_${aid}">
										<img src="./data/forums/${cid}/${aid}.jpg" loading="lazy" alt="图片加载失败" onclick="insert_text('{_i${aid}}')">
										<progress value="100" max="100"></progress>
										<span class="remove" onclick="delete_img('${aid}')">删除</span>
										<span class="aid">aid:${aid}</span>
									</div>
								`
								document.querySelector('.cmd .board main .imgs').insertAdjacentHTML("beforeend", html)
							}
						}
					}
				} catch (error) {
					console.error('xhr请求失败，失败code：' + error)
				}
			}
		}

		// xhr请求超时
		xhr.timeout = 5000	// ms
		xhr.ontimeout = () => alert("请求超时")

	}



	// 
	// 
	//	图片压缩并上传
	// 
	// 
	function img_zip(id, file) {
		return new Promise((resolve, reject) => {
			const reader = new FileReader();

			reader.onload = function(e) {
				const img = new Image();
				img.src = e.target.result;

				img.onload = function() {
				const canvas = document.createElement('canvas');
				const ctx = canvas.getContext('2d');

				// 设置压缩后的宽高（取一边做压缩判定）
				const maxWidth = 1920;
				const maxHeight = 2048;

				let width = img.width;
				let height = img.height;

				// 如果图片尺寸超过最大限制，则等比例缩放
				if (width > maxWidth || height > maxHeight) {
					const ratio = Math.min(maxWidth / width, maxHeight / height);
					width *= ratio;
					height *= ratio;
				}

				// 设置canvas尺寸
				canvas.width = width;
				canvas.height = height;

				// 绘制图片到canvas
				ctx.drawImage(img, 0, 0, width, height);

				// 转为blob返回
				canvas.toBlob(function(blob) {
					// resolve(blob); // 将Blob对象通过resolve()传递回来
					var file = blob

					// 获取DOM元素
					var imgs_area = document.querySelector('.cmd .board main .imgs')

					// 每张图片生成url
					let url = URL.createObjectURL(file)
					var html = `
						<div class="card" id="_${id}">
							<img src="${url}" loading="lazy" alt="图片加载失败">
							<progress value="0" max="100"></progress>
							<span class="remove" onclick="">上传中</span>
							<span class="aid"></span>
						</div>
					`
					imgs_area.insertAdjacentHTML("beforeend", html)

					// 构建xhr文件数据
					var xhr = new XMLHttpRequest()
					var data = new FormData()
					data.append("cmd", "topic_imgs_upload")
					data.append("cid", "<?php echo $cid; ?>")
					data.append("file", file)

					// 监听xhr
					xhr.onreadystatechange = () => {
						if(xhr.readyState == 4 && xhr.status == 200){
							try {
								// xhr上传完成回显aid和删除
								var aid = xhr.responseText

								document.querySelector(`#_${id} .aid`).textContent = 'aid:' + aid

								// 将DOM元素MD5 ID改位aid
								// 目的：辅助删除图片
								document.querySelector(`#_${id}`).setAttribute("id", `_${aid}`)

								// 赋予删除onclick具体内容
								document.querySelector(`#_${aid} .remove`).textContent = '删除'
								document.querySelector(`#_${aid} .remove`).setAttribute("onclick", `delete_img('${aid}')`)

								// // 赋予图片插入code
								document.querySelector(`#_${aid} img`).setAttribute("onclick", `insert_text('{_i${aid}}')`)
							} catch (error) {
								console.error('xhr请求失败，失败code：' + error)
							}
						}
					}

					// 获取进度条dom
					let progress = document.querySelector(`#_${id} progress`)

					// 上传进度条显示
					xhr.upload.onprogress = function(e){
						let progress_value = (e.loaded / e.total) * 100
						progress.value = progress_value

						// 上传完成分配aid
						if (progress_value == 100) {
							document.querySelector(`#_${id} .remove`).textContent = '分配aid中'
						}
					}

					// 发送xhr
					xhr.open("POST", `./data/upload.php`, true)
					xhr.send(data)


				}, 'image/jpeg', 0.75);
				};
			};

			reader.onerror = reject;
			reader.readAsDataURL(file);
		});
	}




	// 
	// 
	// 视频删除
	// 
	// 
	const delete_video = (vid) => {
		var vid = vid.replace(".mp4", '')
		
		// 构建xhr数据
		var data = new FormData()
		data.append("cmd", "delete_video")
		data.append("cid_or_tid", "<?php echo $cid; ?>")
		data.append("vid", vid)

		// 发送xhr到目标服务器
		var xhr = new XMLHttpRequest()
		xhr.open("POST", "./data/upload.php", true)
		xhr.send(data)

		// 监听请求结果
		xhr.onreadystatechange = () => {
			if(xhr.readyState == 4 && xhr.status == 200){
				alert(`${vid}.mp4已删除，请重新打开"视频上传窗口"确认`)
			}
		}

	}








	// 
	// 
	// 视频上传进度条回显示
	// 
	// 
	const video_upload_process = (uploaded, all_chunks) => {
		// 更新上传记录
		if (uploaded < all_chunks) {
			document.querySelector(".cmd .function span").textContent = `正在上传: ${uploaded} / ${all_chunks}`
		}

		// 视频上传到最后一个切片，服务器正在恢复切片
		if (uploaded == all_chunks - 1) {
			document.querySelector(".cmd .function span").textContent = `正在合并切片: ${uploaded} / ${all_chunks} （请耐心等待2分钟）`
		}
	}




	// 
	// 
	// 视频上传终端打开
	// 
	// 
	const open_upload_videos_cmd = () => {
		// 更改cmd标题
		document.querySelector('.cmd .board header span').textContent = '视频上传'

		// cmd内容体变更
		var html = `
			<div class="function">
				<button onclick="open_video_upload_windows()">视频上传</button> <span>未上传</span>
				<input type="file" accept="video/mp4, video/webm, video/x-msvideo" id="upload_video" hidden>
			</div>
			<div id="videos">
				<br>
			</div>
		`
		document.querySelector('.cmd .board main').innerHTML = html

		// cmd终端显示
		document.querySelector('.cmd').style.display = 'block'
		
		// dom元素获取
		var video_upload = document.querySelector('#upload_video')

		// 构建xhr请求当前cid所有的视频
		var data = new FormData()
		data.append("cmd", "load_topic_videos")
		data.append("cid", "<?php echo $cid; ?>")
		
		// 发送xhr到目标服务器
		var xhr = new XMLHttpRequest()
		xhr.open("POST", "./data/upload.php", true)
		xhr.send(data)

		// 监听请求结果
		xhr.onreadystatechange = () => {
			if(xhr.readyState == 4 && xhr.status == 200){
				// 对返回数据进行JSON解析
				var videos = JSON.parse(xhr.responseText)

				// 判断数组长度非0，非0即存在视频
				if (videos.length != 0) {
					
					// 循环每一个视频并添加到DOM中
					for (i = 0; i < videos.length; i++) {
						var video = videos[i]
						var vid = video.replace('.mp4', '')
						var html = `<span class="box" onclick="insert_text('{_v${vid}}')">${video}</span><span class="box2" onclick="delete_video('${video}')">删除${video}</span><br><br>`
						document.querySelector('.cmd .board main #videos').insertAdjacentHTML("beforeend", html)
					}
				}
			}
		}


		// 监听DOM改变
		video_upload.onchange = function () {
			document.querySelector(".cmd .function span").textContent = '文件正在切片中，请稍等'

			// 获取视频文件
			var file = video_upload.files[0]

			// 获取文件md5，定义切片大小，总块数计算
			const file_md5 = md5(file)
			const chunk_size = 1 * 1024 * 1024; // 2MB
			var all_chunks = Math.ceil(file.size / chunk_size)

			// 定义片头，定义片尾
			let start = 0
			let end = Math.min(chunk_size, file.size)
			let uploaded = 0

			// 循环总切片数
			for (i = 0; i < all_chunks; i++) {
				let chunk = file.slice(start, end)

				// 构建xhr数据
				let data = new FormData()
				data.append("cmd", "check_chunk_exist")
				data.append("index", i)
				data.append("all_chunks", all_chunks)
				data.append("path", `./forums/<?php echo $cid; ?>/cache/${file_md5}.mp4.${i}`)

				// 发送xhr
				let xhr = new XMLHttpRequest();
				xhr.open('POST', './data/upload.php', true);
				xhr.send(data);

				// xhr监听
				xhr.onreadystatechange = () => {
					if(xhr.readyState == 4 && xhr.status == 200){
						let index = xhr.responseText

						// 切片存在，更新进度条
						if (index == 'exist') {
							uploaded++
							
							// 更新上传进度
							video_upload_process(uploaded, all_chunks)

						// 切片不存在，或切片为最后一块
						} else {

							// 构建xhr数据
							let data = new FormData()
							data.append("cmd", "upload_video")
							data.append("cid", "<?php echo $cid; ?>")
							data.append('file_md5', file_md5)
							data.append('file_size', file.size)
							data.append('index', index)
							data.append('chunk', chunk)
							data.append('all_chunks', all_chunks)

							// 发送xhr
							let xhr = new XMLHttpRequest()
							xhr.open('POST', './data/upload.php', true)
							xhr.send(data)

							// xhr处理
							xhr.onreadystatechange = () => {
								if(xhr.readyState == 4 && xhr.status == 200){
									uploaded++

									// 更新上传进度
									video_upload_process(uploaded, all_chunks)

									if (xhr.responseText == 'succ') {
										document.querySelector(".cmd .function span").textContent = '上传完成，请重新打开"视频上传"'
									}

									// 恢复视频失败回显
									if (xhr.responseText == 'error') {
										document.querySelector(".cmd .function span").textContent = '视频上传失败，请重新上传或者联系站长'
									}
								}
							}
						}
					}
				}

				// 下一个片段的开头等于上个片段的末尾
				start = end
						
				// 新生成一个片尾，值为end + chunk_size
				end = Math.min(end + chunk_size, file.size)
			}
		}
	}

						// let index = xhr.responseText
						// switch (index) {

						// 	// 片段存在
						// 	case 'exist':
						// 		uploaded++
						// 		video_upload_process(uploaded, all_chunks)
						// 		break;

						// 	// 最后一个片段
						// 	case 'last':；//
						// 		document.querySelector(".cmd .function span").textContent = '最后切片清除，请重新上传'
						// 		break;


						// 	// 不存在片段，通过xhr进行上传
						// 	default:

						// 		break;
						// }






	const open_video_upload_windows = () => {
		var video_upload = document.querySelector('#upload_video')
		video_upload.click()
	}



	// 
	// 
	// 图片上传终端打开
	// 
	// 
	const open_upload_imgs_cmd = () => {
		// 重新准备cmd title
		document.querySelector('.cmd .board header span').textContent = '图片上传（75%有损压缩）'

		// cmd内容体变更
		var html = `
			<div class="function">
				<button onclick="open_imgs_upload_windows()">图片上传</button>
				<input type="file" accept="image/jpeg, image/png, image/gif" id="imgs_upload" multiple hidden>
			</div>
			<div class="imgs"></div>
		`
		document.querySelector('.cmd .board main').innerHTML = html

		// cmd终端显示
		document.querySelector('.cmd').style.display = 'block'

		// dom元素获取
		const imgs_upload = document.querySelector('#imgs_upload')

		// 加载存在的图片
		load_topic_imgs("<?php echo $cid; ?>")

		// 监听图片input的变化
		imgs_upload.onchange = function () {
			const files = imgs_upload.files

			// 遍历所有图片
			for (let n = 0; n < files.length; n++) {

				// 延迟0.5s
				setTimeout(function() {

					// 获取图片并压缩图片
					var file = files[n];

					// 生成每张图片专属的进度条ID
					let id = md5(file['name'] + file['size']);

					// 压缩图片并上传
					img_zip(id, file);
				}, n * 500);
			}
		}
	}





	// 
	// 
	//	加载帖子数据
	// 
	// 
	const load_topic_data = (load_protect) => {
		if (load_protect == 'yes') {
			// 取title，tags，content
			var title = get_cookie('topic_title')
			var tags = get_cookie('topic_tags')
			var content = get_cookie('topic_content')

			// 添加至html
			document.querySelector('#topic_title').value = title
			document.querySelector('#tags').value = tags
			document.querySelector('#topic_content').value = content
		}
	}






	// 
	// 
	//	保存发帖数据至前端cookie
	// 
	// 
	const save_topic_data = (save_protect) => {
		if (save_protect == 'yes') {
			// 取帖子标题进行cookie存储
			var topic_title = document.querySelector('#topic_title').value

			// 取标签
			var topic_tags = document.querySelector('#tags').value

			// 取帖子内容（大量文本存储）
			var topic_content = document.querySelector('#topic_content').value
			
			// 整理cookie信息
			var date = new Date()
			date.setDate(date.getDate() + 30) // 设置有效期为30天
			var expires = 'expires=' + date.toUTCString()

			// 储存title，tags，content
			document.cookie = `topic_title=${topic_title};` + expires + '; path=/'
			document.cookie = `topic_tags=${topic_tags};` + expires + '; path=/'
			document.cookie = `topic_content=${topic_content};` + expires + '; path=/'
		}
	}











	// 
	// 
	//	获取时间格式为：2022-22-22 22:22
	// 
	// 
	const get_time = () => {
		// 创建一个Date对象
		const currentDate = new Date();

		// 获取年、月、日、小时、分钟
		const year = currentDate.getFullYear()
		const month = currentDate.getMonth() + 1; // 月份从0开始，需要加1
		const day = currentDate.getDate()
		const hours = currentDate.getHours()
		const minutes = currentDate.getMinutes()

		// 格式化为"YYYY-MM-DD HH:MM"的形式
		const formattedDate = year + '-' + (month < 10 ? '0' : '') + month + '-' + (day < 10 ? '0' : '') + day + ' ' + (hours < 10 ? '0' : '') + hours + ':' + (minutes < 10 ? '0' : '') + minutes;

		// 输出结果
		return formattedDate
	}










	// 
	// 
	//	获取cookie
	// 
	// 
	const get_cookie = (name) => {
		// 将cookie字符串拆分成一个名值对数组
		const kvArray = document.cookie.split(';');

		for (i=0; i < kvArray.length; i++) {

			const kv = kvArray[i].split('=');
			// 移除名称中的空格
			const cookieName = kv[0].trim();

			if (cookieName === name) {
				return decodeURIComponent(kv[1]);
			}
		}
		return null;
	}











</script>




















<!-- 引入底部模块 -->
<?php require_once dirname(__FILE__).'/footer.php'; ?>







<!-- 关闭数据库 -->
<?php mysqli_close($link); ?>









