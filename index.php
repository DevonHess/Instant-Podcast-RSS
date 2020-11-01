<?php
	header('Content-Type: text/xml');
	$dir = $_GET["d"] ?? '';
	$path = preg_replace('/^(.*\/).*$/','$1',"https://" . $_SERVER['SERVER_NAME'] . $_SERVER['SCRIPT_NAME']);
	$files = [];
	$list = [];

	function grabImage($d, $file)
	{
		global $dir;
		$ext = ["gif", "jpeg", "jpg", "png"];
		for ($i = 0; $i < count($ext); $i++)
		{
			if (file_exists($d . $file . "." . $ext[$i]))
			{
				$img = $d . $file . "." . $ext[$i];
				break;
			}
			else if ($dir !== $d && file_exists($d . "image." . $ext[$i]))
			{
				$img = $d . "image." . $ext[$i];
				break;
			}
			else
			{
				$img = '';
			}
		}
		return $img;
	}

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

	echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
	echo "<rss version=\"2.0\" xmlns:itunes=\"http://www.itunes.com/dtds/podcast-1.0.dtd\">";
	echo "<channel>";
	echo "<title>" . ($dir ? rtrim($dir, "/") : "Devon's Podcast Server") . "</title>";
	echo "<description>Devon's Podcast Server</description>";
	echo "<link>" . $path . $dir . "</link>";
	echo "<itunes:image href=\"" . $path . grabImage($dir, "image") . "\"/>";
	echo "<itunes:block>yes</itunes:block>";
	
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
		$img = grabImage($list[$i]->dir, $list[$i]->file);

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
