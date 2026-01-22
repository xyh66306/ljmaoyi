<template>
	<view>
		<view class="topArea">
			<view class="wallteArea">
				<view class="wallte_tit">钱包酒豆</view>
				<view class="wallte_info">
					<view class="money_nums">{{userInfo.dou}}</view>
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
						<view class='cell-hd-title'>转元宝</view>
					</view>
					<view class='cell-item-bd'>
						<input class='cell-bd-input' type="number" placeholder='请填写转如元宝数量' v-model="dou"></input>
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
				userInfo:{},
				mobile:'',
				dou:'',
				submitStatus: false
			}
		},
		onLoad() {
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
				
				if(parseInt(this.dou)>parseInt(this.userInfo.dou)){
					this.$common.errorToShow("酒豆不足");
					this.submitStatus = false;
					return;
				}
				this.$api.dtoexpApi({
					dou:parseInt(this.dou)
				},res=>{
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
	width: 702rpx;
	margin: 0 auto;
	overflow: hidden;
}

.button-bottom {
	.btn-b {
		width: 100%;
		background:#ff6000;
	}
}
</style>