<template>
	<view class="content">

		<view class="nav-back">
			<view class="back-btn" @click="backBtn()">
				<image class="icon" src="/static/image/back-black.png" mode=""></image>
			</view>
		</view>

<!-- 		<view class="nav-right">
			<view class="back-btn">
				<image class='cell-ft-next icon' @click="changeAuth()" src='/static/image/share.png'></image>
			</view>
		</view> -->

		<view class="content-top">

			<!-- 轮播图 -->
			<view class='swiper'>
				<swiper class="swiper-c" :indicator-dots="swiper.indicatorDots" indicator-color='#fff' indicator-active-color='#e02e24' :autoplay="swiper.autoplay" :interval="swiper.interval"
				 :duration="swiper.duration">
					<swiper-item class="have-none" v-for="(item, index) in goodsInfo.album" :key="index" @click="clickImg(item)">
						<image class='' :src='item' mode="aspectFill"></image>
					</swiper-item>
				</swiper>
			</view>
			<!-- 轮播图end -->
			<view class='cell-group cell-groupArea'>
				<view class='cell-item goods-top' style="padding-bottom: 0;">
					<view class='cell-item-hd'>
						<view class='cell-hd-title goods-price red-price font-goods-price flex'>
							<em class='em'>￥</em>{{ goodsInfo.price || '0.00' }}</view>
						<view class='cell-hd-title goods-price cost-price' v-if="parseFloat(product.mktprice)>0 && parseFloat(product.mktprice)>parseFloat(product.price)">￥{{ product.mktprice  || '0.00'}}</view>
					</view>
					<view class='cell-item-ft'>
						<text>{{ goodsInfo.buy_count || '0' }} 人已购买</text>
					</view>
				</view>

				<view class='cell-item goods-details'>
					<view class='cell-item-hd' style="width: 750rpx;">
						<view class='cell-hd-title' style="width: 702rpx;margin: 0 auto;font-family: 'font';">
							<text v-if="goodsInfo.share>0 && userInfo && userInfo.grade>=2" class="bg_exp">
								分享赚:{{ goodsInfo.share}}
							</text>
<!-- 							<view v-if="userInfo && userInfo.grade==1 " class="openvip flex" @click="topageurl('/pages/member_vip/index')">
								<view class="vip1">vip365</view>
								<view class="desc" v-if="province">购买此商品立省{{province}}</view>
								<view class="open">
									<text>立即开通</text>
									<text class="cuIcon-right"></text>
								</view>
							</view> -->
<!-- 							<text v-if="goodsInfo.exp_offset>0" class="bg_exp">
								抵扣{{ goodsInfo.exp_offset}}消费券
							</text>	 -->						
							<view class="color-3 fsz32 productName cell-hd-title-view" style="color: #051B28 !important;">
								{{ product.name || '' }}
							</view>
						</view>
					</view>
				</view>
			</view>
			
			<view class="cell-group" style="margin-top: 20rpx;">
				<view class='cell-item goods-title-item cell-item-mid'>
					<view class='cell-item-hd'>
						<view class='cell-hd-title'>保障</view>
					</view>
					<view class='cell-item-bd'>
						<text class='cell-bd-text color-6 fsz24' v-if="goodsInfo.supply_ident =='zjrb'">官方补贴</text>
						<text class='cell-bd-text color-6 fsz24' v-else>正品保障 · 官方补贴</text>
					</view>
				</view>
				<view class='cell-item goods-title-item cell-item-mid' v-if="promotion.length">
					<view class='cell-item-hd'>
						<view class='cell-hd-title'>优惠</view>
					</view>
					<view class='cell-item-bd'>
						<view class="romotion-tip">
							<view class="romotion-tip-item" v-for="(item, index) in promotion" :key="index">
								{{ item || ''}}
							</view>
						</view>
					</view>
				</view>
				<view class='cell-item goods-title-item cell-item-mid' v-if="isSpes">
					<view class='cell-item-hd'>
						<view class='cell-hd-title'>规格</view>
					</view>
					<view class='cell-item-bd'>
						<text class='cell-bd-text color-6 fsz24'>{{ product.spes_desc || ''}}</text>
					</view>
				</view>
			</view>			
			
			<view class="cell-group" style="margin-top: 20rpx;">
				<view class='cell-item goods-title-item cell-item-mid'  v-if="goodsInfo.brief">
					<view class='cell-item-hd'>
						<view class='cell-hd-title'>备注</view>
					</view>
					<view class='cell-item-bd'>
						<text class='cell-bd-text color-6 fsz24'>{{ goodsInfo.brief || '' }}</text>
					</view>
				</view>
				<view class='cell-item goods-title-item cell-item-mid' v-if="goodsInfo.goods_cat_id !=4">
					<view class='cell-item-hd'>
						<view class='cell-hd-title'>配送</view>
					</view>
					<view class='cell-item-bd'>
						<text class='cell-bd-text color-6 fsz24'>
							快递(48小时内发货)
						</text>
					</view>
				</view>
			</view>
			<!-- 促销 -->
