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
const ap = require('../../common/ap.js');
import { baseUrl, weixinOpenAlipay } from '@/config/config.js';
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
			openid: '',
			popShow: false,
			payStatus: true
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
			this.passwordArr = []
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
			// h5支付并且是在微信浏览器内 过滤支付宝支付
			if (this.$common.isWeiXinBrowser() && !weixinOpenAlipay) {
				payments = payments.filter((item) => item.code !== 'alipay');
			}

			// 如果是充值订单 过滤余额支付 过滤非线上支付方式
			if (this.type === 2) {
				payments = payments.filter((item) => item.code !== 'balancepay' || item.is_online === 1);
				payments = payments.filter((item) => item.code !== 'yuanbao' || item.is_online === 1);
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
		checkWXJSBridge(data) {
			let that = this;
			let interval = setInterval(() => {
				if (typeof window.WeixinJSBridge != 'undefined') {
					clearTimeout(interval);
					that.onBridgeReady(data);
				}
			}, 200);
		},
		onBridgeReady(data) {
			var _this = this;
			window.WeixinJSBridge.invoke(
				'getBrandWCPayRequest',
				{
					appId: data.appid, // 公众号名称，由商户传入
					timeStamp: data.timeStamp, // 时间戳，自1970年以来的秒数
					nonceStr: data.nonceStr, // 随机串
					package: data.package,
					signType: data.signType, // 微信签名方式：
					paySign: data.paySign // 微信签名
				},
				function (res) {
					if (res.err_msg === 'get_brand_wcpay_request:ok') {
						_this.$common.successToShow('支付成功');
					} else if (res.err_msg === 'get_brand_wcpay_request:cancel') {
						_this.$common.errorToShow('取消支付');
					} else {
						_this.$common.errorToShow('支付失败');
					}
					setTimeout(() => {
						_this.$common.redirectTo('/pages/goods/payment/result?id=' + data.payment_id);
					}, 1000);
				}
			);
		},
		subForm(code){
			this.code = code 
			this.onPayUp();
		},
		// 用户点击支付方式处理
		toPayHandler() {
			this.popShow = true;
			let code = this.code
			let data = {
				payment_code: code,
				payment_type: this.type
			};
			data['ids'] =
				this.type == 1 || this.type == 3 || this.type == 4 || this.type == 5 || this.type == 6 || this.type == 8 || this.type == 9 || this.type == 10 || this.type == 11
					? this.orderId
					: this.uid;
			switch (code) {
				case 'alipay':
				if (this.payStatus == false) {
					return;
				}
				this.payStatus = false;
					/**
					 * 支付宝支付需要模拟GET提交数据
					 */
					if ((this.type == 1 || this.type == 3 || this.type == 4 || this.type == 8 || this.type == 9 || this.type == 10 || this.type == 11) && this.orderId) {
						data['params'] = {
							trade_type: 'WAP',
							return_url: baseUrl + 'wap/pages/goods/payment/result'
						};
					} else if (this.type == 2 && this.recharge) {
						data['params'] = {
							money: this.recharge,
							return_url: baseUrl + 'wap/pages/goods/payment/result'
						};
					} else if ((this.type == 5 || this.type == 6) && this.recharge) {
						data['params'] = {
							formid: this.orderId
						};
					}

					this.$api.pay(data, (res) => {
						if (res.status) {
							const url = res.data.url;
							const data = res.data.data;

							// 模拟GET提交
							let tempForm = document.createElement('form');
							tempForm.id = 'aliPay';
							tempForm.methods = 'post';
							tempForm.action = url;
							tempForm.target = '_self';
							let input = [];
							for (let k in data) {
								input[k] = document.createElement('input');
								input[k].type = 'hidden';
								input[k].name = k;
								input[k].value = data[k];
								tempForm.appendChild(input[k]);
							}
							tempForm.addEventListener('submit', function () {}, false);
							document.body.appendChild(tempForm);
							//微信中支付
							if (this.$common.isWeiXinBrowser() && weixinOpenAlipay) {
								var queryParam = '';
								//queryParam += 'bizcontent=' + encodeURIComponent(data.biz_content);
								Array.prototype.slice.call(document.querySelectorAll('input[type=hidden]')).forEach(function (ele) {
									queryParam += '&' + ele.name + '=' + encodeURIComponent(ele.value);
								});
								var gotoUrl = document.querySelector('#aliPay').getAttribute('action') + '?' + queryParam;
								_AP.pay(gotoUrl);
								return false;
							} else {
								tempForm.dispatchEvent(new Event('submit'));
								tempForm.submit();
								document.body.removeChild(tempForm);
							}
						} else {
							this.payStatus = true;
							this.$common.errorToShow(res.msg);
						}
					});
					break;
				case 'wechatpay':
					if (this.payStatus == false) {
						return;
					}
					this.payStatus = false;
					let _this = this;
					/**
					 * 微信支付有两种
					 * 判断是否在微信浏览器
					 * 	微信jsapi支付
					 */
					let isWeiXin = this.$common.isWeiXinBrowser();

					if (isWeiXin) {
						var transitUrl = baseUrl + 'wap/pages/goods/payment/auth?order_id=' + this.orderId + '&type=' + this.type;

						if ((this.type == 1 || this.type == 3 || this.type == 4 || this.type == 8 || this.type == 9 || this.type == 10 || this.type == 11) && this.orderId) {
							// 微信jsapi支付
							// if (this.openid) {
							//   data['params'] = {
							//     trade_type: 'JSAPI_OFFICIAL',
							//     openid: this.openid
							//   }
							// } else {
							//   data['params'] = {
							//     trade_type: 'JSAPI_OFFICIAL',
							//     url: window.location.href
							//   }
							// }
							data['params'] = {
								trade_type: 'JSAPI_OFFICIAL',
								url: transitUrl
							};
						} else if (this.type == 2 && this.recharge) {
							data['params'] = {
								trade_type: 'JSAPI_OFFICIAL',
								money: this.recharge,
								url: transitUrl + '&uid=' + this.uid + '&money=' + this.recharge
							};
							// if (this.openid) {
							//   data['params'] = {
							//     money: this.recharge,
							//     openid: this.openid
							//   }
							// } else {
							//   data['params'] = {
							//     money: this.recharge,
							//     url: window.location.href
							//   }
							// }
						} else if ((this.type == 5 || this.type == 6) && this.recharge) {
							data['params'] = {
								formid: this.orderId,
								trade_type: 'JSAPI_OFFICIAL',
								url: transitUrl
							};
						}
						this.$api.pay(data, (res) => {
							if (!res.status && res.data == '10066') {
								window.location.href = res.msg;
								return;
							}
							if (!res.status) {
								this.$common.errorToShow(res.msg);
								return;
							}
							const data = res.data;
							this.checkWXJSBridge(data);
						});
					} else {
						// 微信 H5支付
						if ((this.type == 1 || this.type == 3 || this.type == 4 || this.type == 8 || this.type == 9 || this.type == 10 || this.type == 11) && this.orderId) {
							data['params'] = {
								trade_type: 'MWEB',
								return_url: baseUrl + 'wap/pages/goods/payment/result'
							};
						} else if (this.type == 2 && this.recharge) {
							data['params'] = {
								trade_type: 'MWEB',
								money: this.recharge,
								return_url: baseUrl + 'wap/pages/goods/payment/result'
							};
						} else if ((this.type == 5 || this.type == 6) && this.recharge) {
							data['params'] = {
								formid: this.orderId,
								trade_type: 'MWEB',
								return_url: baseUrl + 'wap/pages/goods/payment/result'
							};
						}
						// 微信h5支付
						this.$api.pay(data, (res) => {
							_this.payStatus = true;
							if (res.status) {
								location.href = res.data.mweb_url;
							} else {
								this.$common.errorToShow(res.msg);
							}
						});
					}
					break;
				case 'balancepay':
					/**
					 *  用户余额支付
					 *
					 */
					if (this.payStatus == false) {
						return;
					}
					this.payStatus = false;
					if ((this.type == 5 || this.type == 6) && this.recharge) {
						data['params'] = {
							formid: this.orderId
						};
					}
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
				case 'yuanbao':
					/**
					 *  用户元宝支付
					 *
					 */
					if (this.payStatus == false) {
						return;
					}
					this.payStatus = false;
					if ((this.type == 5 || this.type == 6) && this.recharge) {
						data['params'] = {
							formid: this.orderId
						};
					}
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
					
					this.$common.modelShow('线下支付说明', '请联系客服进行线下支付', () => {}, false, '取消', '确定');
					break;
			}
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
