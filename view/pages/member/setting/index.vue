<template>
	<view class="content">
		<view class="content-top">
			<view class='cell-group right-img'>
				<view class='cell-item' @click="navigateToHandle('./user_info/index')">
					<view class='cell-item-hd'>
						<view class='cell-hd-title'>个人信息</view>
					</view>
					<view class='cell-item-ft'>
						<image class='cell-ft-next icon' src='/static/image/right.png'></image>
					</view>
				</view>
<!-- 				<view class='cell-item' @click="navigateToHandle('./user_info/resetpassword')">
					<view class='cell-item-hd'>
						<view class='cell-hd-title'>重置密码</view>
					</view>
					<view class='cell-item-ft'>
						<image class='cell-ft-next icon' src='/static/image/right.png'></image>
					</view>
				</view> -->
				<view class='cell-item' @click="navigateToHandle('./user_info/resetpaypwd')">
					<view class='cell-item-hd'>
						<view class='cell-hd-title'>重置支付密码</view>
					</view>
					<view class='cell-item-ft'>
						<image class='cell-ft-next icon' src='/static/image/right.png'></image>
					</view>
				</view>				
				<view class='cell-item' @click="navigateToHandle('./user_info/password')">
					<view class='cell-item-hd'>
						<view class='cell-hd-title'>修改密码</view>
					</view>
					<view class='cell-item-ft'>
						<image class='cell-ft-next icon' src='/static/image/right.png'></image>
					</view>
				</view>
				<view class='cell-item' @click="clearCache">
					<view class='cell-item-hd'>
						<view class='cell-hd-title'>清除缓存</view>
					</view>
					<view class='cell-item-ft'>
						<image class='cell-ft-next icon' src='/static/image/right.png'></image>
					</view>
				</view>
				<view class='cell-item' @click="aboutUs">
					<view class='cell-item-hd'>
						<view class='cell-hd-title'>关于我们</view>
					</view>
					<view class='cell-item-ft'>
						<image class='cell-ft-next icon' src='/static/image/right.png'></image>
					</view>
				</view>
				<view class='cell-item' @click="checkVersion()">
					<view class='cell-item-hd'>
						<view class='cell-hd-title'>版本信息</view>
					</view>
					
					<view class='cell-item-ft'>
						{{version_number}}
						<image class='cell-ft-next icon' src='/static/image/right.png'></image>
					</view>
				</view>				
				<view class='cell-item' @click="logOff">
					<view class='cell-item-hd'>
						<view class='cell-hd-title'>退出</view>
					</view>
					<view class='cell-item-ft'>
						<image class='cell-ft-next icon' src='/static/image/right.png'></image>
					</view>
				</view>
			</view>
		</view>
		<view class="color-9 fsz24 agreement">
			<text @click="goAgreement()" class="color-o">《用户协议》</text> 和 <text @click="goPrivacy()" class="color-o">《隐私政策》</text>
		</view>
	</view>
</template>

<script>
// 获取当前app的版本
const systemInfo = uni.getSystemInfoSync();	
export default {
	data() {
		return {
			yw_update:{
				is_force:1,
				is_update:1,
				download_url:"",
				version_desc:"",
				old_v:"",
				new_v:"",
			},
			version_number:'',
		}
	},	
	onLoad() {
		this.getVersion();
	},	
	methods: {
	getVersion(){
		// #ifdef APP
		this.version_number = systemInfo.appWgtVersion;
		// #endif
		// #ifdef H5
		this.version_number = systemInfo.appVersion;
		console.log(systemInfo.appVersion,'版本号');
		// #endif
		// #ifdef MP
		const accountInfo = wx.getAccountInfoSync();
		this.version_number = accountInfo.miniProgram.version // 小程序 版本号
		// #endif
	},
	checkVersion() {
		// #ifdef H5
		this.$common.errorToShow("请前往APP");
		return;
		// #endif
		
		// #ifdef APP
		// 获取应用版本号
		let version = plus.runtime.version;
		//检测当前平台，如果是安卓则启动安卓更新
		uni.getSystemInfo({
			success: res => {
				this.updateHandler(res.platform, version);
			}
		})
		// #endif
	},
	// 更新操作
	updateHandler(platform, version) {
		let data = {
			platform: platform,
			version: version
		}
		let _this = this;
		this.$api.getAppVersion(data,
			res => {
				if (res.status && res.data.version) {
					const info = res.data;
					
					if (info.version !== '' && info.version > version) {
						uni.showModal({
							//提醒用户更新
							title: '更新提示',
							content: info.note,
							success: res => {
								if (res.confirm) {
									plus.runtime.openURL(info.download_url)
								}
							}
						})
					} else {
						this.$common.errorToShow("已是最新版本");
					}
				}
			}
		)
	},	
    navigateToHandle(pageUrl) {
      this.$common.navigateTo(pageUrl)
    },
    // 清除缓存
    clearCache() {
      // 重新获取统一配置信息
      this.$api.shopConfig(res => {
        this.$store.commit('config', res)
      })
      // 删除地区缓存信息
      this.$db.del('areaList')
      setTimeout(() => {
        this.$common.successToShow('清除成功')
      }, 500)
    },
    // 关于我们
    aboutUs() {
	  let articleId = this.$store.state.config.about_article_id;
      this.$common.navigateTo('/pages/article/index?id_type=1&id=' + articleId);
    },
    // 退出登录
    logOff() {
      this.$common.modelShow('退出', '确认退出登录吗?', () => {
        this.$db.del('userToken')
        uni.reLaunch({
          url: '/pages/index/index'
        })
      })
    },
	// 跳转到用户协议
	goAgreement () {
		let articleId = this.$store.state.config.user_agreement_id;
		this.$common.navigateTo('/pages/article/index?id_type=1&id=' + articleId);
	},
	// 跳转到隐私政策
	goPrivacy () {
		let articleId = this.$store.state.config.privacy_policy_id;
		this.$common.navigateTo('/pages/article/index?id_type=1&id=' + articleId);
	}
  }
}
</script>

<style>
.agreement{
	position: fixed;
	bottom: 30rpx;
	width: 100%;
	margin: 20rpx 0;
	text-align: center;
}
.color-o{
	margin: 0 10rpx;
}
</style>
