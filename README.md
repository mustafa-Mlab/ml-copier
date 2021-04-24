# WP Copier
A simple plugin to migrate content from one live site to another

# Details 
Contributors: Mustafa Kamal Hossain
Tags: Migration, Cop Content, Wordpress, Live Content
Requires at least: 3.5
Tested up to: 5.7
Stable tag: 1.0.0
Multisite Compitable: Yes
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Migrate content from one live site to another.


# Description 

This plugin can copy content from onel live site to another. In order to copy content / posts this plugin need to be active on both server. If Multisite this plugin need to be Network Active.

**Installation** 

This process defines you the steps to follow either you are installing through WordPress or Manually from FTP.

**From within WordPress**

1. Visit 'Plugins > Add New'
2. Search for WP Copier
3. Activate WP Copier from your Plugins page.
4. Go to "after activation" below.

**Manually**

1. Upload the `wp-copier` folder to the `/wp-content/plugins/` directory
2. Activate WP Copier through the 'Plugins' menu in WordPress
3. Go to "after activation" below.

**After activation**

1. Go to the WP COPIER settings page.
2. Enter the site's URL from where you want to copy content
3. Press `Get Post Types List` button. and all the post types list will be appeare in that page
4. Check desire post type's checkbox and another checkbox will appear as `Scrab All` 
5. If you want to grab all the post of that post type leave that checked
6. If you want to grab some selective post then uncheck `Scrab all` checkbox and another box will appear with list of all posts of that post type
7. Check the posts checkbox which you want to grab from remote server 
8. Press Start copy button from bottom right site of screen.