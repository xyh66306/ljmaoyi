	
	//检测版本号
	const checkVersion = async function () {
		return new Promise( (resolve) => {
			var _this = this;
			// #ifdef MP-WEIXIN
			let accountInfo = wx.getAccountInfoSync(); 
			let version = accountInfo.miniProgram.version; // 1.0.0 小程序版本号
			if(version != ''){
				var old_v = version;
				//获取线上版本信息
				new_version(old_v).then((res, rej) => {
					resolve(res);
				});
			}
			// #endif
			// #ifdef APP-PLUS
			plus.runtime.getProperty(plus.runtime.appid, function(widgetInfo) {
				var old_v = widgetInfo.version;
				//获取线上版本信息
				new_version(old_v).then((res, rej) => {
					resolve(res);
				});
			});  
			// #endif
			// #ifdef H5
			var systemInfo = uni.getSystemInfoSync();
			var old_v = systemInfo.appVersion;
			//获取线上版本信息
			new_version(old_v).then((res, rej) => {
				//console.log("222",res)
				resolve(res);
			});
			// #endif
		})
		
	};
	// 获取线上新版本信息②
	async function new_version(old_v) {
		var _this=this;
		//1.服务器线上地址返回的数据(当前为模拟数据，实际需要去服务器拉取)
		var server_data={
			'version':'1.0.22',//线上版本号
			'download_url':'https://img.yudingmeihao.com/tp/down/android/甜聘1.0.12.apk',//新版app下载地址
			'is_force':0,//是否强制安装（强制安装会直接弹窗，大版本更新可启用）（0：不强制 1，强制）
			'updateType':1,//下载地址类型，1普通下载，2应用商店（可以把机型提交服务器获取对应应用商店下载地址）
			'version_desc':"修复已知问题",//线上版本说明
		};
		var ov=old_v;//当前版本号
		var nv =server_data.version;//线上版本号
		server_data.old_v=ov;
		server_data.new_v=nv;
		ov = Number(ov.split('.').join(""));
		nv=Number(nv.split('.').join(""));
		console.log("oldversion", ov);
		console.log("newversion", nv);
		server_data.ov=ov;
		server_data.nv=nv;
		server_data.is_update=0;
		server_data.is_force=0;
		if (nv > ov) {
			server_data.is_update=1;
			if(server_data.is_force==1){
				server_data.is_force =1;//直接吊起更新，不需要点击，
			}
		}
		return server_data;
		
	};
	
	export default {
		checkVersion,
	}
