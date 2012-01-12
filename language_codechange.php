<?php
/**
*
* @author carlino1994 / Carlo / www.phpbbitalia.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
* Create code changes for phpBB translations
*
*/

/**
* Append diff to file or echo
* File will be stored in this script directory
*/
$append = false;

/**
* Set translation ISO, phpBB versions and optional author information
*/
$iso = 'en';
$previousVersion = '3.0.9';
$latestVersion = '3.0.10';
$author_username = 'naderman';
$author_email = 'naderman@phpbb.com';
$author_realname = 'Nils Adermann';
$author_website = 'http://www.phpbb.com';

/**
* Translations root directory (with final slash)
*/
$directory = array(
	'previous'	=> "./phpBB/{$previousVersion}/language/{$iso}/",
	'latest'	=> "./phpBB/{$latestVersion}/language/{$iso}/"
);

$files = array(
	'new'	=> array(),
	'edit'	=> array()
);

header("Content-Type: text/plain; charset=UTF-8");
include('./diff_class.php');

$text = createDiff($directory['latest']);
$text = DiffHeader() . $text . DiffFooter();

if ($append)
{
	$filename = "./phpbb-{$previousVersion}_to_{$latestVersion}_language_{$iso}.txt";
	$fp = fopen($filename, 'wb');

	if (!$fp)
	{
		echo "Unable to create {$filename}";
	}
	else
	{
		fwrite($fp, $text);
		fclose($fp);

		chmod($filename, 0666);

		echo "File appended to {$filename}";
	}
}
else
{
	echo $text;
}

/**
* Create diff for all files in $path directory
*/
function createDiff($path)
{
	global $directory, $files, $iso;

	$text = '';

	foreach (glob($path . "*") as $filename)
	{
		if (is_dir($filename))
		{
			$text .= createDiff($filename . '/');
		}
		else
		{
			$phpbb_filename = str_replace($directory['latest'], '', $filename);
			$phpbb_file_ext = substr(strrchr($phpbb_filename, '.'), 1);

			if (file_exists($directory['previous'] . $phpbb_filename) && ($phpbb_file_ext == 'php' || $phpbb_file_ext == 'htm' || $phpbb_file_ext == 'txt'))
			{
				$lines1 = file($directory['previous'] . $phpbb_filename);
				$lines2 = file($directory['latest'] . $phpbb_filename);

				$diff = new Diff($lines1, $lines2);
				$fmt = new BBCodeDiffFormatter(false, 5, false);

				$format = $fmt->format($diff, $lines1);

				if (!empty($format))
				{
					$text .= $fmt->format_open("language/{$iso}/" . $phpbb_filename);
					$text .= $format;
					$text .= $fmt->format_close("language/{$iso}/" . $phpbb_filename);

					$files['edit'][] = $phpbb_filename;
				}
			}
			else
			{
				// New file
				$files['new'][] = $phpbb_filename;
			}
		}
	}

	return $text;
}

/**
* Create diff file header
*/
function DiffHeader()
{
	global $iso, $previousVersion, $latestVersion, $author_username, $author_email, $author_realname, $author_website, $files;

	$text = '';
	$text .= "############################################################## \n";
	$text .= "## Title: phpBB {$previousVersion} to phpBB {$latestVersion} Language Pack Changes [{$iso}] \n";
	$text .= "## Author: {$author_username} < {$author_email} >" . (!empty($author_realname) ? " ({$author_realname})" : '') . (!empty($author_website) ? " ({$author_website})" : '') . " \n";
	$text .= "## Description: \n";
	$text .= "##		\n";
	$text .= "##		\n";
	$text .= "##		These are the phpBB {$previousVersion} to phpBB {$latestVersion} Language Pack [{$iso}] Changes summed up into a\n";
	$text .= "##		little Mod. These changes are only partial and do not include any code changes,\n";
	$text .= "##		therefore not meant for updating phpBB.\n";
	$text .= "## \n";
	$text .= "## \n";
	$text .= "## \n";
	$text .= "## \n";

	if (sizeof($files['edit']))
	{
		$text .= "## Files To Edit: \n";

		foreach($files['edit'] as $filename)
		{
			$text .= "##		language/{$iso}/{$filename}\n";
		}

		$text .= "## \n";
	}

	if (sizeof($files['new']))
	{
		$text .= "## Included Files: \n";

		foreach($files['new'] as $filename)
		{
			$text .= "##		language/{$iso}/{$filename}\n";
		}

		$text .= "## \n";
	}

	$text .= "## License: http://opensource.org/licenses/gpl-license.php GNU General Public License v2 \n";
	$text .= "############################################################## \n";
	$text .= "\n";

	if (sizeof($files['new']))
	{
		$text .= "#\n#-----[ COPY ]------------------------------------------\n#\n";

		foreach($files['new'] as $filename)
		{
			$text .= "copy language/{$iso}/{$filename} to language/{$iso}/{$filename}\n";
		}

		$text .= "\n";
	}

	return $text;
}

/**
* Create diff file footer
*/
function DiffFooter()
{
	$text = '';
	$text .= "# \n";
	$text .= "#-----[ SAVE/CLOSE ALL FILES ]------------------------------------------ \n";
	$text .= "# \n";
	$text .= "# EoM";

	return $text;
}
?>