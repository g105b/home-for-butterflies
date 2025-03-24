<?php
namespace H4B\Content;

use DateTime;
use Gt\DomTemplate\BindGetter;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\Attributes\AttributesExtension;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\MarkdownConverter;

class NewsItem {
	public function __construct(
		private \DateTime $publishDate,
		private string $markdown
	) {}

	#[BindGetter]
	public function getDateString():string {
		return $this->publishDate->format("Y-m-d");
	}

	#[BindGetter]
	public function getFriendlyDateString():string {
		return $this->publishDate->format("jS M Y");
	}

	#[BindGetter]
	public function getSourceContent():string {
		return $this->markdown;
	}

	#[BindGetter]
	public function getContent():string {
		return $this->getHTML();
	}

	#[BindGetter]
	public function getPreview():string {
		$content = $this->getContent();
		preg_match("/<p>(.+)<\/p>/", $content, $matches);
		$firstParagraphText = $matches[1];
		return mb_strimwidth($firstParagraphText, 0, 150, "...");
	}

	#[BindGetter]
	public function getEditable():bool {
		return $this->publishDate > new DateTime("2021-11-08");
	}

	/** @return array<string> URI paths of images */
	public function getImages():array {
		if(!$this->getEditable()) {
			return [];
		}

		$dateString = $this->getDateString();
		$fileArray = glob("asset/photo/news/$dateString*");

		$imageList = [];
		foreach($fileArray as $file) {
			$baseName = pathinfo($file, PATHINFO_BASENAME);
			$baseName = rawurlencode($baseName);
			$fileName = pathinfo($file, PATHINFO_FILENAME);

			$fileNameNoDate = substr($fileName, strlen($dateString));
			$fileNameNoDate = trim($fileNameNoDate, "-");

			$uri = "/asset/photo/news/$baseName";
			$imageList[$fileNameNoDate] = $uri;
		}

		return $imageList;
	}

	private function getHTML():string {
		$markdown = $this->markdown;

		$images = $this->getImages();
		if($images) {
			$markdown .= "\n\n";
		}

		foreach($images as $title => $uri) {
			$markdown .= "![$title]($uri) ";
		}

		if($images) {
			$markdown .= "\n";
		}

		$environment = new Environment([
			"allow_unsafe_links" => true
		]);
		$environment->addExtension(new CommonMarkCoreExtension());
		$environment->addExtension(new AttributesExtension());

		$youtubeHtml = '<iframe src="https://www.youtube-nocookie.com/embed/YOUTUBE_CODE" title="Home For Butterflies video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>';

		preg_match_all("#https?://(?:youtube\.com|youtu\.be)/(?:video/|watch\?v=)?(?P<CODE>[^/\n?]+)(?:/edit|\?si=.+)?#", $markdown, $matches);
		if(isset($matches[0]) && !empty($matches[0])) {
			foreach($matches[0] as $i => $originalText) {
				$youtubeCode = $matches[1][$i];
				$youtubeHtmlInjected = str_replace("YOUTUBE_CODE", $youtubeCode, $youtubeHtml);
				$markdown = str_replace($originalText, "\n\n$youtubeHtmlInjected\n\n", $markdown);
			}
		}

		$converter = new MarkdownConverter($environment);
		return $converter->convert($markdown);
	}

	/** @return array<string> List of public paths */
	public function getPhotoList():array {
		$dateString = $this->getDateString();
		$glob = glob("asset/photo/news/$dateString-*.jpg");
		return array_map(fn($str) => "/$str", $glob);
	}
}
