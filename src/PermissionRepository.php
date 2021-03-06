<?php


namespace publin\src;

/**
 * Class PermissionRepository
 *
 * @package publin\src
 */
class PermissionRepository extends Repository {


	/**
	 * @param      $column
	 * @param      $comparator
	 * @param      $value
	 * @param null $function
	 *
	 * @return $this
	 */
	public function where($column, $comparator, $value, $function = null) {

		if ($column === 'role_id') {
			$table = 'roles_permissions';
			$this->join .= ' LEFT JOIN `roles_permissions` ON (`roles_permissions`.`permission_id` = self.`id`)';
		}
		else if ($column === 'user_id') {
			$this->select(true);
			$table = 'users_roles';
			$this->join .= ' LEFT JOIN `roles_permissions` ON (`roles_permissions`.`permission_id` = self.`id`)';
			$this->join .= ' LEFT JOIN `users_roles` ON (`users_roles`.`role_id` = `roles_permissions`.`role_id`)';
		}
		else {
			$table = 'self';
		}

		return parent::where($column, $comparator, $value, $function, $table);
	}


	/**
	 * @param bool $distinct
	 *
	 * @return $this
	 */
	public function select($distinct = false) {

		$distinct = ($distinct === true) ? ' DISTINCT' : '';
		$this->select = 'SELECT'.$distinct.' self.*';
		$this->from = 'FROM `permissions` self';

		return $this;
	}


	/**
	 * @return Permission[]
	 */
	public function find() {

		$result = parent::find();
		$permissions = array();

		foreach ($result as $row) {
			$permissions[] = new Permission($row);
		}

		return $permissions;
	}


	/**
	 * @return Permission|false
	 */
	public function findSingle() {

		if ($result = parent::findSingle()) {
			return new Permission($result);
		}
		else {
			return false;
		}
	}
}
