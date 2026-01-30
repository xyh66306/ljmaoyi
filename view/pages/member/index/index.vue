<template>
	<view class="content">
		<view class="Toparea">
			<view class="bg">
				<image src="/static/img/user.png" mode=""></image>
			</view>
			<view class="user-info-wrap">
				<template v-if="!hasLogin">
					<view class="login-img">
						<image src="/static/img/default_avatar.png" mode=""></image>
					</view>
					<!-- #ifdef H5 || APP-PLUS -->
					<view class="login-text" @click="toLogin">
						请先登录
					</view> 
					<!-- #endif -->
					<!-- #ifdef MP-WEIXIN -->
					<view class="login-text" @click="goLogin">
						请授权登录
					</view>
					<!-- #endif -->
					<view class="ewm">
						<image src="/static/img/ewm.png" mode=""></image>
					</view>
				</template>
				<template v-else>
					<view class="login-img" @click="goToPage('/pages/member/setting/user_info/index')">
						<image :src="userInfo.avatar" mode=""></image>
					</view>
					<view class="login-info" @click="goToPage('/pages/member/setting/user_info/index')">
						<view class="nickname">{{ userInfo.nickname }}</view>
						<view class="grade">
							{{ userInfo.grade_name }}
						</view>
					</view>
					<view class="ewm" @click="createPoster()">
						<image src="/static/img/ewm.png" mode=""></image>
					</view>
				</template>	
			</view>
			<view class="card-vip" @click="goToPage('/pages/vip/vip')">
				<view class="bg">
					<image src="/static/images/user_vip.png" mode=""></image>
				</view>
				<view class="card-box">
					<view class="left-box">
						<view class="top">会员可享多项权益</view>
						<view class="bottom">已过期</view>
					</view>
					<view class="vipbtn">立即续费</view>
				</view>
			</view>
			<view class="ss-order-menu-wrap flex ss-col-center">
				<view class="item"  v-for="(item, index) in orderItems" :key="index" @click="orderNavigateHandle('/pages/member/order/orderlist', item.index)">
					<view class="item-img order_item-img">
						<image :src='item.icon'></image>
					</view>
					<view class="item-name">{{ item.name }}</view>
				</view>
			</view>			
			<view class="ss-power-menu-wrap flex ss-col-center">
				<view class="item"  v-for="(item, index) in utilityMenus" :key="index"  @click="navigateToHandle(item.router)">
					<view class="item-img">
						<image :src='item.icon'></image>
					</view>
					<view class="item-name">{{ item.name }}</view>
				</view>
				<view class="item">
					<view class="item-img">
						<image src='/static/img/pow_07.png'></image>
					</view>
					<view class="item-name">联系客服</view>
				</view>				
			</view>
		</view>
	
	</view>
</template>



