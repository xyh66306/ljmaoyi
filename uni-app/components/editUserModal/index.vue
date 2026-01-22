<template>
	<view>
		<view class="product-window" :class="{'on':isShow}">
			<view class="iconfont icon-guanbi" @click="closeAttr"></view>
			<view class="mp-data">
				<image :src="mpData.site_logo" mode=""></image>
				<text class="mp-name">{{mpData.site_name || ''}} 申请</text>
			</view>
			<view class="trip-msg">
				<view class="title">
					获取您的昵称、头像
				</view>
				<view class="trip">
					提供具有辨识度的用户中心界面
				</view>
			</view>
			<form @submit="formSubmit">
				<view class="edit">
					<view class="avatar edit-box">
						<view class="left">
							<view class="head">头像</view>
							<view class="avatar-box" v-if="!mp_is_new" @click.stop='uploadAvatar'>
								<image :src="userInfo.avatar || defHead"></image>
							</view>
							<button v-else class="avatar-box" open-type="chooseAvatar" @chooseavatar="onChooseAvatar">
								<image :src="userInfo.avatar || defHead"></image>
							</button>
						</view>
					</view>
					<view class="nickname edit-box">
						<view class="left">
							<view class="head">昵称</view>
							<view class='input'><input type='nickname' placeholder-class="pl-sty"
									placeholder="请输入昵称" name='nickname' :maxlength="16"
									:value='userInfo.nickname'></input>
							</view>
						</view>
						<!-- <view class="iconfont icon-xiangyou"></view> -->
					</view>
				</view>

				<view class="bottom">
					<button class="save" formType="submit" :class="{'open': userInfo.avatar}">
						保存
					</button>
				</view>
			</form>
		</view>
		<canvas canvas-id="canvas" v-if="canvasStatus"
			:style="{width: canvasWidth + 'px', height: canvasHeight + 'px',position: 'absolute',left:'-100000px',top:'-100000px'}"></canvas>
		<view class="mask" @touchmove.prevent v-if="isShow" @click="closeAttr"></view>
	</view>

</template>

<script>	
	import {
		baseUrl,apiBaseUrl
	} from '@/config/config.js';	
	export default {
		props: {
			isShow: {
				type: Number,
				value: 0
			}
		},
		data() {
			return {
				avatar:'',
				defHead: require('@/static/images/f.png'),
				mp_is_new:false,
				userInfo: {
					avatar: '',
					nickname: '',
				},
				mpData: {
					site_logo: 'https://wap.wansenjiankang.cn/static/uploads/images/2024/12/21/173474968067662df03e970.png',
					site_name: '万森堂'
				},
				canvasStatus: false,
			};
		},
		mounted() {
			const version = uni.getSystemInfoSync().SDKVersion;
			if(this.compareVersion(version, '2.21.2') >= 0){
				this.mp_is_new = true;
			}
		},
		methods: {
			/**
			 * 小程序比较版本信息
			 * @param v1 当前版本
			 * @param v2 进行比较的版本 
			 * @return boolen
			 * 
			 */
			compareVersion(v1, v2) {
				v1 = v1.split('.')
				v2 = v2.split('.')
				const len = Math.max(v1.length, v2.length)
			
				while (v1.length < len) {
					v1.push('0')
				}
				while (v2.length < len) {
					v2.push('0')
				}
			
				for (let i = 0; i < len; i++) {
					const num1 = parseInt(v1[i])
					const num2 = parseInt(v2[i])
			
					if (num1 > num2) {
						return 1
					} else if (num1 < num2) {
						return -1
					}
				}
				return 0
			},
			// 用户上传头像
			uploadAvatar() {
				this.$api.uploadFiles(res => {
					if (res.status) {
						let avatar = res.data.url // 上传成功的图片地址
						// 执行头像修改
						this.$api.changeAvatar({
							avatar: avatar
						}, res => {
							if (res.status) {
								this.$common.successToShow('上传成功', () => {
									this.avatar = res.data.avatar
								})
							} else {
								this.$common.errorToShow(res.msg)
							}
						})
					} else {
						this.$common.errorToShow(res.msg)
					}
				})
			},
			// 微信头像获取
			onChooseAvatar(e) {
				const {
					avatarUrl
				} = e.detail
				this.userInfo.avatar = avatarUrl
				let that = this;
				uni.uploadFile({
					url: apiBaseUrl + 'api.html', //仅为示例，非真实的接口地址
					filePath: avatarUrl,
					fileType: 'image',
					name: 'file',
					headers: {
						'Accept': 'application/json',
						'Content-Type': 'multipart/form-data',
					},
					formData: {
						'method': 'images.upload',
						'upfile': avatarUrl
					},
					success: (uploadFileRes) => {
						let res =  JSON.parse(uploadFileRes.data);
						if(res.status){
							that.$api.changeAvatar({
								avatar: res.data.image_id
							}, res => {
								if (res.status) {
									this.$common.successToShow('上传成功', () => {
										this.avatar = res.data.avatar
									})
								} else {
									this.$common.errorToShow(res.msg)
								}
							})							
						}
					},
					fail: (error) => {
						console.log("error",error);
						if (error && error.response) {
							this.$common.errorToShow(error.response)
						}
					},
				});
								
			},
			closeAttr: function() {
				this.$emit('closeEdit');
			},
			/**
			 * 提交修改
			 */
			formSubmit(e) {
				let that = this
				if (!this.avatar){
					return that.$common.errorToShow("请上传头像");
				} 
				if (!e.detail.value.nickname){
					return that.$common.errorToShow("请输入昵称");
				}
				this.userInfo.nickname = e.detail.value.nickname;
				this.$emit('editSuccess',this.userInfo)
			}
		}
	}
