<?php
	header('Content-Type: text/xml');
	$dir = $_GET["d"] ?? '';
	$path = preg_replace('/^(.*\/).*$/','$1',"https://" . $_SERVER['SERVER_NAME'] . $_SERVER['SCRIPT_NAME']);
	$files = [];
	$list = [];

	// Find appropriate image based on directory and filename
	function grabImage($d, $f)
	{
		global $dir;
		$ext = ["gif", "jpeg", "jpg", "png"];
		for ($i = 0; $i < count($ext); $i++)
		{
			// If "file.mp3.png" exists
			if (file_exists($d . $f . "." . $ext[$i]))
			{
				$img = $d . $f . "." . $ext[$i];
				break;
			}
			// If "image.png" exists
			else if (file_exists($d . "image." . $ext[$i]))
			{
				// If file is inside root folder
				if ($dir == $d)
				{
					$img = '';
				}
				else
				{
					$img = $d . "image." . $ext[$i];
				}
				break;
			}
			else
			{
				$img = '';
			}
		}
		return $img;
	}

	// Add an RSS item (title, description, link, guid, date)
	function addEntry($t, $d, $l, $g, $p, $i)
	{
		global $path;
		echo "<item>";
		echo "<title>" . $t . "</title>";
		echo "<description>" . $d . "</description>";
		echo "<link>" . $l . "</link>";
		echo "<guid>" . $g . "</guid>";
		echo "<pubDate>" . $p . "</pubDate>";
		if ($i)
		{
			echo "<itunes:image href=\"" . $path . $i . "\"/>";
		}
		echo "</item>";
	}
	
	// Create list of all files inside folders and subfolders
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

	// Create XML header
	echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
	echo "<rss version=\"2.0\" xmlns:itunes=\"http://www.itunes.com/dtds/podcast-1.0.dtd\">";
	echo "<channel>";
	echo "<title>" . ($dir ? rtrim($dir, "/") : "Devon's Podcast Server") . "</title>";
	echo "<description>Devon's Podcast Server</description>";
	echo "<link>" . $path . $dir . "</link>";
	echo "<itunes:image href=\"" . $path . grabImage($dir, "image") . "\"/>";
	echo "<itunes:block>yes</itunes:block>";
	
	// Create file list starting at root directory
	recurse($dir);

	// Sort files in reverse order to try to match RSS convention
	usort($list, function ($a, $b)
	{
		return strnatcasecmp($b->dir . $b->file, $a->dir . $a->file);
	});

	// Iterate through file list and create XML entries
	for ($i = 0; $i < count($list); $i++)
	{
		$ext = preg_replace('/.*(?:\.(.*))/','$1',strtolower($list[$i]->file));

		// Ignore unsupported file types
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

	// Create XML footer
	echo "</channel>";
	echo "</rss>";
?>