<script>
	const delay = (function() {
		let timer = 0
		return function(callback, ms) {
			clearTimeout(timer)
			timer = setTimeout(callback, ms)
		}
	})()
	import {
		checkLogin,common
	} from '@/config/mixins.js'
	import { h5Url } from '@/config/config.js'
	export default {
		mixins: [checkLogin,common],
		data() {
			return {
				open_id: '',
				hasLogin: false,
				userInfo: {}, // 用户信息
				kefupara: '', //客服传递资料
				afterSaleNums: 0,
				isClerk: false,
				alipayNoLogin: true,
				alipayName: '',
				alipayAvatar: '',
				config: '', //配置信息
				orderItems: [{
						name: '待付款',
						icon: '/static/img/no_pay.png',
						nums: 0,
						index:1
					},
					{
						name: '待收货',
						icon: '/static/img/no_take.png',
						nums: 0,
						index:2
					},
					{
						name: '待评价',
						icon: '/static/img/no_comment.png',
						nums: 0,
						index:4
					},
					{
						name: '售后单',
						icon: '/static/img/change_order.png',
						nums: 0,
						index:99
					},	
					{
						name: '全部订单',
						icon: '/static/img/all_order.png',
						nums: 0,
						index:0
					},															
				],
				utilityMenus: {
					group: {
						name: '我的团队',
						icon: '/static/img/pow_10.png',
						router: '/pages/member/invite/list',
						unshowItem: false,
						nums: 0
					},
					sign: {
						name: '签到',
						icon: '/static/img/pow_01.png',
						router: '/pages/index/sign',
						unshowItem: false,
						nums: 0
					},
					balance: {
						name: '充值',
						icon: '/static/img/pow_02.png',
						router: '/pages/member/balance/recharge',
						unshowItem: false,
						nums: 0
					},	
					setting: {
						name: '设置',
						icon: '/static/img/pow_03.png',
						router: '/pages/member/setting/index',
						unshowItem: false,
						nums: 0
					},	
					tixian: {
						name: '收益',
						icon: '/static/img/pow_12.png',
						router: '/pages/member/commission/profit',
						unshowItem: false,
						nums: 0
					},		
					address: {
						name: '收货地址',
						icon: '/static/img/pow_05.png',
						router: '/pages/member/address/list',
						unshowItem: false,
						nums: 0
					},
					coupon: {
						name: '优惠券',
						icon: '/static/img/pow_04.png',
						router: '/pages/member/coupon/index',
						unshowItem: false,
						nums: 0
					},					
					integral: {
						name: '我的代金券',
						icon: '/static/img/pow_08.png',
						router: '/pages/member/integral/index',
						unshowItem: false,
						nums: 0
					},					
				},
				list: 2,
				suTipStatus: false,
				isNavto: false
			}
		},
		onShow() {
			this.isNavto = true;
			this.initData()
		},
		methods: {
			goToPage(url){
				this.$common.navigateTo(url)
			},
			goLogin() {
				uni.navigateTo({
					url: '/pages/login/choose/index'
				})
			},
			getUserInfo(e) {
				let _this = this
				//return false;
				if (e.detail.errMsg == 'getUserInfo:fail auth deny') {
					_this.$common.errorToShow('未授权')
				} else {
					var data = {
						open_id: _this.open_id,
						iv: e.detail.iv,
						edata: e.detail.encryptedData,
						signature: e.detail.signature
					}
					//有推荐码的话，带上
					var invitecode = _this.$db.get('invitecode')
					if (invitecode) {
						data.invitecode = invitecode
					}
					_this.toWxLogin(data)
				}
			},
			getALICode() {
				let that = this
				uni.login({
					scopes: 'auth_user',
					success: (res) => {
						if (res.authCode) {
							uni.getUserInfo({
								provider: 'alipay',
								success: function(infoRes) {
									if (infoRes.errMsg == "getUserInfo:ok") {
										let user_info = {
											'nickname': infoRes.nickName,
											'avatar': infoRes.avatar
										}
										that.aLiLoginStep1(res.authCode, user_info);
									}
								},
								fail: function(errorRes) {
									that.$common.errorToShow('未取得用户昵称头像信息');
								}
							});
						} else {
							that.$common.errorToShow('未取得code');
						}
					},
					fail: function(res) {
						console.log(res)
						that.$common.errorToShow('用户授权失败my.login');
					}
				});
			},
			getWxCode() {
				let that = this
				uni.login({
					scopes: 'auth_user',
					success: function(res) {
						if (res.code) {
							that.wxLoginStep1(res.code)
						} else {
							this.$common.errorToShow('未取得code')
						}
					},
					fail: function(res) {
						this.$common.errorToShow('用户授权失败wx.login')
					}
				})
			},
			wxLoginStep1(code) {
				this.$api.login1({
					code
				}, res => {
					if (res.status) {
						this.open_id = res.data
					} else {
						this.$common.errorToShow(res.msg, function() {
							uni.navigateBack({
								delta: 1
							})
						})
					}
				})
			},
			aLiLoginStep1(code, user_info) {
				let data = {
					'code': code,
					'user_info': user_info
				}
				this.$api.alilogin1(data, res => {
					this.alipayNoLogin = false;
					if (res.status) {
						this.open_id = res.data.user_wx_id
						//判断是否返回了token，如果没有，就说明没有绑定账号，跳转到绑定页面
						if (!res.data.hasOwnProperty('token')) {
							this.$common.navigateTo('/pages/login/login/index?user_wx_id=' + res.data.user_wx_id);
						} else {
							this.$db.set('userToken', res.data.token)
							this.initData()
						}
					} else {
						this.$common.errorToShow(res.msg)
					}
				})
			},
			toWxLogin(data) {
				let _this = this
				_this.$api.login2(data, function(res) {
					if (res.status) {
						//判断是否返回了token，如果没有，就说明没有绑定账号，跳转到绑定页面
						if (typeof res.data.token == 'undefined') {
							uni.redirectTo({
								url: '/pages/login/login/index?user_wx_id=' + res.data.user_wx_id
							})
						} else {
							_this.$db.set('userToken', res.data.token)
							_this.initData()
						}
					} else {
						_this.$common.errorToShow('登录失败，请重试')
					}
				})
			},
			toLogin() {
				this.$common.navigateTo('/pages/login/login/index1')
			},
			initData() {
				// 获取用户信息
				var _this = this				
				if (this.$db.get('userToken')) {
					this.hasLogin = true
					this.$api.userInfo({}, res => {
						if (res.status) {
							_this.userInfo = res.data
							// #ifdef MP-WEIXIN
							//微信小程序打开客服时，传递用户信息
							var kefupara = {}
							kefupara.nickName = res.data.nickname
							kefupara.tel = res.data.mobile
							_this.kefupara = JSON.stringify(kefupara)
							// #endif
							// 获取订单不同状态的数量
							let data = {
								ids: '1,2,3,4',
								isAfterSale: true
							}
							_this.$api.getOrderStatusSum(data, res => {
								if (res.status) {
									_this.orderItems.forEach((item, key) => {
										item.nums = res.data[key + 1]
									})
									_this.afterSaleNums = res.data.isAfterSale ?
										res.data.isAfterSale :
										0
								}
							})
						}
					})
				} else {
					this.hasLogin = false
					// #ifdef MP-WEIXIN
					this.getWxCode()
					// #endif
				}
			},
			navigateToHandle(pageUrl) {
				if (!this.hasLogin) {
					if(this.isNavto){
						this.checkIsLogin()
						this.isNavto=false;
						return
					}
					return
				}
				this.$common.navigateTo(pageUrl)
			},
			gotoGroup(index){
				// this.$store.commit('orderTab', tab)
				this.$common.navigateTo("/pages/member/groupcoupon/index?tab="+index)
			},
			orderNavigateHandle(url, tab = 0) {
				if (!this.hasLogin) {
					if(this.isNavto){
						this.checkIsLogin()
						this.isNavto=false;
						return
					}
					return
				}
				this.$store.commit('orderTab', tab)
				this.$common.navigateTo(url)
			},
			goAfterSaleList() {
				if (!this.hasLogin) {
					if(this.isNavto){
						this.checkIsLogin()
						this.isNavto=false;
						return
					}
					return
				}
				this.$common.navigateTo('../after_sale/list')
			},
			//在线客服,只有手机号的，请自己替换为手机号
			showChat() {
				// #ifdef H5
				let _this = this
				window._AIHECONG('ini', {
					entId: this.config.ent_id,
					button: false,
					appearance: {
						panelMobile: {
							tone: '#FF7159',
							sideMargin: 30,
							ratio: 'part',
							headHeight: 50
						}
					}
				})
				//传递客户信息
				window._AIHECONG('customer', {
					head: _this.userInfo.avatar,
					'名称': _this.userInfo.nickname,
					'手机': _this.userInfo.mobile
				})
				window._AIHECONG('showChat')
				// #endif

				// 打开客服页面
				// #ifdef APP-PLUS || APP-PLUS-NVUE
				this.$common.navigateTo('/pages/member/customer_service/index');
				// #endif

				// 头条系客服
				// #ifdef MP-TOUTIAO
				if (this.shopMobile != 0) {
					let _this = this;
					tt.makePhoneCall({
						phoneNumber: this.shopMobile.toString(),
						success(res) {},
						fail(res) {}
					});
				} else {
					_this.$common.errorToShow('暂无设置客服电话');
				}
				// #endif
			},
			// 生成邀请海报
			createPoster() {                
				let data = {
				    page: 1,//首页
				    type: 3,//海报
				}
				let userToken = this.$db.get('userToken')
				if (userToken) {
				    data.token = userToken
				}
				
				// #ifdef H5 || APP-PLUS || APP-PLUS-NVUE
				data.client = 1;
				data.url = h5Url + 'pages/share/jump'
				// #endif
				
				// #ifdef MP-WEIXIN
				data.client = 2;
				data.url = 'pages/share/jump'
				// #endif
				
				// #ifdef MP-TOUTIAO
				data.client = 4;
				data.url = '/pages/share/jump'
				// #endif
				
				// #ifdef MP-ALIPAY
				data.client = 6;
				data.url = '/pages/share/jump'
				// #endif
				
				this.$api.share(data, res => {
					if (res.status) {
						this.$common.navigateTo('/pages/share?poster=' + encodeURIComponent(res.data))
					} else {
						this.$common.errorToShow(res.msg)
					}
				});
			},
			// toPages(url) {
			// 	console.log(url)
			// 	let userToken = this.$db.get('userToken')
			// 	if (!userToken) {
			// 		common.jumpToLogin()
			// 		return false
			// 	} else {
			// 		this.$common.navigateTo(url)

			// 	}
			// },
		},
		computed: {
			// 获取店铺联系人手机号
			shopMobile() {
				return this.$store.state.config.shop_mobile || 0;
			},
			invoice_switch() {
				return this.$store.state.config.invoice_switch || 2;
			},
			store_switch() {
				return this.$store.state.config.store_switch || 0;
			}
		},
		watch: {}
	}
