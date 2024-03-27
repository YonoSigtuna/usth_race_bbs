<html>
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
	<meta http-equiv="content-type" content="text/html; charset=utf-8">
	<title>账号管理</title>
	<!-- css -->
	<link rel="stylesheet" href="./css/header.css">
	<link rel="stylesheet" href="./css/style.css">
</head>
<body>
	<!-- 引入顶部导航栏 -->
	<?php require_once dirname(__FILE__).'/header.php'; ?>

	<br><br>
	<div class="forum">
		<main class="board">
			<input type="text" id="ban_reason" placeholder="封禁理由">
			<button class="button2" onclick="ban()">风纪执行</button>
		</main>
	</div>
	


<script>

	// 
	// 
	// 封禁用户
	// 
	// 
	const ban = () => {
		// 获取DOM信息
		const uid = "<?php echo $_GET['uid']; ?>"
		const ban_reason = document.querySelector("#ban_reason").value


		// 通过xhr提交登录信息
		var data = new FormData()
		data.append("cmd", "ban")
		data.append("uid", uid)
		data.append("ban_reason", ban_reason)

		// 发送xhr
		const xhr = new XMLHttpRequest()
		xhr.open("POST", "./server.php", true)
		xhr.send(data)

		// xhr处理
		xhr.onreadystatechange = () => {
			if(xhr.readyState == 4 && xhr.status == 200){
				var data = xhr.responseText
				alert(data)
				
			}
		}

		// xhr请求超时
		xhr.timeout = 10000	// ms
		xhr.ontimeout = () => alert("请求超时")
	}


</script>