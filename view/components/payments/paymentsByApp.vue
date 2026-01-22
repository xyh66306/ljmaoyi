<template>
	<view class="cell-group payment-method">
		<view
			class="cell-item add-title-item cell-item-mid right-img"
			v-for="item in payments"
			:key="item.code"
			@click="subForm(item.code)"
			v-if="!(type == 2 && item.code == 'balancepay')"
		>
			<view class="cell-item-hd">
				<image class="cell-hd-icon" :src="item.icon"></image>
			</view>
			<view class="cell-item-bd cell-item-bd-block">
				<view class="cell-bd-view">
					<text class="cell-bd-text">{{ item.name }}</text>
				</view>
				<view class="cell-bd-view">
					<text class="cell-bd-text address">{{ item.memo }}</text>
				</view>
			</view>
			<view class="cell-item-ft">
				<image class="cell-ft-next icon" src="/static/image/right.png"></image>
			</view>
		</view>


		<view class="payArea" v-if="showPay">
			<view class="pay-title">
				<text v-show="AffirmStatus === 1">请输入6位支付密码</text>
				<text v-show="AffirmStatus === 2">请设置6位支付密码</text>
				<text v-show="AffirmStatus === 3">请确认6位支付密码</text>
			</view>
			<view class="pay-password" @click="onPayUp">
				<view class="list">
					<text v-show="passwordArr.length >= 1">●</text>
				</view>
				<view class="list">
					<text v-show="passwordArr.length >= 2">●</text>
				</view>
				<view class="list">
					<text v-show="passwordArr.length >= 3">●</text>
				</view>
				<view class="list">
					<text v-show="passwordArr.length >= 4">●</text>
				</view>
				<view class="list">
					<text v-show="passwordArr.length >= 5">●</text>
				</view>
				<view class="list">
					<text v-show="passwordArr.length >= 6">●</text>
				</view>
			</view>
		</view>
		<cc-defineKeyboard ref="CodeKeyboard" passwrdType="pay" @KeyInfo="KeyInfo"></cc-defineKeyboard>	

	</view>
</template>

<script>
import { apiBaseUrl } from '@/config/config.js';
export default {
	props: {
		exp:{
			type: Number,
			default() {
				return 2;
			}
		},		
		// 如果是商品订单此参数必须
		orderId: {
			type: String,
			default() {
				return '';
			}
		},
		// 如果是充值订单此参数必须
		recharge: {
			type: Number,
			default() {
				return 0;
			}
		},
		// 用户id
		uid: {
			type: Number,
			default() {
				return 0;
			}
		},
		// 订单类型
		type: {
			type: Number,
			default() {
				return 1;
			}
		}
	},
	data() {
		return {
			code:'',
			showPay:false,
			AffirmStatus: 1,
			passwordArr: [],
			oldPasswordArr: [],
			newPasswordArr: [],
			afPasswordArr: [],				
			payments: [],
			popShow: false
		};
	},
	mounted() {
		this.getPayments();
	},
	methods: {
		/** * 唤起键盘 */
		onPayUp() {
			this.showPay = true
			this.$refs.CodeKeyboard.show();
		},
		closePayUp() {
			this.passwordArr =[]
			this.showPay = false
			this.$refs.CodeKeyboard.hide();
			
		},			
		KeyInfo(val) {
			let that = this;
			
			if (val.index >= 6) {
				return;
			}
			// 判断是否输入的是删除键
			if (val.keyCode === 8) {
				// 删除最后一位
				that.passwordArr.splice(val.index + 1, 1)
			}
			// 判断是否输入的是.
			else if (val.keyCode == 190) {
				// 输入.无效
			} else {
				that.passwordArr.push(val.key);
			}
			if (val.index == 5) {
				let pwd_str = that.passwordArr.join('');
				that.$api.authPaypwd({
					paypwd:pwd_str
				},res=>{
					if(res.status){
						that.toPayHandler();
					}else{
						that.closePayUp();
						if(res.msg=="请先设置支付密码"){
							that.$common.modelShow("温馨提示","请先设置支付密码",res=>{
								that.$common.navigateTo("/pages/member/setting/user_info/resetpaypwd")
							});
						}else{
							that.$common.errorToShow(res.msg)
						}
					}
				})
				return;
			}
		},			
		// 获取可用支付方式列表
		getPayments() {
			this.$api.paymentList({}, (res) => {
				if (res.status) {
					this.payments = this.formatPayments(res.data);
				}
			});
		},
		// 支付方式处理
		formatPayments(payments) {
			// 如果是充值订单 过滤余额支付 过滤非线上支付方式
			if (this.type === 2) {
				payments = payments.filter((item) => item.code !== 'balancepay' || item.is_online === 1);
			}
			if(this.exp == 1){
				payments = payments.filter((item) => item.code !== 'yuanbao');
			}
			if(this.exp == 2){
				payments = payments.filter((item) => item.code !== 'balancepay');
			}
			// 设置logo图片
			payments.forEach((item) => {
				this.$set(item, 'icon', '/static/image/' + item.code + '.png');
			});
			return payments;
		},
		subForm(code){
			this.code = code 
			this.onPayUp();
		},		
		// 用户点击支付方式处理
		toPayHandler() {
			this.popShow = true;
			let _this = this;
			let code = _this.code
			let data = {
				payment_code: code,
				payment_type: _this.type
			};

			data['ids'] =
				this.type == 1 || this.type == 3 || this.type == 4 || this.type == 5 || this.type == 6 || this.type == 8 || this.type == 9 || this.type == 10 || this.type == 11
					? this.orderId
					: this.uid;
			if ((this.type == 5 || this.type == 6) && this.recharge) {
				data['params'] = {
					trade_type: 'APP',
					formid: this.orderId
				};
			}
			switch (code) {
				case 'alipay':
					/**
					 * 支付宝支付需要模拟GET提交数据
					 */
					if ((this.type == 1 || this.type == 3 || this.type == 4 || this.type == 8 || this.type == 9 || this.type == 10 || this.type == 11) && _this.orderId) {
						data['params'] = {
							trade_type: 'APP'
						};
					} else if (_this.type == 2 && _this.recharge) {
						data['params'] = {
							trade_type: 'APP',
							money: _this.recharge
						};
					}

					_this.$api.pay(data, (res) => {
						if (res.status) {
							uni.requestPayment({
								provider: 'alipay',
								orderInfo: res.data.data,
								success: function (data) {
									_this.$common.successToShow('支付成功', () => {
										_this.redirectHandler(res.data.payment_id);
									});
								}
							});
						} else {
							_this.$comon.errorToShow(res.msg);
						}
					});
					break;
				case 'wechatpay':
					// 微信 H5支付
					if ((this.type == 1 || this.type == 3 || this.type == 4 || this.type == 8 || this.type == 9 || this.type == 10 || this.type == 11) && _this.orderId) {
						data['params'] = {
							trade_type: 'APP'
						};
					} else if (_this.type == 2 && _this.recharge) {
						data['params'] = {
							trade_type: 'APP',
							money: _this.recharge
						};
					}

					// 微信app支付
					_this.$api.pay(data, (res) => {
						if (res.status) {
							//console.log(JSON.stringify(res));
							// 调用微信支付
							uni.requestPayment({
								provider: 'wxpay',
								orderInfo: {
									appid: res.data.appid,
									noncestr: res.data.noncestr,
									package: res.data.package,
									partnerid: res.data.partnerid,
									prepayid: res.data.prepayid,
									timestamp: res.data.timestamp,
									sign: res.data.sign
								},
								success: function (data) {
									_this.$common.successToShow('支付成功', () => {
										_this.redirectHandler(res.data.payment_id);
									});
								},
								fail: function (res) {
									console.log(JSON.stringify(res));
								}
							});
						} else {
							_this.$common.errorToShow(res.msg);
						}
					});
					break;
				case 'balancepay':
					/**
					 *  用户余额支付
					 *
					 */
					_this.$api.pay(data, (res) => {
						if (res.status) {
							_this.redirectHandler(res.data.payment_id);
						} else {
							_this.closePayUp();
							_this.$common.errorToShow(res.msg);
						}
					});
					break;
				case 'yuanbao':
					/**
					 *  用户元宝支付
					 *
					 */
					if (this.payStatus == false) {
						return;
					}
					this.payStatus = false;
					this.$api.pay(data, (res) => {
						if (res.status) {
							this.$common.redirectTo('/pages/goods/payment/result?id=' + res.data.payment_id);
						} else {
							this.closePayUp();
							this.payStatus = true;
							this.$common.errorToShow(res.msg);
						}
					});
					break;					
				case 'offline':
					/**
					 * 线下支付
					 */
					_this.$common.modelShow('线下支付说明', '请联系客服进行线下支付', () => {}, false, '取消', '确定');
					break;
			}
		},
		// 支付成功后跳转操作
		redirectHandler(paymentId) {
			this.$common.redirectTo('/pages/goods/payment/result?id=' + paymentId);
		},
		// 支付中显示隐藏
		popBtn() {
			this.popShow = false;
		}
	}
};
</script>