</script>


<style lang="scss" scoped>
image {
	width:100%;
	height:100%;
}
.Toparea {
	position: relative;
	.bg {
		width:750rpx;
		height:320rpx;
	}
	.user-info-wrap {
		position: absolute;
		left:20rpx;
		top:0rpx;
		width:710rpx;
		margin: 40rpx auto 0;
		padding:0 20rpx;
		display: flex;
		align-items: center;
		.login-img {
			width:120rpx;
			height:120rpx;
			border-radius: 120rpx;
			overflow: hidden;
			border:#fff 2rpx solid;
			image {
				width:100%;
				height:100%;
			}
		}
		.login-text {
			margin-left: 20rpx;
			line-height:100rpx;
			font-size:36rpx;
			color:#333;
		}
		.login-info {
			margin-left: 20rpx;
			line-height:50rpx;			
			color:#333;
			.nickname {
				font-size:28rpx;
			}			
			.grade {
				font-size:24rpx;
			}
		}
		.ewm {
			width:36rpx;
			height:36rpx;
			margin-left:auto;
			margin-right: 20rpx;
		}
	}
}

.ss-order-menu-wrap {
	position: relative;
	z-index:9;
	height:160rpx;
	align-items: center;
	.item {
		flex:1;		
		.item-img {
			width:44rpx;
			height:52rpx;
			margin:0 auto;
		}
		.order_item-img {
			width:44rpx;
			height:44rpx;
			margin:0 auto;
		}
		.item-name {
			font-size:24rpx;
			color:#333;
			line-height:46rpx;
			text-align: center;
			margin-top:20rpx;
		}
	}
}

