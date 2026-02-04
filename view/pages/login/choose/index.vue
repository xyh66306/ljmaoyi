<template>
	<view class="content">
		<view class="login-t"><image class="login-logo" :src="logoImage" mode="aspectFill"></image></view>
		<view class="login-b">
			<view>
				<template v-if="checked">
					<button open-type="getPhoneNumber" class="tip_btn" @getphonenumber="getphone" hover-class="btn-hover">手机号一键登录</button>
				</template>
				<template v-else>
					<view class="tip_btn" @click="tips()">手机号一键登录</view>
				</template>
				<view class="color-9 fsz24 agreement">
					<view class="agree" >
						<image :src="checkedImg[checkedindex]" @click="checkedChange()"></image>
					</view>
					<view class="right">
						同意
						<text @click="goAgreement()" class="color-o">用户协议</text>
						和
						<text @click="goPrivacy()" class="color-o">隐私政策</text>						
					</view>
				</view>
			</view>
		</view>
		<!-- #ifdef MP-WEIXIN -->
		<jshopPrivacy></jshopPrivacy>
		<!-- #endif -->		
	</view>
</template>

<script>
import { goBack, jumpBackPage } from '@/config/mixins.js';
import jshopPrivacy from '@/components/jshop-privacy/jshop-privacy';
export default {
	mixins: [goBack, jumpBackPage],
	components: {
		jshopPrivacy
	},
	data() {
		return {
			checkedImg:['/static/image/quan.png','/static/image/success.png'],
			checked:false,
			checkedindex:0,
			maxMobile: 11,
			mobile: '', // 用户手机号
			code: '', // 短信验证码
			user_wx_id: '', //授权id
			verification: true, // 通过v-show控制显示获取还是倒计时
			timer: 60, // 定义初始时间为60s
			btnb: 'btn btn-square btn-c btn-all', //按钮背景
			type: '', // 有值是第三方登录账号绑定
			isWeixinBrowser: this.$common.isWeiXinBrowser()
		};
	},
	onLoad(option) {
		const _this = this
		// #ifdef MP-WEIXIN
		this.getCode(function(code) {
		  var data = {
			code: code
		  }
		  _this.$api.login1(data, (res)=>{
			if (!res.status) {
			  _this.$common.successToShow(res.msg, function() {
				uni.navigateBack({
				  delta: 1
				})
			  })
			} else {
			  _this.open_id = res.data
			}
		  })
		})
		// #endif		
		if (option.user_wx_id) {
			this.user_wx_id = option.user_wx_id;
			uni.setNavigationBarTitle({
				title: '绑定手机号'
			});
		}	
	},
	computed: {
		// 验证手机号
		rightMobile() {
			let res = {};
			if (!this.mobile) {
				res.status = false;
				res.msg = '请输入手机号';
			} else if (!/^1[3456789]{1}\d{9}$/gi.test(this.mobile)) {
				res.status = false;
				res.msg = '手机号格式不正确';
			} else {
				res.status = true;
			}
			return res;
		},
		// 动态计算发送验证码按钮样式
		sendCodeBtn() {
			let btn = 'btn btn-g';
			if (this.mobile.length === this.maxMobile && this.rightMobile.status) {
				return btn + ' btn-b';
			} else {
				return btn;
			}
		},
		// 动态更改登录按钮bg
		regButtonClass() {
			return this.mobile && this.mobile.length === this.maxMobile && this.code ? this.btnb + ' btn-b' : this.btnb;
		},
		logoImage() {
			return this.$store.state.config.shop_logo;
		}
	},
	onShow() {
		let _this = this;
		let userToken = _this.$db.get('userToken');
		if (userToken) {
			uni.switchTab({
				url: '/pages/member/index/index'
			});
			return true;
		}
		_this.timer = parseInt(_this.$db.get('timer'));
		if (_this.timer != null && _this.timer > 0) {
			_this.countDown();
			_this.verification = false;
		}
		// let privacy = this.$db.get('privacy');
		// if(privacy){
		// 	this.checkedindex =1
		// 	this.checked = true
		// }
	},
	methods: {
		checkedChange(){
			this.checked = !this.checked;
			if(this.checked){
				this.checkedindex =1
			} else {
				this.checkedindex = 0
			}
		},
		getCode: function(callback) {
		  uni.login({
			// #ifdef MP-ALIPAY
			scopes: 'auth_user',
			// #endif
			success: function(res) {
			  if (res.code) {
				return callback(res.code)
			  } else {
				//login成功，但是没有取到code
				this.$common.errorToShow('未取得code')
			  }
			},
			fail: function(res) {
			  this.$common.errorToShow('用户授权失败wx.login')
			}
		  })
		},
		tips(){
			var that = this;
			if(!that.checked){
				return that.$common.successToShow('请先同意用户协议和隐私政策');
			}			
		},
		getphone(e){	
			var detail = e.detail;
			var that = this;
			if(!that.checked){
				return that.$common.successToShow('请先同意用户协议和隐私政策');
			}
			if(detail.errMsg == 'getPhoneNumber:fail user deny'){
				return that.$common.successToShow('您拒绝了授权');
			}

			if(detail.errMsg == 'getPhoneNumber:ok'){
				var code = detail.code;
				let invicode = that.$db.get('invitecode');
				that.$api.yjLogin({
					code:code,
					invicode:invicode,
					open_id:that.open_id
				},res=>{
					if(res.status){
						that.$db.set('userToken', res.data);
						that.redirectHandler();
					}
				})
			}
		},
		// 重定向跳转 或者返回上一个页面
		redirectHandler() {
			this.$common.successToShow('登录成功!', () => {
				this.$db.set('timer', 0);
				this.$db.del('invitecode');
				// this.handleBack();
				uni.navigateBack({
				    delta: 1
				});
			});
		},
		// 跳转到普通登录
		toLogin() {
			uni.navigateTo({
				url: '../../login/login/index'
			});
		},
		// 跳转到用户协议
		goAgreement() {
			let articleId = this.$store.state.config.user_agreement_id;
			this.$common.navigateTo('/pages/article/index?id_type=1&id=' + articleId);
		},
		// 跳转到隐私政策
		goPrivacy() {
			let articleId = this.$store.state.config.privacy_policy_id;
			this.$common.navigateTo('/pages/article/index?id_type=1&id=' + articleId);
		},
	}
};
</script>

