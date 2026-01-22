<template>
	<view>
		<view class="banner">
			<image src="/static/img/sign/sign_bg.png" mode="widthFix"></image>
			<view class="content">
				<view class="hd">签到领代金券</view>
				<view class="bd">我的总代金券：{{userInfo.point}}</view>
			</view>
		</view>
		<view class="signArea">
			<view class="header">
				<view class="left">签到越多奖励越多哦~</view>
			</view>
			<view class="box flex">
				<view class="items flex" :class="total>=vo.id?'sign':''" v-for="(vo,index) in sign" :key="index">
					<template v-if="total>=vo.id">
						<view class="txt">已签到</view>
						<view class="xing">
							<image src="/static/img/sign/sign_check.png" mode=""></image>
						</view>
						<view class="give">{{vo.point}}代金券</view>
					</template>
					<template v-else>
						<view class="txt">{{vo.name}}</view>
						<view class="xing">
							<image src="/static/img/sign/xing.png" mode=""></image>
						</view>
						<view class="give">{{vo.point}}代金券</view>
					</template>	
				</view>
				<view class="item_last flex">
					<view class="txt">第七天</view>
					<view class="item_last_box flex">
						<view class="left">
							<view class="xing">
								<image src="/static/img/sign/xing.png" mode=""></image>
							</view>
							<view class="give">7代金券</view>
						</view>
						<view class="right">
							<image src="/static/img/sign/point.png" mode=""></image>
						</view>
					</view>
				</view>
			</view>
			
			<view class="signBtn" @click="sub_sign()" v-if="!status">
				立即签到
			</view>
			<view class="signBtn" v-else>
				已签到
			</view>			
		</view>
	</view>
</template>

<script>
	export default {
		data() {
			return {
				total:0,
				markDays:[],
				today: '',
				nowToday: '',
				pageInfo: {},
				text: "",
				status:true,
				userInfo:{},
				sign:[
					{
						id:1,
						name:'第一天',
						point:1
					},
					{
						id:2,
						name:'第二天',
						point:2
					},
					{
						id:3,
						name:'第三天',
						point:3
					},
					{
						id:4,
						name:'第四天',
						point:4
					},
					{
						id:5,
						name:'第五天',
						point:5
					},
					{
						id:6,
						name:'第六天',
						point:6
					}		
				]
			};
		},
		onLoad() {
			this.init();
			this.getUserInfo();
			this.isSign()
		},
		methods:{
			init() {
				this.$api.getsigninfo({date: this.today}, res => {
					// this.pageInfo = res.data
					// this.markDays = res.data.signday || []
					this.total = res.data.total
				})
			},
			isSign(){
				this.$api.isSign({},res=>{
					if(!res.status){
						this.status = false
					} else {
						this.status = true
					}
				})
			},
			sub_sign(){
				this.$api.sign({},res=>{
					this.$common.errorToShow(res.msg);
					this.isSign();
				})
			},
			getUserInfo(){
				this.$api.userInfo({}, res => {
					if(res.status){
						this.userInfo = res.data;
					}
				})	
			}
		}
	}
</script>

<style lang="scss">

page {
	background: rgba(247, 247, 247, 1);
}

image {
	width: 100%;
	height: 100%;
}

.banner {
	width: 750rpx;
	position: relative;
	.content {
		position: absolute;
		left:60rpx;
		top:100rpx;
		color:#fff;
		.hd {
			font-size: 50rpx;
			font-weight: bold;
			letter-spacing: 10rpx;
			text-shadow: 2px 2px 4px rgba(255,255,255,0.85);
		}
		.bd {
			font-size: 32rpx;
			text-shadow: 2px 2px 4px rgba(255,255,255,0.5);
			margin-top: 20rpx;
		}
	}
}

.signArea {
	width: 644rpx;
	min-height: 600rpx;
	margin:-200rpx auto 0;
	background-color: #fff;
	position: relative;
	z-index: 9;
	border-radius: 20rpx;
	padding:20rpx 20rpx 60rpx 20rpx;
	overflow: hidden;
	.header {
		color:rgba(15, 25, 77, 0.5);
		font-size: 28rpx;
	}
	.box {
		margin-top: 10rpx;
		flex-wrap: wrap;
		justify-content: space-between;
		.items {
			width:140rpx;
			background: rgba(254, 249, 237, 1);
			border-radius: 10rpx;
			flex-direction:column;
			justify-content: center;
			align-items: center;
			margin-top: 20rpx;
			&.sign {
				background: rgba(224, 255, 248, 1);
				.txt {
					color:rgba(11, 194, 150, 1);
				}
				.give {
					color:rgba(11, 194, 150, 1);
				}
			}
			.txt {
				color:#FF8D1A;
				font-size:28rpx;
				line-height: 60rpx;
				overflow: hidden;
			}
			.xing {
				height: 72rpx;
				width: 72rpx;
			}
			.give {
				color:#FF8D1A;
				font-size:28rpx;
				line-height: 60rpx;
				overflow: hidden;
			}
		}
		.item_last {
			width:290rpx;
			background: rgba(254, 249, 237, 1);
			border-radius: 10rpx;
			flex-direction:column;
			justify-content: center;
			align-items: center;
			margin-top: 20rpx;
			.item_last_box {
				width: 230rpx;
				margin-left:60rpx;
				.right {
					width: 124rpx;
					height:124rpx;
					margin-left: auto;
				}
			}
			.txt {
				color:#FF8D1A;
				font-size:28rpx;
				line-height: 60rpx;
				overflow: hidden;
			}
			.xing {
				height: 72rpx;
				width: 72rpx;
			}
			.give {
				color:#FF8D1A;
				font-size:28rpx;
				line-height: 60rpx;
				overflow: hidden;
			}
		}
	}
}


.signBtn {
	width: 574rpx;
	height: 108rpx;
	background: linear-gradient(90deg, rgba(46, 180, 173, 1) 0%, rgba(104, 209, 198, 1) 99.93%);
	box-shadow: 0rpx 18rpx 18rpx  rgba(11, 193, 150, 0.25);
	display: flex;
	justify-content: center;
	align-items: center;
	padding: 34rpx 62rpx 34rpx 62rpx;
	border-radius: 108rpx;
	overflow: hidden;
	font-size: 36rpx;
	font-weight: 700;
	letter-spacing: 0px;
	line-height: 48rpx;
	color: rgba(255, 255, 255, 1);
	text-align: left;
	vertical-align: top;
	margin: 50rpx auto 0;
}
</style>