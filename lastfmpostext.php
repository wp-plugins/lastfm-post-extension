<?php
/*
Plugin Name: Last.fm Post Extension
Plugin URI: http://www.steffen-goertz.de/2008/04/18/lastfm-post-extension/
Description: Enhance your Post with your currently played track
Author: Steffen Görtz
Version: 1.0
Author URI: http://www.steffen-goertz.de/
*/

/*  Copyright 2008  Steffen Görtz  (email : ed.ztreog-neffets ta neffets (mirror-inverted)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
function the_music() {
	global $id;
	static $formatString = NULL;
	
	if(!$formatString) {
		$formatString = get_option('lastfmPostExt_format');
	}
	
	$payload = get_post_meta($id, '_lastfmPostExtMusic', true);
	if($payload) {
		$info = unserialize($payload);
	
		$search = array('/%artist/','/%album/','/%track/');
		$replace = array($info['artist'], $info['album'], $info['track']);
		$formatedString = preg_replace($search, $replace, $formatString);
		
		$output = '<span class="lastfmpostext '.($info['streamable'] ? 'streamable' : '').'">';
		$output .= 'Im Äther: <a href="'.$info['url'].'">'.$formatedString.'</a>';
		$output .= '</span>';
		
		echo $output;
	}
}

function lastfmPostExt_css_link() {
	echo '<link href="'.get_option('siteurl').'/wp-content/plugins/lastfmpostext/lastfmpostext.css" type="text/css" rel="stylesheet"/>';
}
add_action('wp_head','lastfmPostExt_css_link');

function lastfmPostExt_init() {
	add_action('admin_menu', 'lastfmPostExt_config_page');
}
add_action('init', 'lastfmPostExt_init');

function lastfmPostExt_install() {
	add_option('lastfmPostExt_username', '', __('Last.fm Username','lastfmPostExt'));
	add_option('lastfmPostExt_format', '%artist - %track', __('Last.fm Format String for Posts','lastfmPostExt'));
	add_option('lastfmPostExt_timeout','720',__('Timeout for Songs'));
}
register_activation_hook(__FILE__,'lastfmPostExt_install');

function lastfmPostExt_deinstall() {
	 delete_option('lastfmPostExt_username'); 
	 delete_option('lastfmPostExt_format');
	 delete_option('lastfmPostExt_timeout');
}
register_deactivation_hook(__FILE__,'lastfmPostExt_deinstall');

function lastfmPostExt_config_page() {
	add_submenu_page('plugins.php', __('Last.fm PostExt','lastfmPostExt'), __('Last.fm PostExt','lastfmPostExt'), 'manage_options', 'lastfmpostex-user-conf', 'lastfmPostExt_settings');
}

function lastfmPostExt_settings() {
?>
<div class="wrap">
	<h2><?=__('Last.fm Post Extension Configuration','lastfmPostExt')?></h2>
	<div class="narrow">
		<form method="post" id="lastfmPostExtConf" style="margin: auto; width: 400px;" action="options.php">
		<?php wp_nonce_field('update-options'); ?>

		<h3><?=__('Your Last.fm Username', 'lastfmPostExt')?></h3>
		<input type="text" name="lastfmPostExt_username" value="<?php echo get_option('lastfmPostExt_username'); ?>" />
<?php
	$xml = @simplexml_load_file('http://ws.audioscrobbler.com/1.0/user/'.get_option('lastfmPostExt_username').'/recenttracks.xml');
	if($xml) {
		$status = __('Last.fm Service available for','lastfmPostExt');
		$background = '#22DD22';
	} else {
		$status = __('Last.fm Service unavailable for','lastfmPostExt');
		$background = '#DD2222';
	}
?>
	<p style="padding: 0.5em; background-color: <?=$background?>; color: rgb(255, 255, 255); font-weight: bold;">
		<?=$status.' '.get_option('lastfmPostExt_username')?>
	</p>
	
		<h3><?=__('Timeout in Seconds', 'lastfmPostExt')?></h3>
		<p>How many Seconds after Scrobbling is a Song still "currently played" ? <br/> 
			The Problem is Last.fm doesnt provide Track Length via their API. So we have to guess</p>
		<input type="text" name="lastfmPostExt_timeout" value="<?php echo get_option('lastfmPostExt_timeout'); ?>" />

		<h3><?=__('Format String','lastfmPostExt')?></h3>
		<strong>Available Wild-Cards:</strong>
		<ul>
			<li>%artist - The Artists Name</li>
			<li>%album - Album Name</li>
			<li>%track - Track Name</li>
		</ul>
		<input type="text" name="lastfmPostExt_format" value="<?php echo get_option('lastfmPostExt_format'); ?>" />

		<input type="hidden" name="action" value="update" />
		<input type="hidden" name="page_options" value="lastfmPostExt_username,lastfmPostExt_format,lastfmPostExt_timeout" />
		<p class="submit">
			<input type="submit" name="Submit" value="<?php _e('Update Options »') ?>" />
		</p>
		</form>
	</div>
</div>
<?php
}

function lastfmPostExt_populate($postID) {
	$xml = @simplexml_load_file('http://ws.audioscrobbler.com/1.0/user/'.get_option('lastfmPostExt_username').'/recenttracks.xml');

	if(!$xml) 
		return $postID;
	
	$xml = $xml->track[0];	
	
	# I would rather serialize the $xml->track[0] Node directly, but SimpleXML does not allow direct
	# serialization .. hmpf
	$info = array(
		'artist' => (string)$xml->artist,
		'streamable' => ((string)$xml['streamable'] == "true") ? true : false,
		'album' => (string)$xml->album,
		'track' => (string)$xml->name,
		'url' => (string)$xml->url,
		'date' => (string)$xml->date['uts']
	);
	
	$duration = false;
	
	/**
	# Checking for additionaly Information via MusicBrainz
	$mbid = array(
		'track' => (string)$xml->mbid,
		'artist' => (string)$xml->artist['mbid'],
		'album' => (string)$xml->album['mbid']
	);

	# Checking if Track available
	if($mbid['track']) {
		$trackData = @simplexml_load_file('http://musicbrainz.org/ws/1/track/' . $mbid['track']);
	}
	**/
	
	# Lastfm doesnt give any Info how long a track is,
	# so i have to guess - longer than is no normal average song
	# sorry dear electro and chillout listener
	if( (time() - $info['date']) < (integer)get_option('lastfmPostExt_timeout') ) {
		add_post_meta($postID, '_lastfmPostExtMusic', serialize($info));
	}
	
	return $postID;
}
add_action('publish_post', 'lastfmPostExt_populate');
?>