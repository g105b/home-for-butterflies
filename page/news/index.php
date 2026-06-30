<?php
use Gt\DomTemplate\Binder;
use H4B\Content\NewsList;

function go(NewsList $newsList, Binder $binder):void {
	$binder->bindList($newsList);
}
