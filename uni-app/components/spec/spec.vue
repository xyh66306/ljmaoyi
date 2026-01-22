<template>
	<view>
		<view class="goods-specs" v-for="(item, index) in specList" :key="index">
			<text class="pop-m-title">{{ index }}</text>
			<view class="pop-m-bd">
				<view :class="spes.cla" v-for="(spes, key) in item" :key="key" @click="specChangeSpes(index, key)">
					{{ spes.name.replace(/====/g,'.') }}
				</view>
			</view>
		</view>
	</view>
</template>
<script>
	export default {
		name: "spec",
        data() {
            return {
                specList: {}
            }
        },
		props: {
			// 默认picker选中项索引
			spesData: {
				required: true
			}
		},
        watch:{
            spesData: function (val) {
				if(typeof val == 'object'){
					this.specList = val;
				}else{
					let d = JSON.parse(val);
					this.specList = d;
				}
            }
        },
		methods: {
			specChangeSpes(v, k){
				let newData = {
					v: v,
					k: k
				}
				this.$emit("changeSpes", newData);
			},
            changeSpecData(){
                this.specList = {};
            }
		}
	}
</script>
<style>

.goods-specs,.goods-number{
	padding: 26rpx;
	border-top: 1px solid #f3f3f3;
}
.goods-specs:first-child{
	border: none;
}
.pop-m-title{
	margin-right: 10rpx;
	color: #666;
}
.pop-m-item{
	display: inline-block;
	float: left;
	padding: 6rpx 16rpx;
	background-color: #fff;
	color: #333;
	margin-right: 16rpx;
	margin-bottom: 10rpx;
}
.pop-m-bd{
	overflow: hidden;
	margin-top: 10rpx;
}
.selected{
	border: 2rpx solid #333;
	background-color: #333;
	color: #fff;
}
.not-selected{
	border: 2rpx solid #ccc;
}
.none{
	border: 2rpx dashed #ccc;
	color: #888;
}
</style>