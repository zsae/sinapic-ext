<?php
include dirname(__FILE__) . '/config.php';
include dirname(__FILE__) . '/inc/saetv2.ex.class.php';

spe::init();
class spe{
	public static $cookie_expire = 604800;//3600*24*7 7days
	public static function init(){

		$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : null;
		
		switch($action){
			//check-auth
			case 'check-auth':
				self::action_check_auth();
				break;
			//upload
			case 'upload':
				self::action_upload();
				break;
			//authorize
			case 'set-auth':
				self::action_set_auth();
				break;
			case 'donate':
				self::action_donation();
				break;
			default:
				die();
		}
	}
	private static function action_donation(){
		$item_title = mb_convert_encoding('Donate for SinaPic-Ext, thank you!','GBK','UTF-8');
		$email = 'kmvan.com@gmail.com';
		$price = 99;
		?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Redirecting...</title>
	<style>
	#fm{
		display: none;
	}
	</style>
	<script>
	window.onload = function(){
		document.getElementById('fm').submit();
	}
	</script>
</head>
<body>
	<h1>Redirecting...</h1>
	<form id="fm" action="https://shenghuo.alipay.com/send/payment/fill.htm" method="post">
		<input type="hidden" name="optEmail" value="<?php echo $email;?>">
		<input type="hidden" name="payAmount" value="<?php echo (int)$price;?>">
		<input type="hidden" name="title" value="<?php echo $item_title;?>">
		<input type="submit">
	</form>
</body>
</html>
		<?php
	}
	private static function action_set_auth(){
		$token = isset($_GET['access_token']) ? $_GET['access_token'] : null;
		if(empty($token)){
			$output['status'] = 'error';
			$output['code'] = 'invaild_access_token';
			$output['msg'] = 'Sorry, access token is invaild';
			die(json_encode($output));
		}
		$expire = isset($_GET['expires_in']) ? $_GET['expires_in'] : null;
		if(empty($expire)){
			$output['status'] = 'error';
			$output['code'] = 'invaild_expires_in';
			$output['msg'] = 'Sorry, expire time is invaild';
			die(json_encode($output));
		}
		self::set_token($token,time() + $expire);
		die('
		<!doctype html>
		<html lang="en">
		<head>
			<meta charset="UTF-8">
			<title>Authorize successfully</title>
			<style>
			body{color:green;}
			</style>
		</head>
		<body>
			<h1>Congratulation! SinaPic-Ext has been authorized!</h1>
			<p><a href="javascript:window.open(false,\'_self\',false);window.close();">Close this window and reload it.</a></p>
		</body>
		</html>
		');
		
	}
	private static function action_check_auth(){
		if(!self::get_token_form_cookie()){
			$output['status'] = 'error';
			$output['code'] = 'no_auth';
			$output['msg'] = sprintf('Your are NOT authorize me yet, please click %s to athorize and %s.','<a href="' . self::get_cb_url() . '" target="_blank" class="alert-link">here</a>','<a href="javascript:location.reload();" class="alert-link">reload me</a>');
		}else{
			$output['status'] = 'success';
			$output['msg'] = 'Your has authorized me, have fun!';
		}
		die(json_encode($output));
	}
	private static function action_upload(){
		$file_name = isset($_POST['file_name']) ? $_POST['file_name'] : null;
		$file_b64 = isset($_POST['file_b64']) ? base64_decode($_POST['file_b64']) : null;
		$token = self::get_token_form_cookie();
		if(empty($token)){
			$output['status'] = 'error';
			$output['code'] = 'invaild_token';
			$output['msg'] = 'Sorry, token is invaild';
			die(json_encode($output));
		}
		if(empty($file_name) || empty($file_b64)){
			$output['status'] = 'error';
			$output['code'] = 'invaild_file';
			$output['msg'] = 'Sorry, file is invaild';
			die(json_encode($output));
		}

		$allow_types = array(
			'jpg',
			'png',
			'gif'
		);


		@set_time_limit(0);
		$file_ext = explode('.',$file_name);
		$file_ext = $file_ext[count($file_ext) - 1];

		if(!in_array($file_ext,$allow_types)){
			$output['status'] = 'error';
			$output['code'] = 'invaild_type';
			$output['msg'] = 'Sorry, file type is invaild';
			die(json_encode($output));
		}
		
		$file_name = self::get_client_ip() . '-' . date('YmdHis') . rand(100,999) . '.' . $file_ext;
		/** 
		 * sea
		 */
		if(defined('SAE_TMP_PATH')){
			$upload_dir = SAE_TMP_PATH . '/';
		}else{
			$upload_dir = dirname(__FILE__) . '/uploads/';
		}
		
		$file_path = $upload_url . $file_name;
		
		file_put_contents($file_path,$file_b64);

		$c = new SaeTClientV2(AKEY,SKEY,$token);
		$callback = $c->upload(date('Y-m-d H:i:s ' . rand(100,999)) ,$file_path);
		//unlink($upload_dir . $file_name);

		/** 
		 * get callback
		 */
		if(is_array($callback) && isset($callback['bmiddle_pic'])){
			$output['status'] = 'success';
			$output['url'] = $callback['bmiddle_pic'];
			/** 
			 * destroy after upload 
			 */
			//if(isset($options['destroy_after_upload'])){
				$c->destroy($callback['id']);
			//}
			die(json_encode($output));
		/** 
		 * too fast
		 */
		}else if(is_array($callback) && isset($callback['error_code'])){
			$output['status'] = 'error';
			$output['code'] = 'unknow_error';
			$output['msg'] = $callback['error'];
			die(json_encode($output));
		/** 
		 * unknown error
		 */
		}else{
			$output['status'] = 'error';
			$output['code'] = 'unknow_error';
			$output['msg'] = sprintf('Sorry, upload failed. Please try again later or contact the plugin author. Weibo returns an error message: %s',json_encode($callback));
			die(json_encode($output));
		}
	}
	private static function get_cb_url(){
		$uri = HOME_URL . '/action.php?action=set-auth';
		return 'http://api.inn-studio.com/sinapicv2/?action=get_authorize&amp;uri=' . urlencode($uri);
	}
	private static function set_token($token,$expire = 604800){
		setcookie('spe-token',base64_encode($token),$expire);
	}
	private static function get_token_form_cookie(){
		return isset($_COOKIE['spe-token']) ? base64_decode($_COOKIE['spe-token']) : null;
	}
	private function get_client_ip(){
		return preg_replace( '/[^0-9a-fA-F:., ]/', '',$_SERVER['REMOTE_ADDR'] );
	}
}
