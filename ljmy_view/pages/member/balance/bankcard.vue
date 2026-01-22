<template>
	<view class="content">
		<view class="content-top" v-if="cards.length">
			<view class="card-item"
			v-for="(item, index) in cards"
			:key="index"
			>
				<view class="card-item-tip" v-if="item.is_default === 1">
					<view class="cit-bg"></view>
					<view class="cit-text">默</view>
				</view>
				<view class="card-item-body">
					<view class="cib-left">
						<image class="bank-logo" :src="item.bank_logo" mode=""></image>
					</view>
					<view class="cib-right">
						<view class="cibr-t color-3">
							{{ item.bank_name }} - {{ item.card_type }}
						</view>
						<view class="cibr-b color-9">
							{{ item.card_number }}
						</view>
					</view>
				</view>
				<view class="mr-card" 
				v-if="item.is_default === 2"
				@click="setDefault(item.id)"
				>
					<button class="btn btn-w" :disabled='submitStatus' :loading='submitStatus'>设为默认</button>
				</view>
				<view class="del-card" 
				v-if="mold"
				@click="selected(index)"
				>
					<button class="btn btn-b">选择</button>
				</view>
				<view class="del-card" 
				v-else
				@click="removeCard(item.id)"
				>
					<button class="btn btn-b" :disabled='delSubmitStatus' :loading='delSubmitStatus'>删除</button>
				</view>
			</view>
		</view>
		<view class="cards-none" v-else>
			<image class="cards-none-img" src="/static/image/order.png" mode=""></image>
		</view>
		<view class="button-bottom">
			<button class="btn btn-b" @click="goAddcard()">添加银行卡</button>
		</view>
	</view>
</template>

<script>
export default {
	data () {
		return {
			mold: '',
			cards: [] ,// 我的银行卡列表
			submitStatus: false,
			delSubmitStatus: false
		}
	},
	onLoad (options) {
		if (options.mold && options.mold == 'select') {
			this.mold = options.mold
		}
	},
	onShow () {
		this.getBankCards()
	},
	methods:{
		// 获取我的银行卡列表
		getBankCards() {
			this.$api.getBankCardList({}, res => {
				if (res.status) {
					this.cards = res.data
				}
			})
		},
		// 删除银行卡
		removeCard (cardId) {
			this.$common.modelShow('提示', '确定删除该银行卡?', res => {
				this.delSubmitStatus = true;
				let data = {
					id: cardId
				}
				this.$api.removeBankCard(data, res => {
					if (res.status) {
						this.$common.successToShow(res.msg, ress => {
							this.delSubmitStatus = false;
							this.getBankCards();
						})
					} else {
						this.$common.errorToShow(res.msg);
						this.delSubmitStatus = false;
					}
				})
			})
		},
		// 设置默认卡
		setDefault (id) {
			this.submitStatus = true;
			let data = {
				id: id
			}
			this.$api.setDefaultBankCard(data, res => {
				if (res.status) {
					this.$common.successToShow(res.msg, ress => {
						this.getBankCards();
					})
				} else {
					this.$common.errorToShow(res.msg);
				}
			},res => {
				this.submitStatus = false;
			})
		},
		// 添加新的银行卡
		goAddcard(){
			this.$common.navigateTo('./add_bankcard')
		},
		selected (index) {
			let pages = getCurrentPages();//当前页
			let beforePage = pages[pages.length - 2];//上个页面
			
			// #ifdef H5 || APP-PLUS || APP-PLUS-NVUE
			beforePage.cardInfo = this.cards[index]
			// #endif
			
			// #ifdef MP-WEIXIN
			beforePage.$vm.cardInfo = this.cards[index]
			// #endif
			
			// #ifdef MP-ALIPAY || MP-TOUTIAO
			this.$db.set('user_card_info', this.cards[index], true);
			// #endif
			
			uni.navigateBack({
				delta: 1
			})
		}
	}
}
</script>

<style>
.card-item{
	position: relative;
	background-color: #fff;
	margin: 26rpx;
	border-radius: 10rpx;
	box-shadow: 0 0 20rpx #ccc;
	padding: 60rpx 30rpx 80rpx;
}
.card-item-tip{
	position:absolute;
	top:0rpx;
	left:0rpx;
	z-index:10;
	border-top-left-radius:10rpx;
	overflow:hidden;
	width:100rpx;
	height:100rpx;
}
.cit-bg{
	position:absolute;
	top:0;
	left:0;
	z-index:11;
	color:#ffffff;
	width:0rpx;
	height:0rpx;
	border-bottom:solid 100rpx transparent;
	border-right:solid 100rpx transparent;
	border-top:solid 100rpx #FF7159;
}
.cit-text{
	position:absolute;
	top:0;
	left:0;
	z-index:12;
	color:#ffffff;
	margin-top:4rpx;
	margin-left:14rpx;
	font-size:30rpx;

}
.card-item-body{
	position: relative;
}
.cib-left{
	position: absolute;
	top: 60%;
	transform: translateY(-50%);
	width: 250rpx;
}
.bank-logo{
	width: 240rpx;
	height: 70rpx;
}
.cib-right{
	margin-left: 250rpx;
}
.cibr-t{
	font-size: 30rpx;
	margin-bottom: 10rpx;
	text-align: center;
}
.cibr-b{
	font-size: 26rpx;
	text-align: center;
}
.mr-card{
	position: absolute;
	right: 140rpx;
	bottom: 0rpx;
}
/* #ifdef MP-TOUTIAO */
.mr-card{
	position: absolute;
	right: 140rpx;
	bottom: 12rpx;
}
.del-card{
	position: absolute;
	right: 30rpx;
	bottom: 12rpx;
}
/* #endif */
/* #ifndef MP-TOUTIAO */
.mr-card{
	position: absolute;
	right: 140rpx;
	bottom: 0rpx;
}
.del-card{
	position: absolute;
	right: 30rpx;
	bottom: 0rpx;
}
/* #endif */
.del-card .btn,.mr-card .btn{
	font-size: 24rpx;
	height: 48rpx;
	line-height: 46rpx;
	padding: 0 16rpx;
}
.cards-none{
	text-align: center;
	padding: 200rpx 0;
}
.cards-none-img{
	width: 274rpx;
	height: 274rpx;
}
</style>