<?php
class UpdateApp extends MobileApp{
	function update(){
		$doc = new DOMDocument();
		if($this->reqdata->appid==1){	
			$r = $doc->load(APP_ROOT."/mobile/android.xml");
		}else{
			$r = $doc->load(APP_ROOT."/mobile/ios.xml");
		}
		
		$info = $doc->getElementsByTagName( "root" );
		$v = $u = '';
		foreach ($info as $item){
			$version = $item->getElementsByTagName('version');
			$v = $version->item(0)->nodeValue;
			$url = $item->getElementsByTagName('url');
			$u = $url->item(0)->nodeValue;
		}
		$this->success(array('version'=>$v,'url'=>$u));
	}
}