<!-- 			<view class='goods-brief flex' v-if="goodsInfo.brief" >
				<view class='cell-item-hd'>
					<view class='cell-hd-title fsz24 color-9'>备注</view>
				</view>
				<view class='cell-item-bd'>
					<text class='cell-bd-text color-6 fsz24'>{{ goodsInfo.brief || '' }}</text>
				</view>
			</view> -->
			<!-- 促销end -->
					
			<view class="goods-content">				
				<view class="goods_shen">
					<image src="/static/img/shop/shopTitle.jpg"></image>
				</view>
				<!-- <uni-segmented-control :current="current" :values="items" @clickItem="onClickItem" style-type="text" active-color="#333"></uni-segmented-control> -->
				<view class="goods-content-c">
					<view class="goods-detail" v-show="current === 0">
						<jshopContent :content="goodsInfo.intro" v-if="goodsInfo.intro"></jshopContent>
						<view class="comment-none" v-else>
							<image class="comment-none-img" src="/static/img/shop/scence2-3.png" mode=""></image>
						</view>
					</view>
					<view class="goods-parameter" v-show="current === 1">
						<view class='cell-group' v-if="goodsParams.length">
							<view class='cell-item' v-for="(item, index) in goodsParams" :key="index">
								<view class='cell-item-hd'>
									<view class='cell-hd-title'>{{ item.name || ''}}</view>
								</view>
								<view class='cell-item-bd'>
									<text class='cell-bd-text'>{{ item.value || ''}}</text>
								</view>
							</view>
						</view>
						<view class="comment-none" v-else>
							<image class="comment-none-img" src="/static/img/shop/scence2-3.png" mode=""></image>
						</view>
					</view>
					<view class="goods-assess" v-show="current === 2">
						<view v-if="goodsComments.list.length">
							<view class="goods-assess-item" v-for="(item, index) in goodsComments.list" :key="index">
								<view class='cell-group'>
									<view class='cell-item goods-title-item cell-item-mid'>
										<view class='cell-item-hd'>
											<image class='user-head-img' :src='item.user.avatar' mode="aspectFill"></image>
										</view>
										<view class='cell-item-bd'>
											<view class="cell-bd-view">
												<text class="cell-bd-text">{{ (item.user.nickname && item.user.nickname != '')?item.user.nickname:item.user.mobile }}</text>
												<view class="cell-bd-text-right">
													<uni-rate size="16" disabled="true" :value="item.score"></uni-rate>
												</view>
											</view>
											<view class="cell-bd-view">
												<text class="cell-bd-text color-9" style="margin-right: 16rpx;">{{ item.ctime || ''}}</text>
												<text class="cell-bd-text color-9">{{ item.addon || ''}}</text>
											</view>
										</view>
									</view>
								</view>
								<view class="gai-body">
									<view class="gai-body-text">
										{{ item.content || ''}}
									</view>
									<view class="gai-body-img" v-if="item.images_url.length">
										<image :src="img" mode="aspectFill" v-for="(img, key) in item.images_url" :key="key" @click="clickImg(img)"></image>
									</view>
									<view class="seller-content" v-if="item.seller_content">
										<view class="seller-content-top">
											<image class="seller-content-img" src="/static/image/seller-content.png"></image>掌柜回复
										</view>
										{{item.seller_content || ''}}
									</view>
								</view>
							</view>
							<uni-load-more :status="goodsComments.loadStatus"></uni-load-more>
						</view>
						<view class="comment-none" v-else>
							<image class="comment-none-img" src="/static/aixi/scence2-3.png" mode=""></image>
						</view>
					</view>
				</view>
			</view>
		</view>


		<!-- 弹出层 -->
		<lvv-popup position="bottom" ref="lvvpopref">
			<view style="width: 100%;max-height: 804rpx;background: #FFFFFF;position: absolute;left:0;bottom: 0;padding-bottom:constant(safe-area-inset-bottom); padding-bottom: env(safe-area-inset-bottom);">
				<view class="pop-c">
					<view class="pop-t" style="padding: 26rpx;">
						<view class='goods-img'>
							<image :src='product.image_path' mode='aspectFill'></image>
						</view>
						<view class='goods-information'>
							<view class='pop-goods-name' style="margin-bottom: 6rpx;">{{ product.name || ''}}</view>
							<view class='pop-goods-price red-price' style="margin-bottom: 6rpx;">￥ {{ product.price || ''}}</view>
							<view class="fsz24 color-9" v-if="product.stock>20">
								库存{{ product.stock || ''}}{{goodsInfo.unit || ''}}
							</view>
						</view>
						<view class='close-btn' @click="toclose()">
							<image src='/static/image/close.png'></image>
						</view>
					</view>
					<scroll-view class="pop-m" scroll-y="true" style="max-height: 560rpx;">
						<spec :spesData="defaultSpesDesc" ref="spec" @changeSpes="changeSpes"></spec>
						<view class="goods-number">
							<text class="pop-m-title">数量</text>
							<view class="pop-m-bd-in">
								<uni-number-box :min="minNums" :max="maxBuyNum" 
								:value="buyNum" @change="bindChange"></uni-number-box>
							</view>
						</view>
					</scroll-view>
					<view class="pop-b">
						<button class='btn btn-square btn-b btn-all' hover-class="btn-hover2" @click="clickHandle()" :disabled='submitStatus'
						 :loading='submitStatus' v-if="product.stock">确定</button>
						<button class='btn btn-square btn-g btn-all' v-else>已售罄</button>
					</view>
				</view>
			</view>
		</lvv-popup>
		<!-- 弹出层end -->

		<div id="qrCode" ref="qrCodeDiv"></div>
		<!-- 底部按钮 -->
		<view class="goods-bottom">	
			<block v-if="goodsInfo.goods_cat_id !=4">
				<!-- 客服按钮 -->
				<!-- #ifdef H5 || APP-PLUS-NVUE || APP-PLUS -->
				<view class="goods-bottom-ic" @click="showChat()">
					<image class="icon" src="/static/image/customerservice.png" mode=""></image>
					<view>客服</view>
				</view>
				<!-- #endif -->
				<!-- #ifdef MP-WEIXIN -->
				<button class="goods-bottom-ic weiContact" hover-class="none" open-type="contact" bindcontact="showChat"
				 :session-from="kefupara">
					<image class="icon" src="/static/image/customerservice.png" mode=""></image>
					<view>客服</view>
				</button>
				<!-- #endif -->
				<!-- #ifdef MP-ALIPAY -->
				<contact-button class="goods-bottom-ic icon" icon="/static/image/customerservice.png" size="80rpx*80rpx" tnt-inst-id="WKPKUZXG"
				 scene="SCE00040186" hover-class="none" />
				<!-- #endif -->
				<!-- #ifdef MP-TOUTIAO -->
				<view class="goods-bottom-ic" @click="showChat()">
					<image class="icon" src="/static/image/customerservice.png" mode=""></image>
					<view>客服</view>
				</view>
				<!-- #endif -->

				<view class="goods-bottom-ic" @click="redirectCart">
					<view class="badge color-f" v-if="cartNums">{{ cartNums || ''}}</view>
					<image class="icon" src="/static/image/ic-me-car.png" mode=""></image>
					<view>购物车</view>
				</view>

				<view class="goods-bottom-ic" @click="collection">
					<image class="icon" :src="isfav ? favLogo[1] : favLogo[0]" mode=""></image>
					<view v-if="!isfav">收藏</view>
					<view v-if="isfav">已收藏</view>
				</view>
				
				<block v-if="goodsInfo.goods_cat_id==1">
					<button class='btn btn-square btn-b' @click="toshow(3)" hover-class="btn-hover2" style="width: 58%;">立即购买</button>
				</block>
				<block v-else>
					<button class='btn btn-square btn-b' @click="toshow(3)" hover-class="btn-hover2" style="width: 58%;">暂无权限</button>
					<!-- <button class='btn btn-square btn-g' @click="toshow(1)" hover-class="btn-hover2" style="width: 30%;">加入购物车</button>
					<button class='btn btn-square btn-b' @click="toshow(2)" hover-class="btn-hover2" style="width: 28%;">立即购买</button> -->
				</block>
			</block>
			<block v-else>
				<button class='btn btn-square btn-b' @click="toshow(2)" hover-class="btn-hover2" style="width:100%;">立即购买</button>
			</block>			
		</view>

		<!-- 底部按钮end -->


		 
	</view>
