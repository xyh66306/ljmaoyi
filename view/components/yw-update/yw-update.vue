<template>
	<view>
		<lvv-popup position="center" ref="lvvpopref">			
			<view class="update-wrap">
				<view class="white-bg"></view>
				<image src="@/static/imgs/update/update_bg.png" class="updateCon-img"></image>
				<view class="updateCon-top">
					<!-- 发现新版本 -->
					<text class="update-top-title">发现新版本</text>
					<text class="update-top-version">V{{yw_update.new_v}}</text>
				</view>
		
				<text class="uodate-content" v-if="!update_ing">更新内容:{{ yw_update.version_desc }}</text>
				<text class="current-version" v-if="!update_ing">当前版本:V{{yw_update.old_v}}</text>
		
				<view class="update-btn" v-if="!update_ing">
					<view class="update-btn-item update-btn-left" @click="up_close">
						<!-- 残忍拒绝 -->
						<text class="update-btn-item-text ">残忍拒绝</text>
					</view>
					<view class="update-btn-item" @click="nowUpdate">
						<!-- 立即升级 -->
						<text class="update-btn-item-text text-bule">立即升级</text>
					</view>
				</view>
				<!-- 下载进度 -->
				<view class="sche-wrap" v-if="update_ing">
					<!-- 更新包下载中 -->
					<text class="sche-wrap-text">更新包下载中...</text>
					<view class="sche-bg">
						<view class="sche-bg-jindu" :style="lengthWidth">
							<view class="sche-bg-round">
								<text class="sche-bg-round-text" v-if="schedule">{{ schedule }}%</text>
								<text class="sche-bg-round-text" v-else>{{ (downloadedSize / 1024 / 1024).toFixed(2) }}M</text>
							</view>
						</view>
					</view>
				</view>
			</view>
		</lvv-popup>
	</view>
</template>

<script>
	// import uniPopup from '@/uni_modules/uni-popup/components/uni-popup/uni-popup.vue'
	import lvvPopup from '@/components/lvv-popup/lvv-popup.vue';
	export default {
		components: { lvvPopup },
		name:"yw-update",
		props: {
			//参数
			yw_update: {
				type: Object,
				default: {}
			}
		},
		data() {
			return {
				is_update: false, // 是否更新
				is_reques: false, //是否请求中				
				schedule: 0,
				update_ing: false, //点击升级
				is_down: false,
				downloadedSize: 0,
			}
		},
		// mounted() {
		// 	this.checkUpdate() //获取系统新
		// 	// console.log("系统");
		// },
		computed: {
			// 下载进度计算
			lengthWidth: function () {
				return {
					width: this.schedule * 480 / 100 + "rpx"
				}
			}
		},
		watch: {
		    yw_update: {
		        handler(newName, oldName) {
					this.checkUpdate() //检测更新
		        },
		        immediate: true,
		        deep: true
		    }
		},
		methods: {
			// 显示modal弹出框
			toshow() {
				this.$refs.lvvpopref.show();
			},
			// 关闭modal弹出框
			toclose() {
				this.$refs.lvvpopref.close();
			},
			// 检查是否更新③
			checkUpdate() {
				let _this = this;
				var params=_this.yw_update;
				if(params.is_force==1 && params.is_update==1){
					// _this.$refs.popup.show();//显示升级弹窗
					_this.toshow();
				}
			},
			//清除缓存
			removeH(){
				//退出登录
				// this.$store.state.token = '';
				// this.$store.state.userInfo = {};
				// this.$store.state.is_login = false;
				uni.removeStorage({
					key: 'user_info',
					success: function (res) {
						console.log('success');
					}
				});
				
			},
			// 立即更新
			nowUpdate() {
				let _this = this;
				var params=_this.yw_update;
				//退出登录
				//_this.$store.commit('loginout');
				//清除数据
				_this.removeH();
				
				if(params.updateType == 1) {//普通下载更新
					if (_this.is_reques) {
						return false
					} else {
						_this.is_reques = true
					}
					// 热更新方式
					_this.download_wgt()
				}
				if(params.updateType == 2) {//应用商店更新
					// 打开手机应用商店方式
					var download_url = params.download_url;
					plus.runtime.openURL(download_url,function(error){
						console.log('打开应用市场失败', error)
					});
					
					// if(plus.os.name == "Android") {
					// 	// 打开小米
					// 	plus.runtime.openURL('mimarket://details?id=uni.UNI56C65E1',function(error){
					// 		// 打开oppo
					// 		plus.runtime.openURL('oppomarket://details?id=uni.UNI56C65E1',function(error){
					// 			// 打开vivo
					// 			plus.runtime.openURL('vivomarket://details?id=uni.UNI56C65E1',function(error){
					// 				// 打开huawei或者荣耀
					// 				plus.runtime.openURL('appmarket://details?id=uni.UNI56C65E1',function(error){
					// 					console.log('打开huawei失败', error)
					// 					// 用浏览器打开安装包的下载地址
					// 					//plus.runtime.openURL('https://blys.kuailai.shop/download/guizhou.kuailai.apk')
					// 				});
					// 			});
					// 		});
					// 	});
					// } else {
					// 	// plus.runtime.openURL('itms-apps://itunes.apple.com/cn/app/id1591189088',function(error){
					// 	// 	console.log('打开apple store失败', error)
					// 	// });
					// }
				}
			},
			// 取消更新
			up_close() {
				console.log('点击取消')
				plus.os.name == "Android" ? plus.runtime.quit() : plus.ios.import("UIApplication").sharedApplication().performSelector("exit");
			},
			
			// 下载升级资源包
			download_wgt() {
				let _this = this;
				plus.nativeUI.showWaiting("下载更新文件..."); //下载更新文件...
				let options = {
					method: "POST"
				};
				let dtask = plus.downloader.createDownload(_this.yw_update.download_url, {
				});
				dtask.addEventListener("statechanged", function (task, status) {
					console.log("task", task)
					console.log("status", status)
					switch (task.state) {
						case 1: // 开始  
							console.log("task.state1")
							break;
						case 2: //已连接到服务器  
							console.log("task.state2")
							_this.update_ing = true;
							break;
						case 3: // 已接收到数据  
							console.log("task.state3")
							_this.downloadedSize = task.downloadedSize;
							let totalSize = 0;
							if (task.totalSize) {
								totalSize = task.totalSize //服务器须返回正确的content-length才会有长度
							}
							_this.schedule = parseInt(100 * task.downloadedSize / totalSize);
							break;
						case 4:
							console.log("task.state4")
							_this.installWgt(task.filename); // 安装wgt包  
							break;
					}
				});
				dtask.start();
			},
			
			// 安装文件
			installWgt(path) {
				let _this = this;
				plus.nativeUI.showWaiting("安装更新文件..."); //安装更新文件...
				plus.runtime.install(path, {}, function () {
					plus.nativeUI.closeWaiting();
					// 应用资源更新完成！
					plus.nativeUI.alert("应用资源更新完成！请重启应用", function () {
						plus.runtime.restart();
					});
				}, function (e) {
					plus.nativeUI.closeWaiting();
					// 安装更新文件失败
					plus.nativeUI.alert("安装更新文件失败[" + e.code + "]：" + e.message);
				});
			},
			
		}
	}
