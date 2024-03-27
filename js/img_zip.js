
// 图片压缩
function img_zip(file) {
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
				resolve(blob); // 将Blob对象通过resolve()传递回来
			}, 'image/jpeg', 1);
			};
		};
		
		reader.onerror = reject;
		reader.readAsDataURL(file);
	});
}
	
	
	
	// // 压缩成大图片
	// function img_zip(file) {
	// 	const reader = new FileReader();

	// 	reader.onload = function(e) {
	// 	const img = new Image();
	// 	img.src = e.target.result;

	// 	img.onload = function() {
	// 		const canvas = document.createElement('canvas');
	// 		const ctx = canvas.getContext('2d');

	// 		// 设置压缩后的宽高（取一边做压缩判定）
	// 		const maxWidth = 1920;
	// 		const maxHeight = 2048;

	// 		let width = img.width;
	// 		let height = img.height;

	// 		// 如果图片尺寸超过最大限制，则等比例缩放
	// 		if (width > maxWidth || height > maxHeight) {
	// 			const ratio = Math.min(maxWidth / width, maxHeight / height);
	// 			width *= ratio;
	// 			height *= ratio;
	// 		}

	// 		// 设置canvas尺寸
	// 		canvas.width = width;
	// 		canvas.height = height;

	// 		// 绘制图片到canvas
	// 		ctx.drawImage(img, 0, 0, width, height);

	// 		// 转为blob返回
	// 		canvas.toBlob(function(blob) {
	// 			resolve(blob)
	// 		}, 'image/jpg', 0.75) // 0.75是jpg压缩质量
	// 	}
	// 	}
	// 	reader.readAsDataURL(file)
	// }