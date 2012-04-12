<?php

/*
 * Small command line utility for updating files releases
 *
 * (c) Rafał Pitoń <kontakt@rpiton.com>
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met: 
 * 
 * 1. Redistributions of source code must retain the above copyright notice, this
 *    list of conditions and the following disclaimer. 
 * 2. Redistributions in binary form must reproduce the above copyright notice,
 *    this list of conditions and the following disclaimer in the documentation
 *    and/or other materials provided with the distribution. 
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR
 * ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 * 
 * The views and conclusions contained in the software and documentation are those
 * of the authors and should not be interpreted as representing official policies, 
 * either expressed or implied, of the FreeBSD Project.
 */

// Iteration seed
$iterationSeed = array(
	'library' . DIRECTORY_SEPARATOR . 'Miblo',
	'generate.php',
);

// Define constants
define('releasesLog', __DIR__ . DIRECTORY_SEPARATOR . 'releases_log.php');

// Count release number
$startDate = array(4, 12, 2012);
$months = ((date('Y') - date('Y', mktime(0, 0, 0, $startDate[0], $startDate[1], $startDate[2]))) * 12) + (date('n')) - $startDate[0];
define('releaseVersion', (strlen($months) == 2 ? $months : '0' . $months) . date('d'));

// Send message
echo "\n\n\nMarking files for " . releaseVersion . " release version.";

// Load releases history
$releasesHistory = array();
if (file_exists(releasesLog))
{
	$releasesHistory = @include releasesLog;
	if (!is_array($releasesHistory))
	{
		exit("\nCorrupted releases log.\n\n\n");
	}
	echo "\nReleases log with " . count($releasesHistory) . " entries loaded.";
}
else
{
	echo "\nBeginning new releases log.";
}

// Updates stat
$updatesStat = array(
	0, // Total
	0, // New
	0, // Updated
	0, // Unchanged
);

// Iterate seed
echo "\n";
foreach ($iterationSeed as $seedItem)
{
	// Add dir to seed item
	checkItem(__DIR__ . DIRECTORY_SEPARATOR . $seedItem);
}

// Show stat
echo "\n\nNew files:          " . $updatesStat[1] . "\nModified files:     " . $updatesStat[2] . "\nUnchanged files:    " . $updatesStat[3] . "\n\nTotal files:        " . $updatesStat[0];

// Save new log
echo "\n\n\nLog saved.";
file_put_contents(releasesLog, "<?php\n\nreturn " . var_export($releasesHistory, true) . ";");

// Close action
echo "\n\n\n";

/**
 * Iterator function
 * 
 * @global array $releasesHistory
 * @global array $updatesStat
 * @param string $item 
 */
function checkItem($item)
{
	// Globals
	global $releasesHistory;
	global $updatesStat;
	
	// File or directory?
	if (strtolower(substr($item, -4)) == '.php')
	{
		// Update count
		$updatesStat[0]++;
		
		// Get filename
		$fileName = substr($item, strlen(__DIR__ . DIRECTORY_SEPARATOR));
		echo "\nChecking " . $fileName . '... ';
		
		// Tracked file?
		if (isset($releasesHistory[$fileName]))
		{
			// Changed file?
			if (md5_file($item) != $releasesHistory[$fileName] || substr($fileName, -18) == 'Pirx' . DIRECTORY_SEPARATOR . 'Framework.php')
			{
				$releasesHistory[$fileName] = updateFileKeywords($item);
				$updatesStat[2] ++;
				echo "updated.";
			}
			else
			{
				$updatesStat[3] ++;
				echo "unchanged.";
			}
		}
		else
		{
			$releasesHistory[$fileName] = updateFileKeywords($item);
			$updatesStat[1] ++;
			echo "added.";
		}
	}
	else if (is_dir($item))
	{
		// Loop directories
		foreach (new DirectoryIterator($item) as $directory)
		{
			if (!$directory->isDot())
			{
				checkItem($item . DIRECTORY_SEPARATOR . $directory->getFilename());
			}
		}
	}
}

/**
 * Updates release number, returns md5 has 
 * 
 * @param string $file
 * @return string
 */
function updateFileKeywords($file)
{
	// Open file
	$fileContents = file_get_contents($file);
	
	// Replace release number
	$fileContents = preg_replace('/\$Release: [0-9][0-9][0-9][0-9] \$/si', '$Release: ' . releaseVersion . ' $', $fileContents);
	$fileContents = str_ireplace('$Release$', '$Release: ' . releaseVersion . ' $', $fileContents);
	
	// Update version number in framework
	if (substr($file, -18) == 'Pirx' . DIRECTORY_SEPARATOR . 'Framework.php')
	{
		$constantPos = strpos($fileContents, '\'', strpos($fileContents, 'const version')) + 1;
		$constantLength = strpos($fileContents, '\'', $constantPos) - $constantPos;
		$fileContents = substr_replace($fileContents, releaseVersion, $constantPos, $constantLength);
	}
	
	// Save file
	file_put_contents($file, $fileContents);
	return md5_file($file);
}