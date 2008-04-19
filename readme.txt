=== Last.fm Post Extension ===
Contributors: Steffen Görtz
Donate link: http://www.steffen-goertz.de/2008/04/18/lastfm-post-extension/
Tags: post, last.fm, track, music
Requires at least: 2.5.0
Tested up to: 2.5
Stable tag: 1.1

This Plugin automagically adds the last scrobbled Song to your published Post.

== Description ==

It gets the last song you listened to from your Last.fm Profile and add its to your Posts Metadata.
Via the Tagfunction the_music() you can show the formated song information on your post page.
No Last.fm Password is needed, only your Username.

Formating of the Output can be done by
 1. Setting up the Format String in Last.Fm Post Extensions Options Page (Admin -> Plugins)
 2. Editing lastfmpostext.css
 3. Edit the $output Var in lastfmpostext.php the_music()
 
== Requirements ==

Unfortunaly i'm a lazy guy. So there is no PHP4 support since it uses the SimpleXML Class, 
which is only included in PHP5. But PHP4 is obsolete anyway, so this is a good opportunity to get rid of it.

== Installation ==

 * Unpack the Archive and edit lastfmpostext.css ( Exchange my URL with Yours )
 * Upload everything to wp-content/plugins/lastfmpostext/
 * Log in and activate the Last.fm Post Extension in your Admin Panel -> Plugins
 * You will see a new Submenu Item in your Plugin Admin Panel
 * Go there and enter your Last.fm Username and the desired Link Design
 * Go to the Design Section of Wordpress Backend -> Theme Editor -> index.php
 * Call <?=the_music()?> whereever you want. I suggest you do it either directly under the header or in the meta box of the post (look at screenshot-2.png)
 * Do the same in single.php und archiv.php
 * Write a new Post and leave me a Comment how you feel with your new Toys :-)

== Screenshots ==

1. This is what the output of the plugin may look like. You are free to design the output yourself
2. This is how you integrate the song output to your single/index/archiv file
3. The Backend