<?php
use Gt\DomTemplate\Binder;
use Gt\Input\Input;
use H4B\Content\NewsFactory;
use H4B\Content\NewsList;

function go(NewsList $newsList, Binder $binder):void {
	$binder->bindList($newsList);
}

function do_delete(Input $input):void {
	$id = $input->getString("dateString");
	$factory = new NewsFactory();
	$factory->delete($id);
	header("Location: /news/editor");
	exit;
}
