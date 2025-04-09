<?php declare(strict_types = 0);
/*
** Zabbix
** Copyright (C) 2001-2023 Zabbix SIA
**
** This program is free software; you can redistribute it and/or modify
** it under the terms of the GNU General Public License as published by
** the Free Software Foundation; either version 2 of the License, or
** (at your option) any later version.
**
** This program is distributed in the hope that it will be useful,
** but WITHOUT ANY WARRANTY; without even the implied warranty of
** MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
** GNU General Public License for more details.
**
** You should have received a copy of the GNU General Public License
** along with this program; if not, write to the Free Software
** Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
**/


/**
 * Gauge chart widget form view.
 *
 * @var CView $this
 * @var array $data
 */

use Zabbix\Widgets\Fields\CWidgetFieldGraphDataSet;

$lefty_units = new CWidgetFieldSelectView($data['fields']['value_units']);
$lefty_static_units = (new CWidgetFieldTextBoxView($data['fields']['value_static_units']))
	->setPlaceholder(_('value'))
	->setWidth(ZBX_TEXTAREA_TINY_WIDTH);
$agg_type = new CWidgetFieldSelectView($data['fields']['agg_type']);
$items_shape = new CWidgetFieldRadioButtonListView($data['fields']['items_shape']);

$form = new CWidgetFormView($data);

$form
	->addField(
		new CWidgetFieldMultiSelectItemView($data['fields']['itemid'])
	)
	->addField(
		new CWidgetFieldCheckBoxView($data['fields']['adv_conf'])
	)
	->addField(
		new CWidgetFieldColorView($data['fields']['chart_color']),
		'js-advanced-configuration'
	)
	->addField(
		new CWidgetFieldTimePeriodView($data['fields']['time_period']),
		'js-advanced-configuration'
	)
	->addItem([
		$agg_type->getLabel()->addClass('js-advanced-configuration'),
		(new CFormField($agg_type->getView()))->addClass('js-advanced-configuration')
	])
	->addField(
		new CWidgetFieldNumericBoxView($data['fields']['value_min']),
		'js-advanced-configuration'
	)
	->addField(
		new CWidgetFieldNumericBoxView($data['fields']['value_max']),
		'js-advanced-configuration'
	)
	->addItem([
		$items_shape->getLabel()->addClass('js-advanced-configuration'),
		(new CFormField($items_shape->getView()))->addClass('js-advanced-configuration')
	])
	->addItem([
		$lefty_units->getLabel()->addClass('js-advanced-configuration'),
		(new CFormField([
			$lefty_units->getView()->addClass(ZBX_STYLE_FORM_INPUT_MARGIN),
			$lefty_static_units->getView()
		]))->addClass('js-advanced-configuration')
	])
	->addField(
		new CWidgetFieldTextBoxView($data['fields']['description']),
		'js-advanced-configuration'
	)
	->includeJsFile('widget.edit.js.php')
	->addJavaScript('widget_calendar_form.init('.json_encode([
		'color_palette' => [
			'FF465C', 'FFD54F', '0EC9AC', '524BBC', 'ED1248', 'D1E754', '2AB5FF', '385CC7', 'EC1594', 'BAE37D',
			'6AC8FF', 'EE2B29', '3CA20D', '6F4BBC', '00A1FF', 'F3601B', '1CAE59', '45CFDB', '894BBC', '6D6D6D'
		]
	], JSON_THROW_ON_ERROR).');')
	->show();
