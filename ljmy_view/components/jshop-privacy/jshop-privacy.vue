<template>
	<lvv-popup ref="popup" position="bottom" class="privacy-popup">
		<view class="jshop-wx-privacy">
		  <view class="modal-container">
		    <view class="privacy__hd">
		      <text class="privacy__title">{{title}}</text>
		    </view>
		    <view class="privacy__bd">
		      <text class="privacy__tips">为了向您提供更好的服务，我们须向您收集相关的个人信息，了解</text>
		      <text class="privacy__tips privacy-contract" @tap="openPrivacyContract">{{urlTitle}}</text>
		      <text class="privacy__tips">详细信息。如您同意，请点击“同意”开始接受我们的服务。</text>
		    </view>
		    <view class="privacy__ft">
		      <button
		        id="disagree-btn"
		        type="default"
		        class="disagree-btn"
		        @tap="handleDisagree"
		      >拒绝</button>
		      <button
		        id="agree-btn"
		        type="default"
		        open-type="agreePrivacyAuthorization"
		        class="agree-btn"
		        @agreeprivacyauthorization="handleAgree"
		      >同意</button>
		    </view>
		  </view>
		</view>
	</lvv-popup>
</template>

<script>
	export default {
		data(){
			return {
				innerShow: true,
				title: '隐私保护指引',
				urlTitle: ''
			}
		},
		mounted() {
			console.log('dfvd')
			let privacy = this.$db.get('privacy');
			if(!privacy){
				this.init();
			}
		},
		methods: {
			init(){
				wx.getPrivacySetting({
				  success: res => {
						const { privacyContractName } = res;
						this.urlTitle = privacyContractName;
						this.$refs.popup.show();
				  },
				  fail: (e) => {
				    console.log('getPrivacySetting err:', e)
				  },
				  complete: () => { }
				})
			},
			openPrivacyContract(){
				wx.openPrivacyContract({
					success: () => {}, // 打开成功
					fail: (res) => {console.log(res)}, // 打开失败
					complete: () => {}
				})
			},
			handleDisagree(){
				this.$refs.popup.close();
			},
			handleAgree(){
				uni.setStorage({key: 'privacy',data: true});
				this.$refs.popup.close();
			}
		}
	}
</script>

<style lang="scss" scoped>
	.jshop-wx-privacy {
		height: 520rpx;
		width: 100%;
		background-color: #fff;
		border-radius:  20rpx 20rpx 0px 0px;
		padding: 60rpx 54rpx 70rpx;
		position: fixed;
		bottom: 0;
		.privacy__hd{
			color: #0A0A0A;
			font-size: 32rpx;
			font-weight: 600;
			text-align: center;
			margin-bottom: 46rpx;
		}
		.privacy__bd {
			color: #4D4B4B;
			font-size: 28rpx;
			font-weight: 300;
		}
		.privacy-contract {
			color: #0A0A0A;
			font-weight: 400;
		}
		.privacy__ft {
			display: flex;
			width: 100%;
			justify-content: space-around;
			margin-top: 56rpx;
			button {
				width: 160rpx;
				height: 62rpx;
				lighting-color: 62rpx;
				background-color: #fff;
				font-weight: 400;
				font-size: 24rpx;
				color: #09c161;
				background-color: #F2F2F2;
				&::after{
					border: 0!important;
				}
			}
			.agree-btn{
				color: #fff;
				background-color: #09c161;
			}
		}
	}

</style>