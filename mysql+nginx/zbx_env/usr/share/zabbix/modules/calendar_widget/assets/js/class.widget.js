class WidgetCalendar extends CWidget {

	static UNIT_AUTO = 0;
	static UNIT_STATIC = 1;

	static AGG_TYPES = [
		'mean',
		'sum',
		'max',
		'min'
	];

	onInitialize() {
		super.onInitialize();
		this._refresh_frame = null;
		this._treemap_container = null;
		this._canvas = null;
		this._chart_color = null;
		this._min = null;
		this._max = null;
		this._value = null;
		this._last_value = null;
		this._units = '';
	}

	processUpdateResponse(response) {
		function sortByDateAscending(a, b) {
			return a.clock - b.clock;
		}
		function aggregate(agg_type, values) {
			if (d3[agg_type] !== undefined)
				return d3[agg_type](values, d => Number(d.value));
			return d3.mean(values, d => Number(d.value));
		}
		if (response.history === null) {
			this._value = null;
			this._units = '';
		}
		else {
			this._values = []
			this._agg_type = WidgetCalendar.AGG_TYPES[response.fields_values.agg_type] !== undefined
				? WidgetCalendar.AGG_TYPES[response.fields_values.agg_type]
				: WidgetCalendar.AGG_TYPES[0];

			var values = response.history.values;
			console.log(response);

			values = d3.flatRollup(
				values,
				values => aggregate(this._agg_type, values),
				d => +d3.timeDay(new Date(Number(d.clock) * 1000))
			)
			.map(([clock, value]) => ({ clock, value }))
			.sort(sortByDateAscending);
			values.forEach((item) => {
				item.clock = new Date(item.clock);
			});
			this._values = values;
			this._units = response.fields_values.value_units == WidgetCalendar.UNIT_AUTO
				? response.history.units
				: response.fields_values.value_static_units;				
		}
		this._chart_color = response.fields_values.chart_color;
		this._shape = response.fields_values.items_shape;
		this._min = Number(response.fields_values.value_min);
		this._max = Number(response.fields_values.value_max);
		super.processUpdateResponse(response);
	}

	setContents(response) {
		if (this._canvas === null) {
			super.setContents(response);
			this._treemap_container = this._body.querySelector('.treemap');
			this._treemap_container.style.height = `${this._getContentsSize().height}px`;
			// this._treemap_container.style.height =
			// 	`${this._getContentsSize().height - this._body.querySelector('.description').clientHeight}px`;
			this._canvas = document.createElement('canvas');
			this._treemap_container.appendChild(this._canvas);
			this._resizeChart();
		}
		this._updatedChart(response);
	}

	onResize() {
		super.onResize();
		if (this._state === WIDGET_STATE_ACTIVE) {
			this._resizeChart();
		}
	}

	_resizeChart() {
		const ctx = this._canvas.getContext('2d');
		const dpr = window.devicePixelRatio;
		this._canvas.style.display = 'none';
		const size = Math.min(this._treemap_container.offsetWidth, this._treemap_container.offsetHeight);
		this._canvas.style.display = '';
		this._canvas.width = size * dpr;
		this._canvas.height = size * dpr;
		ctx.scale(dpr, dpr);
		this._canvas.style.width = `${size}px`;
		this._canvas.style.height = `${size}px`;
		this._refresh_frame = null;
		this._updatedChart();
	}

	_hexToRgb(hex) {
		var result = /^([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
		return result ? {
			r: parseInt(result[1], 16),
			g: parseInt(result[2], 16),
			b: parseInt(result[3], 16),
			a: 1
		} : null;
	}

	_get_color(v) {
		var c = this._hexToRgb(this._chart_color);
		c.a = Math.min(v / (this._max - this._min), 1);
		return `rgba(${c.r},${c.g},${c.b},${c.a})`;
	}

	_updatedChart(response) {
		this._new_func(response);
	}

	_new_func(response) {
		if (this._last_value === null) {
			this._last_value = this._min;
		}
		const start_time = Date.now();
		const end_time = start_time + 400;

		const animate = () => {
			const time = Date.now();
			if (time <= end_time) {
				// Define formatting functions for the axes and tooltips.
				const formatDate = d3.utcFormat("%Y-%m-%d");
				const formatDay = i => "SMTWTFS"[i];
				const formatMonth = d3.utcFormat("%b");

				// Helpers to compute a day's position in the week.
				const timeWeek = d3.utcMonday; 
				const countDay = i => (i + 6) % 7;

				// Compute the values used to color the cells: percent change is the difference between the day's
				// closing value and the previous day's, as a fraction of the latter.
				const data = d3.pairs(this._values, ({clock, value}) => ({
					date: clock,
					value: value
				}));

				// Compute the extent of the value, ignore the outliers
				// and define a diverging and symmetric color scale.
				const max_value = d3.max(data, function(d) { return d.value });
				const min_value = d3.min(data, function(d) { return d.value });
				if (min_value < this._min) {
					this._min = min_value;
				}
				if (max_value > this._max) {
					this._max = max_value;
				}

				// Group data by year, in reverse input order. (Since the dataset is chronological,
				// this will show years in reverse chronological order.)
				const years = d3.groups(data, d => d.date.getUTCFullYear()).reverse();

				// Specify the chart's dimensions.
				const width = this._getContentsSize().width - 10; // width of the chart
				const cellSize = Math.round((width - 26) / 52) - 1; // height of a day
				const height = (cellSize * 9) - (years.length * 7); // height of a week (5 days + padding)

				// A function that draws a thin white line to the left of each month.
				function pathMonth(t) {
					const d = Math.max(0, Math.min(7, countDay(t.getUTCDay())));
					const w = timeWeek.count(d3.utcYear(t), t);
					return `${d === 0 ? `M${w * cellSize},0`
						: d === 7 ? `M${(w + 1) * cellSize},0`
						: `M${(w + 1) * cellSize},0V${d * cellSize}H${w * cellSize}`}V${7 * cellSize}`;
				}

				const svg = d3.create("svg")
					.attr("width", width)
					.attr("height", height * years.length)
					.attr("viewBox", [0, 0, width, height * years.length])
					.attr("style", `max-width: 100%; height: auto; font: 10px sans-serif; width: calc(100% - ${(years.length * 10)}px);`);

				const year = svg.selectAll("g")
					.data(years)
					.join("g")
					.attr("transform", (d, i) => `translate(40.5,${height * i + cellSize * 1.5})`);

				year.append("text")
					.attr("x", -5)
					.attr("y", -5)
					.attr("font-weight", "bold")
					.attr("text-anchor", "end")
					.text(([key]) => key);

				year.append("g")
					.attr("text-anchor", "end")
					.selectAll()
					.data(d3.range(0, 7))
					.join("text")
					.attr("x", -5)
					.attr("y", i => (countDay(i) + 0.5) * cellSize)
					.attr("dy", "0.31em")
					.text(formatDay);

				year.append("g")
					.selectAll()
					//.data(([, values]) => values.filter(d => ![0, 6].includes(d.date.getUTCDay())))
					.data(([, values]) => values)
					.join("rect")
					.attr("width", cellSize - 1)
					.attr("height", cellSize - 1)
					.attr("x", d => timeWeek.count(d3.utcYear(d.date), d.date) * cellSize + 0.5)
					.attr("y", d => countDay(d.date.getUTCDay()) * cellSize + 0.5)
					.attr("fill", d => this._get_color(d.value))
					.attr("rx", `${this._shape / 10}%`)
					.append("title")
					.text(d => `${formatDate(d.date)}
					${d.value.toFixed(2)} ${this._units}`);

				const month = year.append("g")
					.selectAll()
					.data(([, values]) => d3.utcMonths(d3.utcMonth(values[0].date), values.at(-1).date))
					.join("g");

				month.filter((d, i) => i).append("path")
					.attr("fill", "none")
					.attr("stroke", "#fff")
					.attr("stroke-width", 2)
					.attr("d", pathMonth);

				month.append("text")
					.attr("x", d => timeWeek.count(d3.utcYear(d), timeWeek.ceil(d)) * cellSize + 2)
					.attr("y", -5)
					.text(formatMonth);

				this._body.innerHTML = '';
				this._body.appendChild(svg.node());

				requestAnimationFrame(animate);
			}
			else {
				this._last_value = this._value;
			}
		};

		requestAnimationFrame(animate);
	}
}
