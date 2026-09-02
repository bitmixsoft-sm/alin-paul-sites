<?php
	session_start();

	if(!isset($_SESSION['lang'])){
		if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE']))
		{
			$lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
		}
		else
		{
			$lang = 'en';	
		}
	    $acceptLang = ['fr', 'it', 'en', 'ro', 'es', 'de']; 
	    $lang = in_array($lang, $acceptLang) ? $lang : 'en';
		$_SESSION['lang'] = $lang;
	}
	//file_put_contents("/home/modelesdesites/backend/dating-platform/resources/lang/".$_SESSION['lang']."/lang.json", json_encode($trans));
	function l($str, $lang = null){
		if($lang == null){
			$lang = $_SESSION['lang'];
		}
		$path = dirname(getcwd());
		$lg_path = $path."/backend/dating-platform/resources/lang/".$lang."/lang.json";
		$trans = json_decode(file_get_contents($lg_path), true);
		if(!is_array($trans)){
			$trans[$str] = '';
			file_put_contents($path."/backend/dating-platform/resources/lang/".$lang."/lang.json", json_encode($trans));
			return($str);
		}elseif(!array_key_exists($str, $trans)){
			$trans[$str] = '';
			file_put_contents($path."/backend/dating-platform/resources/lang/".$lang."/lang.json", json_encode($trans));
			return($str);
		}else{
			if($trans[$str] != ''){
				return($trans[$str]);
			}else{
				return($str);
			}
		}
	}