<?php

class Model_filters extends CI_Model
{

	function addFilterObj($filter, $values = false)
	{
		if (!empty($filter['title']) && !empty($filter['name'])) {
			/*проверка существования фильтра в таблице фильтров*/
			$this->db->select('id');
			$this->db->where('name', $filter['name']);
			if ($this->db->get('filters')->num_rows > 0) {
				return "Фильтр с таким алиасом уже существует.";
			}
			$dbins = array(
				'title' => $filter['title'],
				'name' => $filter['name'],
				'comment' => $filter['comment'],
				'show_type' => $filter['show_type'],
				'multiselect' => $filter['multiselect'],
				'multilanguage' => $filter['multilanguage'],
				'classes' => $filter['classes'],
				'active' => $filter['active']
			);
			if ($this->db->insert('filters', $dbins)) {
				if (!empty($values)) {
					//var_dump($values);
					$this->db->select('id');
					$this->db->where('name', $filter['name']);
					$filter_id = $this->db->get('filters')->result_array();
					if ($filter_id[0]['id']) {
						$this->addValues($filter_id[0]['id'], $values);
					}
				}
			} else
				return "Не удалось произвести вставку данных";
		} else
			return "Не задано имя или алиас фильтра";
	}

	function addValues($filter_id, $values)
	{
		if ($filter_id > -1 && !empty($values)) {
			if (is_array($values) && !isset($values['value'])) {
				$dbins = array();
				for ($i = 0; $i < count($values); $i++) {
					$dbins[] = array(
						'filter_id' => $filter_id,
						'value' => $values[$i]['value'],
						'num' => ($values[$i]['num']) ? $values[$i]['num'] : '0',
						'active' => '1'
					);
				}

				return ($this->db->insert_batch('filters_values', $dbins)) ? true : false;
			} else {
				$dbins = array(
					'filter_id' => $filter_id,
					'value' => $values['value'],
					'num' => ($values['num']) ? $values['num'] : '0',
					'active' => '1'
				);
				return ($this->db->insert('filters_values', $dbins)) ? true : false;
			}
		}
		return false;
	}

	function editFilterObj($filter)
	{
		$old_name = $this->getDataByAttributes('filters', array(0 => 'name', 'id' => $filter['id']));
		$dbupd = array();
		if ($old_name[0]['name'] != $filter['name']) {
			$alreadyExists = $this->getDataByAttributes('filters', array(0 => 'id', 'name' => $filter['name']));
			if (!$alreadyExists)
				$dbupd['name'] = $filter['name'];
			else
				return "Фильтр с таким алиасом уже существует.";
		}
		$dbupd['title'] = $filter['title'];
		$dbupd['comment'] = $filter['comment'];
		$dbupd['show_type'] = $filter['show_type'];
		$dbupd['multiselect'] = $filter['multiselect'];
		$dbupd['multilanguage'] = $filter['multilanguage'];
		$dbupd['classes'] = $filter['classes'];
		$this->db->where('id', $filter['id']);
		return ($this->db->update('filters', $dbupd)) ? true : "Не удалось изменить данные фильтра.";
	}

	function editValueObj($value)
	{
		$dbupd = array(
			'value' => $value['value'],
			'num' => $value['num']
		);
		$this->db->where('id', $value['id']);
		return ($this->db->update('filters_values', $dbupd)) ? true : "Не удалось изменить значение";
	}

	function addValuesToProduct($product_id, $values_id)
	{
		if ($product_id > -1 && !empty($values)) {
			if (is_array($values) && !isset($values['value'])) {
				$dbins = array();
				for ($i = 0; $i < count($values_id); $i++) {
					$dbins[] = array(
						'prod_id' => $product_id,
						'val_id' => $values_id[$i]
					);
				}

				return ($this->db->insert_batch('products_values', $dbins)) ? true : false;
			} else {
				$dbins = array(
					'prod_id' => $product_id,
					'val_id' => $values_id
				);
				return ($this->db->insert('products_values', $dbins)) ? true : false;
			}
		}
		return false;
	}

	function getFilters($per_page = -1, $from = -1, $order_by = 'ASC')
	{
		if ($per_page != -1 && $from != -1)
			$this->db->limit($per_page, $from);
		$this->db->order_by('num', $order_by);
		$filters = $this->db->get('filters')->result_array();
		if ($filters) {
			for ($i = 0; $i < count($filters); $i++) {
				$filters[$i]['values'] = $this->getValuesByFilterId($filters[$i]['id']);
			}
			return $filters;
		} else {
			return false;
		}
	}

