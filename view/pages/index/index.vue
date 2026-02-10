<template>
	<view class="content" style="padding-top: 0upx;">
		<view class="topbg"></view>
		<view class="main">			
			<view class="diybox">
				<view class="diy_title"></view>
				<view class="searchArea flex">
					<image class='icon search-icon' src='/static/image/zoom.png'></image>
					<view class="search_tips">请输入搜索关键词</view>
				</view>
				<view class="swiper">
					<swiper class="swiper-c" :indicator-dots="swiper.indicatorDots" :autoplay="swiper.autoplay"
					 :duration="swiper.duration" indicator-color="#ffffff" indicator-active-color="#f0ad4e">
						<swiper-item class="have-none" v-for="(item, index) in banner" :key="index">
							<image class='' :src="item.img" @click="showSliderInfo(item.type,item.val)" mode="aspectFill"></image>
						</swiper-item>
					</swiper>
				</view>				
				<!-- notice -->
				<view class="noticeArea flex">
					<view class="laba">
						<image src="/static/img/laba.png" mode=""></image>
					</view>
					<view class="notice">
						<view class="notice_item">{{notice.title}}</view>
					</view>
				</view>	
				
<!-- 				<view class="gridArea flex">
					<view class="left" @click="showSliderInfo(1,'/pages/classify/index')">
						<image src="/static/img/grid_01.png" mode="widthFix"></image>
					</view>
					<view class="right">
						<view class="right_top"  @click="showSliderInfo(1,'/pages/classify/index')">
							<image src="/static/img/grid_02.png" mode="widthFix"></image>
						</view>
						<view class="right-bottom"  @click="showSliderInfo(1,'/pages/classify/index')">
							<image src="/static/img/grid_03.png" mode="widthFix"></image>
						</view>
					</view>
				</view> -->
				<view class="hot_img">
					<image src="/static/img/taocan.png" mode=""></image>
				</view>
				<!--  -->
				<view class="proLst">
					<view class="items" v-for="(item,index) in hotLst" :key="index" @click="goodsDetail(item.id)">
						<view class="img">
							<image :src="item.image_url"></image>
						</view>
						<view class="title one-line">{{item.name}}</view>
						<view class="price">
							<view class="unit">&yen;</view>
							<view class="nums">{{item.price}}</view>
						</view>
					</view>
				</view>				
			</view>
			<view class="hot_img">
				<image src="/static/img/recom.png" mode=""></image>
			</view>
			<!-- 推荐商品 -->
			<view class="productArea">
<!-- 				<view class="header">
					推荐商品
				</view> -->
				<view class="box flex">
					<view class="item" v-for="(item,index) in reomlist" :key="index" @click="goodsDetail(item.id)">
						<view class="img">
							<image :src="item.image_url"></image>
						</view>
						<view class="info">
							<view class="title two-line">
								{{item.name}}
							</view>
							<!-- <view class="desc" v-if="item.brief">
								{{item.brief}}…
							</view> -->
<!-- 							<view class="price flex">
								<view class="unit">&yen;</view>
								<view class="nums">{{item.price}}</view>
							</view> -->
						</view>
					</view>															
				</view>
			</view>	
		</view>
		<view style="height: 40rpx;"></view>
	</view>