</template>

<script>
	import uniSegmentedControl from "@/components/uni-segmented-control/uni-segmented-control.vue";
	import lvvPopup from '@/components/lvv-popup/lvv-popup.vue';
	import uniNumberBox from "@/components/uni-number-box/uni-number-box.vue";
	import uniRate from "@/components/uni-rate/uni-rate.vue";
	import uniLoadMore from '@/components/uni-load-more/uni-load-more.vue';
	import uniFab from '@/components/uni-fab/uni-fab.vue';
	import {
		get
	} from '@/config/db.js';
	import {
		apiBaseUrl
	} from '@/config/config.js'
	import spec from '@/components/spec/spec.vue'
	import jshopContent from '@/components/jshop/jshop-content.vue' //视频和文本解析组件
	import {
		h5Url
	} from '@/config/config.js'

	export default {
		components: {
			uniSegmentedControl,
			lvvPopup,
			uniNumberBox,
			uniRate,
			uniLoadMore,
			uniFab,
			spec,
			jshopContent,
		},
		data() {
			return {
				scrollData: {},
				barTitle: '',
				swiper: {
					indicatorDots: true,
					autoplay: true,
					interval: 3000,
					duration: 800,
				}, // 轮播图属性设置
				items: ['图文详情', '商品参数', '买家评论'],
				current: 0, // init tab位
				goodsId: 0, // 商品id
				goodsInfo: {}, // 商品详情
				cartNums: 0, // 购物车数量
				product: {}, // 规格详情
				goodsParams: [], // 商品参数信息
				goodsComments: {
					loadStatus: 'more',
					page: 1,
					limit: 5,
					list: []
				}, // 商品评论信息
				buyNum: 1, // 选定的购买数量
				minBuyNum: 1, // 最小可购买数量
				maxBuyNum:99,				
				type: 2, // 1加入购物车 2购买
				isfav: false, // 商品是否收藏
				favLogo: [
					'/static/image/ic-me-collect.png',
					'/static/image/ic-me-collect2.png'
				],
				iszero:false,
				horizontal: 'right', //右下角弹出按钮
				vertical: 'bottom',
				direction: 'vertical',
				pattern: {
					color: '#7A7E83',
					backgroundColor: '#fff',
					selectedColor: '#007AFF',
					buttonColor: "#f94048"
				},
				content: [{
						iconPath: '/static/image/tab-ic-hom-selected.png',
						selectedIconPath: '/static/image/tab-ic-hom-unselected.png',
						// text: '首页',
						active: false,
						url: '/pages/index/index'
					},
					{
						iconPath: '/static/image/tab-ic-me-selected.png',
						selectedIconPath: '/static/image/tab-ic-me-unselected.png',
						// text: '个人中心',
						active: false,
						url: '/pages/user/index'
					}
				],
				submitStatus: false,
				config: '', //配置信息
				goodsShowWord: [],
				shareUrl: '/pages/share/jump',
				userInfo: {}, // 用户信息
				kefupara: '', //客服传递资料
				isbuy:true,
				authBuy:true,
			}
		},
		onLoad(options) {
			//获取商品ID 
			if (options.id != '') {
				this.goodsId = options.id - 0;
			}
			var _this = this
			if (this.$db.get('userToken')) {
				this.$api.userInfo({}, res => {
					if (res.status) {
						_this.userInfo = res.data
						_this.auth = res.data.auth
					}
				})
			};
			if (this.goodsId) {
				this.getGoodsDetail();
				this.getGoodsParams();
				// this.getGoodsComments();
			} else {
				this.$common.errorToShow('获取失败', () => {
					uni.navigateBack({
						delta: 1
					});
				});
			}
			// this.loadFontFaceFromWeb();
		},
		onShow() {
			var _this = this
			if (_this.$db.get('userToken')) {
				_this.$api.userInfo({}, res => {
					if (res.status) {
						_this.userInfo = res.data
					}
				})
			};			
			_this.submitStatus = false;
		},
		computed: {
			province(){
				if(this.product.price == 0 || this.product.costprice==0) {
					return 0;
				} else {
					return this.$common.formatMoney((this.product.price - this.product.costprice)*0.2)
				}
			},
			// 规格切换计算规格商品的 可购买数量
			minNums() {
				if(this.product.stock == 0) {
					this.buyNum = 0
					return 0
				} else {
					return this.product.stock > this.minBuyNum ? this.minBuyNum : this.product.stock;
				}
			},		
			// 判断商品是否是多规格商品  (为了兼容小程序 只能写在计算属性里面了)
			isSpes() {
				if (this.product.hasOwnProperty('default_spes_desc') && Object.keys(this.product.default_spes_desc).length) {
					return true;
				} else {
					return false;
				}
			},
			// 优惠信息重新组装
			promotion() {
				let arr = [];
				if (this.product.promotion_list) {
					for (let k in this.product.promotion_list) {
						arr.push(this.product.promotion_list[k]);
					}
				}
				return arr;
			},
			shareHref() {
				let pages = getCurrentPages()
				let page = pages[pages.length - 1]
				// #ifdef H5 || MP-WEIXIN || APP-PLUS || APP-PLUS-NVUE
				return apiBaseUrl + 'wap/' + page.route + '?id=' + this.goodsId;
				// #endif

				// #ifdef MP-ALIPAY
				return apiBaseUrl + 'wap/' + page.__proto__.route + '?id=' + this.goodsId;
				// #endif
			},
			// 获取店铺联系人手机号
			shopMobile() {
				return this.$store.state.config.shop_mobile || 0;
			},
			defaultSpesDesc() {
				return this.product.default_spes_desc;
			}
		},
		onReachBottom() {
			if (this.current === 2 && this.goodsComments.loadStatus === 'more') {
				this.getGoodsComments();
			}
		},
		methods: {
			loadFontFaceFromWeb() {
				//需要注意的是每个页面都要调用，我是写在mixin里面
				uni.loadFontFace({
				  family: 'font',
				  source: 'url("/static/fonts/OPPOSans-B.ttf")',
				})
			},
			//这个是自己的方法名
			openAuth(){
				this.$refs['authphone'].open() //调起自定义权限目的弹框,具体可看示例里面很详细
			},
			//用户授权权限后的回调
			changeAuth(){
				//这里是权限通过后执行自己的代码逻辑
				console.log('权限已授权，可执行自己的代码逻辑了');
				this.goShare();				
			},			
			// 判断是不是微信浏览器
			ifwxl() {
				this.ifwx = this.$common.isWeiXinBrowser()
			},
			// 返回上一页
			backBtn() {
				var pages = getCurrentPages();
				if (pages.length > 1) {
					uni.navigateBack({
						delta: 1
					});
				} else {
					uni.switchTab({
						url: '/pages/index/index'
					});
				}
			},
			// 获取商品详情
			getGoodsDetail() {
				let data = {
					id: this.goodsId
				}

				// 如果用户已经登录 要传用户token
				let userToken = this.$db.get("userToken");

				if (userToken) {
					data['token'] = userToken;
				}
				this.$api.goodsDetail(data, res => {
					if (res.status == true) {
						let info = res.data;
						let products = res.data.product;

						//var htmlString = info.intro; //replace(/\\/g, "").replace(/<img/g, "<img style=\"display:none;\"")
						//info.intro = htmlParser(htmlString);
						this.goodsInfo = info;
						
						this.isfav = this.goodsInfo.isfav === 'true' ? true : false;
						this.product = this.spesClassHandle(products);
						

						// this.minBuyNum = info.minnum;
						// this.maxBuyNum = info.maxnum==0? this.product.stock :info.maxnum;
						
						
						this.buyNum    = info.minnum
												
						if(info.grade_ids && this.userInfo && userToken){
														
							let grade_arr = info.grade_ids.split(',');
							let grade_str = this.userInfo.grade.toString();
							if(grade_arr.indexOf(grade_str) == -1){
								this.isbuy = false;
							}														
							if(this.userInfo.free_nums>0 && info.isfree)	{
								this.iszero = true
								this.isbuy = true;
							} else if(this.userInfo.free_nums==0 && info.isfree){
								this.isbuy = false;
								this.iszero = false
							}
							
						}						

						// 获取购物车数量
						this.getCartNums();
						this.$api.shopConfig(res => {
							this.config = res;
							// console.log(res)
							this.goodsShowWord = res.goods_show_word;
						});
						// 判断如果登录用户添加商品浏览足迹
						// if (userToken) {
						// 	this.goodsBrowsing();
						// }
					} else {
						this.$common.errorToShow(res.msg, () => {
							uni.navigateBack({
								delta: 1
							});
						})
					}
				})
			},
			// 获取购物车数量
			getCartNums() {
				let userToken = this.$db.get("userToken");
				if (userToken && userToken != '') {
					// 获取购物车数量
					this.$api.getCartNum({}, res => {
						if (res.status) {
							this.cartNums = res.data;
						}
					})
				}
			},
			// 显示modal弹出框
			toshow(type) {
				this.type = type;
				this.$refs.lvvpopref.show();
			},
			// 关闭modal弹出框
			toclose() {
				this.$refs.lvvpopref.close();
			},
			// 切换商品规格
			changeSpes(obj) {
				let index = obj.v;
				let key = obj.k;

				let userToken = this.$db.get('userToken');
				let tmp_default_spes_desc = JSON.parse(this.product.default_spes_desc);
				if (tmp_default_spes_desc[index][key].hasOwnProperty('product_id') && tmp_default_spes_desc[index][key].product_id) {
					// this.$refs.spec.changeSpecData();
					this.$api.getProductInfo({
						id: tmp_default_spes_desc[index][key].product_id,
						token: userToken
					}, res => {
						if (res.status == true) {
							// 切换规格判断可购买数量
							let maxnum = res.data.stock
							if(res.data.maxnum>0 && res.data.maxnum<res.data.stock){
								maxnum = res.data.maxnum
							}
							this.buyNum = maxnum > this.minBuyNum ? this.minBuyNum : maxnum;
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
								this.$set(spes[key][i], 'cla', 'pop-m-item selected');
							} else if (spes[key][i].hasOwnProperty('product_id') && spes[key][i].product_id) {
								this.$set(spes[key][i], 'cla', 'pop-m-item not-selected');
							} else {
								this.$set(spes[key][i], 'cla', 'pop-m-item none');
							}
						}
					}
					spes = JSON.stringify(spes).replace(/\./g,'====');
					/* spes = JSON.stringify(spes) */
					products.default_spes_desc = spes;
				}
				return products;
			},
			// 购买数量加减操作
			bindChange(val) {
				this.buyNum = val;
			},
			// 商品收藏/取消
			collection() {
				let data = {
					goods_id: this.goodsInfo.id
				}
				this.$api.goodsCollection(data, res => {
					if (res.status) {
						this.isfav = !this.isfav;
						this.$common.successToShow(res.msg);
					} else {
						this.$common.errorToShow(res.msg);
					}
				})
			},
			// tab点击切换
			onClickItem(index) {
				if (this.current !== index) {
					this.current = index;
				}
			},
			// 获取商品参数信息
			getGoodsParams() {
				this.$api.goodsParams({
					id: this.goodsId
				}, res => {
					if (res.status == true) {
						this.goodsParams = res.data;
					}
				})
			},
			// 获取商品评论信息
			getGoodsComments() {
				let data = {
					page: this.goodsComments.page,
					limit: this.goodsComments.limit,
					goods_id: this.goodsId
				}

				this.goodsComments.loadStatus = 'loading';

				this.$api.goodsComment(data, res => {
					if (res.status == true) {
						let _list = res.data.list;
						let count = res.data.count;
						this.items = ['图文详情', '商品参数', '买家评论(' + count + ')']
						// 如果评论没有图片 在这块作处理否则控制台报错
						_list.forEach(item => {
							item.ctime = this.$common.timeToDate(item.ctime, true);
							if (!item.hasOwnProperty('images_url')) {
								this.$set(item, 'images_url', [])
							}
						});

						this.goodsComments.list = [...this.goodsComments.list, ..._list];
						// 根据count数量判断是否还有数据
						if (res.data.count > this.goodsComments.list.length) {
							this.goodsComments.loadStatus = 'more';
							this.goodsComments.page++;
						} else {
							this.goodsComments.loadStatus = 'noMore';
						}
					} else {
						this.$common.errorToShow(res.msg);
					}
				})
			},
			// 添加商品浏览足迹
			goodsBrowsing() {
				let data = {
					goods_id: this.goodsInfo.id
				}

				this.$api.addGoodsBrowsing(data, res => {});
			},
			// 加入购物车
			addToCart() {
				
				let buyNum = this.buyNum;
				
				 buyNum = buyNum ? buyNum :1;
				
				if (buyNum > 0) {
					let data = {
						product_id: this.product.id,
						nums: buyNum
					}
					this.$api.addCart(data, res => {
						if (res.status) {
							this.toclose(); // 关闭弹出层
							this.getCartNums(); // 获取购物车数量
							this.$common.successToShow(res.msg);
						} else {
							this.$common.errorToShow(res.msg);
						}
					}, res => {
						this.submitStatus = false;
					})
				}
			},
			// 立即购买
			buyNow() {
				
				let buyNum = this.buyNum;
				
				 buyNum = buyNum ? buyNum :1;
								
				if (buyNum > 0) {
					let data = {
						product_id: this.product.id,
						nums: buyNum,
						type: 2 // 区分加入购物车和购买
					}

					this.$api.addCart(data, res => {
						
						if (res.status) {
							this.toclose();
							let cartIds = res.data;
							if(this.type>=3){
								let url = ''
								url = '/pages/goods/place-order/index?exp=2&cart_ids=' + JSON.stringify(cartIds);								
								this.$common.navigateTo(url);	
							} else {
								this.$common.navigateTo('/pages/goods/place-order/index?exp=1&cart_ids=' + JSON.stringify(cartIds));
							}							
						} else {
							this.$common.errorToShow(res.msg);
						}
					}, res => {
						this.submitStatus = false;
					})
				}
			},		
			// 购物车页面跳转
			redirectCart() {
				uni.switchTab({
					url: '/pages/cart/index/index'
				});
				// this.$common.navigateTo('/pages/cart/index/index');
			},
			// 点击弹出框确定按钮事件处理
			clickHandle() {
				this.submitStatus = true;
				if(this.type == 1){
					this.addToCart()
				} else if(this.type == 2){
					this.buyNow()
				} else if(this.type >=3){
					this.buyNow()
				}
				//this.type === 1 ? this.addToCart() : this.buyNow();
			},
			trigger(e) {
				this.content[e.index].active = !e.item.active;
				uni.switchTab({
					url: e.item.url
				})
			},
			topageurl(url){
				this.$common.navigateTo(url)
			},
			// 图片点击放大
			clickImg(imgs) {
				// 预览图片
				uni.previewImage({
					urls: imgs.split()
				});
			},
			//在线客服,只有手机号的，请自己替换为手机号
			showChat() {
				let _this = this;
				// #ifdef H5
				window._AIHECONG('ini', {
					entId: this.config.ent_id,
					button: false,
					appearance: {
						panelMobile: {
							tone: '#FF7159',
							sideMargin: 30,
							ratio: 'part',
							headHeight: 50
						}
					}
				})
				//传递客户信息
				window._AIHECONG('customer', {
					head: _this.userInfo.avatar,
					'名称': _this.userInfo.nickname,
					'手机': _this.userInfo.mobile
				})
				window._AIHECONG('showChat')
				// #endif

				// 客服页面
				// #ifdef APP-PLUS || APP-PLUS-NVUE
				this.$common.navigateTo('/pages/member/customer_service/index');
				// #endif

				// 头条系客服
				// #ifdef MP-TOUTIAO
				if (this.shopMobile != 0) {
					let _this = this;
					tt.makePhoneCall({
						phoneNumber: this.shopMobile.toString(),
						success(res) {},
						fail(res) {}
					});
				} else {
					_this.$common.errorToShow('暂无设置客服电话');
				}
				// #endif
			},
		},
		onHide() {
			uni.hideLoading()
		},
		onPageScroll(e) {
			this.barTitle = e.scrollTop > 100 ? '商品详情' : ''
			this.scrollData = e
		},
	}