<style lang="scss">
.payment-method .cell-item-hd {
	min-width: 70upx;
}

.payment-method .cell-hd-icon {
	width: 70upx;
	height: 70upx;
}

.payment-method .cell-item-bd {
	border-left: 2upx solid #f0f0f0;
	padding-left: 30upx;
}

.payment-method .cell-bd-text {
	font-size: 28upx;
	color: #666;
}

.payment-method .address {
	font-size: 24upx;
	color: #999;
}

.payment-pop {
	position: fixed;
	top: 50%;
	left: 50%;
	transform: translate(-50%, -50%);
	width: 400rpx;
	height: 272rpx;
	background-color: #fff;
	text-align: center;
	box-shadow: 0 0 20rpx #ccc;
	/* border-radius: 10rpx; */
}

.payment-pop-c {
	padding: 50rpx 30rpx;
	/* line-height: 300rpx; */
	font-size: 32rpx;
	color: #999;
}

.payment-pop-b {
	position: absolute;
	bottom: 0;
	display: flex;
	width: 100%;
	justify-content: space-between;
}

.payment-pop-b .btn {
	flex: 1;
	justify-content: center;
}

.payment-pop .text {
	font-size: 24upx;
}


$base: orangered; 


.payArea {
	position: absolute;
	left: 0;
	top: 0;
	width: 100%;
	height: 100%;
	background-color: #FFFFFF;
}
.pay-title {
	display: flex;
	align-items: center;
	justify-content: center;
	width: 100%;
	height: 200rpx;

	text {
		font-size: 28rpx;
		color: #555555;
	}
}

.pay-password {
	display: flex;
	align-items: center;
	width: 90%;
	height: 80rpx;
	margin: 20rpx auto;
	border: 2rpx solid $base;

	.list {
		display: flex;
		align-items: center;
		justify-content: center;
		width: 16.666%;
		height: 100%;
		border-right: 2rpx solid #EEEEEE;

		text {
			font-size: 32rpx;
		}
	}

	.list:nth-child(6) {
		border-right: none;
	}
}

.hint {
	display: flex;
	align-items: center;
	justify-content: center;
	width: 100%;
	height: 100rpx;

	text {
		font-size: 28rpx;
		color: $base;
	}
}
</style>
