<?php

namespace publin\src;

use Exception;
use InvalidArgumentException;
use RuntimeException;

class PublicationModel {

	private $db;
	private $num;


	public function __construct(Database $db) {

		$this->db = $db;
	}


	public function getNum() {

		return $this->num;
	}


	/**
	 * @param       $mode
	 * @param array $filter
	 *
	 * @return Publication[]
	 */
	public function fetch($mode, array $filter = array()) {

		$publications = array();

		/* Gets the publications */
		$data = $this->db->fetchPublications($filter);
		$this->num = $this->db->getNumRows();

		foreach ($data as $key => $value) {

			/* Gets the publications' authors */
			$model = new AuthorModel($this->db);
			$authors = $model->fetch(false, array('publication_id' => $value['id']));

			if ($mode) {
				/* Gets the publications' keywords */
				$model = new KeywordModel($this->db);
				$keywords = $model->fetch(false, array('publication_id' => $value['id']));
			}
			else {
				$keywords = array();
			}

			$publication = new Publication($value, $authors, $keywords);
			$publications[] = $publication;
		}

		return $publications;
	}


	public function store(Publication $publication) {

		$data = $publication->getData();
		$authors = $publication->getAuthors();
		$keywords = $publication->getKeywords();

		/* Stores the authors */
		$author_ids = array();
		$model = new AuthorModel($this->db);
		foreach ($authors as $author) {
			$author_ids[] = $model->store($author);
		}
		/* Stores the key terms */
		$keyword_ids = array();
		$model = new KeywordModel($this->db);
		foreach ($keywords as $keyword) {
			$keyword_ids[] = $model->store($keyword);
		}
		/* Stores the type */
		if (isset($data['type'])) {
			$model = new TypeModel($this->db);
			$type = new Type(array('name' => $data['type']));
			$data['type_id'] = $model->store($type);
			unset($data['type']);
		}
		/* Stores the study field */
		if (isset($data['study_field'])) {
			$model = new StudyFieldModel($this->db);
			$study_field = new StudyField(array('name' => $data['study_field']));
			$data['study_field_id'] = $model->store($study_field);
			unset($data['study_field']);
		}
		/* Stores the journal */
		if (isset($data['journal'])) {
			$model = new JournalModel($this->db);
			$journal = new Journal(array('name' => $data['journal']));
			$data['journal_id'] = $model->store($journal);
			unset($data['journal']);
		}
		/* Stores the publisher */
		if (isset($data['publisher'])) {
			$model = new PublisherModel($this->db);
			$publisher = new Publisher(array('name' => $data['publisher']));
			$data['publisher_id'] = $model->store($publisher);
			unset($data['publisher']);
		}
		/* Stores the publication */
		$publication_id = $this->db->insertData('list_publications', $data);

		if (!empty($publication_id)) {

			/* Stores the relation between the publication and its authors */
			if (!empty($author_ids)) {
				$priority = 1; // TODO: really start with 1 and go up?
				foreach ($author_ids as $author_id) {
					$this->addAuthor($publication_id, $author_id, $priority);
					$priority++;
				}
			}
			/* Stores the relation between the publication and its key terms */
			if (!empty($keyword_ids)) {
				foreach ($keyword_ids as $keyword_id) {
					$this->addKeyword($publication_id, $keyword_id);
				}
			}

			return $publication_id;
		}
		else {
			// TODO: streamline this with the other Model classes
			throw new Exception('Error while inserting publication to DB');
		}
	}


	public function addAuthor($publication_id, $author_id, $priority) {

		if (!is_numeric($publication_id) || !is_numeric($author_id) || !is_numeric($priority)) {
			throw new InvalidArgumentException('params should be numeric');
		}

		$data = array('publication_id' => $publication_id,
					  'author_id'      => $author_id,
					  'priority'       => $priority);

		return $this->db->insertData('rel_publ_to_authors', $data);
	}


	public function addKeyword($publication_id, $keyword_id) {

		if (!is_numeric($publication_id) || !is_numeric($keyword_id)) {
			throw new InvalidArgumentException('params should be numeric');
		}

		$data = array('publication_id' => $publication_id,
					  'keyword_id' => $keyword_id);

		return $this->db->insertData('rel_publication_keywords', $data);
	}


	public function update($id, array $data) {
	}


	public function delete($id) {

		//TODO: this only works when no foreign key constraints fail
		if (!is_numeric($id)) {
			throw new InvalidArgumentException('param should be numeric');
		}
		$where = array('id' => $id);
		$rows = $this->db->deleteData('list_publications', $where);

		// TODO: how to get rid of these?
		if ($rows == 1) {
			return true;
		}
		else {
			throw new RuntimeException('Error while deleting role '.$id.': '.$this->db->error);
		}
	}


	public function removeAuthor($publication_id, $author_id) {

		if (!is_numeric($publication_id) || !is_numeric($author_id)) {
			throw new InvalidArgumentException('params should be numeric');
		}

		$where = array('publication_id' => $publication_id,
					   'author_id'      => $author_id);

		$rows = $this->db->deleteData('rel_publ_to_authors', $where);

		// TODO: How to get rid of this and move it to DB?
		if ($rows == 1) {
			return true;
		}
		else {
			throw new RuntimeException('Error removing author '.$author_id.' from publication '.$publication_id.': '.$this->db->error);
		}
	}


	public function removeKeyword($publication_id, $keyword_id) {

		if (!is_numeric($publication_id) || !is_numeric($keyword_id)) {
			throw new InvalidArgumentException('params should be numeric');
		}

		$where = array('publication_id' => $publication_id,
					   'keyword_id' => $keyword_id);

		$rows = $this->db->deleteData('rel_publication_keywords', $where);

		// TODO: How to get rid of this and move it to DB?
		if ($rows == 1) {
			return true;
		}
		else {
			throw new RuntimeException('Error removing keyword '.$keyword_id.' from publication '.$publication_id.': '.$this->db->error);
		}
	}


	public function getValidator() {

		$validator = new Validator();
		$validator->addRule('type', 'text', true, 'Type is required but invalid');
		$validator->addRule('study_field', 'text', true, 'Field of Study is required but invalid');
		$validator->addRule('date_published', 'date', true, 'Publication date is required but invalid');
		$validator->addRule('title', 'text', true, 'Title is required but invalid');
		// TODO: validate array with authors?
		// TODO: types

		return $validator;
	}
}
