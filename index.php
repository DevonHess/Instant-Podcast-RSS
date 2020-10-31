<?php
	header('Content-Type: text/xml');
	$dir = $_GET["d"] ?? '';
	$path = preg_replace('/^(.*\/).*$/','$1',"https://" . $_SERVER['SERVER_NAME'] . $_SERVER['SCRIPT_NAME']);
	$files = [];
	$list = [];

	echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
	echo "<rss version=\"2.0\" xmlns:itunes=\"http://www.itunes.com/dtds/podcast-1.0.dtd\">";
	echo "<channel>";
	echo "<title>" . ($dir ? rtrim($dir, "/") : "Devon's Podcast Server") . "</title>";
	echo "<description>Devon's Podcast Server</description>";
	echo "<link>" . $path . $dir . "</link>";
	echo "<itunes:image href=\"" . $path . $dir . "image.png\"/>";
	echo "<itunes:block>yes</itunes:block>";
	
	function addEntry($t,$s,$l,$g,$p,$i)
	{
		global $path;
		echo "<item>";
		echo "<title>" . $t . "</title>";
		echo "<description>" . $s . "</description>";
		echo "<link>" . $l . "</link>";
		echo "<guid>" . $g . "</guid>";
		echo "<pubDate>" . $p . "</pubDate>";
		if ($i)
		{
			echo "<itunes:image href=\"" . $path . $i . "\"/>";
		}
		echo "</item>";
	}
	

	function recurse($d)
	{
		global $path, $list;
		if ($d) {
			$files = scandir($d);
		}
		else
		{
			$files = scandir('./');
		}
		unset($files[array_search('.', $files)]);
		unset($files[array_search('..', $files)]);
		$files = array_values($files);

		for ($i = 0; $i < count($files); $i++)
		{
			if (is_dir($d . $files[$i]))
			{
				recurse($d . $files[$i] . '/');
			}
			else
			{
				$list[] = (object) array('file' => $files[$i], 'dir' => $d);
			}
		}
	}

	recurse($dir);

	usort($list, function ($a, $b)
	{
		return strnatcasecmp($b->dir . $b->file, $a->dir . $a->file);
	});

	for ($i = 0; $i < count($list); $i++)
	{
		$ext = preg_replace('/.*(?:\.(.*))/','$1',strtolower($list[$i]->file));

		switch ($ext)
		{
			case 'flac':
			case 'mp3':
			case 'ogg':
			case 'wav':
				break;
			default:
				continue 2;
		}

		$name = htmlspecialchars(preg_replace('/(.*)(?:\..*)/','$1',$list[$i]->file));
		$desc = $list[$i]->dir . $name . '.' . $ext;
		$url = $path . $list[$i]->dir . $list[$i]->file;
		$pub = date("r", filectime($list[$i]->dir . $list[$i]->file));
		if (file_exists($list[$i]->dir . $list[$i]->file . ".png"))
		{
			$img = $list[$i]->dir . $list[$i]->file . ".png";
		}
		else if (file_exists($list[$i]->dir . "image.png"))
		{
			$img = $list[$i]->dir . "image.png";
		}
		else
		{
			$img = '';
		}

		addEntry
		(
			$name,
			$desc,
			$url,
			$url,
			$pub,
			$img
		);
	}

	echo "</channel>";
	echo "</rss>";
?>