<style lang="scss">
.content {
	/*  #ifdef  H5  */
	height: calc(100vh - 90rpx);
	/*  #endif  */
	/*  #ifndef  H5  */
	height: 100vh;
	/*  #endif  */
	background-color: #fff;

	padding: 0rpx 100rpx;
}
.login-t {
	text-align: center;
	padding-top:150rpx;
}
.login-logo {
	width: 180rpx;
	height: 180rpx;
	border-radius: 20rpx;
	background-color: #f8f8f8;
}
.login-m {
	margin-bottom: 100rpx;
}
.login-item {
	border-bottom: 2rpx solid #d0d0d0;
	overflow: hidden;
	padding: 10rpx;
	color: #333;
	margin-bottom: 30rpx;
}
.login-item-input {
	display: inline-block;
	flex: 1;
	box-sizing: border-box;
}
.login-item .btn {
	border: none;
	width: 40%;
	text-align: right;
	padding: 0;
	&.btn-b {
		background: none;
		color: #333 !important;
	}
}


.login-b {
	margin-top: 100rpx;
}

.login-b .btn {
	color: #999;
}
.btn-b {
	color: #fff !important;
}
.login-other {
	margin-bottom: 40rpx;
	.item {
		padding: 20rpx 0;
	}
}
.btn-square {
	color: #333;
}

.agreement {
	display: flex;
	margin:220rpx 0;
	text-align: center;
	.color-o {
		margin: 0 10rpx;
	}
	.agree {
		width:35rpx;
		height: 35rpx;
		margin-right: 10rpx;
		image {
			width: 100%;
			height: 100%;
		}
	}
}
.goforgetpwd {
	width: 100%;
	text-align: right;
}


.tip_btn {
	with:400rpx;
	height:92rpx;
	line-height:92rpx;
	text-align:center;
	background:#51b352;
	color:#fff;
	border-radius:10rpx;
	margin:70rpx auto 0;
	font-size:36rpx;
	
}
</style>
