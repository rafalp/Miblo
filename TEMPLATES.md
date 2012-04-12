TEMPLATES LIST
==============

wrapper.html.twig 
-----------------
* author - blog author
* date - current post date
* description - current post description or blog description if post one is empty
* domain - blog domain
* name - blog name
* next - array containing next post data or false if current post is empty or newest one
* page - current page's content
* path - blog path
* post - array containing current post data
* previous - array containing previous post data or false if current post is empty or first one
* special - equals to 'index' on blog index, 'archive' on blog archive and undefined on other pages
* title - current post title


index.html.twig and archive.html.twig
-------------------------------------
* author - blog author
* description - current post description or blog description if post one is empty
* domain - blog domain
* name - blog name
* path - blog path
* posts - array containing all existing posts data


post.html.twig
--------------
* author - blog author
* date - current post publication date
* description - current post description or blog description if post one is empty
* domain - blog domain
* link - link to current post
* name - blog name
* next - array containing next post data or false if current post newest one
* path - blog path
* post - array containing current post data
* previous - array containing previous post data or false if current post is first one
* text - current post text
* title - current post title


rss.xml.twig
------------
* author - blog author
* description - blog description
* domain - blog domain
* lastBuildDate - RFC-2822 formatted date of last blog's generation
* name - blog name
* path - blog path
* posts - array containing all existing posts data
* pubDate - RFC-2822 formatted date of last entry


Post data structure
-------------------
* date - formatted post publication date
* depth - two-element array containing post year and month integer
* description - post description
* fancyName - name of file with post
* file - name of source file post was generated from
* hash - eight characters long hash individual for each post
* link - link to post, containing path and depth
* preview - preview string of post
* pubDate - RFC-2822 formatted post date
* title - post tile
* timestamp - unix-epoch timestamp
* template - post custom template name