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
 * LinkedIn profile
 * 
 * Parses a profile page and sets the attributes of the object
 * 
 * @author Guillaume Viguier-Just <guillaume@viguierjust.com>
 * @licence http://www.gnu.org/licenses/gpl-3.0.txt
 */
class LinkedInProfile {
	/**
	 * LinkedIn default URL
	 * @var string
	 */
	protected $_url = 'http://www.linkedin.com/in/';
	
	/**
	 * Language required
	 * @var string
	 */
	protected $_language = '';
	
	/**
	 * Profile name
	 * @var string
	 */
	protected $_username = '';
	
	/**
	 * SimpleXML element
	 * @var SimpleXML
	 */
	protected $_xml = null;
	
	/**
	 * First name
	 * @var string
	 */
	public $firstname = '';
	
	/**
	 * Last name
	 * @var string
	 */
	public $lastname = '';
	
	/**
	 * Current status
	 * @var string
	 */
	public $current_status = '';
	
	/**
	 * Current locality
	 * @var string
	 */
	public $current_locality = '';
  
	/**
	 * Summary
	 * @var string
	 */
	public $summary = '';
	
	/**
	 * Skills
	 * @var string
	 */
	public $skills = '';
	
	/**
	 * Interests
	 * @var string
	 */
	public $interests = '';
	
	/**
	 * Photo
	 * @var string
	 */
	public $photo = '';
	
	/**
	 * Experiences
	 * @var array
	 */
	public $experiences = null;
	
	/**
	 * Education
	 * @var array
	 */
	public $education = null;
	
	/**
	 * Constructor
	 * 
	 * @param string Name of the user whose profile is to be parsed or full URL to user profile
	 * @param string Language, set to en by default
	 */
	public function __construct($username, $language = 'en') {
		$profile_url = "";
		if (strpos($username, "http://") !== FALSE) {
			// Username is a URL
			$profile_url = $username;
			$profile_url = trim($profile_url, '/').'/'.$language;
		}
		else {
			$this->_username = $username;
			$profile_url = $this->_url.$this->_username.'/'.$language;
		}
		$this->_language = $language;
		$this->parse($profile_url);
	}
	
	/**
	 * Searches through the document with xpath and assigns to the right value
	 * 
	 * @param string Element to be searched
	 * @param string Class to be searched
	 * @param string Variable name to be assigned
	 */
	protected function search_and_assign($element, $class, $name) {
		foreach ($this->_xml->xpath('//'.$element.'[@class="'.$class.'"]') as $value) {
			$this->$name = $this->subXML($value->asXML());
		}
	}
	
	/**
	 * Getting $content of formatted expression
	 * "<tag attributes>$content</tag>". Tag will
	 * automatically determined.
	 *
	 * @return content of the tag
	 */
	protected function subXML($s){
		// Position of the first enclosure >
		$pos_first = strpos($s, '>');
		// Position of the last opening <
		$pos_last = strrpos($s, '<');
		return substr($s, $pos_first + 1, $pos_last - $pos_first - 1);
	}

	
	/**
	 * Parses a linkedIn profile URL using SimpleXML
	 * 
	 * @param string Profile URL
	 */
	protected function parse($profile_url) {
		// Create a stream
		$opts = array(
			'http'=>array(
				'method'=>"GET",
				'header'=>"Accept-language: ".$this->_language."\r\n"
			)
		);
		$context = stream_context_create($opts);
		$html_string = file_get_contents($profile_url, false, $context);
		// Import the HTML into DOM before giving it to simpleXML
		$doc = new DOMDocument('1.0');
		@$doc->loadHTML($html_string);
		$this->_xml = simplexml_import_dom($doc);
		if($this->_xml) {
			$elements = array(
				array('span', 'given-name', 'firstname'),
				array('span', 'family-name', 'lastname'),
				array('p', 'headline title summary', 'current_status'),
				array('p', 'locality', 'current_locality'),
				array('p', 'skills', 'skills'),
				array('p', 'summary', 'summary'),
				array('p', 'interests', 'interests')
			);
			
			foreach($elements as $element) {
				$this->search_and_assign($element[0], $element[1], $element[2]);
			}
			
			// See http://wordpress.org/support/topic/plugin-linkedin-sc-missing-headline-title
			if(empty($this->current_status)) {
				$this->search_and_assign('p', 'headline title', 'current_status');
			}
			
			// Get profile picture if any
			foreach($this->_xml->xpath('//img[@class="photo"]') as $photo) {
				$this->photo = $photo['src'];
			}
			
			// Get experiences
			$this->experiences = array();
			foreach($this->_xml->xpath('//li[@class="experience vevent vcard"]') as $experience) {
				$exp = new stdClass();
				$exp->title = trim($experience->h3);
				$exp->organization = new stdClass();
				if($experience->h4->a) {
					$exp->organization->name = trim($experience->h4->a);
					$exp->organization->link = $experience->h4->a['href'];
				} else {
					$exp->organization->name = trim($experience->h4);
				}
				foreach($experience->xpath('.//p[@class="organization-details"]') as $sector) {
					$exp->organization->sector = trim(strtr($sector, '()', '  '));
				}
				foreach($experience->xpath('.//abbr[@class="dtstart"]') as $start) {
					$exp->period->start = $start['title'];
				}
				foreach($experience->xpath('.//abbr[@class="dtend"]') as $end) {
					$exp->period->end = $end['title'];
				}
				foreach($experience->xpath('.//p[@class="description"]') as $description) {
					$exp->description = $this->subXML($description->asXML());
				}
				$this->experiences[] = $exp;
			}
			
			// Get education
			$this->education = array();
			foreach($this->_xml->xpath('//li[@class="education vevent vcard"]') as $education) {
				$ed = new stdClass();
				$ed->title = trim($education->h3);
				foreach($education->xpath('.//span[@class="degree"]') as $degree) {
					$ed->degree = $degree;
				}
				foreach($education->xpath('.//span[@class="major"]') as $major) {
					$ed->major = $major;
				}
				foreach($education->xpath('.//abbr[@class="dtstart"]') as $start) {
					$ed->period->start = $start['title'];
				}
				foreach($education->xpath('.//abbr[@class="dtend"]') as $end) {
					$ed->period->end = $end['title'];
				}
				foreach($education->xpath('.//p[@class="notes"]') as $notes) {
					$ed->notes = $this->subXML($notes->asXML());
				}
				$this->education[] = $ed;
			}
		}
	}
}