</template>
<script>
	import {
		goods
	} from '@/config/mixins.js';
	export default {
		mixins: [goods],
		data() {
			return {
				imageUrl: '/static/image/share_image.png', //店铺分享图片
				pageData: [],
				pageCode: 'mobile_home', //页面布局编码
				pintuan: [], //拼团列表,
				redBagShow: false, //红包
				config: '', //配置信息
				userInfo: {}, // 用户信息
				kefupara: '', //客服传递资料
				copy: false,
				suTipStatus: false,
				shareUrl: '/pages/share/jump',
				banner:[],
				swiper: {
					indicatorDots: true,
					autoplay: true,
					// interval: 2000,
					duration: 500,
				},
				hotLst:[],
				reomlist:[],
				notice:{},
			};
		},
		updated() {
			this.copy = true;
		},
		computed: {
			appTitle() {
				return this.$store.state.config.shop_name;
			},
			// 获取店铺联系人手机号
			shopMobile() {
				return this.$store.state.config.shop_mobile || 0;
			},
			suTip() {
				return this.suTipStatus;
			}
		},
		onLoad(e) {
			if (this.$store.state.config.shop_name) {
				uni.setNavigationBarTitle({
					title: this.$store.state.config.shop_name || ''
				});
			}
			// 分享朋友和朋友圈
			// #ifdef H5
			if (this.$common.isWeiXinBrowser()) {
				this.shareAll()
			}
			// #endif
			this.getBanner();
			this.mydata();
		},
		onShow() {
		},
		methods: {
			getBanner() {
				this.$api.advert(
					{
						codes: 'tpl1_slider'
					},
					res => {
						if(res.data.list){
							this.banner = res.data.list.tpl1_slider;
						}						
					}
				);
			},
			mydata(){
				this.$api.getmyindex({}, res => {
					if (res.status) {
						this.reomlist = res.data.reomlist;
						this.hotLst = res.data.hotlist
						if(res.data.notice){
							this.notice = res.data.notice;
						}
					}
				})
			},
			topage(url){
				this.$common.navigateTo(url)
			},
			destroyed() {
				window.removeEventListener('scroll', this.handleScroll);
			},
			goSearch() {
				uni.navigateTo({
					url: './search'
				});
			},
			// 广告点击查看详情
			showSliderInfo(type, val) {
				if (type == 1) {
					if (val.indexOf('http') != -1) {
						// #ifdef H5 
						window.location.href = val
						// #endif
					} else {
						// #ifdef H5 || APP-PLUS || APP-PLUS-NVUE || MP
						if (val == '/pages/index/index' || val == '/pages/classify/classify' || val == '/pages/cart/index/index' || val == '/pages/member/index/index') {
							uni.switchTab({
								url: val
							});
							return;
						} else if(val.indexOf('/pages/coupon/coupon')>-1){
							var id = val.replace('/pages/coupon/coupon?id=',"");
							this.receiveCoupon(id)
						} else {
							this.$common.navigateTo(val);
							return;
						}
						// #endif
					}
				} else if (type == 2) {
					// 商品详情
					this.goodsDetail(val);
				} else if (type == 3) {
					// 文章详情
					this.$common.navigateTo('/pages/article/index?id=' + val + '&id_type=1');
				} else if (type == 4) {
					// 文章列表
					this.$common.navigateTo('/pages/article/list?cid=' + val);
				}
			},
			goGoodsDetail(id) {
				this.goods.goodsDetail(id);
			},			
			// 首页初始化获取数据
			initData() {
				//获取首页配置
				this.getShareUrl();
			},
			//获取分享URL
			getShareUrl() {
				let data = {
					client: 2,
					url: "/pages/share/jump",
					type: 1,
					page: 1,
				};
				let userToken = this.$db.get('userToken');
				if (userToken && userToken != '') {
					data['token'] = userToken;
				}
				this.$api.share(data, res => {
					this.shareUrl = res.data
				});
			},
			// 分享到朋友或朋友圈
			shareAll() {
				// 微信浏览器里面
				// console.log(window.location.href);
				let data = {
					url: window.location.href
				}
				let _this = this;
				this.$api.getShareInfo(data, res => {
					if (res.status) {
						_this.$wx.config({
							debug: false, // 开启调试模式,调用的所有api的返回值会在客户端alert出来，若要查看传入的参数，可以在pc端打开，参数信息会通过log打出，仅在pc端时才会打印。  
							appId: res.data.appId, // 必填，公众号的唯一标识  
							timestamp: res.data.timestamp, // 必填，生成签名的时间戳  
							nonceStr: res.data.nonceStr, // 必填，生成签名的随机串  
							signature: res.data.signature, // 必填，签名，见附录1  
							jsApiList: ["updateAppMessageShareData", "updateTimelineShareData"]
						});
						_this.$wx.ready(function() {
							let shareInfo = {
								title: "首页",
								desc: "首页",
								imgUrl: _this.config.shop_default_image
							}
							// 分享朋友
							_this.$wx.updateAppMessageShareData(shareInfo);
							// 分享朋友圈
							_this.$wx.updateTimelineShareData(shareInfo);
						})
					}
				});

			}
		},
		//分享
		onShareAppMessage() {
			return {
				title: this.$store.state.config.share_title,
				// #ifdef MP-ALIPAY
				desc: this.$store.state.config.share_desc,
				// #endif
				imageUrl: this.$store.state.config.share_image,
				path: this.shareUrl
			};
		},
		// #ifdef MP-WEIXIN || APP-PLUS || APP-PLUS-NVUE
		onPageScroll() {
			var _this = this;
			const query = uni.createSelectorQuery();
			query
				.select('.content >>> .search')
				.boundingClientRect(function(res) {
					if (res) {
						if (res.top < 0) {
							_this.$store.commit('searchFixed', true);
						} else {
							_this.$store.commit('searchFixed', false);
						}
					}
				})
				.exec();
		}
		//#endif
	};
