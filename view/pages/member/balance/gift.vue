<template>
	<view>
		<view class="topArea">
			<view class="wallteArea">
				<view class="wallte_tit">钱包余额（元）</view>
				<view class="wallte_info">
					<view class="money_nums">{{userInfo.balance}}</view>
				</view>
				<view class="bg">
					<image src="/static/img/wallet_card_bg.png" mode=""></image>
				</view>
			</view>
		</view>
		<view class="integral-bottom">
			<view class='cell-group'>
				<view class='cell-item user-head'>
					<view class='cell-item-hd'>
						<view class='cell-hd-title'>转赠手机号</view>
					</view>
					<view class='cell-item-bd'>
						<input class='cell-bd-input' type="number" maxlength="11" placeholder='请填写手机号' v-model="mobile"></input>
					</view>
				</view>
				<view class='cell-item user-head'>
					<view class='cell-item-hd'>
						<view class='cell-hd-title'>转赠余额</view>
					</view>
					<view class='cell-item-bd'>
						<input class='cell-bd-input' type="number" placeholder='请填写转赠余额' v-model="balance"></input>
					</view>
				</view>
			</view>	
		</view>

		
		<view class="button-bottom">
			<button class="btn btn-square btn-b" hover-class="btn-hover2" @click="submitJiuBao()" :disabled='submitStatus'
			 :loading='submitStatus'>提交</button>
		</view>
	</view>
</template>


<script>
	export default{
		data() {
			return {
				showPay:false,
                AffirmStatus: 1,
                passwordArr: [],
                oldPasswordArr: [],
                newPasswordArr: [],
                afPasswordArr: [],
				userInfo:{},
				mobile:'',
				balance:'',
				submitStatus: false
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
		},
		onShow() {
			this.getUserInfo();
		},		
		methods:{
			getUserInfo() {
				// 获取用户信息
				var _this = this
				if (this.$db.get('userToken')) {
					this.$api.userInfo({}, res => {
						if (res.status) {
							_this.userInfo = res.data
						}
					})
				}
			    
			},		
			submitJiuBao(){
				this.submitStatus = true;
				if (!this.rightMobile.status) {
					this.$common.errorToShow(this.rightMobile.msg);
					this.submitStatus = false;
					return;
				}
				if(!this.balance || this.balance<0){
					this.$common.errorToShow("请输入酒宝数量");
					this.submitStatus = false;
					return;
				}
				if(this.balance>this.userInfo.balance){
					this.$common.errorToShow("酒宝不足");
					this.submitStatus = false;
					return;
				}
				this.$api.jiubaoGiftApi({
					mobile:this.mobile,
					balance:this.balance
				},res=>{
					this.closePayUp();
					this.submitStatus = false;
					this.$common.errorToShow(res.msg)
				})
			}
		}		
	}
</script>


<style lang="scss">
	image {
		width: 100%;
		height: 100%;
	}
	

.topArea {
	padding:30rpx;
	background:#fff;
	.wallteArea {
		background:#ff6000;
		width:690rpx;
		height:301rpx;
		position: relative;
		border-radius: 40rpx;
		overflow: hidden;
		padding:40rpx 60rpx;
		color:#fff;
		.wallte_tit {
			width:100%;
			font-size:34rpx;
			overflow: hidden;
		}
		.wallte_info {
			display:flex;
			justify-content: space-between;
			margin-top:40rpx;
			.money_nums {
				font-size:60rpx;
				font-family: OPPOSANS;
			}
			.txbtn {
				background:#fff;
				border-radius: 66rpx;
				overflow:hidden;
				color:#ff6000;
				font-size:28rpx;
				width:120rpx;
				height: 60rpx;
				line-height:60rpx;
				text-align:center;
			}
		}
		.bg {
			position: absolute;
			left:0rpx;
			top:0rpx;
			width:710rpx;
			height:301rpx;
		}
	}

}


.integral-bottom {
	width: 750rpx;
	overflow: hidden;
}

.button-bottom {
	.btn-b {
		width: 100%;
		background:#ff6000;
	}
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