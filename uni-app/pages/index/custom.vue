<template>
	<view class="content" style="padding-top: 0rpx" id="jshop-content">
		<jshop :jdata="pageData"></jshop>
	</view>
</template>
<script>
import jshop from '@/components/jshop/jshop.vue';
import { goods } from '@/config/mixins.js';
export default {
	mixins: [goods],
	components: {
		jshop
	},
	data() {
		return {
			imageUrl: '/static/image/share_image.png', //店铺分享图片
			pageData: [],
			pageCode: 'mobile_home', //页面布局编码
			statusBarHeight: '0',
			customBarOpacity: false,
			scrollTop: 0,
			showLoad: false, //是否显示loading
			share_name: '',
			shareUrl: '/pages/share/jump',
			page: 1,
			limit: 5,
			viewHeight: 0, //页面加载组件高度
			windowHeight: 0, //页面窗口高度
			loadStatus: 'more',
			loadText: {
				contentdown: '上拉显示更多',
				contentrefresh: '正在加载...',
				contentnomore: ''
			}
		};
	},
	computed: {
		appTitle() {
			return this.$store.state.config.shop_name;
		}
	},
	onReachBottom() {
		if (this.loadStatus === 'more') {
			this.getPageContent();
		}
	},
	created() {
		//获取窗口高度信息
		let _this = this;
		uni.getSystemInfo({
			success: function (data) {
				_this.windowHeight = data.windowHeight;
			}
		});
	},
	onLoad(e) {
		//增加页面编码，可自定义编码
		if (e.page_code) {
			this.pageCode = e.page_code;
		}
		this.initData();
	},
	// 小程序沉浸式状态栏变色
	onPageScroll(e) {
		// console.log(e);
		e.scrollTop > 50 ? (this.customBarOpacity = true) : (this.customBarOpacity = false);
	},
	mounted() {
		// #ifdef H5
		window.addEventListener('scroll', this.handleScroll);
		// #endif
	},
	methods: {
		//获取布局区域高度
		getViewHeight(callback) {
			var _this = this;
			const query = uni.createSelectorQuery().in(this);
			query
				.select('#jshop-content')
				.boundingClientRect((data) => {
					_this.viewHeight = data.height;
					callback();
				})
				.exec();
		},
		getPageContent() {
			let _this = this;
			//获取首页配置
			let data = {
				code: this.pageCode,
				page: this.page,
				limit: this.limit
			};
			if (this.$db.get('userToken')) {
				data.token = this.$db.get('userToken');
			}
			this.loadStatus = 'loading';
			this.$api.getPageConfig(data, (res) => {
				if (res.status == true) {
					if (this.limit > res.data.items.length) {
						// 没有数据了
						this.loadStatus = 'noMore';
					} else {
						// 未加载完毕
						this.loadStatus = 'more';
						this.page++;
					}
					this.pageData = [...this.pageData, ...res.data.items];
					this.share_name = res.data.name;
					uni.setNavigationBarTitle({
						title: res.data.name
					});
					//判断页面高度是否能自动加载下一页
					if (this.loadStatus == 'more') {
						this.getViewHeight(function () {
							if (_this.windowHeight - _this.viewHeight > _this.viewHeight) {
								setTimeout(() => {
									_this.getPageContent();
								}, 500);
							}
						});
					}
				}
			});
		},
		// 搜索框滑动变色
		handleScroll() {
			var scrollTop = window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop;
			scrollTop > 50 ? (this.searchBarOpacity = true) : (this.searchBarOpacity = false);
		},
		// 首页初始化获取数据
		initData() {
			this.getPageContent();
		},
		//获取分享URL
		getShareUrl() {
			let data = {
				client: 2,
				url: '/pages/share/jump',
				type: 1,
				page: 7,
				params: {
					page_code: this.pageCode
				}
			};
			let userToken = this.$db.get('userToken');
			if (userToken && userToken != '') {
				data['token'] = userToken;
			}
			this.$api.share(data, (res) => {
				this.shareUrl = res.data;
			});
		}
	},
	watch: {
		pageCode: {
			handler() {
				this.getShareUrl();
			},
			deep: true
		}
	},
	onPullDownRefresh() {
		this.page = 1;
		this.pageData = [];
		this.getPageContent();
		uni.stopPullDownRefresh();
	},
	//分享
	onShareAppMessage() {
		return {
			title: this.share_name,
			// #ifdef MP-ALIPAY
			//desc: this.$store.state.config.share_desc,
			// #endif
			//imageUrl: this.$store.state.config.share_image,
			path: this.shareUrl
		};
	},
	// #ifdef MP-WEIXIN || APP-PLUS || APP-PLUS-NVUE
	onPageScroll() {
		var _this = this;
		const query = uni.createSelectorQuery();
		query
			.select('.content >>> .search')
			.boundingClientRect(function (res) {
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

<style>
.cell-item {
	border: none;
}

.cell-ft-text {
	font-size: 22rpx;
	color: #999;
}

.status_bar {
	height: var(--status-bar-height);
	width: 100%;
	position: fixed;
	top: 0;
	z-index: 999;
	background: rgba(0, 0, 0, 0);
	transition: all 0.5s;
}

.custom-navbar {
	height: 40px;
	line-height: 34px;
	position: fixed;
	width: 100%;
	padding-left: 26rpx;
	top: var(--status-bar-height);
	z-index: 999;
	background: rgba(0, 0, 0, 0);
	transition: all 0.5s;
}

.index-logo {
	width: 140rpx;
	height: 70rpx;
}

.index-logo-img {
	width: 100%;
	height: 100%;
}

.isOpacity {
	background: rgba(255, 255, 255, 1);
	transition: all 0.5s;
}

/* iPhone X in portrait & landscape */
@media only screen and (min-device-width: 375px) and (max-device-width: 812px) and (-webkit-device-pixel-ratio: 3) {
	.status_bar {
		height: 50px;
	}

	.custom-navbar {
		top: 50px;
	}
}

/* iPhone X in landscape */
@media only screen and (min-device-width: 375px) and (max-device-width: 812px) and (-webkit-device-pixel-ratio: 3) and (orientation: landscape) {
	.status_bar {
		height: 50px;
	}

	.custom-navbar {
		top: 50px;
	}
}

/* iPhone X in portrait */
@media only screen and (min-device-width: 375px) and (max-device-width: 812px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait) {
	.status_bar {
		height: 50px;
	}

	.custom-navbar {
		top: 50px;
	}
}
</style>
