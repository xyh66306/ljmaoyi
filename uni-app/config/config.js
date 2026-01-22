export const apiBaseUrl = 'https://jytx.wansenjiankang.cn/'
export const h5Url = apiBaseUrl + "wap/" //H5端网站地址,
// export const h5Url = "http://localhost:8080/wap/" //H5端网站地址,

// #ifdef H5
export const baseUrl = process.env.NODE_ENV === 'development' ? window.location.origin + '/' : apiBaseUrl
// #endif
export const paymentType = {
	//支付单类型
	order: 1, //订单
	recharge: 2, //充值
	form_order: 5, //表单付款码
	form_pay: 6 //表单订单
}


export const goodsCatBanner = 'tpl1_class_banner1'

// #ifdef MP-TOUTIAO
export const ttPlatform = 'toutiao'; //toutiao=今日头条小程序, douyin=抖音小程序, pipixia=皮皮虾小程序, huoshan=火山小视频小程序
// #endif

export const weixinOpenAlipay = true; //微信公众号下是否开启支付宝支付

export const closeMobileLogin = true; //是否关闭验证码登录

export const logisticsFree = true; //是否通过百度查询物流轨迹 H5有用。

export const logisticsFreeUrl = "https://m.baidu.com/s?word="; //免费查询物流轨迹地址，H5有用

//是否开启企业微信
export const openQiyeWeixin = true;
//企业微信id
export const corpId = "";
//企业微信客服url
export const extInfoUrl = "";

//积分商城
export const pointmallBanner = 'point_mall_banner';