</script>

<style>
	
	.goods-brief {
		width: 750rpx;
		background-color: #fff;
		padding: 20rpx;
		margin:20rpx auto 0rpx;
		border-radius:10rpx;
		overflow: hidden;
	}
	
	.iosfoot{
		bottom:constant(safe-area-inset-bottom);
		bottom:env(safe-area-inset-bottom);
		box-sizing:content-box;
	}
	.content-top {
		padding-bottom: 40rpx;
	}

	.swiper {
		height: 750rpx;
	}

	.goods-top {
		border-bottom: 0;
	}

	.goods-top .goods-price {
		font-size: 38rpx;
		font-family: "font";
	}

	.cost-price {
		font-size: 28rpx !important;
		bottom: -5rpx;
		color: #999;
		text-decoration: line-through;
	}

	.red-price {
		color: rgb(255, 80, 0) !important;
		font-size:48rpx !important;
	}
	
	.red-price .em {
		font-size: 36rpx;
		font-style: normal;
	}

	.goods-top .cell-item-ft {
		font-size: 20rpx;
		color: #666;
	}

	.goods-details {
		padding-top: 0;
	}

/* 	.goods-details .cell-hd-title {
		width: 620rpx;
	} */

	.goods-details .cell-hd-title .cell-hd-title-view {
		width: 100%;
		display: -webkit-box;
		-webkit-box-orient: vertical;
		-webkit-line-clamp: 2;
		overflow: hidden;
	}

	.goods-details .cell-hd-title .cell-hd-title-view:last-child {
		margin-top: 20rpx;
	}

	.goods-details .cell-item-ft {
		top: 24rpx;
	}

	.goods-title-item .cell-item-hd {
		min-width: 60rpx;
		color: #666;
		font-size: 24rpx;
	}

	.goods-title-item .cell-item-bd {
		color: #333;
		font-size: 24rpx;
		display: block;
	}

	.goods-title-item .cell-bd-text {
		bottom: 0;
	}

	.cell-bd-view {
		position: relative;
		overflow: hidden;
		margin-bottom: 8rpx;
	}

	.cell-bd-view:last-child {
		margin-bottom: 0;
	}

	.goods-title-item-ic {
		width: 22rpx;
		height: 22rpx;
		position: absolute;
		top: 50%;
		transform: translateY(-50%);
		/* #ifdef MP-ALIPAY */
		background-size: 100% 100%;
		/* #endif */
	}

	.cell-bd-view .cell-bd-text {
		margin-left: 30rpx;
	}

	.goods-content {
		width: 750rpx;
		margin: 20rpx auto 0;
		background-color: #fff;
		padding: 0rpx;
		border-radius: 10rpx;
		overflow: hidden;
		text-align: center;
	}

	.goods-content-c {
		padding: 0rpx 10rpx 20rpx 10rpx;
	}

	.goods-parameter {
		padding: 10rpx 26rpx;
		min-height: 600rpx;
	}
	.goods-bottom{
		background-color: #fff;
		position: fixed;
		bottom: 0;
		height: 90rpx;
		width: 100%;
		overflow: hidden;
		box-shadow: 0 0 20rpx #ccc;
		box-sizing:content-box;
		padding-bottom:constant(safe-area-inset-bottom); 
		padding-bottom: env(safe-area-inset-bottom);		
	}
	.pop-b {
		background-color: #fff;
		position: fixed;
		bottom: 0;
		height: 90rpx;
		width: 100%;
		overflow: hidden;
		box-shadow: 0 0 20rpx #ccc;
		padding-bottom:constant(safe-area-inset-bottom); 
		padding-bottom: env(safe-area-inset-bottom);	
		box-sizing:content-box;
	}

	.goods-bottom .btn {
		height: 100%;
		width: 29%;
		float: left;
	}

	.goods-bottom-ic {
		display: inline-block;
		position: relative;
		text-align: center;
		height: 100%;
		width: 14%;
		float: left;
		font-size: 22rpx;
		color: #666;
	}

	.goods-bottom-ic .icon {
		position: relative;
		top: 6rpx;
		/* #ifdef MP-ALIPAY */
		background-size: 100% 100%;
		/* #endif */
	}

	.goods-bottom .btn-g {
		color: #333;
		background-color: #D9D9D9;
	}

	.goods-parameter .cell-item {
		border-bottom: none;
		margin-left: 0;
	}

	.goods-parameter .cell-item-hd {
		color: #333;
		font-size: 24rpx;
	}

	.goods-parameter .cell-item-bd {
		color: #999;
	}

	.goods-parameter .cell-item-bd .cell-bd-text {
		bottom: 0;
	}

	.goods-parameter .cell-bd-text {
		margin-left: 0;
	}

	.pop-t {
		position: relative;
		padding: 30rpx 26rpx;
		border-bottom: 2rpx solid #f3f3f3;
	}

	.goods-img {
		width: 160rpx;
		height: 160rpx;
		position: absolute;
		top: -20rpx;
		background-color: #fff;
		border-radius: 6rpx;
		border: 2rpx solid #fff;

	}

	.goods-img image {
		height: 100%;
		width: 100%;
	}

	.goods-information {
		width: 420rpx;
		display: inline-block;
		margin-left: 180rpx;
	}

	.pop-goods-name {
		width: 100%;
		overflow: hidden;
		white-space: nowrap;
		text-overflow: ellipsis;
		display: block;
		font-size: 24rpx;
		margin-bottom: 20rpx;
	}

	.pop-goods-price {
		font-size: 30rpx;
	}

	.close-btn {
		width: 40rpx;
		height: 40rpx;
		border-radius: 50%;
		display: inline-block;
		position: absolute;
		right: 30rpx;
	}

	.close-btn image {
		width: 100%;
		height: 100%;
	}

	.pop-m {
		font-size: 28rpx;
		margin-bottom: 90rpx;
	}

	.goods-specs,
	.goods-number {
		padding: 26rpx;
		border-top: 1px solid #f3f3f3;
	}

	.goods-specs:first-child {
		border: none;
	}

	.pop-m-title {
		margin-right: 10rpx;
		color: #666;
	}

	.pop-m-bd {
		overflow: hidden;
		margin-top: 10rpx;
	}

	.pop-m-item {
		display: inline-block;
		float: left;
		padding: 6rpx 16rpx;
		background-color: #fff;
		color: #333;
		margin-right: 16rpx;
		margin-bottom: 10rpx;
	}

	.selected {
		border: 2rpx solid #C32017;
		background-color: #C32017;
		color: #fff;
	}

	.not-selected {
		border: 2rpx solid #ccc;
	}

	.none {
		border: 2rpx dashed #ccc;
		color: #888;
	}

	.pop-m-bd-in {
		display: inline-block;
	}

	.badge {
		top: 2rpx;
		left: 62rpx;
	}

	.goods-assess .user-head-img {
		width: 80rpx;
		height: 80rpx;
		border-radius: 50%;
	}

	.goods-assess .cell-item-bd {
		padding-right: 0;
	}

	.goods-assess .cell-bd-text {
		margin: 0;
	}

	.goods-assess .cell-bd-text.color-9 {
		overflow: hidden;
		text-overflow: ellipsis;
		white-space: nowrap;
		max-width: 440rpx;
	}

	.gai-body {}

	.gai-body-text {
		font-size: 26rpx;
		color: #333;
		padding: 0 26rpx;
		word-wrap: break-word;
	}

	.gai-body-img {
		overflow: hidden;
		padding: 20rpx 26rpx;
	}

	.gai-body-img image {
		width: 220rpx;
		height: 220rpx;
		float: left;
		margin-right: 19rpx;
		margin-bottom: 18rpx;
	}

	.gai-body-img image:nth-child(3n) {
		margin-right: 0;
	}

	.redstar {
		width: 24rpx;
		height: 24rpx;
		padding: 2rpx;
	}

	.mask-share-wechat {
		display: inline-block;
		background-color: #fff;
		padding: 0;
	}

	.mask-share-wechat:after {
		border: none;
	}

	.right-ball {
		position: fixed;
		right: 30rpx;
		bottom: 300rpx;
		z-index: 999;
		text-align: center;
		padding: 14rpx 0;
		width: 80rpx;
		height: 80rpx;
		font-size: 24rpx;
		color: #fff;
		background-color: rgba(0, 0, 0, .5);
		border-radius: 50%;
	}

	.comment-none {
		text-align: center;
		padding: 160rpx 0;
	}

	.comment-none-img {
		width: 410rpx;
		height: 200rpx;
	}


	.price-salesvolume {
		width: 100%;
		padding: 0 0 0 26rpx;
		overflow: hidden;
		color: #A5A5A5;
		background-color: rgb(252, 226, 80);
		position: relative;
	}

	.commodity-price {
		width: 224rpx;
		display: inline-block;
		float: left;
	}

	.current-price {
		font-size: 40rpx;
		color: #FF7159;
		display: block;
		line-height: 1.5;
	}

	.cost-price {
		font-size: 26rpx;
		text-decoration: line-through;
		display: block;
	}

	.commodity-salesvolume {
		width: 240rpx;
		display: inline-block;
		font-size: 22rpx;
		float: left;
		padding: 16rpx 0;
	}

	.commodity-salesvolume>text {
		display: block;
	}

	.commodity-time-img {
		display: block;
		width: 0;
		height: 0;
		border-width: 48rpx 28rpx 50rpx 0;
		border-style: solid;
		border-color: transparent #FF7159 transparent transparent;
		/*透明 黄 透明 透明 */
		position: absolute;
		top: 0px;
		left: 462rpx;
	}

	.commodity-time {
		display: inline-block;
		width: 260rpx;
		text-align: center;
		font-size: 24rpx;
		background-color: #FF7159;
		padding: 16rpx 0 18rpx;
		color: rgb(250, 233, 0);
	}

	.commodity-time>text {
		display: block;
	}

	.commodity-day {
		font-size: 22rpx;
	}

	.commodity-day>text {
		display: inline-block;
		background-color: rgb(255, 212, 176);
		color: rgb(255, 115, 0);
		padding: 0 6rpx;
		border-radius: 6rpx;
	}

	.nav-back {
		width: 100%;
		height: 44px;
		/* #ifndef MP-WEIXIN */
		padding: 12px 12px 0;
		/* #endif */
		/* #ifdef MP-WEIXIN */
		padding: 26px 12px 0;
		/* #endif */


		/* #ifndef APP */
		top: 0;
		/* #endif */
		/* #ifdef APP */
		top: 70rpx;
		/* #endif */
		position: fixed;		
		background-color: rgba(255, 255, 255, 0);
		z-index: 98;
	}

	.back-btn {
		height: 32px;
		width: 32px;
		border-radius: 50%;
		background-color: rgba(255, 255, 255, 0.8);
	}

	.back-btn .icon {
		height: 20px;
		width: 20px;
		position: relative;
		top: 50%;
		left: 46%;
		transform: translate(-50%, -50%);
	}
	
	
	.nav-right {
		width: 88rpx;
		height: 88rpx;
		/* #ifdef H5 */
		padding: 12px 12px 0;
		top: 0;
		/* #endif */
		
		/* #ifdef APP */
		padding: 12px 12px 0;
		top: 70rpx;
		/* #endif */
		
		/* #ifdef MP-WEIXIN */
		padding: 26px 12px 0;
		top: 80rpx;
		/* #endif */

		position: fixed;		
		right:40rpx;
		background-color: rgba(255, 255, 255, 0);
		z-index: 98;
	}

