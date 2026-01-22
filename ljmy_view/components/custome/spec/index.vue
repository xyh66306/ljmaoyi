<template>
	<view v-if="spesData">
		<template v-for="(item, index, z) in setArr()">
			<view class="goods-specs" :key="index">
				<text class="pop-m-title">{{ index }}</text>
				<view class="pop-m-bd">
					<view
						v-for="(spess, key) in item"
						:key="key"
						@click="specChangeSpes({ index, key, spess })"
						:class="[spess.cla, { img: (spess.image_id && spess.image_id.length > 0), diable: spess.disable }]"
					>
						<image :src="changeSymbol(spess.image_id[0].src)" mode="aspectFill" v-if="spess.image_id && spess.image_id.length > 0"></image>
						{{ spess.name.replace(/====/g, '.') }}
					</view>
				</view>
			</view>
		</template>
	</view>
</template>
<script>
export default {
	name: 'spec',
	props: {
		// 默认picker选中项索引
		spesData: {
			// required: true
		},
		list: {
			type: Array,
			default: () => []
		},
		type: {
			type: Number | String,
			default: 0
		},
		specProduct: {
			type: [Object, Array],
			default: () => {}
		}
	},
	data() {
		return {
			specList: {}
		};
	},
	watch: {
		spesData: function (val) {
			if (typeof val == 'object') {
				this.specList = val;
			} else {
				let d = JSON.parse(val);
				this.specList = d;
			}
		}
	},
	methods: {
		changeSymbol(v) {
			return v.replace(/====/g, '.');
		},
		setArr() {
			let val = this.spesData;
			if (typeof val == 'object') {
				return val;
			} else {
				let d = JSON.parse(val);
				return d;
			}
		},
		specChangeSpes(val) {
			let v = val.index;
			let k = val.key;
			let spess = val.spess;
			if (spess.disable) {
				return;
			}
			// k = k.replace(/====/g, '.')
			let newData = { v, k };

			console.log('newData', newData);
			this.$emit('changeSpes', newData);
		}
	}
};
</script>
<style lang="scss" scoped>
.pop-m-item.diable {
	color: #cccccc;
}
.tag-wrap {
	display: flex;
	flex-wrap: wrap;
	padding: 20rpx;
	padding-top: 0;
	.tag-item {
		background: #ffecec;
		padding: 10rpx;
		color: $gray-5;
		font-size: 22rpx;
		margin-right: 10rpx;
		border-radius: 5rpx;
		margin-bottom: 10rpx;
	}
	&.type2 {
		padding-left: 0;
		padding-bottom: 0;
		.tag-item {
			border-radius: 24rpx;
			padding: 10rpx 16rpx;
		}
	}
}
.goods-specs,
.goods-number {
	padding: 26rpx;
	border-top: 1px solid #f3f3f3;
}
.goods-specs:first-child {
	border: none;
}
.pop-m-title {
	margin-right: 10rpx;
	color: #666;
}
.pop-m-item {
	display: inline-block;
	float: left;
	color: #1a1a1a;
	height: 60rpx;
	line-height: 60rpx;
	@include line-height(50rpx);
	text-align: center;
	margin-right: 24rpx;
	margin-bottom: 10rpx;
	// border-radius: 10rpx;
	background: rgba(154, 154, 154, 0.1);
	border: 2rpx solid transparent;
	padding: 0 40rpx;
	font-size: 32rpx;
	&.selected {
		border: 2rpx solid #ff4444;
		background: rgba(255, 68, 68, 0.05);
	}
	&.img {
		overflow: hidden;
		width: 224rpx;
		height: 280rpx;
		display: flex;
		flex-direction: column;
		align-items: center;
		margin-right: 10rpx;
		box-sizing: border-box;

		&:nth-child(3n) {
			margin-right: 0;
		}
		image {
			width: 224rpx;
			height: 224rpx;
		}
	}
}
.pop-m-bd {
	overflow: hidden;
	margin-top: 10rpx;
}
.is-selected {
	// background-color: $gray-10;
	color: $color-main;
	border: 2rpx solid currentColor;
	background: rgba(255, 68, 68, 0.05);
}
.not-selected {
	// border: 2rpx solid #ccc;
	// color: #ccc;
}
.none {
	border: 2rpx dashed #ccc;
	color: #888;
}
</style>
