<template>
	<view class="content">
		<view class="content-top">
			<view class='cell-group'>
				<view class='cell-item cell-item-mid'>
					<view class='cell-item-hd'>
						<view class='cell-hd-title'>支付密码</view>
					</view>
					<view class='cell-item-bd' style="width: 75%;">
						<input class='cell-bd-input' type="number" placeholder='输入6位新密码' maxlength="6" v-model="newPwd" style="width: 100%"></input>
					</view>
				</view>
				<view class='cell-item cell-item-mid'>
					<view class='cell-item-hd'>
						<view class='cell-hd-title'>确认密码</view>
					</view>
					<view class='cell-item-bd' style="width: 75%;">
						<input class='cell-bd-input' type="number" placeholder='输入6位新密码' v-model="rePwd" style="width: 100%"></input>
					</view>
				</view>
			</view>
		</view>
		<view class="button-bottom">
			<button class="btn btn-square btn-b" hover-class="btn-hover2" @click="submitHandler()" :disabled='submitStatus'
			 :loading='submitStatus'>修改</button>
		</view>
	</view>
</template>

<script>
	export default {
		data() {
			return {
				newPwd: '',
				rePwd: '',
				sex: 0,
				submitStatus: false,
				oldPassword: true,
				mobile:'',
				code:'',
				verification: true, // 通过v-show控制显示获取还是倒计时
				timer: 60, // 定义初始时间为60s
			}
		},
		methods: {
			// 保存资料
			submitHandler() {
				if(this.submitStatus){
					return false;
				}
				this.submitStatus = true;
				if (this.newPwd === '') {
					this.$common.errorToShow('请输入新密码')
					this.submitStatus = false;
				}else if (this.rePwd === '') {
					this.$common.errorToShow('请输入确认密码')
					this.submitStatus = false;
				} else if(this.rePwd.length !=6 || this.newPwd.length !=6){
					this.$common.errorToShow('密码长度6位数')
					this.submitStatus = false;
				} else if(this.rePwd != this.newPwd){
					this.$common.errorToShow('密码不一致')
					this.submitStatus = false;
				} else {
					let data = {
						newpwd: this.newPwd,
						repwd: this.rePwd
					};
					this.$api.updatePaypwd(data, res => {
						if (res.status){
							this.submitStatus = false;
							this.$common.successToShow(res.msg)
							setTimeout(function(){
								uni.navigateBack({
									delta: 1
								});
							},1000)
						} else {
							this.$common.errorToShow(res.msg)
								this.submitStatus = false;
						}
					},res => {
						this.submitStatus = false;
					})
				}
			}
		},
		onLoad: function() {

		}
	}
</script>

<style>
.user-head {
	height: 100upx;
}

.user-head-img {
	height: 90upx;
	width: 90upx;
	border-radius: 50%;
}

.cell-hd-title {
	color: #333;
}

.cell-item-bd {
	color: #666;
	font-size: 26upx;
}
.cell-item-hd{
	width: 160rpx;
}
</style>
