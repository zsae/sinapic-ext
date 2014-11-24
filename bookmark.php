<?php include dirname(__FILE__) . '/config.php';?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>SinaPic-Ext bookmark page</title>
</head>
<body>
	<h1>
	Add this link to your bookmark: <a href="javascript:(function(D,js,css,js_url,css_url,id){
		if(D.getElementById(id + '-js')) return;
		js = D.createElement('script');
		js.setAttribute('id',id + '-js');
		js.setAttribute('src',js_url);
		js.setAttribute('data-home-url','<?php echo HOME_URL;?>');
		D.getElementsByTagName('head')[0].appendChild(js);
		
		css = D.createElement('link');
		css.rel = 'stylesheet';
		css.setAttribute('id',id + '-css');
		css.href = css_url;
		D.getElementsByTagName('head')[0].appendChild(css);
		
	})(document,'#js','#css','<?php echo HOME_URL;?>/static/js/bookmark.js?v=<?php echo VERSION;?>','<?php echo HOME_URL;?>/static/css/bookmark.css?v=<?php echo VERSION;?>','spe');">Sinapic-Ext v<?php echo VERSION;?></a>
	</h1>
	<h2>Just click the link from your bookmark when you upload images.</h2>
	<h3><a href="http://ww3.sinaimg.cn/large/686ee05djw1eihtkzlg6mj216y16ydll.jpg" target="_blank" title="Donate by Alipay">Donate me by Alipay (QR code)</a></h3>
	<h4>By <a href="http://inn-studio.com/" target="_blank" >INN STUDIO</a></h4>
</body>
</html>