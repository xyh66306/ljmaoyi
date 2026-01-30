<template>
	<view class="content">
        <view class="topArea">
			<view class="bg">
				<image src="/static/images/vip_bg.png" mode=""></image>
			</view>
			<view class="card_area">
				<view class="rowArea userinfo">
					<image :src="userInfo.avatar" mode=""></image>
					<view class="info">
						<view class="name">{{userInfo.nickname}}</view>
						<view class="welcome">您与 {{shopName}} 的第{{registerDays}}天.</view>
					</view>
				</view>
				<view class="rowArea vipprofit">
					<view class="left">
						升级即享会员权益
					</view>
					<view class="vipBtn">升级会员</view>
				</view>	
			</view>
		</view>
		<view class="wrap">
			<view class="hyqy">
				<view class="bg">
					<image src="/static/images/vip_profit.png" mode=""></image>
				</view>
				<view class="hyqy_txt">
					<text class="iconfont icon-kuozhanVIP"></text>
					SVIP专属权益
				</view>
			</view>
			<view class="hyqyItems">
				<view class="items" v-for="(vo,index) in qyItems" :key="index">
					<image :src="vo.image" mode=""></image>
					<view class="info">
						<view class="name">{{vo.name}}</view>
						<view class="desc">{{vo.desc}}</view>
					</view>
				</view>
			</view>
		</view>

	</view>
</template>

<script>
export default {
	data() {
		return {
			userInfo:{},
			qyItems:[
				{
					image: '/static/logo.png',
					name: '会员优惠',
					desc: '每月获得优惠'
				},
				{
					image: '/static/logo.png',
					name: '线下折扣',
					desc: '线下付款享受折扣'
				},
				{
					image: '/static/logo.png',
					name: '签到返利',
					desc: '每日签到返积分'
				},
				{
					image: '/static/logo.png',
					name: '运费折扣',
					desc: '运费折扣优惠'
				},
				{
					image: '/static/logo.png',
					name: '专属特价',
					desc: '商品享特价优惠'
				},
				{
					image: '/static/logo.png',
					name: '消费返利',
					desc: '消费返多倍红包'
				}				
			]
		};
	},
	computed: {
		shopName() {
			return this.$store.state.config.shop_name;
		},
		registerDays() {
			// 获取当前日期（不包含时间部分）
			const registerTime = this.userInfo.ctime*1000
			const now = new Date();
			const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());

			// 获取注册日期（不包含时间部分）
			const registerDate = new Date(registerTime);
			const registerDay = new Date(registerDate.getFullYear(), registerDate.getMonth(), registerDate.getDate());	
			// 计算相差天数
			const timeDiff = today.getTime() - registerDay.getTime();
			const daysDiff = Math.floor(timeDiff / (1000 * 3600 * 24));			
			return daysDiff + 1;					
		}		
	},	
	onLoad() {
		this.getUserInfo();
	},
	methods: {
		getUserInfo(){
			var _this = this				
			_this.$api.userInfo({}, res => {
				_this.userInfo = res.data
			})
		}
	}
};
</script>

<style lang="scss" scoped>
	
page {
	font-family: PingFang SC;
	background: #f5f5f5;
}	
image {
	width: 100%;
	height: 100%;
}	
.topArea {
	width: 100%;
	height: 328rpx;
	position: relative;
	.bg {
		width: 100%;
		height: 100%;
	}
	.card_area {
		position: absolute;
		top: 0rpx;
		left: 0;
		width: 100%;
		height: 272rpx;
		padding: 60rpx 66rpx;
		box-sizing: border-box;
		.rowArea {
			display: flex;
			align-items: center;
		}
		.userinfo {
			margin-top: 20rpx;
			image {
				width: 80rpx;
				height: 80rpx;
				border-radius: 50%;
				overflow: hidden;
			}
			.info {
				margin-left: 20rpx;
				.name {
					font-size: 30rpx;
					line-height: 42rpx;
					font-weight: 700;
					color: #333333;
				}
				.welcome {
					font-size: 22rpx;
					line-height: 30rpx;
					color: #89735b;
				}
			}			
		}		
	
		.vipprofit {
			justify-content: space-between;
			margin-top: 70rpx;
			font-size: 24rpx;
			color: #ae5a2a;
			.vipBtn {
				width: 146rpx;
				height: 44rpx;
				border-radius: 22rpx;
				background-color: #fff;
				line-height: 44rpx;
				text-align: center;
			}
		}
	}
}

.wrap {
	padding-top: 40rpx;
	background: #fff;
}

.hyqy {
	width:540rpx;
	height:90rpx;
	margin: 0rpx auto;
	position: relative;
	.bg {
		width:540rpx;
		height:90rpx;
	}
	.hyqy_txt {
		position: absolute;
		top: 0;
		left: 0;
		width: 100%;
		height: 100%;
		display: flex;
		justify-content: center;
		font-size: 34rpx;
		color: #89735b;
		font-weight: 700;
		line-height: 130rpx;
		.iconfont {
			font-size: 50rpx;
		}
	}
}

.hyqyItems {
	width: 690rpx;
	margin: 20rpx auto 0;
	display: flex;
	justify-content: space-between;
	flex-wrap: wrap;
	.items {
		width: 340rpx;
		height: 140rpx;
		margin-bottom: 20rpx;
		display: flex;
		align-items: center;
		background-color: #f7f7f7;
		image {
			width: 80rpx;
			height: 80rpx;
			margin-right: 20rpx;
		}
		.info {
			.name {
				font-size: 30rpx;
				line-height: 42rpx;
				font-weight: 700;
				color: #333333;
			}
			.desc {
				
			}
		}
	}		
}

</style>