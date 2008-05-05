=== Last.fm Post Extension ===
Contributors: Steffen Görtz
Donate link: http://www.steffen-goertz.de/2008/04/18/lastfm-post-extension/
Tags: post, last.fm, track, music
Requires at least: 2.5.0
Tested up to: 2.5
Stable tag: 1.2.2

This Plugin automagically adds the last scrobbled Song to your published Post.

== Description ==

It gets the last song you listened to from your Last.fm Profile and add its to your Posts Metadata.
Via the Tagfunction `the_music()` you can show the formated song information on your post page.
No Last.fm Password is needed, only your Username.

== Individualisation ==
There a three possibilitys to change `Last.fm Post Extension` public Appearance:
* Setting up the Format String in Last.Fm Post Extensions Options Page (Admin -> Plugins)
* Editing `lastfmpostext.css`
* Edit the $output Var in `lastfmpostext.php` `the_music()`
 
== Requirements ==

Unfortunaly i'm a lazy guy. So there is no PHP4 support since it uses the SimpleXML Class, 
which is only included in PHP5. But PHP4 is obsolete anyway, so this is a good opportunity to get rid of it.

== Installation ==
1. Upload the Folder `lastfm-post-extension` to your `\wp-content\plugins\` Folder
1. LogIn and active the Plugin in Wordpress 'Plugin' Menu
1. Setup Last.fm Username, Timeout and Link Layout in `Last.fm Post Ext` Options Page
1. Go to the Design Section of Wordpress Backend -> Theme Editor -> `index.php`
1. Place `<?=the_music()?>` somewhere. I suggest you do it either directly under the header or in the meta box of the post (look at screenshot-2.png)
1. Do the same in `single.php` and `archiv.php`
1. Happy Posting. You might leave me a short comment under the donatation url.

== Screenshots ==

1. This is what the output of the plugin may look like. You are free to design the output yourself
2. This is how you integrate the song output to your single/index/archiv file
3. The Backend