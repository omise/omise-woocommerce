(()=>{"use strict";window.wp.i18n;const a=window.wp.htmlEntities,e=window.React,t=window.wc.wcBlocksRegistry;function o({settings:o,label:i}){const n=()=>(0,a.decodeEntities)(o.description||"");(0,t.registerPaymentMethod)({name:o.name||"",label:(0,e.createElement)((a=>{const{PaymentMethodLabel:t}=a.components;return(0,e.createElement)(t,{text:i})}),null),content:(0,e.createElement)(n,null),edit:(0,e.createElement)(n,null),canMakePayment:()=>!0,ariaLabel:i,supports:{features:o.supports}})}const i=window.wc.wcSettings,n=["omise_alipay_data","omise_alipay_cn_data","omise_alipay_hk_data","omise_dana_data","omise_gcash_data","omise_kakaopay_data","omise_promptpay_data","omise_touch_n_go_data","omise_billpayment_tesco_data","omise_shopeepay_data","omise_wechat_pay_data","omise_grabpay_data","omise_paynow_data","omise_ocbc_data","omise_fpx_data","omise_maybank_qr_data","omise_duitnow_qr_data","omise_paypay_data","omise_rabbit_linepay_data"];for(const e of n){const t=(0,i.getSetting)(e,{});t.name&&o({settings:t,label:(0,a.decodeEntities)(t.title)||"No title set"})}})();