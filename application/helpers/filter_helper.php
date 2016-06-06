<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

function parseFilters($obj, $assocKeys = false, $sortByName = false, $filterFields = -1, $valuesFields = -1)
{
	$CI = & get_instance();
	$filters = array();
	if ($assocKeys) {
		foreach ($obj as $key => $val) {
			if ((strpos($key, 'filter_')) !== false) {
				$filterName = substr($key, strlen('filter_'));
				$filter = $CI->model_filters->getFilterByName($filterName, $val, 1, $filterFields, $valuesFields);
				if ($filter)
					$filters[$filterName] = $filter;
				unset($obj[$key]);
			}
		}

		if ($sortByName) {
			ksort($filters);
		}
	} else {
		foreach ($obj as $key => $val) {
			if ((strpos($key, 'filter_')) !== false) {
				$filterName = substr($key, strlen('filter_'));
				$filter = $CI->model_filters->getFilterByName($filterName, $val, 1, $filterFields, $valuesFields);
				if ($filter)
					$filters[] = $filter;
				unset($obj[$key]);
				if ($sortByName) {
					$control_arr[] = $filterName;
				}
			}
		}
		if ($sortByName && count($control_arr) > 1) {
			array_multisort($control_arr, SORT_ASC, SORT_STRING, $filters);
		}
	}
	$obj['filters'] = $filters;
	return $obj;
}

function cmpFilterValues($filters_1, $filters_2)
{
	if (count($filters_1) != count($filters_2))
		return false;

	foreach ($filters_1 as $key => $vals) {
		if (count($filters_1[$key]['values']) != count($filters_2[$key]['values']))
			return false;
		for ($v = 0; $v < count($filters_1[$key]['values']); $v++) {
			if ($filters_1[$key]['values'][$v] != $filters_2[$key]['values'][$v])
				return false;
		}
	}
	return true;
}

function searchInFilterValues($needle, $values, $key = 'id')
{
	for ($i = 0; $i < count($values); $i++) {
		if ($needle == $values[$i][$key])
			return $values[$i][$key];
	}
	return false;
}


function showFilters_adminpage($filters, $filtersValues = false)
{
	if ($filters) {
		?>
		<tr id="filters">
			<td valign="top"><strong>Фильтры:</strong></td>
			<td>
				<?php
				if (!isset($filters['title'])) {
					foreach ($filters as $filter) {
						?>
						<div style="float: left;">
							<?= getMultilangHtmlTags($filter['title'], 'span') ?><br/>
							<?= getMultilangHtmlTags($filter['values'], 'custom-select', 'filters', '', 'multiple size="6" data-filterName="' . $filter['name'] . '"', '', '', (isset($filtersValues[$filter['name']])) ? array_keys($filtersValues[$filter['name']]) : '') ?>
						</div>
					<?php
					}
				} else {
					?>
					<div style="float: left;">
						<?= getMultilangHtmlTags($filters['title'], 'span') ?><br/>
						<?= getMultilangHtmlTags($filters['values'], 'custom-select', 'filters', '', 'multiple size="6" data-filterName="' . $filters['name'] . '"', '', '', (isset($filtersValues[$filters['name']])) ? array_keys($filtersValues[$filters['name']]) : '') ?>
					</div>
				<?php
				} ?>
			</td>
		</tr>
	<?php
	}
}

