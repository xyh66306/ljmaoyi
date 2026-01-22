<template>
	<lvv-popup position="bottom" ref="lvvpopref">
		<view class="pop-c">
			<view class="close-btn" @click="toclose()"> <text class="iconfont iconguanbi"></text></view>
			<view class="pop-t">
				<view class="goods-img"><image :src="product.image_path" mode="aspectFill"></image></view>
				<view class="goods-information">
					<view class="pop-goods-name">{{ product.name || '' }}</view>
					<view class="pop-goods-price red-price"> 
						<text class="fsz22"> ￥</text> 
						<text class="fsz32">{{ price || product.price || '' }}</text>
						
					 </view>
				</view>
			</view>
			<scroll-view class="pop-m" scroll-y="true">
				<spec v-if="product && product.default_spes_desc" 
				:spesData="product.default_spes_desc" ref="spec" @changeSpes="changeSpes"></spec>
				<view class="goods-number">
					<text class="pop-m-title">购买数量  <text class="g5 fsz24 stock">剩余{{product.stock}} {{unit || ''}}</text> </text>
					
					<view class="pop-m-bd-in">
						<uni-number-box :min="minNums" :max="product.stock" :value="buyNum" @change="bindChange" 
						:has-border="true" :width="62" :size="28"></uni-number-box>
					</view>
				</view>
			</scroll-view>
			<view class="button-bottom sigle">
				<button class="btn btn-square btcf btn-all"
					hover-class="btn-hover2" @click="clickHandle()" :disabled="submitStatus"
					v-if="product.stock" >
          确定
				</button>
				<button class="btn btn-square btn-g btn-all" v-else>已售罄  </button>
			</view>
			
			
	    </view>
	</lvv-popup>
</template>

<script>
	import spec from '@/components/custome/spec/index.vue';
	export default {
		components: {
			spec
		},
		props: {
			product: {
				default: () => {},
				type: Object,
				required: true
			},
			submitStatus: {
				default: false,
				type: Boolean
			},
			minNums: {
				default: 0,
				type: String | Number 
			},
			buyNum: {
				default: 0,
				type: String | Number 
			},
			price: {
				default: 0,
				type: String | Number 
			},
			unit: {
				default: 0,
				type: String  | Number 
			},
			
		},
		mounted() {
			console.log('product', this.product);
		},
		methods: {
			show() {
				this.$refs.lvvpopref.show()
			},
			// 切换商品规格
			changeSpes(obj) {
			    this.$emit("changeSpes", obj)
			},
			clickHandle() {
				console.log('clickHandle')
				this.$emit("clickHandle")
			},
			toclose() {
				this.$refs.lvvpopref.close()
			},
			bindChange(v) {
				if(v==this.product.stock){
					this.$common.errorToShow('已经是最大库存了~')
				}
				this.$emit('bindChange', v)
			}
		}
	}
</script>

<style lang="scss" scoped>
.pop-c {
	width: 100%;
	max-height: 80vh;
	background: #FFFFFF;
	position: absolute;
	left:0;
	bottom: 0;
	z-index: 9999;
	.close-btn {
		width: 40rpx;
		height: 40rpx;
		border-radius: 50%;
		display: inline-block;
		position: absolute;
		right: 30rpx;
		top: 20rpx;
		z-index: 999;
		image {
			width: 100%;
			height: 100%;
		}
	}
	.pop-t {
	    position: relative;
	    padding:45rpx 20rpx 30rpx 20rpx;
		display: flex;
		height: 240rpx;
		box-sizing: border-box;
		.goods-img {
		    width: 238rpx;
		    height: 238rpx;
		    border-radius: 6rpx;
			margin-top: -80rpx;
			border: 6rpx solid #FFFFFF;
			background: #FFFFFF;
			image {
			    height: 100%;
			    width: 100%;
			}
		}
		.goods-information {
		    width: 420rpx;
		    display: inline-block;
			margin-left: 30rpx;
			.pop-goods-price {
			    font-size: 30rpx;
			}
			.pop-goods-name {
			    width: 100%;
			    display: block;
				font-size: $fz14;
				color: $gray-7;
			    margin-bottom: 20rpx;
				@include ellNum();
				margin-top: 20rpx;
			}
			.pop-goods-check {
				color: $gray-7;
				font-size: $fz12;
				@include ellNum();
			}
		}
	}
	.pop-m {
	    font-size: 28rpx;
	    margin-bottom: 300rpx;
		padding-bottom: 300rpx;
		height: calc(80vh - 240rpx - 110rpx);
		.goods-number {
		    padding: 26rpx;
			display: flex;
			justify-content: space-between;
			.pop-m-title {
			    margin-right: 10rpx;
			    color: #666;
			}
			.pop-m-bd-in {
			    display: inline-block;
			}
		}
	}
	.pop-b {
	    background-color: #fff;
	    position: fixed;
	    bottom: 0;
	    height: 90rpx;
	    width: 100%;
	    overflow: hidden;
	    box-shadow: 0 0 20rpx #ccc;
	}
}
.stock {
	margin-left: 10rpx;
}
</style>
