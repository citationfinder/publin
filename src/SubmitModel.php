<?php

namespace publin\src;

use Exception;

class SubmitModel {

	private $db;

	private $required_fields = array(
		'all'           => array('type', 'study_field', 'date_published', 'title', 'authors'),
		'article'       => array('journal'),
		'book'          => array('publisher'),
		'incollection'  => array('booktitle'),
		'inproceedings' => array('booktitle'),
		'masterthesis'  => array('institution'),
		'misc'          => array('howpublished'),
		'phdthesis'     => array('institution'),
		'techreport'    => array('institution'),
		'unpublished'   => array(),);

	private $optional_fields = array(
		'all' => array('keywords', 'abstract'),
		'article'       => array('publisher', 'volume', 'number', 'pages'),
		'book'          => array('volume', 'series', 'edition'),
		'incollection'  => array('publisher', 'pages'),
		'inproceedings' => array('publisher', 'pages'),
		'masterthesis'  => array(),
		'misc'          => array(),
		'phdthesis'     => array(),
		'techreport'    => array('number'),
		'unpublished'   => array(),);

	private $errors = array();
	private $publication;


	public function __construct(Database $db) {

		$this->db = $db;
	}


	public function getErrors() {

		return $this->errors;
	}


	public function getPublication() {

		return $this->publication;
	}


	public function formatPost(array $post) {

		$result = array();

		foreach ($post as $key => $value) {

			if ($key == 'authors' && !empty($value)) {
				$value = $this->rewriteArray($value);
				$value = array_filter($value);
				if ($value) {
					$result['authors'] = $value;
				}
			}
			if (($key == 'keywords' || $key == 'pages') && !empty($value)) {
				$value = array_filter($value);
				if ($value) {
					$result[$key] = $value;
				}
			}
			else if (!empty($value)) {
				$result[$key] = $value;
			}
		}

		return $result;
	}


	private function rewriteArray(array $input) {

		$result = array();
		$given_fields = array_keys($input);

		foreach ($given_fields as $field) {
			foreach ($input[$field] as $key => $value) {
				if (!empty($value)) {
					$result[$key][$field] = $value;
				}
			}
		}

		return $result;
	}


	public function formatImport($string, $format) {

		$result = FormatHandler::import($string, $format);

		return $result;
	}


	public function createNewPublication(array $input) {

		$data = array();
		$authors = array();
		$keywords = array();

		if (empty($input['type'])
			|| !in_array($input['type'], array_keys($this->required_fields))
		) {
			$this->errors[] = 'No or unknown type given';

			return false;
		}

		$required_fields = array_merge($this->required_fields['all'],
			$this->required_fields[$input['type']]);
		$optional_fields = array_merge($this->optional_fields['all'],
			$this->optional_fields[$input['type']]);

		$fields = array_merge($required_fields, $optional_fields);

		foreach ($fields as $field) {
			if (!empty($input[$field])) {

				/* validates authors and creates objects */
				if ($field == 'authors') {
					foreach ($input[$field] as $author_input) {
						$author = $this->createNewAuthor($author_input);
						if ($author) {
							$authors[] = $author;
						}
					}
				}

				/* validates keywords and creates objects */
				else if ($field == 'keywords') {
					foreach ($input[$field] as $keyword_input) {
						$keyword = $this->createNewKeyword($keyword_input);
						if ($keyword) {
							$keywords[] = $keyword;
						}
					}
				}

				/* reformats pages array into separate fields */
				else if ($field == 'pages') {
					$from = false;
					$to = false;

					if (isset($input['pages']['from']) && isset($input['pages']['to'])) {
						$from = $this->validateInput('from', $input['pages']['from']);
						$to = $this->validateInput('to', $input['pages']['to']);
					}

					if ($from && $to && ($from <= $to)) {
						$data['pages_from'] = $from;
						$data['pages_to'] = $to;
					}
					else {
						$this->errors[] = 'Invalid input for pages';
					}
				}

				/* validates all other fields */
				else {
					$value = $this->validateInput($field, $input[$field]);
					if ($value) {
						$data[$field] = $value;
					}
					else {
						$this->errors[] = 'Invalid input for '.$field;
					}
				}
			}
			else if (in_array($field, $required_fields)) {
				$this->errors[] = 'Missing input for '.$field.' required';
			}
		}

		if (empty($this->errors)) {
			$publication = new Publication($data, $authors, $keywords);
			$this->publication = $publication;

			return $publication;
		}
		else {
			return false;
		}
	}


	public function createNewAuthor(array $input) {

		$family = false;
		$given = false;

		if (isset($input['family'])) {
			$family = $this->validateInput('family', $input['family']);
		}
		if (isset($input['given'])) {
			$given = $this->validateInput('given', $input['given']);
		}

		if ($given && $family) {
			$author = new Author(array('given' => $given, 'family' => $family));

			return $author;
		}
		else if ($given) {
			$this->errors[] = 'The author '.$given.' needs a family name';

			return false;
		}
		else if ($family) {
			$this->errors[] = 'The author '.$family.' needs a given name';

			return false;
		}
		else {
			$this->errors[] = 'An author needs a given and a family name';

			return false;
		}
	}


	private function validateInput($key, $input) {

		$fields = array(
			'type'           => 'text',
			'study_field'    => 'text',
			'date_published' => 'date',
			'title'          => 'text',
			'journal'        => 'text',
			'booktitle'      => 'text',
			'publisher'      => 'text',
			'edition'        => 'text',
			'institution'    => 'text',
			'howpublished'   => 'text',
			'pages'          => 'array',
			'from'           => 'number',
			'to'             => 'number',
			'volume'         => 'number',
			'number'         => 'number',
			'series'         => 'number',
			'given'          => 'text',
			'family'         => 'text',
			'keywords' => 'array',
			'abstract'       => 'text',
			'name'           => 'text');

		$result = false;

		if (array_key_exists($key, $fields)) {

			switch ($fields[$key]) {

				case 'number':
					if ((is_numeric($input) && $input >= 0)) {
						$result = (int)$input;
					}
					break;

				case 'text':
					if (is_string($input)) {
						$input = trim($input);
						$input = stripslashes($input);
						$input = htmlspecialchars($input);

						if (!empty($input)) {
							$result = $input;
						}
					}
					break;

				case 'date':
					// TODO: test for date
					if (!empty($input)) {
						$result = $input;
					}
					break;

				default:
					throw new Exception('no content type defined for '.$key);
					break;
			}
		}
		else {
			throw new Exception('unknown field '.$key);
		}

		if ($result === false) {
			// $this -> errors[] = 'value for '.$key.' is incorrect';
		}

		return $result;
	}


	public function createNewKeyword($input) {

		$name = $this->validateInput('name', $input);

		if ($name) {
			$keyword = new Keyword(array('name' => $name));

			return $keyword;
		}
		else {
			$this->errors[] = 'Invalid input for keyword '.$input;

			return false;
		}
	}


	public function storePublication(Publication $publication) {

		$model = new PublicationModel($this->db);
		$model->store($publication);
	}


	public function createTypes() {

		$model = new TypeModel($this->db);

		return $model->fetch();
	}


	public function createStudyFields() {

		$model = new StudyFieldModel($this->db);

		return $model->fetch(false);
	}


}