function filtersToString($filters, $onlyVals = true)
{
	$filterString = '';
	$langs = getOptionArray('languages');
	if (isset($filters[0])) {
		for ($f = 0; $f < count($filters); $f++) {
			$filter = $filters[$f];
			if (!isset($filter['title'])) {
				$CI = & get_instance();
				if (isset($filter['id']))
					$fl = $CI->model_filters->getFilterByField($filter['id'], 'id', false, -1, array('title'));
				elseif (isset($filter['name']))
					$fl = $CI->model_filters->getFilterByField($filter['name'], 'name', false, -1, array('title'));

				if ($fl)
					$filter['title'] = getLangText($fl['title']);
				else
					continue;
			}
			$filterString .= $filter['title'];
			for ($v = 0; $v < count($filter['values']); $v++) {
				$value = $filter['values'][$v];
				if (!isset($value['value'])) {
					if (!$CI)
						$CI = & get_instance();
					if (isset($value['id']))
						$v = $CI->model_filters->getValueById($value['id'], array('value'));

					if ($v)
						$value['value'] = getLangText($v['value']);
					else
						continue;
				}
				$filterString .= ($v == (count($filter['values']) - 1)) ? $value['value'] : $value['value'] . ', ';
			}
			$filterString .= ';<br />';
		}
	} else {
		foreach ($filters as $filter) {
			if (!$onlyVals) {
				if (!isset($filter['title'])) {
					$CI = & get_instance();
					if (isset($filter['id']))
						$fl = $CI->model_filters->getFilterByField($filter['id'], 'id', false, -1, array('title'));
					elseif (isset($filter['name']))
						$fl = $CI->model_filters->getFilterByField($filter['name'], 'name', false, -1, array('title'));

					if ($fl)
						$filter['title'] = getLangText($fl['title']);
					else
						continue;
				}
				$filterString .= $filter['title'] . ': ';
			}
			for ($v = 0; $v < count($filter['values']); $v++) {
				$value = $filter['values'][$v];
				if (!isset($value['value'])) {
					if (!isset($CI))
						$CI = & get_instance();
					if (isset($value['id']))
						$vl = $CI->model_filters->getValueById($value['id'], array('value'));

					if ($vl)
						$value['value'] = getLangText($vl['value']);
					else
						continue;
				}
				$filterString .= ($v == (count($filter['values']) - 1)) ? $value['value'] : $value['value'] . ', ';
			}
			$filterString .= ';<br />';
		}
	}
	return $filterString;
}

function showFilter($filter, $position = 'inProductInfo', $tags = array('openFilterTag' => '', 'closeFilterTag' => '', 'openValuesTag' => '', 'closeValuesTag' => ''))
{
	if ($position == 'inProductInfo') {
		if (isset($filter['show_type'])) {
			switch ($filter['show_type']) {
				case 'checkbox':
					echo '<strong>' . getLangText(stripcslashes($filter['title'])) . ':</strong>
					<ul class = "' . stripcslashes($filter['classes']) . '">';
					for ($i = 0; $i < count($filter['values']); $i++) {
						echo '<li><input name="filter_' . $filter['name'] . '[]" type = "checkbox" value = "' . $filter['values'][$i]['id'] . '" ' . (($i == 0) ? 'checked' : '') . '><label>' . getLangText(stripcslashes($filter['values'][$i]['value'])) . '</label></li>'; //class = "disabled"
					}
					echo '</ul>';
					break;
				case 'select':
					echo '<strong>' . getLangText(stripcslashes($filter['title'])) . ': </strong>
					<select name="filter_' . stripcslashes($filter['name']) . '[]" id = "product-color" class = "' . stripcslashes($filter['classes']) . '">';
					for ($i = 0; $i < count($filter['values']); $i++) {
						echo '<option value = "' . $filter['values'][$i]['id'] . '"' . (($i == 0) ? 'selected' : '') . '>' . getLangText(stripcslashes($filter['values'][$i]['value'])) . '</option>'; //class = "disabled"
					}
					echo '</select>';
					break;
				case 'radio':
					break;
			}
		} else {
			echo $tags['openFilterTag'] . getLangText($filter['title']) . ': ' . $tags['closeFilterTag'];
			echo $tags['openValuesTag'];
			for ($i = 0; $i < count($filter['values']); $i++) {
				echo getLangText(stripcslashes($filter['values'][$i]['value'])) . (($i == (count($filter['values']) - 1)) ? '' : ', ');
			}
			echo $tags['closeValuesTag'];
		}
	}
}
