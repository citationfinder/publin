<?php

namespace publin\src;

/**
 * Class Author
 *
 * @package publin\src
 */
class Author extends ObjectWithPublications {

	protected $id;
	protected $user_id;
	protected $academic_title;
	protected $family;
	protected $given;
	protected $website;
	protected $contact;
	protected $about;


	public function getId() {

		return $this->id;
	}


	/**
	 * Returns the user id or 0, if there is no user id.
	 *
	 * @return    int
	 */
	public function getUserId() {

		return $this->user_id;
	}


	public function getData() {

		$data = array();
		foreach (get_class_vars($this) as $property => $value) {
			$data[$property] = $value;
		}

		return $data;
	}


	/**
	 * Returns the full name, consisting of academic title, first name and last name.
	 *
	 * @return    string
	 */
	public function getName() {

		if ($this->given && $this->family) {
			return $this->given.' '.$this->family;
		}
		else {
			return false;
		}
	}


	/**
	 * Returns the last name.
	 *
	 * @return    string
	 */
	public function getLastName() {

		return $this->family;
	}


	/**
	 * Returns the first name.
	 *
	 * @param    $short        boolean        Set true for first letters only (optional)
	 *
	 * @return    string
	 */
	public function getFirstName($short = false) {

		if ($this->given && $short) {
			// TODO: check preg_split vs. implode
			$names = preg_split("/\s+/", $this->given);
			$string = '';
			foreach ($names as $name) {
				$string .= mb_substr($name, 0, 1).'.';
			}

			return $string;
		}
		else if ($this->given) {
			return $this->given;
		}
		else {
			return false;
		}
	}


	/**
	 * Returns the website.
	 *
	 * @return    string
	 */
	public function getWebsite() {

		return $this->website;
	}


	/**
	 * Returns the contact info.
	 *
	 * @return    string
	 */
	public function getContact() {

		return $this->contact;
	}


	/**
	 * Returns the author's text.
	 *
	 * @return    string
	 */
	public function getText() {

		return $this->about;
	}
}
