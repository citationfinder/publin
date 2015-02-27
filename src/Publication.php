<?php

namespace publin\src;

use InvalidArgumentException;

/**
 * Handles publication data.
 *
 * TODO: comment
 */
class Publication extends Object {

	/**
	 * @var    Author[]
	 */
	private $authors;


	/**
	 * @var    Keyword[]
	 */
	private $keywords;


	public function __construct(array $data, array $authors, array $keywords = array()) {

		parent::__construct($data);
		$this->setAuthors($authors);
		$this->setKeywords($keywords);
	}


	/**
	 * @return array|bool
	 */
	public function getTypeId() {

		return $this->getData('type_id');
	}


	/**
	 * Returns the field of study.
	 *
	 * @return string
	 */
	public function getStudyField() {

		return $this->getData('study_field');
	}


	/**
	 * @return array|bool
	 */
	public function getJournalId() {

		return $this->getData('journal_id');
	}


	/**
	 * @return array|bool
	 */
	public function getPublisherId() {

		return $this->getData('publisher_id');
	}


	/**
	 * @return string
	 */
	public function getIsbn() {

		return $this->getData('isbn');
	}


	/**
	 * @return string
	 */
	public function getDoi() {

		return $this->getData('doi');
	}


	/**
	 * @return array|bool
	 */
	public function getCopyright() {

		return $this->getData('copyright');
	}


	/**
	 * @return array|bool
	 */
	public function getBookId() {

		return $this->getData('book_id');
	}


	/**
	 * @param $divider
	 *
	 * @return string
	 */
	public function getPages($divider) {

		$pages_from = $this->getData('pages_from');
		$pages_to = $this->getData('pages_to');

		if ($pages_from && $pages_to) {
			return $pages_from.$divider.$pages_to;
		}
		else {
			return false;
		}
	}


	public function getFirstPage() {

		return $this->getData('pages_from');
	}


	public function getLastPage() {

		return $this->getData('pages_to');
	}


	/**
	 * Returns the date the publication was added.
	 *
	 * @param    string $format date format (optional)
	 *
	 * @return    string
	 */
	public function getDateAdded($format) {

		if ($this->getData('date_added')) {
			return date($format, strtotime($this->getData('date_added')));
		}
		else {
			return false;
		}
	}


	/**
	 * @return array
	 */
	public function toArray() {

		$data = array();
		$authors = array();
		$keywords = array();

		foreach ($this->getAuthors() as $author) {
			$authors[] = array('given' => $author->getFirstName(), 'family' => $author->getLastName());
		}

		foreach ($this->getKeywords() as $keyword) {
			$keywords[] = $keyword->getName();
		}

		$data['type'] = $this->getTypeName();
		$data['cite_key'] = 'todo';
		$data['title'] = $this->getTitle();
		$data['authors'] = $authors;
		$data['journal'] = $this->getJournalName();
		$data['booktitle'] = $this->getBookName();
		$data['publisher'] = $this->getPublisherName();
		$data['edition'] = $this->getEdition();
		$data['institution'] = $this->getInstitution();
		$data['school'] = $this->getSchool();
		$data['howpublished'] = $this->getHowpublished();
		$data['year'] = $this->getDatePublished('Y');
		$data['month'] = $this->getDatePublished('F');
		$data['volume'] = $this->getVolume();
		$data['pages']['from'] = $this->getData('pages_from');
		$data['pages']['to'] = $this->getData('pages_to');
		$data['number'] = $this->getNumber();
		$data['series'] = $this->getSeries();
		$data['abstract'] = $this->getAbstract();
		$data['note'] = $this->getNote();
		$data['address'] = $this->getAddress();
		$data['bibsource'] = 'publin alpha';
		// some more missing
		$data['keywords'] = $keywords;

		return $data;
	}


	/**
	 * Returns an array with the authors of this publication.
	 * The array consists of Author objects.
	 *
	 * @return    Author[]
	 */
	public function getAuthors() {

		return $this->authors;
	}


	/**
	 * @param array $authors
	 *
	 * @return bool
	 */
	public function setAuthors(array $authors) {

		$this->authors = array();

		foreach ($authors as $author) {

			if ($author instanceof Author) {
				$this->authors[] = $author;
			}
			else {
				throw new InvalidArgumentException('must be array with Author objects');
			}
		}

		return true;
	}


	/**
	 * Returns an array with the key terms of this publication.
	 * The array consists of KeyTerm objects.
	 *
	 * @return    Keyword[]
	 */
	public function getKeywords() {

		return $this->keywords;
	}


	/**
	 * @param array $keywords
	 *
	 * @return bool
	 */
	public function setKeywords(array $keywords) {

		$this->keywords = array();

		foreach ($keywords as $keyword) {

			if ($keyword instanceof Keyword) {
				$this->keywords[] = $keyword;
			}
			else {
				throw new InvalidArgumentException('must be array with KeyTerm objects');
			}
		}

		return true;
	}


	/**
	 * Returns the type.
	 *
	 * @return string
	 */
	public function getTypeName() {

		return $this->getData('type');
	}


	/**
	 * Returns the title of the publication.
	 *
	 * @return string
	 */
	public function getTitle() {

		return $this->getData('title');
	}


	/**
	 * @return string
	 */
	public function getJournalName() {

		return $this->getData('journal');
	}


	/**
	 * @return string
	 */
	public function getBookName() {

		return $this->getData('booktitle');
	}


	/**
	 * @return string
	 */
	public function getPublisherName() {

		return $this->getData('publisher');
	}


	/**
	 * @return string
	 */
	public function getEdition() {

		return $this->getData('edition');
	}


	/**
	 * @return string
	 */
	public function getInstitution() {

		return $this->getData('institution');
	}


	/**
	 * @return string
	 */
	public function getSchool() {

		return $this->getData('school');
	}


	/**
	 * @return string
	 */
	public function getHowpublished() {

		return $this->getData('howpublished');
	}


	/**
	 * Returns the publish date.
	 *
	 * @param    string $format date format (optional)
	 *
	 * @return    string
	 */
	public function getDatePublished($format) {

		if ($this->getData('date_published')) {
			return date($format, strtotime($this->getData('date_published')));
		}
		else {
			return false;
		}
	}


	/**
	 * @return string
	 */
	public function getVolume() {

		return $this->getData('volume');
	}


	/**
	 * @return string
	 */
	public function getNumber() {

		return $this->getData('number');
	}


	/**
	 * @return string
	 */
	public function getSeries() {

		return $this->getData('series');
	}


	/**
	 * Returns the abstract.
	 *
	 * @return string
	 */
	public function getAbstract() {

		return $this->getData('abstract');
	}


	/**
	 * @return string
	 */
	public function getNote() {

		return $this->getData('note');
	}


	/**
	 * @return string
	 */
	public function getAddress() {

		return $this->getData('address');
	}
}
