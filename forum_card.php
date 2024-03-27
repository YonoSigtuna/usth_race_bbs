<html>
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
	<meta http-equiv="content-type" content="text/html; charset=utf-8">
	<!-- css -->
	<link rel="stylesheet" href="./css/header.css">
	<link rel="stylesheet" href="./css/style.css">
	<link rel="stylesheet" href="./css/home_page.css">
	<link rel="stylesheet" href="./css/forum.css">

</head>
<body>
	<!-- 引入顶部导航栏 -->
	<?php require_once dirname(__FILE__).'/header.php'; ?>




	<!-- TITLE分割 -->
	<div class="title_split_line" style="width: 35%;">
		<span>
			<?php
				// 从URL中获取fid
				$fid = $_GET['fid'];

				// 从fid中获取版块名字
				$board_name = get_board_name($fid);

				// 版块不存在
				if (!$board_name) {
					exit('当前fid不存在，请不要随意更改URL，若为管理员操作请检查functions.php中的get_board_name()');
				} else {
					echo $board_name;
				}
			?>
		</span>
	</div>





	<!-- 帖子区域 -->
	<div class="forum">
		<header>
			<a href="./send_topic.php?mod=add&fid=<?php echo $fid; ?>"><button class="post_topic">发帖</button></a>
		</header>
		<main class="board">
		</main>
	</div>






	<!-- 未来开发瀑布流请求需要的DOM -->
	<div class="forums_loading">
		<button class="last_page">上一页</button>
		<span> 当前页数：<?php echo $_GET['page']; ?> </span>
		<button class="next_page" style="margin-left: 5px;">下一页</button>
		
		<!-- 自定义行数跳转 -->
		<br><br>
		<input type="text" id="page_number" placeholder="请输入你需要跳转的页数">
		<button onclick="goto_page()">跳转</button>
	</div>

	<!-- 底部顶行 -->
	<div>
		<br>
	</div>




</body>




<script>
	// 
	// 
	//	网页title变更
	// 
	// 
	var title = document.querySelector(".title_split_line span").textContent
	document.title = title



	// 
	// 
	// 上一页
	// 
	// 
	var last_page_button = document.querySelector('.forums_loading .last_page')
	last_page_button.addEventListener('click', function() {
		// 获取fid和page - 1
		var fid = "<?php echo $_GET['fid']; ?>"
		var page = <?php echo $_GET['page']; ?> - 1

		if (page < 0) {
			alert('无上一页')
		} else {

			// 从URL进行跳转
			window.location.href = `./forum_card.php?fid=${fid}&page=${page}`
		}
	})




	// 
	// 
	// 下一页
	// 
	// 
	var next_page_button = document.querySelector('.forums_loading .next_page')
	next_page_button.addEventListener('click', function() {
		// 获取fid和page + 1
		var fid = "<?php echo $_GET['fid']; ?>"
		var page = <?php echo $_GET['page']; ?> + 1


		// 从URL进行跳转
		window.location.href = `./forum_card.php?fid=${fid}&page=${page}`
	})



	// 
	// 
	// 指定页数跳转
	// 
	// 
	const goto_page = () => {
		// 获取需要跳转的fid和page
		var fid = "<?php echo $_GET['fid']; ?>"
		var page = document.querySelector('#page_number').value

		// 未填入需要跳转的page
		if (!page) {
			alert('请输入你要跳转的网页')

		// 执行跳转
		} else {

			// 从URL进行跳转
			window.location.href = `./forum_card.php?fid=${fid}&page=${page}`
		}
	}






	// 
	// 
	// 整合帖子数据至DOM元素
	// 
	// 
	const add_topic = (forums) => {
		console.log(forums);
		// 获取帖子需要添加至的DOM
		var forums_region = document.querySelector('.forum main')

		// 无帖子数据
		if (!forums) {
			alert('当前页数不存在帖子数据')
			return
		}

		// 循环每个帖子
		for (n = 0; n < forums.length; n++) {

			// 获取当前时间
			var current_time = new Date()

			// 获取给定时间字符串的时间
			var send_time = new Date(forums[n]['date'])

			// 计算时间差（单位为毫秒）
			var time_diff = send_time.getTime() - current_time.getTime()

			// 将时间差转换为天数
			var time_diff = Math.ceil(time_diff / (1000 * 60 * 60 * 24))

			// 取绝对值
			var time_diff = Math.abs(time_diff)

			// 判断帖子封面是否存在
			if (forums[n]['preview']) {
				var path = `./data/forums/${forums[n]['tid']}/preview.jpg`
			} else {
				var path = "./data/imgs/yingmei_small.jpg"
			}

			// 帖子卡片
			var html = `
				<div class="post_card">
					<div class="cover">
						<img src="${path}" title="${forums[n]['title']}" onclick="window.open('./view_topic.php?tid=${forums[n]['tid']}&page=0')" loading="lazy" alt="图片加载失败">
					</div>
					<a class="topic" href="./view_topic.php?tid=${forums[n]['tid']}&page=0" target="_blank" title="${forums[n]['title']}">${forums[n]['title']}</a>
					<div class="info">
						<img class="avatar" src="./data/avatars/1_small.jpg" loading="lazy" alt="图片加载失败">
						${forums[n]['auther']}<span class="date">${time_diff}天前</span>
					</div>
				</div>
			`
			forums_region.insertAdjacentHTML("beforeend", html)
		}
	}






	// 
	// 
	// 请求帖子
	// 
	// 
	// 构建xhr请求
	var data = new FormData()
	data.append("cmd", "request_topics")
	data.append("fid", "<?php echo $_GET['fid']; ?>");
	data.append("page", "<?php echo $_GET['page']; ?>");

	// 发送xhr
	var xhr = new XMLHttpRequest()
	xhr.open("POST", './server.php', true)
	xhr.send(data)

	// xhr处理
	xhr.onreadystatechange = () => {
		if(xhr.readyState == 4 && xhr.status == 200){
			try {
				// 获取xhr返回值
				var return_data = xhr.responseText
				console.log(return_data);

				// 解析返回值为JSON格式
				var return_data = JSON.parse(return_data)

				// 添加列表式帖子卡片
				add_topic(return_data)
			} catch (error) {
				console.error('xhr请求失败，失败code：' + error)
			}
		}
	}
	
	// xhr请求超时
	xhr.timeout = 9000	// ms
	xhr.ontimeout = () => alert("请求超时")
</script>

































<!-- 引入底部模块 -->
<?php require_once dirname(__FILE__).'/footer.php'; ?>







<!-- 关闭数据库 -->
<?php mysqli_close($link); ?>