	function getFilterByField($fieldValue, $fieldName = 'id', $withValues = true, $on_product = -1, $filterFields = -1, $valuesFields = -1)
	{
		if (($filterFields != -1)) {
			foreach ($filterFields as $field) {
				$this->db->select($field);
			}
		}
		$this->db->where($fieldName, $fieldValue);
		if ($on_product != -1)
			$this->db->where('on_product', $on_product);
		$filter = $this->db->get('filters')->result_array();
		if ($filter) {
			if (is_array($withValues)) {
				for ($i = 0; $i, count($withValues); $i++) {
					if (strlen($withValues[$i]) > 0)
						$filter[0]['values'] = $this->getValueById($withValues[$i], (($valuesFields != -1)) ? $valuesFields : null);
				}
			} elseif (is_string($withValues)) {
				$needed_vals = explode('|', $withValues);
				for ($i = 0; $i, count($needed_vals); $i++) {
					if (strlen($needed_vals[$i]) > 0)
						$filter[0]['values'] = $this->getValueById($needed_vals[$i], (($valuesFields != -1)) ? $valuesFields : null);
				}
			} elseif ($withValues === true)
				$filter[0]['values'] = $this->getValuesByFilterId($filter[0]['id']);
			return $filter[0];
		} else {
			return false;
		}
	}

	function getFilterById($id, $withValues = true, $on_product = -1, $filterFields = -1, $valuesFields = -1, $mainKey = -1)
	{
		if (($filterFields != -1)) {
			foreach ($filterFields as $field) {
				$this->db->select($field);
			}
		}
		$this->db->where('id', $id);
		if ($on_product != -1)
			$this->db->where('on_product', $on_product);
		$filter = $this->db->get('filters')->result_array();
		if ($filter) {
			if (is_array($withValues)) {
				for ($i = 0; $i, count($withValues); $i++) {
					if (strlen($withValues[$i]) > 0)
						$filter[0]['values'] = $this->getgetValueById($withValues[$i], (($valuesFields != -1)) ? $valuesFields : null);
				}
			} elseif (is_string($withValues)) {
				$needed_vals = explode('|', $withValues);
				for ($i = 0; $i, count($needed_vals); $i++) {
					if (strlen($needed_vals[$i]) > 0)
						$filter[0]['values'] = $this->getValueById($needed_vals[$i], (($valuesFields != -1)) ? $valuesFields : null);
				}
			} elseif ($withValues === true)
				$filter[0]['values'] = $this->getValuesByFilterId($filter[0]['id'], (($valuesFields != -1)) ? $valuesFields : null);
			if ($mainKey != -1)
				if ($filter[0][$mainKey])
					return array($filter[0][$mainKey] => $filter[0]);
			return $filter[0];
		} else {
			return false;
		}
	}

	function getFilterByName($name, $withValues = true, $on_product = -1, $filterFields = -1, $valuesFields = -1, $mainKey = -1)
	{
		if (($filterFields != -1)) {
			foreach ($filterFields as $field) {
				$this->db->select($field);
			}
		}
		$this->db->where('name', $name);
		if ($on_product != -1)
			$this->db->where('on_product', $on_product);
		$filter = $this->db->get('filters')->result_array();
		if ($filter) {

			if (is_array($withValues)) {
				for ($i = 0; $i < count($withValues); $i++) {
					$withValues[$i] = validate($withValues[$i], 'number');
					if ($withValues[$i] !== false)
						$filter[0]['values'][] = $this->getValueById($withValues[$i], (($valuesFields != -1)) ? $valuesFields : null);
				}
			} elseif (is_string($withValues)) {
				$needed_vals = explode('|', $withValues);
				for ($i = 0; $i < count($needed_vals); $i++) {
					$needed_vals[$i] = validate($needed_vals[$i], 'number');
					if ($needed_vals[$i] !== false)
						$filter[0]['values'][] = $this->getValueById($needed_vals[$i], (($valuesFields != -1)) ? $valuesFields : null);
				}
			} elseif ($withValues === true)
				$filter[0]['values'] = $this->getValuesByFilterId($filter[0]['name'], (($valuesFields != -1)) ? $valuesFields : null);
			if ($mainKey != -1)
				if ($filter[0][$mainKey])
					return array($filter[0][$mainKey] => $filter[0]);
			return $filter[0];
		} else {
			return false;
		}
	}

	function getValueById($id, $fields = null)
	{
		if (!empty($fields))
			foreach ($fields as $field)
				$this->db->select($field);
		$this->db->where('id', $id);
		$value = $this->db->get('filters_values')->result_array();
		return (isset($value[0])) ? $value[0] : false;
	}

	function getDataByAttributes($table, $arr_attributes = null, $arr_order_by = null, $per_page = -1, $from = -1)
	{
		if (!empty($table) && !empty($arr_attributes)) {
			foreach ($arr_attributes as $attr => $data) {
				if (is_string($attr)) {
					$this->db->where($attr, $data);
				} elseif (is_numeric($attr)) {
					$this->db->select($data);
				}
			}
			if (!empty($arr_order_by) && is_array($arr_order_by)) {
				foreach ($arr_order_by as $sort => $attrs) {
					$this->db->order_by($attrs, $sort);
				}
			}
			if ($per_page != -1 && $from != -1)
				$this->db->limit($per_page, $from);
			$data = $this->db->get($table)->result_array();

			return $data;
		}
	}

