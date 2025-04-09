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


namespace Modules\Calendar\Includes;

use Modules\Calendar\Widget;

use Zabbix\Widgets\{
	CWidgetField,
	CWidgetsData,
	CWidgetForm
};

use Zabbix\Widgets\Fields\{
	CWidgetFieldCheckBox,
	CWidgetFieldColor,
	CWidgetFieldMultiSelectItem,
	CWidgetFieldTimePeriod,
	CWidgetFieldNumericBox,
	CWidgetFieldRadioButtonList,
	CWidgetFieldSelect,
	CWidgetFieldTextBox
};

/**
 * Gauge chart widget form.
 */
class WidgetForm extends CWidgetForm {

	public function addFields(): self {
		return $this
			->addField(
				(new CWidgetFieldMultiSelectItem('itemid', _('Item')))
					->setFlags(CWidgetField::FLAG_NOT_EMPTY | CWidgetField::FLAG_LABEL_ASTERISK)
					->setMultiple(false)
			)
			->addField(
				new CWidgetFieldCheckBox('adv_conf', _('Advanced configuration'))
			)
			->addField(
				(new CWidgetFieldColor('chart_color', _('Color')))->setDefault('FF0000')
			)
			->addField(
				(new CWidgetFieldTimePeriod('time_period', _('Time period')))
					->setDefaultPeriod(['from' => 'now-1y', 'to' => 'now'])
					->setFlags(CWidgetField::FLAG_NOT_EMPTY | CWidgetField::FLAG_LABEL_ASTERISK)
			)
			->addField(
				(new CWidgetFieldSelect('agg_type', _('Aggregation'), [
					0 => _('avg'),
					1 => _('sum'),
					2 => _('max'),
					3 => _('min')
				]))->setDefault(0)
				->setFlags(CWidgetField::FLAG_NOT_EMPTY | CWidgetField::FLAG_LABEL_ASTERISK)
			)
			->addField(
				(new CWidgetFieldNumericBox('value_min', _('Min')))
					->setDefault(0)
					->setFlags(CWidgetField::FLAG_NOT_EMPTY | CWidgetField::FLAG_LABEL_ASTERISK)
			)
			->addField(
				(new CWidgetFieldNumericBox('value_max', _('Max')))
					->setDefault(100)
					->setFlags(CWidgetField::FLAG_NOT_EMPTY | CWidgetField::FLAG_LABEL_ASTERISK)
			)
			->addField(
				(new CWidgetFieldRadioButtonList('items_shape', _('Shape'), [
					0 => _('Cube'),
					5 => _('Rounded'),
					100 => _('Circle')
				]))
				->setDefault(0)
			)
			->addField(
				(new CWidgetFieldSelect('value_units', _('Units'), [
					Widget::UNIT_AUTO => _x('Auto', 'history source selection method'),
					Widget::UNIT_STATIC => _x('Static', 'history source selection method')
				]))->setDefault(Widget::UNIT_AUTO)
			)
			->addField(
				(new CWidgetFieldTextBox('value_static_units'))
			)
			->addField(
				new CWidgetFieldTextBox('description', _('Description'))
			);
	}
}
