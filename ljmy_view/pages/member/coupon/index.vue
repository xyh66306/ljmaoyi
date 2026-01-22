<template>
	<view class="content">
		<uni-segmented-control :current="current" :values="items" @clickItem="onClickItem" style-type="text" active-color="#333"></uni-segmented-control>
		<view class="" v-if="listData.length > 0">
			<view class="coupon-c-item" v-for="(item, key) in listData" :key="key">
				<view class="cci-l">
					<view class="cci-l-c color-f" v-if="current == 0">
						coupon
					</view>
					<view class="cci-l-c color-f color-b" v-if="current != 0">
						coupon
					</view>
				</view>
				<view class="cci-r">
					<view class="cci-r-c">
						<view class="ccirc-t color-9 one-line">
							{{item.name}}
						</view>
						<view class="ccirc-b">
							<view class="ccirc-b-l">
								<view class="ccirc-b-tip">
									{{ item.expression1 + item.expression2 }}
								</view>
								<view class="ccirc-b-time color-9">
									有效期：{{item.stime}} - {{item.etime}}
								</view>
							</view>
							<view class="ccirc-b-r color-f" @click="goIndex" v-if="current == 0">
								立即使用
							</view>
						</view>
					</view>
				</view>
			</view>
			<uni-load-more :status="loadStatus"></uni-load-more>
		</view>
		<view class="coupon-none" v-else>
			<image class="coupon-none-img" src="/static/image/order.png" mode=""></image>
		</view>
	</view>
</template>

<script>
	import uniLoadMore from '@/components/uni-load-more/uni-load-more.vue'
	import uniSegmentedControl from "@/components/uni-segmented-control/uni-segmented-control.vue";
	export default {
		components: {
			uniSegmentedControl,
			uniLoadMore
		},
		data() {
			return {
				items: ['未使用', '已使用'],
				current: 0,
				page: 1,
				limit: 10,
				listData: [],
				loadStatus: 'more'
			}
		},
		onLoad() {
			this.getData();
		},
		onReachBottom() {
			if (this.loadStatus === 'more') {
				this.getData();
			}
		},
		methods: {
			// tab点击切换
			onClickItem(index) {
				if (this.current !== index) {
					this.current = index;
					this.page = 1;
					this.listData = [];
					this.getData();
				}
			},
			//获取优惠券列表
			getData() {
				this.loadStatus = 'loading'
				let data = {
					page: this.page,
					limit: this.limit
				}
				if (this.current == 0) {
					data['display'] = 'no_used';
				}
				if (this.current == 1) {
					data['display'] = 'yes_used';
				}
				if (this.current == 2) {
					data['display'] = 'invalid';
				}
				this.$api.userCoupon(data, res => {
					if (res.status) {
						let now_type = 'no_used';
						if (this.current == 1) {
							now_type = 'yes_used';
						}
						if (this.current == 2) {
							now_type = 'invalid';
						}
						if (now_type == res.data.q_type) {
							if (res.data.page >= this.page) {
								let newList = this.listData.concat(res.data.list);
								this.listData = newList;
								if (res.data.count > this.listData.length) {
									this.page++
									this.loadStatus = 'more'
								} else {
									this.loadStatus = 'noMore'
								}
							}
						}
					} else {
						this.$common.errorToShow(res.msg);
					}
				});
			},
			//跳转首页
			goIndex() {
				uni.navigateTo({
					url: '/pages/index/index'
				});
			}
		}
	}
</script>

<style>
	.coupon-c-item {
		margin: 30rpx 20rpx;
		height: 150rpx;
		margin-bottom: 20rpx;
	}

	.cci-l {
		width: 60rpx;
		height: 100%;
		background-color: #FF7159;
		font-size: 32rpx;
		display: inline-block;
		box-sizing: border-box;
		float: left;
		border-top-left-radius: 16rpx;
		border-bottom-left-radius: 16rpx;
	}

	.cci-l-c {
		height: 60rpx;
		line-height: 44rpx;
		width: 150rpx;
		text-align: center;
		transform-origin: 30rpx 30rpx;
		transform: rotate(90deg);
	}

	.cci-r {
		position: relative;
		height: 150rpx;
		width: calc(100% - 70rpx);
		margin-left: 10rpx;
		display: inline-block;
		background-color: #fff;
	}

	.cci-r-img {
		position: absolute;
		width: 100%;
		height: 100%;
		background-color: #fff;
	}

	.cci-r-c {
		position: relative;
		z-index: 99;
	}

	.ccirc-t {
		font-size: 24rpx;
		padding: 10rpx 20rpx;
	}

	.ccirc-b {
		padding: 10rpx;
		position: relative;
	}

	.ccirc-b-l {
		display: inline-block;
		max-width: 540rpx;
	}

	.ccirc-b-tip {
		font-size: 28rpx;
		width: 100%;
		overflow: hidden;
		text-overflow: ellipsis;
		white-space: nowrap;
	}

	.ccirc-b-tip text {
		font-size: 34rpx;
	}

	.ccirc-b-time {
		font-size: 24rpx;
	}

	.ccirc-b-r {
		display: inline-block;
		background-color: #FF7159;
		font-size: 26rpx;
		padding: 4rpx 10rpx;
		border-radius: 4rpx;
		position: absolute;
		right: 12rpx;
		bottom: 44rpx;
	}

	.color-b {
		background-color: #e5e5e5;
		border-bottom-right-radius: 12rpx;
		border-bottom-left-radius: 12rpx;
		color: #fff;
	}

	.coupon-none {
		text-align: center;
		padding: 200rpx 0;
	}

	.coupon-none-img {
		width: 274rpx;
		height: 274rpx;
	}

</style>
