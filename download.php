<html>
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
	<meta http-equiv="content-type" content="text/html; charset=utf-8">
	<title>下载确认</title>
	<!-- css -->
	<link rel="stylesheet" href="./css/header.css">
	<link rel="stylesheet" href="./css/style.css">
	<link rel="stylesheet" href="./css/forum.css">
</head>
<body>
	<!-- 引入顶部导航栏 -->
	<?php require_once dirname(__FILE__).'/header.php'; ?>

	<!-- 基础防御 -->
	<?php
		if (!$_GET['tid']) {
			exit("<script>alert('请不要随意更改本站的URL，谢谢配合！')</script>");
		}
	?>

	<!-- 预览图 -->
	<img class="avatar_big" style="width: 60%;" src="./data/imgs/night.jpg" loading="lazy" alt="图片加载失败"><div></div><br>

	<!-- TITLE分割 -->
	<div class="title_split_line">
		<span>&emsp;如果不愿意看的话请离开本站吧&emsp;</span>
	</div>



	<div class="board else">
		<div class="gal_info_text">
			如果你不填以下1个信息，你将使用本站提供的<b>公共普通账号</b>进行百度网盘链接的解析和下载。<br>
			普通账号下载会<b>限速</b>，请你务必理解！本站根本没钱提供VIP和SVIP账号！！<br>
			<br>
			为了<b>保护</b>每个链接和防范盗狗，本站必须对每个百度网盘链接进行<b>保护</b>，请你务必理解！！<br>
			<br>
			你以为我想要你的账号信息吗？你知道维护这个解析下载功能有多费精力吗？<br>
			百度网盘账号频繁解析和下载会被<b>封号</b>的啊！本站能提供那么多公共账号吗？不能啊！<br>
			所以得用你们自己的账号去解析和下载才<b>不会被封</b>啊！！用自己的账号有VIP还能<b>不限速</b>啊！
			
		</div>
	</div>





	<div class="board else">
		<header>
			<span>百度网盘账号数据</span>
		</header>
		<main>
			Cookie：<input type="text" id="cookie" placeholder="若不填则使用公共账号下载" value="<?php

				// 判断是否填入cookie
				if (mysql_exist("pan_account", "uid", "$uid") == 1) {
					$cookie = get_value("pan_account", "cookie", "uid=$uid");
					echo $cookie;
				}

			?>"><br><br>
			<span>如果你不知道百度网盘cookie怎么获取，请<a href="./data/html/pan_cookie/index.html" target="_blank">点我</a>。</span>
		</main>

		<button class="button2" onclick="save_bdwp_account()">保存账号信息</button>
		<button onclick="download()">开始下载</button>
	</div>


	<div class="board else">
		<header>
			<span>当前下载对象</span>
		</header>
		<main>
			<span class="box2"><?php
				// 获取URL中的tid
				$tid = $_GET['tid'];

				// 根据tid查找帖子标题
				$title = get_topic($tid, 'title');

				echo $title;
			?></span><br>
			<span>本论坛资源来自于网络仅用于个人学习交流使用，不得用于商业用途，请在下载24小时内删除。</span><br>
			<br>
			<span>当前页面将在5分钟后自动关闭</span>
		</main>
	</div>




<script>



	// 
	// 
	// 下载
	// 
	// 
	const download = (mod) => {
		window.open(`./data/html/pan/index.php?&encode=<?php echo $_GET['encode']; ?>`, '_self')
	}










	// 
	// 
	// 保存cookie
	// 
	// 
	const save_bdwp_account = () => {
		const cookie = document.querySelector("#cookie").value

		// 构建请求数据
		var data = new FormData()
		data.append("cmd", "save_bdwp_account")
		data.append("cookie", cookie)

		// 发送请求
		var xhr = new XMLHttpRequest()
		xhr.open("POST", "./server.php", true)
		xhr.send(data)

		xhr.onreadystatechange = () => {
			if(xhr.readyState == 4 && xhr.status == 200){
				if (cookie) {
					alert("保存成功，现可用自己的账号下载！")
				} else {
					alert("个人账号清除成功，现可用公共账号下载")
				}
			}
		}
	}



	// 
	// 
	// 5分钟自动关闭页面
	// 
	// 
	setTimeout(function() {
  		window.close();
	}, 5 * 60 * 1000); // 5分钟
</script>











<!-- 引入底部模块 -->
<?php require_once dirname(__FILE__).'/footer.php'; ?>







<!-- 关闭数据库 -->
<?php mysqli_close($link); ?>