	function getValuesByFilterId($id, $order_by = 'asc')
	{
		if (!empty($id) && $id > -1) {
			$this->db->select('filters_values.id, filters_values.value, filters_values.num, filters_values.active');
			$this->db->from('filters_values');
			$this->db->where('filters_values.filter_id', $id);
			$this->db->order_by('filters_values.num', $order_by);
			$values = $this->db->get()->result_array();
			return $values;
		} else {
			return false;
		}
	}

	function getFiltersByCategoryId($cat_id, $filterFields = -1, $valuesFields = 0, $mainKey = -1, $on_product = -1)
	{
		$this->db->select('filter_id');
		$this->db->where('cat_id', $cat_id);
		$filtersId = $this->db->get('filters_categories')->result_array();
		if ($filtersId) {
			$filters = array();
			for ($i = 0; $i < count($filtersId); $i++) {
				$filter = $this->getFilterByField($filtersId[$i]['filter_id'], 'id', (($valuesFields) ? true : false), $on_product, $filterFields, $valuesFields, $mainKey);
				if (isset($filter['name']))
					$filters[$filter['name']] = $filter;
				else
					$filters[] = $filter;
			}
			return $filters;
		}
	}

	function deleteFiltresById($id)
	{
		if (isset($id)) {
			if (is_array($id)) {
				for ($i = 0; $i < count($id); $i++) {
					$this->deleteAllValuesFromProduct($id[$i]);
					$this->db->delete('filters_values', array('filter_id' => $id[$i]));
					$this->db->delete('filters_categories', array('filter_id' => $id[$i]));
					$this->db->delete('filters', array('id' => $id[$i]));
				}
			} else {
				$this->deleteAllValuesFromProduct($id);
				$this->db->delete('filters_values', array('filter_id' => $id));
				$this->db->delete('filters_categories', array('filter_id' => $id));
				$this->db->delete('filters', array('id' => $id));
			}
			return true;
		}
		return false;
	}

	function deleteValuesById($id)
	{
		if (isset($id)) {
			if (is_array($id)) {
				for ($i = 0; $i < count($id); $i++) {
					$this->db->delete('filters_values', array('filter_id' => $id[$i]));
					$this->db->delete('products_values', array('val_id' => $id[$i]));
				}
			} else {
				$this->db->delete('filters_values', array('id' => $id));
				$this->db->delete('products_values', array('val_id' => $id));
			}

			return true;
		}
		return false;
	}

	function deleteAllValuesFromProduct($filterId)
	{
		$query = "DELETE
					FROM products_values
					USING
						products_values LEFT JOIN filters_values
							ON products_values.val_id = filters_values.id
					WHERE filters_values.filter_id = %d";
		$this->db->query(sprintf($query, $filterId));
	}
	////////////////////////
	// CLIENT FUNCTIONS

	function getValueName($id)
	{
		$this->db->where('id', $id);
		$this->db->limit(1);
		$ret = $this->db->get('filters_values')->result_array();
		if (!$ret)
			return false;
		else
			return $ret[0]['value'];
	}

	function getProductFiltersValuesId($prodId, $mainKey = 'id')
	{
		$this->db->select('pv.val_id, fv.value, f.name');
		$this->db->from('filters f, products_values pv, filters_values fv');
		$this->db->where('f.id = fv.filter_id AND pv.val_id = fv.id');
		$this->db->where('pv.prod_id', $prodId);
		$this->db->order_by('f.name', 'ASC');
		$vals = $this->db->get()->result_array();
		$res = array();
		for ($i = 0; $i < count($vals); $i++) {
			if ($mainKey == 'id')
				$res[$vals[$i]['name']][$vals[$i]['val_id']] = $vals[$i]['value'];
			else {
				$res[$vals[$i]['name']][]['id'] = $vals[$i]['val_id'];
				$res[$vals[$i]['name']][count($res[$vals[$i]['name']]) - 1]['value'] = $vals[$i]['value'];
			}
		}
		return $res;
	}

	function changeActive($id, $table_name, $active = 1)
	{
		$this->db->set('active', $active);
		$this->db->where('id', $id);
		if ($this->db->update($table_name)) {
			return true;
		}
	}

	// attach/remove FILTERS-CATEGORIES
	function attachFilterToCategory($categoryId, $filterId)
	{
		return ($this->db->insert('filters_categories', array('cat_id' => $categoryId, 'filter_id' => $filterId))) ? true : false;
	}

