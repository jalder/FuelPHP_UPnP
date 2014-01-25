<?php


namespace Upnp;

class Model_Renderer extends \Model
{
	public function search()
	{
		require_once(APPPATH.'modules/upnp/vendor/phpupnp.class.php');	
		$upnp = new \phpUPnP();
		$found = array();
		$found = $upnp->mSearch('urn:schemas-upnp-org:service:AVTransport:1');
		$results = array();
		foreach($found as $renderer)
		{
			$curl = \Request::forge($renderer['location'],'curl');
			$data = $curl->execute()->response();
			$description = \Format::forge($data->body,'xml:ns')->to_array();
			$results[$renderer['location']] = $description;
		}
		return $results;
	}


	//requires an AVTransport $ctrlurl and a file $url
	public function play($ctrlurl,$url)
	{
		require_once(APPPATH.'/modules/upnp/vendor/phpupnp.class.php');
		$upnp = new \phpUPnP();
		$args = array(
			'InstanceID'=>111,
			'CurrentURI'=>$url,
			'CurrentURIMetaData'=>'testmetadata'
		);
		$response = $upnp->sendRequestToDevice('SetAVTransportURI',$args,$ctrlurl,$type = 'AVTransport');
		return $response;
	}

}
