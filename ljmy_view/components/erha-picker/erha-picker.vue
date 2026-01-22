<template>
	<view class="level-container">
		<h1 class="title">选择收货地址</h1>
		<ul class="name-list">
			<li :class="showIndex==0?'select':''" @click="anewSelect(0)">{{province}}</li>
			<li :class="showIndex==1?'select':''" @click="anewSelect(1)" v-if="showIndex>=1">{{city}}</li>
			<li :class="showIndex==2?'select':''" @click="anewSelect(2)" v-if="showIndex>=2">{{area}}</li>
			<li :class="showIndex==3?'select':''" @click="anewSelect(3)" v-if="showIndex>=3">{{street}}</li>
		</ul>
		<ul v-if="showIndex==0" class="content" :style="'height:'+ heightCot + 'upx'">
			<li @click="selectPro(item.id,item.name)" v-for="(item,index) in provinceData" :key="item.id">{{item.name}}</li>
		</ul>
		<ul v-if="showIndex==1" class="content" :style="'height:'+ heightCot + 'upx'">
			<li @click="selectCity(item.id,item.name)" v-for="(item,index) in cityData" :key="item.id">{{item.name}}</li>
		</ul>

		<ul v-if="showIndex==2" class="content" :style="'height:'+ heightCot + 'upx'">
			<li @click="selectaArea(item.id,item.name)" v-for="(item,index) in areaData" :key="item.id">{{item.name}}</li>
		</ul>

		<ul v-if="showIndex==3" class="content" :style="'height:'+ heightCot + 'upx'">
			<li @click="selectStreet(item.id,item.name)" v-for="(item,index) in streetsData" :key="item.id">{{item.name}}</li>
		</ul>
	</view>
</template> 

<script>
	export default {
		data() {
			return {
				province: "请选择",
				city: "请选择",
				area: "请选择",
				street: "请选择",
				provinceData: '', // 当前展示的省数据
				cityData: '', // 当前展示的市数据
				areaData: '', //当前展示的区数据
				streetsData: '', //当前展示的区数据
				showIndex: 0,
				heightCot: 0,
				
				areaId: '',
				streetId: '',
			}
		},
		created() {
			uni.getSystemInfo({
				success: res => {
					this.heightCot = (res.safeArea.height * 2) / 2
				}
			}),
			this.$api.getAddressData({}, res => {
				if(res.status) {
					this.provinceData = res.data
				}
			})
		},
		methods: {
			anewSelect(num) {
				switch (num) {
					case 0:
						this.showIndex = 0;
						this.areaData = [];
						this.streetsData = [];
						this.city = '请选择';
						this.area = '请选择';
						this.street = '请选择'
						break;
					case 1:
						this.showIndex = 1;
						this.areaData = [];
						this.streetsData = [];
						this.area = '请选择';
						this.street = '请选择'
						break;
					case 2:
						this.showIndex = 2;
						this.streetsData = [];
						this.street = '请选择'
						break;
					case 3:
						break;
				}
			},
			selectPro(id, name) {
				this.province = name;
				this.$api.getAddressData({pid:id,level:'2'}, res => {
					if(res.status) {
						this.cityData = res.data;
					}
				})
				this.showIndex = 1;
			},
			selectCity(id, name) {
				this.city = name;
				this.$api.getAddressData({pid:id,level:'3'}, res => {
					if(res.status) {
						this.areaData = res.data;
					}
				})
				this.showIndex = 2;
			},
			selectaArea(id, name) {
				this.area = name;
				this.areaId = id;
				this.$api.getAddressData({pid:id,level:'4'}, res => {
					if(res.status) {
						if(res.data.length == 0) {
							let areas = this.province+' '+this.city+' '+this.area
							
							this.$emit('conceal',{areas:areas, area_id:this.areaId, street_id:this.areaId})
						} else {
							this.streetsData = res.data;
						}
					}
				})
				this.showIndex = 3;
			},
			selectStreet(id, name) {
				this.street = name;
				this.streetId = id;
				
				let areas = this.province+' '+this.city+' '+this.area+' '+this.street
				
				this.$emit('conceal',{areas:areas, area_id:this.areaId, street_id:this.streetId})
			}
		}
	}
</script>

<style lang="less">
	ul,li{
		list-style: none;
	}
	.level-container {
		width: 100%;
		background: #fff;
		position: fixed;
		bottom: 0;

		.select {
			color: red;
			position: relative;

			&::after {
				content: '';
				width: 40upx;
				height: 6upx;
				background: red;
				position: absolute;
				left: 50%;
				bottom: -8upx;
				margin-left: -20upx;
			}
		}

		li {
			font-size: 26upx;
		}

		.content,
		.name-list {
			padding: 0 20upx;
		}

		.title {
			font-size: 32upx;
			margin: 30upx 20upx;
		}

		.name-list {
			display: flex;

			li {
				margin-right: 40upx;
				padding-bottom: 6upx;
			}
		}

		.content {
			margin-top: 20upx;
			height: 600upx;
			overflow-y: auto;

			li {
				border-bottom: 1px solid #f1f1f1;
				padding: 20upx 0;
			}

			li:last-child {
				border-bototm: none !important;
			}
		}
	}
</style>