	function removeFiltersFromCategory($categoryId, $filtersId = false)
	{
		$this->db->where('cat_id', $categoryId);
		if ($filtersId)
			if (is_array($filtersId)) {
				for ($i = 0; $i < count($filtersId); $i++) {
					$this->db->or_where('filter_id', $filtersId[$i]);
				}
			} elseif (is_numeric($filtersId)) {
				$this->db->where('filter_id', $filtersId);
			}
		return ($this->db->delete('filters_categories')) ? true : false;
	}

	//END FILTERS-CATEGORIES

	// attach/remove PRODUCTS-VALUES
	function attachValueToProduct($productId, $valueId)
	{
		return ($this->db->insert('products_values', array('prod_id' => $productId, 'val_id' => $valueId))) ? true : false;
	}

	function removeValuesFromProduct($productId, $valuesId = false)
	{
		$this->db->where('prod_id', $productId);
		if ($valuesId)
			if (is_array($valuesId)) {
				for ($i = 0; $i < count($valuesId); $i++) {
					$this->db->or_where('val_id', $valuesId[$i]);
				}
			} elseif (is_numeric($valuesId)) {
				$this->db->where('val_id', $valuesId);
			}
		return ($this->db->delete('products_values')) ? true : false;
	}
	//END PRODUCTS-VALUES

	//SEARCH
	function getFilteredProductsInCategory($fields, $values, $category_id = -1, $per_page = -1, $from = -1, $active = -1, $order_by = "ASC", $sort_by = 'id', $in_warehouse = false)
	{
		$this->db->select($fields);
		$this->db->distinct();
		$this->db->from('products p, products_values pv');
		if ($category_id != -1)
			$this->db->where('p.category_id', $category_id);
		$this->db->where('p.id = pv.prod_id');
		if ($active != -1)
			$this->db->where('p.active', $active);

		if ($in_warehouse)
			$this->db->where('p.in_warehouse', $in_warehouse);

		if ($per_page != -1 && $from != -1)
			$this->db->limit($per_page, $from);

		if ($sort_by != 'name' || $sort_by != 'content')
			$this->db->order_by($sort_by, $order_by);

		if ($values) {
			if (is_array($values)) {
				foreach ($values as $key => $vals) {
					if ($key == 'price') {
						if (isset($filters[$key]['max_price']) && isset($filters[$key]['min_price']))
							$this->db->where("price BETWEEN " . $values[$key]['min_price'] . " AND " . $values[$key]['max_price'], NULL, FALSE);
					} else {
						if (count($vals) > 1) {
							$this->db->where('(pv.val_id', $vals[0]);
							for ($i = 1; $i < count($vals); $i++)
								$this->db->or_where('pv.val_id', $vals[$i]);
							$this->db->ar_where[count($this->db->ar_where) - 1] .= ')';
						} else {
							$this->db->where('pv.val_id', $vals[0]);
						}
					}
				}
			} else {
				$this->db->where('pv.val_id', $values);
			}
			$products = $this->db->get()->result_array();

			if ($sort_by == 'name' || $sort_by == 'content')
				$products = $this->sortBySerializedField($products, $sort_by, $order_by);
		} else {
			return "Нет значений для фильтрации";
		}

		return (isset($products)) ? $products : "Не удалось получить товары";
	}

	function getCountFilteredProductsInCategory($values, $category_id = -1, $active = -1, $in_warehouse = false)
	{

		$this->db->select('count(*) AS count');
		$this->db->distinct();
		$this->db->from('products p, products_values pv');
		if ($category_id != -1)
			$this->db->where('p.category_id', $category_id);
		$this->db->where('p.id = pv.prod_id');
		if ($active != -1)
			$this->db->where('p.active', $active);

		if ($in_warehouse)
			$this->db->where('p.in_warehouse', $in_warehouse);

		if ($category_id != -1)
			$this->db->where('p.category_id', $category_id);

		if ($values) {
			if (is_array($values)) {
				foreach ($values as $key => $vals) {
					if ($key == 'price') {
						if (isset($filters[$key]['max_price']) && isset($filters[$key]['min_price']))
							$this->db->where("price BETWEEN " . $values[$key]['min_price'] . " AND " . $values[$key]['max_price'], NULL, FALSE);
					} else {
						if (count($vals) > 1) {
							$this->db->where('(pv.val_id', $vals[0]);
							for ($i = 1; $i < count($vals); $i++)
								$this->db->or_where('pv.val_id', $vals[$i]);
							$this->db->ar_where[count($this->db->ar_where) - 1] .= ')';
						} else {
							$this->db->where('pv.val_id', $vals[0]);
						}
					}
				}
			} else {
				$this->db->where('pv.val_id', $values);
			}
			$prodCount = $this->db->get()->result_array();
		} else {
			return "Нет значений для фильтрации";
		}

		return $prodCount[0]['count'];
	}
	//END SEARCH
}
