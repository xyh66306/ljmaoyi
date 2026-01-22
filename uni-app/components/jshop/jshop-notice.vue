<template>
	<view class="notice bottom-cell-group" v-if="jdata.params.list && count">
		<view class="notice-icon">
			<image class="icon news-icon" src="/static/image/news.png" mode=""></image>
		</view>
		<swiper class="notice-c" :indicator-dots="false" :autoplay="true" :interval="3000" :duration="1000" :vertical="true" :circular="true">
			<swiper-item v-for="item in jdata.params.list" :key="item.id">
				<view class="swiper-item" @click="goNotice(item.id)">{{ item.title }}</view>
			</swiper-item>
		</swiper>
	</view>
</template>

<script>
	export default {
		name: "jshopnotice",
		props: {
			jdata:{
				// type: Object,
				required: true,
			}
		},
		computed: {
			count() {  
				return (this.jdata.params.list.length > 0)
			}
		},
		methods: {
			// 点击公告
			goNotice(id) {
				// 文章详情
				this.$common.navigateTo('/pages/article/index?id=' + id+'&id_type=2')
			},
		},
	}
</script>

<style>
.notice {
	padding: 6rpx 26rpx 6rpx 60rpx;
	position: relative;
	overflow: hidden;
	background-color: #fff;
	color: #333;
}
.notice-icon {
	display: inline-block;
	height: 40rpx;
	position: absolute;
	top: 59%;
	left: 26rpx;
	transform: translateY(-50%);
	overflow: hidden;
}
.news-icon {
	width: 30rpx;
	height: 30rpx;
	float: left;
}
.notice-c {
	margin-left: 10rpx;
	height: 50rpx;
	line-height: 50rpx;
	width: 630rpx;
	display: inline-block;
	font-size: 28rpx;
	float: left;
}
</style>
