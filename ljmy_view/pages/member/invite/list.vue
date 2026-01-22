<template>
	<view class="content">
		<view class="head flex">
			<view class="bg"></view>
			<view class="mymoney flex">
				<view class="allmoney commoney flex">
					<view class="num">{{total.commission || 0}}</view>
					<view class="txt">团队消费</view>
				</view>
				<view class="currmoney commoney flex">
					<view class="num">{{total.count || 0}}</view>
					<view class="txt">团队数量</view>
				</view>				
			</view>
		</view>	
		<view class="blank80"></view>	
		<view class="collection">
			<view class="container_of_slide" v-for="(item, index) in lists" :key="index">
				<view class="slide_list">
					<view class="now-message-info" hover-class="uni-list-cell-hover">
						<view class="icon-circle">
							<image class='goods-img' :src="item.avatar" mode="aspectFill"></image>
						</view>
						<view class="list-right">
							<view class="list-title">{{ item.nickname }} </view>
							<view class="list-detail color-6">{{item.grade_name}}</view>
						</view>
						<view class="team">团队：<text>{{item.count}}</text>人</view>
					</view>
				</view>
			</view>
			<uni-load-more :status="loadStatus"></uni-load-more>
		</view>
	</view>
</template>

<script>
import uniLoadMore from '@/components/uni-load-more/uni-load-more.vue'
export default {
	components: {
		uniLoadMore
	},
	data() {
		return {
			lists: [],
			page: 1, //当前页
			limit: 10, //每页显示几条
			loadStatus: 'more',
			items: [],			
			current: 0,
			uid:0,
			grade:0,
			total:[],
			userInfo: {},
		};
	},
	onLoad (e) {		
		this.uid = e.id
		this.getDataList();
		this.getProftData();
		this.getUserInfo();
	},
	onReachBottom () {
		if (this.loadStatus === 'more') {
			this.getDataList()
		}
	},
	methods: {	
		// 获取用户信息
		getUserInfo() {
			this.$api.userInfo({}, res => {
				if (res.status) {
					// this.userInfo = res.data
					if(res.data.grade>=3){
						this.items= ['全部','普通用户','VIP','消费商','服务商']
					}
				}
			});
		},
		// tab点击切换
		onClickItem(index) {			
			if (this.current !== index) {
				this.grade = index								
				this.current = index;
				this.page = 1;
				this.lists = [];
				this.getDataList();
			}
		},	
		getProftData(){
			this.$api.getProfit({
				uid:this.uid
			}, res => {
				this.total = res.data
			})
				
		},
		getDataList() {
			this.loadStatus = 'loading'
			let data = {
				uid:this.uid,
				grade:this.grade,
				page: this.page,
				limit: this.limit
			}
			this.$api.recommendList(data, res => {
				if (res.status) {
				    for (let i = 0; i < res.data.length; i++) {
						if (res.data[i].avatar == null) {
							res.data[i].avatar = this.$store.state.config.shop_default_image;
						}
						if (res.data[i].nickname == null) {
							res.data[i].nickname = '暂无昵称'
						}
				    }
					let lists = this.lists.concat(res.data);
					this.lists = lists;
					if (res.total > this.page) {
						this.page++
						this.loadStatus = 'more'
					} else {
						this.loadStatus = 'noMore'
					}
				}else{
					this.$common.errorToShow(res.msg)
				}
			});
		}
	},
};
</script>

<style lang="scss">
page {
	background-color: #F7F7F7;
}	
.head {
	position: relative;
	.bg {
		width: 750upx;
		height: 130upx;
		background-color:#05502C;		
	}
	.bg:after {
		width: 100%;
		height: 20rpx;
		position: absolute;
		left: 0;
		bottom: -20rpx;
		content: '';
		border-radius: 0 0 50% 50%;
		background:#05502C;
	}			
	.box {
		position: absolute;
		top: 40upx;
		left: 50upx;
		width: 450upx;
		height: 110upx;
		color: #FFFFFF;
		.img{
			width: 120upx;
			height: 120upx;
			border-radius: 50%;
			overflow: hidden;
		}
		.uinfo {
			margin-left: 20upx;
			flex-direction: column;
			justify-content: center;
			margin-top: 10upx;
			.uid {
				margin-top: 6upx;
			}
		}
	}
	.mymoney {
		position: absolute;
		bottom: -80upx;
		left:10%;
		width:627upx;
		height:150upx;
		background:rgba(255,255,255,1);
		box-shadow:0upx 2upx 5upx 1upx rgba(0, 0, 0, 0.1);
		border-radius:10upx;			
	}
	.mymoney {
		justify-content: center;
		text-align: center;
		.commoney{
			width: 50%;
			flex-direction: column;
			justify-content: center;
			.num {
				font-weight:bold;
				color:rgba(51,51,51,1);	
				font-size: 38upx;
			}	
			.txt {
				color:rgba(102,102,102,1);
				font-size: 25upx;
				margin-top: 10upx;
			}							
		}
	}
}	
.blank80	{
	width: 100%;
	height: 100rpx;
	overflow: hidden;
}
.wrap {
	width: 690rpx;
	margin: 0 auto;
}
.collection .goods-img{
	width: 100%;
	height: 100%;	
}
.container_of_slide {
	width: 690rpx;
	margin: 20rpx auto 0;
	border-radius: 6rpx;
	overflow: hidden;
}
.slide_list {
	transition: all 100ms;
	transition-timing-function: ease-out;
	min-width: 100%;
	background: #FFFFFF;
		
}
.now-message-info {
	box-sizing:border-box;
	display: flex;
	align-items: center;
	font-size: 16px;
	clear:both;
	padding: 20upx 26upx;
	margin-bottom: 2upx;
	width: 100%;
}

.group-btn {
	float: left;
}
.group-btn {
	display: flex;
	flex-direction: row;
	height: 190upx;
	min-width: 100upx;
	align-items: center;

}
.group-btn .btn-div {
	height: 190upx;
	color: #fff;
	text-align: center;
	padding: 0 50upx;
	font-size: 34upx;
	line-height: 190upx;
}
.group-btn .top {
	background-color: #FF7159;
}
.group-btn .removeM {
	background-color: #999;
}
.icon-circle{
	width:100upx;
	height: 100upx;
	border-radius: 100upx;
	overflow: hidden;
}
.list-right{
	margin-left: 25upx;
	height: 80upx;
}
.list-right-1{
	float: right;
	color: #A9A9A9;
}
.list-title{
	line-height:1.5;
	overflow:hidden;
	color:#333;
	font-size: 26upx;
	min-height: 46upx;
}
.list-detail{
	font-size: 24upx;
	color: #a9a9a9;
	display:-webkit-box;
	-webkit-box-orient:vertical;
	-webkit-line-clamp:1;
	overflow:hidden;
	height: 50upx;
}

.team {
	margin-left: auto;
	color: #666;
	font-size: 26rpx;
	text {
		color: #45264D;
		font-size:32rpx;
		font-weight: bold;
	}
}
.profit {
	border-top:#E7E7E7 2rpx solid;
	width: 650rpx;
	margin: 0 auto;	
	justify-content: space-between;
	padding: 0 60rpx;
	.profit_item {
		display: flex;
		flex-direction: column;
		padding: 20rpx 0;	
		text-align: center;	
		.tit {
			color: #666;
			font-size: 26rpx;
			line-height: 60rpx;
		}		
		.num {
			color: $theme-color;
			font-size: 32rpx;
			font-weight: bold;
		}
	}
}
</style>
