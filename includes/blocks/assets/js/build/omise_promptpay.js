(()=>{"use strict";const e=window.wp.i18n,t=window.wp.htmlEntities,n=window.React,i=window.wc.wcBlocksRegistry,s=(0,window.wc.wcSettings.getSetting)("omise_promptpay_data",{}),a=(0,e.__)("Promptpay","omise");!function({settings:e,label:s}){const a=()=>(0,t.decodeEntities)(e.description||"");(0,i.registerPaymentMethod)({name:e.name,label:(0,n.createElement)((e=>{const{PaymentMethodLabel:t}=e.components;return(0,n.createElement)(t,{text:s})}),null),content:(0,n.createElement)(a,null),edit:(0,n.createElement)(a,null),canMakePayment:()=>!0,ariaLabel:s,supports:{features:e.supports}})}({settings:s,label:(0,t.decodeEntities)(s.title)||a})})();