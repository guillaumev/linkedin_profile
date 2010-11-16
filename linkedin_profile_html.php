<?php
/**
 * This program is free software: you can redistribute it and/or modify 
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */


/**
 * LinkedIn profile HTML
 * 
 * Parses a profile page using LinkedInProfile and returns HTML code based on div tags
 * 
 * @author Guillaume Viguier-Just <guillaume@viguierjust.com>
 * @licence http://www.gnu.org/licenses/gpl-3.0.txt
 */
require_once(dirname(__FILE__).'/linkedin_profile.php');

class LinkedInProfileHTML {

	/**
	 * Profile
	 * @var LinkedInProfile
	 */
	protected $_profile = null;
	
	/**
	 * Constructor
	 * 
	 * @param LinkedInProfile Profile to be based on
	 */
	public function __construct($profile) {
		if($profile instanceof LinkedInProfile) {
			$this->_profile = $profile;
		}
	}
	
	/**
	 * Formats the profile identity
	 *
	 * @return string Profile identity formatted in HTML
	 */
	protected function format_identity() {
		$p = $this->_profile;
		$out = '<div class="linkedin-identity">';
		$out .= '<div class="linkedin-identity-firstname">'.$p->firstname.'</div>';
		$out .= '<div class="linkedin-identity-lastname">'.$p->lastname.'</div>';
		$out .= '<div class="linkedin-identity-currentstatus">'.$p->current_status.'</div>';
		$out .= '<div class="linkedin-identity-currentlocality">'.$p->current_locality.'</div>';
		return $out;
	}
	
	/**
	 * Formats an experience
	 * 
	 * @param object Experience
	 * @return string Experience formatted in HTML
	 */
	protected function format_experience($experience) {
		$out = '<div class="linkedin-experience">';
		$out .= '<div class="linkedin-experience-title">'.$experience->title.'</div>';
		$out .= '<div class="linkedin-organization">';
		$out .= '<div class="linkedin-organization-title">';
		if($experience->organization->link) {
			$out .= '<a href="'.$experience->organization->link.'">';
		}
		$out .= $experience->organization->name;
		if($experience->organization->link) {
			$out .= '</a>';
		}
		$out .= '</div>';
		$out .= '<div class="linkedin-organization-sector">'.$experience->organization->sector.'</div>';
		$out .= '</div>';
		$out .= '<div class="linkedin-period">';
		$out .= '<div class="linkedin-period-start">'.$experience->period->start.'</div>';
		$out .= '<div class="linkedin-period-end">'.$experience->period->end.'</div>';
		$out .= '</div>';
		$out .= '<div class="linkedin-experience-description">'.$experience->description.'</div>';
		$out .= '</div>';
		return $out;
	}
	
	/**
	 * Formats an education
	 * 
	 * @param object Education
	 * @return string Education formatted in HTML
	 */
	protected function format_education($education) {
		$out = '<div class="linkedin-education">';
		$out .= '<div class="linkedin-education-title">'.$education->title.'</div>';
		$out .= '<div class="linkedin-education-degree">'.$education->degree.'</div>';
		$out .= '<div class="linkedin-education-major">'.$education->major.'</div>';
		$out .= '<div class="linkedin-period">';
		$out .= '<div class="linkedin-period-start">'.$education->period->start.'</div>';
		$out .= '<div class="linkedin-period-end">'.$education->period->end.'</div>';
		$out .= '</div>';
		$out .= '<div class="linkedin-education-notes">'.$education->notes.'</div>';
		$out .= '</div>';
		return $out;
	}
	
	/**
	 * Formats skills and interests
	 * 
	 * @return string Skills and interests formatted in HTML
	 */
	protected function format_skills_interests() {
		$p = $this->_profile;
		$out = '<div class="linkedin-skills">'.$p->skills.'</div>';
		$out .= '<div class="linkedin-interests">'.$p->interests.'</div>';
		return $out;
	}
	
	/**
	 * Returns the profile in HTML as a string
	 * 
	 * @return string
	 */
	public function __toString() {
		$out = '';
		$out .= $this->format_identity();
		$experiences = $this->_profile->experiences;
		foreach($experiences as $experience) {
			$out .= $this->format_experience($experience);
		}
		$educations = $this->_profile->education;
		foreach($educations as $education) {
			$out .= $this->format_education($education);
		}
		$out .= $this->format_skills_interests();
		return $out;
	}
}