/* 	.back-btn {
		height: 32px;
		width: 32px;
		border-radius: 50%;
		background-color: rgba(255, 255, 255, 0.8);
	} */

	.nav-right .back-btn .icon {
		left: 21%;
	}	
	

	.seller-content {
		background-color: #f8f8f8;
		margin: 0 13px 15px;
		padding: 10px;
		color: #6e6e6e;
		border-radius: 4px;
	}

	.seller-content-top {
		font-weight: bold;
		margin-bottom: 6px;
	}

	.seller-content-img {
		width: 20px;
		height: 20px;
		vertical-align: middle;
		margin-right: 4px;
	}

	.service {
		width: 80rpx;
		height: 80rpx;
		background-color: #fff;
		border-radius: 50%;
		position: fixed;
		left: 30rpx;
		bottom: 120rpx;
		display: flex;
		justify-content: center;
		align-items: center;
		box-shadow: 0 0 10rpx #ccc;
		padding: 0;
	}

	.service .icon {
		width: 60rpx;
		height: 60rpx;
	}

	/* #ifdef MP-WEIXIN */
	.weiContact {
		background-color: #fff;
		border: none;
	}

	.weiContact::after {
		border: none;
	}

	.weiContact>view {
		position: absolute;
		top: 45rpx;
		left: 50%;
		transform: translateX(-50%);

	}

	/* #endif */
	
	.btn-red {
		background-color: #D52C32;
		flex: 1;
		color: #fff;		
	}
	
	.btn-yellow {
		background-color: #ffaa00;
		color: #fff;
	}
	.bg_exp {
		background: rgba(249, 64, 72, 0.1);
		color: rgba(249, 64, 72, 0.75);
		font-size: 22rpx;
		padding:6rpx 20rpx;
		border-radius: 5rpx;
		overflow: hidden;
		margin-top: 20rpx;
		margin-right: 20rpx;
	}
	.openvip {
		border: #141414 2rpx solid;
		background: linear-gradient(to right,#2f2f2f,#363636,#3d3d3d,#424242);
		height: 65rpx;
		line-height: 65rpx;
		border-radius: 6rpx;
		overflow: hidden;
		padding: 0 20rpx;
		color: #f3bf91;
		margin: 20rpx 0 30rpx;
		justify-content: space-between;
	}
	.openvip .vip1{
		font-family: "font_m"
	}
	.openvip .desc {
		font-family: "font_m"
	}
	.goods-details {
		margin-top: 10rpx;
	}
	.goods-brief-tip {
		line-height: 46rpx;
	}
	
.cell-groupArea {
	width: 750rpx;
	margin: 20rpx auto 0;
	padding-bottom: 20rpx;
	overflow: hidden;
}	

.cell-item {
	width: auto;
}

.font-goods-price {
	align-items: flex-end;
	margin-right: 20rpx;
}

.goods_shen image {
	width: 750rpx;
	height:108rpx;	
}

.productName {
	font-family: "font";	
}
</style>
