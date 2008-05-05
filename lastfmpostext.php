<?php
/*
Plugin Name: Last.fm Post Extension
Plugin URI: http://www.steffen-goertz.de/2008/04/18/lastfm-post-extension/
Description: Enhance your Post with your currently played track
Author: Steffen Görtz
Version: 1.2.2
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
	echo '<link href="'.get_option('siteurl').'/wp-content/plugins/lastfm-post-extension/lastfmpostext.css" type="text/css" rel="stylesheet"/>';
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
		<p class="submit" style="border: 0;">
			<input type="submit" name="Submit" value="<?php _e('Update Options »') ?>" />
		</p>
		<h3><?=__('Your Last.fm Username', 'lastfmPostExt')?></h3>
		<input type="text" name="lastfmPostExt_username" value="<?php echo get_option('lastfmPostExt_username'); ?>" />
<?php
	// Check if Environment is ok
	$background = '#DD2222';
	if( !function_exists('simplexml_load_file') ) {
		$status = __('SimpleXML Extension not available! You must have turned on SimpleXML in your PHP Environment.', 'lastfmPostExt');
	} elseif( strtolower(ini_get('allow_url_fopen')) == 'off' )  {
		$status = __('Reading of Remote Files is not allowed! Turn on `allow_url_fopen` in your PHP Configuration.', 'lastfmPostExt');
	} else {
		$xml = @simplexml_load_file('http://ws.audioscrobbler.com/1.0/user/'.get_option('lastfmPostExt_username').'/recenttracks.xml');
		if(!$xml) {
			$status = __('Last.fm Service unavailable for', 'lastfmPostExt');
		} else {
			$status = __('Last.fm Service available for', 'lastfmPostExt');
			$background = '#22DD22';;
		}
	}
?>
	<p style="padding: 0.5em; background-color: <?=$background?>; color: rgb(255, 255, 255); font-weight: bold;">
		<?=$status.' '.get_option('lastfmPostExt_username')?>
	</p>
	
		<h3><?=__('Timeout in Seconds', 'lastfmPostExt')?></h3>
		<p>When a Song is scrobbled, the point in time is saved. 
		To determine when a Song is "old" we try to get information about the duration of the last played song via 
		the <a href="http://www.musicbrainz.org">MusicBrainz.org-Database</a>. The "Lifetime", so how long a song
		is considered as "currently played" is calculated by</p>
		<p style="padding: 5px 0 5px 15px;">
			Now - ( Time of Scrobbling  + (opt.) Duration )
		</p>
		<p>When this <strong>smaller</strong> then <strong>Timeout</strong> (this field!), a Song is <i>currently played</i></p>
		<p>You will have to play around with this value. A high value (10 - 20 mins) is needed if no duration info is available for your songs 
		or audioscrobbler is lame. A small value ( 5 mins) can be used when audioscrobbler is fast and all your songs have duration info.
		</p>
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
		'date' => (integer)$xml->date['uts']
	);
	
	# Checking for additionaly Information via MusicBrainz
	$mbid = array(
		'track' => (string)$xml->mbid,
		'artist' => (string)$xml->artist['mbid'],
		'album' => (string)$xml->album['mbid']
	);
	
	if($mbid['track'])
		$info['duration'] = lastfmPostExt_infoTrackHelper($mbid['track']);
	else
		$info['duration'] = lastfmPostExt_infoDurationHelper($mbid, $info);
	
	# Lastfm doesnt give any Info how long a track is,
	# so i have to guess - longer than is no normal average song
	# sorry dear electro and chillout listener
	
	if( (time() - ($info['date'] + ($info['duration']/1000) ) ) < (integer)get_option('lastfmPostExt_timeout') ) {
		add_post_meta($postID, '_lastfmPostExtMusic', serialize($info));
	}
	
	return $postID;
}
add_action('publish_post', 'lastfmPostExt_populate');

function lastfmPostExt_infoTrackHelper($mbid) {
	$duration = 0;
	
	$trackData = @simplexml_load_file('http://musicbrainz.org/ws/1/track/' . $mbid . '?type=xml&inc=duration');
	if($trackData) {
		$duration = (integer)((string)$trackData->track[0]->duration);
	}
	
	return $duration;
}

function lastfmPostExt_infoDurationHelper($mbid, $info) {
	$duration = 0;
	
	$searchString = '&title=' . urlencode($info['track']);
	
	# Album Info
	if( $mbid['album'] ) {
		$searchString .= '&releaseid=' . $mbid['album'];
	} else if( $info['album'] ) {
		$searchString .= '&release=' . urlencode($info['album']);
	}
	
	# Artist
	if( $mbid['artist'] ) {
		$searchString .= '&artistid=' . $mbid['artist'];
	} else if( $info['artist'] ) {
		$searchString .= '&artist=' . urlencode($info['artist']);
	}
	
	$artistData = @simplexml_load_file('http://musicbrainz.org/ws/1/track/?type=xml' . $searchString);
	
	if($artistData) {
		if($artistData->{"track-list"} && ( (integer)$artistData->{"track-list"}['count'] != 0 ) ) {
			$count = 0;
			
			foreach ($artistData->{"track-list"}->track as $track) {
				if($track->duration) {
					$duration += (integer)$track->duration;
					$count++;
			
					if($count == 10) 
						break;
				}
			}
			$duration /= $count;
		}
	}
	
	return $duration;
}

function lastfmPostExt_log($msg) {
	file_put_contents('../wp-content/plugins/lastfm-post-extension/log.txt',$msg ."\r\n", FILE_APPEND);
}
?>