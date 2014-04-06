<?php


namespace Upnp;

class Model_Upnp extends \Model
{
	private $upnp;
	public function __construct()
	{
		require_once(APPPATH.'modules/upnp/vendor/phpupnp.class.php');
		$this->upnp = new \phpUPnP();


	}

	public function explore()
	{
		$found = array();
		$found = $this->upnp->mSearch();
		//$found = $this->upnp->mSearch('urn:dial-multiscreen-org:service:dial:1');
		var_dump($found);

		$curl = \Request::forge('http://192.168.1.104:8060/dial_SCPD.xml','curl');
		$curl->set_method('get');
		$result = $curl->execute();
		var_dump($result->response());


		return $found;
	}
	
	public function discover($type = 'screens')
	{
		$found = $this->upnp->mSearch();
		$results = array();
		switch($type)
		{
			case 'screens':
				//find all renderers and dial screens
				foreach($found as $f)
				{
					//var_dump($f);
					if($f['st'] == 'urn:dial-multiscreen-org:service:dial:1')
					{
						$results[$f['usn']] = array(
							'type'=>'dial',
							'description_url'=>$f['location'],
						);
					}
					if($f['st'] == 'urn:schemas-upnp-org:service:AVTransport:1')
					{
						$results[$f['usn']] = array(
							'type'=>'upnp',
							'description_url'=>$f['location'],
						);
					}
				}
				//var_dump($results);
				//die();
				foreach($results as $usn=>$screen)
				{
					$description = $this->get_description($screen['description_url']);
					if(!$description)
					{
						//found it but no description?
						continue;
					}
					if($description['device']['manufacturer'] == 'Google Inc.')
					{
						$results[$usn]['type'] = 'chromecast';
					}
					if($description['device']['manufacturer'] == 'Roku')
					{
						$results[$usn]['type'] = 'roku';
					}
					$results[$usn] = array_merge($description,$results[$usn]);
				}
				break;
			default:
				//return nothing?
				break;
		}
		return $results;
	}

	public function get_description($url)
	{
		$request = \Request::forge($url,'curl');
		$request->set_mime_type('php');
		try{
			$results = $request->execute();
		}
		catch(\RequestException $e)
		{
			//url not working, attempt autofix?
			return false;
		}
		$response = \Format::forge($results->response(),'xml:ns')->to_array();
		if(isset($response['body']))
		{
			if(is_string($response['body']))
			{
				return \Format::forge($response['body'],'xml:ns')->to_array();
			}
			return $response['body'];
		}
		else
		{	
			return $response;
		}
	}

}

