<template>
	<view class="wrap">
		<image :src="`${baseUrl}static/images/public/no-data.png`" mode=""></image>
	</view>
</template>

<script>
	import {apiBaseUrl} from '@/config/config.js';
	export default {
		data() {
			return {
				baseUrl:apiBaseUrl
			}
		},
	}
</script>

<style lang="scss" scoped>
.wrap {
	display: flex;
	align-items: center;
	justify-content: center;
	margin: 30rpx 0;
	padding: 30rpx 0;
	image {
		width: 300rpx;
		height: 300rpx;
	}
}
</style>
