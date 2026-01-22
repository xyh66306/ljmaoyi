<template>
	<view class="swiper" :style="{'width': width, 'height': height}" >
	    <swiper class="swiper-c" 
		:indicator-dots="indicatorDots" :autoplay="autoplay" :interval="interval" :duration="duration" 
		@change="change">
		
			<swiper-item class="have-none"  v-if="video && video.id">
				<video  :src="video.video_url" 
				:poster="video.video_cover"
				:enable-progress-gesture="false" 
				style="width: 100%; height: 100%;object-fit:fill"
				webkit-playsinline="true"
				playsinline="true"
				x5-video-player-type="h5"
				x5-video-player-fullscreen="false"
				></video>
			</swiper-item>
			
			
	        <swiper-item class="have-none" v-for="(item, index) in imgArr " :key="index"  @click="imgClick(imgArr, index)" >
	            <image class="" :src="item.img || item" mode="aspectFill"></image>
	        </swiper-item>
	    </swiper>
		<view class="num-wrap" v-if="showNum">{{acNum + 1}}/{{video && video.id ? imgArr.length + 1 :  imgArr.length}}</view>
	</view>
</template>

<script>
	import { tools } from '@/config/mixins.js'
	export default {
		mixins: [tools],
		props: {
			width: {
				default: '750rpx',
				type: String
			},
			height: {
				default: '750rpx',
				type: String
			},
			imgArr: {
				default: ()=> [],
				type: [Array, Object]
			},
			// 自动循环
			autoplay: {
				default: false,
				type: Boolean
			},
			// 滑动动画时长
			duration: {
				default: 500,
				type: Number
			},
			// 切换间隔
			interval: {
				default: 3000,
				type: Number
			},
			// 指示点
			indicatorDots: {
				default: true,
				type: Boolean
			},
			// 是否以数字显示
			showNum:{
				default: false,
				type: Boolean
			},
			type:{
				default: 'preview',
				type: String
			},
			video: {
				default: () => {},
				type: [Object, String]
			},
			adCode:{
				default: 'home',
				type: String
			}
		},
		data() {
			return {
				acNum: 0,
				dotsNum: 0
			}
		},
		mounted() {
			
		},
		methods: {
			imgClick(urls, current) {
				let {val, type} = urls[current]
				// if (!val) { return; }
				this.$api.addRecord({'type':'5','api_method':"advert.getcarousellists","platform":"2","code":this.adCode,"rel_id":val})
				if(this.type == 'link') {
					if (type == 1) {
						if (val.indexOf('http') != -1) {
							// #ifdef H5 
							window.location.href = val
							// #endif
						} else {
							// #ifdef H5 || APP-PLUS || APP-PLUS-NVUE || MP
							if (val == '/pages/index/index' || val == '/pages/classify/classify' ||
                  val == '/pages/cart/index/index' || val == '/pages/cart/index/auction' || val == '/pages/member/index/index') {
								uni.reLaunch({
									url: val
								});
								return;
							} else {
								this.$common.navigateTo(val);
								return;
							}
							// #endif
						}
					} else if (type == 2) {
						// 商品详情
						this.goodsDetail(val)
						
					} else if (type == 3) {
						// 文章详情
						this.$common.navigateTo('/pageactivity/article/index?id=' + val + '&id_type=1')
					} else if (type == 4) {
						// console.log("11")
						// 文章列表
						this.$common.navigateTo('/pageactivity/article/list?cid=' + val)
					} else if (type == 5) {
						//智能表单 
						this.$common.navigateTo('/pages/form/detail/form?id=' + val)
					}
					
					
				} else {
					uni.previewImage({
						current: current || 0,
					    urls,
						indicator: 'default'
					});
				}
			},
			change(e) {
				this.acNum = e.detail.current
			},
			//跳转到商品详情页面
			goodsDetail: function(id) {
				let url = '/pages/goods/index/index?id=' + id;
				this.$common.navigateTo(url);
			},
		}
	}
</script>

<style lang="scss" scoped>
.swiper {
	position: relative;
	.num-wrap {
		position: absolute;
		right: 20rpx;
		bottom: 20rpx;
		background:rgba(0,0,0,.6);
		padding: 4rpx 20rpx;
		font-size: $fz13;
		color: $white;
		border-radius: 20rpx;
	}
}
</style>
