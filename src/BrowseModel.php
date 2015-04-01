<?php

namespace publin\src;

use Exception;

class BrowseModel {

	private $old_db;
	private $db;
	private $browse_list = array();
	private $result = array();
	private $browse_type;
	private $is_result = false;
	private $num = -9999;


	public function __construct(Database $db) {

		$this->old_db = $db;
		$this->db = new PDODatabase();
	}


	public function getNum() {

		return $this->num;
	}


	public function handle($type, $id) {

		if (!empty($type)) {

			$this->browse_type = $type;

			switch ($type) {

				case 'recent':
					$this->is_result = true;
					$model = new PublicationModel($this->old_db);
					$this->result = $model->findAll(20);
					$this->num = $model->getNum();
					break;

				case 'author':
					$repo = new AuthorRepository($this->db);
					$this->browse_list = $repo->select()->order('family', 'ASC')->find();
					break;

				case 'keyword':
					$repo = new KeywordRepository($this->db);
					$this->browse_list = $repo->select()->order('name', 'ASC')->find();
					break;

				case 'study_field':
					$model = new StudyFieldModel($this->old_db);
					$this->browse_list = $model->fetch();
					break;

				case 'type':
					if ($id > 0) {
						$this->is_result = true;
						$model = new PublicationModel($this->old_db);
						$this->result = $model->findByType($id);
						$this->num = $model->getNum();
					}
					else {
						$model = new TypeModel($this->old_db);
						$this->browse_list = $model->fetch();
					}
					break;

				case 'year':
					if ($id > 0) {

						$this->is_result = true;
						$model = new PublicationModel($this->old_db);
						$this->result = $model->findByYear($id);
						$this->num = $model->getNum();

					}
					else {
						$this->browse_list = $this->fetchYears();
					}
					break;

				default:
					throw new Exception('unknown browse type "'.$type.'"');

					break;
			}
		}
	}


	private function fetchYears() {

		$query = 'SELECT DISTINCT YEAR(`date_published`) AS `year`
					FROM `list_publications`
					ORDER BY `year` DESC';

		$data = $this->old_db->getData($query);

		$this->num = $this->old_db->getNumRows();

		$years = array();

		foreach ($data as $key => $value) {
			$years[] = $value['year'];
		}

		return $years;

	}


	/**
	 * Returns the browse type.
	 *
	 * @return    string
	 */
	public function getBrowseType() {

		return $this->browse_type;
	}


	/**
	 * Returns the browse list.
	 *
	 * @return    array
	 */
	public function getBrowseList() {

		return $this->browse_list;
	}


	/**
	 * Returns the browse results.
	 *
	 * @return    array
	 */
	public function getBrowseResult() {

		return $this->result;
	}


	/**
	 * Returns true if there is a browse result.
	 *
	 * This is used to determine whether the result list or the browse list should be shown,
	 * so this returns true even if the browse result is empty.
	 *
	 * @return    boolean
	 */
	public function isBrowseResult() {

		return $this->is_result;
	}

}
