<template>
	<view class="classify">
		<!-- 搜索框 -->
		<view class="search-body" ref="searchBar">
			<view class="search" ref="searchBar" id="search">
				<view class='search-c' @click='goSearch()'>
					<image class='icon search-icon' src='/static/image/zoom.png'></image>
					<view class='search-input search-input-p'>
						<view class="search-input-p-c">
							请输入关键字
							<!-- {{jdata.params.keywords}} -->
						</view>
					</view>
				</view>
			</view>
		</view>
		<!-- 二级小图 -->
		<view class="goods-box" v-if="cate_style == 3">
			<view class="goods-list">
				<scroll-view scroll-y="true">
					<view class="goods-li" :class="{active:index==ins}" @click="active(index)" v-for="(tab,index) in beans" :key="index">
						<view class="shelectedZhu"></view>
						{{tab.name||''}}
					</view>
				</scroll-view>
			</view>
			<view class="goods-grid">
				<scroll-view class="goods-content" scroll-y="true" :scroll-into-view="toView" @scrolltolower="lower"
				 lower-threshold="500">
					<!-- <view class="goods-banner" v-if="advert.tpl1_class_banner1 && !goodsShow">
						<image mode="widthFix" v-for="item in advert.tpl1_class_banner1" :key="item.id" :src="item.img" @click="showSliderInfo(item.type, item.val)" />
					</view> -->
					<view class="goods-banner" v-if="goodsShow && cateImage">
						<image mode="widthFix" :src="cateImage" />
					</view>
					<view class="jshop-tabbar bottom-cell-group" v-if="childCat.length > 0">
						<scroll-view scroll-x='true' class="tabbar-list">
							<view class="tabbar-item" :class="{childActive:index==childIndex}" v-for="(item, index) in childCat" :key="index"
							 @click="clickChild(index,item.id)">
								{{item.name||''}}
								<view class="active-tabbar"></view>
							</view>
						</scroll-view>
					</view>
					<view class="goods-item">
						<view class="goods-item-box-name" v-if="beans[ins]">
							--- {{beans[ins].name || ''}} ---
						</view>
						<view class="goods-item-box" v-if="goodsListData.length>0">
							<view class="goods-items" v-for="(item, index) in goodsListData" :key="index">
								<view class="goods-item-img-c" @click="goClass(item.id)">
									<image class="goods-item-img" :src="item.image_url" alt="" mode="aspectFill" />
									<view>
										<view class="goods-img-none" v-if="item.product.stock<=0">
											已售罄
										</view>
									</view>
								</view>
								<view class="goods-item-right w65">
									<view class="goods-item-name one-line h-auto" @click="goClass(item.id)">
										{{item.name || ''}}
									</view>
									<view class="goods-item-brief two-line h-auto" @click="goClass(item.id)" >
									{{item.brief || ''}}
									</view>
									<view class="goods-item-spec" v-if="item.oms_spec" @click="goClass(item.id)">
										{{item.oms_spec}}
									</view>
									<view class="goods-item-price">
										<view class="red-price fsz28" @click="goClass(item.id)">
											￥{{item.product.price||'0.00'}}
										</view>
										<image class="icon" src="/static/image/add.png" mode="" v-if="item.product.stock>0 && goodsShow" @click="fastAdd(item)" :data-val="item.id"></image>  
									</view>
								</view>
							</view>
						</view>
						<view class="goods-item-box-name jzz">
							{{toViewText}}
						</view>
						<view v-if="goodsListData.length <= 0 && toViewText == ''">
							<view class="no-goods-tip">暂无在售商品</view>
							
						</view>
					</view>
				</scroll-view>
			</view>
		</view>

		<!-- 一级小图 -->
		<view class="goods-box level1-s" v-if="cate_style == 2">
			<view class="goods-grid">
				<scroll-view class="goods-content" scroll-y="true">
					<view class="goods-item">
						<view class="goods-item-box">
							<view class="goods-items" v-for="(item, index) in beans" :key="index" @click="goClass(item.id)">
								<image class="goods-item-img" :src="item.image_url" alt="" mode="aspectFill" />
								<view class="goods-item-name">{{item.name}}</view>
							</view>
						</view>
					</view>
				</scroll-view>
			</view>
		</view>

		<!-- 一级大图 -->
		<view class="goods-box level1-b" v-if="cate_style == 1">
			<view class="goods-grid">
				<scroll-view class="goods-content" scroll-y="true">
					<view class="goods-item">
						<view class="goods-item-box">
							<view class="goods-items" v-for="(item, index) in beans" :key="index" @click="goClass(item.id)">
								<image class="goods-item-img" :src="item.image_url" alt="" mode="aspectFill" />
								<view class="goods-item-name">{{item.name}}</view>
							</view>
						</view>
					</view>
				</scroll-view>
			</view>
		</view>
		
		<!-- 弹出层 -->
		<lvv-popup position="center" ref="spes" class="spes-content">
			<view class="content">
				<specs :spesData="defaultSpesDesc" :product="product" ref="spec" @changeSpes="changeSpes" @clickHandleFastAdd="clickHandleFastAdd" @toclose="toclose"></specs>
			</view>
		</lvv-popup>
		<!-- 弹出层end -->
	</view>