.ss-coupon-menu-wrap {
	.item {
		width:25%;		
		.item-img {
			width:44rpx;
			height:44rpx;
			margin:0 auto;
		}
		.item-name {
			font-size:24rpx;
			color:#333;
			line-height:46rpx;
			text-align: center;
			margin-top:20rpx;
		}
	}	
}


.ss-power-menu-wrap {
	flex-wrap: wrap;
	.item {
		width:25%;
		height:176rpx;
		display: flex;
		flex-direction: column;
		align-items: center;
		justify-content: center;
		.item-img {
			width:48rpx;
			height:48rpx;
			margin:0 auto;
		}
		.item-name {
			font-size:24rpx;
			color:#333;
			line-height:46rpx;
			text-align: center;
			margin-top:20rpx;
		}
	}	
}

.ss-money-menu-wrap {
	.item {
		width:25%;
		.item-txt {
			font-size:24rpx;
			color:#333;
			line-height:38rpx;
			text-align: center;
		}
		.item-img {
			width:44rpx;
			height:44rpx;
			margin:0 auto;
		}
		.item-name {
			font-size:24rpx;
			color:#333;
			line-height:46rpx;
			text-align: center;
			margin-top:20rpx;
		}
	}
}

.ss-col-center {
	width:710rpx;
	position: relative;
	z-index:9;
	min-height:160rpx;
	align-items: center;
	margin:20rpx auto 0rpx;
	border-radius: 20rpx;
	background-color: #fff;
	overflow: hidden;
}


.card-vip {
	width: 690rpx;
	height: 134rpx;
	margin: -100rpx auto 0;
	position: relative;
	.bg {
		width: 100%;
		height: 100%;
	}
	.card-box {
		position: absolute;
		top:0rpx;
		left:0rpx;
		width: 690rpx;
		height: 134rpx;
		padding:0 35rpx 0 120rpx;
		display: flex;
		justify-content: space-between;
		align-items: center;
		.left-box {
			color:#ae5a2a;
			font-size: 24rpx;
		}
		.vipbtn {
			height: 52rpx;
			line-height: 52rpx;
			text-align: center;
			padding: 0 10px;
			background: #fff;
			border-radius: 28rpx;
			font-size: 26rpx;
			color: #ae5a2a;
		}
	}
}
</style>
