<template>
	<view class="uni-calendar">
		<picker mode="multiSelector" :value="current" :range="rangeCalendar" @change="bindCalendarChange" @columnchange="bindColumChange">
			<!-- <view class="uni-input">{{ dateTime }}</view> -->
			<input class="uni-input" type="text" :value="dateTime" placeholder="请选择" disabled="true" />
		</picker>
	</view>
</template>

<script>
export default {
	props: {
		fields: {
			type: String,
			default: 'day' //选择器粒度为年、月、日--year/month/day
		},
		currentDate: {
			type: String,
			default: '2000-01-01' //日期格式： yy-mm-dd
		},
		// startDate: {
		// 	type: Number,
		// 	default: 1970
		// },
		endDate: {
			type: Number,
			default: new Date().getFullYear()
		}
	},
	data() {
		return {
			current: [0, 0, 0], //年月日
			rangeCalendar: [],
			dateTime: '',
			month: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12], //月
			day: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31] //日
		};
	},

	created() {
		this.initCurrentDate(this.currentDate);
	},

	watch: {
		currentDate(newValue, oldValue) {
			//console.log(newValue, oldValue);
			this.initCurrentDate(newValue);
		}
	},

	methods: {
		//初始日期处理
		initCurrentDate(newValue) {
			var that = this;
			var year = [], //年
				m = that.month, //月
				d = that.day; //日
			for (var i = 2020; i <=2030; i++) {
				year.push(i);
			}

			switch (that.fields) {
				case 'day':
					that.rangeCalendar = [year, that.month, that.day]; //yy-mm-dd
					break;
				case 'month':
					that.rangeCalendar = [year, that.month]; //yy-mm
					break;
				case 'year':
					that.rangeCalendar = [year]; //yy
					break;
				default:
					break;
			}

			if (newValue && newValue != 'true' && newValue != '') {
				var cld = newValue.split('-');
				for (var k = 0; k < cld.length; k++) {
					var temp = year;
					if (k == 0) {
						temp = year;
					} else if (k == 1) {
						temp = m;
					} else {
						temp = d;
					}

					for (var n = 0; n < temp.length; n++) {
						if (parseInt(cld[k]) == temp[n]) {
							that.current[k] = n;
							break;
						}
					}
				}

				that.dateTime = newValue;
			}

			// console.log('默认日期下标', that.current);
			// console.log('默认日期数据', that.dateTime);

			this.$forceUpdate(); //强制刷新
		},

		//日历切换
		bindCalendarChange(e) {
			var that = this;
			// console.log('选择日期：', e.detail.value);
			var current = e.detail.value;

			var lastTime = that.dateTime;
			var range = that.rangeCalendar;

			switch (that.fields) {
				case 'day':
					lastTime = range[0][current[0]] + '-' + that.dateCheck(range[1][current[1]]) + '-' + that.dateCheck(range[2][current[2]]);
					break;
				case 'month':
					lastTime = range[0][current[0]] + '-' + that.dateCheck(range[1][current[1]]);
					break;
				case 'year':
					lastTime = range[0][current[0]];
					break;
				default:
					break;
			}

			that.current = current;
			that.dateTime = lastTime;

			that.$emit('calendarChange', lastTime);
		},

		//列的值改变时触发 columnchange 事件
		bindColumChange(e) {
			// console.log('列的值改变：', e);
			var that = this;
			var rangeCalendar = that.rangeCalendar;
			if (e.detail.column == 1) {
				var m = e.detail.value + 1;
				if (that.fields == 'day') {
					if (m == 4 || m == 6 || m == 9 || m == 11) {
						rangeCalendar[2] = that.day.slice(0, -1);
					} else if (m == 2) {
						rangeCalendar[2] = that.day.slice(0, -3);
					} else {
						rangeCalendar[2] = that.day;
					}

					that.rangeCalendar = rangeCalendar;
				}
			}

			this.$forceUpdate(); //强制刷新
			that.$emit('columChange', e.detail);
		},

		//日期转换
		dateCheck(e) {
			if (e < 10) {
				e = '0' + e;
			}
			return e;
		}
	}
};
</script>

<style lang="less">
.uni-calendar {
	margin: 0 auto;

	.uni-input {
		background: none;
	}
}
</style>