</script>

<style lang="scss" scoped>
	
	page,.content {
		// background:linear-gradient(180deg, #ffa958, rgba(254, 145, 42, .8), rgba(254, 145, 42, .4), rgba(254, 145, 42, .3));
		background: #ffa958;
	}
	
	image {
		width: 100%;
		height: 100%;
	}
	.content {
		position: relative;
		width:100%;
		height:100%;
	}
	.topbg {		
		width: 750rpx;
		height: 2000rpx;
		
	}
	
	.glh {
		width: 694rpx;
		height:162rpx;
		margin-top:20rpx;
	}
	.main {
		width:100%;
		// position:absolute;
		// top:0rpx;
		margin-top: -2000rpx;
	}
	.diybox {
		position: relative;
		width: 696rpx;
		margin:0 auto;
		z-index: 9;
		.diy_title {
			font-size:32rpx;
			font-weight: bold;
			color: #000;
			text-align: center;
			height: 60rpx;
			line-height: 60rpx;
		}
		.searchArea {
			width: 100%;
			height:60rpx;
			background-color: #fff;
			border-radius: 10rpx;
			overflow: hidden;
			justify-content: center;
			align-items: center;
			.search_tips {
				font-size:24rpx;
				color: #ccc;
				height: 100%;
				line-height: 250%;
			}
			.search-icon {
				width: 38rpx;
				height: 38rpx;
			}
		}
	
		.swiper {
			width: 100%;
			height: 320rpx;
			margin-top: 25rpx;
		}
		
		.gridArea {
			width: 100%;
			margin-top: 30rpx;
			justify-content: space-between;
			.left {
				width: 340rpx;
				height: 380rpx;
				overflow: hidden;
			}
			.right {
				image {
					width: 340rpx;
					height: 180rpx;
					overflow: hidden;
				}
				.right-bottom {
					margin-top: 5rpx;					
				}	
			}
		}		
	}
	
	.noticeArea {
		width: 696rpx;
		height: 66rpx;
		background: #fff;
		border-radius: 14rpx;
		overflow: hidden;
		padding:0 30rpx;
		margin: 20rpx auto 0;
		align-items: center;
		.laba {
			width: 50rpx;
			height: 50rpx;
		}
		.notice {
			margin-left: 20rpx;
			font-size: 24rpx;
			font-weight: 500;
			color: #999;
			width:500rpx;
			height: 66rpx;
			overflow: hidden;
			.notice_item {
				width:100%;				
				height: 66rpx;
				line-height: 66rpx;
			}
		}	
	}

	.productArea {
		width: 694rpx;
		margin:30rpx auto 0;
		font-size: 30rpx;
		font-family: PingFang SC;
		font-weight: 500;
		color: #000000;
		.header {
			font-size: 30rpx;
			font-weight: 500;
			color: #000000;
		}
		.box {
			flex-wrap: wrap;
			justify-content: space-between;
			.item {
				width: 332rpx;
				background-color: #fff;
				border-radius:20rpx;
				overflow: hidden;
				margin-top: 30rpx;
				.img {
					width: 332rpx;
					height: 332rpx;
				}
				.info {
					padding:25rpx;
					.title {
						font-size: 28rpx;
						color: #000000;
					}
					.desc {
						margin-top: 20rpx;
						font-size: 24rpx;
						font-family: PingFang SC;
						font-weight: 400;
						color:#666;
					}
					.price {
						margin-top: 20rpx;
						color: #ff1100;
						align-items: center;
						.unit {
							font-size: 24rpx;
							margin-top:5rpx;
						}
						.nums {
							font-size:32rpx;
							margin-left:5rpx;
						}
					}
				}
			}
		}
	}

	.proLst {
		width:690rpx;
		margin: 20rpx auto;
		overflow: hidden;	
		display: flex;
		flex-direction: column;
		.items {
			width:690rpx;	
			border-radius:10rpx;
			overflow: hidden;
			margin-top:20rpx;
			background-color: #fff;
			.img {
				width:100%;
				height:360rpx;
			}
			.title {
				height:60rpx;
				overflow: hidden;
				line-height: 38rpx;
				padding:10rpx 30rpx;
				font-size:28rpx;
				line-height:60rpx;
			}
			.price {
				padding:10rpx 30rpx;
				color:#ff1100;
				.unit {
					display: inline-block;
					font-size:22rpx;
				}
				.nums {
					display: inline-block;
					font-size:28rpx;
				}
			}			
		}
	}
	
	.hot_img {
		width:696rpx;
		height:80rpx;
		margin: 0 auto;
	}
</style>