</template>
  
<script>
	var _this;
	import {
		mapGetters
	} from 'vuex'
	import {
		goods
	} from '@/config/mixins.js'
	import specs from '@/components/spec/specs.vue';
	import lvvPopup from '@/components/lvv-popup/lvv-popup.vue';
	export default {
		components: {
			specs,
			lvvPopup
		},
		mixins: [goods],
		data() {
			return {
				dataList: null,
				selectId: '', //默认、当前分类id
				ins: 0,
				beans: [],
				advert: {},
				isChild: false,
				goodsShow: this.$store.state.config.cate_goods_show || true, //是否显示商品，为true的时候显示商品
				cateImage: '',
				switchActive: true,
				switchStatus: this.$store.state.switchStatus || false, //自提外卖状态
				goodsListData: [],
				ajaxStatus: false,
				loading: true,
				loadingComplete: false,
				nodata: false,
				toView: '',
				toViewText: '加载中...',
				searchData: {
					where: {},
					limit: 10,
					page: 1,
					order: {
						key: 'sort',
						sort: 'asc'
					}
				},
				orderTip: false,
				product: {},
				childCat: [],
				childIndex: 0
			};
		},
		onShow() {
			//默认选中
			const app = getApp();
			var data = app.globalData.tabParams;
			if (data && data.hasOwnProperty('classify')) {
				this.selectId = data.classify.id;
				app.globalData.tabParams = {} //过来后就重置
				if (this.selectId && this.beans) {
					for (let k in this.beans) {
						if (this.selectId == this.beans[k].id) {
							this.active(k)
						}
					}
				}
			}
			

			this.getCartNum()

		},
		computed: {
			cate_style() {
				return this.$store.state.config.cate_style ? this.$store.state.config.cate_style : 3;
			},
			defaultSpesDesc() {
				return this.product.default_spes_desc;
			},
		},
		watch: {
			'$store.state.cartNum': function(newVal) {
				uni.setTabBarBadge({
					index: 3,
					text: newVal.toString()
				})
			},
		},
		methods: {
			//切换样式 请求分类数据
			active(index) {
				this.ins = index;
				this.isChild = this.beans[index].hasOwnProperty('child');
				// 获取二级分类
				this.getchildcat(this.beans[this.ins].id)
				this.childIndex = 0
				if (this.goodsShow) {
					this.cateImage = this.beans[index].image_url;
					let sFilter = this.searchData;
					sFilter.where.cat_id = this.beans[index].id;
					this.setSearchData(sFilter, true);
					this.getGoods();
				}

			},
			categories() {
				if (this.goodsShow) {
					this.$api.getTopCat({}, res => {
						if (res.status) {
							for (var i = 0; i < res.data.length; i++) {
								if (i == 0) {
									res.data[i].active = true;
									this.getchildcat(res.data[i].id)
								}
							}
							this.beans = res.data;
							this.cateImage = this.beans[0].image_url;
							if (this.selectId) {
								for (let k in this.beans) {
									if (this.selectId == this.beans[k].id) {
										this.ins = k
									}
								}
							} else {
								this.selectId = this.beans[0].id;
							}
							let sFilter = this.searchData;
							sFilter.where.cat_id = this.selectId;
							this.setSearchData(sFilter, true);
							this.getGoods();
						}
					});
				} else {
					this.$api.categories({
						'goods_show': this.goodsShow
					}, res => {
						if (res.status) {
							for (var i = 0; i < res.data.length; i++) {
								if (i == 0) {
									res.data[i].active = true;
									this.getchildcat(res.data[i].id)
								}
							}
							this.beans = res.data;

							this.isChild = this.beans[0].hasOwnProperty('child');
							if (this.goodsShow) {
								this.cateImage = this.beans[0].image_url;
							}
							if (this.selectId) {
								for (let k in this.beans) {
									if (this.selectId == this.beans[k].id) {
										this.ins = k
									}
								}
							} else {

							}
						}
					});
				}
			},
			clickChild(index, id) {
				this.childIndex = index;
				let sFilter = this.searchData;
				sFilter.where.cat_id = id;
				this.setSearchData(sFilter, true);
				this.getGoods();
			},
			// 获取二级分类
			getchildcat(id) {
				let data = {
					parent_id: id
				}
				this.$api.getchildcat(data, res => {
					if (res.status) {
						this.childCat = res.data;
						if(this.childCat.length!=0){
							let first = {
								name: "全部",
								id:id
							}
							this.childCat.unshift(first)
							console.log("childCat", this.childCat);
						}
					} else {
						this.$common.errorToShow(res.msg)
					}
				})
			},
			getGoods() {
				var _this = this;
				if (_this.ajaxStatus) {
					return false;
				}

				_this.ajaxStatus = true;
				_this.loading = true;
				_this.loadingComplete = false;
				_this.nodata = true;
				_this.toViewText = "加载中...";

				_this.$api.goodsList(_this.conditions(), function(res) {
					if (res.status) {
						//判是否没有数据了，只要返回的记录条数小于总记录条数，那就说明到底了，因为后面没有数据了
						var isEnd = false;
						if (res.data.list.length < _this.searchData.limit) {
							isEnd = true;
						}

						//判断是否为空
						var isEmpty = false;
						if (_this.searchData.page == 1 && res.data.list.length == 0) {
							isEmpty = true;
						}
						res.data.list.forEach(v => {
							v.weight = parseInt(v.weight);
						});

						_this.goodsListData = _this.goodsListData.concat(res.data.list);
						_this.ajaxStatus = false;
						_this.loading = !isEnd && !isEmpty;
						_this.toView = '';
						_this.loadingComplete = isEnd && !isEmpty;
						_this.toViewText = _this.loadingComplete ? '我们也是有底线的' : '加载中...';
						if (isEmpty) {
							_this.toViewText = '';
						}
						_this.nodata = isEmpty;
					}
				});
			},
			goClass(cat_id) {
				if (this.goodsShow) {
					let url = '/pages/goods/index/index?id=' + cat_id;
					this.$common.navigateTo(url);
				} else {
					uni.navigateTo({
						url: '/pages/classify/index?id=' + cat_id
					});
				}

			},
			// 关闭modal弹出框
			toclose() {
				this.$refs.spes.close();
			},
			// 切换商品规格
			changeSpes(obj) {
				let index = obj.v;
				let key = obj.k;
				let userToken = this.$db.get('userToken');
				// let tmp_default_spes_desc = JSON.parse(this.product.default_spes_desc);
				let tmp_default_spes_desc = this.product.default_spes_desc;
				if (tmp_default_spes_desc[index][key].hasOwnProperty('product_id') && tmp_default_spes_desc[index][key].product_id) {
					//this.$refs.spec.changeSpecData();
					this.$api.getProductInfo({
						id: tmp_default_spes_desc[index][key].product_id,
						token: userToken
					}, res => {
						if (res.status == true) {
							// 切换规格判断可购买数量
							this.product = this.spesClassHandle(res.data);
						}
					});
					uni.showLoading({
						title: '加载中'
					});
					setTimeout(function() {
						uni.hideLoading();
					}, 1000);
				}
			},
			// 多规格样式统一处理
			spesClassHandle(products) {
				// 判断是否是多规格 (是否有默认规格)
				if (products.hasOwnProperty('default_spes_desc')) {
					let spes = products.default_spes_desc;
					for (let key in spes) {
						for (let i in spes[key]) {
							if (spes[key][i].hasOwnProperty('is_default') && spes[key][i].is_default === true) {
								this.$set(spes[key][i], 'cla', 'item selected');
							} else if (spes[key][i].hasOwnProperty('product_id') && spes[key][i].product_id) {
								this.$set(spes[key][i], 'cla', 'item not-selected');
							} else {
								this.$set(spes[key][i], 'cla', 'item none');
							}
						}
					}
					// spes = JSON.stringify(spes)
					products.default_spes_desc = spes;
				}
				return products;
			},
			//快速加入购物车
			fastAdd(item) {
				if (item.spes_desc) {
					this.product = this.spesClassHandle(item.product);
					this.$refs.spes.show();
				} else {
					let data = {}
					let cart = [{
						'product_id': item.product.id,
						"nums": 1
					}];
					data["cart"] = cart;
					this.$api.fastadd(data, res => {
						if (res.status) {
							this.$common.successToShow(res.msg);
							this.getCartNum();//获取购物车数量
							this.toclose()
						} else {
							this.$common.errorToShow(res.msg);
						}
					});
				}
			},
			getBanner() {
				this.$api.advert({
					codes: 'tpl1_class_banner1'
				}, res => {
					this.advert = res.data.list;
					// console.log(this.advert);
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
						if (val == '/pages/index/index' || val == '/pages/classify/classify' || val == '/pages/cart/index/index' || val ==
							'/pages/member/index/index') {
							uni.switchTab({
								url: val
							});
							return;
						} else if (val.indexOf('/pages/coupon/coupon') > -1) {
							var id = val.replace('/pages/coupon/coupon?id=', "");
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
			// 用户领取优惠券
			receiveCoupon(couponId) {
				let data = {
					promotion_id: couponId
				}
				this.$api.getCoupon(data, res => {
					if (res.status) {
						this.$common.successToShow(res.msg)
					} else {
						this.$common.errorToShow(res.msg)
					}
				})
			},
			goChoiceArea() {
				uni.navigateTo({
					url: '/pages/index/choice_area'
				});
			},
			//去搜索
			goSearch() {
				this.$common.navigateTo('/pages/index/search');
			},
			// 切换开关
			switchChange() {
				this.switchActive = !this.switchActive;
				let tday = '';
				if (this.switchActive == false) {
					tday = 't0'
				} else {
					tday = 't2'
				}
				this.$store.commit('tDay', tday)
				uni.startPullDownRefresh(); //自动下拉
			},
			//设置查询条件
			setSearchData: function(searchData, clear = false) {
				var sd = this.searchData;
				this.searchData = this.$common.deepCopy(sd, searchData)
				if (clear) {
					this.goodsListData = [];
					this.searchData.page = 1;
				}
			},
			// 统一返回筛选条件 查询条件 分页
			conditions() {
				let data = this.searchData;
				let newData = {};
				newData = this.$common.deepCopy(newData, data);
				//把data里的where换成json
				if (data.where) {
					newData.where = JSON.stringify(data.where);
				}
				//把排序换成字符串
				if (data.order) {
					let sort = data.order.key + ' ' + data.order.sort;
					if (data.order.key != 'sort') {
						sort = sort + ',sort asc' //如果不是综合排序，增加上第二个排序优先级排序
					}
					newData.order = sort;
				} else {
					newData.order = 'sort asc';
				}
				return newData;
			},
			//上拉加载
			lower: function() {
				var _this = this;
				_this.toView = 'loading';
				if (!_this.loadingComplete && !_this.ajaxStatus) {
					_this.setSearchData({
						page: _this.searchData.page + 1
					});
					_this.getGoods();
				}
			},

			// 获取购物车数量
			getCartNum() {
				if (this.$db.get('userToken')) {
					this.$api.getCartNum({}, res => {
						if (res.status) {
							this.$store.commit('cartNum', res.data)

						}
					})
				}
			},
			//快速购买加入购物车
			clickHandleFastAdd(addCartData){
				let data = {}
				let cart = [{
					'product_id': addCartData.id,
					"nums": addCartData.nums
				}];
				data["cart"] = cart;
				this.$api.fastadd(data, res => {
					if (res.status) {
						this.$common.successToShow(res.msg);
						this.getCartNum();//获取购物车数量
						this.toclose()
					} else {
						this.$common.errorToShow(res.msg);
					}
				});
			},
		},
		onLoad(option) {
			this.categories();
			this.getBanner();
			if (option.id) {
				this.selectId = option.id
			}
			uni.startPullDownRefresh(); //自动下拉刷新库存
		},

		onPullDownRefresh() {
			this.categories()
			this.getCartNum()
			uni.stopPullDownRefresh()
		}
	};
</script>

<style>
	.search-body {
		/* position: fixed; */
		width: 100%;
		/*  #ifdef  H5  */
		/* top: 44px; */
		/*  #endif  */
		/*  #ifndef  H5  */
		/* top: 0; */
		/*  #endif  */
	}

	.search {
		/* position: fixed; */
		/*  #ifdef  H5  */
		/* top: 44px; */
		/*  #endif  */
		/*  #ifndef  H5  */
		/* top: 0; */
		/*  #endif  */
	}

	.classify {
		/*  #ifdef  H5  */
		height: calc(100vh - 94px);
		/* padding-top: 164rpx; */
		/*  #endif  */
		/*  #ifndef  H5  */
		height: 100vh;
		/* padding-top: 164rpx; */
		/*  #endif  */
	}

	.goods-box {
		height: 100%;
		overflow: hidden;
	}

	.goods-list {
		overflow: auto;
		height: 100%;
		width: 160rpx;
		float: left;
		display: inline-block;
		background-color: #f8f8f8;
	}

	.goods-li {
		font-size: 26rpx;
		color: #666;
		height: 100rpx;
		line-height: 100rpx;
		text-align: center;
		position: relative;
		overflow: hidden;
		text-overflow: ellipsis;
		white-space: nowrap;
	}

	.goods-li.active {
		background-color: #fff;
		color: #6CBB59;
		font-size: 30rpx;
	}

	.shelectedZhu {
		height: 56rpx;
		width: 8rpx;
		position: absolute;
		top: 50%;
		transform: translateY(-50%);
	}

	.goods-li.active .shelectedZhu {
		background-color: #6CBB59;
	}

	.goods-content {
		width: 590rpx;
		height: 100%;
		display: inline-block;
		float: left;
		padding: 20rpx;
		box-sizing: border-box;
	}

	.goods-grid {
		height: 100%;
		overflow: auto;
		background-color: #fff;
	}

	.goods-banner {
		width: 100%;
		/* height: 200rpx; */
		margin-bottom: 20rpx;
	}

	.goods-banner image {
		width: 100%;
		height: 100%;
	}

	.goods-item {}

	.goods-item-box {
		overflow: hidden;
	}

	.goods-items {
		width: 100%;
		/* margin-right: 20rpx; */
		/* margin-bottom: 20rpx; */
		display: flex;
		border-bottom: 2rpx solid #eee;
		padding: 20rpx 0;
	}


	/* .goods-items:nth-child(3n) {
		margin-right: 0;
	} */

	.goods-item-img {
		width: 100%;
		height: 100%;
		border-radius: 20rpx;
	}

	.goods-item-name {
		/* text-align: center; */
		color: #666;
		font-size: 28rpx;
		height: 1.2rem;
		overflow: hidden;
	}

	/*简介*/
	.goods-item-brief {
		/* text-align: center; */
		color: #999;
		font-size: 24rpx;
		height: 1.2rem;
		overflow: hidden;
	}

	/*规格*/
	.goods-item-spec {
		/* text-align: center; */
		color: #999;
		font-size: 24rpx;
		/* height: 0.8rem; */
		overflow: hidden;
	}

	.level1-s .goods-content,
	.level1-b .goods-content {
		width: 100%;
	}

	.level1-s .goods-items {
		width: 222rpx;
	}

	.level1-s .goods-item-img {
		width: 222rpx;
		height: 222rpx;
	}

	.level1-b .goods-items {
		width: 100%;
	}

	.level1-b .goods-item-img {
		width: 100%;
		height: 222rpx;
	}

	.area-select {
		display: flex;
		align-items: center;
		margin-right: 30rpx;
	}

	.area-select .icon-area {
		width: 40rpx;
		height: 36rpx;
	}

	.area-select .icon-bottom {
		width: 40rpx;
		height: 40rpx;
		position: relative;
		/* top: 4rpx; */
	}

	.area-select text {
		margin: 0 10rpx;
	}

	.goods-item-box-name {
		margin-bottom: 20rpx;
		width: 100%;
		display: flex;
		justify-content: center;
	}

	.area-select {
		background-color: #fff;
		padding: 10rpx 26rpx;
		display: flex;
		/* position: fixed; */
		/* top: calc(44px + 104rpx); */
		width: 100%;
		z-index: 666;
		justify-content: space-between;
	}

	.area-select-left {
		display: flex;
		align-items: center;
	}

	.area-select-right {
		display: flex;
		border: 2rpx solid rgb(108, 187, 89);
		border-radius: 50rpx;
		padding: 4rpx;
	}

	.asr-switch-item {
		padding: 0 10rpx;
		color: rgb(108, 187, 89);
	}

	.ars-switch-active {
		background-color: rgb(108, 187, 89);
		color: #fff;
		border-radius: 50rpx;
	}

	.goods-item-img-c {
		position: relative;
		width: 170rpx;
		height: 170rpx;
		margin-right: 20rpx;
	}

	.goods-item-img-c .icon {
		position: absolute;
		bottom: 0;
		right: 0;
	}


	.goods-img-none {
		position: absolute;
		z-index: 66;
		background-color: rgba(0, 0, 0, .4);
		color: #fff;
		width: 100%;
		height: 100%;
		display: flex;
		align-items: center;
		justify-content: center;
		top: 0;
		border-radius: 20rpx;
	}

	.goods-item-right {
		position: relative;
		flex: 1;
	}

	.goods-item-price {
		position: absolute;
		bottom: 10rpx;
		display: flex;
		width: 100%;
		justify-content: space-between;
	}

	.jzz {
		color: #999999;
		font-size: 13px;
		margin-top: 10px;
	}

	.no-goods-tip {
		text-align: center;
		font-size: 13px;
		color: #aaa;
		margin-top: 160rpx;
	}

	.order-tip {
		text-align: center;
		font-size: 26rpx;
		background-color: #ffffff;
		color: #aaa;
		padding: 16rpx 0;
	}

	.spes-content {
		z-index: 1000;

	}

	.content {
		width: 80%;
		max-height: 402px;
		position: absolute;
		top: 50%;
		left: 50%;
		transform: translate(-50%, -50%);
	}

	.tabbar-list {
		padding: 10rpx 0;
		background-color: #fff;
		white-space: nowrap;
		width: 100%;
	}

	.tabbar-item {
		display: inline-block;
		padding: 10rpx 20rpx;
	}

	.active-tabbar {
		display: none;
	}

	.childActive {
		color: #FF7159;
	}

	.childActive .active-tabbar {
		display: block;
		width: 100%;
		height: 4rpx;
		margin: 10rpx auto 0;
		background-color: #FF7159;
	}
	.h-auto{
		height: auto;
	}
	.w65{
		width: 65%;
	}
</style>
