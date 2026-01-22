<template>
	<view class="content">
		<view class="Toparea">
			<view class="bg">
				<image src="/static/img/user.png" mode=""></image>
			</view> 
			<view class="user-info-wrap">
				<template v-if="!hasLogin">
					<view class="login-img">
						<image src="/static/images/f.png" mode=""></image>
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
						<image src="/static/images/user/erweima.png" mode=""></image>
					</view>
				</template>
				<template v-else>
					<view class="login-img" @click="editModal=true">
						<image :src="userInfo.avatar" mode=""></image>
					</view>
					<view class="login-info" @click="editModal=true">
						<view class="nickname">{{ userInfo.nickname }}</view>
						<view class="grade">
							{{ userInfo.grade_name }}
						</view>
					</view>
					<view class="ewm" @click="createPoster()">
						<image src="/static/images/user/erweima.png" mode=""></image>
					</view>
				</template>	
			</view>
			<view class="cardVipA" @click="goSvip" v-if="platform !='ios'">
				<image class="svip_user" :src="`${domain}/static/images/user/svip_user.png`"></image>
				<view class="left-box">
					<view v-if="userInfo.grade > 1" class="small">累计为您节省{{userInfo.svip_save_money || 666}}元</view>
					<view v-else-if="userInfo.grade <= 1" class="small">开通享六大特权，省钱又省心
					</view>
				</view>
				<view class="acea-row row-middle">
					<view class="btn-open">{{userInfo.grade > 1 ? '终身会员' : '立即开通'}}</view>
				</view>
			</view>
		</view>		
		<view class="wrap wrap_ios">	
			<view class="ss-money-menu-wrap flex ss-col-center" v-if="hasLogin">			
				<view class="item" @click="goToPage('/pages/member/balance/details')">
					<view class="item-txt">{{userInfo.balance || 0 }}元</view>
					<view class="item-name">余额</view>
				</view>	
				<view class="item" @click="goToPage('/pages/member/balance/details')">
					<view class="item-txt">{{userInfo.coupon_num || '0.00' }}元</view>
					<view class="item-name">消费券</view>
				</view>					
				<view class="item" @click="goToPage('/pages/member/balance/index')">
					<view class="item-img">
						<image src='/static/images/user/wallet_icon.png'></image>
					</view>
					<view class="item-name">我的钱包</view>
				</view>					
			</view> 		

			<view class="ss-order-menu-wrap flex ss-col-center">
				<view class="item"  v-for="(item, index) in orderItems" :key="index" @click="orderNavigateHandle('/pages/member/order/orderlist', item.index)">
					<view class="item-img order_item-img">
						<image :src='item.icon'></image>
					</view>
					<view class="item-name">{{ item.name }}</view>
					<view class="item-nums" v-if="item.nums && item.nums>0">{{item.nums}}</view> 
				</view>
			</view>

			<view class="ss-power-menu-wrap flex ss-col-center">
				<view class="item"  v-for="(item, index) in utilityMenus" :key="index"  @click="navigateToHandle(item.router)">
					<view class="item-img">
						<image :src='item.icon'></image>
					</view>
					<view class="item-name">{{ item.name }}</view>
				</view>
				<view class="item" @click="chooseKf()">
					<view class="item-img">
						<image src='/static/images/user/pow_07.png'></image>
					</view>
					<view class="item-name">联系客服</view>
				</view>				
			</view>
		</view>
		<lvv-popup position="bottom" ref="lvvpopref">
			<view class='lvvPopArea' style="width: 100%;max-height: 804rpx;background: #FFFFFF;position: absolute;left:0;bottom:80rpx;padding-bottom:constant(safe-area-inset-bottom); padding-bottom: env(safe-area-inset-bottom);">
				<view class="item-btn" @click="gototel()">拨打电话</view>
				<view class="item-btn" @click="zxkf()">在线客服</view>
			</view>	
		</lvv-popup>	
		
		<lvv-popup position="bottom" ref="kfpopref">
			<view class='lvvPopArea' style="width: 100%;background: #FFFFFF;position: absolute;left:0;bottom: 80rpx;padding-bottom:constant(safe-area-inset-bottom); padding-bottom: env(safe-area-inset-bottom);text-align:center;">
				<image :src="kefu" class="kefuimg"></image>
				<view class='close-btn' @click="kftoclose()">
					<image src='/static/image/close.png'></image>
				</view>
			</view>	
		</lvv-popup>		
		<!-- #ifdef MP -->
		<editUserModal :isShow="editModal" @closeEdit="closeEdit" @editSuccess="editSuccess"></editUserModal>
		<!-- #endif -->
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
	import { h5Url,apiBaseUrl } from '@/config/config.js'
	import lvvPopup from '@/components/lvv-popup/lvv-popup.vue';
	// #ifdef MP
	import editUserModal from '@/components/editUserModal/index.vue';
	// #endif	
	export default {
		components: {
			lvvPopup,
			// #ifdef MP
			editUserModal
			// #endif	
		},
		mixins: [checkLogin,common],
		data() {
			return {
				editModal:false,
				domain:apiBaseUrl,
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
				orderItems: [
					{
						name: '待付款',
						icon: '/static/images/user/no_pay.png',
						nums: 0,
						index:1
					},
					{
						name: '待收货',
						icon: '/static/images/user/no_take.png',
						nums: 0,
						index:2
					},
					{
						name: '待评价',
						icon: '/static/images/user/no_comment.png',
						nums: 0,
						index:4
					},
					{
						name: '售后单',
						icon: '/static/images/user/change_order.png',
						nums: 0,
						index:99
					},	
					{
						name: '全部订单',
						icon: '/static/images/user/all_order.png',
						nums: 0,
						index:0
					},															
				],
				cardItems:[
					{
						name: '已领取',
						icon: '/static/images/user/nouse_coupon.png',
						index:1
					},
					{
						name: '免单中',
						icon: '/static/images/user/useend_coupon.png',
						index:2
					},
					{
						name: '已结束',
						icon: '/static/images/user/useend_coupon.png',
						index:3
					},										
					{
						name: '活动中心',
						icon: '/static/images/user/all_coupon.png',
						index:0
					}
				],
				utilityMenus: {
					group: {
						name: '我的团队',
						icon: '/static/images/user/pow_10.png',
						router: '/pages/member/invite/list',
						unshowItem: false,
						nums: 0
					},
					profit: {
						name: '我的收益',
						icon: '/static/images/user/pow_13.png',
						router: '/pages/member/commission/profit',
						unshowItem: false,
						nums: 0
					},						
					balance: {
						name: '账户充值',
						icon: '/static/images/user/pow_02.png',
						router: '/pages/member/balance/recharge',
						unshowItem: false,
						nums: 0
					},							
					address: {
						name: '收货地址',
						icon: '/static/images/user/pow_05.png',
						router: '/pages/member/address/list',
						unshowItem: false,
						nums: 0
					},
					history: {
						name: '我的足迹',
						icon: '/static/images/user/pow_06.png',
						router: '/pages/member/history/index',
						unshowItem: false,
						nums: 0
					},	
					setting: {
						name: '设置',
						icon: '/static/images/user/pow_03.png',
						router: '/pages/member/setting/index',
						unshowItem: false,
						nums: 0
					},					
				},
				list: 2,
				suTipStatus: false,
				isNavto: false,
				platform:'',
				kefu:apiBaseUrl+"app/images/kefu.jpg"
			}
		},
		onReady() {
			const systemInfo = uni.getSystemInfoSync();
			this.platform  = systemInfo.platform
		},
		onShow() {
			this.isNavto = true;
			this.initData()
		},
		methods: {
			editSuccess(data) {
				this.editModal = false;
				// this.userInfo = data;
				//在这里可以把获取到的头像昵称信息通过接口保存到数据库
				this.$api.editInfo(data,res=>{
					this.closeEdit();
					this.initData()
				})
			},
			closeEdit() {
				this.editModal = false;
			},				
			kftoclose(){
				this.$refs.kfpopref.close();
			},
			zxkf(){
				this.$refs.lvvpopref.close();
				this.$refs.kfpopref.show();
			},
			// 关闭modal弹出框
			toKeFuclose() {
				this.$refs.lvvpopref.close();
			},
			chooseKf(){
				this.$refs.lvvpopref.show();
			},
			//这个是自己的方法名
			openAuth(){
				this.$refs['authphone'].open() //调起自定义权限目的弹框,具体可看示例里面很详细
			},
			//用户授权权限后的回调
			changeAuth(){
				//这里是权限通过后执行自己的代码逻辑
				console.log('权限已授权，可执行自己的代码逻辑了');
				this.gototel();				
			},
			goSvip(){
				let _this = this;
				_this.navigateToHandle("/pages/activity/level/level");				
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
				this.$common.navigateTo('../../login/login/index1')
			},
			initData() {
				// 获取用户信息
				var _this = this				
				if (this.$db.get('userToken')) {
					this.hasLogin = true
					this.$api.userInfo({}, res => {
						if (res.status) {
							_this.userInfo = res.data
							if(res.data.username == res.data.nickname){
								_this.editModal = true;
							}
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
				// if (!this.hasLogin) {
				// 	if(this.isNavto){
				// 		this.checkIsLogin()
				// 		this.isNavto=false;
				// 		return
				// 	}
				// 	return
				// }
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
			gototel(){
				uni.makePhoneCall({
					phoneNumber: this.shopMobile.toString(),
					success(res) {},
					fail(res) {}
				});
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
.lvvPopArea {
	padding:40rpx 0 80rpx;
	.item-btn {
		width:90%;
		height:45px;
		border-radius: 30px;
		background:#05502C;
		color:#fff;
		font-size:16px;
		line-height:45px;
		text-align:center;
		margin:0 auto 20px;
	}
}

.kefuimg {
	width:550rpx;
	height:750rpx;
	margin:0 auto;
}
.close-btn {
	width: 40rpx;
	height: 40rpx;
	border-radius: 50%;
	display: inline-block;
	position: absolute;
	right: 30rpx;
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
			color:#fff;
		}
		.login-info {
			margin-left: 20rpx;
			line-height:50rpx;			
			color:#fff;
			.nickname {
				font-size:28rpx;
			}			
			.grade {
				font-size:24rpx;
			}
		}
		.ewm {
			width:50rpx;
			height:50rpx; 
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
		position: relative;
		.item-img {
			width:44rpx;
			height:52rpx;
			margin:0 auto;
		}
		.order_item-img {
			width:52rpx;
			height:52rpx;
			margin:0 auto;
		}
		.item-name {
			font-size:24rpx;
			color:#333;
			line-height:46rpx;
			text-align: center;
			margin-top:20rpx;
		}
		.item-nums {
			position: absolute;
			right:16rpx;
			top:-10rpx;
			width:36rpx;
			height:36rpx;
			border-radius: 36rpx;
			background-color: #c76359;
			border:#c76359 2rpx solid;
			color:#fff;
			font-size:20rpx;
			line-height:36rpx;
			text-align: center;
			overflow:hidden;
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
	margin:0 auto;
	.item {
		width:33.33%;
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

.cardVipA {
	position: absolute;
	background: linear-gradient(145deg, #F8E3A8 0%, #E8C077 100%);
	background-size: 100% 100%;
	width: 710rpx;
	height: 84rpx;
	bottom: 0rpx;
	left: 20rpx;
	padding: 0 30rpx 0 105rpx;
	border-radius: 16rpx 16rpx 0 0;
	box-sizing: border-box;
	display: flex;
	justify-content: space-between;
	align-items: center;
	.svip_user{
		width: 52rpx;
		height: 52rpx;
		border-radius: 100%;
		position: absolute;
		left: 30rpx;
		top: 17rpx;
	}
	.left-box {
		font-size: 26rpx;
		color: #905100;
		font-weight: 400;
	}
	.btn {
		color: #905100;
		font-weight: 400;
		font-size: 24rpx;
	}
	.btn-open {
		background: #282828;
		border-radius: 40rpx;
		color: #F7E1A6;
		font-size: 24rpx;
		width: 140rpx;
		height: 50rpx;
		display: flex;
		align-items: center;
		justify-content: center;
	}
}
.wrap_ios {
	margin-top:-100rpx;	
}
</style>
