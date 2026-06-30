<?php
namespace H4B;

use Gt\WebEngine\Middleware\DefaultServiceLoader;
use H4B\Content\ContentRepository;
use H4B\Content\NewsList;

class ServiceLoader extends DefaultServiceLoader {
	public function getContentRepository():ContentRepository {
		return new ContentRepository();
	}

	public function getNewsList():NewsList {
		return new NewsList();
	}
}