</script>


<style scoped>
/*#ifndef APP-NVUE*/
view {
	display: flex;
	flex-direction: column;
	box-sizing: border-box;
}

/*#endif*/

.updateBox {
	background-color: rgba(0, 0, 0, 0.6);
	z-index: 1000;
	position: fixed;
	right: 0px;
	top: 0;
	left: 0;
	bottom: 0;
	align-items: center;
	justify-content: center;
}

.update-wrap {
	width: 580rpx;
	height: 500rpx;
	border-radius: 10px;
	padding-bottom: 0px;
	position: relative;
}

.white-bg {
	position: absolute;
	top: 60rpx;
	left: 0px;
	width: 580rpx;
	height: 440rpx;
	background-color: #ffffff;
	border-bottom-left-radius: 30rpx;
	border-bottom-right-radius: 30rpx;
}

.updateCon-img {
	position: absolute;
	top: 0rpx;
	left: 0px;
	width: 580rpx;
	height: 440rpx;
}

.updateCon-top {
	padding: 70rpx 50rpx;
	/*#ifndef APP-NVUE*/
	z-index: 1;
	/*#endif*/
}

.update-top-title {
	color: #fff;
	font-size: 36rpx;
	font-weight: bold;
}

.update-top-version {
	color: #fff;
	font-size: 28rpx;
	margin-top: 10rpx;
}

.uodate-content {
	color: #333;
	font-size: 26rpx;
	padding: 0px 50rpx;
	margin-top: 80rpx;
	/*#ifndef APP-NVUE*/
	z-index: 1;
	/*#endif*/
}

.current-version {
	text-align: center;
	margin-top: 10rpx;
	font-size: 24rpx;
	color: #666;
	/*#ifndef APP-NVUE*/
	z-index: 1;
	/*#endif*/
}

.update-btn {
	position: absolute;
	left: 0px;
	bottom: 0px;
	width: 580rpx;
	height: 80rpx;
	padding: 0px 50rpx;

	align-items: center;
	flex-direction: row;
	border-top-color: #e7e7e7;
	border-top-style: solid;
	border-top-width: 1px;

	background-color: #fff;
	border-bottom-left-radius: 30rpx;
	border-bottom-right-radius: 30rpx;
}

.update-btn-item {
	width: 240rpx;
	height: 80rpx;
	justify-content: center;
	align-items: center;
}

.update-btn-left {
	border-right-color: #e7e7e7;
	border-right-style: solid;
	border-right-width: 1px;
}

.update-btn-item-text {
	text-align: center;
	font-size: 28rpx;
	color: #666;
}

.text-bule {
	color: #045FCF;
}

.sche-wrap {
	padding: 0px 50rpx 0rpx;
	height: 100rpx;
	align-items: center;
	border-bottom-left-radius: 30rpx;
	border-bottom-right-radius: 30rpx;
	flex: 1;
	justify-content: flex-end;
}

.sche-wrap-text {
	font-size: 24rpx;
	color: #666;
	margin-bottom: 20rpx;
}

.sche-bg {
	position: relative;
	background-color: #ccc;
	height: 20rpx;
	border-radius: 100px;
	width: 480rpx;
	margin-bottom: 30rpx;
}

.sche-bg-jindu {
	position: absolute;
	left: 0px;
	top: 0px;
	height: 20rpx;
	background-color: #5775e7;
	border-radius: 100px;
}

.sche-bg-round {
	position: absolute;
	left: 100%;
	height: 24rpx;
	width: 24rpx;
	background-color: #fff;
	border-color: #fff;
	border-style: solid;
	border-width: 10px;
	border-radius: 100px;
	transform: translateX(-20rpx) translateY(-10rpx);

}

.sche-bg-round-text {
	font-size: 24rpx;
	width: 120rpx;
	text-align: center;
	transform: translateX(-50rpx) translateY(-40rpx);
	color: #5775e7;
}

.uodate-close {
	/* display: flex; */
	flex-direction: row;
	justify-content: center;
	padding-top: 50rpx;
}

.uodate-close-img {
	width: 80rpx;
	height: 80rpx;
}
</style>

