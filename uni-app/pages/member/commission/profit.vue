<template>
	<view>
		<view class="head flex">
			<view class="bg"></view>
			<view class="mymoney flex">
				<view class="allmoney commoney flex">
					<view class="num">{{ mycendata[0] || 0 }}</view>
					<view class="txt">总收益</view>
				</view>
				<view class="currmoney commoney flex">
					<view class="num">{{ mycendata[2] || 0}}</view>
					<view class="txt">团队消费</view>
				</view>				
			</view>
		</view>
		<view class="choosedate flex">
			<view class="left flex">
				<view class="rili"><image src="/static/images/rili.png" mode=""></image></view>
				<view class="time">
					<uCalendar fields="month" :currentDate='dateValue'  @calendarChange="selectDateFun" @columChange='columChangeFun'></uCalendar>
				</view>
				<view class="arrow cuIcon-triangledownfill"></view>
			</view>
			<view class="right">月收益：&yen;{{monthcount || 0}}</view>
		</view>	
		<view class="syboxList" v-for="(item,index) in monthlist" :key="index" @click="opendetail(index,item.date)">
			<view class="lists flex">
				<view class="split"></view>
				<view class="time">{{item.date}}</view>
				<view class="title">我的收益</view>
				<view class="right flex">
					<view class="price">+{{item.money}}</view>
					<block v-if="item.show">
						<view class="arrow cuIcon-fold"></view>
					</block>
					<block v-else>
						<view class="arrow cuIcon-unfold"></view>
					</block>
				</view>
			</view>
			<view class="detail" v-if="item.show">
				<block v-for="(vo,index2) in item.daylist" :key="index2">
					<view class="datelist">
						<view class="uinfo flex type9">
							<view class="img"><image :src="vo.avatar"></image></view>
							<view class="centerInfo flex">
								<view class="name">{{vo.nickname}}</view>
								<view class="time">
									<!-- <block v-if="vo.expire==0">[待结算]</block> -->
									<text style="margin-left: 10rpx;">{{vo.ctime}}</text>
								</view>
							</view>
							<view class="profit">
								<view class="top">{{vo.type}}</view>
								<view class="btm">{{vo.money}}</view>
							</view>
						</view>											
					</view>	
				</block>			
			</view>			
		</view>
		<view class="empty" v-if="monthlist.length==0">
			<image src="/static/img/shop/empty.png" mode=""></image>
			暂无收益
		</view>
	</view>
</template>

<script>
	import uCalendar from '@/components/calendar-lch/calendar-lch.vue';
	export default {
		components: {
			uCalendar	
		},		
		data() {
			return {
				mycendata: [],
				dateValue: '',
				monthlist:[],
				monthcount:0,
				daylist:[],
				showdate:false,
				showindex:0,
				expire:'',
			}
		},
		onLoad(e) {
			this.getCurrentTime();
			this.GetMyCenData();
			this.GetMonthData(this.dateValue);
			if(e.type==0){
				this.expire = 0;
			} else if(e.type==1){
				this.expire = 1;
			} else {
				this.expire = '';
			}
		},
		methods: {
			getCurrentTime() {
				let nowDate = new Date();
				let date = {
					year: nowDate.getFullYear(),
					month: nowDate.getMonth() + 1,
				}
				this.dateValue = date.year+'-'+date.month;
			},
			opendetail(index,day){
				//this.showdate = !this.showdate;
				this.monthlist[index].show= !this.monthlist[index].show;
				if(this.monthlist[index].show && !this.monthlist[index].daylist){
					this.GetDayData(index,day);
				}				
			},
			// 日期选中
			selectDateFun(e) {
				this.dateValue = e;
				this.GetMonthData(e);
			},
			//列的值改变时触发 columnchange 事件
			columChangeFun(e){

			},			
			jumpurl(pageUrl){			
				this.$common.navigateTo(pageUrl);			
			},
			GetMyCenData() {
				//获取个人中心的一些数据
				this.$api.getmycendata({}, res => {
					if (res.status) {
						this.mycendata = res.data
					}
					
				})
			},
			GetMonthData(month){
				//获取月数据
				this.$api.getMonthEarnings({
					month:month
				},res=>{
					if (res.status && res.data.list) {
						let defaultDay = '';
						res.data.list.forEach(function(item,index){
							if(index ==0){
								defaultDay = item.date
							}
						})
						this.monthlist = res.data.list,
						this.monthcount = res.data.sum
						this.opendetail(0,defaultDay);
					} else {
						this.monthlist = [];
						this.monthcount = 0
					}
				})
			},
			GetDayData(index,day){
				var that = this;
				//获取天数据
				that.$api.getDayEarnings({
					date:day,
					expire:this.expire
				},res=>{
					if (res.status) {					
						//that.monthlist[index]['daylist'] = res.data.list;
						that.$set(that.monthlist[index],'daylist',res.data.list);
					}
				})				
			}
		}
	}
</script>

