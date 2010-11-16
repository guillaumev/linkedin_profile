<?php

/**
 * Example code to parse a LinkedIn profile using the LinkedInProfile class
 * 
 * @author Guillaume Viguier-Just <guillaume@viguierjust.com>
 */

require_once(dirname(__FILE__).'/linkedin_profile.php');

$profile = new LinkedInProfile('guillaumev', 'fr');

echo 'Your profile: <br />';
echo 'First name: '.$profile->firstname.'<br />';
echo 'Last name: '.$profile->lastname.'<br />';
echo 'Current status: '.$profile->current_status.'<br />';
echo 'Current locality: '.$profile->current_locality.'<br />';
echo 'Skills: '.$profile->skills.'<br />';
echo 'Interests: '.$profile->interests.'<br />';
foreach($profile->experiences as $experience) {
	echo 'Experience: <br />';
	echo '&nbsp;'.$experience->title.'<br />';
	echo '&nbsp;<a href="'.$experience->organization->link.'">'.$experience->organization->name.'</a><br />';
	echo '&nbsp;'.$experience->organization->sector.'<br />';
	echo '&nbsp;'.$experience->period->start.' to '.$experience->period->end.'<br />';
	echo '&nbsp;'.$experience->description.'<br />';
}

foreach($profile->education as $education) {
	echo 'Education: <br />';
	echo '&nbsp;'.$education->title.'<br />';
	echo '&nbsp;'.$education->degree.'<br />';
	echo '&nbsp;'.$education->major.'<br />';
	echo '&nbsp;'.$education->period->start.' to '.$education->period->end.'<br />';
	echo '&nbsp;'.$education->notes.'<br />';
}
