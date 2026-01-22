<template>
	<view class="content">
		<view class="topArea">
			<view class="wallteArea">
				<view class="bg">
					<image src="/static/img/wallet_card_bg.png" mode=""></image>
				</view>
				<view class="wallteArea_box">
					<view class="top">
						<view class="wallte_tit">酒豆</view>
						<view class="money_nums">{{userInfo.dou}}</view>
					</view>
					<view class="wallte_info">
						<view class="txbtn" @click="gotopage('/pages/member/dou/zjb')">转酒宝</view>
						<view class="txbtn" @click="gotopage('/pages/member/dou/zyb')">转元宝</view>
						<!-- <view class="zzbtn" @click="gotopage('/pages/member/balance/gift')">红包</view> -->
					</view>
				</view>
			</view>
		</view>
		
		<view class="tabLst">
			<ive-tab :tabArr="tabArr" :current="tab" @onClick="onClickItem"></ive-tab>
		</view>
		
		<view class="type-c"
		v-if="list.length"
		>
			<view class="cell-group margin-cell-group"
			v-for="(item, index) in list"
			:key="index"
			>
				<view class="cell-item">
					<view class="cell-item-hd">
						<view class='cell-hd-title'>{{ item.type }}</view>
					</view>
					<view class="cell-item-ft">
						<view class="cell-ft-p color-9">
							{{ item.ctime }}
						</view>
					</view>
				</view>
				<view class="cell-item">
					<view class="cell-item-hd">
						<view class='cell-hd-title color-9'>余额：{{ item.balance }}</view>
					</view>
					<view class="cell-item-ft red-price">
						<block v-if="item.flow == 1"> + </block>
						<block v-else>-</block>
						{{ item.num }}
					</view>
				</view>
			</view>
			<uni-load-more
			:status="loadStatus"
			></uni-load-more>
		</view>
		<view class="order-none" v-else>
			<image class="balance-none-img" src="/static/img/order-empty.png" mode=""></image>
		</view>
		
	</view>
</template>

<script>
import iveTab from "@/components/ive-tab/ive-tab.vue";	
export default {
	data () {
		return {
			userInfo: {},
			tabArr:[
				{
				name:'全部',
				},
				{
				name:'收入',
				},
				{
				name:'支出',
				},								
			],
			tab:0,
			index: 0,	// 默认选中的类型	索引
			page: 1,
			limit: 10,
			list: [],
			loadStatus: 'more'
		}
	},
	components:{
		iveTab
	},
	onLoad () {
		this.getUserInfo();
		this.balances()//修复多次加载问题
	},
	onReachBottom () {
		if (this.loadStatus === 'more') {
			this.balances()
		}
	},
	methods: {
		// 订单状态切换
		onClickItem(index) {
			if (this.tab !== index) {
				this.tab = index;
				this.list = [];
				this.page = 1;
				this.balances()
			}
		},
		// 获取余额明细
		balances () {
			let flow = '';
			if(this.tab == 0){
				flow = false
			}else if(this.tab == 1){
				flow = 1
			}else{
				flow = 2
			}
			let data = {
				flow:flow,
				page: this.page,
				limit: this.limit
			}			
			this.loadStatus = 'loading'			
			this.$api.getDouLstApi(data, res => {
				if (res.status) {
					if (this.page >= res.total) {
						// 没有数据了
						this.loadStatus = 'noMore'
					} else {
						// 未加载完毕
						this.loadStatus = 'more'
						this.page ++
					}
					this.list = [...this.list, ...res.data]
				} else {
					this.$common.errorToShow(res.msg)
				}
			})
		},		
		// 获取用户信息
		getUserInfo () {
			let _this = this;
			uni.getSystemInfo({
				success: function (res) {
					_this.platform = res.platform;
				}
			});
			
			this.$api.userInfo({}, res => {
				if (res.status) {
					this.userInfo = res.data
				} else {
					this.$common.errorToShow(res.msg)
				}
			})
		},
		// 页面跳转
		gotopage(url) {
			console.log(url);
			this.$common.navigateTo(url)
		}
	}
}	
</script>

<style lang="scss">
image {
	width:100%;
	height:100%;
}	
page {
	background-color: #f5f5f5;
	font-family: system-ui, -apple-system, Segoe UI, Roboto, Helvetica Neue, Arial, Noto Sans, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", Segoe UI Symbol, "Noto Color Emoji";
}
.topArea {
	padding:30rpx;
	background:#fff;
	.wallteArea {
		background:#e97e3d;
		width:690rpx;
		height:301rpx;
		position: relative;
		border-radius: 40rpx;
		overflow: hidden;
		padding:40rpx 60rpx;
		color:#fff;
		.wallteArea_box {
			position: absolute;
			left:50rpx;
			top:50rpx;
			width:600rpx;
			height:200rpx;
			display:flex;
			justify-content: space-between;
			.top {
				display:flex;
				flex-direction: column;
				
				.money_nums {
					font-size:50rpx;
					font-family: OPPOSANS;
					margin-top: 20rpx;
				}
				.wallte_tit {
					width:100%;
					font-size:42rpx;
					overflow: hidden;
				}
			}

		}

		.wallte_info {
			width:260rpx;
			display:flex;
			flex-direction: column;
			align-items: center;
			justify-content: space-between;
			// padding:20rpx 14rpx;
			margin-top:30rpx;
			border-left:rgba(255, 255, 255,0.45) 1rpx solid;
			.txbtn {
				background:rgba(255, 255, 255,1);
				border-radius:20rpx;
				overflow:hidden;
				color:#ff6000;
				font-size:28rpx;
				width:140rpx;
				height: 60rpx;
				line-height:60rpx;
				text-align:center;
			}
			.zzbtn {
				background:rgba(255, 255, 255, 0.92);
				border-radius: 20rpx;
				overflow:hidden;
				color:#ff6000;
				font-size:28rpx;
				width:140rpx;
				height: 60rpx;
				line-height:60rpx;
				text-align:center;
			}
		}
		.bg {
			width:710rpx;
			height:301rpx;
		}
	}

}

.tabLst {
	font-size:28rpx;
	background:#fff;
	margin-top:20rpx;
}

.order-none{
	text-align: center;
	padding: 200upx 0;
}
.balance-none-img{
	width: 274upx;
	height: 274upx;
}
</style>
