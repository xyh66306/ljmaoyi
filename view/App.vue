<script>
export default {
	onLaunch() {
		// 重新获取统一配置信息
		this.$api.shopConfig(res => {
		  this.$store.commit('config', res)
		})
		// #ifdef APP-PLUS || APP-PLUS-NVUE
		this.checkVersion()
		// #endif
		
		// #ifdef MP-WEIXIN
		this.checkMiniProgramVersion()
		// #endif			
	},
	onShow: function() {
		//console.log('App Show')
	},
	onHide: function() {
		//console.log('App Hide')
	},
	methods: {
		// #ifdef MP-WEIXIN
		// 微信小程序版本更新检测
		checkMiniProgramVersion() {
			if (!wx.canIUse('getUpdateManager')) return
		
			const updateManager = wx.getUpdateManager()
			
			updateManager.onCheckForUpdate((res) => {
				// 请求完新版本信息的回调
				if (res.hasUpdate) {
					console.log('发现新版本')
				}
			})
		
			updateManager.onUpdateReady(() => {
				uni.showModal({
					title: '更新提示',
					content: '新版本已经准备好，是否重启应用？',
					success: (res) => {
						if (res.confirm) {
							// 新的版本已经下载好，调用 applyUpdate 应用新版本并重启
							updateManager.applyUpdate()
						}
					}
				})
			})
		
			updateManager.onUpdateFailed(() => {
				// 新的版本下载失败
				uni.showToast({
					title: '新版本下载失败',
					icon: 'none'
				})
			})
		},
		// #endif		
		// #ifdef APP-PLUS || APP-PLUS-NVUE
		// app更新检测
		checkVersion() {
			// 获取应用版本号
			let version = plus.runtime.version;

			//检测当前平台，如果是安卓则启动安卓更新
			uni.getSystemInfo({
				success: res => {
					this.updateHandler(res.platform, version);
				}
			})
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
						}
					}
				}
			)
		}
		// #endif
	}
}
</script>

<style lang="scss">
/*每个页面公共css */
@import './static/css/style.css';
@import './static/font/iconfont.css';
@font-face {
  font-family: 'OPPOSANS';
  src: url('./static/font/OPPOSANS.ttf') format('truetype');
  font-weight: normal;
  font-style: normal;
}
.bgf {
	background: #fff;
}

.flc {
	display: flex;
	justify-content: space-between;
	align-items: center;
}

.flc-inline {
	display: inline-flex;
	align-items: center;
}

.g5 {
	color: $g5;
}

	.fz12 {
		font-size: $fz12;
	}
	.displayC{
		display: flex;
		align-items: center;
	}
	.fw7{
		font-weight: 700;
	}
</style>
<style>
@import url('/components/gaoyia-parse/parse.css');
</style>
