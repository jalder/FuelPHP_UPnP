<?php

namespace Upnp;

class Model_Mediaserver extends \Model
{
	public function search()
	{
		require_once(APPPATH.'modules/upnp/vendor/phpupnp.class.php');
		$upnp = new \phpUPnP();
		$found = array();
		$found = $upnp->mSearch('urn:schemas-upnp-org:device:MediaServer:1');
		//var_dump($found);
		$results = array();
		foreach($found as $server)
		{
			$curl = \Request::forge($server['location'],'curl');
			//var_dump($server);
			//$data = $curl->execute();
			$curl->set_options(array(
				CURLOPT_TIMEOUT => 5,
				CURLOPT_FOLLOWLOCATION => true,
			));
			try{
				$data = $curl->execute()->response();
			} catch (\HttpNotFoundException $e)
			{
				//catch the 404
				//die('caught');
				continue;
			}
			//var_dump($data);
			$description = \Format::forge($data->body,'xml:ns')->to_array();
			$results[$server['location']] = $description;
		}
		return $results;
	}

	public function browse($location,$base = '0', $browseflag = 'BrowseDirectChildren')
	{
		libxml_use_internal_errors(true);
		require_once(APPPATH.'modules/upnp/vendor/phpupnp.class.php');
		$upnp = new \phpUPnP();
		$args = array(
			'ObjectID'=>$base,
			'BrowseFlag'=>$browseflag,
			'Filter'=>'',
			'StartingIndex'=>0,
			'RequestedCount'=>0,
			'SortCriteria'=>''
		);
		$response = $upnp->sendRequestToDevice('Browse',$args,$location,$type = 'ContentDirectory');
		//var_dump($response);
		/**
		$result = explode('<Result>',$response);
		if(count($result)<2)
		{
			return false;
		}

		$data = explode('</Result>',$result[1]);
		**/
		$response = \Format::forge($response,'xml:ns')->to_array();
//			var_dump($response['s:Body']); die();
		//var_dump(html_entity_decode($data[0]));
		//var_dump($response);
		if(isset($response['body']['h1']))
		{
			//echo 'Location: '.$location.' Says: '.$response['body']['h1'];
			return false;
		}
		$returned = $response['s:Body']['u:BrowseResponse']['NumberReturned'];
		$total = $response['s:Body']['u:BrowseResponse']['TotalMatches'];
		$data = \Format::forge($response['s:Body']['u:BrowseResponse']['Result'],'xml:ns')->to_array();
		//var_dump($data);
		return $data;
	}
	
	public function parseDescription($url)
	{
		try{
			$curl = \Request::forge($url,'curl');
			$data = $curl->execute()->response();
			$description = \Format::forge($data->body,'xml:ns')->to_array();
		}
		catch(\RequestException $e)
		{
			return '';
		}
		return $description;
	}

	public function getControlURL($description_url)
	{
		$baseurl = $this->getBaseURL($description_url);
		$description = $this->parseDescription($description_url);
		foreach($description['device']['serviceList']['service'] as $s)
		{
			if($s['serviceId'] == 'urn:upnp-org:serviceId:ContentDirectory')
			{
				return $baseurl.$s['controlURL'];
			}
		}
		return $description;
	}

	public function getBaseURL($url)
	{
		$parts = parse_url($url);
//		var_dump($parts);
		return $parts['scheme'].'://'.$parts['host'].':'.$parts['port'];
	}

}
