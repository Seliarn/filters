<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class Filters extends CI_Controller
{

	public function __construct()
	{
		parent::__construct();
		$this->load->helper('login_helper');
		isAdminLogin();
		$this->load->model('Model_admin', 'ma');
		$this->load->model('Model_filters', 'filters');
	}

	public function index()
	{
		if (isset($_POST['search'])) {
			$this->search($_POST['search']);
		}

		$data['title'] = "Фильтры";
		$data['filters'] = $this->filters->getFilters();
		$this->load->view('admin/filters', $data);
	}

	public function add()
	{
		$type = validate($_POST['type'], 'string', array('value', 'filter'));
		$opt_mlang = getOption('multilanguage');
		if ($type == 'filter') {
			$name = validate($_POST['name'], 'string');
			$name = translitRuToEn(substr($name, 0, 10));
			$title = validate($_POST['title'], 'string');
			if ($title === false || $name === false) {
				$result['done'] = false;
				$result['msg'] = "Не заданы основные данные фильтра (имя и алиас)";
			} else {
				/*проверка данных other_helpers->validate()*/
				$filter = array();
				$filter['name'] = $name;
				$filter['show_type'] = validate($_POST['show_type'], 'string', array('none', 'select', 'checkbox', 'radio'));
				$filter['num'] = validate($_POST['num'], 'number', '', false, 0);
				$filter['multiselect'] = (!empty($_POST['multiselect']) && ($_POST['multiselect'] === 'on' || $_POST['multiselect'] == '1')) ? 1 : 0;
				$filter['active'] = 1;
				$filter['classes'] = validate($_POST['classes'], 'string', '', true);

				$comment = validate($_POST['comment'], 'string', '', true);
				/*TODO make multilanguage*/
				if ($opt_mlang) {
					$filter['multilanguage'] = (!empty($_POST['multilanguage']) && ($_POST['multilanguage'] === 'on' || $_POST['multilanguage'] === 1)) ? 1 : 0;
					$curr_lang = validate((!empty($_POST['curr_lang'])) ? $_POST['curr_lang'] : '', 'string');
				} else {
					$curr_lang = false;
					$filter['multilanguage'] = 0;
				}

				if ($curr_lang !== false) {
					if ($title !== false) {
						$filter['title'] = serialize($this->setMultilangData($title, $filter['title'], $curr_lang));
					}
					if ($comment !== false) {
						$filter['comment'] = serialize($this->setMultilangData($comment, $filter['comment'], $curr_lang));
					}
				} else {
					if ($filter['multilanguage'] == 1) {
						if ($title !== false) {
							$filter['title'] = serialize($this->setMultilangData($title, $filter['title']));
						}
						if ($comment !== false) {
							$filter['comment'] = serialize($this->setMultilangData($comment, $filter['comment']));
						}
					} else {
						if ($title !== false)
							$filter['title'] = $title;
						if ($comment !== false)
							$filter['comment'] = $comment;
					}
				}
				foreach ($filter as $key => $val) {
					if ($filter[$key] === false)
						unset($filter[$key]);
				}

				$inp_values = $_POST['values'];
				if ($inp_values) {
					$values = array();
					for ($i = 0; $i < count($inp_values['value']); $i++) {
						if (!empty($inp_values['value'][$i])) {
							$tmp = validate($inp_values['value'][$i], 'string');
							if ($tmp === false)
								continue;
							$values[$i]['value'] = $tmp;
							$values[$i]['num'] = ($tmp = validate($inp_values['num'][$i], 'number')) ? $tmp : 0;
						}
					}
					if (!count($values))
						$values = false;
				} else {
					$values = false;
				}
				$add_done = $this->filters->addFilterObj($filter, $values);
				if (is_string($add_done)) {
					$result['done'] = false;
					$result['msg'] = $add_done;
				} elseif ($add_done) {
					$filter['values'] = $values;
					redirect("/admin/filters/");
					echo json_encode($filter);
				}
			}
		} elseif ($type == 'value') {
			/*TODO make multilanguage */
			$value['value'] = validate($_POST['value'], 'string');
			$value['num'] = ($tmp = validate($_POST['num'], 'number')) ? $tmp : 0;
			$value['filter_id'] = validate($_POST['filter_id'], 'number');
			if (($value['value'] !== false) && $this->filters->addValues($value['filter_id'], $value)) {
				$newValId = $this->filters->getDataByAttributes('filters_values',
					array(
						0 => 'id',
						'value' => $value['value'],
						'num' => $value['num'],
						'filter_id' => $value['filter_id']
					));
				$value['id'] = $newValId[0]['id'];
				$value['msg'] = "Значение добавлено";
				$value['done'] = true;
				echo json_encode($value);
				exit();
			}
		}

		redirect("/admin/filters/");
	}

	public function edit()
	{
		$type = validate($_POST['type'], 'string', array('value', 'filter'));
		$opt_mlang = getOption('multilanguage');
		if ($type == 'filter') {
			$filter_id = validate($_POST['id'], 'number');
			if ($filter_id === false) {
				$result['done'] = false;
				$result['msg'] = "Не задан ИД фильтра";
			} else {
				$filter = $this->filters->getFilterById($filter_id, false);
				if ($filter) {
					/*проверка данных other_helpers->validate()*/
					$filter['name'] = validate($_POST['name'], 'string');
					$filter['show_type'] = validate($_POST['show_type'], 'string', array('none', 'select', 'checkbox', 'radio'));
					$filter['num'] = validate($_POST['num'], 'number', '', false, 0);
					$filter['multiselect'] = (!empty($_POST['multiselect']) && ($_POST['multiselect'] === 'on' || $_POST['multiselect'] === 1)) ? 1 : 0;
					$filter['classes'] = validate($_POST['classes'], 'string', '', true);
					$title = validate($_POST['title'], 'string');
					$comment = validate($_POST['comment'], 'string');

					if ($opt_mlang) {
						$filter['multilanguage'] = (!empty($_POST['multilanguage']) && ($_POST['multilanguage'] === 'on' || $_POST['multilanguage'] === 1)) ? 1 : 0;
						$curr_lang = validate((!empty($_POST['curr_lang'])) ? $_POST['curr_lang'] : '', 'string');
					} else {
						$curr_lang = false;
						$filter['multilanguage'] = 0;
					}

					if ($curr_lang !== false) {
						if ($title !== false) {
							$filter['title'] = serialize($this->setMultilangData($title, $filter['title'], $curr_lang));
						}
						if ($comment !== false) {
							$filter['comment'] = serialize($this->setMultilangData($comment, $filter['comment'], $curr_lang));
						}
					} else {
						if ($filter['multilanguage'] == 1) {
							if ($title !== false) {
								$filter['title'] = serialize($this->setMultilangData($title, $filter['title']));
							}
							if ($comment !== false) {
								$filter['comment'] = serialize($this->setMultilangData($comment, $filter['comment']));
							}
						} else {
							if ($title !== false && $filter['title'] != $title)
								$filter['title'] = $title;
							else
								unset($filter['title']);
							if ($comment !== false && $filter['comment'] != $comment)
								$filter['comment'] = $comment;
							else
								unset($filter['comment']);
						}
					}
					foreach ($filter as $key => $val) {
						if ($filter[$key] === false)
							unset($filter[$key]);
					}
					if ($this->filters->editFilterObj($filter)) {
						$result['done'] = true;
						$result['msg'] = "Изменения приняты";
					}
				} else {
					$result['done'] = false;
					$result['msg'] = "Фильтр[$filter_id] не найден";
				}
			}
		} elseif ($type == 'value') {
			$id = validate($_POST['id'], 'number');
			if ($id === false) {
				$result['done'] = false;
				$result['msg'] = "Не задан ИД фильтра";
			} else {
				$value = $this->filters->getValueById($id, array('value', 'id', 'num'));
				if ($value) {
					$curr_lang = validate((!empty($_POST['curr_lang'])) ? $_POST['curr_lang'] : '', 'string');
					$newValue = validate($_POST['value'], 'string');
					$value['num'] = validate($_POST['num'], 'number');
					if ($curr_lang !== false) {
						if ($newValue !== false) {
							$value['value'] = serialize($this->setMultilangData($newValue, $value['value'], $curr_lang));
						}
					} else {
						if ($newValue !== false && $value['value'] != $newValue)
							$value['value'] = $newValue;
					}
					foreach ($value as $key => $val) {
						if ($value[$key] === false)
							unset($value[$key]);
					}
					if ($this->filters->editValueObj($value)) {
						$result['done'] = true;
						$result['msg'] = "Изменения приняты";
					}
				} else {
					$result['done'] = false;
					$result['msg'] = "Значение[$id] не найдено";
				}
			}
		} else {
			$result['done'] = false;
			$result['msg'] = "Тип [$type] не определен";
		}

		echo json_encode($result);
	}

	public function delete()
	{
		$type = validate($_POST['type'], 'string', array('value', 'filter'));
		$id = validate($_POST['id'], 'number');

		if ($type === false || $id === false) {
			$result['done'] = false;
			$result['msg'] = "Ошибка в передаваемых параметрах.";
		} else {
			if ($type == 'value') {
				if ($this->filters->deleteValuesById($id)) {
					$result['done'] = true;
					$result['msg'] = "Параметр успешно удален.";
				}
			} elseif ($type == 'filter') {
				if ($this->filters->deleteFiltresById($id)) {
					$result['done'] = true;
					$result['msg'] = "Фильтр со всеми параметрами успешно удален.";
				}
			}
		}

		echo json_encode($result);
	}

	public function activate()
	{
		$type = validate($_POST['type'], 'string', array('value', 'filter'));
		$id = validate($_POST['id'], 'number');
		if ($type !== false)
			echo ($type == 'filter') ?
				$this->filters->activate($id, 'filters') :
				$this->filters->activate($id, 'filters_values');
		exit();
	}

	public function deactivate()
	{
		$type = validate($_POST['type'], 'string', array('value', 'filter'));
		$id = validate($_POST['id'], 'number');
		if ($type !== false)
			echo ($type == 'filter') ?
				$this->filters->deactivate($id, 'filters') :
				$this->filters->deactivate($id, 'filters_values');
		exit();
	}

	public function search($search)
	{

	}

	function getFiltersByCategoryId()
	{
		$categories = $_POST['categories'];
		if ($categories) {
			if (is_array($categories)) {
				for ($i = 0; $i < count($categories); $i++) {
					$filters[] = $this->filters->getFiltersByCategoryId($categories[$i], array('id', 'name', 'title'), array('id', 'value'), 'name');
				}

			} else {
				$filters = $this->filters->getFiltersByCategoryId($categories, array('id', 'name', 'title'), array('id', 'value'), 'name');
			}

			ob_start();
			showFilters_adminpage($filters);

			echo ob_get_clean();
		}
	}

	public function selectFilterInCategory()
	{
		$filterId = validate($_POST['filter_id'], 'number');
		$categoryId = validate($_POST['category_id'], 'number');
		if ($filterId !== false && $categoryId !== false) {
			$this->filters->attachFilterToCategory($categoryId, $filterId);
			$result['done'] = true;
			$result['msg'] = "Фильтр подключен";
		} else {
			$result['done'] = false;
			$result['msg'] = "Ошибка подключения фильтра";
		}
		echo json_encode($result);
	}

	public function unselectFilterInCategory()
	{
		$filterId = validate($_POST['filter_id'], 'number');
		$categoryId = validate($_POST['category_id'], 'number');
		if ($filterId !== false && $categoryId !== false) {
			$this->filters->deleteAllValuesFromProduct($filterId);
			$this->filters->removeFiltersFromCategory($categoryId, $filterId);
			$result['done'] = true;
			$result['msg'] = "Фильтр отключен";
		} else {
			$result['done'] = false;
			$result['msg'] = "Ошибка отключения фильтра";
		}
		echo json_encode($result);
	}

	public function selectValueInProduct()
	{
		$productId = validate($_POST['product_id'], 'number');
		$valueId = validate($_POST['value_id'], 'number');
		if ($productId !== false && $valueId !== false) {
			$this->filters->attachValueToProduct($productId, $valueId);
			$result['done'] = true;
			$result['msg'] = "Значение подключено";
		} else {
			$result['done'] = false;
			$result['msg'] = "Ошибка подключения значения";
		}
		echo json_encode($result);
	}

	public function unselectValueInProduct()
	{
		$productId = validate($_POST['product_id'], 'number');
		$valueId = validate($_POST['value_id'], 'number');
		if ($productId !== false && $valueId !== false) {
			$this->filters->removeValuesFromProduct($productId, $valueId);
			$result['done'] = true;
			$result['msg'] = "Значение отключено";
		} else {
			$result['done'] = false;
			$result['msg'] = "Ошибка отключения значения";
		}
		echo json_encode($result);
	}

	private function setMultilangData($newValue, $data, $lang = false)
	{
		$langs = getOptionArray('languages');
		if (!isset($data)) {
			if ($lang) {
				$data[$lang] = $newValue;
			} else {
				$data[$langs[0]] = $newValue;
			}
		} else {
			$old_val = @unserialize($data);
			if ($lang) {
				if ($old_val) {
					$old_val[$lang] = $newValue;
					$data = $old_val;
				} else {
					$old_val = $data;
					unset($data);
					$data[$langs[0]] = $old_val;
					$data[$lang] = $newValue;
				}
			} else {
				if ($old_val) {
					$old_val[$langs[0]] = $newValue;
					$data = $old_val;
				} else {
					unset($data);
					$data[$langs[0]] = $newValue;
				}
			}
		}
		return $data;
	}

}