</script>
<style>
	.pl-sty {
		color: #999999;
		font-size: 30rpx;
	}
</style>
<style scoped lang="scss">
	.product-window.on {
		transform: translate3d(0, 0, 0);
	}

	.mask {
		z-index: 100;
	}

	.product-window {
		position: fixed;
		bottom: 0;
		width: 100%;
		left: 0;
		background-color: #fff;
		z-index: 1000;
		border-radius: 20rpx 20rpx 0 0;
		transform: translate3d(0, 100%, 0);
		transition: all .3s cubic-bezier(.25, .5, .5, .9);
		padding: 38rpx 40rpx;
		padding-bottom: 80rpx;
		padding-bottom: calc(80rpx+ constant(safe-area-inset-bottom)); ///兼容 IOS<11.2/
		padding-bottom: calc(80rpx + env(safe-area-inset-bottom)); ///兼容 IOS>11.2/

		.icon-guanbi {
			position: absolute;
			top: 30rpx;
			right: 30rpx;
			font-size: 24rpx;
			font-weight: bold;
			color: #999;
			padding: 10rpx;
		}

		.mp-data {
			display: flex;
			align-items: center;
			margin-bottom: 30rpx;

			.mp-name {
				font-size: 28rpx;
				font-weight: bold;
				color: #000000;
			}

			image {
				width: 48rpx;
				height: 48rpx;
				border-radius: 50%;
				margin-right: 16rpx;
			}
		}

		.trip-msg {
			padding-bottom: 32rpx;
			border-bottom: 1px solid #F5F5F5;

			.title {
				font-size: 30rpx;
				font-weight: bold;
				color: #000;
				margin-bottom: 6rpx;
			}

			.trip {
				font-size: 26rpx;
				color: #777777;
			}
		}

		.edit {
			border-bottom: 1px solid #F5F5F5;

			.avatar {
				border-bottom: 1px solid #F5F5F5;
			}

			.nickname {
				.input {
					width: 100%;

				}

				input {
					height: 80rpx;
				}
			}

			.edit-box {
				display: flex;
				justify-content: space-between;
				align-items: center;
				font-size: 30rpx;
				padding: 22rpx 0;

				.left {
					display: flex;
					align-items: center;
					flex: 1;

					.head {
						color: rgba(0, 0, 0, 0.9);
						white-space: nowrap;
						margin-right: 60rpx;
					}

					button {
						display: flex;
						align-items: center;
						justify-content: center;
						width: 84rpx;
						height: 84rpx;
						border-radius: 6rpx;
						margin: unset;
						padding: unset;
						border: unset;
					}
				}

				image {
					width: 80rpx;
					height: 80rpx;
					border-radius: 6rpx;
				}
			}

			.icon-xiangyou {
				color: #cfcfcf;
			}
		}

		.bottom {
			display: flex;
			align-items: center;
			justify-content: center;

			.save {
				border: 1px solid #F5F5F5;
				display: flex;
				align-items: center;
				justify-content: center;
				width: 368rpx;
				height: 80rpx;
				border-radius: 12rpx;
				margin-top: 52rpx;
				background-color: #F5F5F5;
				color: #ccc;
				font-size: 30rpx;
				font-weight: bold;
			}

			.save.open {
				border: 1px solid #fff;
				background-color: #07C160;
				color: #fff;
			}
		}
	}
	.avatar-box {
		display: flex;
		align-items: center;
	
		image {
			width: 30rpx;
			height: 30rpx;
			border-radius: 50%;
		}
	}
</style>
