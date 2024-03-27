

<script>
	// 
	// 
	// 格式化帖子内容
	// 
	// 
	const format_content = (content) => {
		// 如果包含了'
		if (content.includes("'")) {
			var content = content.replace("'", "&apos;")
		}
		return content
	}






	// 
	// 
	// 发帖
	// 
	// 
	const send_topic = () => {
		var title = document.querySelector('#topic_title').value
		var content = format_content(document.querySelector('#topic_content').value)
		console.log(content);
		var date = get_time()
		var tags = document.querySelector('#tags').value
		var cover = document.querySelector('#cover').value
		var fid = document.querySelector('#target_fid').value
		var cid = "<?php echo $cid; ?>"

		// 构建xhr请求
		var data = new FormData()
		data.append("cmd", "send_topic")
		data.append("title", title)
		data.append("content", content)
		data.append("date", date)
		data.append("tags", tags)
		data.append("cover", cover)
		data.append("fid", fid)
		data.append("cid", cid)

		// 发送xhr
		var xhr = new XMLHttpRequest()
		xhr.open("POST", './server.php', true)
		xhr.send(data)

		// xhr处理
		xhr.onreadystatechange = () => {
			if(xhr.readyState == 4 && xhr.status == 200){
				
				// 修改帖子内容，回到帖子
				if (cid + 0 > -9000) {
					window.location.href = `view_topic.php?tid=${cid}&page=0`

				// 第一次发帖
				} else {
					const newest_tid = `
						<?php
							// 获取最新tid
							echo get_value("sys_auto_increment_value", "value", "variable='tid'");
						?>
					`
					window.location.href = `view_topic.php?tid=${newest_tid}&page=0`
				}
			}
		}

		// xhr请求超时
		xhr.timeout = 60000	// ms
		xhr.ontimeout = () => alert("请求超时")
	}
</script>