<html>
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
	<meta http-equiv="content-type" content="text/html; charset=utf-8">
	<title>注册 / 登录</title>
	<!-- css -->
	<link rel="stylesheet" href="./css/header.css">
	<link rel="stylesheet" href="./css/style.css">
	<link rel="stylesheet" href="./css/register.css">

	<style>
		/* 清除内外边距 */
		* {
			margin: 0;
			padding: 0;
		}
	</style>
</head>
<body>
	<!-- 引入顶部导航栏 -->
	<?php require_once dirname(__FILE__).'/header.php'; ?>





	<!-- TITLE分割 -->
	<div class="title_split_line" style="width: 35%;">
		<span>注册 / 登录</span>
	</div>



	<!-- 注册框 -->
	<div class="board reg_login">
		<header>
			<span>注册</span>
		</header>
		<main>
			<input type="text" id="reg_uname" placeholder="用户名">
			<input type="password" id="reg_psw" placeholder="密码">
			<input type="text" id="reg_email" placeholder="邮箱"><br>
		</main>
		<button onclick="register()">提交注册信息</button>
		
	</div>




	<!-- 登录框 -->
	<div class="board reg_login">
		<header>
			<span>登录</span>
		</header>
		<main>
			<input type="text" id="login_uname" placeholder="用户名">
			<input type="password" id="login_psw" placeholder="密码">
			<button onclick="login()">提交登录信息</button>
		</main>
	</div>



</body>




<!-- MD5 -->
<script src="https://cdn.bootcdn.net/ajax/libs/blueimp-md5/2.18.0/js/md5.js"></script>

<script>


	// 登录提交
	const login = () => {
		// 获取登录信息
		const uname = document.querySelector('#login_uname').value
		const psw = md5(document.querySelector('#login_psw').value)

		// 通过xhr提交登录信息
		var data = new FormData()
		data.append("cmd", "login")
		data.append("uname", uname)
		data.append("psw", psw)

		// 发送xhr
		const xhr = new XMLHttpRequest()
		xhr.open("POST", "./server.php", true)
		xhr.send(data)

		// xhr处理
		xhr.onreadystatechange = () => {
			if(xhr.readyState == 4 && xhr.status == 200){
				var data = xhr.responseText

				if (data == "登录成功") {				
					// 跳转到管理页面
					window.location.href = `./user_admin.php`
				} else {
					alert(data);
				}
			}
		}

		// xhr请求超时
		xhr.timeout = 10000	// ms
		xhr.ontimeout = () => alert("请求超时")
	}





	// 注册提交
	const register = () => {
		// 注册信息提取
		const uname = document.querySelector('#reg_uname').value
		const psw = md5(document.querySelector('#reg_psw').value)
		const email = document.querySelector('#reg_email').value


		// 判断是否所有信息都填入
		if (uname && psw && email) {

			// 构建xhr参数
			var data = new FormData()
			data.append("cmd", "register")
			data.append("uname", uname)
			data.append("psw", psw)
			data.append("email", email)

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



		// 没有提交完整注册信息
		} else {
			alert("请填写用户名、密码和QQ号")
		}
	}



</script>








<!-- 引入底部模块 -->
<?php require_once dirname(__FILE__).'/footer.php'; ?>







<!-- 关闭数据库 -->
<?php mysqli_close($link); ?>