<style lang="scss">
	page {
		background-color: #F5F5F5;
	}
	image {
		width: 100%;
		height: 100%;
	}
	.head {
		position: relative;
		.bg {
			width: 750rpx;
			height: 130rpx;
			background-color: $themeTop-color;		
		}
		.bg:after {
			width: 100%;
			height: 20rpx;
			position: absolute;
			left: 0;
			bottom: -20rpx;
			content: '';
			border-radius: 0 0 50% 50%;
			background:$themeTop-color;
		}			
		.box {
			position: absolute;
			top: 40rpx;
			left: 50rpx;
			width: 450rpx;
			height: 110rpx;
			color: #FFFFFF;
			.img{
				width: 120rpx;
				height: 120rpx;
				border-radius: 50%;
				overflow: hidden;
			}
			.uinfo {
				margin-left: 20rpx;
				flex-direction: column;
				justify-content: center;
				margin-top: 10rpx;
				.uid {
					margin-top: 6rpx;
				}
			}
		}
		.mymoney {
			position: absolute;
			bottom: -60rpx;
			left:10%;
			width:627rpx;
			height:150rpx;
			background:rgba(255,255,255,1);
			box-shadow:0rpx 2rpx 5rpx 1rpx rgba(0, 0, 0, 0.1);
			border-radius:10rpx;			
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
					font-size: 38rpx;
				}	
				.txt {
					color:rgba(102,102,102,1);
					font-size: 25rpx;
					margin-top: 10rpx;
				}							
			}
		}
	}

.choosedate {
	margin:70rpx auto 0rpx;
	width: 680rpx;
	justify-content: space-between;
	line-height: 80rpx;
	.left{
		height: 60rpx;
		line-height: 80rpx;
		overflow: hidden;
		.rili {
			width: 30rpx;
			height: 30rpx;
			margin-top: 6rpx;
		}
		.time {
			width: 130rpx;
			margin-left: 10rpx;
			margin-top: 20rpx;
			color:rgba(51,51,51,1);
			font-size: 28rpx;
		}
		.arrow {
			font-size: 60rpx;
			width: 44rpx;
			height: 40rpx;
			line-height:88rpx;
			margin-left: -20rpx;
		}
	}
}

.syboxList {
	.lists {
		padding: 0 20rpx;
		height: 94rpx;
		line-height: 94rpx;
		border-bottom: rgba(230,230,230,1) 2rpx solid;
		overflow: hidden;
		align-items: center;
		background-color: #fff;
		.split {
			width:5rpx;
			height:31rpx;
			background:rgba(211,7,21,1);
			margin-right: 10rpx;
		}
		.title {
			margin-left: 10rpx;
		}
		.right {
			margin-left:auto;
			margin-top: 6rpx;
			.arrow {
				margin-top: 5rpx;
				margin-left: 10rpx;
			}
		}
	}

}

.detail {
	padding:0 20rpx 20rpx;
	.datelist {
		width: 100%;
		background:rgba(255,255,255,1);
		border-radius:10rpx;
		margin-top: 20rpx;
		padding:25rpx 0;
		.uinfo {
			padding: 0 20rpx;
			align-items: center;
			.img {
				width: 90rpx;
				height: 90rpx;
				border-radius: 50%;
				overflow: hidden;
			}
			.centerInfo {
				flex-direction: column;
				line-height: 46rpx;
				padding-left: 14rpx;
				.name {
					color:rgba(35,35,35,1);
					font-size: 28rpx;
				}
				.time {
					color:rgba(153,153,153,1);
					font-size: 24rpx;
				}				
			}
			.profit {
				margin-left: auto;				
				font-size: 24rpx;
				display: flex;
				flex-direction: column;
				text-align: center;
				align-items: center;
				.top {
					color: #666;
				}
				.btm {
					color: #F94048;
					margin-top: 20rpx;
				}
			}
		}
		.proinfo {
			padding: 20rpx;
			.img {
				width:127rpx;
				height:127rpx;
				// background:rgba(85,163,0,1);
				border-radius:10rpx;
				overflow: hidden;
			}
			.info {
				width: 350rpx;
				padding-left: 20rpx;
				flex-direction: column;
				justify-content: space-around;
				.title {
					height: 80rpx;
					line-height: 40rpx;
					font-size: 24rpx;
					overflow: hidden;
				}
				.price {
					font-size: 24rpx;
					font-weight:500;
					color:rgba(145,145,145,1);
				}
			}
			.profit-type {
				margin-left: auto;		
				align-items: center;
				justify-content:space-around;
				flex-direction: column;
				text-align: center;
				color:#d6271e;
				font-weight: bold;
				font-size: 24rpx;
				line-height: 46rpx;				
				.type {

				}
			}
		}
	}
}
.empty {
	width: 300rpx;
	height: 300rpx;
	margin: 50rpx auto 0; 
	text-align: center;
	line-height: 50rpx;
	color: #666;
}

</style>
