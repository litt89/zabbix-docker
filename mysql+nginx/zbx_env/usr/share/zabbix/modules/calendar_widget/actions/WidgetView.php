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


namespace Modules\Calendar\Actions;

use API,
	CControllerDashboardWidgetView,
	CControllerResponseData;

class WidgetView extends CControllerDashboardWidgetView {

	protected function doAction(): void {
		$db_items = API::Item()->get([
			'output' => ['itemid', 'value_type', 'name', 'units'],
			'itemids' => $this->fields_values['itemid'],
			'webitems' => true,
			'filter' => [
				'value_type' => [ITEM_VALUE_TYPE_UINT64, ITEM_VALUE_TYPE_FLOAT]
			]
		]);

		$trend_values = [
			"values" => [],
			"units" => ""
		];

		if ($db_items) {
			$item = $db_items[0];

			$trends = API::Trend()->get([
				'output' => API_OUTPUT_EXTEND,
				'itemids' => $item['itemid'],
				'time_from' => $this->fields_values['time_period']['from_ts'],
				'time_till' => $this->fields_values['time_period']['to_ts'],
				'sortfield' => 'clock',
				'sortorder' => ZBX_SORT_DOWN
			]);

			if ($trends) {
				$trend_values["units"] = $item['units'];

				$agg_type = $this->fields_values['agg_type'];
				$value_field = "value_avg";
				if ($agg_type == 2) { # $agg_type = 'max'
					$value_field = "value_max";
				}
				if ($agg_type == 3) { # $agg_type = 'min'
					$value_field = "value_min";
				}

				foreach ($trends as $trend) {
					array_push(
						$trend_values["values"],
						[
							"clock" => $trend["clock"],
							"value" => $trend[$value_field],
						]
					);
				}
			}
		}

		$this->setResponse(new CControllerResponseData([
			'name' => $this->getInput('name', $this->widget->getName()),
			'history' => $trend_values,
			'fields_values' => $this->fields_values,
			'user' => [
				'debug_mode' => $this->getDebugMode()
			]
		]));
	}
}
