<template>
	<view class="content">
		<hx-navbar title="会员中心" :backgroundColor="bgColor" color="#fff"/>
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
				<template v-if="index==0">
					<view class="items vip"  v-for="(vo,idx) in vipqy" :key="idx">
						<image :src="vo.image" mode=""></image>
						<view class="info">
							<view class="name">{{vo.name}}</view>
							<view class="desc">{{vo.desc}}</view>
						</view>
					</view>
				</template>
				<template v-if="index==1">
					<view class="items hhy" v-for="(vo,idx2) in hhrqy" :key="idx2">
						<image :src="vo.image" mode=""></image>
						<view class="info">
							<view class="name">{{vo.name}}</view>
							<view class="desc">{{vo.desc}}</view>
						</view>
					</view>	
				</template>
			</view>
		</view>
		<view class="cardArea">
			<view class="header">
				<view class="tit">
					开通会员
				</view>
			</view>
			<view class="box">
				<view class="items" :class="index==0?'on':''" @click="tabClick(0)">
					<view class="name">VIP</view>
					<view class="price">
						<text class="yen">&yen;</text>
						<text class="nums">198</text>
					</view>
				</view>
				<view class="items" :class="index==1?'on':''" @click="tabClick(1)">
					<view class="name">合伙人</view>
					<view class="price">
						<text class="yen">&yen;</text>
						<text class="nums">1980</text>
					</view>
				</view>				
			</view>
			<view class="agree">
				购买即视为同意 <text class="mark">会员用户协议</text>
			</view>
			<view class="buyBtn" @click="goPay()">
				立即支付
			</view>
		</view>
		<view class="cardArea">
			<view class="header">
				<view class="tit">
					SVIP商品推荐
				</view>
			</view>
			<view class="productLst">
				<view class="items" v-for="(vo,index) in goodslist" :key="index" @click="goToDetails(vo.id)">
					<view class="img">
						<image :src="vo.image_url" mode=""></image>
					</view>
					<view class="info">
						<view class="name one-line">{{vo.name}}</view>
						<view class="svip-price">
							<text class="yen">&yen;</text>
							<text class="nums">{{vo.price}}</text>
						</view>
						<view class="shop-price">
							<text>商城价</text>
							<text class="yen">&yen;</text>
							<text class="nums">{{vo.mktprice}}</text>
						</view>	
					</view>				
				</view>
			</view>
		</view>	
	</view>
</template>

<script>
	
