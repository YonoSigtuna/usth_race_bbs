


	// 
	// 
	//	回复功能
	// 
	// 
	const reply = (tid, rid='') => {
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
			xhr.open("POST", '../server.php', true)
			xhr.send(data)

			// xhr处理
			xhr.onreadystatechange = () => {
				if(xhr.readyState == 4 && xhr.status == 200){
					try {
						alert('回复成功，请自行按F5刷新网页，同时你的回复内容已进入人工二审。')
					} catch (error) {
						console.error('xhr请求失败，失败code：' + error)
					}
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
			data.append("tid", "<?php echo $_GET['tid']; ?>")
			data.append("content", content)
			console.log("<?php echo $_GET['tid']; ?>");

			// 发送xhr
			const xhr = new XMLHttpRequest()
			xhr.open("POST", '../server.php', true)
			xhr.send(data)

			// xhr处理
			xhr.onreadystatechange = () => {
				if(xhr.readyState == 4 && xhr.status == 200){
					try {
						alert('回复成功，请自行按F5刷新网页，同时你的回复内容已进入人工二审。')
					} catch (error) {
						console.error('xhr请求失败，失败code：' + error)
					}
				}
			}

			
			// // xhr请求超时
			// xhr.timeout = 8000	// ms
			// xhr.ontimeout = () => alert("请求超时")
		}
	}





