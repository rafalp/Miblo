README
======

What is Miblo?
--------------
Miblo is static site generator writen in PHP and powered by Twig. There is no code being executed on server side as Miblo is entirely an command line utility aimed on running from your PC. This means no database usage and less heat when your blog gets reddited.


How it works?
-------------
Using Miblo to create your own blog is easy as pie.

Defautly Miblo comes with four directories:

* posts - This is where your entries live
* templates - Templates used to generate your blog
* site - Where generated blog lies in wait of being uploaded
* library - Miblo source code is located there


Configuring your blog
---------------------
Your blog's configuration is stored in generate.php file. Once you open it, you will quickly realise that on initialisation, Miblo accepts two arguments: first one is path to current Miblo's directory, second one is array of configuration variables.

Following settings are available for you to configure your project:

* author - Blog author name
* name - Blog name
* description - Blog description
* domain - Blog domain, used in generation of RSS
* path - Blog path, allows you to put your blog outside your domains home directory
* format - Allows you to set a way Miblo formats dates, defaults to "l, j F Y"
* translation - Language used to translate dates. Defauts to "en", but "pl" is also available

author, name, description, domain and path and format are also available for use in templates and your entries.


Writing new entries
-------------------
### Creating file with new entry
To write new entry, create new file in posts directory. Every entry file must follow this syntax in order to be used by Miblo:

YYYY-MM-DD-entry-file-name.html

For example, entry "Hello everyone!" published on 7 april 2012 should be named like this:

2012-04-07-hello-everyone.html

This entry will accessible by following link:

2012\04\hello-everyone.html


### Writing new entry
Open your file in text editor. Every entry file is made of two parts: headers and content.

Headers allow you to set basic metadata for your entry such as title, description or custom template to use to render it. Headers must be defined at very beginning of file. Each line may define only one header.

To mark line as header definition, begin it with "at" (@) character followed by header name. After header name put colon. Everything you write from after colon until end of line will be used as header value.

@Title: Welcome to Miblo!
@Description: Miblo is blog-aware static site generator.

In addition you can also use "Template" header that tells Miblo to use custom template during generation of blog.

Headers cannot be mixed with entry contents. Miblo will stop reading them at first line that doesnt contain correct header.

Everything you write since this point is threated as entry content. If you want to split your entry to display short part of it as "preview", put line containing only "<!-- more -->" at point where you want preview content to end. If you dont put this line in your entry content, entire entry will be treated as preview.


Customising appearance
----------------------
Customisation of Miblo is two step process. First you edit templates located in "templates" directory, second you create or edit assets (stylesheets, javascript images) that will be used by generated pages and are located in site directory.

Defautly Miblo uses following templates during generation:

* wrapper.html.twig - Used on both blog index and post pages
* index.html.twig - Used on blog index page to present list of blog entries
* post.html.twig - Used on post page to present post content
* rss.xml.twig - Used to generate RSS channel for your blog

Miblo is using Twig (http://twig.sensiolabs.org) for parsing templates.
For detailed list of variables available in every template, see "TEMPLATES.md" file.

Generating and publishing
-------------------------
Once everything is done, all that is left is to generate and publish your blog. To generate new site you simply run generate.php file that is located in Miblo directory. Once its work is complete, all you have to do is to upload files located in "site" directory to your server.

Congratulations, your blog is now waiting for readers!