import hxNavbar from "@/components/hx-navbar/hx-navbar.vue"
import { mapGetters } from 'vuex';
import { goods } from '@/config/mixins.js';	
import { apiBaseUrl } from '@/config/config.js';	
export default {
	mixins: [goods],
	components: {hxNavbar},
	data() {
		return {
			bgColor:[53, 56, 65],
			userInfo:{},
			vipqy:[
				{
					image: apiBaseUrl+'app/images/qy_01.png',
					name: '会员优惠',
					desc: '每月获得优惠'
				},
				{
					image: apiBaseUrl+'app/images/qy_03.png',
					name: '签到返利',
					desc: '每日签到返积分'
				},
				{
					image: apiBaseUrl+'app/images/qy_05.png',
					name: '专属特价',
					desc: '商品享特价优惠'
				},
				{
					image: apiBaseUrl+'app/images/qy_06.png',
					name: '消费返利',
					desc: '消费返多倍红包'
				},				
				{
					image: apiBaseUrl+'app/images/qy_02.png',
					name: 'VIP权益',
					desc: '礼包1'
				},
				{
					image: apiBaseUrl+'app/images/qy_04.png',
					name: '推荐复购',
					desc: '8%收益'
				}				
			],
			hhrqy:[
				{
					image: apiBaseUrl+'app/images/qy_01.png',
					name: '会员优惠',
					desc: '每月获得优惠'
				},
				{
					image: apiBaseUrl+'app/images/qy_03.png',
					name: '签到返利',
					desc: '每日签到返积分'
				},
				{
					image: apiBaseUrl+'app/images/qy_05.png',
					name: '专属特价',
					desc: '商品享特价优惠'
				},
				{
					image: apiBaseUrl+'app/images/qy_06.png',
					name: '消费返利',
					desc: '消费返多倍红包'
				},				
				{
					image: apiBaseUrl+'app/images/qy_02.png',
					name: '合伙人权益',
					desc: '礼包2*4'
				},
				{
					image: apiBaseUrl+'app/images/qy_04.png',
					name: '推荐复购',
					desc: '12%收益'
				}				
			],						
			index:0,
			loadStatus: 'more',
			goodslist:[],
			where:{
				recommend:1
			}
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
		this.getCateData();
	},
	methods: {	
		goToDetails(id){
			this.goodsDetail(id);
		},
		goPay(){
			let goodsId = 0
			if(this.index==0){
				goodsId = 4
			}else if(this.index==1){
				goodsId = 5
			}
			this.$common.navigateTo("/pages/goods/index/index?id="+goodsId)
		},
		tabClick(index){
			if(this.index !=index){
				this.index = index
			}
		},
		getUserInfo(){
			var _this = this				
			_this.$api.userInfo({}, res => {
				_this.userInfo = res.data
			})
		},
		getCateData() {
			let map = {};
			let where = this.where;
			map.page = 1;
			map.limit = 30;

			map.where = JSON.stringify(where)
			map.token = this.$db.get("userToken");
			this.loadStatus = 'loading';
			this.$api.goodsList(map, res => {
				if (res.status) {
					const _list = res.data.list;
					this.goodslist = [...this.goodslist, ..._list];
					if (res.data.count > this.goodslist.length) {
						this.loadStatus = 'more';
						this.page++;
					} else {
						// 数据已加载完毕
						this.loadStatus = 'noMore';
					}
				}
			})
		},		
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
		padding-left:30rpx;
		image {
			width: 80rpx;
			height: 80rpx;
			margin-right: 20rpx;
		}
		.info {
			.name {
				margin-bottom: 2rpx;
				font-size: 26rpx;
				line-height: 36rpx;
				color: #333;
			}
			.desc {
				font-size: 24rpx;
				line-height: 32rpx;
				color: #9a856d;
			}
		}
	}		
}


.cardArea {
	background-color: #fff;
	margin-top: 20rpx;
	padding-bottom: 20rpx;
	overflow: hidden;
	.header {
		padding: 26rpx 30rpx 0;
		font-size:24rpx;
		color: #797979;
		.tit {
			display: inline-block;
			margin-right: 14rpx;
			font-weight: 700;
			font-size: 32rpx;
			line-height: 44rpx;
			color: #333;
		}
	}
	.box {
		display: flex;
		justify-content: space-between;
		padding:0 30rpx 0rpx 30rpx;
		.items {
			display: inline-flex;
			flex-direction: column;
			justify-content: center;
			align-items: center;
			width: 320rpx;
			height: 232rpx;
			border-radius: 12rpx;
			margin: 32rpx 30rpx 30rpx 0;
			box-shadow: 0 2rpx 30rpx rgba(0, 0, 0, .1);
			font-size: 30rpx;
			line-height: 42rpx;
			color: #754e19;
			.price {
				margin-top: 22rpx;
				font-weight: 600;
				font-size: 34rpx;
				.nums {
					font-size: 48rpx;
					line-height: 48rpx;
				}
			}
			&.on {
				border: 2rpx solid #fcc282;
				background-color: #fef7ec;
			}
		}
	}
}

.agree {
	font-size: 22rpx;
	text-align: center;
	color: #797979;	
	.mark {
		color: #ae5a2a;	
	}
}
.buyBtn {
	height: 80rpx;
	border-radius:12rpx;
	margin: 30rpx 30rpx 0;
	background: linear-gradient(90deg, #fee2b7, #fdc383);
	font-size: 30rpx;
	line-height: 80rpx;
	text-align: center;
	color: #5d3324;
	margin-bottom: 12rpx;
}

.productLst {
	padding:26rpx 10rpx;
	display: flex;
	flex-wrap: wrap;
	overflow: hidden;
	.items {
		width: 215rpx;
		margin-bottom: 20rpx;
		margin-left: 20rpx;
		.img {
			width: 215rpx;
			height: 215rpx;
			border-radius: 10rpx;
			overflow: hidden;
		}
		.info {
			font-size: 26rpx;
			padding-left:6rpx;
			.name {
				margin-top: 10rpx;
				line-height: 36rpx;
				color: #333;				
			}
			.svip-price {
				margin-top: 6rpx;
				font-size: 26rpx;
				color: #333;
			}
			.shop-price {
				margin-top: 4rpx;
				font-size: 20rpx;
				color: #999;
			}			
		}
	}
}
</style>