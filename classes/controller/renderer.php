<?php

namespace Upnp;

class Controller_Renderer extends \Controller
{
	public function action_index()
	{
		$rockchip = new Model_Rockchip();
		$rockchip->play(\Input::post('url'));
		$view = \View::forge('forms/render');
		return $view;
	}

	public function action_search()
	{
		$renderer = new Model_Renderer();
		$results = $renderer->search();
	//	var_dump($results);
		return \Format::forge($results)->to_json();
	}
}
