<template>
	<view class="content">
		<view class="content-top">
			<view class='cell-group'>
				<view class='cell-item cell-item-mid'>
					<view class='cell-item-hd'>
						<view class='cell-hd-title'>收货人</view>
					</view>
					<view class='cell-item-bd'>
						<input type="text" class='cell-bd-input' placeholder='请填写收货人姓名' v-model="name"></input>
					</view>
				</view>
				<view class='cell-item cell-item-mid'>
					<view class='cell-item-hd'>
						<view class='cell-hd-title'>手机号</view>
					</view>
					<view class='cell-item-bd'>
						<input type="text" class='cell-bd-input' placeholder='请填写收货人手机号' v-model="mobile"></input>
					</view>
				</view>
				
				<view class='cell-item cell-item-mid right-img'>
					<view class='cell-item-hd'>
						<view class='cell-hd-title'>所在地区</view>
					</view>
					
					<view class='cell-item-bd' @click="selectAddress">					
						<view :class="{active:isActive}">{{dizhi}}</view>
					</view>					
					<view class='cell-item-ft' @click="selectAddress">
						<image class='cell-ft-next icon' src='/static/image/ic-pull-down.png'></image>
					</view>
				</view>
				
				<view class='cell-item cell-item-mid'>
					<view class='cell-item-hd'>
						<view class='cell-hd-title'>详细地址</view>
					</view>
					<view class='cell-item-bd'>
						<input type="text" class='cell-bd-input' placeholder='请填写收货详细地址' v-model="address"></input>
					</view>
				</view>
				<view class='cell-item' @click="defaultChange">
					<view class='cell-item-hd'>
						<view class='cell-hd-title'>设为默认</view>
					</view>
					<view class='cell-item-ft' >
						<label class="radio" ><radio value="1" :checked="checked" color="#FF7159"/></label>
					</view>
				</view>
			</view>
		</view>
		<view class="button-bottom">			
			<button class="btn btn-square btn-w" @click="delShip" v-if="id && id != 0" hover-class="btn-hover2" :disabled='submitStatus' :loading='submitStatus'>删除</button>
			
			<button class="btn btn-square btn-b" @click="saveShip" hover-class="btn-hover2" :disabled='submitStatus' :loading='submitStatus'>保存</button>
		</view>
		
		<view class="cu-modal bottom-modal" :class="modalName=='bottomModal'?'show':''">
			<view class="cu-dialog">
				<ehPicker @conceal="conceal" v-if="popup" />
			</view>
		</view>
				
		
	</view>

</template>

<script>
import ehPicker from '@/components/erha-picker/erha-picker.vue';
export default {
	components: {
		ehPicker
	},
	data() {
		return {
			id: 0,
			name: '',
			mobile: '',
			region: ['北京市', '北京市', '东城区', '东华门街道办事处'],
			areaId: '',//四级地址
			cityId: '',//三级地址
			address: '',
			is_def: 1,
			multiArray: [
				[],
				[],
				[]
			],
			multiIndex: [110000, 110100, 110101, 920106],
			checked: true,
			pickerValue: '',
			defaultIndex: [0, 0, 0, 0],
			submitStatus: false,

			dizhi:'请选择地址',
			isActive:false,//字体颜色样式	
			popup: false,
			modalName: null,
		}
	},
	computed: {},
	methods: {
		selectAddress() {
			this.popup = true;
			this.showModal();
		},
		showModal() {
			this.modalName = 'bottomModal';
		},
		hideModal() {
			this.modalName = null
		},		
		conceal(param) {
			// 获取到传过来的 省 市 区 县数据
			if(param.street_id == '' || param.street_id == undefined) {
				this.$common.errorToShow('请选择地区信息')
			}
			this.dizhi = param.areas
			this.areaId = param.street_id
			this.cityId = param.area_id
			// 选择完毕后隐藏
			this.popup = false;
			this.hideModal();
		},
		// 信息验证
		checkData (data) {
			this.submitStatus = false;
			if (!data.name) {
				this.$common.errorToShow('请输入收货人姓名')
				return false
			} else if (!data.mobile) {
				this.$common.errorToShow('请输入收货人手机号')
				return false
			} else if (data.mobile.length !== 11) {
				this.$common.errorToShow('收货人手机号格式不正确')
				return false
			} else if (!data.area_id) {
				this.$common.errorToShow('请选择地区信息')
				return false
			} else if (!data.address) {
				this.$common.errorToShow('请输入收货地址详细信息')
				return false
			} else {
				return true
			}
		},
		//默认
		defaultChange(){
			if(this.checked){
				this.checked = false;
				this.is_def = 2;
			}else{
				this.checked = true;
				this.is_def = 1;
			}
		},
		//编辑获取收货地址信息
		getShipInfo() {
			let data = {
				'id': this.id
			}
			this.$api.shipDetail(data, res => {
				if(res.status){
					let region = res.data.area_name.split(" ");
					this.name = res.data.name;
					this.mobile = res.data.mobile;
					this.region = region;
					this.areaId = res.data.area_id;
					
					//this.pickerValue = this.region[0]+ " "+ this.region[1]+" "+this.region[2]
					//this.$refs.areaPicker.init();//初始化插件
					
					this.address = res.data.address;
					this.is_def = res.data.is_def;
					this.dizhi = res.data.area_name;
					if(res.data.is_def == 1){
						this.checked = true;
					}else{
						this.checked = false;
					}
				}else{
					this.$common.errorToShow('获取收货地址信息出现问题');
					// this.submitStatus = false;
				}
			},res => {
				this.submitStatus = false;
			});
		},
		//删除地址
		delShip() {
			this.submitStatus = true;
			this.$api.removeShip({'id': this.id}, res => {
				if(res.status){
					this.$common.successToShow(res.msg, ress => {
						// this.submitStatus = false;
						uni.navigateBack({
							delta: 1
						});
					});
				}else{
					this.$common.errorToShow(res.msg);
					// this.submitStatus = false;
				}
			},res => {
				this.submitStatus = false;
			});
		},
		//存储收货地址
		saveShip() {
			this.submitStatus = true;
			let data = {
				name: this.name,
				address: this.address,
				mobile: this.mobile,
				is_def: this.is_def,
				area_id: this.areaId,
				city_id: this.cityId
			}
			
			if(this.id && this.id != 0){
				//编辑存储
				data.id = this.id
				
				if (this.checkData(data)) {
					this.$api.editShip(data, res => {
						if(res.status){
							this.$common.successToShow(res.msg, ress => {
								// this.submitStatus = false;
								uni.navigateBack({
									delta: 1
								});
							});
						}else{
							this.$common.errorToShow(res.msg);
							// this.submitStatus = false;
						}
					},res => {
						this.submitStatus = false;
					});
				}
			}else{
				//添加
				if (this.checkData(data)) {
					this.$api.editShip(data, res => {
						if(res.status){
							this.$common.successToShow(res.msg, ress => {
								// this.submitStatus = false;
								uni.navigateBack({
									delta: 1
								});
							});
						}else{
							this.$common.errorToShow(res.msg);
							// this.submitStatus = false;
						}
					},res => {
						this.submitStatus = false;
					});
				}
			}
		},
			
	},
	onNavigationBarButtonTap(e) {

	},	
	onLoad(e) {
		if(e.ship_id){
			//编辑
			this.id = e.ship_id;
			this.getShipInfo();
		}else{
			//添加
			//this.pickerValue = this.region[0]+ " "+ this.region[1]+" "+this.region[2];
			uni.setNavigationBarTitle({
				title: '添加地址'
			});
		}
	}

}
</script>

