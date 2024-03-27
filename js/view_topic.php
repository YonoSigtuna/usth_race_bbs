






<script>


	
	// 
	//	回复功能
	// 
	// 
	const reply_topic = (rid='') => {
		// rid不存在，新回复
		if (!rid) {
			var content = document.querySelector('.bottom .reply_content').value

			// 构建cmd请求
			const data = new FormData()
			data.append("cmd", "reply_topic")
			data.append("tid", "<? echo $_GET['tid']; ?>")
			data.append("content", content)

			// 发送xhr
			const xhr = new XMLHttpRequest()
			xhr.open("POST", './server.php', true)
			xhr.send(data)

			// xhr处理
			xhr.onreadystatechange = () => {
				if(xhr.readyState == 4 && xhr.status == 200){
					location.reload()
				}
			}

			// xhr请求超时
			xhr.timeout = 8000	// ms
			xhr.ontimeout = () => alert("请求超时")

		// rid存在，追加回复
		} else {
			// 获取追加内容
			var content = document.querySelector(`#rid_${rid}`).value

			// 构建cmd请求
			const data = new FormData()
			data.append("cmd", "reply_reply")
			data.append("rid", rid)
			data.append("tid", "<? echo $_GET['tid']; ?>")
			data.append("content", content)

			// 发送xhr
			const xhr = new XMLHttpRequest()
			xhr.open("POST", './server.php', true)
			xhr.send(data)

			// xhr处理
			xhr.onreadystatechange = () => {
				if(xhr.readyState == 4 && xhr.status == 200){
					location.reload()
				}
			}

			
			// xhr请求超时
			xhr.timeout = 8000	// ms
			xhr.ontimeout = () => alert("请求超时")
		}
	}






	// 
	// 
	// 删除评论
	// 
	// 
	const remove_reply = (rid) => {
		// 获取tid
		var tid = "<?php echo $_GET['tid']; ?>"

		// 构造请求数据
		var data = new FormData()
		data.append("cmd", "remove_reply")
		data.append("tid", tid)
		data.append("rid", rid)

		// 发送xhr
		var xhr = new XMLHttpRequest()
		xhr.open("POST", "./server.php", true)
		xhr.send(data)

		// xhr处理
		xhr.onreadystatechange = () => {
			if(xhr.readyState == 4 && xhr.status == 200){
				var result = xhr.responseText

				// 成功删除
				if (result == 'succ') {

					// 返回当前帖子页面page=0
					window.open(`./view_topic.php?tid=${tid}&page=0`)
				}

				// 没权限
				if (result == 'refuse') {
					alert("你没权限删除此贴")
				}
			}
		}
	}


	// 
	// 
	// 删除帖子
	// 
	// 
	const remove_topic = () => {
		// 构造请求数据
		var data = new FormData()
		data.append("cmd", "remove_topic")
		data.append("tid", <?php echo $_GET['tid']; ?>)

		// 发送xhr
		var xhr = new XMLHttpRequest()
		xhr.open("POST", "./server.php", true)
		xhr.send(data)

		// xhr处理
		xhr.onreadystatechange = () => {
			if(xhr.readyState == 4 && xhr.status == 200){
				var result = xhr.responseText

				// 成功删除
				if (result == 'succ') {

					// 返回资源收入版块
					if (fid == '1-1' || fid == '1-2' || fid == '1-3' || fid == '1-4') {
						window.open(`./forum_card.php?fid=${fid}&page=0`)
					// 返回其他版块
					} else {
						window.open(`./forum_list.php?fid=${fid}&page=0`)

					}
				}

				// 没权限
				if (result == 'refuse') {
					alert("你没权限删除此贴")
				}
			}
		}
	}




</script>




