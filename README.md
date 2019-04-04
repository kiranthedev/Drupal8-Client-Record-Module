# Drupal8 Client Record Module

It's an extensive custom CRUD module for Drupal8. 

This demonstrates the use of various drupal8 API. 

This will help you to understand the api changes for drupal 8.

I has custom database table which demonstrates the use of various drupal8 APIs e.g form, block, configuration, views etc.


**Setup Steps**

* Installation 
    * Make sure the Drupal 8 is installed in your system already if not than please follow below links
    * Drupal 8 installation :  You need a web server with a database and PHP. The server could be your personal computer, or at an online web host. You can use of any version of PHP above 7.x
        * Step 1: Get the Code => Download https://www.drupal.org/download-latest/tar.gz from and just copy these files into your apache base folder (www / htdocs)
        * Step 2: Install dependencies with composer => If you installed or updated the codebase using git, then install required PHP libraries with composer.
        * Step 3: Create a database => Create a database for Drupal to use.
        * Step 4: Configure your installation => Set up the web server and PHP to work together.
        * Step 5: Run the installer => Run the installation script.
        * Step 6: Status check => Check the status of your site at Administration > Reports > Status report. Set trusted hosts patterns, create files directories.
    * Now copy "client" folder in sites/all/modules/ folder
    * Check Home > Administration > Extend (http://localhost/drupal/admin/modules)
    * You will be able to see our module Clients Under Custom section 
    * Just Enable it and save the page
    
    
* Clients
    * Client Dashboard http://localhost/drupal/admin/content/client
    * Add New Client http://localhost/drupal/admin/content/client/add
    * On Dashboard you can access all features like Quick Edit, Edit, Delete and Mail etc.
    * Is also supports Delete, Active, Block Multiple in group.
    * Search is also working smoothly
    
    
     