<style>
.active{
	color: #000000;
}	
	
.user-head{
	height: 100rpx;
}
.user-head-img{
	height: 90rpx;
	width: 90rpx;
	border-radius: 50%;
}
.cell-hd-title{
	color: #333;
}
.cell-item-hd{
	width: 180rpx;
}
.cell-item-bd{
	color: #666;
	font-size: 26rpx;
}
.button-bottom .btn {
	width: 50%;
}
.cell-bd-input{
	width: 100%;
}
.right-img .cell-item-ft{
	right: 26rpx;
}
/* #ifdef MP-ALIPAY */
input{
	font-size: 24rpx;
}
/* #endif */



/* ==================
         模态窗口
 ==================== */

.cu-modal {
	position: fixed;
	top: 0;
	right: 0;
	bottom: 0;
	left: 0;
	z-index: 1110;
	opacity: 0;
	outline: 0;
	text-align: center;
	-ms-transform: scale(1.185);
	transform: scale(1.185);
	backface-visibility: hidden;
	perspective: 2000upx;
	background: rgba(0, 0, 0, 0.6);
	transition: all 0.3s ease-in-out 0s;
	pointer-events: none;
}

.cu-modal::before {
	content: "\200B";
	display: inline-block;
	height: 100%;
	vertical-align: middle;
}

.cu-modal.show {
	opacity: 1;
	transition-duration: 0.3s;
	-ms-transform: scale(1);
	transform: scale(1);
	overflow-x: hidden;
	overflow-y: auto;
	pointer-events: auto;
}

.cu-dialog {
	position: relative;
	display: inline-block;
	vertical-align: middle;
	margin-left: auto;
	margin-right: auto;
	width: 680upx;
	max-width: 100%;
	background-color: #f8f8f8;
	border-radius: 10upx;
	overflow: hidden;
}

.cu-modal.bottom-modal::before {
	vertical-align: bottom;
}

.cu-modal.bottom-modal .cu-dialog {
	width: 100%;
	border-radius: 0;
}

.cu-modal.bottom-modal {
	margin-bottom: -1000upx;
}

.cu-modal.bottom-modal.show {
	margin-bottom: 0;
}

.cu-modal.drawer-modal {
	transform: scale(1);
	display: flex;
}

.cu-modal.drawer-modal .cu-dialog {
	height: 100%;
	min-width: 200upx;
	border-radius: 0;
	margin: initial;
	transition-duration: 0.3s;
}

.cu-modal.drawer-modal.justify-start .cu-dialog {
	transform: translateX(-100%);
}

.cu-modal.drawer-modal.justify-end .cu-dialog {
	transform: translateX(100%);
}
.cu-modal.drawer-modal.show .cu-dialog {
	transform: translateX(0%);
}
.cu-modal .cu-dialog>.cu-bar:first-child .action{
  min-width: 100rpx;
  margin-right: 0;
  min-height: 100rpx;
}
</style>