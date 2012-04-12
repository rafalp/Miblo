<?php

/*
 * This file is part of the Miblo package.
 *
 * (c) Rafał Pitoń <kontakt@rpiton.com>
 * 
 * $Release: 0012 $
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Miblo;

use Symfony\Component\HttpKernel\Util\Filesystem;

class Application
{
	/**
	 * Blog author
	 * 
	 * @var string
	 */
	protected $_author		= 'DEMOn';
	
	/**
	 * Blog description
	 * 
	 * @var string
	 */
	protected $_description	= 'Noname';
	
	/**
	 * Blog domain
	 * 
	 * @var string
	 */
	protected $_domain		= '';
	
	/**
	 * Dates format
	 * 
	 * @var string
	 */
	protected $_format		= 'l, j F Y';
	
	/**
	 * Blog name
	 * 
	 * @var string
	 */
	protected $_name		= 'Noname';
	
	/**
	 * Path prefix to all urls
	 * 
	 * @var string
	 */
	protected $_path		= '';
	
	/**
	 * Posts data
	 * 
	 * @var array
	 */
	protected $_posts		= array();
	
	/**
	 * Root directory
	 * 
	 * @var string
	 */
	protected $_rootDir		= '';
	
	/**
	 * Wrapper template
	 * 
	 * @var Twig_Template
	 */
	protected $_tplWrapper	= false;
	
	/**
	 * Default translation data
	 * 
	 * @var array
	 */
	protected $_translation	= array(
		'langName' => 'English'
	);
	
	/**
	 * Twig
	 * 
	 * @var Twig_Enviroment
	 */
	protected $_twig		= false;
	
	/**
	 * Application entry point
	 * 
	 * @param string $rootDir
	 * @param array $config
	 */
	public function __construct($rootDir, $config)
	{
		// Set error reporting
		error_reporting(E_ALL);
		
		// Send "Hi world!" message
		echo "\n\nWelcome to Miblo, blog-aware static site generator!";
		
		// Store rootDir and date format
		$this->_rootDir		= $rootDir . DIRECTORY_SEPARATOR;
		
		// Set format
		if (isset($config['format']))
		{
			$this->_format = $config['format'];
		}
		
		// Set blog author
		if (isset($config['author']))
		{
			$this->_author = $config['author'];
		}
		
		// Set blog name
		if (isset($config['name']))
		{
			$this->_name = $config['name'];
		}
		
		// Set blog description
		if (isset($config['description']))
		{
			$this->_description = $config['description'];
		}
		
		// Set domain
		if (isset($config['domain']))
		{
			$config['domain'] = mb_strtolower($config['domain']);
			if (substr($config['domain'], 0, 7) != 'http://')
			{
				$config['domain'] = 'http://' . $config['domain'];
			}
		}
		
		// Set path
		if (isset($config['path']))
		{
			$this->_path = $config['path'];
			
			// Trim beginning slash
			while (isset($this->_path[0]) && $this->_path[0] == '/')
			{
				$this->_path = trim(substr($this->_path, 1));
			}
			
			// Add trailing slash
			if (substr($this->_path, 0, -1) != '/')
			{
				$this->_path .= '/';
			}
		}
		
		// Prepend opening slash
		if (strlen($this->_path) > 1)
		{
			$this->_path = '/' . $this->_path;
		}
		
		// Set translation
		if (isset($config['translation']))
		{
			$translationFile = $this->_makeDirPath('library/Miblo/Translation/') . str_replace(array('/', '\\'), '', $config['translation']) . '.php';
			if (file_exists($translationFile))
			{
				include $translationFile;
				echo "\nUsing \"" . $this->_translation['langName'] . "\" translation.";
			}
		}
		
		// Prune existing site
		$this->_pruneExistingPages();

		// Start Twig
		$this->_twig = new \Twig_Environment(
			new \Twig_Loader_Filesystem($this->_makeDirPath('templates/')));
		
		// Load shared templates
		$this->_tplWrapper = $this->_twig->loadTemplate('wrapper.html.twig');
		
		// Generate new site
		$this->_findPosts();
		$this->_generateNewSite();

		// Add space at bottom of script
		echo "\n\n";
	}
	
	/**
	 * Asserts there is a date directory
	 * 
	 * @param array $date 
	 */
	protected function _assertDateDirectoryExists($date)
	{
		$dirYear = $this->_makeDirPath('site/' . $date[0] . '/');
		$dirMonth = $this->_makeDirPath('site/' . $date[0] . '/' . $date[1] . '/');
		
		// Year
		if (!is_dir($dirYear))
		{
			mkdir($dirYear);
			
		}
		
		// Month
		if (!is_dir($dirMonth))
		{
			mkdir($dirMonth);
		}
				
		return $dirMonth . DIRECTORY_SEPARATOR;
	}
	
	/**
	 * Make fancy date
	 * 
	 * @param integer $timestamp
	 * @param string $format
	 * @return string 
	 */
	protected function _date($timestamp, $format = false)
	{
		return strtr(
			date(($format !== false ? $format : $this->_format), $timestamp),
			$this->_translation);
	}
	
	/**
	 * Skim files for posts
	 * 
	 */
	protected function _findPosts()
	{
		foreach (new \DirectoryIterator($this->_makeDirPath('posts')) as $file)
		{
			if ($file->isFile())
			{
				// Empty file title and default template
				$fileTitle = 'Empty';
				$fileTemplate = 'post';
				$fileDescription = '';
				
				// Get file date and timestamp
				$fileDate = explode('-', substr($file->getFilename(), 0, 10));
				$fileTimestamp = mktime(0, 0, 0, $fileDate[1], $fileDate[2], $fileDate[0]);
				
				// Build fancy file name
				$fileName = substr($file->getFilename(), 0, strrpos($file->getFilename(), '.'));
				$fileName = substr($fileName, strpos($fileName, '-') + 1);
				$fileName = substr($fileName, strpos($fileName, '-') + 1);
				$fileName = substr($fileName, strpos($fileName, '-') + 1);

				// Read file headers
				$fileContent = file($this->_makeDirPath('posts/' . $file->getFilename()));
				foreach ($fileContent as $n => $line)
				{
					// File header?
					if (isset($line[0]) && $line[0] == '@')
					{
						$line = trim(substr($line, 1));
						if (strpos($line, ':') !== false)
						{
							$headerType = strtolower(trim(substr($line, 0, strpos($line, ':'))));
							$headerValue = trim(substr($line, strpos($line, ':') + 1));
							
							// Handle header
							if (strlen($headerValue) > 0)
							{
								switch ($headerType)
								{
									case 'description':
											$fileDescription = $headerValue;
										break;
									
									case 'title':
											$fileTitle = $headerValue;
										break;
									
									case 'template':
											if (!in_array($fileTemplate, array('wrapper', 'index', 'post', 'rss')) && file_exists($this->_makeDirPath('templates/' . $headerValue . '.html.twig')))
											{
												$fileTemplate = $headerValue;
											}
										break;
								}
							}
						}
					}
					else
					{
						// Nope, break iteration
						break;
					}
				}
				
				// Extract "short"
				$fileLines = count($fileContent);
				$fileShort = array();
				for ($i = $n; $i < $fileLines; $i ++)
				{
					$lineTrimmed = trim($fileContent[$i]);
					if (strtolower($lineTrimmed) == '<!-- more -->')
					{
						break;
					}
					$fileShort[] = $fileContent[$i];
				}
				
				$fileDate = array(gmdate('Y', $fileTimestamp), gmdate('m', $fileTimestamp));
				
				$this->_posts[$fileTimestamp] = array(
					'hash'				=> substr(md5(implode('-', $fileDate)), 0, 8),
					'title'				=> $fileTitle,
					'description'		=> $fileDescription,
					'preview'			=> $this->_parseText(trim(implode("\n", $fileShort))),
					'depth'				=> $fileDate,
					'timestamp'			=> $fileTimestamp,
					'date'				=> $this->_date($fileTimestamp),
					'pubDate'			=> date('r', $fileTimestamp),
					'template'			=> $fileTemplate,
					'file'				=> $file->getFilename(),
					'fancyName'			=> $fileName . '.html',
					'link'				=> $this->_path . $fileDate[0] . '/' . $fileDate[1] . '/' . $fileName . '.html',
				);				
			}
		}
		ksort($this->_posts);
		$this->_posts = array_reverse($this->_posts);
	}
	
	/**
	 * Turn raw files into entries
	 *  
	 */
	protected function _generateNewSite()
	{
		// Load templates
		$TplPost = $this->_twig->loadTemplate('post.html.twig');
		
		// Reiterate files
		$pages = 0;
		foreach ($this->_posts as $id => $post)
		{
			// Assert directory exists
			$dirName = $this->_assertDateDirectoryExists($post['depth']);
			
			// Open and clear file content
			$fileContent = file($this->_makeDirPath('posts/' . $post['file']));
			foreach ($fileContent as $n => $line)
			{
				if (isset($line[0]) && $line[0] == '@')
				{
					unset($fileContent[$n]);
				}
				else
				{
					break;
				}
			}
			$fileContent = trim(implode("\n", $fileContent));
						
			// Defaultise post description
			if (strlen($post['description']) == 0)
			{
				$post['description'] = $this->_description;
			}
			
			// Render template
			file_put_contents($dirName . $post['fancyName'], $this->_tplWrapper->render(array(
				'name'			=> $this->_name,
				'author'		=> $this->_author,
				'title'			=> $post['title'],
				'date'			=> $post['date'],
				'description'	=> $post['description'],
				'domain'		=> $this->_domain,
				'path'			=> $this->_path,
				'post'			=> $post,
				'next'			=> (isset($this->_posts[$id + 1])
					? $this->_posts[$id + 1]
					: false),
				'previous'		=> (isset($this->_posts[$id - 1])
					? $this->_posts[$id - 1]
					: false),
				'page'			=> $TplPost->render(array(
					'name'			=> $this->_name,
					'author'		=> $this->_author,
					'path'			=> $this->_path,
					'post'			=> $post,
					'next'			=> (isset($this->_posts[$id + 1])
						? $this->_posts[$id + 1]
						: false),
					'previous'		=> (isset($this->_posts[$id - 1])
						? $this->_posts[$id - 1]
						: false),
					'title'			=> $post['title'],
					'description'	=> $post['description'],
					'date'			=> $post['date'],
					'domain'		=> $this->_domain,
					'path'			=> $this->_path,
					'link'			=> $post['link'],
					'text'			=> $this->_parseText($fileContent),
				)),
			)));
			
			// Increase counter
			$pages ++;
		}
		
		// Generate Index and Archive
		foreach (array('index', 'archive') as $page)
		{
			$TplIndex = $this->_twig->loadTemplate($page . '.html.twig');
			file_put_contents($this->_makeDirPath('site/' . $page . '.html'), $this->_tplWrapper->render(array(
				'special'		=> $page,
				'name'			=> $this->_name,
				'author'		=> $this->_author,
				'description'	=> $this->_description,
				'domain'		=> $this->_domain,
				'path'			=> $this->_path,
				'page'			=> $TplIndex->render(array(
					'name'			=> $this->_name,
					'author'		=> $this->_author,
					'description'	=> $this->_description,
					'domain'		=> $this->_domain,
					'path'			=> $this->_path,
					'posts'			=> $this->_posts,
				)),
			)));
		}
	
		// Generate RSS
		$TplRss = $this->_twig->loadTemplate('rss.xml.twig');
		file_put_contents($this->_makeDirPath('site/rss.xml'), $TplRss->render(array(
			'name'				=> $this->_name,
			'author'			=> $this->_author,
			'description'		=> $this->_description,
			'domain'			=> $this->_domain,
			'path'				=> $this->_path,
			'posts'				=> $this->_posts,
			'pubDate'			=> date('r', reset(array_keys($this->_posts))),
			'lastBuildDate'		=> date('r'),
		)));
		
		echo "\nWebsite containing " . ($pages == 1 ? 'one page' : $pages . ' pages') . ' has been generated!';
	}
		
	/**
	 * Make path
	 * 
	 * @param string $dirPath
	 * @return string
	 */
	protected function _makeDirPath($path)
	{
		return $this->_rootDir . str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $path);
	}
	
	/**
	 * Parse text
	 * 
	 * @param string $text
	 * @return string
	 */
	protected function _parseText($text)
	{
		return strtr($text, array(
			'{{ author }}'	=> $this->_author,
			'{{ name }}'	=> $this->_name,
			'{{ domain }}'	=> $this->_domain,
			'{{ path }}'	=> $this->_path,
		));
	}
	
	/**
	 * Prune existing pages
	 *  
	 */
	protected function _pruneExistingPages()
	{
		// Start helper
		$Filesystem = new Filesystem();
		
		echo "\nPruning existing entries... ";
		foreach (new \DirectoryIterator($this->_makeDirPath('site')) as $dir)
		{
			if ($dir->isDir() && is_numeric($dir->getFilename()))
			{
				$Filesystem->remove($this->_makeDirPath('site/' . $dir->getFilename()));
			}
		}
		echo "done.";
	}
}