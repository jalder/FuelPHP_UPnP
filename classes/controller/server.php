<?php

namespace Upnp;

class Controller_Server extends \Controller
{

	public function action_index()
	{
		$s = new Model_Mediaserver();
		$servers = $s->search();
		
		$view = \View::forge('servers');
		$view->servers = $servers;
		return $view;

	}

	public function action_search()
	{
		$s = new Model_Mediaserver();
		$servers = $s->search();
		return \Format::forge($servers)->to_json();
	}

	public function action_browse()
	{
		$output = array();
		$s = new Model_Mediaserver();
		if(\Input::get('location'))
		{
			$location = \Input::get('location');
			$parent = \Input::get('directory');
		}
		else if(\Input::get('description'))
		{
			$info = $s->parseDescription(\Input::get('description'));
			//var_dump($info);
			if($i = $info['device']['serviceList']['service']){
				foreach($i as $service){
					if($service['serviceType']=='urn:schemas-upnp-org:service:ContentDirectory:1'){
						$location = dirname(\Input::get('description')).$service['controlURL'];
					}
				}
			}
			//$location = ''; //get from description
			$parent = '0';
		}
		if($location){
			$contents = $s->browse($location,$parent);
			$meta = $s->browse($location,$parent,'BrowseMetadata');
			if(isset($meta['container']))
			{
				$contents['parent'] = $meta['container']['@attributes']['parentID'];
			}
			else
			{
				$contents['parent'] = $meta['item']['@attributes']['parentID'];
				//var_dump($meta);
				//$contents['parent'] = '';
			}

			if(isset($contents['container']))
			{
				foreach($contents['container'] as $container)
				{
//					echo '<a href="/upnp/server/browse?location='.urlencode($location).'&directory='.urlencode($container['@attributes']['id']).'">link</a>';
				}
			}
			//if(strpos($contents['@attributes'],'special://')===0)
			//{
			//	$contents['@attributes']['id'] = urlencode($contents['@attributes']['id']);
			//}
			$output[urlencode($location)] = $contents;
			return \Format::forge($output)->to_json();
		}
//consider die() here? the rest is redundant and slow now that upnp search and storage is in place
		$servers = $s->search();
		foreach($servers as $location=>$server)
		{
			//var_dump($server);
			foreach($server['device']['serviceList']['service'] as $service)
			{
				if($service['serviceType']=='urn:schemas-upnp-org:service:ContentDirectory:1')
				{
					$contentdirectory = $service['controlURL'];
					$cdlocation = dirname($location).$contentdirectory;
							
					$contents = $s->browse($cdlocation,'0');
					if(isset($contents['container']))
					{
						foreach($contents['container'] as $container)
						{
//						echo '<a href="/upnp/server/browse?location='.urlencode($cdlocation).'&directory='.urlencode($container['@attributes']['id']).'">link</a> ';
						}

					//var_dump($contents);
						$output[urlencode($cdlocation)] = $contents;
					}
					//return \Format::forge($contents)->to_json();
					//$decoded = \Format::forge($contents,'xml')->to_array();
					//var_dump($decoded);
				}
			}
		}
		return \Format::forge($output)->to_json();
	}

	public function action_tree()
	{
		$view = \View::forge('tree');
		return $view;
	}

	public function action_explorer()
	{
		$view = \View::forge('explorer');
		return $view;
	}
}
