<template>
	<view class="content">
		<view class="content-bg" v-if="status && paymentInfo.status === 2">
			<view class="bg bg-common"></view>
			<view class="result-body">
				<view class="result-body-title">
					<view class="body-title-left">
						<image src="/static/images/icon.png"></image>
					</view>
					<view class="body-title-right">
						<view class="">
							支付成功!
						</view>
						<view class="">
							感谢您的购买
						</view>
					</view>
				</view> 
				<view class="result-body-detail">
					<view class="body-detail-price">
						<text style="font-size: 28rpx;">￥</text>
						<text>{{ paymentInfo.money || '' }}</text>
					</view>
					<view class="body-detail-btm">
						<view class="">
							下单时间：{{ paymentInfo.ctime }}
						</view>
						<view class="">
							支付方式：{{ paymentInfo.payment_name }}
						</view>
					</view>
				</view>
			</view>
		</view>
		<view class="content-bg" v-else-if="status && paymentInfo.status === 1">
			<view class="fail-bg bg-common"></view>
			<view class="result-body">
				<view class="result-body-title">
					<view class="body-title-left">
						<image src="/static/images/icon1.png"></image>
					</view>
					<view class="body-title-right">
						<view class="">
							支付失败!
						</view>
						<view class="">
							请重新支付
						</view>
					</view>
				</view>
				<view class="result-fail-detail">
					<view class="body-detail-price">
						<text style="font-size: 28rpx;">￥</text>
						<text>{{ paymentInfo.money || '' }}</text>
					</view>
					<view class="body-detail-btm">
						<view class="">
							下单时间：{{ paymentInfo.ctime }}
						</view>
						<view class="">
							支付方式：{{ paymentInfo.payment_name }}
						</view>
					</view>
				</view>
			</view>
		</view>
		<view class="result-btn">
			<button v-if="status && paymentInfo.status === 2" class="success-btn" @click="orderDetail()">查看详情</button>
			<button v-else-if="status && paymentInfo.status === 1" class="fail-btn" @click="orderDetail()">查看详情</button>
		</view>
	</view>
</template>

<script>
export default {
	data() {
		return {
			paymentId: 0,
			paymentInfo: {
			}, // 支付单详情
			orderId: 0,
			status: false,
			placeTime:''
		};
	},
	onLoad(options) {
		if (options.id) {
			this.paymentId = options.id;
		}
		if (options.order_id) {
			this.orderId = options.order_id;
		}
		this.getPaymentInfo();
	},
	methods: {
		getPaymentInfo() {
			console.log(this.orderId);
			if (!this.paymentId && this.orderId) {
				this.status = true;
				this.paymentInfo.money = '0.00';
				this.paymentInfo.status = 2;
				this.paymentInfo.type = 1;
				this.paymentInfo.payment_name = "消费券抵扣";
				this.paymentInfo.ctime = this.$common.getCurrentTime()
				return;
			}
			let data = {
				payment_id: this.paymentId
			};

			this.$api.paymentInfo(data, res => {
				if (res.status) {
					let info = res.data;
					if (info.payment_code === 'alipay') {
						info.payment_name = '支付宝支付';
					} else if (info.payment_code === 'wechatpay') {
						info.payment_name = '微信支付';
					} else if (info.payment_code === 'balancepay') {
						info.payment_name = '余额支付';
					} else if (info.payment_code === 'zero') {
						info.payment_name = '消费抵扣';
					}
					// 获取订单号
					if (info.rel.length) {
						for (let i = 0; i < info.rel.length; i++) {
							if (info.rel[i].source_id) {
								this.orderId = info.rel[i].source_id;
								break;
							}
						}
					}					
					if(info.ctime){
						info.ctime = this.$common.timeToDate(info.ctime)
					} else {
						var time = new Date();
						var year = time.getFullYear();
						var month = time.getMonth();
						var day = time.getDate();
						info.ctime = year+'-'+month+'-'+day						
					}
					
					this.status = true;
					this.paymentInfo = info;
				} else {
					this.$common.errorToShow(res.msg);
				}
			});
		},
		orderDetail() {
			if (this.orderId && this.paymentInfo.type === 1) {
				this.$common.redirectTo('/pages/member/order/orderdetail?order_id=' + this.orderId);
			} else if (this.paymentInfo.type === 2) {
				this.$common.redirectTo('/pages/member/balance/details');
			} else if (this.paymentInfo.type === 7) {
				uni.switchTab({
					url: '/pages/member/index/index'
				});
			}else if (this.paymentInfo.type === 5 || this.paymentInfo.type === 6) {
				uni.switchTab({
					url: '/pages/index/index'
				});
			}
		}
	}
};
</script>

<style lang="scss">
.content-bg {
	position: relative;
	padding-top: 150rpx;
}
.bg-common {
	position: absolute;
	top: 0;
	left: 0;
	width: 100%;
	height: 400rpx;
}
.bg {
	background: $theme-color;
}
.fail-bg {
	background: linear-gradient(rgb(254, 142, 90),rgb(254, 142, 150));
}

.result-body {
	position: relative;
	z-index: 100;
}
.result-body-title {
	display: flex;
	align-items: center;
	justify-content: center;
	color: #FFFFFF;
	margin-bottom:20rpx;
}
.body-title-left {
	width: 162rpx;
	height: 110rpx;
}
.body-title-left image {
	width: 100%;
	height: 100%;
}
.body-title-right {
	font-size: 36rpx;
	font-family: PingFang SC;
	font-weight: bold;
	color: #FFFFFF;
	line-height: 60rpx;
}
.body-title-right view:last-child {
	font-size: 28rpx;
}
.result-body-detail {
	width: 100%;
	height: 465rpx;
	background: url(../../../static/images/bg2.png) no-repeat;
	background-size: 100% 100%;
	padding: 80rpx 60rpx 0;
	color: #333333;
}
.result-fail-detail {
	width: 100%;
	height: 465rpx;
	background: url(../../../static/images/bg3.png) no-repeat;
	background-size: 100% 100%;
	padding: 80rpx 60rpx 0;
	color: #999999;
}
.body-detail-price {
	font-size: 42rpx;
	font-family: PingFang SC;
	font-weight: bold;
	text-align: center;
	padding-bottom: 50rpx;
	border-bottom: 1rpx solid #F2F3F5;
	margin-bottom: 50rpx;
}

.body-detail-btm {
	font-size: 28rpx;
	font-family: PingFang SC;
	font-weight: 500;
	line-height: 60rpx;
}
.result-btn button {
	width: 420rpx;
	height: 86rpx;
	border-radius: 43rpx;
	font-size: 28rpx;
	font-family: PingFang SC;
	font-weight: 500;
	color: #FFFFFF;
	text-align: center;
	line-height: 86rpx;
	margin: 50rpx auto;
}
.result-btn .success-btn {
	background: $theme-color;
}

.result-btn .fail-btn {
	background: linear-gradient(rgb(254, 142, 90),rgb(254, 142, 150));
}

.result {
	text-align: center;
	padding-top: 200upx;
}

.result-img {
	width: 140upx;
	height: 140upx;
	margin-bottom: 20upx;
}

.result-num {
	color: #666;
	font-size: 30upx;
	margin-bottom: 20upx;
}

.result-top {
	color: #666;
	font-size: 30upx;
	margin-bottom: 20upx;
}

.result-mid {
	margin-bottom: 60upx;
}

.result-bot .btn {
	margin-top: 40upx;
	font-size: 26upx;
	padding: 0 50upx;
}
</style>
